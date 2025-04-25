<?php
// Definir o título da página
$page_title = "Gerenciamento de Clientes";

// CSS específico para esta página
$extraCSS = [
    '../assets/css/clientes.css'
];

// Verificar permissões
require_once '../auth.php';
requireAccess(3); // Nível mínimo: administrador (3)

// Incluir modelos necessários
require_once '../models/ClienteModel.php';

// Incluir o cabeçalho
require_once '../templates/header.php';

// Inicializar modelo de clientes
$clienteModel = new ClienteModel();

// Buscar clientes
$clientes = $clienteModel->listarClientes();

// Estatísticas de clientes
$total_clientes = $clienteModel->contarClientes();
$clientes_ativos = $clienteModel->contarClientes(['status' => 'ativo']);
$clientes_pf = $clienteModel->contarClientes(['tipo' => 'pessoa_fisica']);
$clientes_pj = $clienteModel->contarClientes(['tipo' => 'pessoa_juridica']);
?>

<div class="content">
    <div class="content-header">
        <h1>Gerenciamento de Clientes</h1>
        <p class="text-muted">Gerencie os clientes do sistema, adicione, edite ou remova registros.</p>
    </div>
    
    <!-- Cards de estatísticas -->
    <div class="dashboard-stats">
        <div class="stat-card primary">
            <div class="stat-card-header">
                <h5 class="stat-card-title">Total de Clientes</h5>
                <div class="stat-card-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <h2 class="stat-card-value"><?php echo number_format($total_clientes, 0, ',', '.'); ?></h2>
            <p class="stat-card-description">
                <i class="fas fa-arrow-up"></i> 
                <span>Clientes cadastrados no sistema</span>
            </p>
            <button class="mt-3 btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#novoClienteModal">
                Adicionar Cliente <i class="fas fa-plus"></i>
            </button>
        </div>
        
        <div class="stat-card success">
            <div class="stat-card-header">
                <h5 class="stat-card-title">Clientes Ativos</h5>
                <div class="stat-card-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <h2 class="stat-card-value"><?php echo number_format($clientes_ativos, 0, ',', '.'); ?></h2>
            <p class="stat-card-description">
                <i class="fas fa-arrow-up"></i> 
                <span>Clientes com cadastro ativo</span>
            </p>
        </div>
        
        <div class="stat-card info">
            <div class="stat-card-header">
                <h5 class="stat-card-title">Pessoas Físicas</h5>
                <div class="stat-card-icon">
                    <i class="fas fa-user"></i>
                </div>
            </div>
            <h2 class="stat-card-value"><?php echo number_format($clientes_pf, 0, ',', '.'); ?></h2>
            <p class="stat-card-description">
                <i class="fas fa-info-circle"></i> 
                <span>Clientes pessoa física</span>
            </p>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-card-header">
                <h5 class="stat-card-title">Pessoas Jurídicas</h5>
                <div class="stat-card-icon">
                    <i class="fas fa-building"></i>
                </div>
            </div>
            <h2 class="stat-card-value"><?php echo number_format($clientes_pj, 0, ',', '.'); ?></h2>
            <p class="stat-card-description">
                <i class="fas fa-info-circle"></i> 
                <span>Clientes pessoa jurídica</span>
            </p>
        </div>
    </div>
    
    <!-- Tabela de Clientes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">Lista de Clientes</h5>
                    <div class="card-actions d-flex gap-2">
                        <select class="form-select form-select-sm" id="filtroTipo">
                            <option value="">Todos os tipos</option>
                            <option value="pessoa_fisica">Pessoa Física</option>
                            <option value="pessoa_juridica">Pessoa Jurídica</option>
                        </select>
                        <select class="form-select form-select-sm" id="filtroStatus">
                            <option value="">Todos os status</option>
                            <option value="ativo">Ativos</option>
                            <option value="inativo">Inativos</option>
                        </select>
                        <input type="text" class="form-control form-control-sm" placeholder="Buscar cliente..." id="buscarCliente">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nome</th>
                                    <th>CPF/CNPJ</th>
                                    <th>Telefone</th>
                                    <th>Email</th>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tabelaClientes">
                                <?php foreach($clientes as $cliente): ?>
                                <tr>
                                    <td><?php echo $cliente['codigo']; ?></td>
                                    <td><?php echo $cliente['nome']; ?></td>
                                    <td><?php echo $cliente['cpf_cnpj']; ?></td>
                                    <td><?php echo $cliente['telefone']; ?></td>
                                    <td><?php echo $cliente['email']; ?></td>
                                    <td>
                                        <?php if($cliente['tipo'] == 'pessoa_fisica'): ?>
                                            <span class="badge bg-info">Pessoa Física</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Pessoa Jurídica</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php 
                                            echo $cliente['status'] == 'ativo' ? 'bg-success' : 'bg-danger'; 
                                        ?>">
                                            <?php echo ucfirst($cliente['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-primary btn-editar" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editarClienteModal" 
                                                    data-id="<?php echo $cliente['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger btn-excluir" 
                                                    data-id="<?php echo $cliente['id']; ?>"
                                                    data-nome="<?php echo htmlspecialchars($cliente['nome']); ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($clientes)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-3">
                                        <p class="text-muted mb-0">Nenhum cliente cadastrado.</p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <span>Mostrando <?php echo count($clientes); ?> de <?php echo $total_clientes; ?> clientes</span>
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

<!-- Modal Novo Cliente -->
<div class="modal fade" id="novoClienteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adicionar Novo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNovoCliente">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Código</label>
                            <input type="text" class="form-control" name="codigo" id="novoCodigo" readonly>
                            <small class="text-muted">Gerado automaticamente</small>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Nome/Razão Social <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nome" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tipo de Pessoa</label>
                            <select class="form-select" name="tipo" id="novoTipo">
                                <option value="pessoa_fisica">Pessoa Física</option>
                                <option value="pessoa_juridica">Pessoa Jurídica</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" id="labelCpfCnpj">CPF</label>
                            <input type="text" class="form-control" name="cpf_cnpj" id="novoCpfCnpj">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Telefone</label>
                            <input type="text" class="form-control" name="telefone">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">CEP</label>
                            <input type="text" class="form-control" name="cep" id="novoCep">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Endereço</label>
                            <input type="text" class="form-control" name="endereco">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Cidade</label>
                            <input type="text" class="form-control" name="cidade">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="estado">
                                <option value="">Selecione...</option>
                                <option value="AC">Acre</option>
                                <option value="AL">Alagoas</option>
                                <option value="AP">Amapá</option>
                                <option value="AM">Amazonas</option>
                                <option value="BA">Bahia</option>
                                <option value="CE">Ceará</option>
                                <option value="DF">Distrito Federal</option>
                                <option value="ES">Espírito Santo</option>
                                <option value="GO">Goiás</option>
                                <option value="MA">Maranhão</option>
                                <option value="MT">Mato Grosso</option>
                                <option value="MS">Mato Grosso do Sul</option>
                                <option value="MG">Minas Gerais</option>
                                <option value="PA">Pará</option>
                                <option value="PB">Paraíba</option>
                                <option value="PR">Paraná</option>
                                <option value="PE">Pernambuco</option>
                                <option value="PI">Piauí</option>
                                <option value="RJ">Rio de Janeiro</option>
                                <option value="RN">Rio Grande do Norte</option>
                                <option value="RS">Rio Grande do Sul</option>
                                <option value="RO">Rondônia</option>
                                <option value="RR">Roraima</option>
                                <option value="SC">Santa Catarina</option>
                                <option value="SP">São Paulo</option>
                                <option value="SE">Sergipe</option>
                                <option value="TO">Tocantins</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" value="ativo" checked>
                            <label class="form-check-label">Ativo</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" value="inativo">
                            <label class="form-check-label">Inativo</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="salvarNovoCliente">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Cliente -->
<div class="modal fade" id="editarClienteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarCliente">
                    <input type="hidden" name="id" id="editarId">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Código</label>
                            <input type="text" class="form-control" name="codigo" id="editarCodigo" readonly>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Nome/Razão Social <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nome" id="editarNome" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tipo de Pessoa</label>
                            <select class="form-select" name="tipo" id="editarTipo">
                                <option value="pessoa_fisica">Pessoa Física</option>
                                <option value="pessoa_juridica">Pessoa Jurídica</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" id="editarLabelCpfCnpj">CPF</label>
                            <input type="text" class="form-control" name="cpf_cnpj" id="editarCpfCnpj">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Telefone</label>
                            <input type="text" class="form-control" name="telefone" id="editarTelefone">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="editarEmail">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">CEP</label>
                            <input type="text" class="form-control" name="cep" id="editarCep">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Endereço</label>
                            <input type="text" class="form-control" name="endereco" id="editarEndereco">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Cidade</label>
                            <input type="text" class="form-control" name="cidade" id="editarCidade">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="estado" id="editarEstado">
                                <option value="">Selecione...</option>
                                <option value="AC">Acre</option>
                                <option value="AL">Alagoas</option>
                                <option value="AP">Amapá</option>
                                <option value="AM">Amazonas</option>
                                <option value="BA">Bahia</option>
                                <option value="CE">Ceará</option>
                                <option value="DF">Distrito Federal</option>
                                <option value="ES">Espírito Santo</option>
                                <option value="GO">Goiás</option>
                                <option value="MA">Maranhão</option>
                                <option value="MT">Mato Grosso</option>
                                <option value="MS">Mato Grosso do Sul</option>
                                <option value="MG">Minas Gerais</option>
                                <option value="PA">Pará</option>
                                <option value="PB">Paraíba</option>
                                <option value="PR">Paraná</option>
                                <option value="PE">Pernambuco</option>
                                <option value="PI">Piauí</option>
                                <option value="RJ">Rio de Janeiro</option>
                                <option value="RN">Rio Grande do Norte</option>
                                <option value="RS">Rio Grande do Sul</option>
                                <option value="RO">Rondônia</option>
                                <option value="RR">Roraima</option>
                                <option value="SC">Santa Catarina</option>
                                <option value="SP">São Paulo</option>
                                <option value="SE">Sergipe</option>
                                <option value="TO">Tocantins</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" value="ativo" id="editarStatusAtivo">
                            <label class="form-check-label">Ativo</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" value="inativo" id="editarStatusInativo">
                            <label class="form-check-label">Inativo</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="salvarEdicaoCliente">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Exclusão -->
<div class="modal fade" id="confirmarExclusaoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o cliente <strong id="nomeClienteExcluir"></strong>?</p>
                <p class="text-danger">Esta ação não pode ser desfeita.</p>
                <input type="hidden" id="idClienteExcluir">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmarExclusao">Excluir</button>
            </div>
        </div>
    </div>
</div>

<?php
// Scripts específicos para a página
$page_scripts = [
    '../assets/js/clientes.js'
];

// Incluir o rodapé
require_once '../templates/footer.php';
?> 