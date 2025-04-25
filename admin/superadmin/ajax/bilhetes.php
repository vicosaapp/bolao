<?php
session_start();

// Configurar cabeçalho para resposta JSON
header('Content-Type: application/json');

// Verificar permissão de acesso
require_once '../../auth.php';
requireAccess(3);

// Incluir modelo de bilhetes
require_once '../../models/BilheteModel.php';

// Inicializar modelo de bilhetes
$bilheteModel = new BilheteModel();

// Processar ação solicitada
$action = $_GET['action'] ?? null;

try {
    switch ($action) {
        case 'cancelar':
            // Validar ID do bilhete
            $bilheteId = $_GET['id'] ?? null;

            if (!$bilheteId) {
                throw new Exception('ID do bilhete não fornecido.');
            }

            // Verificar se o bilhete pode ser cancelado
            // Aqui você pode adicionar lógicas adicionais, como:
            // - Verificar se o bilhete já não está cancelado
            // - Verificar se o concurso ainda não foi iniciado
            // - Verificar se o usuário tem permissão para cancelar

            // Atualizar status do bilhete
            $resultado = $bilheteModel->atualizarStatusBilhete($bilheteId, 'cancelado');

            if ($resultado) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Bilhete cancelado com sucesso!'
                ]);
            } else {
                throw new Exception('Erro ao cancelar bilhete.');
            }
            break;

        default:
            throw new Exception('Ação inválida.');
    }
} catch (Exception $e) {
    // Resposta de erro
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?> 