<?php
// Ativar exibição de erros para diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar ou continuar a sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>Teste e Reparo de Sessões</h1>";

// Verificar estado atual da sessão
echo "<h2>Estado Atual da Sessão</h2>";
echo "<p>ID da Sessão: " . session_id() . "</p>";
echo "<p>Status da Sessão: " . (session_status() == PHP_SESSION_ACTIVE ? "Ativa" : "Inativa") . "</p>";

echo "<h3>Variáveis de Sessão</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Verificar se o usuário está logado
echo "<h2>Status de Login</h2>";
if (isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id'])) {
    echo "<p style='color:green'>Usuário está logado. ID: " . $_SESSION['usuario_id'] . "</p>";
    
    // Verificar nível de acesso
    $nivel = $_SESSION['usuario_nivel'] ?? 1;
    echo "<p>Nível de acesso: " . $nivel . " (" . 
        ($nivel == 3 ? "Administrador" : ($nivel == 2 ? "Revendedor" : "Usuário")) . 
        ")</p>";
    
    // Possíveis caminhos para perfil
    echo "<h3>Caminhos de Perfil Possíveis</h3>";
    $caminhos = [
        1 => "/admin/usuario/perfil.php",
        2 => "/admin/revendedor/perfil.php",
        3 => "/admin/superadmin/perfil.php"
    ];
    
    echo "<p>Caminho correspondente ao nível de acesso: " . $caminhos[$nivel] . "</p>";
    
    // Verificar se os arquivos existem
    foreach ($caminhos as $n => $caminho) {
        $caminho_completo = $_SERVER['DOCUMENT_ROOT'] . $caminho;
        echo "<p>Nível $n - Arquivo: $caminho_completo - " . 
            (file_exists($caminho_completo) ? "Existe" : "Não existe") . "</p>";
    }
} else {
    echo "<p style='color:orange'>Usuário não está logado.</p>";
    
    // Opção para criar uma sessão de teste
    echo "<h3>Criar Sessão de Teste</h3>";
    echo "<form method='post' action=''>";
    echo "<p><label>ID de Usuário: <input type='number' name='user_id' value='1'></label></p>";
    echo "<p><label>Nome de Usuário: <input type='text' name='user_name' value='Usuário de Teste'></label></p>";
    echo "<p><label>Nível de Acesso: 
          <select name='user_level'>
            <option value='1'>Usuário</option>
            <option value='2'>Revendedor</option>
            <option value='3'>Administrador</option>
          </select></label></p>";
    echo "<p><button type='submit' name='create_session'>Criar Sessão de Teste</button></p>";
    echo "</form>";
}

// Processar formulário para criar sessão de teste
if (isset($_POST['create_session'])) {
    $_SESSION['usuario_id'] = $_POST['user_id'];
    $_SESSION['usuario_nome'] = $_POST['user_name'];
    $_SESSION['usuario_nivel'] = $_POST['user_level'];
    
    echo "<p style='color:green'>Sessão de teste criada com sucesso!</p>";
    echo "<p>Recarregando página...</p>";
    echo "<script>setTimeout(function(){ window.location.reload(); }, 1500);</script>";
}

// Opções para limpar sessão ou regenerar ID
echo "<h2>Opções de Gerenciamento</h2>";
echo "<form method='post' action=''>";
echo "<p><button type='submit' name='clear_session'>Limpar Sessão</button></p>";
echo "<p><button type='submit' name='regenerate_id'>Regenerar ID da Sessão</button></p>";
echo "</form>";

// Processar opções de gerenciamento
if (isset($_POST['clear_session'])) {
    session_unset();
    session_destroy();
    echo "<p style='color:green'>Sessão limpa com sucesso!</p>";
    echo "<p>Recarregando página...</p>";
    echo "<script>setTimeout(function(){ window.location.reload(); }, 1500);</script>";
}

if (isset($_POST['regenerate_id'])) {
    session_regenerate_id(true);
    echo "<p style='color:green'>ID da sessão regenerado com sucesso!</p>";
    echo "<p>Recarregando página...</p>";
    echo "<script>setTimeout(function(){ window.location.reload(); }, 1500);</script>";
}

// Verificar diretório de sessões do PHP
echo "<h2>Informações do Sistema de Sessões</h2>";
echo "<p>Diretório de sessões: " . session_save_path() . "</p>";
echo "<p>Prazo máximo de vida da sessão: " . ini_get('session.gc_maxlifetime') . " segundos</p>";
echo "<p>Cookir HTTP Only: " . (ini_get('session.cookie_httponly') ? "Sim" : "Não") . "</p>";
echo "<p>Secure Cookie: " . (ini_get('session.cookie_secure') ? "Sim" : "Não") . "</p>";

// Links para as páginas de perfil
echo "<h2>Links para Testes</h2>";
echo "<p><a href='/admin/usuario/perfil.php' target='_blank'>Perfil do Usuário</a></p>";
echo "<p><a href='/admin/revendedor/perfil.php' target='_blank'>Perfil do Revendedor</a></p>";
echo "<p><a href='/admin/superadmin/perfil.php' target='_blank'>Perfil do Administrador</a></p>";
echo "<p><a href='/admin/check_session.php' target='_blank'>Verificar Sessão e Banco de Dados</a></p>";
echo "<p><a href='/admin/test_usuario_model.php' target='_blank'>Testar UsuarioModel</a></p>";
?> 