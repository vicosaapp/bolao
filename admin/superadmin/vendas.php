<?php
// Definir o título da página
$page_title = "Vendas";

// Verificar permissões
require_once '../auth.php';
requireAccess(3); // Nível mínimo: administrador

// Incluir modelos necessários
require_once '../models/BilheteModel.php';
require_once '../models/ConcursoModel.php';
require_once '../models/UsuarioModel.php';

// Inicializar modelos
$bilheteModel = new BilheteModel();
$concursoModel = new ConcursoModel();
$usuarioModel = new UsuarioModel();

// Configurações de paginação
$paginaAtual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$itensPorPagina = 10;
$offset = ($paginaAtual - 1) * $itensPorPagina;

// Filtros de busca
$filtroStatus = $_GET['status'] ?? null;
$filtroConcurso = $_GET['concurso_id'] ?? null;
$filtroVendedor = $_GET['vendedor_id'] ?? null;
$filtroPeriodo = $_GET['periodo'] ?? null;

// Processar filtros adicionais
$dataInicio = null;
$dataFim = null;

if ($filtroPeriodo) {
    switch ($filtroPeriodo) {
        case 'hoje':
            $dataInicio = date('Y-m-d');
            $dataFim = date('Y-m-d');
            break;
        case 'semana':
            $dataInicio = date('Y-m-d', strtotime('-1 week'));
            $dataFim = date('Y-m-d');
            break;
        case 'mes':
            $dataInicio = date('Y-m-d', strtotime('-1 month'));
            $dataFim = date('Y-m-d');
            break;
        case 'trimestre':
            $dataInicio = date('Y-m-d', strtotime('-3 months'));
            $dataFim = date('Y-m-d');
            break;
    }
}

// Buscar vendas com filtros
$filtros = [
    'status' => $filtroStatus,
    'concurso_id' => $filtroConcurso,
    'vendedor_id' => $filtroVendedor,
    'data_inicio' => $dataInicio,
    'data_fim' => $dataFim,
    'limite' => $itensPorPagina,
    'offset' => $offset
];

$vendas = $bilheteModel->listarBilhetesComFiltros($filtros);
$totalVendas = $bilheteModel->contarBilhetesComFiltros($filtros);
$totalValorVendas = $bilheteModel->calcularTotalVendas();

// Buscar concursos para filtro
$concursos = $concursoModel->listarConcursosComFiltros(['limite' => 100]); // Limitar a 100 concursos para o select

// Calcular total de páginas
$totalPaginas = ceil($totalVendas / $itensPorPagina);

// Incluir cabeçalho
require_once '../templates/header.php';
?>

