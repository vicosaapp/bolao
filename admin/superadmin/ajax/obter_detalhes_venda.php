<?php
session_start();
require_once '../../../config.php';
require_once '../../../includes/functions.php';
require_once '../../models/BilheteModel.php';

// Verificar se o usuário está autenticado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário não autenticado']);
    exit;
}

// Verificar se o método é GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido']);
    exit;
}

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'ID não fornecido']);
    exit;
}

$id = intval($_GET['id']);

// Utilizar a classe BilheteModel para obter os detalhes
$bilheteModel = new BilheteModel($db);
$bilhete = $bilheteModel->obterDetalhesBilhete($id);

if (!$bilhete) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Bilhete não encontrado']);
    exit;
}

// Formatar resposta
$resposta = [
    'sucesso' => true,
    'id' => $bilhete['id'],
    'numero' => $bilhete['numero'],
    'concurso' => $bilhete['concurso_nome'] . ' (' . $bilhete['concurso_numero'] . ')',
    'valor' => 'R$ ' . number_format($bilhete['valor'], 2, ',', '.'),
    'status' => $bilhete['status'],
    'data_compra' => date('d/m/Y H:i:s', strtotime($bilhete['data_criacao'])),
    'usuario' => $bilhete['usuario_nome'] ?? 'N/A',
    'apostador' => [
        'nome' => $bilhete['apostador_nome'] ?? 'N/A',
        'email' => $bilhete['apostador_email'] ?? 'N/A',
        'telefone' => $bilhete['apostador_telefone'] ?? 'N/A'
    ],
    'jogos' => $bilhete['jogos'] ?? []
];

echo json_encode($resposta);
exit;
?> 