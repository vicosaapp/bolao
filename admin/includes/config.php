<?php
/**
 * Arquivo de configuração para a área administrativa
 * Responsável por incluir as dependências e inicializar a conexão com o banco de dados
 */

// Incluir configurações do banco de dados do diretório raiz
require_once dirname(dirname(__DIR__)) . '/includes/db_config.php';

// Incluir funções utilitárias
require_once __DIR__ . '/functions.php';

// Inicializar conexão PDO
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Registrar erro e exibir mensagem amigável
    error_log("Erro de conexão com o banco de dados: " . $e->getMessage());
    die("Não foi possível conectar ao banco de dados. Por favor, tente novamente mais tarde ou contate o administrador.");
}

// Iniciar sessão se ainda não estiver ativa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Constantes específicas da área administrativa
define('ADMIN_ROOT', dirname(__DIR__));
define('ADMIN_URL', '/admin');

// Funções específicas da área administrativa podem ser adicionadas aqui 