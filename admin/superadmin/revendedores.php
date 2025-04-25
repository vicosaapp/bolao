<?php
// Definir o título da página
$page_title = "Gerenciamento de Revendedores";

// Verificar permissões
require_once '../auth.php';
requireAccess(3); // Nível mínimo: administrador (3)

// Incluir modelos necessários
require_once '../models/VendedorModel.php';

// Incluir o cabeçalho
require_once '../templates/header.php';

// Inicializar modelo de vendedores
$vendedorModel = new VendedorModel();

// Buscar vendedores
$vendedores = $vendedorModel->listarVendedores();

// Estatísticas de vendedores
$total_vendedores = count($vendedores);
$vendedores_ativos = count(array_filter($vendedores, function($vendedor) {
    return isset($vendedor['status']) && $vendedor['status'] == 'ativo';
}));
$total_vendas = $vendedorModel->contarTotalVendas();
$total_comissoes = $vendedorModel->calcularTotalComissoes();
?>

<div class="content">
    <div class="content-header">
        <h1>Gerenciamento de Revendedores</h1>
        <p class="text-muted">Cadastre e gerencie os revendedores do sistema.</p>
    </div>
    
    <!-- Cards de estatísticas -->
    <div class="dashboard-stats">
        <div class="stat-card primary">
            <div class="stat-card-header">
                <h5 class="stat-card-title">Total de Revendedores</h5>
                <div class="stat-card-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <h2 class="stat-card-value"><?php echo number_format($total_vendedores, 0, ',', '.'); ?></h2>
            <p class="stat-card-description">
                <i class="fas fa-arrow-up"></i> 
                <span>Revendedores cadastrados</span>
            </p>
            <button class="mt-3 btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#novoVendedorModal">
                Adicionar Revendedor <i class="fas fa-plus"></i>
            </button>
        </div>
        
        <div class="stat-card success">
            <div class="stat-card-header">
                <h5 class="stat-card-title">Revendedores Ativos</h5>
                <div class="stat-card-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <h2 class="stat-card-value"><?php echo number_format($vendedores_ativos, 0, ',', '.'); ?></h2>
            <p class="stat-card-description">
                <i class="fas fa-arrow-up"></i> 
                <span>Revendedores em operação</span>
            </p>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-card-header">
                <h5 class="stat-card-title">Total de Vendas</h5>
                <div class="stat-card-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
            </div>
            <h2 class="stat-card-value"><?php echo number_format($total_vendas, 0, ',', '.'); ?></h2>
            <p class="stat-card-description">
                <i class="fas fa-arrow-up"></i> 
                <span>Bilhetes vendidos</span>
            </p>
        </div>
        
        <div class="stat-card info">
            <div class="stat-card-header">
                <h5 class="stat-card-title">Total de Comissões</h5>
                <div class="stat-card-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
            <h2 class="stat-card-value">R$ <?php echo number_format($total_comissoes, 2, ',', '.'); ?></h2>
            <p class="stat-card-description">
                <i class="fas fa-arrow-up"></i> 
                <span>Comissões geradas</span>
            </p>
        </div>
    </div>
    
    <!-- Tabela de Revendedores -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">Lista de Revendedores</h5>
                    <div class="card-actions">
                        <input type="text" class="form-control form-control-sm" placeholder="Buscar revendedor..." id="buscarVendedor">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nome</th>
                                    <th>Telefone</th>
                                    <th>Email</th>
                                    <th>Comissão (%)</th>
                                    <th>Total Vendas</th>
                                    <th>Total Comissões</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($vendedores)): ?>
                                    <?php foreach($vendedores as $vendedor): ?>
                                    <tr>
                                        <td><?php echo $vendedor['codigo']; ?></td>
                                        <td><?php echo $vendedor['nome']; ?></td>
                                        <td><?php echo $vendedor['telefone']; ?></td>
                                        <td><?php echo $vendedor['email']; ?></td>
                                        <td><?php echo number_format($vendedor['comissao'], 2, ',', '.'); ?>%</td>
                                        <td><?php echo number_format($vendedor['total_vendas'] ?? 0, 0, ',', '.'); ?></td>
                                        <td>R$ <?php echo number_format($vendedor['total_comissoes'] ?? 0, 2, ',', '.'); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detalhesVendedorModal" 
                                                        data-id="<?php echo $vendedor['id']; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editarVendedorModal" 
                                                        data-id="<?php echo $vendedor['id']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger btn-excluir-vendedor" data-id="<?php echo $vendedor['id']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-3">
                                            <p class="text-muted mb-0">Nenhum revendedor cadastrado.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <span>Mostrando <?php echo count($vendedores); ?> de <?php echo $total_vendedores; ?> revendedores</span>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item disabled">
                                <a class="page-link" href="#">Anterior</a>
                            </li>
                            <li class="page-item active">
                                <a class="page-link" href="#">1</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="#">Próximo</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo Revendedor -->
