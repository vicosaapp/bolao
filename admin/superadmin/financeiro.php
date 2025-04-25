<?php
// Definir o título da página
$page_title = "Gestão Financeira";

// CSS específico para esta página
$extraCSS = [
    '../assets/css/financeiro.css'
];

// JavaScript específico para esta página
$extraJS = [
    'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js'
];

// Verificar permissões
require_once '../auth.php';
requireAccess(3); // Nível mínimo: administrador (3)

// Incluir modelos necessários
require_once '../models/FinanceiroModel.php';
require_once '../models/ConcursoModel.php';

// Incluir o cabeçalho
require_once '../templates/header.php';

// Inicializar modelos
$financeiroModel = new FinanceiroModel();
$concursoModel = new ConcursoModel();

// Obter filtros da URL
$filtros = [
    'tipo' => $_GET['tipo'] ?? null,
    'categoria' => $_GET['categoria'] ?? null,
    'data_inicio' => $_GET['data_inicio'] ?? date('Y-m-01'), // Primeiro dia do mês atual
    'data_fim' => $_GET['data_fim'] ?? date('Y-m-t'), // Último dia do mês atual
    'status' => $_GET['status'] ?? null,
    'concurso_id' => $_GET['concurso_id'] ?? null,
    'busca' => $_GET['busca'] ?? null
];

// Obter lista de concursos para filtro
$concursos = $concursoModel->listarConcursos();

// Obter categorias para filtro
$categorias = $financeiroModel->obterCategorias();

// Obter resumo financeiro
$resumo = $financeiroModel->obterResumoFinanceiro($filtros);

// Obter transações com filtros
$transacoes = $financeiroModel->listarTransacoes($filtros);

// Obter dados para gráfico
$dadosGrafico = $financeiroModel->obterDadosGrafico('mensal', date('Y'), date('m'));
?>

