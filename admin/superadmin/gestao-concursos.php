<?php
// Definir o título da página
$page_title = "Gestão de Concursos";

// CSS específico para esta página
$extraCSS = [
    '../assets/css/gestao-concursos.css'
];

// Verificar permissões
require_once '../auth.php';
requireAccess(3); // Nível mínimo: administrador (3)

// Incluir modelos necessários
require_once '../models/ConcursoModel.php';

// Incluir o cabeçalho
require_once '../templates/header.php';

// Inicializar modelo
$concursoModel = new ConcursoModel();

// Obter lista de concursos
$concursos = $concursoModel->listarConcursos();

// Obter próximo concurso
$proximoConcurso = $concursoModel->obterProximoConcurso();

// Obter concurso atual
$concursoAtual = $concursoModel->obterConcursoAtual();
?>

<div class="content">
    <div class="content-header">
        <h1>Gestão de Concursos</h1>
        <p class="text-muted">Gerencie os concursos, configure sorteios e acompanhe resultados.</p>
    </div>
    
    <!-- Cartões de status -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Concurso Atual</h5>
                    <?php if ($concursoAtual): ?>
                    <div class="concurso-info">
                        <div class="concurso-numero">#<?php echo $concursoAtual['numero']; ?></div>
                        <div class="concurso-data">
                            <i class="fas fa-calendar-alt"></i> 
                            <?php echo date('d/m/Y', strtotime($concursoAtual['data_fim'])); ?>
                        </div>
                        <div class="concurso-status">
                            <span class="badge bg-<?php echo $concursoAtual['status'] == 'em_andamento' ? 'success' : 'warning'; ?>">
                                <?php echo ucfirst($concursoAtual['status']); ?>
                            </span>
                        </div>
                        <div class="concurso-acoes mt-3">
                            <a href="detalhes-concurso.php?id=<?php echo $concursoAtual['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> Detalhes
                            </a>
                            <?php if ($concursoAtual['status'] == 'em_andamento'): ?>
                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#realizarSorteioModal" data-id="<?php echo $concursoAtual['id']; ?>">
                                <i class="fas fa-random"></i> Sortear
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> Nenhum concurso ativo no momento.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Próximo Concurso</h5>
                    <?php if ($proximoConcurso): ?>
                    <div class="concurso-info">
                        <div class="concurso-numero">#<?php echo $proximoConcurso['numero']; ?></div>
                        <div class="concurso-data">
                            <i class="fas fa-calendar-alt"></i> 
                            <?php echo date('d/m/Y', strtotime($proximoConcurso['data_fim'])); ?>
                        </div>
                        <div class="concurso-status">
                            <span class="badge bg-info">Programado</span>
                        </div>
                        <div class="concurso-acoes mt-3">
                            <a href="detalhes-concurso.php?id=<?php echo $proximoConcurso['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> Detalhes
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#cancelarConcursoModal" data-id="<?php echo $proximoConcurso['id']; ?>">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Nenhum concurso programado.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Ações Rápidas</h5>
                    <div class="quick-actions">
                        <button type="button" class="btn btn-success mb-2" data-bs-toggle="modal" data-bs-target="#novoConcursoModal">
                            <i class="fas fa-plus-circle"></i> Novo Concurso
                        </button>
                        <a href="resultados.php" class="btn btn-info mb-2">
                            <i class="fas fa-trophy"></i> Resultados
                        </a>
                        <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#configurarPremiosModal">
                            <i class="fas fa-money-bill-wave"></i> Configurar Prêmios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Lista de concursos -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="m-0">Histórico de Concursos</h5>
            <div class="card-actions">
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm" placeholder="Buscar concurso" id="buscarConcurso">
                    <button class="btn btn-sm btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Data do Sorteio</th>
                            <th>Status</th>
                            <th>Bilhetes Vendidos</th>
                            <th>Valor Arrecadado</th>
                            <th>Premiação Total</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($concursos)): ?>
                            <?php foreach ($concursos as $concurso): ?>
                            <tr>
                                <td>#<?php echo $concurso['numero']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($concurso['data_fim'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        if ($concurso['status'] == 'em_andamento') echo 'success';
                                        elseif ($concurso['status'] == 'finalizado') echo 'primary';
                                        elseif ($concurso['status'] == 'cancelado') echo 'danger';
                                        else echo 'warning';
                                    ?>">
                                        <?php echo ucfirst($concurso['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($concurso['bilhetes_vendidos'] ?? 0, 0, ',', '.'); ?></td>
                                <td>R$ <?php echo number_format($concurso['valor_arrecadado'] ?? 0, 2, ',', '.'); ?></td>
                                <td>R$ <?php echo number_format($concurso['premiacao_total'] ?? 0, 2, ',', '.'); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="detalhes-concurso.php?id=<?php echo $concurso['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($concurso['status'] == 'em_andamento'): ?>
                                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#realizarSorteioModal" data-id="<?php echo $concurso['id']; ?>">
                                            <i class="fas fa-random"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php if ($concurso['status'] != 'finalizado' && $concurso['status'] != 'cancelado'): ?>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#cancelarConcursoModal" data-id="<?php echo $concurso['id']; ?>">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-3">
                                    <p class="text-muted mb-0">Nenhum concurso cadastrado.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer">
            <nav>
                <ul class="pagination pagination-sm justify-content-end mb-0">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">Anterior</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Próximo</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Modal Novo Concurso -->
<div class="modal fade" id="novoConcursoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Criar Novo Concurso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNovoConcurso" action="actions/criar_concurso.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Número do Concurso</label>
                        <input type="number" class="form-control" name="numero" required min="1">
                        <small class="form-text text-muted">O número deve ser único e sequencial.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Data do Sorteio</label>
                        <input type="date" class="form-control" name="data_fim" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Hora do Sorteio</label>
                        <input type="time" class="form-control" name="hora_sorteio" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Valor do Bilhete (R$)</label>
                        <input type="number" class="form-control" name="valor_bilhete" required min="1" step="0.01">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea class="form-control" name="descricao" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarNovoConcurso">Criar Concurso</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Realizar Sorteio -->
<div class="modal fade" id="realizarSorteioModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Realizar Sorteio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Atenção: Esta ação irá encerrar as vendas de bilhetes e iniciar o processo de sorteio. Esta ação não pode ser desfeita.
                </div>
                
                <form id="formRealizarSorteio" action="actions/realizar_sorteio.php" method="POST">
                    <input type="hidden" name="concurso_id" id="sorteioConcursoId">
                    
                    <div class="mb-3">
                        <label class="form-label">Método de Sorteio</label>
                        <select class="form-select" name="metodo_sorteio" required>
                            <option value="manual">Manual (inserir números manualmente)</option>
                            <option value="automatico">Automático (gerado pelo sistema)</option>
                        </select>
                    </div>
                    
                    <div id="camposSorteioManual">
                        <div class="mb-3">
                            <label class="form-label">Números Sorteados</label>
                            <input type="text" class="form-control" name="numeros_sorteados" placeholder="Ex: 01,05,10,15,20,25">
                            <small class="form-text text-muted">Insira os números separados por vírgula.</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btnConfirmarSorteio">Realizar Sorteio</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cancelar Concurso -->
<div class="modal fade" id="cancelarConcursoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancelar Concurso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Atenção: Esta ação irá cancelar o concurso. Todos os bilhetes vendidos serão cancelados e os valores deverão ser reembolsados. Esta ação não pode ser desfeita.
                </div>
                
                <form id="formCancelarConcurso" action="actions/cancelar_concurso.php" method="POST">
                    <input type="hidden" name="concurso_id" id="cancelarConcursoId">
                    
                    <div class="mb-3">
                        <label class="form-label">Motivo do Cancelamento</label>
                        <textarea class="form-control" name="motivo" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Desistir</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarCancelamento">Confirmar Cancelamento</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Configurar Prêmios -->
<div class="modal fade" id="configurarPremiosModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Configurar Prêmios</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formConfigurarPremios" action="actions/configurar_premios.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Concurso</label>
                        <select class="form-select" name="concurso_id" required>
                            <option value="">Selecione um concurso</option>
                            <?php foreach ($concursos as $concurso): ?>
                                <?php if ($concurso['status'] == 'em_andamento'): ?>
                                <option value="<?php echo $concurso['id']; ?>">
                                    #<?php echo $concurso['numero']; ?> - <?php echo date('d/m/Y', strtotime($concurso['data_fim'])); ?>
                                </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="premio-container">
                        <h6>Configuração de Prêmios</h6>
                        
                        <div class="premio-item row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">1º Prêmio</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" class="form-control" name="premio_valor[]" min="0" step="0.01" required>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Quantidade</label>
                                <input type="number" class="form-control" name="premio_quantidade[]" min="1" value="1" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Descrição</label>
                                <input type="text" class="form-control" name="premio_descricao[]" required>
                            </div>
                        </div>
                        
                        <div id="premiosAdicionais"></div>
                        
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnAdicionarPremio">
                            <i class="fas fa-plus-circle"></i> Adicionar Prêmio
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarPremios">Salvar Configuração</button>
            </div>
        </div>
    </div>
</div>

<?php
// Scripts específicos para a página
$page_scripts = [
    '../assets/js/gestao-concursos.js'
];

// Incluir o rodapé
require_once '../templates/footer.php';
?> 