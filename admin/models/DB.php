<?php
/**
 * Classe para gerenciamento de conexão com o banco de dados usando PDO
 */
class DB {
    private static $instance = null;
    private $pdo;

    /**
     * Construtor privado para impedir instanciação direta
     */
    private function __construct() {
        // Incluir as configurações do banco de dados
        require_once dirname(dirname(__DIR__)) . '/includes/db_config.php';
        
        try {
            // Estabelecer conexão PDO
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Erro na conexão com o banco de dados: " . $e->getMessage());
            throw new Exception("Erro ao conectar com o banco de dados: " . $e->getMessage());
        }
    }

    /**
     * Método Singleton para obter a instância da classe
     * @return DB Instância única da classe
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Prepara uma consulta SQL
     * @param string $query Consulta SQL
     * @return PDOStatement Statement preparado
     */
    public function prepare($query) {
        return $this->pdo->prepare($query);
    }

    /**
     * Inicia uma transação
     * @return bool Sucesso da operação
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /**
     * Confirma uma transação
     * @return bool Sucesso da operação
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     * Reverte uma transação
     * @return bool Sucesso da operação
     */
    public function rollBack() {
        return $this->pdo->rollBack();
    }

    /**
     * Obtém o ID do último registro inserido
     * @return string Último ID inserido
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    /**
     * Executa uma consulta SQL direta
     * @param string $query Consulta SQL
     * @return PDOStatement|false Statement ou false em caso de erro
     */
    public function query($query) {
        return $this->pdo->query($query);
    }
} 