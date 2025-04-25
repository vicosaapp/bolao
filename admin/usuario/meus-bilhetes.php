<?php
// Definir o título da página
$page_title = "Meus Bilhetes";

// CSS específico para esta página
$extraCSS = [
    '../assets/css/meus-bilhetes.css'
];

// JavaScript específico para esta página
$extraJS = [
    '../assets/js/meus-bilhetes.js'
];

// Verificar permissões
require_once '../auth.php';
requireAccess(1); // Nível mínimo: usuário comum

// Incluir modelos necessários
require_once '../models/BilheteModel.php';
require_once '../models/ConcursoModel.php';

// Incluir o cabeçalho
require_once '../templates/header.php';

// Inicializar modelos
$bilheteModel = new BilheteModel();
$concursoModel = new ConcursoModel();

// Obter ID do usuário logado
$usuarioId = $_SESSION['usuario_id'] ?? null;

// Configurações de paginação
$paginaAtual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$itensPorPagina = 10;
$offset = ($paginaAtual - 1) * $itensPorPagina;

// Filtros de busca
$filtroStatus = $_GET['status'] ?? null;
$filtroConcurso = $_GET['concurso_id'] ?? null;

// Buscar bilhetes do usuário com filtros
$filtros = [
    'usuario_id' => $usuarioId,
    'status' => $filtroStatus,
    'concurso_id' => $filtroConcurso,
    'limite' => $itensPorPagina,
    'offset' => $offset
];

// Obter lista de bilhetes e total
$bilhetes = $bilheteModel->listarBilhetesDoUsuario($filtros);
$totalBilhetes = $bilheteModel->contarBilhetesDoUsuario($filtros);

// Calcular total de páginas
$totalPaginas = ceil($totalBilhetes / $itensPorPagina);

// Obter lista de concursos para o filtro
$concursos = $concursoModel->listarConcursosAtivos();

// Calcular estatísticas
$totalApostado = $bilheteModel->calcularTotalApostadoPorUsuario($usuarioId);
$totalPremios = $bilheteModel->calcularTotalPremiosGanhosPorUsuario($usuarioId);
$bilhetesVencedores = $bilheteModel->contarBilhetesVencedoresPorUsuario($usuarioId);
$concursosParticipados = $bilheteModel->contarConcursosParticipados($usuarioId);
?>

