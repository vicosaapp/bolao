<?php
// Definir o título da página
$page_title = "Gerenciar Clientes";

// Verificar permissões
require_once '../auth.php';
requireAccess(2); // Nível mínimo: revendedor

// Incluir o cabeçalho
require_once '../templates/header.php';

// Incluir modelos necessários
require_once '../models/ClienteModel.php';
require_once '../models/BilheteModel.php';

// Instanciar os modelos
$clienteModel = new ClienteModel();
$bilheteModel = new BilheteModel();

// Recuperar o ID do vendedor da sessão
$vendedor_id = $_SESSION['usuario_id'] ?? 0;

// Parâmetros de paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 10;

// Parâmetros para filtro
$filtros = [
    'vendedor_id' => $vendedor_id,
    'offset' => ($pagina - 1) * $por_pagina,
    'limite' => $por_pagina
];

// Adicionar filtro de busca, se fornecido
if (isset($_GET['busca']) && !empty($_GET['busca'])) {
    $filtros['busca'] = $_GET['busca'];
}

// Adicionar filtro de status, se fornecido
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $filtros['status'] = $_GET['status'];
}

// Obter clientes do revendedor
try {
    $clientes = $clienteModel->listarClientesPorVendedor($vendedor_id, $filtros);
    $total_clientes = $clienteModel->contarClientesPorVendedor($vendedor_id, $filtros);
} catch (Exception $e) {
    $clientes = [];
    $total_clientes = 0;
    $erro_message = "Erro ao carregar clientes: " . $e->getMessage();
}

// Calcular total de páginas
$total_paginas = ceil($total_clientes / $por_pagina);

// Obter estatísticas de clientes
try {
    $estatisticas = [
        'total_clientes' => $total_clientes,
        'clientes_ativos' => $clienteModel->contarClientesPorVendedor($vendedor_id, ['status' => 'ativo']),
        'total_compras' => $bilheteModel->contarBilhetesComFiltros(['vendedor_id' => $vendedor_id])
    ];
} catch (Exception $e) {
    $estatisticas = [
        'total_clientes' => 0,
        'clientes_ativos' => 0,
        'total_compras' => 0
    ];
}
?>

<div class="content-header">
    <div class="content-title">
        <h1><?php echo $page_title; ?></h1>
        <p>Gerencie seus clientes e acompanhe o histórico de compras</p>
    </div>
</div>

