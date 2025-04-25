<?php
// Configurar cabeçalho para JSON
header('Content-Type: application/json');

// Verificar se o método é POST e se os parâmetros necessários foram fornecidos
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id']) || !isset($_POST['status'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Parâmetros inválidos']);
    exit;
}

// Incluir autenticação
require_once '../../auth.php';

// Verificar se o usuário está logado e tem permissão de administrador
if (!isLoggedIn() || !hasAccess(3)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Acesso não autorizado']);
    exit;
}

// Incluir os modelos necessários
require_once '../../models/BilheteModel.php';

// Obter os parâmetros
$id = (int) $_POST['id'];
$status = $_POST['status'];

// Validar o status
$statusesValidos = ['pago', 'pendente', 'cancelado'];
if (!in_array($status, $statusesValidos)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Status inválido']);
    exit;
}

// Inicializar modelo
$bilheteModel = new BilheteModel();

try {
    // Atualizar o status do bilhete
    $resultado = $bilheteModel->atualizarStatusBilhete($id, $status);
    
    if ($resultado) {
        echo json_encode(['sucesso' => true, 'mensagem' => 'Status atualizado com sucesso']);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao atualizar status']);
    }
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao atualizar status: ' . $e->getMessage()]);
}
?> 