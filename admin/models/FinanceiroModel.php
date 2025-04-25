<?php
// Incluir a classe de banco de dados
require_once dirname(__DIR__, 2) . '/includes/Database.php';

class FinanceiroModel {
    private $conn;
    private $database;

    public function __construct() {
        try {
            // Obter instância do banco de dados
            $this->database = Database::getInstance();
            $this->conn = $this->database->getConnection();
        } catch (Exception $e) {
            // Log do erro
            error_log("Erro ao estabelecer conexão no modelo Financeiro: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Criar a tabela de transações financeiras se não existir
     * @return bool Sucesso da operação
     */
    public function criarTabelaSeNaoExistir() {
        $query = "CREATE TABLE IF NOT EXISTS transacoes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tipo ENUM('receita', 'despesa') NOT NULL,
            categoria VARCHAR(50) NOT NULL,
            descricao VARCHAR(255) NOT NULL,
            valor DECIMAL(10,2) NOT NULL,
            data_transacao DATE NOT NULL,
            concurso_id INT NULL,
            usuario_id INT NULL,
            forma_pagamento VARCHAR(50) NULL,
            status ENUM('pendente', 'concluida', 'cancelada') DEFAULT 'pendente',
            comprovante_url VARCHAR(255) NULL,
            observacao TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (concurso_id) REFERENCES concursos(id) ON DELETE SET NULL,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
        )";

        try {
            $this->conn->query($query);
            return true;
        } catch (Exception $e) {
            error_log("Erro ao criar tabela de transações: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registrar nova transação financeira
     * @param array $dados Dados da transação
     * @return int|false ID da transação inserida ou false em caso de erro
     */
    public function registrarTransacao($dados) {
        try {
            // Garantir que a tabela existe
            $this->criarTabelaSeNaoExistir();

            $query = "INSERT INTO transacoes (tipo, categoria, descricao, valor, data_transacao, 
                      concurso_id, usuario_id, forma_pagamento, status, comprovante_url, observacao) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->database->executeQuery($query, [
                $dados['tipo'],
                $dados['categoria'],
                $dados['descricao'],
                $dados['valor'],
                $dados['data_transacao'],
                $dados['concurso_id'] ?? null,
                $dados['usuario_id'] ?? null,
                $dados['forma_pagamento'] ?? null,
                $dados['status'] ?? 'pendente',
                $dados['comprovante_url'] ?? null,
                $dados['observacao'] ?? null
            ], 'sssdsiiisss');

            return $this->conn->insert_id;
        } catch (Exception $e) {
            error_log("Erro ao registrar transação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar status da transação
     * @param int $id ID da transação
     * @param string $status Novo status
     * @return bool Sucesso da operação
     */
    public function atualizarStatusTransacao($id, $status) {
        try {
            $query = "UPDATE transacoes SET status = ? WHERE id = ?";
            
            $stmt = $this->database->executeQuery($query, [$status, $id], 'si');
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Erro ao atualizar status da transação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar transação financeira
     * @param int $id ID da transação
     * @param array $dados Dados atualizados
     * @return bool Sucesso da operação
     */
    public function atualizarTransacao($id, $dados) {
        try {
            $fields = [];
            $params = [];
            $types = '';

            foreach ($dados as $key => $value) {
                if ($key !== 'id') {
                    $fields[] = "$key = ?";
                    $params[] = $value;
                    
                    // Definir o tipo do parâmetro
                    if (in_array($key, ['valor'])) {
                        $types .= 'd'; // decimal
                    } elseif (in_array($key, ['concurso_id', 'usuario_id'])) {
                        $types .= 'i'; // integer
                    } else {
                        $types .= 's'; // string
                    }
                }
            }

            $query = "UPDATE transacoes SET " . implode(', ', $fields) . " WHERE id = ?";
            $params[] = $id;
            $types .= 'i';

            $stmt = $this->database->executeQuery($query, $params, $types);
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Erro ao atualizar transação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Excluir transação financeira
     * @param int $id ID da transação
     * @return bool Sucesso da operação
     */
    public function excluirTransacao($id) {
        try {
            $query = "DELETE FROM transacoes WHERE id = ?";
            
            $stmt = $this->database->executeQuery($query, [$id], 'i');
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Erro ao excluir transação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Listar transações com filtros opcionais
     * @param array $filtros Filtros para a busca
     * @return array Lista de transações
     */
    public function listarTransacoes($filtros = []) {
        try {
            // Garantir que a tabela existe
            $this->criarTabelaSeNaoExistir();
            
            $transacoes = [];
            $params = [];
            $types = '';
            
            // Construir query base
            $query = "SELECT t.*, c.numero AS concurso_numero, u.nome AS usuario_nome 
                      FROM transacoes t
                      LEFT JOIN concursos c ON t.concurso_id = c.id
                      LEFT JOIN usuarios u ON t.usuario_id = u.id
                      WHERE 1=1";
            
            // Adicionar filtros
            if (!empty($filtros['tipo'])) {
                $query .= " AND t.tipo = ?";
                $params[] = $filtros['tipo'];
                $types .= 's';
            }
            
            if (!empty($filtros['categoria'])) {
                $query .= " AND t.categoria = ?";
                $params[] = $filtros['categoria'];
                $types .= 's';
            }
            
            if (!empty($filtros['status'])) {
                $query .= " AND t.status = ?";
                $params[] = $filtros['status'];
                $types .= 's';
            }
            
            if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
                $query .= " AND t.data_transacao BETWEEN ? AND ?";
                $params[] = $filtros['data_inicio'];
                $params[] = $filtros['data_fim'];
                $types .= 'ss';
            } elseif (!empty($filtros['data_inicio'])) {
                $query .= " AND t.data_transacao >= ?";
                $params[] = $filtros['data_inicio'];
                $types .= 's';
            } elseif (!empty($filtros['data_fim'])) {
                $query .= " AND t.data_transacao <= ?";
                $params[] = $filtros['data_fim'];
                $types .= 's';
            }
            
            if (!empty($filtros['concurso_id'])) {
                $query .= " AND t.concurso_id = ?";
                $params[] = $filtros['concurso_id'];
                $types .= 'i';
            }
            
            if (!empty($filtros['usuario_id'])) {
                $query .= " AND t.usuario_id = ?";
                $params[] = $filtros['usuario_id'];
                $types .= 'i';
            }
            
            if (!empty($filtros['busca'])) {
                $query .= " AND (t.descricao LIKE ? OR t.categoria LIKE ?)";
                $busca = '%' . $filtros['busca'] . '%';
                $params[] = $busca;
                $params[] = $busca;
                $types .= 'ss';
            }
            
            // Adicionar ordenação
            $query .= " ORDER BY t.data_transacao DESC, t.id DESC";
            
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
                $transacoes[] = $row;
            }
            
            return $transacoes;
        } catch (Exception $e) {
            error_log("Erro ao listar transações: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obter resumo financeiro
     * @param array $filtros Filtros para a busca
     * @return array Resumo financeiro
     */
    public function obterResumoFinanceiro($filtros = []) {
        try {
            // Garantir que a tabela existe
            $this->criarTabelaSeNaoExistir();
            
            $params = [];
            $types = '';
            
            // Construir query base
            $query = "SELECT 
                        SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) AS total_receitas,
                        SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END) AS total_despesas,
                        SUM(CASE WHEN tipo = 'receita' THEN valor ELSE -valor END) AS saldo,
                        COUNT(*) AS total_transacoes,
                        COUNT(CASE WHEN tipo = 'receita' THEN 1 END) AS num_receitas,
                        COUNT(CASE WHEN tipo = 'despesa' THEN 1 END) AS num_despesas,
                        MAX(data_transacao) AS ultima_transacao
                      FROM transacoes
                      WHERE status <> 'cancelada'";
            
            // Adicionar filtros
            if (!empty($filtros['data_inicio']) && !empty($filtros['data_fim'])) {
                $query .= " AND data_transacao BETWEEN ? AND ?";
                $params[] = $filtros['data_inicio'];
                $params[] = $filtros['data_fim'];
                $types .= 'ss';
            } elseif (!empty($filtros['data_inicio'])) {
                $query .= " AND data_transacao >= ?";
                $params[] = $filtros['data_inicio'];
                $types .= 's';
            } elseif (!empty($filtros['data_fim'])) {
                $query .= " AND data_transacao <= ?";
                $params[] = $filtros['data_fim'];
                $types .= 's';
            }
            
            if (!empty($filtros['concurso_id'])) {
                $query .= " AND concurso_id = ?";
                $params[] = $filtros['concurso_id'];
                $types .= 'i';
            }
            
            $stmt = $this->database->executeQuery($query, $params, $types);
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                return $row;
            }
            
            return [
                'total_receitas' => 0,
                'total_despesas' => 0,
                'saldo' => 0,
                'total_transacoes' => 0,
                'num_receitas' => 0,
                'num_despesas' => 0,
                'ultima_transacao' => null
            ];
        } catch (Exception $e) {
            error_log("Erro ao obter resumo financeiro: " . $e->getMessage());
            return [
                'total_receitas' => 0,
                'total_despesas' => 0,
                'saldo' => 0,
                'total_transacoes' => 0,
                'num_receitas' => 0,
                'num_despesas' => 0,
                'ultima_transacao' => null
            ];
        }
    }

    /**
     * Obter transação por ID
     * @param int $id ID da transação
     * @return array|null Dados da transação ou null se não encontrada
     */
    public function obterTransacaoPorId($id) {
        try {
            $query = "SELECT t.*, c.numero AS concurso_numero, u.nome AS usuario_nome 
                      FROM transacoes t
                      LEFT JOIN concursos c ON t.concurso_id = c.id
                      LEFT JOIN usuarios u ON t.usuario_id = u.id
                      WHERE t.id = ?";
            
            $stmt = $this->database->executeQuery($query, [$id], 'i');
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                return $row;
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Erro ao obter transação: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obter categorias de transações
     * @return array Lista de categorias
     */
    public function obterCategorias() {
        try {
            $categoriasReceita = [
                'venda_bilhete' => 'Venda de Bilhetes',
                'premio_nao_reclamado' => 'Prêmio Não Reclamado',
                'investimento' => 'Investimento',
                'outro' => 'Outro'
            ];
            
            $categoriasDespesa = [
                'pagamento_premio' => 'Pagamento de Prêmio',
                'comissao' => 'Comissão de Vendedor',
                'marketing' => 'Marketing e Publicidade',
                'operacional' => 'Despesa Operacional',
                'imposto' => 'Impostos e Taxas',
                'salario' => 'Salários e Encargos',
                'aluguel' => 'Aluguel e Serviços',
                'software' => 'Software e Tecnologia',
                'outro' => 'Outro'
            ];
            
            return [
                'receita' => $categoriasReceita,
                'despesa' => $categoriasDespesa
            ];
        } catch (Exception $e) {
            error_log("Erro ao obter categorias: " . $e->getMessage());
            return ['receita' => [], 'despesa' => []];
        }
    }

    /**
     * Obter dados para gráfico financeiro
     * @param string $periodo Período (mensal, anual)
     * @param int $ano Ano
     * @param int $mes Mês (se período for mensal)
     * @return array Dados para o gráfico
     */
    public function obterDadosGrafico($periodo = 'mensal', $ano = null, $mes = null) {
        try {
            // Definir ano e mês padrão se não informados
            if (!$ano) {
                $ano = date('Y');
            }
            
            if (!$mes && $periodo === 'mensal') {
                $mes = date('m');
            }
            
            $params = [];
            $types = '';
            
            if ($periodo === 'mensal' && $mes) {
                // Dados diários para um mês específico
                $query = "SELECT 
                            DAY(data_transacao) AS label,
                            SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) AS receitas,
                            SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END) AS despesas
                          FROM transacoes
                          WHERE status <> 'cancelada'
                            AND YEAR(data_transacao) = ?
                            AND MONTH(data_transacao) = ?
                          GROUP BY DAY(data_transacao)
                          ORDER BY DAY(data_transacao)";
                
                $params = [$ano, $mes];
                $types = 'ii';
                
                // Determinar número de dias no mês
                $dias_no_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
                
                // Inicializar array de resultados para todos os dias do mês
                $resultados = [];
                for ($i = 1; $i <= $dias_no_mes; $i++) {
                    $resultados[] = [
                        'label' => $i,
                        'receitas' => 0,
                        'despesas' => 0
                    ];
                }
            } else {
                // Dados mensais para um ano específico
                $query = "SELECT 
                            MONTH(data_transacao) AS label,
                            SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) AS receitas,
                            SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END) AS despesas
                          FROM transacoes
                          WHERE status <> 'cancelada'
                            AND YEAR(data_transacao) = ?
                          GROUP BY MONTH(data_transacao)
                          ORDER BY MONTH(data_transacao)";
                
                $params = [$ano];
                $types = 'i';
                
                // Inicializar array de resultados para todos os meses do ano
                $resultados = [];
                for ($i = 1; $i <= 12; $i++) {
                    $resultados[] = [
                        'label' => $i,
                        'receitas' => 0,
                        'despesas' => 0
                    ];
                }
            }
            
            $stmt = $this->database->executeQuery($query, $params, $types);
            $result = $stmt->get_result();
            
            // Preencher resultados com os dados do banco
            while ($row = $result->fetch_assoc()) {
                $index = $row['label'] - 1; // Ajustar para índice baseado em 0
                if (isset($resultados[$index])) {
                    $resultados[$index]['receitas'] = (float)$row['receitas'];
                    $resultados[$index]['despesas'] = (float)$row['despesas'];
                }
            }
            
            return $resultados;
        } catch (Exception $e) {
            error_log("Erro ao obter dados para gráfico: " . $e->getMessage());
            return [];
        }
    }
} 