<div class="content">
    <div class="content-header">
        <h1>Gestão Financeira</h1>
        <p class="text-muted">Gerencie receitas, despesas e acompanhe o fluxo financeiro do sistema.</p>
    </div>
    
    <!-- Cards de estatísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Receitas</h5>
                            <h2 class="mt-3 mb-0">R$ <?= number_format($resumo['total_receitas'] ?? 0, 2, ',', '.') ?></h2>
                        </div>
                        <div class="icon-bg">
                            <i class="fas fa-arrow-up fa-3x"></i>
                        </div>
                    </div>
                    <p class="card-text mt-3">
                        <small><?= $resumo['num_receitas'] ?? 0 ?> transações</small>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Despesas</h5>
                            <h2 class="mt-3 mb-0">R$ <?= number_format($resumo['total_despesas'] ?? 0, 2, ',', '.') ?></h2>
                        </div>
                        <div class="icon-bg">
                            <i class="fas fa-arrow-down fa-3x"></i>
                        </div>
                    </div>
                    <p class="card-text mt-3">
                        <small><?= $resumo['num_despesas'] ?? 0 ?> transações</small>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card <?= ($resumo['saldo'] ?? 0) >= 0 ? 'bg-primary' : 'bg-warning' ?> text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Saldo</h5>
                            <h2 class="mt-3 mb-0">R$ <?= number_format(abs($resumo['saldo'] ?? 0), 2, ',', '.') ?></h2>
                        </div>
                        <div class="icon-bg">
                            <i class="fas fa-wallet fa-3x"></i>
                        </div>
                    </div>
                    <p class="card-text mt-3">
                        <small><?= ($resumo['saldo'] ?? 0) >= 0 ? 'Positivo' : 'Negativo' ?></small>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total de Transações</h5>
                            <h2 class="mt-3 mb-0"><?= number_format($resumo['total_transacoes'] ?? 0, 0, ',', '.') ?></h2>
                        </div>
                        <div class="icon-bg">
                            <i class="fas fa-exchange-alt fa-3x"></i>
                        </div>
                    </div>
                    <p class="card-text mt-3">
                        <small>Última: <?= $resumo['ultima_transacao'] ? date('d/m/Y', strtotime($resumo['ultima_transacao'])) : 'N/A' ?></small>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ações rápidas e gráfico -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-0">Ações Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <button class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#novaTransacaoModal" data-tipo="receita">
                            <i class="fas fa-plus-circle"></i> Nova Receita
                        </button>
                        <button class="btn btn-danger btn-lg" data-bs-toggle="modal" data-bs-target="#novaTransacaoModal" data-tipo="despesa">
                            <i class="fas fa-minus-circle"></i> Nova Despesa
                        </button>
                        <button class="btn btn-primary btn-lg" data-bs-toggle="collapse" data-bs-target="#collapseFiltros">
                            <i class="fas fa-filter"></i> Filtrar Transações
                        </button>
                        <button class="btn btn-info btn-lg" id="btnExportarRelatorio">
                            <i class="fas fa-file-export"></i> Exportar Relatório
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">Fluxo Financeiro</h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary active" id="btnGraficoMensal">Mensal</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnGraficoAnual">Anual</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="graficoFinanceiro" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="collapse mb-4" id="collapseFiltros">
        <div class="card">
            <div class="card-header">
                <h5 class="m-0">Filtros</h5>
            </div>
            <div class="card-body">
                <form action="" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" name="tipo">
                            <option value="">Todos</option>
                            <option value="receita" <?= $filtros['tipo'] == 'receita' ? 'selected' : '' ?>>Receitas</option>
                            <option value="despesa" <?= $filtros['tipo'] == 'despesa' ? 'selected' : '' ?>>Despesas</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Categoria</label>
                        <select class="form-select" name="categoria" id="selectCategoria">
                            <option value="">Todas</option>
                            <!-- As categorias serão carregadas via JavaScript dependendo do tipo selecionado -->
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">Todos</option>
                            <option value="pendente" <?= $filtros['status'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                            <option value="concluida" <?= $filtros['status'] == 'concluida' ? 'selected' : '' ?>>Concluída</option>
                            <option value="cancelada" <?= $filtros['status'] == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Concurso</label>
                        <select class="form-select" name="concurso_id">
                            <option value="">Todos</option>
                            <?php foreach ($concursos as $concurso): ?>
                            <option value="<?= $concurso['id'] ?>" <?= $filtros['concurso_id'] == $concurso['id'] ? 'selected' : '' ?>>
                                #<?= $concurso['numero'] ?> (<?= date('d/m/Y', strtotime($concurso['data_fim'])) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Data Início</label>
                        <input type="date" class="form-control" name="data_inicio" value="<?= $filtros['data_inicio'] ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Data Fim</label>
                        <input type="date" class="form-control" name="data_fim" value="<?= $filtros['data_fim'] ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Busca</label>
                        <input type="text" class="form-control" name="busca" placeholder="Buscar por descrição..." value="<?= $filtros['busca'] ?>">
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="w-100">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Tabela de Transações -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="m-0">Transações</h5>
            <div class="card-actions">
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#novaTransacaoModal">
                    <i class="fas fa-plus"></i> Nova Transação
                </button>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Categoria</th>
                            <th>Descrição</th>
                            <th>Concurso</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($transacoes)): ?>
                            <?php foreach ($transacoes as $transacao): ?>
                            <tr class="<?= $transacao['tipo'] == 'receita' ? 'table-success' : 'table-danger' ?>">
                                <td><?= $transacao['id'] ?></td>
                                <td><?= date('d/m/Y', strtotime($transacao['data_transacao'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $transacao['tipo'] == 'receita' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($transacao['tipo']) ?>
                                    </span>
                                </td>
                                <td><?= $transacao['categoria'] ?></td>
                                <td><?= $transacao['descricao'] ?></td>
                                <td>
                                    <?php if ($transacao['concurso_id']): ?>
                                        #<?= $transacao['concurso_numero'] ?>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">R$ <?= number_format($transacao['valor'], 2, ',', '.') ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $transacao['status'] == 'concluida' ? 'primary' : 
                                        ($transacao['status'] == 'pendente' ? 'warning' : 'secondary') 
                                    ?>">
                                        <?= ucfirst($transacao['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-info btn-visualizar-transacao" data-id="<?= $transacao['id'] ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-primary btn-editar-transacao" data-id="<?= $transacao['id'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($transacao['status'] != 'cancelada'): ?>
                                        <button type="button" class="btn btn-danger btn-cancelar-transacao" data-id="<?= $transacao['id'] ?>">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-3">
                                    <p class="text-muted mb-0">Nenhuma transação encontrada.</p>
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

<!-- Modal Nova Transação -->
<div class="modal fade" id="novaTransacaoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nova Transação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNovaTransacao" action="actions/salvar_transacao.php" method="POST">
                    <input type="hidden" name="id" id="transacao_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo" id="tipo_receita" value="receita" checked>
                            <label class="btn btn-outline-success" for="tipo_receita">Receita</label>
                            
                            <input type="radio" class="btn-check" name="tipo" id="tipo_despesa" value="despesa">
                            <label class="btn btn-outline-danger" for="tipo_despesa">Despesa</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Categoria</label>
                        <select class="form-select" name="categoria" id="categoria" required>
                            <!-- As categorias serão carregadas via JavaScript dependendo do tipo selecionado -->
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <input type="text" class="form-control" name="descricao" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Valor (R$)</label>
                        <input type="number" class="form-control" name="valor" step="0.01" min="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Data</label>
                        <input type="date" class="form-control" name="data_transacao" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Concurso (opcional)</label>
                        <select class="form-select" name="concurso_id">
                            <option value="">Selecione um concurso</option>
                            <?php foreach ($concursos as $concurso): ?>
                            <option value="<?= $concurso['id'] ?>">
                                #<?= $concurso['numero'] ?> (<?= date('d/m/Y', strtotime($concurso['data_fim'])) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Forma de Pagamento</label>
                        <select class="form-select" name="forma_pagamento">
                            <option value="dinheiro">Dinheiro</option>
                            <option value="cartao_credito">Cartão de Crédito</option>
                            <option value="cartao_debito">Cartão de Débito</option>
                            <option value="transferencia">Transferência Bancária</option>
                            <option value="pix">PIX</option>
                            <option value="boleto">Boleto</option>
                            <option value="outro">Outro</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="pendente">Pendente</option>
                            <option value="concluida">Concluída</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Observações</label>
                        <textarea class="form-control" name="observacao" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formNovaTransacao" class="btn btn-primary">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Incluir o rodapé -->
<?php require_once '../templates/footer.php'; ?>

<!-- JavaScript específico da página -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Categorias
    const categorias = <?= json_encode($categorias) ?>;
    
    // Preencher categorias baseado no tipo selecionado
    function preencherCategorias(tipo) {
        const selectCategoria = document.getElementById('categoria');
        const selectCategoriaFiltro = document.getElementById('selectCategoria');
        
        // Limpar selects
        selectCategoria.innerHTML = '';
        if (selectCategoriaFiltro) {
            selectCategoriaFiltro.innerHTML = '<option value="">Todas</option>';
        }
        
        // Adicionar categorias correspondentes ao tipo
        if (tipo in categorias) {
            for (const [valor, texto] of Object.entries(categorias[tipo])) {
                const option = new Option(texto, valor);
                selectCategoria.appendChild(option.cloneNode(true));
                
                if (selectCategoriaFiltro) {
                    selectCategoriaFiltro.appendChild(option);
                }
            }
        }
    }
    
    // Inicializar com categorias de receita
    preencherCategorias('receita');
    
    // Alternar categorias quando mudar o tipo
    document.querySelectorAll('input[name="tipo"]').forEach(radio => {
        radio.addEventListener('change', function() {
            preencherCategorias(this.value);
        });
    });
    
    // Configurar tipo na abertura do modal
    const novaTransacaoModal = document.getElementById('novaTransacaoModal');
    if (novaTransacaoModal) {
        novaTransacaoModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const tipo = button.getAttribute('data-tipo');
            
            if (tipo) {
                const radioTipo = document.getElementById('tipo_' + tipo);
                if (radioTipo) {
                    radioTipo.checked = true;
                    preencherCategorias(tipo);
                }
            }
        });
    }
    
    // Inicializar gráfico financeiro
    const ctx = document.getElementById('graficoFinanceiro').getContext('2d');
    const dadosGrafico = <?= json_encode($dadosGrafico) ?>;
    
    // Preparar dados para o gráfico
    const labels = dadosGrafico.map(item => item.label);
    const dataReceitas = dadosGrafico.map(item => item.receitas);
    const dataDespesas = dadosGrafico.map(item => item.despesas);
    
    const grafico = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Receitas',
                    data: dataReceitas,
                    backgroundColor: 'rgba(40, 167, 69, 0.6)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Despesas',
                    data: dataDespesas,
                    backgroundColor: 'rgba(220, 53, 69, 0.6)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toFixed(2).replace('.', ',');
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': R$ ' + context.raw.toFixed(2).replace('.', ',');
                        }
                    }
                }
            }
        }
    });
    
    // Manipuladores para botões de editar e cancelar transação
    document.querySelectorAll('.btn-editar-transacao').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            // Aqui você pode implementar a lógica para carregar os dados da transação
            // e preencher o modal de edição
            alert('Editar transação #' + id);
        });
    });
    
    document.querySelectorAll('.btn-cancelar-transacao').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            if (confirm('Tem certeza que deseja cancelar esta transação?')) {
                // Aqui você pode implementar a lógica para cancelar a transação
                alert('Transação #' + id + ' cancelada!');
            }
        });
    });
    
    // Exportar relatório
    document.getElementById('btnExportarRelatorio').addEventListener('click', function() {
        alert('Funcionalidade de exportação de relatório será implementada.');
    });
});
</script> 