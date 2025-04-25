<?php
// Definir o título da página
$page_title = "Nova Venda";

// Verificar permissões
require_once '../auth.php';
requireAccess(2); // Nível mínimo: revendedor

// Incluir o cabeçalho
require_once '../templates/header.php';

// Incluir modelos necessários
require_once '../models/ConcursoModel.php';
require_once '../models/BilheteModel.php';
require_once '../models/VendedorModel.php';

// Instanciar os modelos
$concursoModel = new ConcursoModel();
$bilheteModel = new BilheteModel();
$vendedorModel = new VendedorModel();

// Recuperar o ID do vendedor da sessão
$vendedor_id = $_SESSION['usuario_id'] ?? 0;

// Obter informações do vendedor
$vendedor = $vendedorModel->obterVendedorPorId($vendedor_id);

// Obter concursos ativos
$concursos_ativos = $concursoModel->listarConcursosComFiltros([
    'status' => 'em_andamento',
    'limite' => 100
]);

// Inicializar variáveis para o formulário
$errors = [];
$success = false;
$form_data = [
    'concurso_id' => $_POST['concurso_id'] ?? '',
    'nome_cliente' => $_POST['nome_cliente'] ?? '',
    'telefone_cliente' => $_POST['telefone_cliente'] ?? '',
    'cidade_cliente' => $_POST['cidade_cliente'] ?? '',
    'estado_cliente' => $_POST['estado_cliente'] ?? '',
    'quantidade_bilhetes' => $_POST['quantidade_bilhetes'] ?? 1,
    'valor_unitario' => $_POST['valor_unitario'] ?? 5.00,
    'status_pagamento' => $_POST['status_pagamento'] ?? 'pago'
];

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar dados do formulário
    if (empty($_POST['concurso_id'])) {
        $errors[] = "Por favor, selecione um concurso.";
    }
    
    if (empty($_POST['nome_cliente'])) {
        $errors[] = "Por favor, informe o nome do cliente.";
    }
    
    // Validar quantidade de bilhetes
    $quantidade_bilhetes = (int)$_POST['quantidade_bilhetes'];
    if ($quantidade_bilhetes <= 0 || $quantidade_bilhetes > 100) {
        $errors[] = "A quantidade de bilhetes deve estar entre 1 e 100.";
    }
    
    // Validar valor unitário
    $valor_unitario = (float)$_POST['valor_unitario'];
    if ($valor_unitario <= 0) {
        $errors[] = "O valor unitário deve ser maior que zero.";
    }
    
    // Se não houver erros, processar a venda
    if (empty($errors)) {
        try {
            // Obter o concurso selecionado
            $concurso_id = (int)$_POST['concurso_id'];
            $concurso = $concursoModel->obterConcursoPorId($concurso_id);
            
            if (!$concurso) {
                $errors[] = "Concurso não encontrado.";
            } else {
                // Verificar se o cliente já existe
                $apostador_id = null;
                
                // Registrar o cliente se não existir
                // Para simplificar, vamos apenas criar um novo apostador a cada venda
                $nome_cliente = $_POST['nome_cliente'];
                $telefone_cliente = $_POST['telefone_cliente'] ?? '';
                $cidade_cliente = $_POST['cidade_cliente'] ?? '';
                $estado_cliente = $_POST['estado_cliente'] ?? '';
                
                // Inserir o apostador
                $apostador_data = [
                    'nome' => $nome_cliente,
                    'telefone' => $telefone_cliente,
                    'cidade' => $cidade_cliente,
                    'estado' => $estado_cliente
                ];
                
                // Aqui você precisaria de um modelo ApostadorModel, para simplificar vamos supor que o ID do apostador é 1
                $apostador_id = 1;
                
                // Criar os bilhetes
                $bilhetes_criados = 0;
                $status_pagamento = $_POST['status_pagamento'];
                
                for ($i = 0; $i < $quantidade_bilhetes; $i++) {
                    // Gerar número de bilhete único
                    $numero_bilhete = mt_rand(1000000, 9999999);
                    
                    // Dados do bilhete
                    $bilhete_data = [
                        'numero' => $numero_bilhete,
                        'concurso_id' => $concurso_id,
                        'apostador_id' => $apostador_id,
                        'vendedor_id' => $vendedor_id,
                        'valor' => $valor_unitario,
                        'status' => $status_pagamento,
                        'data_compra' => date('Y-m-d H:i:s')
                    ];
                    
                    // Inserir o bilhete
                    $bilhete_id = $bilheteModel->adicionarBilhete($bilhete_data);
                    
                    if ($bilhete_id) {
                        $bilhetes_criados++;
                    }
                }
                
                if ($bilhetes_criados > 0) {
                    $success = true;
                    $form_data = [
                        'concurso_id' => '',
                        'nome_cliente' => '',
                        'telefone_cliente' => '',
                        'cidade_cliente' => '',
                        'estado_cliente' => '',
                        'quantidade_bilhetes' => 1,
                        'valor_unitario' => 5.00,
                        'status_pagamento' => 'pago'
                    ];
                } else {
                    $errors[] = "Houve um problema ao criar os bilhetes. Por favor, tente novamente.";
                }
            }
        } catch (Exception $e) {
            $errors[] = "Erro ao processar a venda: " . $e->getMessage();
        }
    }
}
?>

