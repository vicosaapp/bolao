<?php
// Incluir a classe de banco de dados
require_once dirname(__DIR__, 2) . '/includes/Database.php';

class ConfiguracaoModel {
    private $conn;
    private $database;

    public function __construct() {
        try {
            // Obter instância do banco de dados
            $this->database = Database::getInstance();
            $this->conn = $this->database->getConnection();
        } catch (Exception $e) {
            // Log do erro
            error_log("Erro ao estabelecer conexão no modelo Configuração: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Criar a tabela de configurações se não existir
     * @return bool Sucesso da operação
     */
    public function criarTabelaSeNaoExistir() {
        $query = "CREATE TABLE IF NOT EXISTS configuracoes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            chave VARCHAR(100) UNIQUE NOT NULL,
            valor TEXT NOT NULL,
            descricao VARCHAR(255) NULL,
            categoria VARCHAR(50) DEFAULT 'geral',
            tipo ENUM('text', 'number', 'boolean', 'email', 'select', 'textarea', 'color', 'file') DEFAULT 'text',
            opcoes TEXT NULL,
            is_publico BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";

        try {
            $this->conn->query($query);
            return true;
        } catch (Exception $e) {
            error_log("Erro ao criar tabela de configurações: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Inserir configuração padrão se não existir
     * @param string $chave Chave da configuração
     * @param mixed $valor Valor da configuração
     * @param string $descricao Descrição da configuração
     * @param string $categoria Categoria da configuração
     * @param string $tipo Tipo do campo de configuração
     * @param string $opcoes Opções para selects (formato JSON)
     * @param bool $is_publico Se a configuração é pública
     * @return bool Sucesso da operação
     */
    public function inserirConfiguracaoPadrao($chave, $valor, $descricao = '', $categoria = 'geral', $tipo = 'text', $opcoes = null, $is_publico = false) {
        try {
            // Verificar se a configuração já existe
            $query = "SELECT id FROM configuracoes WHERE chave = ?";
            $stmt = $this->database->executeQuery($query, [$chave], 's');
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Configuração já existe, não fazer nada
                return true;
            }
            
            // Inserir nova configuração
            $query = "INSERT INTO configuracoes (chave, valor, descricao, categoria, tipo, opcoes, is_publico) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $this->database->executeQuery(
                $query, 
                [$chave, $valor, $descricao, $categoria, $tipo, $opcoes, $is_publico ? 1 : 0], 
                'sssssis'
            );
            
            return true;
        } catch (Exception $e) {
            error_log("Erro ao inserir configuração padrão: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Configurar valores padrão do sistema
     * @return bool Sucesso da operação
     */
    public function configurarValoresPadrao() {
        try {
            // Garantir que a tabela existe
            $this->criarTabelaSeNaoExistir();
            
            // Configurações gerais
            $this->inserirConfiguracaoPadrao('nome_site', 'Sistema de Bolão', 'Nome do site', 'geral', 'text', null, true);
            $this->inserirConfiguracaoPadrao('descricao_site', 'O melhor sistema de bolão online', 'Descrição do site', 'geral', 'textarea', null, true);
            $this->inserirConfiguracaoPadrao('email_contato', 'contato@bolao.com', 'Email de contato', 'geral', 'email', null, true);
            $this->inserirConfiguracaoPadrao('telefone_contato', '(00) 00000-0000', 'Telefone de contato', 'geral', 'text', null, true);
            $this->inserirConfiguracaoPadrao('logo_url', '/assets/img/logo.png', 'URL do logo', 'geral', 'file', null, true);
            $this->inserirConfiguracaoPadrao('cor_primaria', '#3498db', 'Cor primária do tema', 'aparencia', 'color', null, true);
            $this->inserirConfiguracaoPadrao('cor_secundaria', '#2ecc71', 'Cor secundária do tema', 'aparencia', 'color', null, true);
            
            // Configurações de bolões
            $this->inserirConfiguracaoPadrao('valor_padrao_bilhete', '10.00', 'Valor padrão do bilhete', 'bolao', 'number', null, false);
            $this->inserirConfiguracaoPadrao('percentual_premio', '70', 'Percentual do valor arrecadado destinado ao prêmio', 'bolao', 'number', null, true);
            $this->inserirConfiguracaoPadrao('percentual_comissao', '10', 'Percentual de comissão para vendedores', 'bolao', 'number', null, false);
            $this->inserirConfiguracaoPadrao('maximo_numeros_bilhete', '10', 'Máximo de números permitidos por bilhete', 'bolao', 'number', null, true);
            
            // Configurações de pagamento
            $this->inserirConfiguracaoPadrao('metodos_pagamento', json_encode(['pix', 'boleto', 'credito', 'debito']), 'Métodos de pagamento aceitos', 'pagamento', 'select', json_encode(['pix' => 'PIX', 'boleto' => 'Boleto', 'credito' => 'Cartão de Crédito', 'debito' => 'Cartão de Débito', 'dinheiro' => 'Dinheiro']), true);
            $this->inserirConfiguracaoPadrao('chave_pix', '', 'Chave PIX para pagamentos', 'pagamento', 'text', null, false);
            
            // Configurações de notificação
            $this->inserirConfiguracaoPadrao('enviar_email_resultado', 'true', 'Enviar email com resultado do sorteio', 'notificacao', 'boolean', null, false);
            $this->inserirConfiguracaoPadrao('enviar_email_compra', 'true', 'Enviar email de confirmação de compra', 'notificacao', 'boolean', null, false);
            
            // Configurações de relatórios
            $this->inserirConfiguracaoPadrao('dias_relatorio', '30', 'Período padrão para relatórios (em dias)', 'relatorios', 'number', null, false);
            
            // Configurações de segurança
            $this->inserirConfiguracaoPadrao('max_tentativas_login', '5', 'Máximo de tentativas de login antes do bloqueio', 'seguranca', 'number', null, false);
            $this->inserirConfiguracaoPadrao('tempo_bloqueio_login', '30', 'Tempo de bloqueio após tentativas falhas (minutos)', 'seguranca', 'number', null, false);
            
            return true;
        } catch (Exception $e) {
            error_log("Erro ao configurar valores padrão: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obter todas as configurações
     * @param string $categoria Categoria específica para filtrar (opcional)
     * @param bool $apenasPublico Retornar apenas configurações públicas
     * @return array Lista de configurações
     */
    public function obterConfiguracoes($categoria = null, $apenasPublico = false) {
        try {
            // Garantir que a tabela existe e valores padrão estão configurados
            $this->criarTabelaSeNaoExistir();
            $this->configurarValoresPadrao();
            
            $params = [];
            $types = '';
            
            $query = "SELECT * FROM configuracoes";
            
            if ($categoria) {
                $query .= " WHERE categoria = ?";
                $params[] = $categoria;
                $types .= 's';
                
                if ($apenasPublico) {
                    $query .= " AND is_publico = 1";
                }
            } else if ($apenasPublico) {
                $query .= " WHERE is_publico = 1";
            }
            
            $query .= " ORDER BY categoria, id";
            
            $stmt = $this->database->executeQuery($query, $params, $types);
            $result = $stmt->get_result();
            
            $configuracoes = [];
            while ($row = $result->fetch_assoc()) {
                $configuracoes[] = $row;
            }
            
            return $configuracoes;
        } catch (Exception $e) {
            error_log("Erro ao obter configurações: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obter uma configuração específica pelo chave
     * @param string $chave Chave da configuração
     * @param mixed $valorPadrao Valor padrão caso a configuração não exista
     * @return mixed Valor da configuração
     */
    public function obterConfiguracao($chave, $valorPadrao = null) {
        try {
            // Garantir que a tabela existe e valores padrão estão configurados
            $this->criarTabelaSeNaoExistir();
            $this->configurarValoresPadrao();
            
            $query = "SELECT valor, tipo FROM configuracoes WHERE chave = ?";
            $stmt = $this->database->executeQuery($query, [$chave], 's');
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $valor = $row['valor'];
                
                // Converter valor para o tipo adequado
                switch ($row['tipo']) {
                    case 'number':
                        return is_numeric($valor) ? (float)$valor : $valorPadrao;
                    case 'boolean':
                        return $valor === 'true' || $valor === '1' || $valor === true;
                    default:
                        return $valor;
                }
            }
            
            return $valorPadrao;
        } catch (Exception $e) {
            error_log("Erro ao obter configuração: " . $e->getMessage());
            return $valorPadrao;
        }
    }

    /**
     * Atualizar uma configuração
     * @param string $chave Chave da configuração
     * @param mixed $valor Novo valor
     * @return bool Sucesso da operação
     */
    public function atualizarConfiguracao($chave, $valor) {
        try {
            // Garantir que a tabela existe
            $this->criarTabelaSeNaoExistir();
            
            $query = "UPDATE configuracoes SET valor = ? WHERE chave = ?";
            $stmt = $this->database->executeQuery($query, [$valor, $chave], 'ss');
            
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Erro ao atualizar configuração: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar múltiplas configurações de uma vez
     * @param array $configuracoes Array associativo com chaves e valores
     * @return bool Sucesso da operação
     */
    public function atualizarConfiguracoes($configuracoes) {
        try {
            // Iniciar transação
            $this->conn->begin_transaction();
            
            foreach ($configuracoes as $chave => $valor) {
                $query = "UPDATE configuracoes SET valor = ?, updated_at = NOW() WHERE chave = ?";
                $stmt = $this->conn->prepare($query);
                
                if (!$stmt) {
                    throw new Exception('Erro ao preparar a consulta: ' . $this->conn->error);
                }
                
                $stmt->bind_param('ss', $valor, $chave);
                
                if (!$stmt->execute()) {
                    throw new Exception('Erro ao atualizar configuração: ' . $stmt->error);
                }
                
                $stmt->close();
            }
            
            // Commit da transação
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Rollback da transação
            $this->conn->rollback();
            error_log('Erro ao atualizar configurações: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obter categorias de configuração disponíveis
     * @return array Lista de categorias
     */
    public function obterCategorias() {
        try {
            // Garantir que a tabela existe e valores padrão estão configurados
            $this->criarTabelaSeNaoExistir();
            $this->configurarValoresPadrao();
            
            $query = "SELECT DISTINCT categoria FROM configuracoes ORDER BY categoria";
            $stmt = $this->database->executeQuery($query, [], '');
            $result = $stmt->get_result();
            
            $categorias = [];
            while ($row = $result->fetch_assoc()) {
                $categorias[] = $row['categoria'];
            }
            
            return $categorias;
        } catch (Exception $e) {
            error_log("Erro ao obter categorias de configuração: " . $e->getMessage());
            return ['geral'];
        }
    }

    public function __destruct() {
        // Garantir que qualquer transação aberta seja finalizada
        try {
            // No mysqli não existe um método direto para verificar se há uma transação aberta
            // Vamos usar um método alternativo seguro
            $this->conn->rollback();
        } catch (Exception $e) {
            // Se ocorrer um erro no rollback, provavelmente não havia transação ativa
            // Apenas ignoramos silenciosamente
        }
    }
} 