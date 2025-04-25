<?php
require_once '../../includes/config.php';
require_once '../auth.php';
requireAccess(3); // Exige nível de acesso de superadmin

// Carrega o modelo de log de atividades
require_once '../models/LogAtividadeModel.php';
$logModel = new LogAtividadeModel();

// Inicialização dos filtros
$filtros = [];
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$itensPorPagina = 50;

// Aplicação dos filtros da URL
if (isset($_GET['usuario_id']) && !empty($_GET['usuario_id'])) {
    $filtros['usuario_id'] = (int)$_GET['usuario_id'];
}

if (isset($_GET['tipo']) && !empty($_GET['tipo'])) {
    $filtros['tipo'] = $_GET['tipo'];
}

if (isset($_GET['data_inicio']) && !empty($_GET['data_inicio'])) {
    $filtros['data_inicio'] = $_GET['data_inicio'];
}

if (isset($_GET['data_fim']) && !empty($_GET['data_fim'])) {
    $filtros['data_fim'] = $_GET['data_fim'];
}

// Obtém os logs com base nos filtros
$resultado = $logModel->listarAtividades($filtros, $pagina, $itensPorPagina);
$logs = $resultado['registros'];
$totalPaginas = $resultado['paginas'];

// Obtém os tipos de atividades para o filtro
$tiposAtividades = $logModel->obterTiposAtividades();

// Carrega o modelo de usuários para o filtro
require_once '../models/UsuarioModel.php';
$usuarioModel = new UsuarioModel();
$usuarios = $usuarioModel->listarUsuarios([], 1, 1000)['usuarios']; // Limita a 1000 para o filtro
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log de Atividades - Administração</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/log-atividades.css">
</head>
<body>

<?php include '../includes/header.php'; ?>
<?php include '../includes/menu.php'; ?>

<div class="content">
    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="h3 mb-2">Log de Atividades do Sistema</h1>
                <p class="text-muted">Monitore todas as ações realizadas pelos usuários no sistema.</p>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-filter mr-2"></i> Filtros</h5>
            </div>
            <div class="card-body">
                <form id="filtroForm" method="get" action="">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="usuario_id">Usuário:</label>
                                <select class="form-control" id="usuario_id" name="usuario_id">
                                    <option value="">Todos os Usuários</option>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <option value="<?= $usuario['id'] ?>" <?= (isset($filtros['usuario_id']) && $filtros['usuario_id'] == $usuario['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($usuario['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="tipo">Tipo de Atividade:</label>
                                <select class="form-control" id="tipo" name="tipo">
                                    <option value="">Todos os Tipos</option>
                                    <?php foreach ($tiposAtividades as $tipo): ?>
                                        <option value="<?= $tipo ?>" <?= (isset($filtros['tipo']) && $filtros['tipo'] == $tipo) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($tipo) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="data_inicio">Data Início:</label>
                                <input type="text" class="form-control date-picker" id="data_inicio" name="data_inicio" 
                                       value="<?= isset($filtros['data_inicio']) ? htmlspecialchars($filtros['data_inicio']) : '' ?>" 
                                       placeholder="Data inicial">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="data_fim">Data Fim:</label>
                                <input type="text" class="form-control date-picker" id="data_fim" name="data_fim" 
                                       value="<?= isset($filtros['data_fim']) ? htmlspecialchars($filtros['data_fim']) : '' ?>" 
                                       placeholder="Data final">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search mr-1"></i> Filtrar
                            </button>
                            <a href="log-atividades.php" class="btn btn-secondary ml-2">
                                <i class="fas fa-undo mr-1"></i> Limpar Filtros
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabela de Logs -->
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-history mr-2"></i> Registros de Atividades</h5>
                <span class="badge badge-primary"><?= $resultado['total'] ?> registros encontrados</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Usuário</th>
                                <th scope="col">Tipo</th>
                                <th scope="col">Descrição</th>
                                <th scope="col">IP</th>
                                <th scope="col">Data/Hora</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-info-circle mr-2"></i> Nenhum registro de atividade encontrado.
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr class="log-row" data-id="<?= $log['id'] ?>" style="cursor: pointer;" title="Clique para ver detalhes">
                                        <td><?= $log['id'] ?></td>
                                        <td><?= htmlspecialchars($log['nome_usuario'] ?? 'Usuário Excluído') ?></td>
                                        <td>
                                            <span class="badge <?= getLogTypeBadgeClass($log['tipo']) ?>">
                                                <?= htmlspecialchars($log['tipo']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($log['descricao']) ?></td>
                                        <td><?= htmlspecialchars($log['ip']) ?></td>
                                        <td><?= date('d/m/Y H:i:s', strtotime($log['data_hora'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Paginação -->
            <?php if ($totalPaginas > 1): ?>
                <div class="card-footer bg-white">
                    <nav aria-label="Navegação de páginas">
                        <ul class="pagination justify-content-center mb-0">
                            <?php if ($pagina > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= gerarUrlPaginacao($filtros, 1) ?>">
                                        <i class="fas fa-angle-double-left"></i>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="<?= gerarUrlPaginacao($filtros, $pagina - 1) ?>">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            // Exibe no máximo 5 páginas na navegação
                            $inicio = max(1, $pagina - 2);
                            $fim = min($totalPaginas, $inicio + 4);
                            $inicio = max(1, $fim - 4);
                            
                            for ($i = $inicio; $i <= $fim; $i++):
                            ?>
                                <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= gerarUrlPaginacao($filtros, $i) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagina < $totalPaginas): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= gerarUrlPaginacao($filtros, $pagina + 1) ?>">
                                        <i class="fas fa-angle-right"></i>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="<?= gerarUrlPaginacao($filtros, $totalPaginas) ?>">
                                        <i class="fas fa-angle-double-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para detalhes do log -->
<div class="modal fade" id="modalDetalhesLog" tabindex="-1" role="dialog" aria-labelledby="modalDetalhesLogTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalhesLogTitle">Detalhes do Log</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Conteúdo será preenchido via JavaScript -->
                <div class="text-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Carregando...</span>
                    </div>
                    <p class="mt-2">Carregando detalhes...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Container para alertas -->
<div id="alertaContainer" class="position-fixed" style="top: 20px; right: 20px; z-index: 9999;"></div>

<?php
// Função para gerar a URL de paginação mantendo os filtros
function gerarUrlPaginacao($filtros, $pagina) {
    $params = ['pagina' => $pagina];
    
    if (!empty($filtros['usuario_id'])) {
        $params['usuario_id'] = $filtros['usuario_id'];
    }
    
    if (!empty($filtros['tipo'])) {
        $params['tipo'] = $filtros['tipo'];
    }
    
    if (!empty($filtros['data_inicio'])) {
        $params['data_inicio'] = $filtros['data_inicio'];
    }
    
    if (!empty($filtros['data_fim'])) {
        $params['data_fim'] = $filtros['data_fim'];
    }
    
    return 'log-atividades.php?' . http_build_query($params);
}

// Função para determinar a classe da badge com base no tipo de log
function getLogTypeBadgeClass($tipo) {
    switch (strtolower($tipo)) {
        case 'login':
            return 'badge-success';
        case 'logout':
            return 'badge-secondary';
        case 'erro':
        case 'exclusão':
            return 'badge-danger';
        case 'alteração':
            return 'badge-warning';
        case 'criação':
            return 'badge-info';
        default:
            return 'badge-primary';
    }
}
?>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
<script src="../assets/js/admin.js"></script>
<script src="../assets/js/log-atividades.js"></script>

</body>
</html> 