<div class="content">
    <div class="content-header">
        <h1>Vendas</h1>
        <p class="text-muted">Visualize e gerencie as vendas de bilhetes</p>
    </div>

    <!-- Estatísticas rápidas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total de Vendas</h5>
                    <h3 class="mb-0"><?php echo number_format($totalVendas, 0, ',', '.'); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Valor Total</h5>
                    <h3 class="mb-0">R$ <?php echo number_format($totalValorVendas, 2, ',', '.'); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Todos os Status</option>
                            <option value="pago" <?php echo ($filtroStatus == 'pago') ? 'selected' : ''; ?>>Pago</option>
                            <option value="pendente" <?php echo ($filtroStatus == 'pendente') ? 'selected' : ''; ?>>Pendente</option>
                            <option value="cancelado" <?php echo ($filtroStatus == 'cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Concurso</label>
                        <select name="concurso_id" class="form-select">
                            <option value="">Todos os Concursos</option>
                            <?php foreach ($concursos as $concurso): ?>
                            <option value="<?php echo $concurso['id']; ?>" <?php echo ($filtroConcurso == $concurso['id']) ? 'selected' : ''; ?>>
                                <?php echo $concurso['numero'] . ' - ' . $concurso['nome']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Período</label>
                        <select name="periodo" class="form-select">
                            <option value="">Todo o Período</option>
                            <option value="hoje" <?php echo ($filtroPeriodo == 'hoje') ? 'selected' : ''; ?>>Hoje</option>
                            <option value="semana" <?php echo ($filtroPeriodo == 'semana') ? 'selected' : ''; ?>>Últimos 7 dias</option>
                            <option value="mes" <?php echo ($filtroPeriodo == 'mes') ? 'selected' : ''; ?>>Último mês</option>
                            <option value="trimestre" <?php echo ($filtroPeriodo == 'trimestre') ? 'selected' : ''; ?>>Último trimestre</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Ações</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <a href="vendas.php" class="btn btn-secondary">
                                <i class="fas fa-sync"></i> Limpar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Vendas -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="m-0">Lista de Vendas</h5>
            <div class="card-actions">
                <span class="text-muted">
                    Total: <?php echo number_format($totalVendas, 0, ',', '.'); ?> vendas
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Bilhete</th>
                            <th>Concurso</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Data da Compra</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($vendas)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                Nenhuma venda encontrada.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach($vendas as $venda): ?>
                            <tr>
                                <td><?php echo $venda['numero']; ?></td>
                                <td><?php echo $venda['concurso']; ?></td>
                                <td>R$ <?php echo number_format($venda['valor'], 2, ',', '.'); ?></td>
                                <td>
                                    <span class="badge <?php 
                                        switch($venda['status']) {
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
                                        <?php echo ucfirst($venda['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($venda['data_compra'])); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" 
                                                data-bs-target="#modalDetalhesVenda" 
                                                data-id="<?php echo $venda['id']; ?>">
                                            <i class="fas fa-eye"></i> Ver
                                        </button>
                                        <?php if ($venda['status'] !== 'cancelado'): ?>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" 
                                                data-bs-target="#modalAtualizarStatus" 
                                                data-id="<?php echo $venda['id']; ?>"
                                                data-status="<?php echo $venda['status']; ?>">
                                            <i class="fas fa-edit"></i> Status
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
                        <a class="page-link" href="?pagina=<?php echo $paginaAtual - 1; ?>&status=<?php echo $filtroStatus; ?>&concurso_id=<?php echo $filtroConcurso; ?>&periodo=<?php echo $filtroPeriodo; ?>">
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
                        <a class="page-link" href="?pagina=<?php echo $i; ?>&status=<?php echo $filtroStatus; ?>&concurso_id=<?php echo $filtroConcurso; ?>&periodo=<?php echo $filtroPeriodo; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>

                    <?php if($paginaAtual < $totalPaginas): ?>
                    <li class="page-item">
                        <a class="page-link" href="?pagina=<?php echo $paginaAtual + 1; ?>&status=<?php echo $filtroStatus; ?>&concurso_id=<?php echo $filtroConcurso; ?>&periodo=<?php echo $filtroPeriodo; ?>">
                            Próximo
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Modal Detalhes Venda -->
<div class="modal fade" id="modalDetalhesVenda" tabindex="-1" aria-labelledby="tituloModalDetalhesVenda" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalDetalhesVenda">Detalhes da Venda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
                <div id="detalhesVendaConteudo" class="d-none">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Número do Bilhete:</strong> <span id="detalhesNumeroBilhete"></span></p>
                            <p><strong>Concurso:</strong> <span id="detalhesConcurso"></span></p>
                            <p><strong>Valor:</strong> <span id="detalhesValor"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> <span id="detalhesStatus"></span></p>
                            <p><strong>Data da Compra:</strong> <span id="detalhesDataCompra"></span></p>
                            <p><strong>Vendedor:</strong> <span id="detalhesVendedor"></span></p>
                        </div>
                    </div>
                    <hr>
                    <h6>Apostador</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nome:</strong> <span id="detalhesNomeApostador"></span></p>
                            <p><strong>Telefone:</strong> <span id="detalhesTelefoneApostador"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Email:</strong> <span id="detalhesEmailApostador"></span></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <a href="#" id="linkImprimirBilhete" class="btn btn-primary" target="_blank">
                    <i class="fas fa-print"></i> Imprimir Bilhete
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Atualizar Status -->
<div class="modal fade" id="modalAtualizarStatus" tabindex="-1" aria-labelledby="tituloModalAtualizarStatus" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalAtualizarStatus">Atualizar Status da Venda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="formAtualizarStatus" method="POST" action="ajax/atualizar_status_venda.php">
                <div class="modal-body">
                    <input type="hidden" id="vendaId" name="id">
                    <div class="mb-3">
                        <label for="status" class="form-label">Novo Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pago">Pago</option>
                            <option value="pendente">Pendente</option>
                            <option value="cancelado">Cancelado</option>
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
// Incluir rodapé
require_once '../templates/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal de atualização de status
    const modalAtualizarStatus = document.getElementById('modalAtualizarStatus');
    if (modalAtualizarStatus) {
        modalAtualizarStatus.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const status = button.getAttribute('data-status');
            
            document.getElementById('vendaId').value = id;
            document.getElementById('status').value = status;
        });
    }
    
    // Modal de detalhes da venda
    const modalDetalhesVenda = document.getElementById('modalDetalhesVenda');
    if (modalDetalhesVenda) {
        modalDetalhesVenda.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const spinner = modalDetalhesVenda.querySelector('.spinner-border');
            const conteudo = document.getElementById('detalhesVendaConteudo');
            
            // Resetar e mostrar spinner
            conteudo.classList.add('d-none');
            spinner.classList.remove('d-none');
            
            // Carregar detalhes da venda via AJAX
            fetch(`ajax/obter_detalhes_venda.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    // Preencher detalhes
                    document.getElementById('detalhesNumeroBilhete').textContent = data.numero;
                    document.getElementById('detalhesConcurso').textContent = data.concurso;
                    document.getElementById('detalhesValor').textContent = `R$ ${parseFloat(data.valor).toFixed(2).replace('.', ',')}`;
                    document.getElementById('detalhesStatus').textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
                    document.getElementById('detalhesDataCompra').textContent = new Date(data.data_compra).toLocaleString('pt-BR');
                    document.getElementById('detalhesVendedor').textContent = data.vendedor || 'Não informado';
                    
                    // Preencher dados do apostador
                    document.getElementById('detalhesNomeApostador').textContent = data.apostador?.nome || 'Não informado';
                    document.getElementById('detalhesTelefoneApostador').textContent = data.apostador?.telefone || 'Não informado';
                    document.getElementById('detalhesEmailApostador').textContent = data.apostador?.email || 'Não informado';
                    
                    // Configurar link de impressão
                    document.getElementById('linkImprimirBilhete').href = `imprimir_bilhete.php?id=${id}`;
                    
                    // Esconder spinner e mostrar conteúdo
                    spinner.classList.add('d-none');
                    conteudo.classList.remove('d-none');
                })
                .catch(error => {
                    console.error('Erro ao carregar detalhes:', error);
                    spinner.classList.add('d-none');
                    conteudo.innerHTML = '<div class="alert alert-danger">Erro ao carregar detalhes da venda.</div>';
                    conteudo.classList.remove('d-none');
                });
        });
    }
    
    // Formulário de atualização de status
    const formAtualizarStatus = document.getElementById('formAtualizarStatus');
    if (formAtualizarStatus) {
        formAtualizarStatus.addEventListener('submit', function(event) {
            event.preventDefault();
            
            const formData = new FormData(formAtualizarStatus);
            
            fetch(formAtualizarStatus.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    // Fechar modal
                    bootstrap.Modal.getInstance(modalAtualizarStatus).hide();
                    
                    // Mostrar alerta de sucesso
                    alert('Status atualizado com sucesso!');
                    
                    // Recarregar página
                    window.location.reload();
                } else {
                    alert('Erro ao atualizar status: ' + data.mensagem);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao processar a solicitação.');
            });
        });
    }
});
</script> 