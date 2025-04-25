<?php
// Iniciar sessão
session_start();

// Verificar se o usuário já está logado e redirecionar apropriadamente
if (isset($_SESSION['usuario_id'])) {
    header('Location: admin/');
    exit;
}

// Processar formulário de login
$erro = '';
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';
$access_denied = isset($_GET['access_denied']) ? true : false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    // Validar campos obrigatórios
    if (empty($email) || empty($senha)) {
        $erro = 'Por favor, preencha todos os campos.';
    } else {
        // Em um sistema real, você faria a consulta no banco de dados
        // e verificaria as credenciais corretamente
        
        // EXEMPLO para fins de demonstração - NÃO USE EM PRODUÇÃO
        // Usuário comum
        if ($email === 'usuario@exemplo.com' && $senha === 'senha123') {
            $_SESSION['usuario_id'] = 1;
            $_SESSION['usuario_nome'] = 'Usuário Demo';
            $_SESSION['usuario_email'] = 'usuario@exemplo.com';
            $_SESSION['usuario_nivel'] = 1; // Nível 1 = usuário
            
            header('Location: admin/');
            exit;
        }
        // Revendedor
        elseif ($email === 'revendedor@exemplo.com' && $senha === 'senha123') {
            $_SESSION['usuario_id'] = 2;
            $_SESSION['usuario_nome'] = 'Revendedor Demo';
            $_SESSION['usuario_email'] = 'revendedor@exemplo.com';
            $_SESSION['usuario_nivel'] = 2; // Nível 2 = revendedor
            
            header('Location: admin/');
            exit;
        }
        // Superadmin
        elseif ($email === 'admin@exemplo.com' && $senha === 'senha123') {
            $_SESSION['usuario_id'] = 3;
            $_SESSION['usuario_nome'] = 'Admin Demo';
            $_SESSION['usuario_email'] = 'admin@exemplo.com';
            $_SESSION['usuario_nivel'] = 3; // Nível 3 = superadmin
            
            header('Location: admin/');
            exit;
        }
        else {
            $erro = 'Email ou senha incorretos. Tente novamente.';
        }
    }
}

// Configurações da página
$pageTitle = "Login";
$pageDescription = "Acesse sua conta no Bolão 10";
$pageKeywords = "login, acesso, bolão";

// Incluir o cabeçalho
include 'templates/header.php';
?>

<div class="login-page">
    <div class="container">
        <div class="login-box">
            <div class="login-header">
                <h2>Acesse sua conta</h2>
                <p>Informe suas credenciais para acessar o sistema</p>
                
                <?php if ($access_denied): ?>
                <div class="alert alert-warning">
                    Acesso negado. Por favor, faça login com uma conta que tenha permissões adequadas.
                </div>
                <?php endif; ?>
                
                <?php if (!empty($erro)): ?>
                <div class="alert alert-danger">
                    <?php echo $erro; ?>
                </div>
                <?php endif; ?>

                <!-- Informações de demonstração - remover em produção -->
                <div class="demo-info">
                    <p><strong>Dados de demonstração:</strong></p>
                    <ul>
                        <li><strong>Usuário:</strong> usuario@exemplo.com / senha123</li>
                        <li><strong>Revendedor:</strong> revendedor@exemplo.com / senha123</li>
                        <li><strong>Admin:</strong> admin@exemplo.com / senha123</li>
                    </ul>
                </div>
            </div>
            
            <div class="login-body">
                <form action="login.php<?php echo $redirect ? "?redirect=$redirect" : ''; ?>" method="POST">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" id="email" class="form-control" placeholder="Seu email" required>
                        </div>
                    </div>
                    
                    <div class="form-group mt-3">
                        <label for="senha">Senha</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="senha" id="senha" class="form-control" placeholder="Sua senha" required>
                        </div>
                    </div>
                    
                    <div class="form-check mt-3">
                        <input type="checkbox" name="lembrar" id="lembrar" class="form-check-input">
                        <label for="lembrar" class="form-check-label">Lembrar de mim</label>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary w-100">Entrar</button>
                    </div>
                </form>
            </div>
            
            <div class="login-footer mt-3">
                <p><a href="recuperar-senha.php">Esqueceu a senha?</a></p>
                <p>Não tem uma conta? <a href="cadastro.php">Cadastre-se</a></p>
            </div>
        </div>
    </div>
</div>

<style>
.login-page {
    padding: 60px 0;
    min-height: 600px;
}

.login-box {
    max-width: 450px;
    margin: 0 auto;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    padding: 30px;
}

.login-header {
    text-align: center;
    margin-bottom: 30px;
}

.login-header h2 {
    margin-bottom: 10px;
    color: #333;
}

.login-header p {
    color: #666;
}

.login-footer {
    text-align: center;
    margin-top: 20px;
    color: #666;
}

.login-footer a {
    color: var(--primary-color);
    text-decoration: none;
}

.login-footer a:hover {
    text-decoration: underline;
}

.demo-info {
    margin: 20px 0;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 5px;
    text-align: left;
    border-left: 3px solid var(--primary-color);
}

.demo-info p {
    margin-bottom: 10px;
    color: #333;
}

.demo-info ul {
    margin-bottom: 0;
    padding-left: 20px;
}
</style>

<?php
// Incluir o rodapé
include 'templates/footer.php';
?> 