<div class="content">
    <div class="content-header">
        <h1>Meus Bilhetes</h1>
        <p class="text-muted">Visualize e acompanhe seus bilhetes de loteria</p>
    </div>

    <!-- Cards de estatísticas -->
    <div class="dashboard-stats">
        <div class="stat-card primary">
            <div class="stat-card-header">
                <h5 class="stat-card-title">Total de Bilhetes</h5>
                <div class="stat-card-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
            </div>
            <h2 class="stat-card-value"><?php echo number_format($totalBilhetes, 0, ',', '.'); ?></h2>
            <p class="stat-card-description">
                <i class="fas fa-info-circle"></i> 
                <span>Bilhetes adquiridos</span>
            </p>
        </div>
        
        <div class="stat-card success">
            <div class="stat-card-header">
                <h5 class="stat-card-title">Total Apostado</h5>
                <div class="stat-card-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
            <h2 class="stat-card-value">R$ <?php echo number_format($totalApostado, 2, ',', '.'); ?></h2>
            <p class="stat-card-description">
                <i class="fas fa-chart-line"></i> 
                <span>Valor total investido</span>
            </p>
        </div>
        
        <div class="stat-card info">
            <div class="stat-card-header">
                <h5 class="stat-card-title">Bilhetes Premiados</h5>
                <div class="stat-card-icon">
                    <i class="fas fa-trophy"></i>
                </div>
            </div>
            <h2 class="stat-card-value"><?php echo number_format($bilhetesVencedores, 0, ',', '.'); ?></h2>
            <p class="stat-card-description">
                <i class="fas fa-arrow-up"></i> 
                <span>Bilhetes vencedores</span>
            </p>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-card-header">
                <h5 class="stat-card-title">Total de Prêmios</h5>
                <div class="stat-card-icon">
                    <i class="fas fa-gem"></i>
                </div>
            </div>
            <h2 class="stat-card-value">R$ <?php echo number_format($totalPremios, 2, ',', '.'); ?></h2>
            <p class="stat-card-description">
                <i class="fas fa-award"></i> 
                <span>Valor ganho em prêmios</span>
            </p>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos os Status</option>
                        <option value="aguardando" <?php echo ($filtroStatus == 'aguardando') ? 'selected' : ''; ?>>Aguardando Sorteio</option>
                        <option value="premiado" <?php echo ($filtroStatus == 'premiado') ? 'selected' : ''; ?>>Premiado</option>
                        <option value="nao_premiado" <?php echo ($filtroStatus == 'nao_premiado') ? 'selected' : ''; ?>>Não Premiado</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Concurso</label>
                    <select name="concurso_id" class="form-select">
                        <option value="">Todos os Concursos</option>
                        <?php foreach ($concursos as $concurso): ?>
                            <option value="<?php echo $concurso['id']; ?>" <?php echo ($filtroConcurso == $concurso['id']) ? 'selected' : ''; ?>>
                                <?php echo $concurso['nome']; ?> (#<?php echo $concurso['numero']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3 d-flex align-items-end">
                    <div class="btn-group w-100">
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
            <h5 class="m-0">Meus Bilhetes</h5>
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
                            <th>Data da Compra</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Premiação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($bilhetes)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted mb-0">Você ainda não possui bilhetes.</p>
                                <a href="../comprar-bilhete.php" class="btn btn-sm btn-primary mt-2">
                                    <i class="fas fa-plus"></i> Comprar Bilhete
                                </a>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach($bilhetes as $bilhete): ?>
                            <tr>
                                <td><?php echo $bilhete['numero']; ?></td>
                                <td><?php echo $bilhete['concurso_nome']; ?> (#<?php echo $bilhete['concurso_numero']; ?>)</td>
                                <td><?php echo date('d/m/Y H:i', strtotime($bilhete['data_compra'])); ?></td>
                                <td>R$ <?php echo number_format($bilhete['valor'], 2, ',', '.'); ?></td>
                                <td>
                                    <span class="badge <?php 
                                        switch($bilhete['status']) {
                                            case 'aguardando':
                                                echo 'bg-info';
                                                break;
                                            case 'premiado':
                                                echo 'bg-success';
                                                break;
                                            case 'nao_premiado':
                                                echo 'bg-danger';
                                                break;
                                            default:
                                                echo 'bg-secondary';
                                        }
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $bilhete['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if(isset($bilhete['valor_premio']) && $bilhete['valor_premio'] > 0): ?>
                                        <span class="text-success">
                                            R$ <?php echo number_format($bilhete['valor_premio'], 2, ',', '.'); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info ver-bilhete" data-id="<?php echo $bilhete['id']; ?>">
                                        <i class="fas fa-eye"></i> Detalhes
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Paginação -->
        <?php if($totalPaginas > 1): ?>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <nav aria-label="Navegação de páginas">
                <ul class="pagination mb-0">
                    <li class="page-item <?php echo ($paginaAtual <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $paginaAtual - 1; ?>&status=<?php echo $filtroStatus; ?>&concurso_id=<?php echo $filtroConcurso; ?>" aria-label="Anterior">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for($i = 1; $i <= $totalPaginas; $i++): ?>
                        <li class="page-item <?php echo ($paginaAtual == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?pagina=<?php echo $i; ?>&status=<?php echo $filtroStatus; ?>&concurso_id=<?php echo $filtroConcurso; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($paginaAtual >= $totalPaginas) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $paginaAtual + 1; ?>&status=<?php echo $filtroStatus; ?>&concurso_id=<?php echo $filtroConcurso; ?>" aria-label="Próximo">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="text-muted">
                Página <?php echo $paginaAtual; ?> de <?php echo $totalPaginas; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Detalhes do Bilhete -->
<div class="modal fade" id="modalDetalhesBilhete" tabindex="-1" aria-labelledby="modalDetalhesBilheteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalhesBilheteLabel">Detalhes do Bilhete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="mt-2">Carregando detalhes do bilhete...</p>
                </div>
                <div id="detalhesBilhete" class="d-none">
                    <!-- Conteúdo será preenchido via JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <a href="#" class="btn btn-primary" id="btnImprimirBilhete" target="_blank">
                    <i class="fas fa-print"></i> Imprimir Bilhete
                </a>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir o rodapé
require_once '../templates/footer.php';
?> 