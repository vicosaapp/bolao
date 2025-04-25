<?php
// Evitar inclusão múltipla
if (!defined('DB_INCLUDED')) {
    define('DB_INCLUDED', true);
    
    // Configurações de conexão com o banco de dados
    $host = 'localhost';
    $usuario = 'root';
    $senha = '';
    $banco = 'bolao_db';
    
    // Log para depuração
    error_log("[DATABASE] Tentando conectar ao banco de dados {$banco} em {$host}");
    
    // Estabelecer conexão apenas se ainda não existir
    global $conn;
    if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_error) {
        // Estabelecer nova conexão
        $conn = new mysqli($host, $usuario, $senha, $banco);
        
        // Verificar conexão
        if ($conn->connect_error) {
            error_log("[DATABASE] ERRO na conexão: " . $conn->connect_error);
            die("Erro na conexão com o banco de dados: " . $conn->connect_error);
        } else {
            error_log("[DATABASE] Conexão estabelecida com sucesso");
        }
        
        // Definir charset para UTF-8
        $conn->set_charset("utf8mb4");
        error_log("[DATABASE] Charset definido como utf8mb4");
        
        // Variável para verificação
        $conn_established = true;
    } else {
        error_log("[DATABASE] Usando conexão existente");
    }
}
?> 