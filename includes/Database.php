<?php
class Database {
    private static $instance = null;
    private $conn;

    // Construtor privado para impedir instanciação direta
    private function __construct() {
        // Incluir arquivo de configuração
        require_once dirname(__DIR__) . '/includes/db_config.php';

        try {
            // Estabelecer conexão
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            // Verificar conexão
            if ($this->conn->connect_error) {
                throw new Exception("Erro na conexão: " . $this->conn->connect_error);
            }

            // Definir charset
            $this->conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            // Log do erro
            error_log("Erro de conexão com o banco de dados: " . $e->getMessage());
            throw $e;
        }
    }

    // Método singleton para obter a instância da conexão
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Método para obter a conexão
    public function getConnection() {
        return $this->conn;
    }

    // Método para executar consultas preparadas
    public function executeQuery($query, $params = [], $types = '') {
        $stmt = $this->conn->prepare($query);
        
        if ($stmt === false) {
            throw new Exception("Erro ao preparar a consulta: " . $this->conn->error);
        }

        // Bind de parâmetros se existirem
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $result = $stmt->execute();
        
        if ($result === false) {
            throw new Exception("Erro ao executar a consulta: " . $stmt->error);
        }

        return $stmt;
    }

    // Método para fechar a conexão
    public function closeConnection() {
        if ($this->conn) {
            $this->conn->close();
            self::$instance = null;
        }
    }

    // Prevenir clonagem
    private function __clone() {}

    // Destrutor para fechar a conexão
    public function __destruct() {
        $this->closeConnection();
    }
} 