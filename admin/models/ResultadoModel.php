<?php
// Incluir a classe de banco de dados
require_once dirname(__DIR__, 2) . '/includes/Database.php';

class ResultadoModel {
    private $conn;
    private $database;

    public function __construct() {
        try {
            // Obter instância do banco de dados
            $this->database = Database::getInstance();
            $this->conn = $this->database->getConnection();
        } catch (Exception $e) {
            // Log do erro
            error_log("Erro ao estabelecer conexão no modelo de Resultado: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Adicionar novo resultado
     * @param array $dados Dados do resultado
     * @return int|false ID do resultado inserido ou false em caso de erro
     */
    public function adicionarResultado($dados) {
        try {
            $query = "INSERT INTO resultados (concurso_id, numeros_sorteados, data_sorteio, status) 
                      VALUES (?, ?, ?, ?)";
            
            $stmt = $this->database->executeQuery($query, [
                $dados['concurso_id'], 
                $dados['numeros_sorteados'], 
                $dados['data_sorteio'], 
                $dados['status']
            ], 'isss');

            return $this->conn->insert_id;
        } catch (Exception $e) {
            error_log("Erro ao adicionar resultado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar status do resultado
     * @param int $id ID do resultado
     * @param string $status Novo status
     * @return bool Sucesso da operação
     */
    public function atualizarStatusResultado($id, $status) {
        try {
            $query = "UPDATE resultados SET status = ? WHERE id = ?";
            $this->database->executeQuery($query, [$status, $id], 'si');
            return true;
        } catch (Exception $e) {
            error_log("Erro ao atualizar status do resultado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Listar resultados com filtros
     * @param array $filtros Filtros para busca de resultados
     * @return array Lista de resultados
     */
    public function listarResultadosComFiltros($filtros = []) {
        $resultados = [];
        
        // Construir query base
        $query = "SELECT r.id, r.concurso_id, r.numeros_sorteados, r.data_sorteio, r.status, 
                         c.nome AS nome_concurso
                  FROM resultados r
                  JOIN concursos c ON r.concurso_id = c.id
                  WHERE 1=1";
        
        // Adicionar filtros
        $params = [];
        $types = '';
        
        if (!empty($filtros['concurso_id'])) {
            $query .= " AND r.concurso_id = ?";
            $params[] = $filtros['concurso_id'];
            $types .= 'i';
        }
        
        if (!empty($filtros['status'])) {
            $query .= " AND r.status = ?";
            $params[] = $filtros['status'];
            $types .= 's';
        }
        
        // Adicionar ordenação
        $query .= " ORDER BY r.data_sorteio DESC";
        
        // Adicionar limite e offset
        if (isset($filtros['limite'])) {
            $query .= " LIMIT ?";
            $params[] = $filtros['limite'];
            $types .= 'i';
            
            if (isset($filtros['offset'])) {
                $query .= " OFFSET ?";
                $params[] = $filtros['offset'];
                $types .= 'i';
            }
        }
        
        try {
            // Preparar e executar a consulta
            $stmt = $this->database->executeQuery($query, $params, $types);
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $resultados[] = $row;
            }

            return $resultados;
        } catch (Exception $e) {
            error_log("Erro ao listar resultados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Contar resultados com filtros
     * @param array $filtros Filtros para contagem de resultados
     * @return int Número total de resultados
     */
    public function contarResultadosComFiltros($filtros = []) {
        // Construir query base
        $query = "SELECT COUNT(*) AS total FROM resultados r WHERE 1=1";
        
        // Adicionar filtros
        $params = [];
        $types = '';
        
        if (!empty($filtros['concurso_id'])) {
            $query .= " AND r.concurso_id = ?";
            $params[] = $filtros['concurso_id'];
            $types .= 'i';
        }
        
        if (!empty($filtros['status'])) {
            $query .= " AND r.status = ?";
            $params[] = $filtros['status'];
            $types .= 's';
        }
        
        try {
            // Preparar e executar a consulta
            $stmt = $this->database->executeQuery($query, $params, $types);
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return $row['total'];
        } catch (Exception $e) {
            error_log("Erro ao contar resultados: " . $e->getMessage());
            return 0;
        }
    }
}
?> 