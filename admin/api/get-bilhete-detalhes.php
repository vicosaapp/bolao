<?php
/**
 * API para obter detalhes de um bilhete específico
 * Endpoint: /admin/api/get-bilhete-detalhes.php?id=X
 */

// Verificar acesso
require_once '../auth.php';
requireAccess(1); // Nível mínimo: usuário comum

// Incluir o modelo de bilhetes
require_once '../models/BilheteModel.php';

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID do bilhete não fornecido.'
    ]);
    exit;
}

// Obter ID do bilhete
$bilheteId = intval($_GET['id']);

// Obter ID do usuário logado
$usuarioId = $_SESSION['usuario_id'] ?? 0;

// Inicializar modelo de bilhetes
$bilheteModel = new BilheteModel();

// Obter detalhes do bilhete
$bilhete = $bilheteModel->obterDetalhesBilhete($bilheteId);

// Verificar se o bilhete existe
if (!$bilhete) {
    echo json_encode([
        'success' => false,
        'message' => 'Bilhete não encontrado.'
    ]);
    exit;
}

// Verificar se o bilhete pertence ao usuário logado ou se é um administrador
if ($bilhete['usuario_id'] != $usuarioId && !isAdmin()) {
    echo json_encode([
        'success' => false,
        'message' => 'Você não tem permissão para visualizar este bilhete.'
    ]);
    exit;
}

// Retornar dados do bilhete em formato JSON
echo json_encode([
    'success' => true,
    'bilhete' => $bilhete
]);
exit;

/**
 * Verifica se o usuário é um administrador
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['usuario_nivel']) && $_SESSION['usuario_nivel'] >= 3;
}
?> 