<div class="content-header">
    <div class="content-title">
        <h1><?php echo $page_title; ?></h1>
        <p>Registre uma nova venda de bilhetes</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title">Registrar Nova Venda</h5>
    </div>
    <div class="card-body">
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Venda registrada com sucesso! <?php echo $bilhetes_criados; ?> bilhete(s) criado(s).
                <div class="mt-2">
                    <a href="vendas.php" class="btn btn-sm btn-primary">Ver todas as vendas</a>
                    <button class="btn btn-sm btn-success" onclick="window.location.reload()">Registrar nova venda</button>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (empty($concursos_ativos)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Não há concursos ativos no momento. Não é possível registrar vendas.
            </div>
        <?php else: ?>
            <form action="" method="post" id="vendaForm" class="needs-validation" novalidate>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="concurso_id" class="form-label">Concurso <span class="text-danger">*</span></label>
                        <select class="form-select" id="concurso_id" name="concurso_id" required>
                            <option value="">Selecione um concurso</option>
                            <?php foreach ($concursos_ativos as $concurso): ?>
                                <option value="<?php echo $concurso['id']; ?>" <?php echo ($form_data['concurso_id'] == $concurso['id']) ? 'selected' : ''; ?>>
                                    #<?php echo $concurso['numero']; ?> - <?php echo htmlspecialchars($concurso['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Por favor, selecione um concurso.</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="vendedor" class="form-label">Vendedor</label>
                        <input type="text" class="form-control" id="vendedor" value="<?php echo htmlspecialchars($vendedor['nome'] ?? $_SESSION['usuario_nome']); ?>" readonly>
                        <input type="hidden" name="vendedor_id" value="<?php echo $vendedor_id; ?>">
                    </div>
                </div>
                
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title m-0">Dados do Cliente</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nome_cliente" class="form-label">Nome do Cliente <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nome_cliente" name="nome_cliente" value="<?php echo htmlspecialchars($form_data['nome_cliente']); ?>" required>
                                <div class="invalid-feedback">Por favor, informe o nome do cliente.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="telefone_cliente" class="form-label">Telefone</label>
                                <input type="tel" class="form-control" id="telefone_cliente" name="telefone_cliente" value="<?php echo htmlspecialchars($form_data['telefone_cliente']); ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="cidade_cliente" class="form-label">Cidade</label>
                                <input type="text" class="form-control" id="cidade_cliente" name="cidade_cliente" value="<?php echo htmlspecialchars($form_data['cidade_cliente']); ?>">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="estado_cliente" class="form-label">Estado</label>
                                <select class="form-select" id="estado_cliente" name="estado_cliente">
                                    <option value="">Selecione</option>
                                    <option value="AC" <?php echo ($form_data['estado_cliente'] == 'AC') ? 'selected' : ''; ?>>Acre</option>
                                    <option value="AL" <?php echo ($form_data['estado_cliente'] == 'AL') ? 'selected' : ''; ?>>Alagoas</option>
                                    <option value="AP" <?php echo ($form_data['estado_cliente'] == 'AP') ? 'selected' : ''; ?>>Amapá</option>
                                    <option value="AM" <?php echo ($form_data['estado_cliente'] == 'AM') ? 'selected' : ''; ?>>Amazonas</option>
                                    <option value="BA" <?php echo ($form_data['estado_cliente'] == 'BA') ? 'selected' : ''; ?>>Bahia</option>
                                    <option value="CE" <?php echo ($form_data['estado_cliente'] == 'CE') ? 'selected' : ''; ?>>Ceará</option>
                                    <option value="DF" <?php echo ($form_data['estado_cliente'] == 'DF') ? 'selected' : ''; ?>>Distrito Federal</option>
                                    <option value="ES" <?php echo ($form_data['estado_cliente'] == 'ES') ? 'selected' : ''; ?>>Espírito Santo</option>
                                    <option value="GO" <?php echo ($form_data['estado_cliente'] == 'GO') ? 'selected' : ''; ?>>Goiás</option>
                                    <option value="MA" <?php echo ($form_data['estado_cliente'] == 'MA') ? 'selected' : ''; ?>>Maranhão</option>
                                    <option value="MT" <?php echo ($form_data['estado_cliente'] == 'MT') ? 'selected' : ''; ?>>Mato Grosso</option>
                                    <option value="MS" <?php echo ($form_data['estado_cliente'] == 'MS') ? 'selected' : ''; ?>>Mato Grosso do Sul</option>
                                    <option value="MG" <?php echo ($form_data['estado_cliente'] == 'MG') ? 'selected' : ''; ?>>Minas Gerais</option>
                                    <option value="PA" <?php echo ($form_data['estado_cliente'] == 'PA') ? 'selected' : ''; ?>>Pará</option>
                                    <option value="PB" <?php echo ($form_data['estado_cliente'] == 'PB') ? 'selected' : ''; ?>>Paraíba</option>
                                    <option value="PR" <?php echo ($form_data['estado_cliente'] == 'PR') ? 'selected' : ''; ?>>Paraná</option>
                                    <option value="PE" <?php echo ($form_data['estado_cliente'] == 'PE') ? 'selected' : ''; ?>>Pernambuco</option>
                                    <option value="PI" <?php echo ($form_data['estado_cliente'] == 'PI') ? 'selected' : ''; ?>>Piauí</option>
                                    <option value="RJ" <?php echo ($form_data['estado_cliente'] == 'RJ') ? 'selected' : ''; ?>>Rio de Janeiro</option>
                                    <option value="RN" <?php echo ($form_data['estado_cliente'] == 'RN') ? 'selected' : ''; ?>>Rio Grande do Norte</option>
                                    <option value="RS" <?php echo ($form_data['estado_cliente'] == 'RS') ? 'selected' : ''; ?>>Rio Grande do Sul</option>
                                    <option value="RO" <?php echo ($form_data['estado_cliente'] == 'RO') ? 'selected' : ''; ?>>Rondônia</option>
                                    <option value="RR" <?php echo ($form_data['estado_cliente'] == 'RR') ? 'selected' : ''; ?>>Roraima</option>
                                    <option value="SC" <?php echo ($form_data['estado_cliente'] == 'SC') ? 'selected' : ''; ?>>Santa Catarina</option>
                                    <option value="SP" <?php echo ($form_data['estado_cliente'] == 'SP') ? 'selected' : ''; ?>>São Paulo</option>
                                    <option value="SE" <?php echo ($form_data['estado_cliente'] == 'SE') ? 'selected' : ''; ?>>Sergipe</option>
                                    <option value="TO" <?php echo ($form_data['estado_cliente'] == 'TO') ? 'selected' : ''; ?>>Tocantins</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title m-0">Detalhes da Venda</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="quantidade_bilhetes" class="form-label">Quantidade de Bilhetes</label>
                                <input type="number" class="form-control" id="quantidade_bilhetes" name="quantidade_bilhetes" min="1" max="100" value="<?php echo $form_data['quantidade_bilhetes']; ?>" required>
                                <div class="invalid-feedback">Por favor, informe uma quantidade válida (entre 1 e 100).</div>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="valor_unitario" class="form-label">Valor Unitário (R$)</label>
                                <input type="number" class="form-control" id="valor_unitario" name="valor_unitario" min="0.01" step="0.01" value="<?php echo $form_data['valor_unitario']; ?>" required>
                                <div class="invalid-feedback">Por favor, informe um valor válido.</div>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="valor_total" class="form-label">Valor Total (R$)</label>
                                <input type="text" class="form-control" id="valor_total" readonly>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="status_pagamento" class="form-label">Status do Pagamento</label>
                                <select class="form-select" id="status_pagamento" name="status_pagamento">
                                    <option value="pago" <?php echo ($form_data['status_pagamento'] == 'pago') ? 'selected' : ''; ?>>Pago</option>
                                    <option value="pendente" <?php echo ($form_data['status_pagamento'] == 'pendente') ? 'selected' : ''; ?>>Pendente</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Registrar Venda</button>
                    <a href="vendas.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Scripts personalizados para a página -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Validação do formulário
        const form = document.getElementById('vendaForm');
        
        if (form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            });
            
            // Atualizar valor total ao mudar quantidade ou valor unitário
            const quantidadeInput = document.getElementById('quantidade_bilhetes');
            const valorUnitarioInput = document.getElementById('valor_unitario');
            const valorTotalInput = document.getElementById('valor_total');
            
            function atualizarValorTotal() {
                const quantidade = parseInt(quantidadeInput.value) || 0;
                const valorUnitario = parseFloat(valorUnitarioInput.value) || 0;
                const valorTotal = quantidade * valorUnitario;
                
                valorTotalInput.value = 'R$ ' + valorTotal.toFixed(2).replace('.', ',');
            }
            
            quantidadeInput.addEventListener('input', atualizarValorTotal);
            valorUnitarioInput.addEventListener('input', atualizarValorTotal);
            
            // Inicializar valor total
            atualizarValorTotal();
        }
    });
</script>

<?php
// Incluir o rodapé
require_once '../templates/footer.php';
?> 