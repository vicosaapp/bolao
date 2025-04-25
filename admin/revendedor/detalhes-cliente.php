<?php
// Definir o título da página
$page_title = "Detalhes do Cliente";

// Verificar permissões
require_once '../auth.php';
requireAccess(2); // Nível mínimo: revendedor

// Verificar se o ID do cliente foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: clientes.php");
    exit;
}

$cliente_id = (int)$_GET['id'];

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

// Obter dados do cliente
try {
    $cliente = $clienteModel->buscarClientePorId($cliente_id);
    
    // Verificar se o cliente existe e pertence ao vendedor
    if (!$cliente || $cliente['vendedor_id'] != $vendedor_id) {
        $erro_message = "Cliente não encontrado ou sem permissão para acessar.";
        $cliente = null;
    }
} catch (Exception $e) {
    $cliente = null;
    $erro_message = "Erro ao carregar cliente: " . $e->getMessage();
}

// Obter histórico de compras do cliente
$compras = [];
$total_paginas = 0;

if ($cliente) {
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $por_pagina = 10;
    
    try {
        // Filtros para bilhetes
        $filtros = [
            'apostador_id' => $cliente_id,
            'vendedor_id' => $vendedor_id,
            'offset' => ($pagina - 1) * $por_pagina,
            'limite' => $por_pagina
        ];
        
        $compras = $bilheteModel->listarBilhetesComFiltros($filtros);
        $total_compras = $bilheteModel->contarBilhetesComFiltros([
            'apostador_id' => $cliente_id,
            'vendedor_id' => $vendedor_id
        ]);
        
        // Calcular total de páginas
        $total_paginas = ceil($total_compras / $por_pagina);
    } catch (Exception $e) {
        $erro_message = "Erro ao carregar histórico de compras: " . $e->getMessage();
    }
}
?>

<div class="content-header">
    <div class="content-title">
        <h1><?php echo $page_title; ?></h1>
        <p>Visualize informações detalhadas do cliente e seu histórico de compras</p>
    </div>
    <div class="content-actions">
        <a href="clientes.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<?php if (isset($erro_message)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $erro_message; ?>
    </div>
