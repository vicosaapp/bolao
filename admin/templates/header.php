<?php
// Iniciar buffer de saída
ob_start();

// Iniciar a sessão se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php?redirect=admin');
    exit;
}

// Obter nível de acesso do usuário
$usuario_nivel = $_SESSION['usuario_nivel'] ?? 1;

// Definir rotas de acesso com base no nível
$dashboard_path = '../';
if ($usuario_nivel == 3) { // Super Admin
    $dashboard_path = '../superadmin/';
} elseif ($usuario_nivel == 2) { // Revendedor
    $dashboard_path = '../revendedor/';
} elseif ($usuario_nivel == 1) { // Usuário comum
    $dashboard_path = '../usuario/';
}

// Obter título da página se definido
$page_title = $page_title ?? 'Painel de Controle';

// URL atual para marcar item de menu ativo
$current_url = $_SERVER['REQUEST_URI'];
$current_page = basename(parse_url($current_url, PHP_URL_PATH));

// Adicionar log de depuração
error_log("Caminho atual: " . $current_url);
error_log("Página atual: " . $current_page);
error_log("Nível de usuário: " . $usuario_nivel);
error_log("Dashboard path: " . $dashboard_path);

// Adicionar logs de depuração detalhados
error_log("Sessão iniciada: " . (session_status() == PHP_SESSION_ACTIVE ? "Sim" : "Não"));
error_log("ID da sessão: " . session_id());
error_log("Variáveis de sessão: " . print_r($_SESSION, true));
error_log("Dados do usuário na sessão:");
error_log("- ID: " . ($_SESSION['usuario_id'] ?? 'Não definido'));
error_log("- Nome: " . ($_SESSION['usuario_nome'] ?? 'Não definido'));
error_log("- Nível: " . ($_SESSION['usuario_nivel'] ?? 'Não definido'));

// Log específico para caminho do perfil
error_log("Caminho do perfil: " . $dashboard_path . "perfil.php");
error_log("URL atual: " . $_SERVER['REQUEST_URI']);
error_log("Verificando se o arquivo perfil.php existe nos seguintes caminhos:");
error_log(" - " . $_SERVER['DOCUMENT_ROOT'] . $dashboard_path . "perfil.php: " . (file_exists($_SERVER['DOCUMENT_ROOT'] . $dashboard_path . "perfil.php") ? "SIM" : "NÃO"));

// Logs de depuração detalhados
error_log("DEBUG: Início do header");
error_log("DEBUG: Método da requisição: " . $_SERVER['REQUEST_METHOD']);
error_log("DEBUG: URL completa: " . $_SERVER['REQUEST_URI']);
error_log("DEBUG: Caminho do script: " . $_SERVER['SCRIPT_NAME']);
error_log("DEBUG: Informações da sessão antes da verificação:");
error_log("DEBUG: Session ID: " . session_id());
error_log("DEBUG: Session Status: " . session_status());
error_log("DEBUG: Variáveis de sessão: " . print_r($_SESSION, true));
error_log("DEBUG: Cookies de sessão: " . print_r($_COOKIE, true));

// Log de depuração para nível de acesso
error_log("DEBUG: Nível de acesso do usuário: " . ($usuario_nivel ?? 'Não definido'));
error_log("DEBUG: Dashboard path determinado: " . $dashboard_path);

