<?php
// Definir o título da página
$page_title = "Concursos Disponíveis";

// CSS específico para esta página
$extraCSS = [
    '../assets/css/concursos.css'
];

// JavaScript específico para esta página
$extraJS = [
    '../assets/js/concursos.js'
];

// Verificar permissões
require_once '../auth.php';
requireAccess(1); // Nível mínimo: usuário comum

// Incluir modelos necessários
require_once '../models/ConcursoModel.php';
require_once '../models/BilheteModel.php';

// Incluir o cabeçalho
require_once '../templates/header.php';

// Inicializar modelos
$concursoModel = new ConcursoModel();
$bilheteModel = new BilheteModel();
$usuarioId = $_SESSION['usuario_id'];

// Carregar estatísticas do usuário
$totalApostado = $bilheteModel->calcularTotalApostadoPorUsuario($usuarioId);
$totalPremios = $bilheteModel->calcularTotalPremiosGanhosPorUsuario($usuarioId);
$bilhetesVencedores = $bilheteModel->contarBilhetesVencedoresPorUsuario($usuarioId);
$concursosParticipados = $bilheteModel->contarConcursosParticipados($usuarioId);

// Obter concursos ativos
$concursosAtivos = $concursoModel->listarConcursosAtivos();

// Obter concursos finalizados (limitado aos 5 mais recentes)
$concursosFinalizados = $concursoModel->listarConcursosFinalizados(5);

// Obter próximo concurso (primeiro da lista de ativos)
$proximoConcurso = !empty($concursosAtivos) ? $concursosAtivos[0] : null;
?>

