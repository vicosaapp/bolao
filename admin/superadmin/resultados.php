<?php
// Definir o título da página
$page_title = "Resultados de Concursos";

// Verificar permissões
require_once '../auth.php';
requireAccess(3); // Nível mínimo: administrador

// Incluir modelos necessários
require_once '../models/ConcursoModel.php';
require_once '../models/ResultadoModel.php';

// Inicializar modelos
$concursoModel = new ConcursoModel();
$resultadoModel = new ResultadoModel();

// Configurações de paginação
$paginaAtual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$itensPorPagina = 10;
$offset = ($paginaAtual - 1) * $itensPorPagina;

// Filtros de busca
$filtroConcurso = $_GET['concurso'] ?? null;
$filtroStatus = $_GET['status'] ?? null;

// Buscar resultados com filtros
$filtros = [
    'concurso_id' => $filtroConcurso,
    'status' => $filtroStatus,
    'limite' => $itensPorPagina,
    'offset' => $offset
];
$resultados = $resultadoModel->listarResultadosComFiltros($filtros);
$totalResultados = $resultadoModel->contarResultadosComFiltros($filtros);

// Buscar concursos para o filtro
$concursos = $concursoModel->listarConcursos();

// Calcular total de páginas
$totalPaginas = ceil($totalResultados / $itensPorPagina);

// Processar ações de formulário
$acao = $_GET['acao'] ?? null;
$mensagem = '';
$tipoMensagem = '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        switch ($acao) {
            case 'adicionar':
                $dadosResultado = [
                    'concurso_id' => $_POST['concurso_id'] ?? null,
                    'numeros_sorteados' => $_POST['numeros_sorteados'] ?? null,
                    'data_sorteio' => $_POST['data_sorteio'] ?? null,
                    'status' => $_POST['status'] ?? 'pendente'
                ];

                $resultado = $resultadoModel->adicionarResultado($dadosResultado);
                
                if ($resultado) {
                    $mensagem = "Resultado adicionado com sucesso!";
                    $tipoMensagem = 'success';
                } else {
                    $mensagem = "Erro ao adicionar resultado.";
                    $tipoMensagem = 'danger';
                }
                break;

            case 'atualizar_status':
                $id = $_POST['id'] ?? null;
                $status = $_POST['status'] ?? null;

                if ($id && $status) {
                    $resultado = $resultadoModel->atualizarStatusResultado($id, $status);
                    
                    if ($resultado) {
                        $mensagem = "Status do resultado atualizado com sucesso!";
                        $tipoMensagem = 'success';
                    } else {
                        $mensagem = "Erro ao atualizar status do resultado.";
                        $tipoMensagem = 'danger';
                    }
                }
                break;
        }
    }
} catch (Exception $e) {
    $mensagem = "Erro: " . $e->getMessage();
    $tipoMensagem = 'danger';
}

// Incluir cabeçalho
require_once '../templates/header.php';
?>