<!-- Cards de estatísticas -->
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total de Clientes</h5>
                <div class="d-flex align-items-center">
                    <i class="fas fa-users fa-3x me-3"></i>
                    <h2 class="card-value mb-0"><?php echo $estatisticas['total_clientes']; ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Clientes Ativos</h5>
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-check fa-3x me-3"></i>
                    <h2 class="card-value mb-0"><?php echo $estatisticas['clientes_ativos']; ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Total de Compras</h5>
                <div class="d-flex align-items-center">
                    <i class="fas fa-shopping-cart fa-3x me-3"></i>
                    <h2 class="card-value mb-0"><?php echo $estatisticas['total_compras']; ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros de clientes -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title">Filtrar Clientes</h5>
    </div>
    <div class="card-body">
        <form action="" method="get" class="row g-3">
            <div class="col-md-6">
                <label for="busca" class="form-label">Buscar por nome, código ou CPF/CNPJ</label>
                <input type="text" class="form-control" id="busca" name="busca" value="<?php echo $_GET['busca'] ?? ''; ?>">
            </div>
            
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos</option>
                    <option value="ativo" <?php echo (isset($_GET['status']) && $_GET['status'] == 'ativo') ? 'selected' : ''; ?>>Ativo</option>
                    <option value="inativo" <?php echo (isset($_GET['status']) && $_GET['status'] == 'inativo') ? 'selected' : ''; ?>>Inativo</option>
                </select>
            </div>
            
            <div class="col-md-3 d-flex align-items-end">
                <div>
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="clientes.php" class="btn btn-secondary">Limpar Filtros</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Lista de clientes -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Seus Clientes</h5>
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalNovoCliente">
            <i class="fas fa-plus"></i> Novo Cliente
        </button>
    </div>
    <div class="card-body">
        <?php if (isset($erro_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $erro_message; ?>
            </div>
        <?php elseif (empty($clientes)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Nenhum cliente encontrado com os filtros selecionados.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Telefone</th>
                            <th>Cidade/UF</th>
                            <th>Compras</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td><?php echo $cliente['codigo']; ?></td>
                                <td><?php echo htmlspecialchars($cliente['nome']); ?></td>
                                <td><?php echo !empty($cliente['telefone']) ? $cliente['telefone'] : '-'; ?></td>
                                <td>
                                    <?php
                                    $localizacao = [];
                                    if (!empty($cliente['cidade'])) $localizacao[] = $cliente['cidade'];
                                    if (!empty($cliente['estado'])) $localizacao[] = $cliente['estado'];
                                    echo !empty($localizacao) ? implode('/', $localizacao) : '-';
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    // Obter número de compras do cliente
                                    $num_compras = $bilheteModel->contarBilhetesComFiltros([
                                        'apostador_id' => $cliente['id'],
                                        'vendedor_id' => $vendedor_id
                                    ]);
                                    echo $num_compras;
                                    ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $cliente['status'] == 'ativo' ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $cliente['status'] == 'ativo' ? 'Ativo' : 'Inativo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="detalhes-cliente.php?id=<?php echo $cliente['id']; ?>" class="btn btn-primary" title="Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-warning btn-editar-cliente" title="Editar"
                                                data-id="<?php echo $cliente['id']; ?>"
                                                data-bs-toggle="modal" data-bs-target="#modalEditarCliente">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="nova-venda.php?cliente_id=<?php echo $cliente['id']; ?>" class="btn btn-success" title="Nova Venda">
                                            <i class="fas fa-shopping-cart"></i>
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
                            <a class="page-link" href="?pagina=<?php echo $pagina - 1; ?>&busca=<?php echo $_GET['busca'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>" aria-label="Anterior">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $i; ?>&busca=<?php echo $_GET['busca'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?pagina=<?php echo $pagina + 1; ?>&busca=<?php echo $_GET['busca'] ?? ''; ?>&status=<?php echo $_GET['status'] ?? ''; ?>" aria-label="Próximo">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para adicionar novo cliente -->
<div class="modal fade" id="modalNovoCliente" tabindex="-1" aria-labelledby="modalNovoClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNovoClienteLabel">Adicionar Novo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formNovoCliente" class="needs-validation" novalidate>
                    <input type="hidden" name="vendedor_id" value="<?php echo $vendedor_id; ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="codigo" class="form-label">Código</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="codigo" name="codigo" readonly>
                                <button class="btn btn-outline-secondary" type="button" id="btnGerarCodigo">Gerar</button>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <label for="nome" class="form-label">Nome/Razão Social <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                            <div class="invalid-feedback">
                                Por favor, informe o nome/razão social.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="tipo" class="form-label">Tipo de Cliente</label>
                            <select class="form-select" id="tipo" name="tipo">
                                <option value="pessoa_fisica">Pessoa Física</option>
                                <option value="pessoa_juridica">Pessoa Jurídica</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="cpf_cnpj" class="form-label">CPF/CNPJ</label>
                            <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="tel" class="form-control" id="telefone" name="telefone">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="endereco" class="form-label">Endereço</label>
                            <input type="text" class="form-control" id="endereco" name="endereco">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="cep" class="form-label">CEP</label>
                            <input type="text" class="form-control" id="cep" name="cep">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="cidade" class="form-label">Cidade</label>
                            <input type="text" class="form-control" id="cidade" name="cidade">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="">Selecione</option>
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
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="alert alert-danger d-none" id="erroNovoCliente"></div>
                    <div class="alert alert-success d-none" id="sucessoNovoCliente"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarNovoCliente">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar cliente -->
<div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-labelledby="modalEditarClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarClienteLabel">Editar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarCliente" class="needs-validation" novalidate>
                    <input type="hidden" name="cliente_id" id="edit_cliente_id">
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="edit_codigo" class="form-label">Código</label>
                            <input type="text" class="form-control" id="edit_codigo" name="codigo" readonly>
                        </div>
                        
                        <div class="col-md-8">
                            <label for="edit_nome" class="form-label">Nome/Razão Social <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_nome" name="nome" required>
                            <div class="invalid-feedback">
                                Por favor, informe o nome/razão social.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_tipo" class="form-label">Tipo de Cliente</label>
                            <select class="form-select" id="edit_tipo" name="tipo">
                                <option value="pessoa_fisica">Pessoa Física</option>
                                <option value="pessoa_juridica">Pessoa Jurídica</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="edit_cpf_cnpj" class="form-label">CPF/CNPJ</label>
                            <input type="text" class="form-control" id="edit_cpf_cnpj" name="cpf_cnpj">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_telefone" class="form-label">Telefone</label>
                            <input type="tel" class="form-control" id="edit_telefone" name="telefone">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="edit_email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="edit_email" name="email">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="edit_endereco" class="form-label">Endereço</label>
                            <input type="text" class="form-control" id="edit_endereco" name="endereco">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="edit_cep" class="form-label">CEP</label>
                            <input type="text" class="form-control" id="edit_cep" name="cep">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="edit_cidade" class="form-label">Cidade</label>
                            <input type="text" class="form-control" id="edit_cidade" name="cidade">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="edit_estado" class="form-label">Estado</label>
                            <select class="form-select" id="edit_estado" name="estado">
                                <option value="">Selecione</option>
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
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="alert alert-danger d-none" id="erroEditarCliente"></div>
                    <div class="alert alert-success d-none" id="sucessoEditarCliente"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarEdicaoCliente">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts personalizados para a página -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Gerar código para novo cliente
        const btnGerarCodigo = document.getElementById('btnGerarCodigo');
        if (btnGerarCodigo) {
            btnGerarCodigo.addEventListener('click', function() {
                // Normalmente isso seria feito via AJAX para o servidor
                // Mas para simplificar, vamos gerar localmente
                const codigoInput = document.getElementById('codigo');
                const codigo = 'CLI' + Math.floor(Math.random() * 10000).toString().padStart(5, '0');
                codigoInput.value = codigo;
            });
            
            // Gerar um código ao abrir o modal
            const modalNovoCliente = document.getElementById('modalNovoCliente');
            modalNovoCliente.addEventListener('shown.bs.modal', function() {
                btnGerarCodigo.click();
            });
        }
        
        // Salvar novo cliente
        const btnSalvarNovoCliente = document.getElementById('btnSalvarNovoCliente');
        if (btnSalvarNovoCliente) {
            btnSalvarNovoCliente.addEventListener('click', function() {
                const form = document.getElementById('formNovoCliente');
                const erroElement = document.getElementById('erroNovoCliente');
                const sucessoElement = document.getElementById('sucessoNovoCliente');
                
                // Validar formulário
                if (!form.checkValidity()) {
                    form.classList.add('was-validated');
                    return;
                }
                
                // Esconder mensagens anteriores
                erroElement.classList.add('d-none');
                sucessoElement.classList.add('d-none');
                
                // Simulação de envio - em produção, use AJAX para enviar ao servidor
                setTimeout(function() {
                    sucessoElement.textContent = "Cliente adicionado com sucesso!";
                    sucessoElement.classList.remove('d-none');
                    
                    // Em um caso real, recarregaria a página ou atualizaria a tabela
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                }, 1000);
            });
        }
        
        // Carregar dados do cliente para edição
        const botoesEditarCliente = document.querySelectorAll('.btn-editar-cliente');
        if (botoesEditarCliente.length > 0) {
            botoesEditarCliente.forEach(function(botao) {
                botao.addEventListener('click', function() {
                    const clienteId = this.getAttribute('data-id');
                    document.getElementById('edit_cliente_id').value = clienteId;
                    
                    // Em um caso real, carregaria os dados do cliente via AJAX
                    // Aqui estamos apenas simulando
                    
                    // Atribuir dados fictícios para demonstração
                    document.getElementById('edit_codigo').value = 'CLI' + clienteId.padStart(5, '0');
                    document.getElementById('edit_nome').value = 'Nome do Cliente ' + clienteId;
                    document.getElementById('edit_telefone').value = '(11) 99999-9999';
                    document.getElementById('edit_email').value = 'cliente' + clienteId + '@exemplo.com';
                    document.getElementById('edit_cidade').value = 'São Paulo';
                    document.getElementById('edit_estado').value = 'SP';
                });
            });
        }
        
        // Salvar edição de cliente
        const btnSalvarEdicaoCliente = document.getElementById('btnSalvarEdicaoCliente');
        if (btnSalvarEdicaoCliente) {
            btnSalvarEdicaoCliente.addEventListener('click', function() {
                const form = document.getElementById('formEditarCliente');
                const erroElement = document.getElementById('erroEditarCliente');
                const sucessoElement = document.getElementById('sucessoEditarCliente');
                
                // Validar formulário
                if (!form.checkValidity()) {
                    form.classList.add('was-validated');
                    return;
                }
                
                // Esconder mensagens anteriores
                erroElement.classList.add('d-none');
                sucessoElement.classList.add('d-none');
                
                // Simulação de envio - em produção, use AJAX para enviar ao servidor
                setTimeout(function() {
                    sucessoElement.textContent = "Cliente atualizado com sucesso!";
                    sucessoElement.classList.remove('d-none');
                    
                    // Em um caso real, recarregaria a página ou atualizaria a tabela
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                }, 1000);
            });
        }
    });
</script>

<?php
// Incluir o rodapé
require_once '../templates/footer.php';
?> 