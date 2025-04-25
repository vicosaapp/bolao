<?php
/**
 * Classe para gerenciamento de logs de atividades do sistema
 */
class LogAtividadeModel {
    private $db;

    /**
     * Construtor
     */
    public function __construct() {
        $this->db = DB::getInstance();
    }

    /**
     * Registra uma atividade do usuário no sistema
     * 
     * @param int $usuario_id ID do usuário que realizou a ação
     * @param string $tipo_atividade Tipo de atividade (login, logout, criar, editar, excluir, etc)
     * @param string $descricao Descrição detalhada da atividade
     * @return bool True se registrado com sucesso, False caso contrário
     */
    public function registrarAtividade($usuario_id, $tipo_atividade, $descricao) {
        try {
            $ip = $this->getIpUsuario();
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconhecido';
            
            $stmt = $this->db->prepare(
                "INSERT INTO log_atividades (usuario_id, tipo_atividade, descricao, ip, user_agent, data_registro) 
                 VALUES (?, ?, ?, ?, ?, NOW())"
            );
            
            $stmt->bindParam(1, $usuario_id);
            $stmt->bindParam(2, $tipo_atividade);
            $stmt->bindParam(3, $descricao);
            $stmt->bindParam(4, $ip);
            $stmt->bindParam(5, $user_agent);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao registrar atividade: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lista as atividades registradas com filtros opcionais
     * 
     * @param array $filtros Filtros a serem aplicados (usuario_id, tipo_atividade, data_inicio, data_fim)
     * @param int $pagina Número da página para paginação
     * @param int $por_pagina Itens por página
     * @return array Array com os logs de atividades e informações de paginação
     */
    public function listarAtividades($filtros = [], $pagina = 1, $por_pagina = 20) {
        try {
            $where = [];
            $params = [];
            $index = 1;
            
            // Aplicar filtros
            if (!empty($filtros['usuario_id'])) {
                $where[] = "usuario_id = ?";
                $params[$index++] = $filtros['usuario_id'];
            }
            
            if (!empty($filtros['tipo_atividade'])) {
                $where[] = "tipo_atividade = ?";
                $params[$index++] = $filtros['tipo_atividade'];
            }
            
            if (!empty($filtros['data_inicio'])) {
                $where[] = "DATE(data_registro) >= ?";
                $params[$index++] = $filtros['data_inicio'];
            }
            
            if (!empty($filtros['data_fim'])) {
                $where[] = "DATE(data_registro) <= ?";
                $params[$index++] = $filtros['data_fim'];
            }
            
            // Construir cláusula WHERE
            $whereClause = empty($where) ? "" : "WHERE " . implode(" AND ", $where);
            
            // Contar total de registros
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM log_atividades $whereClause");
            
            foreach ($params as $i => $value) {
                $stmt->bindValue($i, $value);
            }
            
            $stmt->execute();
            $totalRegistros = $stmt->fetch()['total'];
            
            // Calcular paginação
            $totalPaginas = ceil($totalRegistros / $por_pagina);
            $pagina = max(1, min($pagina, $totalPaginas));
            $offset = ($pagina - 1) * $por_pagina;
            
            // Buscar logs paginados
            $sql = "SELECT l.*, u.nome as nome_usuario 
                    FROM log_atividades l
                    LEFT JOIN usuarios u ON l.usuario_id = u.id
                    $whereClause
                    ORDER BY l.data_registro DESC
                    LIMIT $offset, $por_pagina";
            
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $i => $value) {
                $stmt->bindValue($i, $value);
            }
            
            $stmt->execute();
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'logs' => $logs,
                'paginacao' => [
                    'pagina_atual' => $pagina,
                    'total_paginas' => $totalPaginas,
                    'total_registros' => $totalRegistros,
                    'por_pagina' => $por_pagina
                ]
            ];
        } catch (PDOException $e) {
            error_log("Erro ao listar atividades: " . $e->getMessage());
            return [
                'logs' => [],
                'paginacao' => [
                    'pagina_atual' => 1,
                    'total_paginas' => 0,
                    'total_registros' => 0,
                    'por_pagina' => $por_pagina
                ]
            ];
        }
    }
    
    /**
     * Obtém o endereço IP do usuário
     * 
     * @return string Endereço IP
     */
    private function getIpUsuario() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
    }

    /**
     * Obtém detalhes de um log específico pelo ID
     * 
     * @param int $logId ID do log
     * @return array|boolean Dados do log ou false se não encontrado
     */
    public function obterDetalhesLog($logId) {
        try {
            $sql = "SELECT l.*, u.nome as nome_usuario 
                    FROM log_atividades l
                    LEFT JOIN usuarios u ON l.usuario_id = u.id
                    WHERE l.id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $logId, PDO::PARAM_INT);
            $stmt->execute();
            
            $log = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$log) {
                return false;
            }
            
            return $log;
        } catch (PDOException $e) {
            error_log("Erro ao obter detalhes do log: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtém os tipos de atividades distintos para uso em filtros
     * 
     * @return array Lista de tipos de atividades
     */
    public function obterTiposAtividades() {
        try {
            $sql = "SELECT DISTINCT tipo_atividade FROM log_atividades ORDER BY tipo_atividade";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Erro ao obter tipos de atividades: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Registra uma ação específica do sistema
     * 
     * @param int $usuario_id ID do usuário realizando a ação
     * @param string $tipo_acao Tipo da ação (criar_concurso, editar_usuario, etc)
     * @param string $descricao Descrição da ação
     * @param array $dados_adicionais Dados adicionais para debugging
     * @return bool True se registrado com sucesso, False caso contrário
     */
    public function registrarLog($usuario_id, $tipo_acao, $descricao, $dados_adicionais = null) {
        try {
            $ip = $this->getIpUsuario();
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconhecido';
            
            $sql = "INSERT INTO log_atividades 
                   (usuario_id, tipo_atividade, descricao, ip, user_agent, dados_adicionais, data_registro) 
                   VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $usuario_id, PDO::PARAM_INT);
            $stmt->bindParam(2, $tipo_acao, PDO::PARAM_STR);
            $stmt->bindParam(3, $descricao, PDO::PARAM_STR);
            $stmt->bindParam(4, $ip, PDO::PARAM_STR);
            $stmt->bindParam(5, $user_agent, PDO::PARAM_STR);
            
            // Converter dados adicionais para JSON se existirem
            $json_dados = null;
            if ($dados_adicionais !== null) {
                $json_dados = json_encode($dados_adicionais, JSON_UNESCAPED_UNICODE);
            }
            $stmt->bindParam(6, $json_dados, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao registrar log de ação: " . $e->getMessage());
            return false;
        }
    }
} 