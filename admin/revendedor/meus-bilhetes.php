<?php
// Definir o título da página
$page_title = "Meus Bilhetes Vendidos";

// Verificar permissões
require_once '../auth.php';
requireAccess(2); // Nível mínimo: revendedor

// Incluir o cabeçalho
require_once '../templates/header.php';

// Incluir modelo de bilhete
require_once '../models/BilheteModel.php';
$bilheteModel = new BilheteModel();

// Recuperar o ID do vendedor da sessão
$vendedor_id = $_SESSION['usuario_id'] ?? 0;

// Definir opções de filtro
$status = isset($_GET['status']) ? $_GET['status'] : '';
$concurso_id = isset($_GET['concurso_id']) ? (int)$_GET['concurso_id'] : 0;
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 10;

// Configurar filtros para consulta
$filtros = [
    'vendedor_id' => $vendedor_id,
    'offset' => ($pagina - 1) * $por_pagina,
    'limite' => $por_pagina
];

// Adicionar filtros adicionais se fornecidos
if (!empty($status)) {
    $filtros['status'] = $status;
}
if (!empty($concurso_id)) {
    $filtros['concurso_id'] = $concurso_id;
}
if (!empty($data_inicio) && !empty($data_fim)) {
    $filtros['data_inicio'] = $data_inicio;
    $filtros['data_fim'] = $data_fim;
}

// Obter bilhetes com os filtros
$bilhetes = $bilheteModel->listarBilhetesComFiltros($filtros);

// Contagem total para paginação
$total_bilhetes = $bilheteModel->contarBilhetesComFiltros([
    'vendedor_id' => $vendedor_id,
    'status' => $status,
    'concurso_id' => $concurso_id,
    'data_inicio' => $data_inicio,
    'data_fim' => $data_fim
]);

// Calcular total de páginas
$total_paginas = ceil($total_bilhetes / $por_pagina);
?>

