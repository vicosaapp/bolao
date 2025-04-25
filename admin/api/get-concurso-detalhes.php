<?php
/**
 * API para obter detalhes de um concurso específico
 * Endpoint: /admin/api/get-concurso-detalhes.php?id=X
 */

// Verificar acesso
require_once '../auth.php';
requireAccess(1); // Nível mínimo: usuário comum

// Incluir modelos necessários
require_once '../models/ConcursoModel.php';
require_once '../models/BilheteModel.php';

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID do concurso não fornecido.'
    ]);
    exit;
}

// Obter ID do concurso
$concursoId = intval($_GET['id']);

// Inicializar modelos
$concursoModel = new ConcursoModel();
$bilheteModel = new BilheteModel();

// Obter dados básicos do concurso
$concurso = obterDadosConcurso($concursoId, $concursoModel);

// Verificar se o concurso existe
if (!$concurso) {
    echo json_encode([
        'success' => false,
        'message' => 'Concurso não encontrado.'
    ]);
    exit;
}

// Retornar dados do concurso em formato JSON
echo json_encode([
    'success' => true,
    'concurso' => $concurso
]);
exit;

/**
 * Obtém os dados completos de um concurso
 * 
 * @param int $id ID do concurso
 * @param ConcursoModel $concursoModel Instância do modelo de concursos
 * @return array|null Dados do concurso ou null se não encontrado
 */
function obterDadosConcurso($id, $concursoModel) {
    global $bilheteModel;
    
    // Obter dados básicos do concurso e complementar com informações adicionais
    $baseQuery = "SELECT c.*, 
                 (SELECT COUNT(*) FROM bilhetes WHERE concurso_id = c.id) as bilhetes_vendidos,
                 (SELECT SUM(valor) FROM bilhetes WHERE concurso_id = c.id) as valor_arrecadado,
                 (SELECT COUNT(DISTINCT usuario_id) FROM bilhetes WHERE concurso_id = c.id) as participantes,
                 c.valor_premios as premiacao_total
                 FROM concursos c 
                 WHERE c.id = ?";
    
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare($baseQuery);
        
        if ($stmt === false) {
            throw new Exception("Erro ao preparar consulta: " . $db->error);
        }
        
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        $concurso = $result->fetch_assoc();
        
        // Adicionar informações complementares se o concurso estiver finalizado
        if ($concurso['status'] === 'finalizado') {
            // Buscar números sorteados e outros dados de resultado, se houver
            $resultadoQuery = "SELECT numeros_sorteados, data_sorteio, 
                               (SELECT COUNT(*) FROM bilhetes WHERE concurso_id = ? AND status = 'premiado') as total_ganhadores
                               FROM resultados WHERE concurso_id = ? LIMIT 1";
            
            $stmtResultado = $db->prepare($resultadoQuery);
            if ($stmtResultado !== false) {
                $stmtResultado->bind_param('ii', $id, $id);
                $stmtResultado->execute();
                $resultResultado = $stmtResultado->get_result();
                
                if ($resultResultado->num_rows > 0) {
                    $resultado = $resultResultado->fetch_assoc();
                    $concurso['numeros_sorteados'] = $resultado['numeros_sorteados'];
                    $concurso['data_sorteio'] = $resultado['data_sorteio'];
                    $concurso['total_ganhadores'] = $resultado['total_ganhadores'];
                }
            }
        }
        
        return $concurso;
    } catch (Exception $e) {
        error_log("Erro ao obter dados completos do concurso: " . $e->getMessage());
        return null;
    }
}
?> 