<?php
// Iniciar sessão
session_start();

// Configurações de cabeçalho para respostas JSON
header('Content-Type: application/json');

// Verificar permissões
require_once '../auth.php';
requireAccess(3); // Nível mínimo: administrador (3)

// Incluir modelo de usuário
require_once '../models/UsuarioModel.php';

// Inicializar modelo
$usuarioModel = new UsuarioModel();

// Verificar ação solicitada
$action = $_GET['action'] ?? null;

try {
    switch ($action) {
        case 'adicionar':
            // Validar dados de entrada
            $dados = [
                'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING),
                'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
                'senha' => $_POST['senha'] ?? '',
                'nivel' => filter_input(INPUT_POST, 'nivel', FILTER_VALIDATE_INT),
                'status' => filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING)
            ];

            // Validações básicas
            if (empty($dados['nome']) || empty($dados['email']) || empty($dados['senha'])) {
                throw new Exception('Preencha todos os campos obrigatórios.');
            }

            if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email inválido.');
            }

            // Tentar adicionar usuário
            $resultado = $usuarioModel->adicionarUsuario($dados);

            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuário adicionado com sucesso!',
                    'id' => $resultado
                ]);
            } else {
                throw new Exception('Erro ao adicionar usuário.');
            }
            break;

        case 'excluir':
            // Obter ID do usuário
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

            if (!$id) {
                throw new Exception('ID de usuário inválido.');
            }

            // Verificar se não está tentando excluir o próprio usuário
            if ($id == $_SESSION['user_id']) {
                throw new Exception('Você não pode excluir seu próprio usuário.');
            }

            // Tentar excluir usuário
            $resultado = $usuarioModel->excluirUsuario($id);

            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuário excluído com sucesso!'
                ]);
            } else {
                throw new Exception('Erro ao excluir usuário.');
            }
            break;

        case 'atualizar':
            // Obter ID do usuário
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

            // Validar dados de entrada
            $dados = [
                'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING),
                'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
                'senha' => $_POST['senha'] ?? '',
                'nivel' => filter_input(INPUT_POST, 'nivel', FILTER_VALIDATE_INT),
                'status' => filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING)
            ];

            // Validações básicas
            if (empty($dados['nome']) || empty($dados['email'])) {
                throw new Exception('Preencha todos os campos obrigatórios.');
            }

            if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email inválido.');
            }

            // Tentar atualizar usuário
            $resultado = $usuarioModel->atualizarUsuario($id, $dados);

            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuário atualizado com sucesso!'
                ]);
            } else {
                throw new Exception('Erro ao atualizar usuário.');
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