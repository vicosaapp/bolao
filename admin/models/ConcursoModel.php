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
                      (SELECT COUNT(*) FROM bilhetes b WHERE b.concurso_id = c.id) as bilhetes_vendidos
                      FROM concursos c
                      WHERE c.status = 'em_andamento'
                      ORDER BY c.data_inicio DESC";
            
            $result = $this->db->query($query);
            
            if ($result === false) {
                error_log("Erro ao executar consulta listarConcursosAtivos: " . $this->db->error);
                return [];
            }
            
            $concursos = [];
            while ($row = $result->fetch_assoc()) {
                // Adicionar dados adicionais para compatibilidade
                $row['titulo'] = $row['nome']; // Título é o mesmo que nome
                $row['data_sorteio'] = $row['data_fim']; // Data de sorteio é a data fim
                $row['descricao'] = 'Concurso #' . $row['numero']; // Descrição padrão
                $row['valor_bilhete'] = 10.00; // Valor padrão do bilhete
                $row['total_bilhetes'] = 1000; // Total padrão de bilhetes
                
                $concursos[] = $row;
            }
            
            return $concursos;
        } catch (Exception $e) {
            error_log("Erro ao listar concursos ativos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Listar concursos finalizados
     * @param int $limite Número máximo de concursos a retornar
     * @return array Lista de concursos finalizados
     */
    public function listarConcursosFinalizados($limite = 10) {
        $concursos = [];
        
        try {
            // Consulta usando apenas campos que existem na tabela
            $query = "SELECT c.id, c.numero, c.nome, c.data_inicio, c.data_fim, c.status, c.valor_premios,
                      (SELECT COUNT(*) FROM bilhetes b WHERE b.concurso_id = c.id) as total_bilhetes,
                      (SELECT COUNT(*) FROM bilhetes b WHERE b.concurso_id = c.id AND b.status = 'premiado') as total_ganhadores
                      FROM concursos c 
                      WHERE c.status = 'finalizado'
                      ORDER BY c.data_fim DESC 
                      LIMIT " . (int)$limite;
            
            // Executar consulta diretamente
            $result = $this->db->query($query);
            
            if ($result === false) {
                error_log("Erro ao executar consulta listarConcursosFinalizados: " . $this->db->error);
                return [];
            }
            
            while ($row = $result->fetch_assoc()) {
                // Adicionar dados adicionais para compatibilidade
                $row['titulo'] = $row['nome']; // Título é o mesmo que nome
                $row['data_sorteio'] = $row['data_fim']; // Data de sorteio é a data fim
                
                $concursos[] = $row;
            }

            return $concursos;
        } catch (Exception $e) {
            error_log("Erro ao listar concursos finalizados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obter concurso por ID
     * @param int $id ID do concurso
     * @return array|null Dados do concurso ou null se não encontrado
     */
    public function obterConcursoPorId($id) {
        try {
            $query = "SELECT c.id, c.numero, c.nome, c.data_inicio, c.data_fim, c.status, c.valor_premios, 
                     (SELECT COUNT(*) FROM bilhetes b WHERE b.concurso_id = c.id) as bilhetes_vendidos,
                     (SELECT SUM(valor) FROM bilhetes b WHERE b.concurso_id = c.id) as valor_arrecadado
                     FROM concursos c 
                     WHERE c.id = ?";
            
            $stmt = $this->db->prepare($query);
            
            // Verificar se a preparação foi bem-sucedida
            if ($stmt === false) {
                error_log("Erro ao preparar consulta obterConcursoPorId: " . $this->db->error);
                return null;
            }
            
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $concurso = $result->fetch_assoc();
                
                // Adicionar dados adicionais para compatibilidade
                $concurso['titulo'] = $concurso['nome']; // Título é o mesmo que nome
                $concurso['data_sorteio'] = $concurso['data_fim']; // Data de sorteio é a data fim
                
                return $concurso;
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Erro ao obter concurso por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Listar próximos sorteios
     * @param int $limite Limite de sorteios a serem retornados
     * @return array Lista de próximos sorteios
     */
    public function listarProximosSorteios($limite = 5) {
        try {
            $query = "SELECT c.id, c.numero, c.nome, c.data_sorteio, c.valor_premios 
                      FROM concursos c 
                      WHERE c.status = 'em_andamento' AND c.data_sorteio > NOW() 
                      ORDER BY c.data_sorteio ASC 
                      LIMIT ?";
            
            $stmt = $this->db->prepare($query);
            if ($stmt === false) {
                throw new Exception("Erro ao preparar a consulta: " . $this->db->error);
            }
            
            $stmt->bind_param('i', $limite);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $sorteios = [];
            while ($row = $result->fetch_assoc()) {
                $sorteios[] = $row;
            }
            
            return $sorteios;
        } catch (Exception $e) {
            error_log("Erro ao listar próximos sorteios: " . $e->getMessage());
            return [];
        }
    }
}
?> 