<div class="content-header">
    <div class="content-title">
        <h1><?php echo $page_title; ?></h1>
        <p>Gerencie todos os bilhetes que você vendeu</p>
    </div>
    
    <div class="content-actions">
        <a href="vender-bilhete.php" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Vender Novo Bilhete
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title">Filtros de Busca</h5>
    </div>
    <div class="card-body">
        <form action="" method="get" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="pago" <?php echo $status === 'pago' ? 'selected' : ''; ?>>Pago</option>
                    <option value="pendente" <?php echo $status === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                    <option value="cancelado" <?php echo $status === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="concurso_id" class="form-label">Concurso</label>
                <select name="concurso_id" id="concurso_id" class="form-select">
                    <option value="">Todos</option>
                    <?php
                    // Obter lista de concursos (código adicional necessário)
                    $concursos = [];
                    if (class_exists('ConcursoModel')) {
                        require_once '../models/ConcursoModel.php';
                        $concursoModel = new ConcursoModel();
                        $concursos = $concursoModel->listarConcursos();
                    }
                    
                    foreach ($concursos as $concurso) {
                        $selected = ($concurso['id'] == $concurso_id) ? 'selected' : '';
                        echo "<option value=\"{$concurso['id']}\" {$selected}>#{$concurso['numero']} {$concurso['nome']}</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="data_inicio" class="form-label">Data Início</label>
                <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?php echo $data_inicio; ?>">
            </div>
            
            <div class="col-md-3">
                <label for="data_fim" class="form-label">Data Fim</label>
                <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?php echo $data_fim; ?>">
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="meus-bilhetes.php" class="btn btn-secondary">Limpar Filtros</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Lista de Bilhetes</h5>
            <span class="badge bg-primary"><?php echo $total_bilhetes; ?> bilhetes encontrados</span>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($bilhetes)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Nenhum bilhete encontrado com os filtros selecionados.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Concurso</th>
                            <th>Cliente</th>
                            <th>Data Compra</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bilhetes as $bilhete): ?>
                            <tr>
                                <td><?php echo $bilhete['numero']; ?></td>
                                <td>
                                    <?php 
                                    if (isset($bilhete['concurso_nome']) && isset($bilhete['concurso_numero'])) {
                                        echo "#{$bilhete['concurso_numero']} {$bilhete['concurso_nome']}";
                                    } else {
                                        echo "Concurso #{$bilhete['concurso_id']}";
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    if (isset($bilhete['apostador_nome'])) {
                                        echo $bilhete['apostador_nome'];
                                    } elseif (isset($bilhete['usuario_nome'])) {
                                        echo $bilhete['usuario_nome'];
                                    } else {
                                        echo "Cliente #" . ($bilhete['apostador_id'] ?? $bilhete['usuario_id'] ?? 'N/A');
                                    }
                                    ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($bilhete['data_compra'])); ?></td>
                                <td>R$ <?php echo number_format($bilhete['valor'], 2, ',', '.'); ?></td>
                                <td>
                                    <?php 
                                    $status_class = '';
                                    switch ($bilhete['status']) {
                                        case 'pago':
                                            $status_class = 'bg-success';
                                            break;
                                        case 'pendente':
                                            $status_class = 'bg-warning text-dark';
                                            break;
                                        case 'cancelado':
                                            $status_class = 'bg-danger';
                                            break;
                                        default:
                                            $status_class = 'bg-secondary';
                                    }
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>">
                                        <?php echo ucfirst($bilhete['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="detalhes-bilhete.php?id=<?php echo $bilhete['id']; ?>" class="btn btn-sm btn-info" title="Ver Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($bilhete['status'] == 'pendente'): ?>
                                        <a href="confirmar-pagamento.php?id=<?php echo $bilhete['id']; ?>" class="btn btn-sm btn-success" title="Confirmar Pagamento">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <?php endif; ?>
                                        <a href="imprimir-bilhete.php?id=<?php echo $bilhete['id']; ?>" class="btn btn-sm btn-secondary" title="Imprimir" target="_blank">
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
                        <a class="page-link" href="?pagina=<?php echo $pagina - 1; ?>&status=<?php echo $status; ?>&concurso_id=<?php echo $concurso_id; ?>&data_inicio=<?php echo $data_inicio; ?>&data_fim=<?php echo $data_fim; ?>" aria-label="Anterior">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>&status=<?php echo $status; ?>&concurso_id=<?php echo $concurso_id; ?>&data_inicio=<?php echo $data_inicio; ?>&data_fim=<?php echo $data_fim; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $pagina + 1; ?>&status=<?php echo $status; ?>&concurso_id=<?php echo $concurso_id; ?>&data_inicio=<?php echo $data_inicio; ?>&data_fim=<?php echo $data_fim; ?>" aria-label="Próximo">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Resumo financeiro -->
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total de Bilhetes</h5>
                <h2 class="card-value"><?php echo $total_bilhetes; ?></h2>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Valor Total</h5>
                <h2 class="card-value">
                    <?php 
                    // Calcular valor total dos bilhetes filtrados
                    $valor_total = 0;
                    foreach ($bilhetes as $bilhete) {
                        if ($bilhete['status'] !== 'cancelado') {
                            $valor_total += $bilhete['valor'];
                        }
                    }
                    echo 'R$ ' . number_format($valor_total, 2, ',', '.');
                    ?>
                </h2>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h5 class="card-title">Comissão Estimada</h5>
                <h2 class="card-value">
                    <?php 
                    // Calcular comissão (assumindo 15% de comissão)
                    $comissao = $valor_total * 0.15;
                    echo 'R$ ' . number_format($comissao, 2, ',', '.');
                    ?>
                </h2>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Alerta para ações de sucesso ou erro
        <?php if (isset($_GET['sucesso'])): ?>
        showAlert('success', 'Operação realizada com sucesso!');
        <?php endif; ?>
        
        <?php if (isset($_GET['erro'])): ?>
        showAlert('danger', 'Ocorreu um erro na operação. Tente novamente.');
        <?php endif; ?>
        
        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.role = 'alert';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            `;
            
            document.querySelector('.content-header').after(alertDiv);
            
            // Auto fechar após 5 segundos
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alertDiv);
                bsAlert.close();
            }, 5000);
        }
    });
</script>

<?php
// Incluir o rodapé
require_once '../templates/footer.php';
?> 