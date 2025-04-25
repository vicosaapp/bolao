<?php
// Definir o título da página
$page_title = "Concursos";

// Verificar permissões
require_once '../auth.php';
requireAccess(3); // Nível mínimo: administrador

// Incluir modelos necessários
require_once '../models/ConcursoModel.php';

// Inicializar modelo
$concursoModel = new ConcursoModel();

// Configurações de paginação
$paginaAtual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$itensPorPagina = 10;
$offset = ($paginaAtual - 1) * $itensPorPagina;

// Filtros de busca
$filtroStatus = $_GET['status'] ?? null;

// Buscar concursos com filtros
$filtros = [
    'status' => $filtroStatus,
    'limite' => $itensPorPagina,
    'offset' => $offset
];
$concursos = $concursoModel->listarConcursosComFiltros($filtros);
$totalConcursos = $concursoModel->contarConcursosComFiltros($filtros);

// Calcular total de páginas
$totalPaginas = ceil($totalConcursos / $itensPorPagina);

// Incluir cabeçalho
require_once '../templates/header.php';
?>

<div class="content">
    <div class="content-header">
        <h1>Concursos</h1>
        <p class="text-muted">Visualize e gerencie os concursos</p>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Todos os Status</option>
                            <option value="em_andamento" <?php echo ($filtroStatus == 'em_andamento') ? 'selected' : ''; ?>>Em Andamento</option>
                            <option value="finalizado" <?php echo ($filtroStatus == 'finalizado') ? 'selected' : ''; ?>>Finalizado</option>
                            <option value="pendente" <?php echo ($filtroStatus == 'pendente') ? 'selected' : ''; ?>>Pendente</option>
                        </select>
                    </div>
                    <div class="col-md-4 align-self-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="concursos.php" class="btn btn-secondary">
                            <i class="fas fa-sync"></i> Limpar
                        </a>
                    </div>
                    <div class="col-md-4 text-end align-self-end">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAdicionarConcurso">
                            <i class="fas fa-plus"></i> Adicionar Concurso
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Concursos -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="m-0">Lista de Concursos</h5>
            <div class="card-actions">
                <span class="text-muted">
                    Total: <?php echo number_format($totalConcursos, 0, ',', '.'); ?> concursos
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Nome</th>
                            <th>Data Início</th>
                            <th>Data Fim</th>
                            <th>Valor Prêmios</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($concursos)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                Nenhum concurso encontrado.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach($concursos as $concurso): ?>
                            <tr>
                                <td><?php echo $concurso['numero']; ?></td>
                                <td><?php echo $concurso['nome']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($concurso['data_inicio'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($concurso['data_fim'])); ?></td>
                                <td>R$ <?php echo number_format($concurso['valor_premios'], 2, ',', '.'); ?></td>
                                <td>
                                    <span class="badge <?php 
                                        switch($concurso['status']) {
                                            case 'em_andamento':
                                                echo 'bg-success';
                                                break;
                                            case 'finalizado':
                                                echo 'bg-danger';
                                                break;
                                            case 'pendente':
                                                echo 'bg-warning';
                                                break;
                                            default:
                                                echo 'bg-secondary';
                                        }
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $concurso['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" 
                                                data-bs-target="#modalAtualizarStatus" 
                                                data-id="<?php echo $concurso['id']; ?>">
                                            <i class="fas fa-sync"></i> Status
                                        </button>
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
                        <a class="page-link" href="?pagina=<?php echo $paginaAtual - 1; ?>&status=<?php echo $filtroStatus; ?>">
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
                        <a class="page-link" href="?pagina=<?php echo $i; ?>&status=<?php echo $filtroStatus; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>

                    <?php if($paginaAtual < $totalPaginas): ?>
                    <li class="page-item">
                        <a class="page-link" href="?pagina=<?php echo $paginaAtual + 1; ?>&status=<?php echo $filtroStatus; ?>">
                            Próximo
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Modal Adicionar Concurso -->
<div class="modal fade" id="modalAdicionarConcurso" tabindex="-1" aria-labelledby="tituloModalAdicionarConcurso" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalAdicionarConcurso">Adicionar Novo Concurso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="formAdicionarConcurso" method="POST" action="?acao=adicionar">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="numero" class="form-label">Número do Concurso</label>
                        <input type="number" class="form-control" id="numero" name="numero" required>
                    </div>
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome do Concurso</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="data_inicio" class="form-label">Data de Início</label>
                        <input type="date" class="form-control" id="data_inicio" name="data_inicio" required>
                    </div>
                    <div class="mb-3">
                        <label for="data_fim" class="form-label">Data de Fim</label>
                        <input type="date" class="form-control" id="data_fim" name="data_fim" required>
                    </div>
                    <div class="mb-3">
                        <label for="valor_premios" class="form-label">Valor dos Prêmios (R$)</label>
                        <input type="number" step="0.01" class="form-control" id="valor_premios" name="valor_premios" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Adicionar Concurso</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Atualizar Status -->
<div class="modal fade" id="modalAtualizarStatus" tabindex="-1" aria-labelledby="tituloModalAtualizarStatus" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalAtualizarStatus">Atualizar Status do Concurso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="formAtualizarStatus" method="POST" action="?acao=atualizar_status">
                <div class="modal-body">
                    <input type="hidden" id="statusId" name="id">
                    <div class="mb-3">
                        <label for="status" class="form-label">Novo Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="em_andamento">Em Andamento</option>
                            <option value="finalizado">Finalizado</option>
                            <option value="pendente">Pendente</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Atualizar Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Incluir scripts personalizados
require_once '../templates/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Script para preencher modal de atualização de status
    const modalAtualizarStatus = document.getElementById('modalAtualizarStatus');
    modalAtualizarStatus.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const modal = this;
        modal.querySelector('#statusId').value = id;
    });
});
</script> 