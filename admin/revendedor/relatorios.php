<?php
// Definir o título da página
$page_title = "Relatórios";

// Verificar permissões
require_once '../auth.php';
requireAccess(2); // Nível mínimo: revendedor

// Incluir modelos necessários
require_once '../models/BilheteModel.php';
require_once '../models/VendedorModel.php';
require_once '../models/ConcursoModel.php';
require_once '../models/ComissaoModel.php';

// Inicializar modelos
$bilheteModel = new BilheteModel();
$vendedorModel = new VendedorModel();
$concursoModel = new ConcursoModel();

// Recuperar o ID do vendedor da sessão
$vendedor_id = $_SESSION['usuario_id'] ?? 0;

// Obter informações do vendedor
try {
    $vendedor = $vendedorModel->obterVendedorPorId($vendedor_id);
    $taxa_comissao = $vendedor['taxa_comissao'] ?? 10; // Taxa padrão de 10% se não estiver definida
} catch (Exception $e) {
    $vendedor = [];
    $taxa_comissao = 10;
    $erro_message = "Erro ao carregar informações do vendedor: " . $e->getMessage();
}

// Definir período para os relatórios
$data_inicio = $_GET['data_inicio'] ?? date('Y-m-01'); // Primeiro dia do mês atual
$data_fim = $_GET['data_fim'] ?? date('Y-m-t'); // Último dia do mês atual
$periodo = $_GET['periodo'] ?? 'mes'; // Período padrão: mês atual

// Ajustar datas com base no período selecionado
if ($periodo == 'semana') {
    $data_inicio = date('Y-m-d', strtotime('monday this week'));
    $data_fim = date('Y-m-d', strtotime('sunday this week'));
} elseif ($periodo == 'mes') {
    $data_inicio = date('Y-m-01');
    $data_fim = date('Y-m-t');
} elseif ($periodo == 'trimestre') {
    $mes_atual = date('n');
    $trimestre_atual = ceil($mes_atual / 3);
    $mes_inicio_trimestre = (($trimestre_atual - 1) * 3) + 1;
    $data_inicio = date('Y-' . sprintf('%02d', $mes_inicio_trimestre) . '-01');
    $data_fim = date('Y-m-t', strtotime($data_inicio . ' +2 months'));
} elseif ($periodo == 'ano') {
    $data_inicio = date('Y-01-01');
    $data_fim = date('Y-12-31');
} elseif ($periodo == 'personalizado') {
    // Usar as datas fornecidas pelo usuário
    $data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
    $data_fim = $_GET['data_fim'] ?? date('Y-m-t');
}

// Obter concursos ativos para filtro
try {
    $concursos = $concursoModel->listarConcursosComFiltros(['limite' => 100]);
    $concurso_id = $_GET['concurso_id'] ?? '';
} catch (Exception $e) {
    $concursos = [];
    $concurso_id = '';
}

// Relatório de vendas por período
try {
    $filtros_vendas = [
        'vendedor_id' => $vendedor_id,
        'data_inicio' => $data_inicio,
        'data_fim' => $data_fim
    ];
    
    if (!empty($concurso_id)) {
        $filtros_vendas['concurso_id'] = $concurso_id;
    }
    
    $vendas = $bilheteModel->listarBilhetesComFiltros($filtros_vendas);
    
    // Agrupar vendas por dia para gráfico
    $vendas_por_dia = [];
    $comissoes_por_dia = [];
    
    foreach ($vendas as $venda) {
        $data_venda = date('Y-m-d', strtotime($venda['data_compra']));
        
        if (!isset($vendas_por_dia[$data_venda])) {
            $vendas_por_dia[$data_venda] = 0;
            $comissoes_por_dia[$data_venda] = 0;
        }
        
        $vendas_por_dia[$data_venda] += $venda['valor'];
        
        // Calcular comissão se o status for pago ou premiado
        if ($venda['status'] == 'pago' || $venda['status'] == 'premiado') {
            $comissoes_por_dia[$data_venda] += $venda['valor'] * ($taxa_comissao / 100);
        }
    }
    
    // Ordenar por data
    ksort($vendas_por_dia);
    ksort($comissoes_por_dia);
    
    // Calcular estatísticas
    $total_vendas = array_sum($vendas_por_dia);
    $total_comissoes = array_sum($comissoes_por_dia);
    $total_bilhetes = count($vendas);
    $media_valor_bilhete = $total_vendas > 0 ? $total_vendas / $total_bilhetes : 0;
    
    // Converter para formato de gráfico
    $labels_vendas = array_keys($vendas_por_dia);
    $valores_vendas = array_values($vendas_por_dia);
    $valores_comissoes = array_values($comissoes_por_dia);
    
    // Agrupar vendas por concurso
    $vendas_por_concurso = [];
    $comissoes_por_concurso = [];
    
    foreach ($vendas as $venda) {
        $concurso_nome = $venda['concurso_nome'] ?? 'Desconhecido';
        
        if (!isset($vendas_por_concurso[$concurso_nome])) {
            $vendas_por_concurso[$concurso_nome] = 0;
            $comissoes_por_concurso[$concurso_nome] = 0;
        }
        
        $vendas_por_concurso[$concurso_nome] += $venda['valor'];
        
        // Calcular comissão se o status for pago ou premiado
        if ($venda['status'] == 'pago' || $venda['status'] == 'premiado') {
            $comissoes_por_concurso[$concurso_nome] += $venda['valor'] * ($taxa_comissao / 100);
        }
    }
    
    // Ordenar por valor (decrescente)
    arsort($vendas_por_concurso);
    
    // Converter para formato de gráfico
    $labels_concursos = array_keys($vendas_por_concurso);
    $valores_vendas_concursos = array_values($vendas_por_concurso);
    $valores_comissoes_concursos = array_map(function($concurso) use ($comissoes_por_concurso) {
        return $comissoes_por_concurso[$concurso] ?? 0;
    }, $labels_concursos);
    
} catch (Exception $e) {
    $erro_message = "Erro ao gerar relatório de vendas: " . $e->getMessage();
    $vendas = [];
    $total_vendas = 0;
    $total_comissoes = 0;
    $total_bilhetes = 0;
    $media_valor_bilhete = 0;
}

