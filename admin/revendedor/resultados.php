<?php
// Definir o título da página
$page_title = "Resultados dos Concursos";

// Verificar permissões
require_once '../auth.php';
requireAccess(2); // Nível mínimo: revendedor

// Incluir o cabeçalho
require_once '../templates/header.php';

// Incluir modelos necessários
require_once '../models/ConcursoModel.php';
require_once '../models/BilheteModel.php';
require_once '../models/SorteioModel.php';

// Instanciar os modelos
$concursoModel = new ConcursoModel();
$bilheteModel = new BilheteModel();
$sorteioModel = new SorteioModel();

// Recuperar o ID do vendedor da sessão
$vendedor_id = $_SESSION['usuario_id'] ?? 0;

// Parâmetros de paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 5;

// Parâmetros para filtro
$concurso_id = isset($_GET['concurso_id']) ? (int)$_GET['concurso_id'] : 0;

// Obter concursos finalizados
if ($concurso_id > 0) {
    // Se um concurso específico foi solicitado
    $concurso = $concursoModel->obterConcursoPorId($concurso_id);
    $concursos = $concurso ? [$concurso] : [];
    $total_concursos = $concurso ? 1 : 0;
} else {
    // Listar todos os concursos finalizados (paginados)
    $filtros = [
        'status' => 'finalizado',
        'offset' => ($pagina - 1) * $por_pagina,
        'limite' => $por_pagina
    ];
    
    $concursos = $concursoModel->listarConcursosComFiltros($filtros);
    $total_concursos = $concursoModel->contarConcursosComFiltros(['status' => 'finalizado']);
}

// Calcular total de páginas
$total_paginas = ceil($total_concursos / $por_pagina);

// Obter estatísticas de vendas para o revendedor atual
$estatisticas_vendedor = [];
if (!empty($concursos)) {
    foreach ($concursos as $concurso) {
        $estatisticas_vendedor[$concurso['id']] = [
            'total_bilhetes' => $bilheteModel->contarBilhetesComFiltros([
                'vendedor_id' => $vendedor_id,
                'concurso_id' => $concurso['id']
            ]),
            'valor_total' => $bilheteModel->calcularValorTotalPorConcurso($vendedor_id, $concurso['id']),
            // Bilhetes premiados vendidos pelo revendedor
            'bilhetes_premiados' => $bilheteModel->contarBilhetesComFiltros([
                'vendedor_id' => $vendedor_id,
                'concurso_id' => $concurso['id'],
                'status' => 'premiado'
            ])
        ];
    }
}

// Obter lista de concursos para o filtro
$todos_concursos_finalizados = $concursoModel->listarConcursosComFiltros(['status' => 'finalizado', 'limite' => 100]);
?>

<div class="content-header">
    <div class="content-title">
        <h1><?php echo $page_title; ?></h1>
        <p>Visualize os resultados dos concursos finalizados</p>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title">Filtrar Resultados</h5>
    </div>
    <div class="card-body">
        <form action="" method="get" class="row g-3">
            <div class="col-md-6">
                <label for="concurso_id" class="form-label">Concurso</label>
                <select name="concurso_id" id="concurso_id" class="form-select">
                    <option value="">Todos os concursos finalizados</option>
                    <?php foreach ($todos_concursos_finalizados as $concurso_item): ?>
                        <option value="<?php echo $concurso_item['id']; ?>" <?php echo $concurso_id == $concurso_item['id'] ? 'selected' : ''; ?>>
                            #<?php echo $concurso_item['numero']; ?> - <?php echo htmlspecialchars($concurso_item['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="resultados.php" class="btn btn-secondary">Limpar Filtros</a>
            </div>
        </form>
    </div>
</div>

