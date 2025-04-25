<?php
// Definir o título da página
$page_title = "Relatórios";

// CSS específico para esta página
$extraCSS = [
    '../assets/css/relatorios.css'
];

// Verificar permissões
require_once '../auth.php';
requireAccess(3); // Nível mínimo: administrador (3)

// Incluir modelos necessários
require_once '../models/VendedorModel.php';
require_once '../models/ComissaoModel.php';
require_once '../models/BilheteModel.php';

// Incluir o cabeçalho
require_once '../templates/header.php';

// Inicializar modelos
$vendedorModel = new VendedorModel();
$comissaoModel = new ComissaoModel();
$bilheteModel = new BilheteModel();

// Obter dados para os relatórios
$vendedores = $vendedorModel->listarVendedores();
$resumoComissoes = $comissaoModel->obterResumoComissoes();
$topVendedores = $comissaoModel->obterTopVendedores(5);
?>

<div class="content">
    <div class="content-header">
        <h1>Relatórios</h1>
        <p class="text-muted">Visualize relatórios de desempenho, vendas e comissões.</p>
    </div>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">Filtros</h5>
                    <div class="card-actions">
                        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFiltros">
                            <i class="fas fa-filter"></i> Opções de Filtro
                        </button>
                    </div>
                </div>
                
                <!-- Filtros Colapsáveis -->
                <div class="collapse show" id="collapseFiltros">
                    <div class="card-body">
                        <form action="" method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Tipo de Relatório</label>
                                <select class="form-select" name="tipo_relatorio" id="tipoRelatorio">
                                    <option value="vendas" <?php echo ($_GET['tipo_relatorio'] ?? '') == 'vendas' ? 'selected' : ''; ?>>Vendas</option>
                                    <option value="comissoes" <?php echo ($_GET['tipo_relatorio'] ?? '') == 'comissoes' ? 'selected' : ''; ?>>Comissões</option>
                                    <option value="resultados" <?php echo ($_GET['tipo_relatorio'] ?? '') == 'resultados' ? 'selected' : ''; ?>>Resultados</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Período</label>
                                <select class="form-select" name="periodo" id="periodoRelatorio">
                                    <option value="hoje" <?php echo ($_GET['periodo'] ?? '') == 'hoje' ? 'selected' : ''; ?>>Hoje</option>
                                    <option value="ontem" <?php echo ($_GET['periodo'] ?? '') == 'ontem' ? 'selected' : ''; ?>>Ontem</option>
                                    <option value="7dias" <?php echo ($_GET['periodo'] ?? '') == '7dias' ? 'selected' : ''; ?>>Últimos 7 dias</option>
                                    <option value="30dias" <?php echo ($_GET['periodo'] ?? '') == '30dias' ? 'selected' : ''; ?>>Últimos 30 dias</option>
                                    <option value="mes" <?php echo ($_GET['periodo'] ?? '') == 'mes' ? 'selected' : ''; ?>>Este mês</option>
                                    <option value="mesanterior" <?php echo ($_GET['periodo'] ?? '') == 'mesanterior' ? 'selected' : ''; ?>>Mês anterior</option>
                                    <option value="personalizado" <?php echo ($_GET['periodo'] ?? '') == 'personalizado' ? 'selected' : ''; ?>>Personalizado</option>
                                </select>
                            </div>
                            <div class="col-md-3 periodo-personalizado" style="display: none;">
                                <label class="form-label">Data Início</label>
                                <input type="date" class="form-control" name="data_inicio" value="<?php echo $_GET['data_inicio'] ?? date('Y-m-d', strtotime('-30 days')); ?>">
                            </div>
                            <div class="col-md-3 periodo-personalizado" style="display: none;">
                                <label class="form-label">Data Fim</label>
                                <input type="date" class="form-control" name="data_fim" value="<?php echo $_GET['data_fim'] ?? date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Vendedor</label>
                                <select class="form-select" name="vendedor_id">
                                    <option value="">Todos os vendedores</option>
                                    <?php foreach($vendedores as $vendedor): ?>
                                    <option value="<?php echo $vendedor['id']; ?>" <?php echo ($_GET['vendedor_id'] ?? '') == $vendedor['id'] ? 'selected' : ''; ?>>
                                        <?php echo $vendedor['codigo'] . ' - ' . $vendedor['nome']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 d-flex justify-content-end mt-4">
                                <a href="relatorios.php" class="btn btn-secondary me-2">Limpar</a>
                                <button type="submit" class="btn btn-primary">Gerar Relatório</button>
                                <button type="button" id="btnExportar" class="btn btn-success ms-2">
                                    <i class="fas fa-file-export"></i> Exportar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Área do relatório -->
    <div class="relatorio-area">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="m-0" id="tituloRelatorio">Relatório de Vendas</h5>
                        <div class="card-actions">
                            <span class="badge bg-primary" id="periodoExibicao">Período: Últimos 30 dias</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Gráfico principal -->
                        <div class="grafico-container mb-4">
                            <canvas id="graficoRelatorio"></canvas>
                        </div>
                        
                        <!-- Cards de resumo -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="stat-card primary">
                                    <div class="stat-card-header">
                                        <h5 class="stat-card-title">Total de Vendas</h5>
                                        <div class="stat-card-icon">
                                            <i class="fas fa-shopping-cart"></i>
                                        </div>
                                    </div>
                                    <h2 class="stat-card-value" id="totalVendas">R$ 0,00</h2>
                                    <p class="stat-card-description">
                                        <i class="fas fa-calendar-alt"></i> 
                                        <span>No período selecionado</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card success">
                                    <div class="stat-card-header">
                                        <h5 class="stat-card-title">Total de Comissões</h5>
                                        <div class="stat-card-icon">
                                            <i class="fas fa-hand-holding-usd"></i>
                                        </div>
                                    </div>
                                    <h2 class="stat-card-value" id="totalComissoes">R$ 0,00</h2>
                                    <p class="stat-card-description">
                                        <i class="fas fa-percentage"></i> 
                                        <span>Média: <span id="percentualMedio">0,00%</span></span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card warning">
                                    <div class="stat-card-header">
                                        <h5 class="stat-card-title">Bilhetes Vendidos</h5>
                                        <div class="stat-card-icon">
                                            <i class="fas fa-ticket-alt"></i>
                                        </div>
                                    </div>
                                    <h2 class="stat-card-value" id="totalBilhetes">0</h2>
                                    <p class="stat-card-description">
                                        <i class="fas fa-chart-line"></i> 
                                        <span>Valor médio: <span id="valorMedioBilhete">R$ 0,00</span></span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card info">
                                    <div class="stat-card-header">
                                        <h5 class="stat-card-title">Vendedores Ativos</h5>
                                        <div class="stat-card-icon">
                                            <i class="fas fa-users"></i>
                                        </div>
                                    </div>
                                    <h2 class="stat-card-value" id="totalVendedores">0</h2>
                                    <p class="stat-card-description">
                                        <i class="fas fa-star"></i> 
                                        <span>Top: <span id="vendedorDestaque">-</span></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tabela de dados detalhados -->
                        <div class="tabela-relatorio">
                            <h6 class="mb-3">Detalhamento</h6>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped" id="tabelaRelatorio">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Data</th>
                                            <th>Vendedor</th>
                                            <th>Valor</th>
                                            <th>Comissão</th>
                                            <th>Status</th>
                                            <th>Detalhes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Dados serão preenchidos via JavaScript -->
                                        <tr>
                                            <td colspan="7" class="text-center py-3">
                                                <p class="text-muted mb-0">Selecione os filtros e clique em "Gerar Relatório"</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Exportação -->
<div class="modal fade" id="modalExportar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Exportar Relatório</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formExportar">
                    <div class="mb-3">
                        <label class="form-label">Formato de Exportação</label>
                        <select class="form-select" name="formato" id="formatoExportacao">
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="pdf">PDF</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Conteúdo</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="incluirGrafico" checked>
                            <label class="form-check-label" for="incluirGrafico">
                                Incluir gráfico
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="incluirResumo" checked>
                            <label class="form-check-label" for="incluirResumo">
                                Incluir cards de resumo
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="incluirDetalhamento" checked>
                            <label class="form-check-label" for="incluirDetalhamento">
                                Incluir tabela detalhada
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarExportacao">
                    <i class="fas fa-download"></i> Baixar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts específicos para a página -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php
// Scripts específicos para a página
$page_scripts = [
    '../assets/js/relatorios.js'
];

// Incluir o rodapé
require_once '../templates/footer.php';
?> 