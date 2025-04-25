<?php
session_start();

// Configurar cabeçalho para resposta JSON
header('Content-Type: application/json');

// Verificar permissão de acesso (nível mínimo de administrador)
require_once '../../auth.php';
requireAccess(3);

// Incluir modelo de usuário
require_once '../../models/UsuarioModel.php';

// Inicializar modelo de usuário
$usuarioModel = new UsuarioModel();

// Processar ação solicitada
$action = $_GET['action'] ?? null;

try {
    switch ($action) {
        case 'listar':
            // Obter lista de usuários
            $usuarios = $usuarioModel->listarUsuarios();
            echo json_encode($usuarios);
            break;
            
        case 'obter':
            // Validar ID do usuário
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID do usuário não fornecido');
            }
            
            // Obter dados do usuário
            $usuario = $usuarioModel->obterUsuarioPorId($id);
            
            if (!$usuario) {
                throw new Exception('Usuário não encontrado');
            }
            
            echo json_encode($usuario);
            break;
        
        case 'adicionar':
            // Validar dados de entrada
            $nome = $_POST['nome'] ?? null;
            $email = $_POST['email'] ?? null;
            $senha = $_POST['senha'] ?? null;
            $nivel = $_POST['nivel_acesso'] ?? null;
            $status = isset($_POST['status']) ? 1 : 0;

            // Verificar campos obrigatórios
            if (!$nome || !$email || !$senha || !$nivel) {
                throw new Exception('Todos os campos são obrigatórios.');
            }

            // Validar email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email inválido.');
            }

            // Adicionar usuário
            $usuarioId = $usuarioModel->adicionarUsuario($nome, $email, $senha, $nivel, $status);

            echo json_encode([
                'success' => true, 
                'message' => 'Usuário adicionado com sucesso!', 
                'id' => $usuarioId
            ]);
            break;

        case 'excluir':
            // Validar ID do usuário
            $usuarioId = $_POST['id'] ?? null;

            if (!$usuarioId) {
                throw new Exception('ID do usuário não fornecido.');
            }

            // Impedir exclusão do próprio usuário
            if ($usuarioId == $_SESSION['user_id']) {
                throw new Exception('Você não pode excluir sua própria conta.');
            }

            // Excluir usuário
            $resultado = $usuarioModel->excluirUsuario($usuarioId);

            echo json_encode([
                'success' => true, 
                'message' => 'Usuário excluído com sucesso!'
            ]);
            break;

        case 'atualizar':
            // Validar dados de entrada
            $usuarioId = $_POST['id'] ?? null;
            $nome = $_POST['nome'] ?? null;
            $email = $_POST['email'] ?? null;
            $nivel = $_POST['nivel_acesso'] ?? null;
            $status = isset($_POST['status']) ? 1 : 0;

            // Verificar campos obrigatórios
            if (!$usuarioId || !$nome || !$email || !$nivel) {
                throw new Exception('Todos os campos são obrigatórios.');
            }

            // Validar email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email inválido.');
            }

            // Senha é opcional na atualização
            $senha = !empty($_POST['senha']) ? $_POST['senha'] : null;

            // Atualizar usuário
            $resultado = $usuarioModel->atualizarUsuario($usuarioId, $nome, $email, $nivel, $status, $senha);

            echo json_encode([
                'success' => true, 
                'message' => 'Usuário atualizado com sucesso!'
            ]);
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