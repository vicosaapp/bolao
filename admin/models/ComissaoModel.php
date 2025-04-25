<?php
// Incluir a classe de banco de dados
require_once dirname(__DIR__, 2) . '/includes/Database.php';

class ComissaoModel {
    private $conn;
    private $database;

    public function __construct() {
        try {
            // Obter instância do banco de dados
            $this->database = Database::getInstance();
            $this->conn = $this->database->getConnection();
        } catch (Exception $e) {
            // Log do erro
            error_log("Erro ao estabelecer conexão no modelo de Comissão: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Criar a tabela de comissões se não existir
     * @return bool Sucesso da operação
     */
    public function criarTabelaSeNaoExistir() {
        $query = "CREATE TABLE IF NOT EXISTS comissoes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            vendedor_id INT NOT NULL,
            bilhete_id INT NOT NULL,
            valor_venda DECIMAL(10,2) NOT NULL,
            percentual DECIMAL(5,2) NOT NULL,
            valor_comissao DECIMAL(10,2) NOT NULL,
            status ENUM('pendente', 'pago', 'cancelado') DEFAULT 'pendente',
            data_venda DATETIME NOT NULL,
            data_pagamento DATETIME DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (vendedor_id) REFERENCES vendedores(id),
            FOREIGN KEY (bilhete_id) REFERENCES bilhetes(id)
        )";

        try {
            $this->conn->query($query);
            return true;
        } catch (Exception $e) {
            error_log("Erro ao criar tabela de comissões: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registrar comissão para venda de bilhete
     * @param array $dados Dados da comissão
     * @return int|false ID da comissão inserida ou false em caso de erro
     */
    public function registrarComissao($dados) {
        try {
            // Garantir que a tabela existe
            $this->criarTabelaSeNaoExistir();

            $query = "INSERT INTO comissoes (vendedor_id, bilhete_id, valor_venda, percentual, valor_comissao, data_venda) 
                      VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->database->executeQuery($query, [
                $dados['vendedor_id'],
                $dados['bilhete_id'],
                $dados['valor_venda'],
                $dados['percentual'],
                $dados['valor_comissao'],
                $dados['data_venda']
            ], 'iiddds');

            return $this->conn->insert_id;
        } catch (Exception $e) {
            error_log("Erro ao registrar comissão: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar status da comissão
     * @param int $id ID da comissão
     * @param string $status Novo status
     * @param string|null $data_pagamento Data de pagamento (para status 'pago')
     * @return bool Sucesso da operação
     */
    public function atualizarStatusComissao($id, $status, $data_pagamento = null) {
        try {
            $query = "UPDATE comissoes SET status = ?";
            $params = [$status];
            $types = 's';
            
            if ($status === 'pago' && $data_pagamento) {
                $query .= ", data_pagamento = ?";
                $params[] = $data_pagamento;
                $types .= 's';
            }
            
            $query .= " WHERE id = ?";
            $params[] = $id;
            $types .= 'i';
            
            $stmt = $this->database->executeQuery($query, $params, $types);
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Erro ao atualizar status da comissão: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Listar comissões com filtros opcionais
     * @param array $filtros Filtros para a busca
     * @return array Lista de comissões
     */
    public function listarComissoes($filtros = []) {
        try {
            // Garantir que a tabela existe
            $this->criarTabelaSeNaoExistir();
            
            $comissoes = [];
            $params = [];
            $types = '';
            
            // Construir query base
            $query = "SELECT c.*, v.nome AS vendedor_nome, v.codigo AS vendedor_codigo, 
                             b.numero AS bilhete_numero, b.concurso_id
                      FROM comissoes c
                      JOIN vendedores v ON c.vendedor_id = v.id
                      JOIN bilhetes b ON c.bilhete_id = b.id
                      WHERE 1=1";
            
            // Adicionar filtros
            if (!empty($filtros['vendedor_id'])) {
                $query .= " AND c.vendedor_id = ?";
                $params[] = $filtros['vendedor_id'];
                $types .= 'i';
            }
            
            if (!empty($filtros['status'])) {
                $query .= " AND c.status = ?";
                $params[] = $filtros['status'];
                $types .= 's';
            }
            
            if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
                $query .= " AND c.data_venda BETWEEN ? AND ?";
                $params[] = $filtros['data_inicio'] . ' 00:00:00';
                $params[] = $filtros['data_fim'] . ' 23:59:59';
                $types .= 'ss';
            } elseif (!empty($filtros['data_inicio'])) {
                $query .= " AND c.data_venda >= ?";
                $params[] = $filtros['data_inicio'] . ' 00:00:00';
                $types .= 's';
            } elseif (!empty($filtros['data_fim'])) {
                $query .= " AND c.data_venda <= ?";
                $params[] = $filtros['data_fim'] . ' 23:59:59';
                $types .= 's';
            }
            
            if (!empty($filtros['concurso_id'])) {
                $query .= " AND b.concurso_id = ?";
                $params[] = $filtros['concurso_id'];
                $types .= 'i';
            }
            
            // Adicionar ordenação
            $query .= " ORDER BY c.data_venda DESC";
            
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
            
            $stmt = $this->database->executeQuery($query, $params, $types);
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $comissoes[] = $row;
            }
            
            return $comissoes;
        } catch (Exception $e) {
            error_log("Erro ao listar comissões: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obter resumo das comissões
     * @param array $filtros Filtros para a busca
     * @return array Resumo das comissões
     */
    public function obterResumoComissoes($filtros = []) {
        try {
            // Garantir que a tabela existe
            $this->criarTabelaSeNaoExistir();
            
            $params = [];
            $types = '';
            
            // Construir query base
            $query = "SELECT 
                        COUNT(*) AS total_registros,
                        SUM(valor_venda) AS total_vendas,
                        SUM(valor_comissao) AS total_comissoes,
                        SUM(CASE WHEN status = 'pendente' THEN valor_comissao ELSE 0 END) AS comissoes_pendentes,
                        SUM(CASE WHEN status = 'pago' THEN valor_comissao ELSE 0 END) AS comissoes_pagas,
                        ROUND(AVG(percentual), 2) AS media_percentual
                      FROM comissoes
                      WHERE 1=1";
            
            // Adicionar filtros
            if (!empty($filtros['vendedor_id'])) {
                $query .= " AND vendedor_id = ?";
                $params[] = $filtros['vendedor_id'];
                $types .= 'i';
            }
            
            if (!empty($filtros['status'])) {
                $query .= " AND status = ?";
                $params[] = $filtros['status'];
                $types .= 's';
            }
            
            if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
                $query .= " AND data_venda BETWEEN ? AND ?";
                $params[] = $filtros['data_inicio'] . ' 00:00:00';
                $params[] = $filtros['data_fim'] . ' 23:59:59';
                $types .= 'ss';
            } elseif (!empty($filtros['data_inicio'])) {
                $query .= " AND data_venda >= ?";
                $params[] = $filtros['data_inicio'] . ' 00:00:00';
                $types .= 's';
            } elseif (!empty($filtros['data_fim'])) {
                $query .= " AND data_venda <= ?";
                $params[] = $filtros['data_fim'] . ' 23:59:59';
                $types .= 's';
            }
            
            $stmt = $this->database->executeQuery($query, $params, $types);
            $result = $stmt->get_result();
            $resumo = $result->fetch_assoc();
            
            // Para evitar valores nulos
            if ($resumo) {
                foreach ($resumo as $key => $value) {
                    if ($value === null) {
                        $resumo[$key] = 0;
                    }
                }
            }
            
            return $resumo;
        } catch (Exception $e) {
            error_log("Erro ao obter resumo de comissões: " . $e->getMessage());
            return [
                'total_registros' => 0,
                'total_vendas' => 0,
                'total_comissoes' => 0,
                'comissoes_pendentes' => 0,
                'comissoes_pagas' => 0,
                'media_percentual' => 0
            ];
        }
    }

    /**
     * Obter top vendedores com mais comissões
     * @param int $limite Quantidade de vendedores a retornar
     * @param array $filtros Filtros adicionais
     * @return array Lista de vendedores com suas comissões
     */
    public function obterTopVendedores($limite = 5, $filtros = []) {
        try {
            // Garantir que a tabela existe
            $this->criarTabelaSeNaoExistir();
            
            $params = [];
            $types = '';
            
            // Construir query base
            $query = "SELECT 
                        v.id, 
                        v.nome, 
                        v.codigo,
                        COUNT(c.id) AS total_vendas,
                        SUM(c.valor_venda) AS valor_total_vendas,
                        SUM(c.valor_comissao) AS valor_total_comissoes
                      FROM vendedores v
                      JOIN comissoes c ON v.id = c.vendedor_id
                      WHERE 1=1";
            
            // Adicionar filtros
            if (!empty($filtros['status'])) {
                $query .= " AND c.status = ?";
                $params[] = $filtros['status'];
                $types .= 's';
            }
            
            if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
                $query .= " AND c.data_venda BETWEEN ? AND ?";
                $params[] = $filtros['data_inicio'] . ' 00:00:00';
                $params[] = $filtros['data_fim'] . ' 23:59:59';
                $types .= 'ss';
            } elseif (!empty($filtros['data_inicio'])) {
                $query .= " AND c.data_venda >= ?";
                $params[] = $filtros['data_inicio'] . ' 00:00:00';
                $types .= 's';
            } elseif (!empty($filtros['data_fim'])) {
                $query .= " AND c.data_venda <= ?";
                $params[] = $filtros['data_fim'] . ' 23:59:59';
                $types .= 's';
            }
            
            // Agrupar por vendedor
            $query .= " GROUP BY v.id, v.nome, v.codigo";
            
            // Ordenar por total de comissões
            $query .= " ORDER BY valor_total_comissoes DESC";
            
            // Limitar resultados
            $query .= " LIMIT ?";
            $params[] = $limite;
            $types .= 'i';
            
            $stmt = $this->database->executeQuery($query, $params, $types);
            $result = $stmt->get_result();
            
            $vendedores = [];
            while ($row = $result->fetch_assoc()) {
                $vendedores[] = $row;
            }
            
            return $vendedores;
        } catch (Exception $e) {
            error_log("Erro ao obter top vendedores: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Pagar múltiplas comissões
     * @param array $ids IDs das comissões a serem pagas
     * @param string $data_pagamento Data do pagamento
     * @return int Número de comissões atualizadas
     */
    public function pagarComissoes($ids, $data_pagamento) {
        try {
            if (empty($ids)) {
                return 0;
            }
            
            // Preparar placeholders para IDs
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            
            // Construir query
            $query = "UPDATE comissoes SET status = 'pago', data_pagamento = ? WHERE id IN ($placeholders) AND status = 'pendente'";
            
            // Preparar parâmetros
            $params = [$data_pagamento];
            $types = 's';
            
            foreach ($ids as $id) {
                $params[] = $id;
                $types .= 'i';
            }
            
            $stmt = $this->database->executeQuery($query, $params, $types);
            return $stmt->affected_rows;
        } catch (Exception $e) {
            error_log("Erro ao pagar comissões: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obter comissão por ID
     * @param int $id ID da comissão
     * @return array|null Dados da comissão ou null se não encontrada
     */
    public function obterComissaoPorId($id) {
        try {
            $query = "SELECT c.*, v.nome AS vendedor_nome, v.codigo AS vendedor_codigo
                      FROM comissoes c
                      JOIN vendedores v ON c.vendedor_id = v.id
                      WHERE c.id = ?";
            
            $stmt = $this->database->executeQuery($query, [$id], 'i');
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Erro ao obter comissão: " . $e->getMessage());
            return null;
        }
    }
} 