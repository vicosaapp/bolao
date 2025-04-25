<?php
// Definir o título da página
$page_title = "Gerenciamento de Comissões";

// CSS específico para esta página
$extraCSS = [
    '../assets/css/comissoes.css'
];

// Verificar permissões
require_once '../auth.php';
requireAccess(3); // Nível mínimo: administrador (3)

// Incluir modelos necessários
require_once '../models/ComissaoModel.php';
require_once '../models/VendedorModel.php';

// Incluir o cabeçalho
require_once '../templates/header.php';

// Inicializar modelos
$comissaoModel = new ComissaoModel();
$vendedorModel = new VendedorModel();

// Obter filtros da URL
$filtros = [
    'vendedor_id' => $_GET['vendedor_id'] ?? null,
    'status' => $_GET['status'] ?? null,
    'data_inicio' => $_GET['data_inicio'] ?? null,
    'data_fim' => $_GET['data_fim'] ?? null,
    'concurso_id' => $_GET['concurso_id'] ?? null
];

// Buscar comissões com filtros
$comissoes = $comissaoModel->listarComissoes($filtros);

// Obter resumo das comissões
$resumo = $comissaoModel->obterResumoComissoes($filtros);

// Obter top vendedores
$top_vendedores = $comissaoModel->obterTopVendedores(5, $filtros);

// Obter lista de vendedores para o filtro
$vendedores = $vendedorModel->listarVendedores();
?>

