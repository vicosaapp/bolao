<?php
// Iniciar sessão e verificar autenticação
session_start();
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
require_once '../models/LogAtividadeModel.php';

// Checar se usuário está logado e tem permissão
requireAccess(3); // Nível 3 = Superadmin

// Processar apenas solicitações GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido']);
    exit;
}

// Verificar se ID foi fornecido
if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
    exit;
}

$logId = (int) $_GET['id'];
$logModel = new LogAtividadeModel();

try {
    // Obter detalhes do log
    $detalhes = $logModel->obterDetalhesLog($logId);
    
    if (!$detalhes) {
        echo json_encode(['status' => 'error', 'message' => 'Log não encontrado']);
        exit;
    }
    
    // Determinar a classe de badge baseada no tipo de atividade
    $badgeClass = 'badge-secondary';
    switch ($detalhes['tipo_atividade']) {
        case 'login':
        case 'logout':
            $badgeClass = 'badge-info';
            break;
        case 'criar':
        case 'cadastrar':
            $badgeClass = 'badge-success';
            break;
        case 'editar':
        case 'atualizar':
            $badgeClass = 'badge-primary';
            break;
        case 'excluir':
        case 'deletar':
            $badgeClass = 'badge-danger';
            break;
        case 'cancelar':
            $badgeClass = 'badge-warning';
            break;
    }
    
    // Formatando data e hora
    $dataHora = date('d/m/Y H:i:s', strtotime($detalhes['data_registro']));
    
    // Preparar resposta
    $resposta = [
        'status' => 'success',
        'data' => [
            'id' => $detalhes['id'],
            'usuario_nome' => $detalhes['nome_usuario'] ?? 'Sistema',
            'tipo' => $detalhes['tipo_atividade'],
            'descricao' => $detalhes['descricao'],
            'ip' => $detalhes['ip'],
            'user_agent' => $detalhes['user_agent'],
            'data_hora' => $dataHora,
            'badge_class' => $badgeClass
        ]
    ];
    
    // Adicionar dados adicionais se existirem
    if (!empty($detalhes['dados_adicionais'])) {
        $resposta['data']['dados_adicionais'] = $detalhes['dados_adicionais'];
    }
    
    echo json_encode($resposta);
    
} catch (Exception $e) {
    error_log("Erro ao obter detalhes do log: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Erro ao processar a solicitação']);
} 