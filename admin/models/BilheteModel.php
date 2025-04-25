<?php
require_once dirname(dirname(__DIR__)) . '/includes/Database.php';

class BilheteModel {
    private $db;

    public function __construct() {
        try {
            $this->db = Database::getInstance()->getConnection();
        } catch (Exception $e) {
            error_log("Erro ao estabelecer conexão no modelo de Bilhete: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Contar total de bilhetes
     * @return int Número total de bilhetes
     */
    public function contarBilhetes() {
        $query = "SELECT COUNT(*) AS total FROM bilhetes";
        $result = $this->db->query($query);
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    /**
     * Calcular total de vendas
     * @return float Valor total de vendas
     */
    public function calcularTotalVendas() {
        $query = "SELECT SUM(valor) AS total FROM bilhetes WHERE status = 'pago'";
        $result = $this->db->query($query);
        $row = $result->fetch_assoc();
        return $row['total'] ? $row['total'] : 0;
    }

    /**
     * Listar bilhetes
     * @param int $limite Número máximo de bilhetes a retornar
     * @return array Lista de bilhetes
     */
    public function listarBilhetes($limite = 10) {
        $bilhetes = [];
        $query = "SELECT b.*, c.nome as concurso_nome, c.numero as concurso_numero, u.nome as usuario_nome
                  FROM bilhetes b
                  LEFT JOIN concursos c ON b.concurso_id = c.id
                  LEFT JOIN usuarios u ON b.usuario_id = u.id
                  ORDER BY b.data_compra DESC 
                  LIMIT ?";
        
        try {
            $stmt = $this->db->prepare($query);
            
            // Verificar se a preparação foi bem-sucedida
            if ($stmt === false) {
                throw new Exception("Erro ao preparar a consulta de listagem de bilhetes: " . $this->db->error);
            }
            
            $stmt->bind_param('i', $limite);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $bilhetes[] = $row;
            }

            return $bilhetes;
        } catch (Exception $e) {
            error_log("Erro ao listar bilhetes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Adicionar novo bilhete
     * @param array $dados Dados do bilhete
     * @return int|false ID do bilhete inserido ou false em caso de erro
     */
    public function adicionarBilhete($dados) {
        try {
            $query = "INSERT INTO bilhetes (numero, concurso_id, usuario_id, data_compra, valor, status, dezenas) 
                      VALUES (?, ?, ?, NOW(), ?, ?, ?)";
            
            $stmt = $this->db->prepare($query);
            
            // Verificar se a preparação foi bem-sucedida
            if ($stmt === false) {
                throw new Exception("Erro ao preparar a consulta de adição de bilhete: " . $this->db->error);
            }
            
            $stmt->bind_param('siidss', $dados['numero'], $dados['concurso_id'], $dados['usuario_id'], 
                            $dados['valor'], $dados['status'], $dados['dezenas']);
            $stmt->execute();

            return $this->db->insert_id;
        } catch (Exception $e) {
            error_log("Erro ao adicionar bilhete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar status do bilhete
     * @param int $id ID do bilhete
     * @param string $status Novo status
     * @return bool Sucesso da operação
     */
    public function atualizarStatusBilhete($id, $status) {
        try {
            $query = "UPDATE bilhetes SET status = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            
            // Verificar se a preparação foi bem-sucedida
            if ($stmt === false) {
                throw new Exception("Erro ao preparar a consulta de atualização de status: " . $this->db->error);
            }
            
            $stmt->bind_param('si', $status, $id);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            error_log("Erro ao atualizar status do bilhete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Listar bilhetes com filtros
     * @param array $filtros Filtros para busca de bilhetes
     * @return array Lista de bilhetes
     */
    public function listarBilhetesComFiltros($filtros = []) {
        $bilhetes = [];
        
        // Construir query base
        $query = "SELECT b.*, c.nome as concurso_nome, c.numero as concurso_numero, u.nome as usuario_nome
                  FROM bilhetes b
                  LEFT JOIN concursos c ON b.concurso_id = c.id
                  LEFT JOIN usuarios u ON b.usuario_id = u.id
                  WHERE 1=1";
        
        // Adicionar filtros
        $params = [];
        $types = '';
        
        if (!empty($filtros['status'])) {
            $query .= " AND b.status = ?";
            $params[] = $filtros['status'];
            $types .= 's';
        }
        
        if (!empty($filtros['concurso_id'])) {
            $query .= " AND b.concurso_id = ?";
            $params[] = $filtros['concurso_id'];
            $types .= 'i';
        }
        
        if (!empty($filtros['usuario_id'])) {
            $query .= " AND b.usuario_id = ?";
            $params[] = $filtros['usuario_id'];
            $types .= 'i';
        }
        
        // Adicionar ordenação
        $query .= " ORDER BY b.data_compra DESC";
        
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
            // Preparar a consulta
            $stmt = $this->db->prepare($query);
            
            // Verificar se a preparação foi bem-sucedida
            if ($stmt === false) {
                throw new Exception("Erro ao preparar a consulta: " . $this->db->error);
            }
            
            // Vincular parâmetros se houver
            if (!empty($params)) {
                // Criar uma matriz de referências para bind_param
                $bindParams = array();
                $bindParams[] = &$types;
                foreach ($params as $key => $value) {
                    $bindParams[] = &$params[$key];
                }
                
                // Chamar bind_param com os parâmetros
                call_user_func_array(array($stmt, 'bind_param'), $bindParams);
            }
            
            // Executar a consulta
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $bilhetes[] = $row;
            }

            return $bilhetes;
        } catch (Exception $e) {
            error_log("Erro ao listar bilhetes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Contar bilhetes com filtros
     * @param array $filtros Filtros para busca de bilhetes
     * @return int Número total de bilhetes
     */
    public function contarBilhetesComFiltros($filtros = []) {
        try {
            // Construir query base
            $query = "SELECT COUNT(*) as total FROM bilhetes b WHERE 1=1";
            
            // Adicionar filtros
            $params = [];
            $types = '';
            
            if (!empty($filtros['vendedor_id'])) {
                $query .= " AND b.vendedor_id = ?";
                $params[] = $filtros['vendedor_id'];
                $types .= 'i';
            }
            
            if (!empty($filtros['status'])) {
                $query .= " AND b.status = ?";
                $params[] = $filtros['status'];
                $types .= 's';
            }
            
            if (!empty($filtros['concurso_id'])) {
                $query .= " AND b.concurso_id = ?";
                $params[] = $filtros['concurso_id'];
                $types .= 'i';
            }
            
            if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
                $query .= " AND b.data_compra BETWEEN ? AND ?";
                $params[] = $filtros['data_inicio'] . ' 00:00:00';
                $params[] = $filtros['data_fim'] . ' 23:59:59';
                $types .= 'ss';
            }
            
            // Preparar e executar a consulta
            $stmt = $this->db->prepare($query);
            
            if ($stmt === false) {
                throw new Exception("Erro ao preparar a consulta: " . $this->db->error);
            }
            
            // Bind parameters se houver parâmetros
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Erro ao contar bilhetes com filtros: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Listar bilhetes do usuário
     * @param array $filtros Filtros para busca de bilhetes
     * @return array Lista de bilhetes do usuário
     */
    public function listarBilhetesDoUsuario($filtros) {
        if (empty($filtros['usuario_id'])) {
            return [];
        }
        
        return $this->listarBilhetesComFiltros($filtros);
    }

    /**
     * Contar bilhetes do usuário
     * @param array $filtros Filtros para busca de bilhetes
     * @return int Número de bilhetes do usuário
     */
    public function contarBilhetesDoUsuario($filtros) {
        if (empty($filtros['usuario_id'])) {
            return 0;
        }
        
        return $this->contarBilhetesComFiltros($filtros);
    }

    /**
     * Calcular total apostado por usuário
     * @param int $usuarioId ID do usuário
     * @return float Total apostado pelo usuário
     */
    public function calcularTotalApostadoPorUsuario($usuarioId) {
        $query = "SELECT SUM(valor) AS total FROM bilhetes WHERE usuario_id = ?";
        
        try {
            $stmt = $this->db->prepare($query);
            
            // Verificar se a preparação foi bem-sucedida
            if ($stmt === false) {
                throw new Exception("Erro ao preparar a consulta de total apostado: " . $this->db->error);
            }
            
            $stmt->bind_param('i', $usuarioId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['total'] ? $row['total'] : 0;
        } catch (Exception $e) {
            error_log("Erro ao calcular total apostado: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calcular total de prêmios ganhos por usuário
     * @param int $usuarioId ID do usuário
     * @return float Total de prêmios ganhos pelo usuário
     */
    public function calcularTotalPremiosGanhosPorUsuario($usuarioId) {
        $query = "SELECT SUM(valor_premio) AS total FROM bilhetes WHERE usuario_id = ? AND status = 'premiado'";
        
        try {
            $stmt = $this->db->prepare($query);
            
            // Verificar se a preparação foi bem-sucedida
            if ($stmt === false) {
                throw new Exception("Erro ao preparar a consulta de total de prêmios: " . $this->db->error);
            }
            
            $stmt->bind_param('i', $usuarioId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['total'] ? $row['total'] : 0;
        } catch (Exception $e) {
            error_log("Erro ao calcular total de prêmios: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Contar bilhetes vencedores por usuário
     * @param int $usuarioId ID do usuário
     * @return int Número de bilhetes vencedores do usuário
     */
    public function contarBilhetesVencedoresPorUsuario($usuarioId) {
        $query = "SELECT COUNT(*) AS total FROM bilhetes WHERE usuario_id = ? AND status = 'premiado'";
        
        try {
            $stmt = $this->db->prepare($query);
            
            // Verificar se a preparação foi bem-sucedida
            if ($stmt === false) {
                throw new Exception("Erro ao preparar a consulta de contagem de bilhetes vencedores: " . $this->db->error);
            }
            
            $stmt->bind_param('i', $usuarioId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['total'] ? $row['total'] : 0;
        } catch (Exception $e) {
            error_log("Erro ao contar bilhetes vencedores: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Contar concursos participados por usuário
     * @param int $usuarioId ID do usuário
     * @return int Número de concursos diferentes em que o usuário participou
     */
    public function contarConcursosParticipados($usuarioId) {
        $query = "SELECT COUNT(DISTINCT concurso_id) AS total FROM bilhetes WHERE usuario_id = ?";
        
        try {
            $stmt = $this->db->prepare($query);
            
            // Verificar se a preparação foi bem-sucedida
            if ($stmt === false) {
                throw new Exception("Erro ao preparar a consulta de contagem de concursos: " . $this->db->error);
            }
            
            $stmt->bind_param('i', $usuarioId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['total'] ? $row['total'] : 0;
        } catch (Exception $e) {
            error_log("Erro ao contar concursos participados: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obter detalhes de um bilhete específico
     * @param int $bilheteId ID do bilhete
     * @return array|null Dados do bilhete ou null se não encontrado
     */
    public function obterDetalhesBilhete($bilheteId) {
        try {
            $query = "SELECT b.*, c.nome as concurso_nome, c.numero as concurso_numero,
                      u.nome as usuario_nome, a.nome as apostador_nome, a.email as apostador_email,
                      a.telefone as apostador_telefone
                      FROM bilhetes b
                      LEFT JOIN concursos c ON b.concurso_id = c.id
                      LEFT JOIN usuarios u ON b.usuario_id = u.id
                      LEFT JOIN apostadores a ON b.apostador_id = a.id
                      WHERE b.id = ?";
            
            $stmt = $this->db->prepare($query);
            
            // Verificar se a preparação foi bem-sucedida
            if ($stmt === false) {
                throw new Exception("Erro ao preparar a consulta de detalhes do bilhete: " . $this->db->error);
            }
            
            $stmt->bind_param('i', $bilheteId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return null;
            }
            
            $bilhete = $result->fetch_assoc();
            
            // Buscar jogos do bilhete, se necessário
            $jogosQuery = "SELECT * FROM jogos_bilhete WHERE bilhete_id = ?";
            $jogosStmt = $this->db->prepare($jogosQuery);
            
            // Verificar se a preparação foi bem-sucedida
            if ($jogosStmt === false) {
                throw new Exception("Erro ao preparar a consulta de jogos do bilhete: " . $this->db->error);
            }
            
            $jogosStmt->bind_param('i', $bilheteId);
            $jogosStmt->execute();
            $jogosResult = $jogosStmt->get_result();
            
            $jogos = [];
            while ($jogo = $jogosResult->fetch_assoc()) {
                $jogos[] = $jogo;
            }
            
            // Adicionar jogos ao bilhete
            $bilhete['jogos'] = $jogos;
            
            return $bilhete;
        } catch (Exception $e) {
            error_log("Erro ao obter detalhes do bilhete: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Contar bilhetes de um usuário em um concurso específico
     * @param int $usuarioId ID do usuário
     * @param int $concursoId ID do concurso
     * @return int Número de bilhetes
     */
    public function contarBilhetesUsuarioConcurso($usuarioId, $concursoId) {
        try {
            $query = "SELECT COUNT(*) as total FROM bilhetes 
                      WHERE usuario_id = ? AND concurso_id = ?";
            
            $stmt = $this->db->prepare($query);
            if ($stmt === false) {
                throw new Exception("Erro ao preparar a consulta: " . $this->db->error);
            }
            
            $stmt->bind_param('ii', $usuarioId, $concursoId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return intval($row['total']);
        } catch (Exception $e) {
            error_log("Erro ao contar bilhetes do usuário no concurso: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Listar bilhetes de um usuário em um concurso específico
     * @param int $usuarioId ID do usuário
     * @param int $concursoId ID do concurso
     * @return array Lista de bilhetes
     */
    public function listarBilhetesUsuarioConcurso($usuarioId, $concursoId) {
        $bilhetes = [];
        
        try {
            $query = "SELECT b.*, c.nome as concurso_nome, c.numero as concurso_numero
                      FROM bilhetes b
                      JOIN concursos c ON b.concurso_id = c.id
                      WHERE b.usuario_id = ? AND b.concurso_id = ?
                      ORDER BY b.data_compra DESC";
            
            $stmt = $this->db->prepare($query);
            if ($stmt === false) {
                throw new Exception("Erro ao preparar a consulta: " . $this->db->error);
            }
            
            $stmt->bind_param('ii', $usuarioId, $concursoId);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $bilhetes[] = $row;
            }

            return $bilhetes;
        } catch (Exception $e) {
            error_log("Erro ao listar bilhetes do usuário no concurso: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calcular valor total de bilhetes por concurso e vendedor
     * @param int $vendedor_id ID do vendedor
     * @param int $concurso_id ID do concurso
     * @return float Valor total dos bilhetes
     */
    public function calcularValorTotalPorConcurso($vendedor_id, $concurso_id) {
        try {
            $query = "SELECT SUM(valor) as total FROM bilhetes 
                      WHERE vendedor_id = ? AND concurso_id = ? AND status != 'cancelado'";
            
            $stmt = $this->db->prepare($query);
            if ($stmt === false) {
                throw new Exception("Erro ao preparar a consulta: " . $this->db->error);
            }
            
            $stmt->bind_param('ii', $vendedor_id, $concurso_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Erro ao calcular valor total por concurso: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calcular valor total de bilhetes por vendedor
     * @param int $vendedor_id ID do vendedor
     * @return float Valor total dos bilhetes
     */
    public function calcularValorTotalPorVendedor($vendedor_id) {
        try {
            $query = "SELECT SUM(valor) as total FROM bilhetes 
                      WHERE vendedor_id = ? AND status != 'cancelado'";
            
            $stmt = $this->db->prepare($query);
            if ($stmt === false) {
                throw new Exception("Erro ao preparar a consulta: " . $this->db->error);
            }
            
            $stmt->bind_param('i', $vendedor_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Erro ao calcular valor total por vendedor: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calcular comissões por período
     * @param int $vendedor_id ID do vendedor
     * @param string $data_inicio Data inicial (formato Y-m-d)
     * @param string $data_fim Data final (formato Y-m-d)
     * @param float $taxa_comissao Taxa de comissão em porcentagem (ex: 10 para 10%)
     * @return float Total de comissões no período
     */
    public function calcularComissoesPorPeriodo($vendedor_id, $data_inicio, $data_fim, $taxa_comissao = 10) {
        try {
            $query = "SELECT SUM(valor) as total FROM bilhetes 
                      WHERE vendedor_id = ? 
                      AND (status = 'pago' OR status = 'premiado')
                      AND data_compra BETWEEN ? AND ?";
            
            $stmt = $this->db->prepare($query);
            
            if ($stmt === false) {
                throw new Exception("Erro ao preparar a consulta: " . $this->db->error);
            }
            
            // Formatar datas para o formato do banco
            $data_inicio_formatada = $data_inicio . ' 00:00:00';
            $data_fim_formatada = $data_fim . ' 23:59:59';
            
            $stmt->bind_param('iss', $vendedor_id, $data_inicio_formatada, $data_fim_formatada);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            $total_vendas = $row['total'] ?? 0;
            
            // Calcular comissão
            $comissao = $total_vendas * ($taxa_comissao / 100);
            
            return $comissao;
        } catch (Exception $e) {
            error_log("Erro ao calcular comissões por período: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Calcular comissões totais
     * @param int $vendedor_id ID do vendedor
     * @param float $taxa_comissao Taxa de comissão em porcentagem (ex: 10 para 10%)
     * @return float Total de comissões de todos os tempos
     */
    public function calcularComissoesTotais($vendedor_id, $taxa_comissao = 10) {
        try {
            $query = "SELECT SUM(valor) as total FROM bilhetes 
                      WHERE vendedor_id = ? 
                      AND (status = 'pago' OR status = 'premiado')";
            
            $stmt = $this->db->prepare($query);
            
            if ($stmt === false) {
                throw new Exception("Erro ao preparar a consulta: " . $this->db->error);
            }
            
            $stmt->bind_param('i', $vendedor_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            $total_vendas = $row['total'] ?? 0;
            
            // Calcular comissão
            $comissao = $total_vendas * ($taxa_comissao / 100);
            
            return $comissao;
        } catch (Exception $e) {
            error_log("Erro ao calcular comissões totais: " . $e->getMessage());
            return 0;
        }
    }
}
?> 