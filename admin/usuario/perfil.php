<?php
// Ativar exibição de erros para diagnóstico durante desenvolvimento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar e iniciar a sessão se necessário
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Registrar acesso para depuração
error_log("[PERFIL USUARIO] Acesso à página de perfil. Session ID: " . session_id());
error_log("[PERFIL USUARIO] Variáveis de sessão: " . print_r($_SESSION, true));

// Definir título da página
$pageTitle = "Meu Perfil";

// Incluir arquivos necessários
require_once dirname(__DIR__) . '/auth.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__) . '/models/UsuarioModel.php';

// Verificar permissão (nível mínimo: usuário comum)
requireAccess(1);

// Inicializar modelo de usuário
$usuarioModel = new UsuarioModel();

// Obter ID do usuário logado
$usuario_id = $_SESSION['usuario_id'] ?? null;

// Verificar se o usuário está autenticado
if (!$usuario_id) {
    error_log("[PERFIL USUARIO] Usuário não autenticado. Redirecionando para login.");
    header("Location: /login.php");
    exit;
}

// Inicializar mensagens
$successMsg = '';
$errorMsg = '';

// Processar formulário de atualização de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_perfil'])) {
    $nome = trim($_POST['nome']) ?? '';
    $email = trim($_POST['email']) ?? '';
    $telefone = trim($_POST['telefone'] ?? '');
    
    // Validar dados
    $errors = [];
    
    if (empty($nome)) {
        $errors[] = "Nome é obrigatório.";
    }
    
    if (empty($email)) {
        $errors[] = "E-mail é obrigatório.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "E-mail inválido.";
    }
    
    // Se não houver erros, atualizar perfil
    if (empty($errors)) {
        $result = $usuarioModel->atualizarPerfil($usuario_id, [
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone
        ]);
        
        if ($result) {
            // Atualizar dados da sessão
            $_SESSION['usuario_nome'] = $nome;
            $successMsg = "Perfil atualizado com sucesso!";
        } else {
            $errorMsg = "Erro ao atualizar perfil. Tente novamente.";
        }
    } else {
        $errorMsg = implode("<br>", $errors);
    }
}

// Processar formulário de alteração de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alterar_senha'])) {
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    
    // Validar dados
    $errors = [];
    
    if (empty($senha_atual)) {
        $errors[] = "Senha atual é obrigatória.";
    }
    
    if (empty($nova_senha)) {
        $errors[] = "Nova senha é obrigatória.";
    } elseif (strlen($nova_senha) < 6) {
        $errors[] = "Nova senha deve ter pelo menos 6 caracteres.";
    }
    
    if ($nova_senha !== $confirmar_senha) {
        $errors[] = "As senhas não coincidem.";
    }
    
    // Se não houver erros, alterar senha
    if (empty($errors)) {
        $result = $usuarioModel->alterarSenha($usuario_id, $senha_atual, $nova_senha);
        
        if ($result === true) {
            $successMsg = "Senha alterada com sucesso!";
        } else {
            $errorMsg = $result; // Mensagem de erro retornada pelo modelo
        }
    } else {
        $errorMsg = implode("<br>", $errors);
    }
}

// Obter dados do usuário
try {
    $usuario = $usuarioModel->obterUsuarioPorId($usuario_id);
    
    if (empty($usuario)) {
        error_log("[PERFIL USUARIO] Usuário não encontrado no banco de dados. ID: $usuario_id");
        
        // Destruir sessão e redirecionar para login
        session_destroy();
        header("Location: /login.php?erro=usuario_nao_encontrado");
        exit;
    }
} catch (Exception $e) {
    error_log("[PERFIL USUARIO] Erro ao obter dados do usuário: " . $e->getMessage());
    $errorMsg = "Erro ao carregar dados do perfil. Por favor, tente novamente mais tarde.";
    $usuario = [
        'nome' => $_SESSION['usuario_nome'] ?? 'Não disponível',
        'email' => 'Não disponível',
        'telefone' => 'Não disponível',
        'nivel_acesso' => $_SESSION['usuario_nivel'] ?? 1
    ];
}

// Incluir o cabeçalho
include_once dirname(__DIR__) . '/templates/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Meu Perfil</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($successMsg)): ?>
                        <div class="alert alert-success">
                            <?php echo $successMsg; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errorMsg)): ?>
                        <div class="alert alert-danger">
                            <?php echo $errorMsg; ?>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5>Informações Pessoais</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <div class="form-group mb-3">
                                            <label for="nome">Nome</label>
                                            <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome'] ?? ''); ?>" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="email">E-mail</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="telefone">Telefone</label>
                                            <input type="text" class="form-control" id="telefone" name="telefone" value="<?php echo htmlspecialchars($usuario['telefone'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="nivel_acesso">Nível de Acesso</label>
                                            <input type="text" class="form-control" id="nivel_acesso" value="<?php 
                                                $nivel = $usuario['nivel_acesso'] ?? 1;
                                                echo $nivel == 3 ? 'Administrador' : ($nivel == 2 ? 'Revendedor' : 'Usuário'); 
                                            ?>" readonly>
                                        </div>
                                        <button type="submit" name="atualizar_perfil" class="btn btn-primary">Atualizar Perfil</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Alterar Senha</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <div class="form-group mb-3">
                                            <label for="senha_atual">Senha Atual</label>
                                            <input type="password" class="form-control" id="senha_atual" name="senha_atual" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="nova_senha">Nova Senha</label>
                                            <input type="password" class="form-control" id="nova_senha" name="nova_senha" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="confirmar_senha">Confirmar Nova Senha</label>
                                            <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required>
                                        </div>
                                        <button type="submit" name="alterar_senha" class="btn btn-warning">Alterar Senha</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para máscara de telefone -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const telefoneInput = document.getElementById('telefone');
    if (telefoneInput) {
        telefoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value.length <= 2) {
                    value = '(' + value;
                } else if (value.length <= 6) {
                    value = '(' + value.substring(0, 2) + ') ' + value.substring(2);
                } else if (value.length <= 10) {
                    value = '(' + value.substring(0, 2) + ') ' + value.substring(2, 6) + '-' + value.substring(6);
                } else {
                    value = '(' + value.substring(0, 2) + ') ' + value.substring(2, 7) + '-' + value.substring(7, 11);
                }
            }
            e.target.value = value;
        });
    }
    
    // Validação de confirmação de senha
    const novaSenhaInput = document.getElementById('nova_senha');
    const confirmarSenhaInput = document.getElementById('confirmar_senha');
    
    if (confirmarSenhaInput && novaSenhaInput) {
        confirmarSenhaInput.addEventListener('input', function() {
            if (this.value !== novaSenhaInput.value) {
                this.setCustomValidity('As senhas não coincidem');
            } else {
                this.setCustomValidity('');
            }
        });
        
        novaSenhaInput.addEventListener('input', function() {
            if (confirmarSenhaInput.value !== '' && confirmarSenhaInput.value !== this.value) {
                confirmarSenhaInput.setCustomValidity('As senhas não coincidem');
            } else {
                confirmarSenhaInput.setCustomValidity('');
            }
        });
    }
});
</script>

<?php include_once dirname(__DIR__) . '/templates/footer.php'; ?> 