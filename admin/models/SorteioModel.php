<?php
// Incluir a classe de banco de dados
require_once dirname(dirname(__DIR__)) . '/includes/Database.php';

class SorteioModel {
    private $db;

    public function __construct() {
        try {
            // Obter instância do banco de dados
            $this->db = Database::getInstance()->getConnection();
        } catch (Exception $e) {
            // Log do erro
            error_log("Erro ao estabelecer conexão no modelo de Sorteio: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Listar sorteios por concurso
     * @param int $concurso_id ID do concurso
     * @return array Lista de sorteios
     */
    public function listarSorteiosPorConcurso($concurso_id) {
        $sorteios = [];
        $query = "SELECT id, concurso_id, ordem, descricao, data_sorteio 
                  FROM sorteios 
                  WHERE concurso_id = ? 
                  ORDER BY ordem ASC";
        
        try {
            $stmt = $this->db->prepare($query);
            
            // Verificar se a preparação foi bem-sucedida
            if ($stmt === false) {
                error_log("Erro ao preparar consulta listarSorteiosPorConcurso: " . $this->db->error);
                return [];
            }
            
            $stmt->bind_param('i', $concurso_id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $sorteios[] = $row;
            }

            return $sorteios;
        } catch (Exception $e) {
            error_log("Erro ao listar sorteios por concurso: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obter números sorteados de um sorteio
     * @param int $sorteio_id ID do sorteio
     * @return array Lista de números sorteados
     */
    public function obterNumerosSorteados($sorteio_id) {
        $numeros = [];
        
        // Modificando a consulta para que corresponda à estrutura real da tabela
        $query = "SELECT numero 
                  FROM numeros_sorteados 
                  WHERE sorteio_id = ? 
                  ORDER BY id ASC";
        
        try {
            $stmt = $this->db->prepare($query);
            
            // Verificar se a preparação foi bem-sucedida
            if ($stmt === false) {
                error_log("Erro ao preparar consulta obterNumerosSorteados: " . $this->db->error);
                return [];
            }
            
            $stmt->bind_param('i', $sorteio_id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $numeros[] = $row['numero'];
            }

            return $numeros;
        } catch (Exception $e) {
            error_log("Erro ao obter números sorteados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Adicionar sorteio
     * @param array $dados Dados do sorteio
     * @return int|false ID do sorteio inserido ou false em caso de erro
     */
    public function adicionarSorteio($dados) {
        try {
            $query = "INSERT INTO sorteios (concurso_id, ordem, descricao, data_sorteio) 
                      VALUES (?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('iiss', $dados['concurso_id'], $dados['ordem'], $dados['descricao'], $dados['data_sorteio']);
            $stmt->execute();

            $sorteio_id = $this->db->insert_id;
            
            // Se houver números sorteados, adicioná-los
            if (!empty($dados['numeros']) && is_array($dados['numeros'])) {
                $this->adicionarNumerosSorteados($sorteio_id, $dados['numeros']);
            }

            return $sorteio_id;
        } catch (Exception $e) {
            error_log("Erro ao adicionar sorteio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Adicionar números sorteados
     * @param int $sorteio_id ID do sorteio
     * @param array $numeros Lista de números sorteados
     * @return bool Sucesso da operação
     */
    public function adicionarNumerosSorteados($sorteio_id, $numeros) {
        try {
            // Primeiro, remover números existentes (se houver)
            $this->removerNumerosSorteados($sorteio_id);
            
            // Obter o concurso_id relacionado ao sorteio
            $query = "SELECT concurso_id FROM sorteios WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $sorteio_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $concurso_id = $row['concurso_id'];
            
            // Adicionar novos números com concurso_id
            $query = "INSERT INTO numeros_sorteados (concurso_id, sorteio_id, numero) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($query);
            
            foreach ($numeros as $numero) {
                $stmt->bind_param('iis', $concurso_id, $sorteio_id, $numero);
                $stmt->execute();
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Erro ao adicionar números sorteados: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remover números sorteados
     * @param int $sorteio_id ID do sorteio
     * @return bool Sucesso da operação
     */
    private function removerNumerosSorteados($sorteio_id) {
        try {
            $query = "DELETE FROM numeros_sorteados WHERE sorteio_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $sorteio_id);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            error_log("Erro ao remover números sorteados: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar sorteio
     * @param int $sorteio_id ID do sorteio
     * @param array $dados Dados do sorteio
     * @return bool Sucesso da operação
     */
    public function atualizarSorteio($sorteio_id, $dados) {
        try {
            $query = "UPDATE sorteios 
                      SET descricao = ?, data_sorteio = ? 
                      WHERE id = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ssi', $dados['descricao'], $dados['data_sorteio'], $sorteio_id);
            $stmt->execute();
            
            // Se houver números sorteados, atualizá-los
            if (isset($dados['numeros']) && is_array($dados['numeros'])) {
                $this->adicionarNumerosSorteados($sorteio_id, $dados['numeros']);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Erro ao atualizar sorteio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Excluir sorteio
     * @param int $sorteio_id ID do sorteio
     * @return bool Sucesso da operação
     */
    public function excluirSorteio($sorteio_id) {
        try {
            // Primeiro, remover números sorteados
            $this->removerNumerosSorteados($sorteio_id);
            
            // Em seguida, remover o sorteio
            $query = "DELETE FROM sorteios WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $sorteio_id);
            $stmt->execute();
            
            return true;
        } catch (Exception $e) {
            error_log("Erro ao excluir sorteio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obter sorteio por ID
     * @param int $sorteio_id ID do sorteio
     * @return array|null Dados do sorteio ou null se não encontrado
     */
    public function obterSorteioPorId($sorteio_id) {
        try {
            $query = "SELECT id, concurso_id, ordem, descricao, data_sorteio 
                      FROM sorteios 
                      WHERE id = ?";
            
            $stmt = $this->db->prepare($query);
            
            // Verificar se a preparação foi bem-sucedida
            if ($stmt === false) {
                error_log("Erro ao preparar consulta obterSorteioPorId: " . $this->db->error);
                return null;
            }
            
            $stmt->bind_param('i', $sorteio_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $sorteio = $result->fetch_assoc();
                
                // Obter números sorteados
                $sorteio['numeros'] = $this->obterNumerosSorteados($sorteio_id);
                
                return $sorteio;
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Erro ao obter sorteio por ID: " . $e->getMessage());
            return null;
        }
    }
}
?> 