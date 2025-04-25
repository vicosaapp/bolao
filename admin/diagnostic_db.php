<?php
// Ativar exibição de erros para diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico de Conexão com Banco de Dados</h1>";

try {
    echo "<h2>Verificando configurações do PHP</h2>";
    echo "Versão do PHP: " . phpversion() . "<br>";
    echo "Extensão mysqli: " . (extension_loaded('mysqli') ? 'Carregada' : 'Não carregada') . "<br>";
    
    echo "<h2>Testando arquivo database.php</h2>";
    
    // Verificar se o arquivo existe
    $dbPath = realpath(dirname(__FILE__, 2) . '/config/database.php');
    echo "Caminho do arquivo: " . $dbPath . "<br>";
    echo "Arquivo existe: " . (file_exists($dbPath) ? 'Sim' : 'Não') . "<br>";
    
    if (file_exists($dbPath)) {
        echo "Conteúdo do arquivo database.php:<br>";
        echo "<pre>" . htmlspecialchars(file_get_contents($dbPath)) . "</pre>";
        
        echo "<h2>Testando conexão diretamente</h2>";
        
        // Configurações de conexão locais para teste
        $host = 'localhost';
        $usuario = 'root';
        $senha = '';
        $banco = 'bolao_db';
        
        // Tentar estabelecer conexão diretamente
        $conn = new mysqli($host, $usuario, $senha, $banco);
        
        if ($conn->connect_error) {
            echo "Erro na conexão direta: " . $conn->connect_error . "<br>";
        } else {
            echo "Conexão direta estabelecida com sucesso!<br>";
            echo "Informações do servidor: " . $conn->server_info . "<br>";
            
            // Testar query básica
            $testResult = $conn->query("SELECT 1 as test");
            if ($testResult) {
                $row = $testResult->fetch_assoc();
                echo "Teste de query: " . ($row['test'] === '1' ? 'Sucesso' : 'Falha') . "<br>";
            } else {
                echo "Falha ao executar query de teste: " . $conn->error . "<br>";
            }
            
            // Testar tabela usuarios
            $usersResult = $conn->query("SHOW TABLES LIKE 'usuarios'");
            echo "Tabela 'usuarios' existe: " . ($usersResult->num_rows > 0 ? 'Sim' : 'Não') . "<br>";
            
            if ($usersResult->num_rows > 0) {
                $structureResult = $conn->query("DESCRIBE usuarios");
                if ($structureResult) {
                    echo "Estrutura da tabela 'usuarios':<br><pre>";
                    while ($field = $structureResult->fetch_assoc()) {
                        echo htmlspecialchars(print_r($field, true)) . "\n";
                    }
                    echo "</pre>";
                } else {
                    echo "Erro ao obter estrutura da tabela: " . $conn->error . "<br>";
                }
            }
            
            $conn->close();
        }
        
        echo "<h2>Testando inclusão do arquivo</h2>";
        
        // Testar inclusão do arquivo e variável $conn
        include_once $dbPath;
        
        if (isset($conn) && $conn instanceof mysqli) {
            echo "Arquivo incluído e variável \$conn definida corretamente<br>";
            echo "Informações da conexão: " . $conn->server_info . "<br>";
            echo "Erro (se houver): " . $conn->connect_error . "<br>";
            
            // Não fechar a conexão aqui, pois ela pode ser necessária depois
        } else {
            echo "Problema ao incluir arquivo ou variável \$conn não definida corretamente<br>";
        }
    }
    
    echo "<h2>Testando classe UsuarioModel</h2>";
    
    require_once dirname(__FILE__) . '/models/UsuarioModel.php';
    
    try {
        echo "Inicializando UsuarioModel...<br>";
        $usuarioModel = new UsuarioModel();
        echo "UsuarioModel inicializado com sucesso!<br>";
    } catch (Exception $e) {
        echo "Erro ao inicializar UsuarioModel: " . $e->getMessage() . "<br>";
    }
    
} catch (Exception $e) {
    echo "Erro geral: " . $e->getMessage() . "<br>";
}
?> 