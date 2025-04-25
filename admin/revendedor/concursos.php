<?php
// Definir o título da página
$page_title = "Concursos Disponíveis";

// Verificar permissões
require_once '../auth.php';
requireAccess(2); // Nível mínimo: revendedor

// Incluir o cabeçalho
require_once '../templates/header.php';

// Incluir modelo de concurso
require_once '../models/ConcursoModel.php';
$concursoModel = new ConcursoModel();

// Incluir modelo de bilhete para estatísticas
require_once '../models/BilheteModel.php';
$bilheteModel = new BilheteModel();

// Recuperar o ID do vendedor da sessão
$vendedor_id = $_SESSION['usuario_id'] ?? 0;

// Definir opções de filtro
$status = isset($_GET['status']) ? $_GET['status'] : '';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 10;

// Configurar filtros para consulta
$filtros = [
    'offset' => ($pagina - 1) * $por_pagina,
    'limite' => $por_pagina
];

// Adicionar filtro de status se fornecido
if (!empty($status)) {
    $filtros['status'] = $status;
}

// Obter concursos com os filtros
$concursos = $concursoModel->listarConcursosComFiltros($filtros);

// Contagem total para paginação
$total_concursos = $concursoModel->contarConcursosComFiltros([
    'status' => $status
]);

// Calcular total de páginas
$total_paginas = ceil($total_concursos / $por_pagina);

// Obter estatísticas de vendas por concurso para o vendedor
$estatisticas_vendas = [];
if (!empty($concursos)) {
    foreach ($concursos as $concurso) {
        $estatisticas_vendas[$concurso['id']] = [
            'total_bilhetes' => $bilheteModel->contarBilhetesComFiltros([
                'vendedor_id' => $vendedor_id,
                'concurso_id' => $concurso['id']
            ]),
            'valor_total' => $bilheteModel->calcularValorTotalPorConcurso($vendedor_id, $concurso['id'])
        ];
    }
}
?>

<div class="content-header">
    <div class="content-title">
        <h1><?php echo $page_title; ?></h1>
        <p>Visualize os concursos e inicie vendas de bilhetes</p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title">Filtros</h5>
    </div>
    <div class="card-body">
        <form action="" method="get" class="row g-3">
            <div class="col-md-4">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="em_andamento" <?php echo $status === 'em_andamento' ? 'selected' : ''; ?>>Em andamento</option>
                    <option value="finalizado" <?php echo $status === 'finalizado' ? 'selected' : ''; ?>>Finalizado</option>
                    <option value="cancelado" <?php echo $status === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="concursos.php" class="btn btn-secondary">Limpar Filtros</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Lista de Concursos</h5>
            <span class="badge bg-primary"><?php echo $total_concursos; ?> concursos encontrados</span>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($concursos)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Nenhum concurso encontrado com os filtros selecionados.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Concurso</th>
                            <th>Data Início</th>
                            <th>Data Fim</th>
                            <th>Premiação</th>
                            <th>Status</th>
                            <th>Vendas</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($concursos as $concurso): ?>
                            <tr>
                                <td>
                                    #<?php echo $concurso['numero']; ?> - <?php echo htmlspecialchars($concurso['nome']); ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($concurso['data_inicio'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($concurso['data_fim'])); ?></td>
                                <td>R$ <?php echo number_format($concurso['valor_premios'], 2, ',', '.'); ?></td>
                                <td>
                                    <?php 
                                    $status_class = '';
                                    $status_text = '';
                                    switch ($concurso['status']) {
                                        case 'em_andamento':
                                            $status_class = 'bg-success';
                                            $status_text = 'Em andamento';
                                            break;
                                        case 'finalizado':
                                            $status_class = 'bg-primary';
                                            $status_text = 'Finalizado';
                                            break;
                                        case 'cancelado':
                                            $status_class = 'bg-danger';
                                            $status_text = 'Cancelado';
                                            break;
                                        default:
                                            $status_class = 'bg-secondary';
                                            $status_text = ucfirst($concurso['status']);
                                    }
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    if (isset($estatisticas_vendas[$concurso['id']])) {
                                        echo $estatisticas_vendas[$concurso['id']]['total_bilhetes'] . ' bilhetes<br>';
                                        echo 'R$ ' . number_format($estatisticas_vendas[$concurso['id']]['valor_total'], 2, ',', '.');
                                    } else {
                                        echo "Sem vendas";
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="detalhes-concurso.php?id=<?php echo $concurso['id']; ?>" class="btn btn-sm btn-info" title="Ver Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <?php if ($concurso['status'] == 'em_andamento'): ?>
                                        <a href="vender-bilhete.php?concurso_id=<?php echo $concurso['id']; ?>" class="btn btn-sm btn-success" title="Vender Bilhete">
                                            <i class="fas fa-ticket-alt"></i>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <a href="bilhetes-concurso.php?concurso_id=<?php echo $concurso['id']; ?>" class="btn btn-sm btn-primary" title="Ver Bilhetes Vendidos">
                                            <i class="fas fa-list"></i>
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
                        <a class="page-link" href="?pagina=<?php echo $pagina - 1; ?>&status=<?php echo $status; ?>" aria-label="Anterior">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?>&status=<?php echo $status; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $pagina + 1; ?>&status=<?php echo $status; ?>" aria-label="Próximo">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Resumo de concursos -->
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Concursos Ativos</h5>
                <h2 class="card-value">
                    <?php 
                    // Contar concursos ativos
                    $concursos_ativos = $concursoModel->contarConcursosComFiltros(['status' => 'em_andamento']);
                    echo $concursos_ativos;
                    ?>
                </h2>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total de Bilhetes Vendidos</h5>
                <h2 class="card-value">
                    <?php 
                    // Total de bilhetes vendidos pelo revendedor
                    $total_bilhetes = $bilheteModel->contarBilhetesComFiltros(['vendedor_id' => $vendedor_id]);
                    echo $total_bilhetes;
                    ?>
                </h2>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h5 class="card-title">Total de Vendas</h5>
                <h2 class="card-value">
                    <?php 
                    // Total de vendas do revendedor
                    $total_vendas = $bilheteModel->calcularValorTotalPorVendedor($vendedor_id);
                    echo 'R$ ' . number_format($total_vendas, 2, ',', '.');
                    ?>
                </h2>
            </div>
        </div>
    </div>
</div>

<!-- Próximos sorteios -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title">Próximos Sorteios</h5>
    </div>
    <div class="card-body">
        <?php 
        $proximos_sorteios = $concursoModel->listarProximosSorteios(5);
        if (empty($proximos_sorteios)): 
        ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Não há sorteios programados.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Concurso</th>
                            <th>Data do Sorteio</th>
                            <th>Prêmio Estimado</th>
                            <th>Tempo Restante</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proximos_sorteios as $sorteio): ?>
                            <tr>
                                <td>#<?php echo $sorteio['numero']; ?> - <?php echo htmlspecialchars($sorteio['nome']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($sorteio['data_sorteio'])); ?></td>
                                <td>R$ <?php echo number_format($sorteio['valor_premios'], 2, ',', '.'); ?></td>
                                <td>
                                    <?php 
                                    $data_sorteio = new DateTime($sorteio['data_sorteio']);
                                    $agora = new DateTime();
                                    $intervalo = $agora->diff($data_sorteio);
                                    
                                    if ($intervalo->days > 0) {
                                        echo $intervalo->format('%a dias, %h horas');
                                    } else {
                                        echo $intervalo->format('%h horas, %i minutos');
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
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