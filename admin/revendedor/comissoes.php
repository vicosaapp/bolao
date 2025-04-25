<?php
// Definir o título da página
$page_title = "Minhas Comissões";

// Verificar permissões
require_once '../auth.php';
requireAccess(2); // Nível mínimo: revendedor

// Incluir o cabeçalho
require_once '../templates/header.php';

// Incluir modelos necessários
require_once '../models/BilheteModel.php';
require_once '../models/VendedorModel.php';
require_once '../models/ConcursoModel.php';

// Instanciar os modelos
$bilheteModel = new BilheteModel();
$vendedorModel = new VendedorModel();
$concursoModel = new ConcursoModel();

// Recuperar o ID do vendedor da sessão
$vendedor_id = $_SESSION['usuario_id'] ?? 0;

// Parâmetros de paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 10;

// Parâmetros para filtro
$filtros = [
    'vendedor_id' => $vendedor_id,
    'offset' => ($pagina - 1) * $por_pagina,
    'limite' => $por_pagina
];

// Adicionar filtros de data, se fornecidos
if (isset($_GET['data_inicio']) && !empty($_GET['data_inicio'])) {
    $filtros['data_inicio'] = $_GET['data_inicio'];
}

if (isset($_GET['data_fim']) && !empty($_GET['data_fim'])) {
    $filtros['data_fim'] = $_GET['data_fim'];
}

// Adicionar filtro de concurso, se fornecido
if (isset($_GET['concurso_id']) && !empty($_GET['concurso_id'])) {
    $filtros['concurso_id'] = (int)$_GET['concurso_id'];
}

// Adicionar filtro de status, se fornecido
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $filtros['status'] = $_GET['status'];
}

// Obter vendas (bilhetes vendidos pelo revendedor)
try {
    $vendas = $bilheteModel->listarBilhetesComFiltros($filtros);
    $total_vendas = $bilheteModel->contarBilhetesComFiltros(['vendedor_id' => $vendedor_id]);
} catch (Exception $e) {
    $vendas = [];
    $total_vendas = 0;
    $erro_message = "Erro ao carregar vendas: " . $e->getMessage();
}

// Obter informações do vendedor
try {
    $vendedor = $vendedorModel->obterVendedorPorId($vendedor_id);
    $taxa_comissao = $vendedor['taxa_comissao'] ?? 10; // Taxa padrão de 10% se não estiver definida
} catch (Exception $e) {
    $vendedor = [];
    $taxa_comissao = 10;
    $erro_message = "Erro ao carregar informações do vendedor: " . $e->getMessage();
}

// Calcular total de páginas
$total_paginas = ceil($total_vendas / $por_pagina);

// Obter concursos para filtro
try {
    $concursos = $concursoModel->listarConcursosComFiltros(['limite' => 100]);
} catch (Exception $e) {
    $concursos = [];
}

// Calcular estatísticas de comissões
try {
    // Total de comissões do mês atual
    $mes_atual = date('Y-m');
    $inicio_mes = date('Y-m-01');
    $fim_mes = date('Y-m-t');
    
    $comissoes_mes = $bilheteModel->calcularComissoesPorPeriodo($vendedor_id, $inicio_mes, $fim_mes, $taxa_comissao);
    
    // Total de comissões de todos os tempos
    $comissoes_total = $bilheteModel->calcularComissoesTotais($vendedor_id, $taxa_comissao);
    
    // Quantidade de vendas que geraram comissão
    $vendas_com_comissao = $bilheteModel->contarBilhetesComFiltros([
        'vendedor_id' => $vendedor_id,
        'status' => 'pago'
    ]);
    
    $estatisticas = [
        'comissoes_mes' => $comissoes_mes,
        'comissoes_total' => $comissoes_total,
        'vendas_com_comissao' => $vendas_com_comissao
    ];
} catch (Exception $e) {
    $estatisticas = [
        'comissoes_mes' => 0,
        'comissoes_total' => 0,
        'vendas_com_comissao' => 0
    ];
}
?>

<div class="content-header">
    <div class="content-title">
        <h1><?php echo $page_title; ?></h1>
        <p>Acompanhe suas comissões sobre as vendas realizadas</p>
    </div>