<?php if (empty($concursos)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Nenhum concurso finalizado encontrado.
    </div>
<?php else: ?>
    <!-- Resultados dos concursos -->
    <?php foreach ($concursos as $concurso): ?>
        <?php 
        // Obter sorteios deste concurso
        $sorteios = $sorteioModel->listarSorteiosPorConcurso($concurso['id']);
        
        // Obter bilhetes premiados deste concurso
        $bilhetes_premiados = $bilheteModel->listarBilhetesComFiltros([
            'concurso_id' => $concurso['id'],
            'status' => 'premiado',
            'limite' => 10
        ]);
        ?>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Concurso #<?php echo $concurso['numero']; ?> - <?php echo htmlspecialchars($concurso['nome']); ?></h5>
                <div class="card-subtitle text-muted">
                    Finalizado em <?php echo date('d/m/Y', strtotime($concurso['data_fim'])); ?>
                </div>
            </div>
            
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <h5 class="info-title">Informações do Concurso</h5>
                            <ul class="list-unstyled">
                                <li><strong>Data do sorteio:</strong> <?php echo date('d/m/Y', strtotime($concurso['data_fim'])); ?></li>
                                <li><strong>Valor da premiação:</strong> R$ <?php echo number_format($concurso['valor_premios'], 2, ',', '.'); ?></li>
                                <li><strong>Total de bilhetes vendidos:</strong> <?php echo $concurso['bilhetes_vendidos'] ?? 'N/A'; ?></li>
                                <li><strong>Valor arrecadado:</strong> R$ <?php echo number_format($concurso['valor_arrecadado'] ?? 0, 2, ',', '.'); ?></li>
                            </ul>

                            <h5 class="info-title mt-4">Suas Vendas</h5>
                            <ul class="list-unstyled">
                                <li><strong>Bilhetes vendidos:</strong> <?php echo $estatisticas_vendedor[$concurso['id']]['total_bilhetes']; ?></li>
                                <li><strong>Valor total:</strong> R$ <?php echo number_format($estatisticas_vendedor[$concurso['id']]['valor_total'], 2, ',', '.'); ?></li>
                                <li><strong>Bilhetes premiados:</strong> <?php echo $estatisticas_vendedor[$concurso['id']]['bilhetes_premiados']; ?></li>
                            </ul>
                            
                            <div class="mt-3">
                                <a href="bilhetes-concurso.php?concurso_id=<?php echo $concurso['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-list"></i> Ver meus bilhetes deste concurso
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <!-- Sorteios do concurso -->
                        <h5 class="mb-3">Sorteios Realizados</h5>
                        <?php if (empty($sorteios)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Nenhum sorteio registrado para este concurso.
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($sorteios as $sorteio): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0"><?php echo htmlspecialchars($sorteio['descricao']); ?></h6>
                                                <div class="small text-muted">
                                                    <?php echo date('d/m/Y H:i', strtotime($sorteio['data_sorteio'])); ?>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="sorteio-numeros">
                                                    <?php 
                                                    // Obter números sorteados
                                                    $numeros = $sorteioModel->obterNumerosSorteados($sorteio['id']);
                                                    if (!empty($numeros)): 
                                                    ?>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <?php foreach ($numeros as $numero): ?>
                                                                <div class="numero-sorteado"><?php echo $numero; ?></div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="alert alert-warning py-1">
                                                            Números não disponíveis
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Bilhetes premiados -->
                        <h5 class="mt-4 mb-3">Bilhetes Premiados</h5>
                        <?php if (empty($bilhetes_premiados)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Nenhum bilhete premiado registrado para este concurso.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Bilhete</th>
                                            <th>Cliente</th>
                                            <th>Pontos</th>
                                            <th>Prêmio</th>
                                            <th>Vendido por</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bilhetes_premiados as $bilhete): ?>
                                            <tr>
                                                <td><?php echo $bilhete['numero']; ?></td>
                                                <td>
                                                    <?php 
                                                    $nome_cliente = '';
                                                    if (!empty($bilhete['apostador_nome'])) {
                                                        $nome_cliente = $bilhete['apostador_nome'];
                                                    } elseif (!empty($bilhete['usuario_nome'])) {
                                                        $nome_cliente = $bilhete['usuario_nome'];
                                                    } else {
                                                        $nome_cliente = 'Cliente #' . ($bilhete['apostador_id'] ?? $bilhete['usuario_id'] ?? 'N/A');
                                                    }
                                                    echo htmlspecialchars($nome_cliente);
                                                    ?>
                                                </td>
                                                <td><?php echo $bilhete['pontos'] ?? 'N/A'; ?></td>
                                                <td>R$ <?php echo number_format($bilhete['valor_premio'] ?? 0, 2, ',', '.'); ?></td>
                                                <td>
                                                    <?php 
                                                    $nome_vendedor = '';
                                                    if (isset($bilhete['vendedor_id']) && $bilhete['vendedor_id'] == $vendedor_id) {
                                                        echo '<span class="badge bg-success">Você</span>';
                                                    } else {
                                                        echo $bilhete['vendedor_nome'] ?? 'Vendedor #' . ($bilhete['vendedor_id'] ?? 'N/A');
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
            </div>
            
            <div class="card-footer">
                <a href="detalhes-concurso.php?id=<?php echo $concurso['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-eye"></i> Ver detalhes completos
                </a>
            </div>
        </div>
    <?php endforeach; ?>
    
    <!-- Paginação -->
    <?php if ($total_paginas > 1 && $concurso_id == 0): ?>
    <nav aria-label="Navegação de página" class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?pagina=<?php echo $pagina - 1; ?>&concurso_id=<?php echo $concurso_id; ?>" aria-label="Anterior">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            
            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>">
                <a class="page-link" href="?pagina=<?php echo $i; ?>&concurso_id=<?php echo $concurso_id; ?>">
                    <?php echo $i; ?>
                </a>
            </li>
            <?php endfor; ?>
            
            <li class="page-item <?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?pagina=<?php echo $pagina + 1; ?>&concurso_id=<?php echo $concurso_id; ?>" aria-label="Próximo">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
<?php endif; ?>

<!-- Estatísticas e resumo -->
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total de Concursos</h5>
                <h2 class="card-value">
                    <?php echo $concursoModel->contarConcursosComFiltros(['status' => 'finalizado']); ?>
                </h2>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Bilhetes Premiados</h5>
                <h2 class="card-value">
                    <?php 
                    $bilhetes_premiados_total = $bilheteModel->contarBilhetesComFiltros([
                        'vendedor_id' => $vendedor_id,
                        'status' => 'premiado'
                    ]);
                    echo $bilhetes_premiados_total;
                    ?>
                </h2>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h5 class="card-title">Comissões Recebidas</h5>
                <h2 class="card-value">
                    <?php 
                    // Aqui você pode adicionar lógica para calcular comissões totais, 
                    // caso tenha um modelo específico para isso
                    echo 'R$ ' . number_format(0, 2, ',', '.');
                    ?>
                </h2>
            </div>
        </div>
    </div>
</div>

<style>
    .info-title {
        font-size: 1.1rem;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 0.5rem;
        margin-bottom: 0.75rem;
    }
    
    .info-box {
        padding: 1rem;
        background-color: #f9f9f9;
        border-radius: 0.25rem;
        height: 100%;
    }
    
    .numero-sorteado {
        width: 2.5rem;
        height: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #007bff;
        color: white;
        border-radius: 50%;
        font-weight: bold;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

<?php
// Incluir o rodapé
require_once '../templates/footer.php';
?> 