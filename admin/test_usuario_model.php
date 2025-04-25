<?php
// Ativar exibição de erros para diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste da Classe UsuarioModel</h1>";

try {
    // Incluir o arquivo da classe
    require_once 'models/UsuarioModel.php';
    
    // Inicializar a classe
    echo "<p>Tentando inicializar UsuarioModel...</p>";
    $usuarioModel = new UsuarioModel();
    echo "<p style='color:green'>UsuarioModel inicializado com sucesso!</p>";
    
    // Testar método obterUsuarioPorId com ID 1
    echo "<h2>Testando método obterUsuarioPorId</h2>";
    echo "<p>Buscando usuário com ID 1...</p>";
    
    $usuario = $usuarioModel->obterUsuarioPorId(1);
    
    if ($usuario) {
        echo "<p style='color:green'>Usuário encontrado!</p>";
        echo "<pre>";
        print_r($usuario);
        echo "</pre>";
    } else {
        echo "<p style='color:orange'>Usuário não encontrado ou retornou array vazio.</p>";
        echo "<pre>";
        print_r($usuario);
        echo "</pre>";
    }
    
    // Testar listarUsuarios
    echo "<h2>Testando método listarUsuarios</h2>";
    echo "<p>Listando usuários...</p>";
    
    $usuarios = $usuarioModel->listarUsuarios();
    
    if (count($usuarios) > 0) {
        echo "<p style='color:green'>Encontrados " . count($usuarios) . " usuários!</p>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Nível</th><th>Status</th></tr>";
        
        foreach ($usuarios as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['nome']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['nivel']) . "</td>";
            echo "<td>" . htmlspecialchars($user['status']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p style='color:orange'>Nenhum usuário encontrado.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
    echo "<p>Detalhes: <pre>" . $e->getTraceAsString() . "</pre></p>";
}
?> 