<div class="modal fade" id="novoVendedorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adicionar Novo Revendedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNovoVendedor">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Código</label>
                            <input type="text" class="form-control" name="codigo" required>
                            <small class="form-text text-muted">Código único para identificação</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" name="nome" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telefone</label>
                            <input type="text" class="form-control" name="telefone" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Percentual de Comissão (%)</label>
                        <input type="number" class="form-control" name="comissao" value="10" min="0" max="100" step="0.1" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="salvarNovoVendedor">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Revendedor -->
<div class="modal fade" id="editarVendedorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Revendedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarVendedor">
                    <input type="hidden" name="id" id="editarVendedorId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Código</label>
                            <input type="text" class="form-control" name="codigo" id="editarVendedorCodigo" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" name="nome" id="editarVendedorNome" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telefone</label>
                            <input type="text" class="form-control" name="telefone" id="editarVendedorTelefone" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="editarVendedorEmail">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Percentual de Comissão (%)</label>
                        <input type="number" class="form-control" name="comissao" id="editarVendedorComissao" min="0" max="100" step="0.1" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="salvarEdicaoVendedor">Salvar Alterações</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalhes do Revendedor -->
<div class="modal fade" id="detalhesVendedorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Revendedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Informações do Revendedor</h6>
                        <table class="table table-sm">
                            <tr>
                                <th style="width: 30%">Código:</th>
                                <td id="detalhesCodigo"></td>
                            </tr>
                            <tr>
                                <th>Nome:</th>
                                <td id="detalhesNome"></td>
                            </tr>
                            <tr>
                                <th>Telefone:</th>
                                <td id="detalhesTelefone"></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td id="detalhesEmail"></td>
                            </tr>
                            <tr>
                                <th>Comissão:</th>
                                <td id="detalhesComissao"></td>
                            </tr>
                            <tr>
                                <th>Data Cadastro:</th>
                                <td id="detalhesDataCadastro"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Estatísticas de Vendas</h6>
                        <div class="stats-container">
                            <div class="stat-item">
                                <span class="stat-label">Total de Vendas:</span>
                                <span class="stat-value" id="detalhesTotalVendas">0</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Comissões Geradas:</span>
                                <span class="stat-value" id="detalhesComissoesGeradas">R$ 0,00</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Comissões Pagas:</span>
                                <span class="stat-value" id="detalhesComissoesPagas">R$ 0,00</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Comissões Pendentes:</span>
                                <span class="stat-value" id="detalhesComissoesPendentes">R$ 0,00</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h6>Últimas Vendas</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Bilhete</th>
                                <th>Concurso</th>
                                <th>Data</th>
                                <th>Apostador</th>
                                <th>Valor</th>
                                <th>Comissão</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaUltimasVendas">
                            <tr>
                                <td colspan="6" class="text-center">Carregando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <a href="#" class="btn btn-primary" id="btnVerTodasVendas">Ver Todas as Vendas</a>
            </div>
        </div>
    </div>
</div>

<?php
// Scripts específicos para a página
$page_scripts = [
    '../assets/js/revendedores.js'
];

// Incluir o rodapé
require_once '../templates/footer.php';
?> 