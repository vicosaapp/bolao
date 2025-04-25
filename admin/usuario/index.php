<?php
// Definir o título da página
$page_title = "Dashboard do Usuário";

// Verificar permissões
require_once '../auth.php';
requireAccess(1); // Nível mínimo: usuário

// Incluir o cabeçalho
require_once '../templates/header.php';

// Dados do usuário (em produção, estes dados viriam do banco de dados)
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$usuario_email = $_SESSION['usuario_email'] ?? 'usuario@exemplo.com';

// Dados de exemplo para o dashboard
$total_bilhetes = 5;
$bilhetes_premiados = 1;
$proximos_sorteios = [
    ['concurso' => 'Mega-Sena 2345', 'data' => '17/05/2023', 'valor' => 'R$ 45.000.000,00'],
    ['concurso' => 'Lotofácil 1234', 'data' => '18/05/2023', 'valor' => 'R$ 1.700.000,00'],
    ['concurso' => 'Quina 6789', 'data' => '19/05/2023', 'valor' => 'R$ 12.500.000,00'],
];
?>

<div class="dashboard-wrapper">
    <!-- Boas-vindas e resumo -->
    <div class="welcome-card mb-4">
        <div class="card">
            <div class="card-body">
                <h4 class="welcome-title">Olá, <?php echo $usuario_nome; ?>!</h4>
                <p class="welcome-text">Bem-vindo ao seu painel de controle. Aqui você pode gerenciar seus bilhetes e acompanhar os resultados.</p>
            </div>
        </div>
    </div>

    <!-- Cards de estatísticas -->
    <div class="row">
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <div>
                            <h5 class="card-title">Meus Bilhetes</h5>
                            <h2 class="card-value"><?php echo $total_bilhetes; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center">
                    <span>Ver todos</span>
                    <a href="meus-bilhetes.php" class="ms-auto">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div>
                            <h5 class="card-title">Bilhetes Premiados</h5>
                            <h2 class="card-value"><?php echo $bilhetes_premiados; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center">
                    <span>Ver prêmios</span>
                    <a href="bilhetes-premiados.php" class="ms-auto">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div>
                            <h5 class="card-title">Comprar Bilhete</h5>
                            <p class="card-subtitle">Novos concursos disponíveis!</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center">
                    <span>Comprar agora</span>
                    <a href="comprar.php" class="ms-auto">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Próximos sorteios -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Próximos Sorteios</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php foreach ($proximos_sorteios as $sorteio): ?>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0"><?php echo $sorteio['concurso']; ?></h6>
                                        <small class="text-muted">Data: <?php echo $sorteio['data']; ?></small>
                                    </div>
                                    <span class="badge bg-primary rounded-pill"><?php echo $sorteio['valor']; ?></span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="card-footer text-end">
                    <a href="concursos.php" class="btn btn-primary btn-sm">Ver todos os concursos</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimos resultados -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Últimos Resultados</h5>
                    <a href="resultados.php" class="btn btn-sm btn-outline-primary">Ver todos</a>
                </div>
                <div class="card-body">
                    <div class="result-list">
                        <div class="result-item">
                            <div class="result-header">
                                <h6>Mega-Sena 2344</h6>
                                <span class="result-date">15/05/2023</span>
                            </div>
                            <div class="result-numbers">
                                <span class="number">05</span>
                                <span class="number">19</span>
                                <span class="number">24</span>
                                <span class="number">37</span>
                                <span class="number">43</span>
                                <span class="number">56</span>
                            </div>
                            <div class="result-prize">
                                <strong>Prêmio: R$ 43.256.874,00</strong>
                            </div>
                        </div>
                        
                        <div class="result-item">
                            <div class="result-header">
                                <h6>Lotofácil 1233</h6>
                                <span class="result-date">14/05/2023</span>
                            </div>
                            <div class="result-numbers">
                                <span class="number">01</span>
                                <span class="number">03</span>
                                <span class="number">05</span>
                                <span class="number">06</span>
                                <span class="number">07</span>
                                <span class="number">08</span>
                                <span class="number">09</span>
                                <span class="number">12</span>
                                <span class="number">13</span>
                                <span class="number">15</span>
                                <span class="number">17</span>
                                <span class="number">19</span>
                                <span class="number">20</span>
                                <span class="number">22</span>
                                <span class="number">25</span>
                            </div>
                            <div class="result-prize">
                                <strong>Prêmio: R$ 1.524.364,00</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir o rodapé
require_once '../templates/footer.php';
?> 