<?php
// Definir o título da página
$page_title = "Gerenciar Vendas";

// Verificar permissões
require_once '../auth.php';
requireAccess(2); // Nível mínimo: revendedor

// Incluir o cabeçalho
require_once '../templates/header.php';

// Incluir modelos necessários
require_once '../models/BilheteModel.php';
require_once '../models/ConcursoModel.php';
require_once '../models/VendedorModel.php';

// Instanciar os modelos
$bilheteModel = new BilheteModel();
$concursoModel = new ConcursoModel();
$vendedorModel = new VendedorModel();

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
$vendas = $bilheteModel->listarBilhetesComFiltros($filtros);
$total_vendas = $bilheteModel->contarBilhetesComFiltros(['vendedor_id' => $vendedor_id]);

// Obter concursos para filtro
$concursos_ativos = $concursoModel->listarConcursosComFiltros(['status' => 'em_andamento']);

// Calcular total de páginas
$total_paginas = ceil($total_vendas / $por_pagina);

// Obter estatísticas de vendas
$estatisticas = [
    'total_vendas' => $total_vendas,
    'valor_total' => $bilheteModel->calcularValorTotalPorVendedor($vendedor_id),
    'bilhetes_premiados' => $bilheteModel->contarBilhetesComFiltros([
        'vendedor_id' => $vendedor_id,
        'status' => 'premiado'
    ])
];

// Obter informações do vendedor
$vendedor = $vendedorModel->obterVendedorPorId($vendedor_id);
?>

<div class="content-header">
    <div class="content-title">
        <h1><?php echo $page_title; ?></h1>
        <p>Gerencie suas vendas e acompanhe o desempenho</p>
    </div>
</div>

<!-- Cards de estatísticas -->
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total de Vendas</h5>
                <div class="d-flex align-items-center">
                    <i class="fas fa-shopping-cart fa-3x me-3"></i>
                    <h2 class="card-value mb-0"><?php echo $estatisticas['total_vendas']; ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Valor Total</h5>
                <div class="d-flex align-items-center">
                    <i class="fas fa-dollar-sign fa-3x me-3"></i>
                    <h2 class="card-value mb-0">R$ <?php echo number_format($estatisticas['valor_total'], 2, ',', '.'); ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h5 class="card-title">Bilhetes Premiados</h5>
                <div class="d-flex align-items-center">
                    <i class="fas fa-trophy fa-3x me-3"></i>
                    <h2 class="card-value mb-0"><?php echo $estatisticas['bilhetes_premiados']; ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros de vendas -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title">Filtrar Vendas</h5>
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
                    <?php foreach ($concursos_ativos as $concurso): ?>
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
                <a href="vendas.php" class="btn btn-secondary">Limpar Filtros</a>
            </div>
        </form>
    </div>
</div>

<!-- Lista de vendas -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Suas Vendas</h5>
        <a href="nova-venda.php" class="btn btn-success btn-sm">
            <i class="fas fa-plus"></i> Nova Venda
        </a>
    </div>
    <div class="card-body">
        <?php if (empty($vendas)): ?>
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
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vendas as $venda): ?>
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
                                    <div class="btn-group btn-group-sm">
                                        <a href="detalhes-bilhete.php?id=<?php echo $venda['id']; ?>" class="btn btn-primary" title="Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($venda['status'] === 'pendente'): ?>
                                            <a href="confirmar-pagamento.php?id=<?php echo $venda['id']; ?>" class="btn btn-success" title="Confirmar Pagamento">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="imprimir-bilhete.php?id=<?php echo $venda['id']; ?>" class="btn btn-info" title="Imprimir" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
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
    });
</script>

<?php
// Incluir o rodapé
require_once '../templates/footer.php';
?> 