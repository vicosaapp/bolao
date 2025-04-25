<?php
/**
 * Script para cancelar concursos
 * Requer nível de acesso de administrador (3)
 */

require_once '../../includes/config.php';
require_once '../../includes/functions.php';
require_once '../models/DB.php';
require_once '../models/ConcursoModel.php';
require_once '../models/BilheteModel.php';
require_once '../models/LogAtividadeModel.php';
require_once '../auth.php';

// Definir header para retornar JSON
header('Content-Type: application/json');

// Verificar se é administrador
requireAccess(3); // Nível 3: superadmin

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['sucesso' => false, 'erro' => 'Método não permitido']);
    exit;
}

// Verificar se o ID do concurso foi enviado
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'ID do concurso não informado']);
    exit;
}

// Obter parâmetros
$concursoId = intval($_POST['id']);
$observacao = isset($_POST['observacao']) ? $_POST['observacao'] : 'Cancelado pelo administrador';

// Iniciar conexão com o banco
$db = DB::getInstance();
$concursoModel = new ConcursoModel();
$bilheteModel = new BilheteModel();
$logModel = new LogAtividadeModel();

try {
    // Iniciar transação
    $db->beginTransaction();
    
    // Cancelar o concurso
    $stmt = $db->prepare("UPDATE concursos SET status = 'cancelado', data_atualizacao = NOW(), observacoes = ? WHERE id = ?");
    $stmt->bindParam(1, $observacao);
    $stmt->bindParam(2, $concursoId);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        throw new Exception("Concurso não encontrado ou já está cancelado");
    }
    
    // Registrar no log
    $usuario_id = $_SESSION['usuario_id'];
    $descricao = "Concurso ID #$concursoId cancelado. Motivo: $observacao";
    $logModel->registrarLog($usuario_id, 'cancelar_concurso', $descricao);
    
    // Verificar se existe algum bilhete para este concurso
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM bilhetes WHERE concurso_id = ?");
    $stmt->bindParam(1, $concursoId);
    $stmt->execute();
    $result = $stmt->fetch();
    $totalBilhetes = $result['total'] ?? 0;
    
    // Se houver bilhetes, cancelar todos
    if ($totalBilhetes > 0) {
        $stmt = $db->prepare("UPDATE bilhetes SET status = 'cancelado', data_atualizacao = NOW() WHERE concurso_id = ?");
        $stmt->bindParam(1, $concursoId);
        $stmt->execute();
        
        // Registrar no log
        $descricao = "Cancelados $totalBilhetes bilhetes relacionados ao concurso ID #$concursoId";
        $logModel->registrarLog($usuario_id, 'cancelar_bilhetes', $descricao);
    }
    
    // Commit da transação
    $db->commit();
    
    // Resposta de sucesso
    echo json_encode([
        'sucesso' => true, 
        'mensagem' => "Concurso cancelado com sucesso. " . ($totalBilhetes > 0 ? "$totalBilhetes bilhetes foram cancelados." : "Não havia bilhetes para este concurso.")
    ]);
    
} catch (Exception $e) {
    // Rollback em caso de erro
    $db->rollBack();
    
    // Log do erro
    $logModel->registrarLog($_SESSION['usuario_id'], 'erro', "Erro ao cancelar concurso ID #$concursoId: " . $e->getMessage());
    
    // Resposta de erro
    echo json_encode(['sucesso' => false, 'erro' => "Erro ao cancelar concurso: " . $e->getMessage()]);
}
?> 