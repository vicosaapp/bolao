<?php
// Definir o título da página
$page_title = "Meus Bilhetes";

// Verificar permissões
require_once '../auth.php';
requireAccess(3); // Nível mínimo: administrador

// Incluir modelos necessários
require_once '../models/BilheteModel.php';
require_once '../models/ConcursoModel.php';

// Inicializar modelos
$bilheteModel = new BilheteModel();
$concursoModel = new ConcursoModel();

// Configurações de paginação
$paginaAtual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$itensPorPagina = 10;
$offset = ($paginaAtual - 1) * $itensPorPagina;

// Filtros de busca
$filtroStatus = $_GET['status'] ?? null;
$filtroConcurso = $_GET['concurso'] ?? null;

// Buscar bilhetes com filtros
$filtros = [
    'status' => $filtroStatus,
    'concurso_id' => $filtroConcurso,
    'limite' => $itensPorPagina,
    'offset' => $offset
];
$bilhetes = $bilheteModel->listarBilhetesComFiltros($filtros);
$totalBilhetes = $bilheteModel->contarBilhetesComFiltros($filtros);

// Calcular total de páginas
$totalPaginas = ceil($totalBilhetes / $itensPorPagina);

// Buscar concursos para o filtro
$concursos = $concursoModel->listarConcursos();

// Incluir cabeçalho
require_once '../templates/header.php';
?>

<div class="content">
    <div class="content-header">
        <h1>Meus Bilhetes</h1>
        <p class="text-muted">Visualize e gerencie seus bilhetes</p>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Concurso</label>
                        <select name="concurso" class="form-select">
                            <option value="">Todos os Concursos</option>
                            <?php foreach($concursos as $concurso): ?>
                            <option value="<?php echo $concurso['id']; ?>" 
                                <?php echo ($filtroConcurso == $concurso['id']) ? 'selected' : ''; ?>>
                                <?php echo $concurso['nome']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Todos os Status</option>
                            <option value="pago" <?php echo ($filtroStatus == 'pago') ? 'selected' : ''; ?>>Pago</option>
                            <option value="pendente" <?php echo ($filtroStatus == 'pendente') ? 'selected' : ''; ?>>Pendente</option>
                            <option value="cancelado" <?php echo ($filtroStatus == 'cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-4 align-self-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="meus-bilhetes.php" class="btn btn-secondary">
                            <i class="fas fa-sync"></i> Limpar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Bilhetes -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="m-0">Lista de Bilhetes</h5>
            <div class="card-actions">
                <span class="text-muted">
                    Total: <?php echo number_format($totalBilhetes, 0, ',', '.'); ?> bilhetes
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Concurso</th>
                            <th>Valor</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($bilhetes)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                Nenhum bilhete encontrado.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach($bilhetes as $bilhete): ?>
                            <tr>
                                <td><?php echo $bilhete['numero']; ?></td>
                                <td><?php echo $bilhete['concurso']; ?></td>
                                <td>R$ <?php echo number_format($bilhete['valor'], 2, ',', '.'); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($bilhete['data_compra'])); ?></td>
                                <td>
                                    <span class="badge <?php 
                                        switch($bilhete['status']) {
                                            case 'pago':
                                                echo 'bg-success';
                                                break;
                                            case 'pendente':
                                                echo 'bg-warning';
                                                break;
                                            case 'cancelado':
                                                echo 'bg-danger';
                                                break;
                                            default:
                                                echo 'bg-secondary';
                                        }
                                    ?>">
                                        <?php echo ucfirst($bilhete['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" 
                                                data-bs-target="#detalhesBilheteModal<?php echo $bilhete['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if($bilhete['status'] == 'pendente'): ?>
                                        <button class="btn btn-sm btn-danger btn-cancelar-bilhete" 
                                                data-id="<?php echo $bilhete['id']; ?>">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Paginação -->
        <div class="card-footer d-flex justify-content-between align-items-center">
            <span>
                Página <?php echo $paginaAtual; ?> de <?php echo $totalPaginas; ?>
            </span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php if($paginaAtual > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?pagina=<?php echo $paginaAtual - 1; ?>&concurso=<?php echo $filtroConcurso; ?>&status=<?php echo $filtroStatus; ?>">
                            Anterior
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php 
                    $inicioIntervalo = max(1, $paginaAtual - 2);
                    $fimIntervalo = min($totalPaginas, $paginaAtual + 2);
                    
                    for($i = $inicioIntervalo; $i <= $fimIntervalo; $i++): 
                    ?>
                    <li class="page-item <?php echo $i == $paginaAtual ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>&concurso=<?php echo $filtroConcurso; ?>&status=<?php echo $filtroStatus; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>

                    <?php if($paginaAtual < $totalPaginas): ?>
                    <li class="page-item">
                        <a class="page-link" href="?pagina=<?php echo $paginaAtual + 1; ?>&concurso=<?php echo $filtroConcurso; ?>&status=<?php echo $filtroStatus; ?>">
                            Próximo
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Modais de Detalhes dos Bilhetes -->
<?php foreach($bilhetes as $bilhete): ?>
<div class="modal fade" id="detalhesBilheteModal<?php echo $bilhete['id']; ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Bilhete #<?php echo $bilhete['numero']; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Concurso:</strong>
                        <p><?php echo $bilhete['concurso']; ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Número do Bilhete:</strong>
                        <p><?php echo $bilhete['numero']; ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Valor:</strong>
                        <p>R$ <?php echo number_format($bilhete['valor'], 2, ',', '.'); ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Data da Compra:</strong>
                        <p><?php echo date('d/m/Y H:i:s', strtotime($bilhete['data_compra'])); ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Status:</strong>
                        <p>
                            <span class="badge <?php 
                                switch($bilhete['status']) {
                                    case 'pago':
                                        echo 'bg-success';
                                        break;
                                    case 'pendente':
                                        echo 'bg-warning';
                                        break;
                                    case 'cancelado':
                                        echo 'bg-danger';
                                        break;
                                    default:
                                        echo 'bg-secondary';
                                }
                            ?>">
                                <?php echo ucfirst($bilhete['status']); ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php
// Scripts específicos para a página
$page_scripts = [
    '../assets/js/meus-bilhetes.js'
];

// Incluir o rodapé
require_once '../templates/footer.php';
?> 