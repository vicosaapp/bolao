<?php
// Ativar exibição de erros para diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sessão se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>Verificação de Sessão e Banco de Dados</h1>";

// Verificar sessão
echo "<h2>Informações da Sessão</h2>";
echo "<p>ID da Sessão: " . session_id() . "</p>";
echo "<p>Status da Sessão: " . (session_status() == PHP_SESSION_ACTIVE ? "Ativa" : "Inativa") . "</p>";

echo "<h3>Variáveis de Sessão</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Verificar arquivo de configuração do banco de dados
echo "<h2>Verificação dos Arquivos de Configuração</h2>";

$config_paths = [
    dirname(__DIR__) . '/config/database.php',
    dirname(__DIR__) . '/includes/db_config.php'
];

foreach ($config_paths as $path) {
    echo "<h3>Arquivo: " . htmlspecialchars($path) . "</h3>";
    
    if (file_exists($path)) {
        echo "<p style='color:green'>Arquivo existe!</p>";
        echo "<p>Última modificação: " . date("d/m/Y H:i:s", filemtime($path)) . "</p>";
        
        echo "<h4>Conteúdo do arquivo:</h4>";
        echo "<pre style='max-height: 300px; overflow: auto; border: 1px solid #ccc; padding: 10px;'>";
        echo htmlspecialchars(file_get_contents($path));
        echo "</pre>";
        
        // Testar inclusão
        echo "<h4>Testando inclusão do arquivo:</h4>";
        
        try {
            include_once $path;
            echo "<p style='color:green'>Arquivo incluído com sucesso!</p>";
            
            global $conn;
            if (isset($conn) && $conn instanceof mysqli) {
                echo "<p style='color:green'>Conexão com o banco de dados estabelecida!</p>";
                echo "<p>Informações do servidor: " . htmlspecialchars($conn->server_info) . "</p>";
                echo "<p>Charset: " . htmlspecialchars($conn->character_set_name()) . "</p>";
                
                // Testar query simples
                $result = $conn->query("SELECT 1 as test");
                if ($result) {
                    $row = $result->fetch_assoc();
                    echo "<p>Teste de query: " . ($row['test'] == 1 ? "Sucesso" : "Falha") . "</p>";
                } else {
                    echo "<p style='color:red'>Falha ao executar query de teste: " . $conn->error . "</p>";
                }
            } else {
                echo "<p style='color:red'>Variável \$conn não está definida ou não é uma instância de mysqli!</p>";
            }
        } catch (Throwable $e) {
            echo "<p style='color:red'>Erro ao incluir arquivo: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color:red'>Arquivo não existe!</p>";
    }
}

// Verificar a classe UsuarioModel
echo "<h2>Verificação da Classe UsuarioModel</h2>";

try {
    require_once dirname(__FILE__) . '/models/UsuarioModel.php';
    echo "<p style='color:green'>Arquivo UsuarioModel.php incluído com sucesso!</p>";
    
    $usuarioModel = new UsuarioModel();
    echo "<p style='color:green'>Classe UsuarioModel inicializada com sucesso!</p>";
    
    // Testar métodos da classe
    echo "<h3>Testando método obterUsuarioPorId</h3>";
    
    try {
        $usuario = $usuarioModel->obterUsuarioPorId(1);
        echo "<p>Resultado:</p>";
        echo "<pre>";
        print_r($usuario);
        echo "</pre>";
    } catch (Throwable $e) {
        echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
    }
    
} catch (Throwable $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
    echo "<p>Rastreamento:</p>";
    echo "<pre>";
    print_r($e->getTraceAsString());
    echo "</pre>";
}
?> 