// Definir os caminhos base para os recursos
$base_url = '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Bolão 10</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS personalizado -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>../assets/css/admin.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    
    <?php if (isset($extraCSS)): ?>
        <?php foreach ($extraCSS as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <img src="<?php echo $base_url; ?>../assets/img/logo.png" alt="Logo Bolão">
                </div>
                <h2>Bolão 10</h2>
                <button class="sidebar-toggle-btn" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <?php if ($usuario_nivel >= 1): // Menu para todos os usuários ?>
                    <li class="nav-item <?php echo ($current_page == 'index.php' || $current_page == 'dashboard.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_path; ?>">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'meus-bilhetes.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_path; ?>meus-bilhetes.php">
                            <i class="fas fa-ticket-alt"></i>
                            <span>Meus Bilhetes</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'concursos.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_path; ?>concursos.php">
                            <i class="fas fa-trophy"></i>
                            <span>Concursos</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'resultados.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_path; ?>resultados.php">
                            <i class="fas fa-check-circle"></i>
                            <span>Resultados</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'perfil.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_path; ?>perfil.php">
                            <i class="fas fa-user"></i>
                            <span>Meu Perfil</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if ($usuario_nivel >= 2): // Menu para revendedores e admins ?>
                    <li class="nav-section">
                        <span>Revendedor</span>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'vendas.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_path; ?>vendas.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Vendas</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'clientes.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_path; ?>clientes.php">
                            <i class="fas fa-users"></i>
                            <span>Clientes</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'comissoes.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_path; ?>comissoes.php">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Comissões</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'relatorios.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_path; ?>relatorios.php">
                            <i class="fas fa-chart-bar"></i>
                            <span>Relatórios</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if ($usuario_nivel >= 3): // Menu apenas para administradores ?>
                    <li class="nav-section">
                        <span>Administração</span>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'gestao-concursos.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_path; ?>gestao-concursos.php">
                            <i class="fas fa-cogs"></i>
                            <span>Gestão de Concursos</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'usuarios.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_path; ?>usuarios.php">
                            <i class="fas fa-user-cog"></i>
                            <span>Usuários</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'revendedores.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_path; ?>revendedores.php">
                            <i class="fas fa-store"></i>
                            <span>Revendedores</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'financeiro.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_path; ?>financeiro.php">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span>Financeiro</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'configuracoes.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_path; ?>configuracoes.php">
                            <i class="fas fa-wrench"></i>
                            <span>Configurações</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <a href="../../logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Sair</span>
                </a>
            </div>
        </aside>
        
        <!-- Conteúdo principal -->
        <div class="main-content">
            <!-- Header -->
            <header class="main-header">
                <button class="mobile-toggle" id="mobileToggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="search-bar">
                    <input type="text" placeholder="Buscar...">
                    <button><i class="fas fa-search"></i></button>
                </div>
                
                <div class="header-right">
                    <div class="notifications dropdown">
                        <button class="dropdown-toggle">
                            <i class="fas fa-bell"></i>
                            <span class="badge">3</span>
                        </button>
                        <div class="dropdown-menu">
                            <div class="dropdown-header">
                                <h3>Notificações</h3>
                                <a href="#">Marcar todas como lidas</a>
                            </div>
                            <ul class="notification-list">
                                <li class="notification-item unread">
                                    <div class="notification-icon">
                                        <i class="fas fa-ticket-alt"></i>
                                    </div>
                                    <div class="notification-content">
                                        <p>Novo concurso disponível</p>
                                        <span>Há 15 minutos</span>
                                    </div>
                                </li>
                                <li class="notification-item">
                                    <div class="notification-icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="notification-content">
                                        <p>Resultado do concurso publicado</p>
                                        <span>Há 1 dia</span>
                                    </div>
                                </li>
                            </ul>
                            <div class="dropdown-footer">
                                <a href="#">Ver todas</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="user-dropdown dropdown">
                        <button class="dropdown-toggle">
                            <div class="user-avatar">
                                <img src="<?php echo $base_url; ?>../assets/img/user-avatar.png" alt="Avatar">
                            </div>
                            <span class="user-name"><?php echo $_SESSION['usuario_nome'] ?? 'Usuário'; ?></span>
                        </button>
                        <div class="dropdown-menu">
                            <ul>
                                <li><a href="<?php echo $dashboard_path; ?>perfil.php"><i class="fas fa-user"></i> Meu Perfil</a></li>
                                <li><a href="<?php echo $dashboard_path; ?>configuracoes.php"><i class="fas fa-cog"></i> Configurações</a></li>
                                <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Container do conteúdo -->
            <div class="content-container">
                <!-- Aqui será inserido o conteúdo específico de cada página --> 