<div class="container mt-4">
    <h1 class="mb-4"><?php echo $page_title; ?></h1>
    
    <!-- Dashboard de Estatísticas do Usuário -->
    <div class="dashboard-stats mb-5">
        <div class="stat-card primary">
            <div class="stat-card-header">
                <i class="fas fa-coins"></i>
            </div>
            <div class="stat-card-body">
                <h2 class="stat-card-title">Total Apostado</h2>
                <div class="stat-card-value">R$ <?php echo number_format($totalApostado, 2, ',', '.'); ?></div>
                <p class="stat-card-desc">Valor total de suas apostas</p>
            </div>
        </div>
        
        <div class="stat-card success">
            <div class="stat-card-header">
                <i class="fas fa-trophy"></i>
            </div>
            <div class="stat-card-body">
                <h2 class="stat-card-title">Prêmios Ganhos</h2>
                <div class="stat-card-value">R$ <?php echo number_format($totalPremios, 2, ',', '.'); ?></div>
                <p class="stat-card-desc">Total de prêmios recebidos</p>
            </div>
        </div>
        
        <div class="stat-card info">
            <div class="stat-card-header">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="stat-card-body">
                <h2 class="stat-card-title">Bilhetes Vencedores</h2>
                <div class="stat-card-value"><?php echo $bilhetesVencedores; ?></div>
                <p class="stat-card-desc">Número de bilhetes premiados</p>
            </div>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-card-header">
                <i class="fas fa-gamepad"></i>
            </div>
            <div class="stat-card-body">
                <h2 class="stat-card-title">Participações</h2>
                <div class="stat-card-value"><?php echo $concursosParticipados; ?></div>
                <p class="stat-card-desc">Número de concursos participados</p>
            </div>
        </div>
    </div>
    
    <?php if ($proximoConcurso): ?>
    <!-- Próximo Concurso em Destaque -->
    <div class="featured-contest mb-5">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Próximo Concurso</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4><?php echo htmlspecialchars($proximoConcurso['titulo']); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($proximoConcurso['descricao']); ?></p>
                        
                        <div class="row mt-3">
                            <div class="col-6">
                                <p><strong>Valor do Bilhete:</strong> R$ <?php echo number_format($proximoConcurso['valor_bilhete'], 2, ',', '.'); ?></p>
                            </div>
                            <div class="col-6">
                                <p><strong>Prêmio Estimado:</strong> R$ <?php echo number_format($proximoConcurso['valor_premios'], 2, ',', '.'); ?></p>
                            </div>
                        </div>
                        
                        <?php 
                        // Calcular progresso da arrecadação
                        $valorArrecadado = $proximoConcurso['valor_bilhete'] * $proximoConcurso['bilhetes_vendidos'];
                        $valorTotal = $proximoConcurso['valor_bilhete'] * $proximoConcurso['total_bilhetes'];
                        $progresso = ($valorTotal > 0) ? min(100, ($valorArrecadado / $valorTotal) * 100) : 0;
                        ?>
                        <div class="progress mt-3">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                role="progressbar" 
                                style="width: <?php echo $progresso; ?>%" 
                                aria-valuenow="<?php echo $progresso; ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                                <?php echo number_format($progresso, 0); ?>%
                            </div>
                        </div>
                        <p class="text-muted mt-1">
                            <?php echo $proximoConcurso['bilhetes_vendidos']; ?> de <?php echo $proximoConcurso['total_bilhetes']; ?> bilhetes vendidos
                        </p>
                        
                        <div class="mt-4">
                            <a href="comprar-bilhete.php?id=<?php echo $proximoConcurso['id']; ?>" class="btn btn-success">
                                <i class="fas fa-shopping-cart"></i> Comprar Bilhete
                            </a>
                            <button class="btn btn-outline-primary ver-concurso" data-id="<?php echo $proximoConcurso['id']; ?>">
                                <i class="fas fa-info-circle"></i> Ver Detalhes
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="countdown-container">
                            <h5>Sorteio em:</h5>
                            <div class="countdown" data-date="<?php echo $proximoConcurso['data_sorteio']; ?>">
                                <div class="countdown-item">
                                    <span class="countdown-value days">00</span>
                                    <span class="countdown-label">Dias</span>
                                </div>
                                <div class="countdown-item">
                                    <span class="countdown-value hours">00</span>
                                    <span class="countdown-label">Horas</span>
                                </div>
                                <div class="countdown-item">
                                    <span class="countdown-value minutes">00</span>
                                    <span class="countdown-label">Min</span>
                                </div>
                                <div class="countdown-item">
                                    <span class="countdown-value seconds">00</span>
                                    <span class="countdown-label">Seg</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Lista de Concursos Ativos -->
    <h2 class="mb-3">Concursos Ativos</h2>
    <?php if (!empty($concursosAtivos)): ?>
        <div class="row">
            <?php foreach ($concursosAtivos as $concurso): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><?php echo htmlspecialchars($concurso['titulo']); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <div>
                                    <span class="badge bg-primary">Ativo</span>
                                </div>
                                <div>
                                    <strong>Bilhetes: </strong> 
                                    <?php echo $concurso['bilhetes_vendidos']; ?>/<?php echo $concurso['total_bilhetes']; ?>
                                </div>
                            </div>
                            
                            <p class="card-text text-truncate"><?php echo htmlspecialchars($concurso['descricao']); ?></p>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <strong>R$ <?php echo number_format($concurso['valor_bilhete'], 2, ',', '.'); ?></strong>
                                </div>
                                <div>
                                    <button class="btn btn-outline-primary btn-sm ver-concurso" data-id="<?php echo $concurso['id']; ?>">
                                        <i class="fas fa-info-circle"></i> Detalhes
                                    </button>
                                    <a href="comprar-bilhete.php?id=<?php echo $concurso['id']; ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-shopping-cart"></i> Comprar
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            <small>Sorteio: <?php echo formatar_data($concurso['data_sorteio']); ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Não há concursos ativos no momento. Fique atento para os próximos lançamentos!
        </div>
    <?php endif; ?>
    
    <!-- Lista de Concursos Finalizados -->
    <h2 class="mb-3 mt-5">Concursos Recentes</h2>
    <?php if (!empty($concursosFinalizados)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Concurso</th>
                        <th>Data do Sorteio</th>
                        <th>Valor do Prêmio</th>
                        <th>Vencedores</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($concursosFinalizados as $concurso): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($concurso['titulo']); ?></td>
                            <td><?php echo formatar_data($concurso['data_sorteio']); ?></td>
                            <td>R$ <?php echo number_format($concurso['valor_premios'], 2, ',', '.'); ?></td>
                            <td><?php echo isset($concurso['total_ganhadores']) ? $concurso['total_ganhadores'] : 'N/A'; ?></td>
                            <td>
                                <button class="btn btn-outline-info btn-sm ver-concurso" data-id="<?php echo $concurso['id']; ?>">
                                    <i class="fas fa-info-circle"></i> Detalhes
                                </button>
                                <a href="resultado.php?id=<?php echo $concurso['id']; ?>" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-list-ol"></i> Resultado
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="text-center mt-3">
            <a href="historico-concursos.php" class="btn btn-outline-primary">Ver todos os concursos</a>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Não há concursos finalizados no histórico.
        </div>
    <?php endif; ?>
</div>

<!-- Modal de Detalhes do Concurso -->
<div class="modal fade" id="modalDetalhes" tabindex="-1" aria-labelledby="modalDetalhesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalhesLabel">Detalhes do Concurso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div id="carregando" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="mt-2">Carregando detalhes do concurso...</p>
                </div>
                <div id="detalhes-concurso" style="display: none;">
                    <!-- Conteúdo será preenchido dinamicamente via JavaScript -->
                </div>
                <div id="erro-carregamento" class="alert alert-danger" style="display: none;">
                    Não foi possível carregar os detalhes do concurso. Por favor, tente novamente.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <a href="#" id="btn-comprar-bilhete" class="btn btn-success">
                    <i class="fas fa-shopping-cart"></i> Comprar Bilhete
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?> 