// Incluir o cabeçalho
require_once '../templates/header.php';
?>

<div class="content-header">
    <div class="content-title">
        <h1><?php echo $page_title; ?></h1>
        <p>Analise seu desempenho com gráficos e estatísticas detalhadas</p>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title">Filtrar Relatórios</h5>
    </div>
    <div class="card-body">
        <form action="" method="get" class="row g-3">
            <div class="col-md-3">
                <label for="periodo" class="form-label">Período</label>
                <select class="form-select" id="periodo" name="periodo" onchange="ajustarPeriodo(this.value)">
                    <option value="semana" <?php echo $periodo == 'semana' ? 'selected' : ''; ?>>Esta semana</option>
                    <option value="mes" <?php echo $periodo == 'mes' ? 'selected' : ''; ?>>Este mês</option>
                    <option value="trimestre" <?php echo $periodo == 'trimestre' ? 'selected' : ''; ?>>Este trimestre</option>
                    <option value="ano" <?php echo $periodo == 'ano' ? 'selected' : ''; ?>>Este ano</option>
                    <option value="personalizado" <?php echo $periodo == 'personalizado' ? 'selected' : ''; ?>>Personalizado</option>
                </select>
            </div>
            
            <div class="col-md-3 periodo-personalizado" style="<?php echo $periodo != 'personalizado' ? 'display: none;' : ''; ?>">
                <label for="data_inicio" class="form-label">Data Inicial</label>
                <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?php echo $data_inicio; ?>">
            </div>
            
            <div class="col-md-3 periodo-personalizado" style="<?php echo $periodo != 'personalizado' ? 'display: none;' : ''; ?>">
                <label for="data_fim" class="form-label">Data Final</label>
                <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?php echo $data_fim; ?>">
            </div>
            
            <div class="col-md-3">
                <label for="concurso_id" class="form-label">Concurso (Opcional)</label>
                <select class="form-select" id="concurso_id" name="concurso_id">
                    <option value="">Todos os concursos</option>
                    <?php foreach ($concursos as $concurso): ?>
                        <option value="<?php echo $concurso['id']; ?>" <?php echo $concurso_id == $concurso['id'] ? 'selected' : ''; ?>>
                            #<?php echo $concurso['numero']; ?> - <?php echo htmlspecialchars($concurso['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Gerar Relatório</button>
                <a href="relatorios.php" class="btn btn-secondary">Limpar Filtros</a>
                <button type="button" class="btn btn-success" onclick="exportarRelatorio()">
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </button>
                <button type="button" class="btn btn-danger" onclick="exportarPDF()">
                    <i class="fas fa-file-pdf"></i> Exportar PDF
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (isset($erro_message)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $erro_message; ?>
    </div>
<?php else: ?>
    <!-- Cards de Estatísticas -->
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total de Vendas</h5>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-shopping-cart fa-3x me-3"></i>
                        <h2 class="card-value mb-0">R$ <?php echo number_format($total_vendas, 2, ',', '.'); ?></h2>
                    </div>
                    <small class="text-white-50 mt-2">Período: <?php echo date('d/m/Y', strtotime($data_inicio)); ?> - <?php echo date('d/m/Y', strtotime($data_fim)); ?></small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total de Comissões</h5>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-money-bill-wave fa-3x me-3"></i>
                        <h2 class="card-value mb-0">R$ <?php echo number_format($total_comissoes, 2, ',', '.'); ?></h2>
                    </div>
                    <small class="text-white-50 mt-2">Taxa: <?php echo $taxa_comissao; ?>%</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Bilhetes Vendidos</h5>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-ticket-alt fa-3x me-3"></i>
                        <h2 class="card-value mb-0"><?php echo $total_bilhetes; ?></h2>
                    </div>
                    <small class="text-white-50 mt-2">No período selecionado</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Valor Médio</h5>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calculator fa-3x me-3"></i>
                        <h2 class="card-value mb-0">R$ <?php echo number_format($media_valor_bilhete, 2, ',', '.'); ?></h2>
                    </div>
                    <small class="text-dark-50 mt-2">Por bilhete</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row">
        <!-- Gráfico de Vendas por Dia -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title">Vendas e Comissões por Dia</h5>
                </div>
                <div class="card-body">
                    <canvas id="vendasPorDiaChart" height="250"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Gráfico de Vendas por Concurso -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title">Vendas por Concurso</h5>
                </div>
                <div class="card-body">
                    <canvas id="vendasPorConcursoChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Vendas -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Detalhamento de Vendas</h5>
        </div>
        <div class="card-body">
            <?php if (empty($vendas)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Nenhuma venda encontrada no período selecionado.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Concurso</th>
                                <th>Data</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Comissão</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vendas as $venda): ?>
                                <?php 
                                    $comissao = 0;
                                    if ($venda['status'] == 'pago' || $venda['status'] == 'premiado') {
                                        $comissao = $venda['valor'] * ($taxa_comissao / 100);
                                    }
                                    
                                    $status_class = '';
                                    switch ($venda['status']) {
                                        case 'pago':
                                            $status_class = 'bg-success';
                                            break;
                                        case 'pendente':
                                            $status_class = 'bg-warning';
                                            break;
                                        case 'cancelado':
                                            $status_class = 'bg-danger';
                                            break;
                                        case 'premiado':
                                            $status_class = 'bg-info';
                                            break;
                                    }
                                ?>
                                <tr>
                                    <td><?php echo $venda['id']; ?></td>
                                    <td><?php echo htmlspecialchars($venda['usuario_nome']); ?></td>
                                    <td><?php echo htmlspecialchars($venda['concurso_nome']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($venda['data_compra'])); ?></td>
                                    <td>R$ <?php echo number_format($venda['valor'], 2, ',', '.'); ?></td>
                                    <td><span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($venda['status']); ?></span></td>
                                    <td>R$ <?php echo number_format($comissao, 2, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-dark">
                                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                <td><strong>R$ <?php echo number_format($total_vendas, 2, ',', '.'); ?></strong></td>
                                <td></td>
                                <td><strong>R$ <?php echo number_format($total_comissoes, 2, ',', '.'); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<script>
    // Função para ajustar o período
    function ajustarPeriodo(periodo) {
        const camposPersonalizados = document.querySelectorAll('.periodo-personalizado');
        
        if (periodo === 'personalizado') {
            camposPersonalizados.forEach(campo => campo.style.display = 'block');
        } else {
            camposPersonalizados.forEach(campo => campo.style.display = 'none');
        }
    }
    
    // Função para exportar relatório (simulada)
    function exportarRelatorio() {
        alert('Funcionalidade de exportação para Excel será implementada em breve!');
    }
    
    // Função para exportar PDF (simulada)
    function exportarPDF() {
        alert('Funcionalidade de exportação para PDF será implementada em breve!');
    }
    
    // Configurar gráficos
    document.addEventListener('DOMContentLoaded', function() {
        // Configurar Chart.js
        Chart.defaults.font.family = 'Poppins, sans-serif';
        Chart.defaults.font.size = 12;
        
        // Gráfico de Vendas por Dia
        const ctxVendasDia = document.getElementById('vendasPorDiaChart').getContext('2d');
        const charVendasDia = new Chart(ctxVendasDia, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_map(function($data) { return date('d/m', strtotime($data)); }, $labels_vendas ?? [])); ?>,
                datasets: [
                    {
                        label: 'Vendas (R$)',
                        data: <?php echo json_encode($valores_vendas ?? []); ?>,
                        borderColor: 'rgba(0, 123, 255, 1)',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Comissões (R$)',
                        data: <?php echo json_encode($valores_comissoes ?? []); ?>,
                        borderColor: 'rgba(40, 167, 69, 1)',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += 'R$ ' + context.parsed.y.toFixed(2).replace('.', ',');
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toFixed(2).replace('.', ',');
                            }
                        }
                    }
                }
            }
        });
        
        // Gráfico de Vendas por Concurso
        const ctxVendasConcurso = document.getElementById('vendasPorConcursoChart').getContext('2d');
        const chartVendasConcurso = new Chart(ctxVendasConcurso, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels_concursos ?? []); ?>,
                datasets: [
                    {
                        label: 'Vendas (R$)',
                        data: <?php echo json_encode($valores_vendas_concursos ?? []); ?>,
                        backgroundColor: 'rgba(0, 123, 255, 0.7)',
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Comissões (R$)',
                        data: <?php echo json_encode($valores_comissoes_concursos ?? []); ?>,
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += 'R$ ' + context.parsed.y.toFixed(2).replace('.', ',');
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toFixed(2).replace('.', ',');
                            }
                        }
                    }
                }
            }
        });
    });
</script>

<?php
require_once '../templates/footer.php';
?> 