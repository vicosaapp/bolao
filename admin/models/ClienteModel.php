<?php
// Incluir a classe de banco de dados
require_once dirname(__DIR__, 2) . '/includes/Database.php';

class ClienteModel {
    private $conn;
    private $database;

    public function __construct() {
        try {
            // Obter instância do banco de dados
            $this->database = Database::getInstance();
            $this->conn = $this->database->getConnection();
        } catch (Exception $e) {
            // Log do erro
            error_log("Erro ao estabelecer conexão no modelo de Cliente: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Criar a tabela de clientes se ela não existir
     * @return bool Sucesso da operação
     */
    public function criarTabelaSeNaoExistir() {
        $query = "CREATE TABLE IF NOT EXISTS clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            codigo VARCHAR(20) NOT NULL,
            nome VARCHAR(100) NOT NULL,
            cpf_cnpj VARCHAR(20),
            telefone VARCHAR(20),
            email VARCHAR(100),
            endereco VARCHAR(255),
            cidade VARCHAR(100),
            estado VARCHAR(2),
            cep VARCHAR(10),
            tipo ENUM('pessoa_fisica', 'pessoa_juridica') DEFAULT 'pessoa_fisica',
            status ENUM('ativo', 'inativo') DEFAULT 'ativo',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";

        try {
            $this->conn->query($query);
            return true;
        } catch (Exception $e) {
            error_log("Erro ao criar tabela de clientes: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Adicionar novo cliente
     * @param array $dados Dados do cliente
     * @return int|false ID do cliente inserido ou false em caso de erro
     */
    public function adicionarCliente($dados) {
        try {
            // Garantir que a tabela existe
            $this->criarTabelaSeNaoExistir();

            $query = "INSERT INTO clientes (codigo, nome, cpf_cnpj, telefone, email, endereco, cidade, estado, cep, tipo, status) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->database->executeQuery($query, [
                $dados['codigo'], 
                $dados['nome'], 
                $dados['cpf_cnpj'] ?? '',
                $dados['telefone'] ?? '', 
                $dados['email'] ?? '', 
                $dados['endereco'] ?? '',
                $dados['cidade'] ?? '',
                $dados['estado'] ?? '',
                $dados['cep'] ?? '',
                $dados['tipo'] ?? 'pessoa_fisica',
                $dados['status'] ?? 'ativo'
            ], 'sssssssssss');

            return $this->conn->insert_id;
        } catch (Exception $e) {
            error_log("Erro ao adicionar cliente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar cliente
     * @param int $id ID do cliente
     * @param array $dados Dados atualizados do cliente
     * @return bool Sucesso da operação
     */
    public function atualizarCliente($id, $dados) {
        try {
            $query = "UPDATE clientes SET 
                      nome = ?, 
                      cpf_cnpj = ?, 
                      telefone = ?, 
                      email = ?, 
                      endereco = ?, 
                      cidade = ?, 
                      estado = ?, 
                      cep = ?, 
                      tipo = ?, 
                      status = ? 
                      WHERE id = ?";
            
            $stmt = $this->database->executeQuery($query, [
                $dados['nome'], 
                $dados['cpf_cnpj'] ?? '',
                $dados['telefone'] ?? '', 
                $dados['email'] ?? '', 
                $dados['endereco'] ?? '',
                $dados['cidade'] ?? '',
                $dados['estado'] ?? '',
                $dados['cep'] ?? '',
                $dados['tipo'] ?? 'pessoa_fisica',
                $dados['status'] ?? 'ativo',
                $id
            ], 'ssssssssssi');

            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Erro ao atualizar cliente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Excluir cliente
     * @param int $id ID do cliente
     * @return bool Sucesso da operação
     */
    public function excluirCliente($id) {
        try {
            $query = "DELETE FROM clientes WHERE id = ?";
            $stmt = $this->database->executeQuery($query, [$id], 'i');
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Erro ao excluir cliente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar cliente pelo ID
     * @param int $id ID do cliente
     * @return array|null Dados do cliente ou null se não encontrado
     */
    public function buscarClientePorId($id) {
        try {
            $query = "SELECT * FROM clientes WHERE id = ?";
            $stmt = $this->database->executeQuery($query, [$id], 'i');
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Erro ao buscar cliente: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Listar todos os clientes
     * @param array $filtros Filtros opcionais para a busca
     * @return array Lista de clientes
     */
    public function listarClientes($filtros = []) {
        try {
            // Garantir que a tabela existe
            $this->criarTabelaSeNaoExistir();
            
            $clientes = [];
            $params = [];
            $types = '';
            
            // Construir query base
            $query = "SELECT * FROM clientes WHERE 1=1";
            
            // Adicionar filtros
            if (!empty($filtros['status'])) {
                $query .= " AND status = ?";
                $params[] = $filtros['status'];
                $types .= 's';
            }
            
            if (!empty($filtros['tipo'])) {
                $query .= " AND tipo = ?";
                $params[] = $filtros['tipo'];
                $types .= 's';
            }
            
            if (!empty($filtros['busca'])) {
                $query .= " AND (nome LIKE ? OR codigo LIKE ? OR cpf_cnpj LIKE ? OR email LIKE ?)";
                $busca = "%" . $filtros['busca'] . "%";
                $params[] = $busca;
                $params[] = $busca;
                $params[] = $busca;
                $params[] = $busca;
                $types .= 'ssss';
            }
            
            // Adicionar ordenação
            $query .= " ORDER BY nome ASC";
            
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
                $clientes[] = $row;
            }
            
            return $clientes;
        } catch (Exception $e) {
            error_log("Erro ao listar clientes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Contar total de clientes
     * @param array $filtros Filtros opcionais para a contagem
     * @return int Número total de clientes
     */
    public function contarClientes($filtros = []) {
        try {
            // Garantir que a tabela existe
            $this->criarTabelaSeNaoExistir();
            
            $params = [];
            $types = '';
            
            // Construir query base
            $query = "SELECT COUNT(*) as total FROM clientes WHERE 1=1";
            
            // Adicionar filtros
            if (!empty($filtros['status'])) {
                $query .= " AND status = ?";
                $params[] = $filtros['status'];
                $types .= 's';
            }
            
            if (!empty($filtros['tipo'])) {
                $query .= " AND tipo = ?";
                $params[] = $filtros['tipo'];
                $types .= 's';
            }
            
            if (!empty($filtros['busca'])) {
                $query .= " AND (nome LIKE ? OR codigo LIKE ? OR cpf_cnpj LIKE ? OR email LIKE ?)";
                $busca = "%" . $filtros['busca'] . "%";
                $params[] = $busca;
                $params[] = $busca;
                $params[] = $busca;
                $params[] = $busca;
                $types .= 'ssss';
            }
            
            $stmt = $this->database->executeQuery($query, $params, $types);
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Erro ao contar clientes: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Gerar código único para cliente
     * @return string Código gerado
     */
    public function gerarCodigoCliente() {
        try {
            $query = "SELECT MAX(CAST(SUBSTRING(codigo, 3) AS UNSIGNED)) as ultimo FROM clientes WHERE codigo LIKE 'CL%'";
            $result = $this->conn->query($query);
            
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $ultimo = $row['ultimo'] ?? 0;
                $proximo = $ultimo + 1;
            } else {
                $proximo = 1;
            }
            
            return 'CL' . str_pad($proximo, 4, '0', STR_PAD_LEFT);
        } catch (Exception $e) {
            error_log("Erro ao gerar código de cliente: " . $e->getMessage());
            // Fallback para código baseado em timestamp
            return 'CL' . date('YmdHis');
        }
    }
} 