<?php
// Definir o título da página
$page_title = "Dashboard do Revendedor";

// Verificar permissões
require_once '../auth.php';
requireAccess(2); // Nível mínimo: revendedor

// Incluir o cabeçalho
require_once '../templates/header.php';

// Dados do revendedor (em produção, estes dados viriam do banco de dados)
$revendedor_nome = $_SESSION['usuario_nome'] ?? 'Revendedor';
$revendedor_email = $_SESSION['usuario_email'] ?? 'revendedor@exemplo.com';

// Dados de exemplo para o dashboard
$total_vendas = 32;
$vendas_mes = 1250.00;
$comissoes = 187.50;
$clientes = 15;

// Últimas vendas (exemplo)
$ultimas_vendas = [
    ['id' => 1023, 'cliente' => 'João Silva', 'concurso' => 'Mega-Sena 2345', 'data' => '15/05/2023', 'valor' => 'R$ 17,50', 'status' => 'Pago'],
    ['id' => 1022, 'cliente' => 'Maria Souza', 'concurso' => 'Lotofácil 1234', 'data' => '15/05/2023', 'valor' => 'R$ 30,00', 'status' => 'Pago'],
    ['id' => 1021, 'cliente' => 'Pedro Costa', 'concurso' => 'Quina 6789', 'data' => '14/05/2023', 'valor' => 'R$ 10,00', 'status' => 'Pago'],
    ['id' => 1020, 'cliente' => 'Ana Oliveira', 'concurso' => 'Mega-Sena 2345', 'data' => '14/05/2023', 'valor' => 'R$ 4,50', 'status' => 'Pendente'],
    ['id' => 1019, 'cliente' => 'Carlos Santos', 'concurso' => 'Timemania 987', 'data' => '13/05/2023', 'valor' => 'R$ 10,00', 'status' => 'Pago'],
];
?>

<div class="dashboard-wrapper">
    <!-- Boas-vindas e resumo -->
    <div class="welcome-card mb-4">
        <div class="card">
            <div class="card-body">
                <h4 class="welcome-title">Olá, <?php echo $revendedor_nome; ?>!</h4>
                <p class="welcome-text">Bem-vindo ao seu painel de controle de revendedor. Aqui você pode gerenciar suas vendas e acompanhar suas comissões.</p>
            </div>
        </div>
    </div>

    <!-- Cards de estatísticas -->
    <div class="row">
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div>
                            <h5 class="card-title">Total de Vendas</h5>
                            <h2 class="card-value"><?php echo $total_vendas; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center">
                    <span>Ver detalhes</span>
                    <a href="vendas.php" class="ms-auto">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div>
                            <h5 class="card-title">Vendas do Mês</h5>
                            <h2 class="card-value">R$ <?php echo number_format($vendas_mes, 2, ',', '.'); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center">
                    <span>Ver relatório</span>
                    <a href="relatorios.php" class="ms-auto">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div>
                            <h5 class="card-title">Comissões</h5>
                            <h2 class="card-value">R$ <?php echo number_format($comissoes, 2, ',', '.'); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center">
                    <span>Ver comissões</span>
                    <a href="comissoes.php" class="ms-auto">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h5 class="card-title">Clientes</h5>
                            <h2 class="card-value"><?php echo $clientes; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center">
                    <span>Gerenciar clientes</span>
                    <a href="clientes.php" class="ms-auto">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de vendas e Últimas vendas -->
    <div class="row">
        <!-- Gráfico de Vendas -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Vendas Recentes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Concurso</th>
                                    <th>Data</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimas_vendas as $venda): ?>
                                    <tr>
                                        <td>#<?php echo $venda['id']; ?></td>
                                        <td><?php echo $venda['cliente']; ?></td>
                                        <td><?php echo $venda['concurso']; ?></td>
                                        <td><?php echo $venda['data']; ?></td>
                                        <td><?php echo $venda['valor']; ?></td>
                                        <td><span class="badge <?php echo ($venda['status'] == 'Pago') ? 'bg-success' : 'bg-warning'; ?>"><?php echo $venda['status']; ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="vendas.php" class="btn btn-primary btn-sm">Ver todas as vendas</a>
                </div>
            </div>
        </div>

        <!-- Resumo de Comissões -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Resumo de Comissões</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:250px;">
                        <canvas id="comissaoChart"></canvas>
                    </div>
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Comissão do mês atual:</span>
                            <strong>R$ 187,50</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Comissão pendente:</span>
                            <strong>R$ 45,00</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Próximo pagamento:</span>
                            <strong>05/06/2023</strong>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="comissoes.php" class="btn btn-primary btn-sm">Ver detalhes</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Novos concursos e ações rápidas -->
    <div class="row">
        <!-- Novos concursos -->
        <div class="col-lg-7 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Concursos em Andamento</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Concurso</th>
                                    <th>Data Sorteio</th>
                                    <th>Prêmio</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Mega-Sena 2345</td>
                                    <td>17/05/2023</td>
                                    <td>R$ 45.000.000,00</td>
                                    <td><a href="vender.php?concurso=2345" class="btn btn-sm btn-primary">Vender</a></td>
                                </tr>
                                <tr>
                                    <td>Lotofácil 1234</td>
                                    <td>18/05/2023</td>
                                    <td>R$ 1.700.000,00</td>
                                    <td><a href="vender.php?concurso=1234" class="btn btn-sm btn-primary">Vender</a></td>
                                </tr>
                                <tr>
                                    <td>Quina 6789</td>
                                    <td>19/05/2023</td>
                                    <td>R$ 12.500.000,00</td>
                                    <td><a href="vender.php?concurso=6789" class="btn btn-sm btn-primary">Vender</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="concursos.php" class="btn btn-primary btn-sm">Ver todos os concursos</a>
                </div>
            </div>
        </div>

        <!-- Ações rápidas -->
        <div class="col-lg-5 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ações Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        <a href="vender.php" class="quick-action-btn">
                            <div class="action-icon bg-primary">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <span>Nova Venda</span>
                        </a>
                        <a href="cliente-novo.php" class="quick-action-btn">
                            <div class="action-icon bg-success">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <span>Novo Cliente</span>
                        </a>
                        <a href="relatorios.php" class="quick-action-btn">
                            <div class="action-icon bg-info">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <span>Relatórios</span>
                        </a>
                        <a href="material.php" class="quick-action-btn">
                            <div class="action-icon bg-warning">
                                <i class="fas fa-download"></i>
                            </div>
                            <span>Material</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para o gráfico de comissões -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dados para o gráfico
    const comissaoData = {
        labels: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio'],
        datasets: [{
            label: 'Comissões (R$)',
            data: [120, 145, 105, 165, 187.5],
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    };

    // Configuração do gráfico
    const ctx = document.getElementById('comissaoChart').getContext('2d');
    const comissaoChart = new Chart(ctx, {
        type: 'bar',
        data: comissaoData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value;
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>

<?php
// Incluir o rodapé
require_once '../templates/footer.php';
?> 