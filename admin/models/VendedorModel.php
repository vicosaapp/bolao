<?php
// Incluir a classe de banco de dados
require_once dirname(dirname(__DIR__)) . '/includes/Database.php';

class VendedorModel {
    private $db;

    public function __construct() {
        try {
            // Obter instância do banco de dados
            $this->db = Database::getInstance()->getConnection();
        } catch (Exception $e) {
            // Log do erro
            error_log("Erro ao estabelecer conexão no modelo de Vendedor: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Listar todos os vendedores
     * @return array Lista de vendedores
     */
    public function listarVendedores() {
        $vendedores = [];
        $query = "SELECT v.*, 
                 (SELECT COUNT(*) FROM bilhetes WHERE vendedor_id = v.id) as total_vendas,
                 (SELECT SUM(valor_comissao) FROM comissoes WHERE vendedor_id = v.id) as total_comissoes
                 FROM vendedores v 
                 ORDER BY v.nome ASC";
        
        try {
            $result = $this->db->query($query);
            
            while ($row = $result->fetch_assoc()) {
                $vendedores[] = $row;
            }
            
            return $vendedores;
        } catch (Exception $e) {
            error_log("Erro ao listar vendedores: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obter um vendedor pelo ID
     * @param int $id ID do vendedor
     * @return array|null Dados do vendedor ou null se não encontrado
     */
    public function obterVendedorPorId($id) {
        try {
            $query = "SELECT * FROM vendedores WHERE id = ? LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Erro ao obter vendedor por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Adicionar novo vendedor
     * @param array $dados Dados do vendedor
     * @return int|false ID do vendedor inserido ou false em caso de erro
     */
    public function adicionarVendedor($dados) {
        try {
            $query = "INSERT INTO vendedores (codigo, nome, telefone, email, comissao) 
                      VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ssssd', 
                $dados['codigo'], 
                $dados['nome'], 
                $dados['telefone'], 
                $dados['email'], 
                $dados['comissao']
            );
            $stmt->execute();
            
            return $this->db->insert_id;
        } catch (Exception $e) {
            error_log("Erro ao adicionar vendedor: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar dados do vendedor
     * @param int $id ID do vendedor
     * @param array $dados Novos dados do vendedor
     * @return bool Sucesso da operação
     */
    public function atualizarVendedor($id, $dados) {
        try {
            $query = "UPDATE vendedores SET 
                      codigo = ?, 
                      nome = ?, 
                      telefone = ?, 
                      email = ?, 
                      comissao = ? 
                      WHERE id = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ssssdi', 
                $dados['codigo'], 
                $dados['nome'], 
                $dados['telefone'], 
                $dados['email'], 
                $dados['comissao'],
                $id
            );
            $stmt->execute();
            
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Erro ao atualizar vendedor: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Excluir vendedor
     * @param int $id ID do vendedor
     * @return bool Sucesso da operação
     */
    public function excluirVendedor($id) {
        try {
            // Verificar se o vendedor tem bilhetes associados
            $query = "SELECT COUNT(*) as total FROM bilhetes WHERE vendedor_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['total'] > 0) {
                // Não pode excluir vendedor com bilhetes associados
                return false;
            }
            
            // Excluir o vendedor
            $query = "DELETE FROM vendedores WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Erro ao excluir vendedor: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Contar o total de vendas de todos os vendedores
     * @return int Total de vendas
     */
    public function contarTotalVendas() {
        try {
            $query = "SELECT COUNT(*) as total FROM bilhetes";
            $result = $this->db->query($query);
            $row = $result->fetch_assoc();
            
            return $row['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Erro ao contar total de vendas: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calcular o total de comissões geradas
     * @return float Total de comissões
     */
    public function calcularTotalComissoes() {
        try {
            $query = "SELECT SUM(valor_comissao) as total FROM comissoes";
            $result = $this->db->query($query);
            $row = $result->fetch_assoc();
            
            return $row['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Erro ao calcular total de comissões: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obter últimas vendas de um vendedor
     * @param int $id ID do vendedor
     * @param int $limite Número máximo de vendas a retornar
     * @return array Lista de vendas
     */
    public function obterUltimasVendas($id, $limite = 5) {
        try {
            $query = "SELECT b.id, b.numero, b.concurso_id, b.valor, b.data_compra, a.nome as apostador,
                      c.numero as concurso_numero,
                      (b.valor * v.comissao / 100) as valor_comissao
                      FROM bilhetes b
                      JOIN vendedores v ON b.vendedor_id = v.id
                      LEFT JOIN apostadores a ON b.apostador_id = a.id
                      JOIN concursos c ON b.concurso_id = c.id
                      WHERE b.vendedor_id = ?
                      ORDER BY b.data_compra DESC
                      LIMIT ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ii', $id, $limite);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $vendas = [];
            while ($row = $result->fetch_assoc()) {
                $vendas[] = $row;
            }
            
            return $vendas;
        } catch (Exception $e) {
            error_log("Erro ao obter últimas vendas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obter estatísticas de comissões de um vendedor
     * @param int $id ID do vendedor
     * @return array Estatísticas de comissões
     */
    public function obterEstatisticasComissoes($id) {
        try {
            $query = "SELECT 
                      SUM(valor_comissao) as total_comissoes,
                      SUM(CASE WHEN status = 'pago' THEN valor_comissao ELSE 0 END) as comissoes_pagas,
                      SUM(CASE WHEN status = 'pendente' THEN valor_comissao ELSE 0 END) as comissoes_pendentes
                      FROM comissoes
                      WHERE vendedor_id = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            
            return [
                'total_comissoes' => 0,
                'comissoes_pagas' => 0,
                'comissoes_pendentes' => 0
            ];
        } catch (Exception $e) {
            error_log("Erro ao obter estatísticas de comissões: " . $e->getMessage());
            return [
                'total_comissoes' => 0,
                'comissoes_pagas' => 0,
                'comissoes_pendentes' => 0
            ];
        }
    }

    /**
     * Obter top vendedores com maior volume de vendas
     * @param int $limite Quantidade de vendedores a retornar
     * @return array Lista dos top vendedores
     */
    public function obterTopVendedores($limite = 5) {
        try {
            $query = "SELECT v.id, v.codigo, v.nome, 
                     COUNT(b.id) as total_bilhetes,
                     SUM(b.valor) as total_vendas,
                     SUM(c.valor_comissao) as comissao
                     FROM vendedores v 
                     LEFT JOIN bilhetes b ON v.id = b.vendedor_id
                     LEFT JOIN comissoes c ON v.id = c.vendedor_id
                     GROUP BY v.id, v.codigo, v.nome
                     ORDER BY total_vendas DESC 
                     LIMIT ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $limite);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $vendedores = [];
            while ($row = $result->fetch_assoc()) {
                // Garantir que os valores não sejam nulos
                $row['total_bilhetes'] = $row['total_bilhetes'] ?? 0;
                $row['total_vendas'] = $row['total_vendas'] ?? 0;
                $row['comissao'] = $row['comissao'] ?? 0;
                
                $vendedores[] = $row;
            }
            
            return $vendedores;
        } catch (Exception $e) {
            error_log("Erro ao obter top vendedores: " . $e->getMessage());
            return [];
        }
    }
}
?> 