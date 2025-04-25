<?php
// Definir o título da página
$page_title = "Painel Administrativo";

// Verificar permissões
require_once '../auth.php';
requireAccess(3); // Nível mínimo: administrador

// Incluir modelos necessários
require_once '../models/ConcursoModel.php';
require_once '../models/BilheteModel.php';
require_once '../models/VendedorModel.php';

// Inicializar modelos
$concursoModel = new ConcursoModel();
$bilheteModel = new BilheteModel();
$vendedorModel = new VendedorModel();

// Buscar estatísticas
$totalConcursos = $concursoModel->contarConcursos();
$concursosAtivos = $concursoModel->contarConcursosAtivos();
$totalBilhetes = $bilheteModel->contarBilhetes();
$totalVendas = $bilheteModel->calcularTotalVendas();
$topVendedores = $vendedorModel->obterTopVendedores(5);

// Incluir cabeçalho
require_once '../templates/header.php';
?>

<div class="content">
    <div class="content-header">
        <h1>Painel Administrativo</h1>
        <p class="text-muted">Visão geral do sistema de bolão</p>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="dashboard-stats">
        <div class="stat-card primary">
            <div class="stat-card-header">
                <h5 class="stat-card-title">Total de Concursos</h5>
                <div class="stat-card-icon">
                    <i class="fas fa-trophy"></i>
                </div>
            </div>
            <h2 class="stat-card-value"><?php echo number_format($totalConcursos, 0, ',', '.'); ?></h2>
            <p class="stat-card-description">
                <span>Concursos realizados</span>
            </p>
        </div>

        <div class="stat-card success">
            <div class="stat-card-header">
                <h5 class="stat-card-title">Concursos Ativos</h5>
                <div class="stat-card-icon">
                    <i class="fas fa-play-circle"></i>
                </div>
            </div>
            <h2 class="stat-card-value"><?php echo number_format($concursosAtivos, 0, ',', '.'); ?></h2>
            <p class="stat-card-description">
                <span>Concursos em andamento</span>
            </p>
        </div>

        <div class="stat-card info">
            <div class="stat-card-header">
                <h5 class="stat-card-title">Total de Bilhetes</h5>
                <div class="stat-card-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
            </div>
            <h2 class="stat-card-value"><?php echo number_format($totalBilhetes, 0, ',', '.'); ?></h2>
            <p class="stat-card-description">
                <span>Bilhetes vendidos</span>
            </p>
        </div>

        <div class="stat-card danger">
            <div class="stat-card-header">
                <h5 class="stat-card-title">Total de Vendas</h5>
                <div class="stat-card-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
            <h2 class="stat-card-value">R$ <?php echo number_format($totalVendas, 2, ',', '.'); ?></h2>
            <p class="stat-card-description">
                <span>Valor total de vendas</span>
            </p>
        </div>
    </div>

    <!-- Top Vendedores -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Top 5 Vendedores</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nome</th>
                                    <th>Total de Vendas</th>
                                    <th>Comissão</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($topVendedores as $vendedor): ?>
                                <tr>
                                    <td><?php echo $vendedor['codigo']; ?></td>
                                    <td><?php echo $vendedor['nome']; ?></td>
                                    <td>R$ <?php echo number_format($vendedor['total_vendas'], 2, ',', '.'); ?></td>
                                    <td>R$ <?php echo number_format($vendedor['comissao'], 2, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Vendas -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Vendas por Mês</h5>
                </div>
                <div class="card-body">
                    <canvas id="vendasPorMesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Scripts específicos para a página
$page_scripts = [
    '../assets/js/chart.min.js',
    '../assets/js/dashboard.js'
];

// Incluir o rodapé
require_once '../templates/footer.php';
?> 