</div>

<!-- Cards de estatísticas -->
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Comissões do Mês</h5>
                <div class="d-flex align-items-center">
                    <i class="fas fa-calendar-alt fa-3x me-3"></i>
                    <h2 class="card-value mb-0">R$ <?php echo number_format($estatisticas['comissoes_mes'], 2, ',', '.'); ?></h2>
                </div>
                <small class="text-white-50 mt-2">Referente a <?php echo date('F/Y'); ?></small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Total de Comissões</h5>
                <div class="d-flex align-items-center">
                    <i class="fas fa-money-bill-wave fa-3x me-3"></i>
                    <h2 class="card-value mb-0">R$ <?php echo number_format($estatisticas['comissoes_total'], 2, ',', '.'); ?></h2>
                </div>
                <small class="text-white-50 mt-2">Desde o início</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Vendas com Comissão</h5>
                <div class="d-flex align-items-center">
                    <i class="fas fa-ticket-alt fa-3x me-3"></i>
                    <h2 class="card-value mb-0"><?php echo $estatisticas['vendas_com_comissao']; ?></h2>
                </div>
                <small class="text-white-50 mt-2">Taxa de comissão: <?php echo $taxa_comissao; ?>%</small>
            </div>
        </div>
    </div>
</div>

<!-- Filtros de comissões -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title">Filtrar Comissões</h5>
    </div>
    <div class="card-body">
        <form action="" method="get" class="row g-3">
            <div class="col-md-3">
                <label for="data_inicio" class="form-label">Data Inicial</label>
                <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?php echo $_GET['data_inicio'] ?? ''; ?>">
            </div>
            
            <div class="col-md-3">
                <label for="data_fim" class="form-label">Data Final</label>
                <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?php echo $_GET['data_fim'] ?? ''; ?>">
            </div>
            
            <div class="col-md-3">
                <label for="concurso_id" class="form-label">Concurso</label>
                <select class="form-select" id="concurso_id" name="concurso_id">
                    <option value="">Todos os concursos</option>
                    <?php foreach ($concursos as $concurso): ?>
                        <option value="<?php echo $concurso['id']; ?>" <?php echo (isset($_GET['concurso_id']) && $_GET['concurso_id'] == $concurso['id']) ? 'selected' : ''; ?>>
                            #<?php echo $concurso['numero']; ?> - <?php echo htmlspecialchars($concurso['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos</option>
                    <option value="pago" <?php echo (isset($_GET['status']) && $_GET['status'] == 'pago') ? 'selected' : ''; ?>>Pago</option>
                    <option value="pendente" <?php echo (isset($_GET['status']) && $_GET['status'] == 'pendente') ? 'selected' : ''; ?>>Pendente</option>
                    <option value="cancelado" <?php echo (isset($_GET['status']) && $_GET['status'] == 'cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                    <option value="premiado" <?php echo (isset($_GET['status']) && $_GET['status'] == 'premiado') ? 'selected' : ''; ?>>Premiado</option>
                </select>
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="comissoes.php" class="btn btn-secondary">Limpar Filtros</a>
            </div>
        </form>
    </div>
</div>

<!-- Tabela de comissões -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Detalhamento das Comissões</h5>
    </div>
    <div class="card-body">
        <?php if (isset($erro_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $erro_message; ?>
            </div>
        <?php elseif (empty($vendas)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Nenhuma venda encontrada com os filtros selecionados.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Bilhete</th>
                            <th>Cliente</th>
                            <th>Concurso</th>
                            <th>Data</th>
                            <th>Valor Venda</th>
                            <th>Status</th>
                            <th>Comissão</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_comissoes_filtradas = 0;
                        foreach ($vendas as $venda): 
                            // Calcular comissão
                            $comissao = 0;
                            if ($venda['status'] == 'pago' || $venda['status'] == 'premiado') {
                                $comissao = $venda['valor'] * ($taxa_comissao / 100);
                                $total_comissoes_filtradas += $comissao;
                            }
                        ?>
                            <tr>
                                <td><?php echo $venda['numero']; ?></td>
                                <td>
                                    <?php
                                    // Lógica para exibir o nome do cliente
                                    $nome_cliente = 'Cliente não identificado';
                                    if (!empty($venda['apostador_nome'])) {
                                        $nome_cliente = $venda['apostador_nome'];
                                    }
                                    echo htmlspecialchars($nome_cliente);
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    // Lógica para exibir informações do concurso
                                    $concurso_info = 'Concurso #' . ($venda['concurso_numero'] ?? $venda['concurso_id']);
                                    if (!empty($venda['concurso_nome'])) {
                                        $concurso_info .= ' - ' . $venda['concurso_nome'];
                                    }
                                    echo htmlspecialchars($concurso_info);
                                    ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($venda['data_compra'])); ?></td>
                                <td>R$ <?php echo number_format($venda['valor'], 2, ',', '.'); ?></td>
                                <td>
                                    <?php
                                    // Lógica para exibir badge com status
                                    $status_class = '';
                                    $status_text = $venda['status'];
                                    
                                    switch ($venda['status']) {
                                        case 'pago':
                                            $status_class = 'bg-success';
                                            $status_text = 'Pago';
                                            break;
                                        case 'pendente':
                                            $status_class = 'bg-warning text-dark';
                                            $status_text = 'Pendente';
                                            break;
                                        case 'cancelado':
                                            $status_class = 'bg-danger';
                                            $status_text = 'Cancelado';
                                            break;
                                        case 'premiado':
                                            $status_class = 'bg-info';
                                            $status_text = 'Premiado';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </td>
                                <td>
                                    <?php if ($venda['status'] == 'pago' || $venda['status'] == 'premiado'): ?>
                                        <span class="text-success fw-bold">
                                            R$ <?php echo number_format($comissao, 2, ',', '.'); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-primary">
                            <td colspan="6" class="text-end fw-bold">Total de comissões (nesta página):</td>
                            <td class="fw-bold">R$ <?php echo number_format($total_comissoes_filtradas, 2, ',', '.'); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <!-- Paginação -->
            <?php if ($total_paginas > 1): ?>
                <nav aria-label="Navegação de página" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?pagina=<?php echo $pagina - 1; ?>&data_inicio=<?php echo $_GET['data_inicio'] ?? ''; ?>&data_fim=<?php echo $_GET['data_fim'] ?? ''; ?>&concurso_id=<?php echo $_GET['concurso_id'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>" aria-label="Anterior">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $i; ?>&data_inicio=<?php echo $_GET['data_inicio'] ?? ''; ?>&data_fim=<?php echo $_GET['data_fim'] ?? ''; ?>&concurso_id=<?php echo $_GET['concurso_id'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?pagina=<?php echo $pagina + 1; ?>&data_inicio=<?php echo $_GET['data_inicio'] ?? ''; ?>&data_fim=<?php echo $_GET['data_fim'] ?? ''; ?>&concurso_id=<?php echo $_GET['concurso_id'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>" aria-label="Próximo">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para visualizar detalhes da comissão -->
<div class="modal fade" id="modalDetalhesComissao" tabindex="-1" aria-labelledby="modalDetalhesComissaoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalhesComissaoLabel">Detalhes da Comissão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div id="detalhesComissaoConteudo">
                    <!-- O conteúdo será preenchido via JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts personalizados para a página -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Inicializar datepickers
        const dataInputs = document.querySelectorAll('input[type="date"]');
        if (dataInputs.length > 0) {
            dataInputs.forEach(input => {
                // Pode adicionar lógica adicional para datepickers se necessário
            });
        }
        
        // Calcular período quando mudar as datas
        const dataInicioInput = document.getElementById('data_inicio');
        const dataFimInput = document.getElementById('data_fim');
        
        if (dataInicioInput && dataFimInput) {
            dataInicioInput.addEventListener('change', function() {
                if (!dataFimInput.value) {
                    dataFimInput.value = dataInicioInput.value;
                }
            });
            
            dataFimInput.addEventListener('change', function() {
                if (!dataInicioInput.value) {
                    dataInicioInput.value = dataFimInput.value;
                }
            });
        }
    });
</script>

<?php
// Incluir o rodapé
require_once '../templates/footer.php';
?> 