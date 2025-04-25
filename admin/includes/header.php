<?php
// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Obter nível de acesso do usuário
$user_level = $_SESSION['user_level'] ?? 0;

// Definir rotas de acesso com base no nível
$dashboard_link = './';
if ($user_level == 3) { // Super Admin
    $dashboard_link = '../superadmin/';
} elseif ($user_level == 2) { // Revendedor
    $dashboard_link = '../revendedor/';
} elseif ($user_level == 1) { // Usuário comum
    $dashboard_link = '../usuario/';
}

// Obter título da página se definido
$page_title = $page_title ?? 'Painel de Controle';

// URL atual para marcar item de menu ativo
$current_url = $_SERVER['REQUEST_URI'];
$current_page = basename(parse_url($current_url, PHP_URL_PATH));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Sistema de Bolão</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <img src="../assets/img/logo.png" alt="Logo Bolão">
                </div>
                <h2>Bolão</h2>
                <button class="sidebar-toggle-btn" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <?php if ($user_level >= 1): // Menu para todos os usuários ?>
                    <li class="nav-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_link; ?>">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'meus-bilhetes.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_link; ?>meus-bilhetes.php">
                            <i class="fas fa-ticket-alt"></i>
                            <span>Meus Bilhetes</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'concursos.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_link; ?>concursos.php">
                            <i class="fas fa-trophy"></i>
                            <span>Concursos</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'resultados.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_link; ?>resultados.php">
                            <i class="fas fa-check-circle"></i>
                            <span>Resultados</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'perfil.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_link; ?>perfil.php">
                            <i class="fas fa-user"></i>
                            <span>Meu Perfil</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if ($user_level >= 2): // Menu para revendedores e admins ?>
                    <li class="nav-section">
                        <span>Revendedor</span>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'vendas.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_link; ?>vendas.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Vendas</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'clientes.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_link; ?>clientes.php">
                            <i class="fas fa-users"></i>
                            <span>Clientes</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'comissoes.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_link; ?>comissoes.php">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Comissões</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'relatorios.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_link; ?>relatorios.php">
                            <i class="fas fa-chart-bar"></i>
                            <span>Relatórios</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if ($user_level >= 3): // Menu apenas para administradores ?>
                    <li class="nav-section">
                        <span>Administração</span>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'gestao-concursos.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_link; ?>gestao-concursos.php">
                            <i class="fas fa-cogs"></i>
                            <span>Gestão de Concursos</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'usuarios.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_link; ?>usuarios.php">
                            <i class="fas fa-user-cog"></i>
                            <span>Usuários</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'revendedores.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_link; ?>revendedores.php">
                            <i class="fas fa-store"></i>
                            <span>Revendedores</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'financeiro.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_link; ?>financeiro.php">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span>Financeiro</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($current_page == 'configuracoes.php') ? 'active' : ''; ?>">
                        <a href="<?php echo $dashboard_link; ?>configuracoes.php">
                            <i class="fas fa-wrench"></i>
                            <span>Configurações</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <a href="../logout.php">
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
                                <li class="notification-item unread">
                                    <div class="notification-icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="notification-content">
                                        <p>Resultado do Concurso #325 disponível</p>
                                        <span>Há 2 horas</span>
                                    </div>
                                </li>
                                <li class="notification-item">
                                    <div class="notification-icon">
                                        <i class="fas fa-trophy"></i>
                                    </div>
                                    <div class="notification-content">
                                        <p>Você ganhou um prêmio!</p>
                                        <span>Ontem</span>
                                    </div>
                                </li>
                            </ul>
                            <div class="dropdown-footer">
                                <a href="#">Ver todas</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="user-profile dropdown">
                        <button class="dropdown-toggle">
                            <img src="../assets/img/profile.jpg" alt="Foto de Perfil">
                            <span><?php echo $_SESSION['user_name'] ?? 'Usuário'; ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu">
                            <ul>
                                <li>
                                    <a href="<?php echo $dashboard_link; ?>perfil.php">
                                        <i class="fas fa-user"></i>
                                        Meu Perfil
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $dashboard_link; ?>configuracoes.php">
                                        <i class="fas fa-cog"></i>
                                        Configurações
                                    </a>
                                </li>
                                <li>
                                    <a href="../logout.php">
                                        <i class="fas fa-sign-out-alt"></i>
                                        Sair
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Conteúdo da página -->
            <div class="content">
                <!-- Aqui o conteúdo específico de cada página será inserido -->
            </div>
        </div>
    </div>
</body>
</html> 