<div class="content">
    <div class="content-header">
        <h1>Gerenciamento de Comissões</h1>
        <p class="text-muted">Gerencie as comissões dos vendedores, visualize estatísticas e efetue pagamentos.</p>
    </div>
    
    <!-- Cards de estatísticas -->
    <div class="dashboard-stats">
        <div class="stat-card primary">
            <div class="stat-card-header">
                <h5 class="stat-card-title">Total de Comissões</h5>
                <div class="stat-card-icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
            </div>
            <h2 class="stat-card-value">R$ <?php echo number_format($resumo['total_comissoes'], 2, ',', '.'); ?></h2>
            <p class="stat-card-description">
                <i class="fas fa-info-circle"></i> 
                <span>Em <?php echo number_format($resumo['total_registros'], 0, ',', '.'); ?> registros</span>
            </p>
        </div>
        
        <div class="stat-card success">
            <div class="stat-card-header">
                <h5 class="stat-card-title">Comissões Pagas</h5>
                <div class="stat-card-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <h2 class="stat-card-value">R$ <?php echo number_format($resumo['comissoes_pagas'], 2, ',', '.'); ?></h2>
            <p class="stat-card-description">
                <i class="fas fa-check"></i> 
                <span>Pagamentos já efetuados</span>
            </p>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-card-header">
                <h5 class="stat-card-title">Comissões Pendentes</h5>
                <div class="stat-card-icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <h2 class="stat-card-value">R$ <?php echo number_format($resumo['comissoes_pendentes'], 2, ',', '.'); ?></h2>
            <p class="stat-card-description">
                <i class="fas fa-exclamation-circle"></i> 
                <span>Aguardando pagamento</span>
            </p>
            <button class="mt-3 btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#pagarComissoesModal" <?php echo $resumo['comissoes_pendentes'] > 0 ? '' : 'disabled'; ?>>
                Efetuar Pagamentos <i class="fas fa-money-bill-wave"></i>
            </button>
        </div>
        
        <div class="stat-card info">
            <div class="stat-card-header">
                <h5 class="stat-card-title">Percentual Médio</h5>
                <div class="stat-card-icon">
                    <i class="fas fa-percentage"></i>
                </div>
            </div>
            <h2 class="stat-card-value"><?php echo number_format($resumo['media_percentual'], 2, ',', '.'); ?>%</h2>
            <p class="stat-card-description">
                <i class="fas fa-chart-line"></i> 
                <span>Média de comissão</span>
            </p>
        </div>
    </div>
    
    <!-- Top Vendedores -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-0">Top Vendedores</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nome</th>
                                    <th>Total de Vendas</th>
                                    <th>Valor Total</th>
                                    <th>Comissões</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($top_vendedores as $vendedor): ?>
                                <tr>
                                    <td><?php echo $vendedor['codigo']; ?></td>
                                    <td><?php echo $vendedor['nome']; ?></td>
                                    <td><?php echo number_format($vendedor['total_vendas'], 0, ',', '.'); ?></td>
                                    <td>R$ <?php echo number_format($vendedor['valor_total_vendas'], 2, ',', '.'); ?></td>
                                    <td>R$ <?php echo number_format($vendedor['valor_total_comissoes'], 2, ',', '.'); ?></td>
                                    <td>
                                        <a href="?vendedor_id=<?php echo $vendedor['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-filter"></i> Filtrar
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($top_vendedores)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-3">
                                        <p class="text-muted mb-0">Nenhum vendedor com comissões no período.</p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filtros e Tabela de Comissões -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">Comissões</h5>
                    <div class="card-actions">
                        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFiltros">
                            <i class="fas fa-filter"></i> Filtros
                        </button>
                    </div>
                </div>
                
                <!-- Filtros Colapsáveis -->
                <div class="collapse" id="collapseFiltros">
                    <div class="card-body border-bottom">
                        <form action="" method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Vendedor</label>
                                <select class="form-select" name="vendedor_id">
                                    <option value="">Todos os vendedores</option>
                                    <?php foreach($vendedores as $vendedor): ?>
                                    <option value="<?php echo $vendedor['id']; ?>" <?php echo isset($_GET['vendedor_id']) && $_GET['vendedor_id'] == $vendedor['id'] ? 'selected' : ''; ?>>
                                        <?php echo $vendedor['codigo'] . ' - ' . $vendedor['nome']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="">Todos os status</option>
                                    <option value="pendente" <?php echo isset($_GET['status']) && $_GET['status'] == 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                    <option value="pago" <?php echo isset($_GET['status']) && $_GET['status'] == 'pago' ? 'selected' : ''; ?>>Pago</option>
                                    <option value="cancelado" <?php echo isset($_GET['status']) && $_GET['status'] == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Data Início</label>
                                <input type="date" class="form-control" name="data_inicio" value="<?php echo $_GET['data_inicio'] ?? ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Data Fim</label>
                                <input type="date" class="form-control" name="data_fim" value="<?php echo $_GET['data_fim'] ?? ''; ?>">
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <a href="comissoes.php" class="btn btn-secondary me-2">Limpar</a>
                                <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="checkAll">
                                        </div>
                                    </th>
                                    <th>ID</th>
                                    <th>Vendedor</th>
                                    <th>Bilhete</th>
                                    <th>Valor Venda</th>
                                    <th>Percentual</th>
                                    <th>Comissão</th>
                                    <th>Status</th>
                                    <th>Data Venda</th>
                                    <th>Data Pagamento</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tabelaComissoes">
                                <?php foreach($comissoes as $comissao): ?>
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input comissao-check" type="checkbox" 
                                                   value="<?php echo $comissao['id']; ?>" 
                                                   <?php echo $comissao['status'] !== 'pendente' ? 'disabled' : ''; ?>>
                                        </div>
                                    </td>
                                    <td><?php echo $comissao['id']; ?></td>
                                    <td><?php echo $comissao['vendedor_codigo'] . ' - ' . $comissao['vendedor_nome']; ?></td>
                                    <td><?php echo $comissao['bilhete_numero']; ?></td>
                                    <td>R$ <?php echo number_format($comissao['valor_venda'], 2, ',', '.'); ?></td>
                                    <td><?php echo number_format($comissao['percentual'], 2, ',', '.'); ?>%</td>
                                    <td>R$ <?php echo number_format($comissao['valor_comissao'], 2, ',', '.'); ?></td>
                                    <td>
                                        <span class="badge <?php 
                                            if ($comissao['status'] == 'pendente') echo 'bg-warning';
                                            elseif ($comissao['status'] == 'pago') echo 'bg-success';
                                            else echo 'bg-danger';
                                        ?>">
                                            <?php echo ucfirst($comissao['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($comissao['data_venda'])); ?></td>
                                    <td>
                                        <?php 
                                        if ($comissao['data_pagamento']) {
                                            echo date('d/m/Y H:i', strtotime($comissao['data_pagamento']));
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-primary btn-detalhes" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#detalhesComissaoModal" 
                                                    data-id="<?php echo $comissao['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($comissao['status'] == 'pendente'): ?>
                                            <button class="btn btn-sm btn-success btn-pagar-unica" 
                                                    data-id="<?php echo $comissao['id']; ?>"
                                                    data-valor="<?php echo number_format($comissao['valor_comissao'], 2, ',', '.'); ?>"
                                                    data-vendedor="<?php echo htmlspecialchars($comissao['vendedor_nome']); ?>">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </button>
                                            <?php endif; ?>
                                            <?php if ($comissao['status'] != 'cancelado'): ?>
                                            <button class="btn btn-sm btn-danger btn-cancelar" 
                                                    data-id="<?php echo $comissao['id']; ?>"
                                                    data-valor="<?php echo number_format($comissao['valor_comissao'], 2, ',', '.'); ?>"
                                                    data-vendedor="<?php echo htmlspecialchars($comissao['vendedor_nome']); ?>">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($comissoes)): ?>
                                <tr>
                                    <td colspan="11" class="text-center py-3">
                                        <p class="text-muted mb-0">Nenhuma comissão encontrada.</p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <div>
                        <button id="btnPagarSelecionadas" class="btn btn-success btn-sm" disabled>
                            <i class="fas fa-money-bill-wave"></i> Pagar Selecionadas
                        </button>
                    </div>
                    <span>Mostrando <?php echo count($comissoes); ?> comissões</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalhes da Comissão -->
<div class="modal fade" id="detalhesComissaoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes da Comissão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detalhesComissaoConteudo">
                    <p class="text-center">
                        <i class="fas fa-spinner fa-spin"></i> Carregando detalhes...
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pagar Comissões -->
<div class="modal fade" id="pagarComissoesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Efetuar Pagamento de Comissões</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formPagarComissoes">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Selecionar vendedor</label>
                            <select class="form-select" id="vendedorPagamento">
                                <option value="">Todos os vendedores</option>
                                <?php foreach($vendedores as $vendedor): ?>
                                <option value="<?php echo $vendedor['id']; ?>">
                                    <?php echo $vendedor['codigo'] . ' - ' . $vendedor['nome']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data de pagamento</label>
                            <input type="datetime-local" class="form-control" id="dataPagamento" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Resumo</h6>
                        <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                            <span>Comissões pendentes:</span>
                            <span id="resumoPendentes"><?php echo count(array_filter($comissoes, function($c) { return $c['status'] == 'pendente'; })); ?></span>
                        </div>
                        <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                            <span>Valor total a pagar:</span>
                            <span id="resumoValorTotal">R$ <?php echo number_format($resumo['comissoes_pendentes'], 2, ',', '.'); ?></span>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Atenção: Esta ação marcará todas as comissões pendentes como pagas. Confirme os valores antes de prosseguir.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmarPagamentoComissoes">
                    <i class="fas fa-money-bill-wave"></i> Confirmar Pagamento
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pagar Comissão Única -->
<div class="modal fade" id="pagarComissaoUnicaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pagar Comissão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formPagarComissaoUnica">
                    <input type="hidden" id="comissaoId">
                    <p>Confirmar pagamento da comissão para <strong id="vendedorNome"></strong> no valor de <strong id="valorComissao"></strong>?</p>
                    
                    <div class="mb-3">
                        <label class="form-label">Data de pagamento</label>
                        <input type="datetime-local" class="form-control" id="dataPagamentoUnica" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmarPagamentoUnico">
                    <i class="fas fa-check"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cancelar Comissão -->
<div class="modal fade" id="cancelarComissaoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancelar Comissão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCancelarComissao">
                    <input type="hidden" id="comissaoCancelarId">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> Atenção: Você está prestes a cancelar a comissão para <strong id="vendedorCancelarNome"></strong> no valor de <strong id="valorCancelarComissao"></strong>.
                    </div>
                    <p>Esta ação não pode ser desfeita. Deseja continuar?</p>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Desistir</button>
                <button type="button" class="btn btn-danger" id="confirmarCancelamento">
                    <i class="fas fa-ban"></i> Cancelar Comissão
                </button>
            </div>
        </div>
    </div>
</div>

<?php
// Scripts específicos para a página
$page_scripts = [
    '../assets/js/comissoes.js'
];

// Incluir o rodapé
require_once '../templates/footer.php';
?> 