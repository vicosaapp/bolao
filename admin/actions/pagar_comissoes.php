<?php
require_once '../../config/conexao.php';
require_once '../../includes/funcoes.php';
require_once '../auth.php';

// Verifica se o usuário está logado e tem permissão
requireAccess(3); // Nível mínimo: administrador (3)

header('Content-Type: application/json');

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
    exit;
}

// Verifica se os campos obrigatórios foram enviados
if (empty($_POST['idsComissoes']) || empty($_POST['metodoPagamento'])) {
    echo json_encode(['status' => 'error', 'message' => 'Dados incompletos']);
    exit;
}

// Prepara os dados
$idsComissoes = explode(',', $_POST['idsComissoes']);
$metodoPagamento = mysqli_real_escape_string($conexao, $_POST['metodoPagamento']);
$observacao = mysqli_real_escape_string($conexao, $_POST['observacao'] ?? '');
$dataPagamento = date('Y-m-d H:i:s');
$usuario = $_SESSION['usuario_id'];

// Inicia uma transação para garantir que todas as operações sejam concluídas ou nenhuma
mysqli_begin_transaction($conexao);

try {
    // Array para armazenar os IDs de comissões que foram processadas com sucesso
    $comissoesProcessadas = [];
    $valorTotal = 0;
    
    // Processa cada comissão
    foreach ($idsComissoes as $idComissao) {
        $idComissao = (int)$idComissao;
        
        // Verifica se a comissão existe e está com status pendente
        $queryVerifica = "SELECT id, valor, vendedor_id FROM comissoes WHERE id = $idComissao AND status = 'pendente'";
        $resultadoVerifica = mysqli_query($conexao, $queryVerifica);
        
        if (mysqli_num_rows($resultadoVerifica) === 0) {
            continue; // Pula comissões que não existem ou não estão pendentes
        }
        
        $comissao = mysqli_fetch_assoc($resultadoVerifica);
        $valorTotal += $comissao['valor'];
        
        // Atualiza o status da comissão para 'pago'
        $queryAtualiza = "UPDATE comissoes SET 
                          status = 'pago',
                          data_pagamento = '$dataPagamento', 
                          metodo_pagamento = '$metodoPagamento',
                          observacao = '$observacao',
                          usuario_pagamento = $usuario
                          WHERE id = $idComissao";
        
        if (!mysqli_query($conexao, $queryAtualiza)) {
            throw new Exception("Erro ao atualizar comissão ID: $idComissao");
        }
        
        // Registra o pagamento no histórico
        $idVendedor = $comissao['vendedor_id'];
        $valor = $comissao['valor'];
        
        $queryHistorico = "INSERT INTO historico_pagamentos 
                          (comissao_id, vendedor_id, valor, data_pagamento, metodo, observacao, usuario_id) 
                          VALUES 
                          ($idComissao, $idVendedor, $valor, '$dataPagamento', '$metodoPagamento', '$observacao', $usuario)";
        
        if (!mysqli_query($conexao, $queryHistorico)) {
            throw new Exception("Erro ao registrar histórico para comissão ID: $idComissao");
        }
        
        $comissoesProcessadas[] = $idComissao;
    }
    
    // Se não conseguiu processar nenhuma comissão
    if (count($comissoesProcessadas) === 0) {
        throw new Exception("Nenhuma comissão válida para processamento");
    }
    
    // Commit da transação se tudo ocorreu bem
    mysqli_commit($conexao);
    
    // Retorna sucesso
    echo json_encode([
        'status' => 'success',
        'message' => 'Pagamento realizado com sucesso! ' . count($comissoesProcessadas) . ' comissões processadas.',
        'comissoes_processadas' => $comissoesProcessadas,
        'valor_total' => $valorTotal,
        'data_pagamento' => date('d/m/Y H:i', strtotime($dataPagamento))
    ]);
    
} catch (Exception $e) {
    // Rollback em caso de erro
    mysqli_rollback($conexao);
    
    // Registra o erro no log
    error_log('Erro ao processar pagamento de comissões: ' . $e->getMessage());
    
    // Retorna erro
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao processar pagamento: ' . $e->getMessage()
    ]);
}

// Fecha a conexão
mysqli_close($conexao); 