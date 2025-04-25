<?php
/**
 * Configurações do Banco de Dados
 * Define as constantes para conexão com o banco de dados
 */

// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bolao_db');

// Estabelecer conexão com o banco de dados
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset("utf8");
    
    if ($conn->connect_error) {
        throw new Exception("Erro na conexão com o banco de dados: " . $conn->connect_error);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    die("Erro ao conectar com o banco de dados. Por favor, tente novamente mais tarde.");
}
?> 