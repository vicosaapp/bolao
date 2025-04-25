<?php
// Verificar permissões
require_once '../auth.php';
requireAccess(3); // Nível mínimo: administrador (3)

// Incluir modelos necessários
require_once '../models/ClienteModel.php';

// Inicializar modelo de clientes
$clienteModel = new ClienteModel();

// Definir cabeçalhos para JSON
header('Content-Type: application/json');

// Responder solicitação com base na ação
$acao = $_REQUEST['acao'] ?? '';

switch ($acao) {
    case 'gerar_codigo':
        // Gerar código único para novo cliente
        $codigo = $clienteModel->gerarCodigoCliente();
        echo json_encode([
            'status' => 'success',
            'codigo' => $codigo
        ]);
        break;
        
    case 'adicionar':
        // Validar dados
        if (empty($_POST['nome'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'O nome/razão social é obrigatório'
            ]);
            exit;
        }
        
        // Preparar dados
        $dados = [
            'codigo' => $_POST['codigo'],
            'nome' => $_POST['nome'],
            'cpf_cnpj' => $_POST['cpf_cnpj'] ?? '',
            'telefone' => $_POST['telefone'] ?? '',
            'email' => $_POST['email'] ?? '',
            'endereco' => $_POST['endereco'] ?? '',
            'cidade' => $_POST['cidade'] ?? '',
            'estado' => $_POST['estado'] ?? '',
            'cep' => $_POST['cep'] ?? '',
            'tipo' => $_POST['tipo'] ?? 'pessoa_fisica',
            'status' => $_POST['status'] ?? 'ativo'
        ];
        
        // Adicionar cliente
        $resultado = $clienteModel->adicionarCliente($dados);
        
        if ($resultado) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Cliente adicionado com sucesso',
                'id' => $resultado
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Erro ao adicionar cliente'
            ]);
        }
        break;
        
    case 'atualizar':
        // Validar dados
        if (empty($_POST['id']) || empty($_POST['nome'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'ID e nome/razão social são obrigatórios'
            ]);
            exit;
        }
        
        $id = (int) $_POST['id'];
        
        // Preparar dados
        $dados = [
            'nome' => $_POST['nome'],
            'cpf_cnpj' => $_POST['cpf_cnpj'] ?? '',
            'telefone' => $_POST['telefone'] ?? '',
            'email' => $_POST['email'] ?? '',
            'endereco' => $_POST['endereco'] ?? '',
            'cidade' => $_POST['cidade'] ?? '',
            'estado' => $_POST['estado'] ?? '',
            'cep' => $_POST['cep'] ?? '',
            'tipo' => $_POST['tipo'] ?? 'pessoa_fisica',
            'status' => $_POST['status'] ?? 'ativo'
        ];
        
        // Atualizar cliente
        $resultado = $clienteModel->atualizarCliente($id, $dados);
        
        if ($resultado) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Cliente atualizado com sucesso'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Erro ao atualizar cliente'
            ]);
        }
        break;
        
    case 'excluir':
        // Validar dados
        if (empty($_POST['id'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'ID do cliente é obrigatório'
            ]);
            exit;
        }
        
        $id = (int) $_POST['id'];
        
        // Excluir cliente
        $resultado = $clienteModel->excluirCliente($id);
        
        if ($resultado) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Cliente excluído com sucesso'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Erro ao excluir cliente'
            ]);
        }
        break;
        
    case 'buscar':
        // Validar dados
        if (empty($_GET['id'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'ID do cliente é obrigatório'
            ]);
            exit;
        }
        
        $id = (int) $_GET['id'];
        
        // Buscar cliente
        $cliente = $clienteModel->buscarClientePorId($id);
        
        if ($cliente) {
            echo json_encode([
                'status' => 'success',
                'cliente' => $cliente
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Cliente não encontrado'
            ]);
        }
        break;
        
    case 'filtrar':
        // Preparar filtros
        $filtros = [
            'tipo' => $_POST['tipo'] ?? '',
            'status' => $_POST['status'] ?? '',
            'busca' => $_POST['busca'] ?? ''
        ];
        
        // Buscar clientes com filtros
        $clientes = $clienteModel->listarClientes($filtros);
        
        echo json_encode([
            'status' => 'success',
            'clientes' => $clientes,
            'total' => count($clientes)
        ]);
        break;
        
    default:
        // Ação desconhecida
        echo json_encode([
            'status' => 'error',
            'message' => 'Ação desconhecida'
        ]);
        break;
} 