<?php elseif ($cliente): ?>
    <div class="row">
        <!-- Informações do cliente -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Dados do Cliente</h5>
                    <button type="button" class="btn btn-warning btn-sm btn-editar-cliente" 
                            data-id="<?php echo $cliente['id']; ?>"
                            data-bs-toggle="modal" data-bs-target="#modalEditarCliente">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                </div>
                <div class="card-body">
                    <div class="cliente-info">
                        <p class="cliente-info-item">
                            <strong>Código:</strong> 
                            <span><?php echo $cliente['codigo']; ?></span>
                        </p>
                        <p class="cliente-info-item">
                            <strong>Nome/Razão Social:</strong> 
                            <span><?php echo htmlspecialchars($cliente['nome']); ?></span>
                        </p>
                        <p class="cliente-info-item">
                            <strong>Tipo:</strong> 
                            <span>
                                <?php echo $cliente['tipo'] == 'pessoa_fisica' ? 'Pessoa Física' : 'Pessoa Jurídica'; ?>
                            </span>
                        </p>
                        <?php if (!empty($cliente['cpf_cnpj'])): ?>
                            <p class="cliente-info-item">
                                <strong>CPF/CNPJ:</strong> 
                                <span><?php echo $cliente['cpf_cnpj']; ?></span>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($cliente['telefone'])): ?>
                            <p class="cliente-info-item">
                                <strong>Telefone:</strong> 
                                <span><?php echo $cliente['telefone']; ?></span>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($cliente['email'])): ?>
                            <p class="cliente-info-item">
                                <strong>E-mail:</strong> 
                                <span><?php echo $cliente['email']; ?></span>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($cliente['endereco'])): ?>
                            <p class="cliente-info-item">
                                <strong>Endereço:</strong> 
                                <span><?php echo htmlspecialchars($cliente['endereco']); ?></span>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($cliente['cidade']) || !empty($cliente['estado'])): ?>
                            <p class="cliente-info-item">
                                <strong>Cidade/UF:</strong> 
                                <span>
                                    <?php 
                                    $localizacao = [];
                                    if (!empty($cliente['cidade'])) $localizacao[] = $cliente['cidade'];
                                    if (!empty($cliente['estado'])) $localizacao[] = $cliente['estado'];
                                    echo implode('/', $localizacao); 
                                    ?>
                                </span>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($cliente['cep'])): ?>
                            <p class="cliente-info-item">
                                <strong>CEP:</strong> 
                                <span><?php echo $cliente['cep']; ?></span>
                            </p>
                        <?php endif; ?>
                        <p class="cliente-info-item">
                            <strong>Status:</strong> 
                            <span class="badge <?php echo $cliente['status'] == 'ativo' ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $cliente['status'] == 'ativo' ? 'Ativo' : 'Inativo'; ?>
                            </span>
                        </p>
                        <p class="cliente-info-item">
                            <strong>Data de Cadastro:</strong> 
                            <span><?php echo date('d/m/Y H:i', strtotime($cliente['created_at'])); ?></span>
                        </p>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="nova-venda.php?cliente_id=<?php echo $cliente['id']; ?>" class="btn btn-success w-100">
                        <i class="fas fa-shopping-cart"></i> Nova Venda
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Histórico de compras -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Histórico de Compras</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($compras)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Este cliente ainda não realizou nenhuma compra.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Bilhete</th>
                                        <th>Concurso</th>
                                        <th>Data</th>
                                        <th>Valor</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($compras as $compra): ?>
                                        <tr>
                                            <td><?php echo $compra['numero']; ?></td>
                                            <td>
                                                <?php
                                                // Lógica para exibir informações do concurso
                                                $concurso_info = 'Concurso #' . ($compra['concurso_numero'] ?? $compra['concurso_id']);
                                                if (!empty($compra['concurso_nome'])) {
                                                    $concurso_info .= ' - ' . $compra['concurso_nome'];
                                                }
                                                echo htmlspecialchars($concurso_info);
                                                ?>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($compra['data_compra'])); ?></td>
                                            <td>R$ <?php echo number_format($compra['valor'], 2, ',', '.'); ?></td>
                                            <td>
                                                <?php
                                                // Lógica para exibir badge com status
                                                $status_class = '';
                                                $status_text = $compra['status'];
                                                
                                                switch ($compra['status']) {
                                                    case 'pago':
                                                        $status_class = 'bg-success';
                                                        $status_text = 'Pago';
                                                        break;
                                                    case 'pendente':
                                                        $status_class = 'bg-warning text-dark';
                                                        $status_text = 'Pendente';
                                                        break;
                                                    case 'cancelado':
                                                        $status_class = 'bg-danger';
                                                        $status_text = 'Cancelado';
                                                        break;
                                                    case 'premiado':
                                                        $status_class = 'bg-info';
                                                        $status_text = 'Premiado';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="detalhes-bilhete.php?id=<?php echo $compra['id']; ?>" class="btn btn-primary" title="Detalhes">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($compra['status'] === 'pendente'): ?>
                                                        <a href="confirmar-pagamento.php?id=<?php echo $compra['id']; ?>" class="btn btn-success" title="Confirmar Pagamento">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="imprimir-bilhete.php?id=<?php echo $compra['id']; ?>" class="btn btn-info" title="Imprimir" target="_blank">
                                                        <i class="fas fa-print"></i>
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
                                        <a class="page-link" href="?id=<?php echo $cliente_id; ?>&pagina=<?php echo $pagina - 1; ?>" aria-label="Anterior">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    
                                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                        <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?id=<?php echo $cliente_id; ?>&pagina=<?php echo $i; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?php echo ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?id=<?php echo $cliente_id; ?>&pagina=<?php echo $pagina + 1; ?>" aria-label="Próximo">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar cliente (mesmo do arquivo clientes.php) -->
    <div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-labelledby="modalEditarClienteLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarClienteLabel">Editar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <!-- Formulário de edição (similar ao do arquivo clientes.php) -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSalvarEdicaoCliente">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts JavaScript para carregar e salvar dados do cliente -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Lógica para carregar dados do cliente no modal de edição
            // e salvar alterações (similar ao arquivo clientes.php)
        });
    </script>
<?php endif; ?>

<?php
// Incluir o rodapé
require_once '../templates/footer.php';
?> 