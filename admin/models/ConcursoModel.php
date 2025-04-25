<?php
// Incluir a classe de banco de dados
require_once dirname(dirname(__DIR__)) . '/includes/Database.php';

class ConcursoModel {
    private $db;

    public function __construct() {
        try {
            // Obter instância do banco de dados
            $this->db = Database::getInstance()->getConnection();
        } catch (Exception $e) {
            // Log do erro
            error_log("Erro ao estabelecer conexão no modelo de Concurso: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Contar total de concursos
     * @return int Número total de concursos
     */
    public function contarConcursos() {
        $query = "SELECT COUNT(*) AS total FROM concursos";
        $result = $this->db->query($query);
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    /**
     * Contar concursos ativos
     * @return int Número de concursos em andamento
     */
    public function contarConcursosAtivos() {
        $query = "SELECT COUNT(*) AS total FROM concursos WHERE status = 'em_andamento'";
        $result = $this->db->query($query);
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    /**
     * Listar concursos
     * @param int $limite Número máximo de concursos a retornar
     * @return array Lista de concursos
     */
    public function listarConcursos($limite = 10) {
        $concursos = [];
        $query = "SELECT id, numero, nome, data_inicio, data_fim, status, valor_premios 
                  FROM concursos 
                  ORDER BY data_inicio DESC 
                  LIMIT ?";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $limite);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $concursos[] = $row;
            }

            return $concursos;
        } catch (Exception $e) {
            error_log("Erro ao listar concursos: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Adicionar novo concurso
     * @param array $dados Dados do concurso
     * @return int|false ID do concurso inserido ou false em caso de erro
     */
    public function adicionarConcurso($dados) {
        try {
            $query = "INSERT INTO concursos (numero, nome, data_inicio, data_fim, valor_premios) 
                      VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('isssd', $dados['numero'], $dados['nome'], $dados['data_inicio'], $dados['data_fim'], $dados['valor_premios']);
            $stmt->execute();

            return $this->db->insert_id;
        } catch (Exception $e) {
            error_log("Erro ao adicionar concurso: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar status do concurso
     * @param int $id ID do concurso
     * @param string $status Novo status
     * @return bool Sucesso da operação
     */
    public function atualizarStatusConcurso($id, $status) {
        try {
            $query = "UPDATE concursos SET status = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('si', $status, $id);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            error_log("Erro ao atualizar status do concurso: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Listar concursos com filtros
     * @param array $filtros Filtros para busca de concursos
     * @return array Lista de concursos
     */
    public function listarConcursosComFiltros($filtros = []) {
        $concursos = [];
        
        // Construir query base
        $query = "SELECT id, numero, nome, data_inicio, data_fim, status, valor_premios 
                  FROM concursos 
                  WHERE 1=1";
        
        // Adicionar filtro de status, se fornecido
        $params = [];
        $types = '';
        
        if (!empty($filtros['status'])) {
            $query .= " AND status = ?";
            $params[] = $filtros['status'];
            $types .= 's';
        }
        
        // Adicionar ordenação
        $query .= " ORDER BY data_inicio DESC";
        
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
            $stmt = $this->db->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $concursos[] = $row;
            }

            return $concursos;
        } catch (Exception $e) {
            error_log("Erro ao listar concursos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Contar concursos com filtros
     * @param array $filtros Filtros para busca de concursos
     * @return int Número de concursos
     */
    public function contarConcursosComFiltros($filtros = []) {
        // Construir query base
        $query = "SELECT COUNT(*) as total FROM concursos WHERE 1=1";
        
        // Adicionar filtro de status, se fornecido
        $params = [];
        $types = '';
        
        if (!empty($filtros['status'])) {
            $query .= " AND status = ?";
            $params[] = $filtros['status'];
            $types .= 's';
        }
        
        try {
            // Preparar e executar a consulta
            $stmt = $this->db->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['total'];
        } catch (Exception $e) {
            error_log("Erro ao contar concursos: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obter o próximo concurso programado
     * @return array|null Dados do próximo concurso ou null se não houver
     */
    public function obterProximoConcurso() {
        try {
            $dataAtual = date('Y-m-d');
            $query = "SELECT id, numero, nome, data_inicio, data_fim, status, valor_premios, 
                      (SELECT COUNT(*) FROM bilhetes WHERE concurso_id = concursos.id) as bilhetes_vendidos,
                      (SELECT SUM(valor) FROM bilhetes WHERE concurso_id = concursos.id) as valor_arrecadado,
                      valor_premios as premiacao_total
                      FROM concursos 
                      WHERE data_fim > ? AND status != 'cancelado'
                      ORDER BY data_fim ASC 
                      LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('s', $dataAtual);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Erro ao obter próximo concurso: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obter o concurso atual (em andamento)
     * @return array|null Dados do concurso atual ou null se não houver
     */
    public function obterConcursoAtual() {
        try {
            $dataAtual = date('Y-m-d');
            $query = "SELECT id, numero, nome, data_inicio, data_fim, status, valor_premios,
                      (SELECT COUNT(*) FROM bilhetes WHERE concurso_id = concursos.id) as bilhetes_vendidos,
                      (SELECT SUM(valor) FROM bilhetes WHERE concurso_id = concursos.id) as valor_arrecadado,
                      valor_premios as premiacao_total
                      FROM concursos 
                      WHERE status = 'em_andamento' OR (data_fim = ? AND status != 'cancelado')
                      ORDER BY data_fim ASC 
                      LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('s', $dataAtual);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Erro ao obter concurso atual: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Listar concursos ativos (em andamento ou pendentes)
     * @return array Lista de concursos ativos
     */
    public function listarConcursosAtivos() {
        try {
            $query = "SELECT c.id, c.numero, c.nome, c.data_inicio, c.data_fim, c.status, c.valor_premios,
                      c.nome as titulo, 
                      c.descricao,
                      c.valor_bilhete,
                      c.total_bilhetes,
                      c.data_sorteio,
                      (SELECT COUNT(*) FROM bilhetes WHERE bilhetes.concurso_id = c.id) as bilhetes_vendidos
                      FROM concursos c
                      WHERE c.status IN ('em_andamento', 'pendente')
                      ORDER BY c.data_inicio DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $concursos = [];
            while ($row = $result->fetch_assoc()) {
                $concursos[] = $row;
            }
            
            return $concursos;
        } catch (Exception $e) {
            error_log("Erro ao listar concursos ativos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista os concursos finalizados com limite opcional
     * @param int $limite Número máximo de concursos a retornar (0 = sem limite)
     * @return array Lista de concursos finalizados
     */
    public function listarConcursosFinalizados($limite = 5) {
        try {
            $sql = "SELECT c.*, 
                    c.nome as titulo,
                    c.descricao,
                    c.valor_bilhete,
                    c.data_sorteio,
                    (SELECT COUNT(*) FROM bilhetes WHERE bilhetes.concurso_id = c.id) AS total_bilhetes,
                    (SELECT COUNT(*) FROM bilhetes WHERE bilhetes.concurso_id = c.id AND bilhetes.status = 'premiado') AS total_ganhadores
                    FROM concursos c 
                    WHERE c.status = 'finalizado'
                    ORDER BY c.data_sorteio DESC";
            
            if ($limite > 0) {
                $sql .= " LIMIT " . (int)$limite;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $concursos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            return $concursos;
        } catch (Exception $e) {
            error_log("Erro ao listar concursos finalizados: " . $e->getMessage());
            return [];
        }
    }
}
?> 