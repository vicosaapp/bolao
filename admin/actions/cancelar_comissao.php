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

// Verifica se os IDs foram enviados
if (empty($_POST['idsComissoes'])) {
    echo json_encode(['status' => 'error', 'message' => 'Dados incompletos']);
    exit;
}

// Prepara os dados
$idsComissoes = explode(',', $_POST['idsComissoes']);
$motivo = mysqli_real_escape_string($conexao, $_POST['motivo'] ?? 'Cancelamento administrativo');
$dataCancelamento = date('Y-m-d H:i:s');
$usuario = $_SESSION['usuario_id'];

// Inicia uma transação
mysqli_begin_transaction($conexao);

try {
    // Array para armazenar os IDs de comissões canceladas
    $comissoesCanceladas = [];
    
    // Processa cada comissão
    foreach ($idsComissoes as $idComissao) {
        $idComissao = (int)$idComissao;
        
        // Verifica se a comissão existe e está com status pendente
        $queryVerifica = "SELECT id, vendedor_id, valor FROM comissoes WHERE id = $idComissao AND status = 'pendente'";
        $resultadoVerifica = mysqli_query($conexao, $queryVerifica);
        
        if (mysqli_num_rows($resultadoVerifica) === 0) {
            continue; // Pula comissões que não existem ou não estão pendentes
        }
        
        $comissao = mysqli_fetch_assoc($resultadoVerifica);
        
        // Atualiza o status da comissão para 'cancelado'
        $queryAtualiza = "UPDATE comissoes SET 
                           status = 'cancelado',
                           data_cancelamento = '$dataCancelamento', 
                           motivo_cancelamento = '$motivo',
                           usuario_cancelamento = $usuario
                           WHERE id = $idComissao";
        
        if (!mysqli_query($conexao, $queryAtualiza)) {
            throw new Exception("Erro ao cancelar comissão ID: $idComissao");
        }
        
        // Registra o cancelamento no histórico
        $idVendedor = $comissao['vendedor_id'];
        $valor = $comissao['valor'];
        
        $queryHistorico = "INSERT INTO historico_cancelamentos 
                           (comissao_id, vendedor_id, valor, data_cancelamento, motivo, usuario_id) 
                           VALUES 
                           ($idComissao, $idVendedor, $valor, '$dataCancelamento', '$motivo', $usuario)";
        
        if (!mysqli_query($conexao, $queryHistorico)) {
            throw new Exception("Erro ao registrar histórico para comissão ID: $idComissao");
        }
        
        $comissoesCanceladas[] = $idComissao;
    }
    
    // Se não conseguiu cancelar nenhuma comissão
    if (count($comissoesCanceladas) === 0) {
        throw new Exception("Nenhuma comissão válida para cancelamento");
    }
    
    // Commit da transação se tudo ocorreu bem
    mysqli_commit($conexao);
    
    // Retorna sucesso
    echo json_encode([
        'status' => 'success',
        'message' => 'Cancelamento realizado com sucesso! ' . count($comissoesCanceladas) . ' comissões canceladas.',
        'comissoes_canceladas' => $comissoesCanceladas
    ]);
    
} catch (Exception $e) {
    // Rollback em caso de erro
    mysqli_rollback($conexao);
    
    // Registra o erro no log
    error_log('Erro ao processar cancelamento de comissões: ' . $e->getMessage());
    
    // Retorna erro
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao processar cancelamento: ' . $e->getMessage()
    ]);
}

// Fecha a conexão
mysqli_close($conexao); 