<div class="content">
    <div class="content-header">
        <h1>Resultados de Concursos</h1>
        <p class="text-muted">Visualize e gerencie os resultados dos concursos</p>
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
                            <option value="pendente" <?php echo ($filtroStatus == 'pendente') ? 'selected' : ''; ?>>Pendente</option>
                            <option value="publicado" <?php echo ($filtroStatus == 'publicado') ? 'selected' : ''; ?>>Publicado</option>
                        </select>
                    </div>
                    <div class="col-md-4 align-self-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="resultados.php" class="btn btn-secondary">
                            <i class="fas fa-sync"></i> Limpar
                        </a>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAdicionarResultado">
                            <i class="fas fa-plus"></i> Adicionar Resultado
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Resultados -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="m-0">Lista de Resultados</h5>
            <div class="card-actions">
                <span class="text-muted">
                    Total: <?php echo number_format($totalResultados, 0, ',', '.'); ?> resultados
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Concurso</th>
                            <th>Números Sorteados</th>
                            <th>Data do Sorteio</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($resultados)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                Nenhum resultado encontrado.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach($resultados as $resultado): ?>
                            <tr>
                                <td><?php echo $resultado['nome_concurso']; ?></td>
                                <td><?php echo $resultado['numeros_sorteados']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($resultado['data_sorteio'])); ?></td>
                                <td>
                                    <span class="badge <?php 
                                        switch($resultado['status']) {
                                            case 'publicado':
                                                echo 'bg-success';
                                                break;
                                            case 'pendente':
                                                echo 'bg-warning';
                                                break;
                                            default:
                                                echo 'bg-secondary';
                                        }
                                    ?>">
                                        <?php echo ucfirst($resultado['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" 
                                                data-bs-target="#modalAtualizarStatus" 
                                                data-id="<?php echo $resultado['id']; ?>">
                                            <i class="fas fa-sync"></i> Status
                                        </button>
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" 
                                                data-bs-target="#modalDetalhesResultado" 
                                                data-resultado='<?php echo htmlspecialchars(json_encode($resultado)); ?>'>
                                            <i class="fas fa-eye"></i> Detalhes
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

<!-- Modal Adicionar Resultado -->
<div class="modal fade" id="modalAdicionarResultado" tabindex="-1" aria-labelledby="tituloModalAdicionarResultado" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalAdicionarResultado">Adicionar Novo Resultado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="formAdicionarResultado" method="POST" action="?acao=adicionar">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="concurso_id" class="form-label">Concurso</label>
                        <select class="form-select" id="concurso_id" name="concurso_id" required>
                            <option value="">Selecione um Concurso</option>
                            <?php foreach($concursos as $concurso): ?>
                            <option value="<?php echo $concurso['id']; ?>">
                                <?php echo $concurso['nome']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="numeros_sorteados" class="form-label">Números Sorteados</label>
                        <input type="text" class="form-control" id="numeros_sorteados" name="numeros_sorteados" 
                               placeholder="Ex: 01 02 03 04 05 06" required>
                    </div>
                    <div class="mb-3">
                        <label for="data_sorteio" class="form-label">Data do Sorteio</label>
                        <input type="date" class="form-control" id="data_sorteio" name="data_sorteio" required>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="pendente">Pendente</option>
                            <option value="publicado">Publicado</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Adicionar Resultado</button>
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
                <h5 class="modal-title" id="tituloModalAtualizarStatus">Atualizar Status do Resultado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="formAtualizarStatus" method="POST" action="?acao=atualizar_status">
                <div class="modal-body">
                    <input type="hidden" id="statusId" name="id">
                    <div class="mb-3">
                        <label for="status" class="form-label">Novo Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pendente">Pendente</option>
                            <option value="publicado">Publicado</option>
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

<!-- Modal Detalhes do Resultado -->
<div class="modal fade" id="modalDetalhesResultado" tabindex="-1" aria-labelledby="tituloModalDetalhesResultado" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalDetalhesResultado">Detalhes do Resultado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Concurso</label>
                        <p id="detalheConcurso" class="form-control-plaintext"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Data do Sorteio</label>
                        <p id="detalheSorteio" class="form-control-plaintext"></p>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Números Sorteados</label>
                        <p id="detalheNumeros" class="form-control-plaintext"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <p id="detalheStatus" class="form-control-plaintext"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
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

    // Script para preencher modal de detalhes do resultado
    const modalDetalhesResultado = document.getElementById('modalDetalhesResultado');
    modalDetalhesResultado.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const resultado = JSON.parse(button.getAttribute('data-resultado'));
        
        document.getElementById('detalheConcurso').textContent = resultado.nome_concurso;
        document.getElementById('detalheSorteio').textContent = new Date(resultado.data_sorteio).toLocaleDateString('pt-BR');
        document.getElementById('detalheNumeros').textContent = resultado.numeros_sorteados;
        document.getElementById('detalheStatus').textContent = resultado.status.charAt(0).toUpperCase() + resultado.status.slice(1);
    });
});
</script> 