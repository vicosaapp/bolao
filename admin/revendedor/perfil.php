<?php
// Iniciar sessão primeiro, antes de qualquer operação
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Definir o título da página
$page_title = "Meu Perfil";

// Log para depuração
error_log("[PERFIL REVENDEDOR] Acessando perfil.php do revendedor");
error_log("[PERFIL REVENDEDOR] SESSION: " . print_r($_SESSION, true));

// Verificar permissões
require_once '../auth.php';
requireAccess(2); // Nível mínimo: revendedor

// Incluir modelos necessários
require_once '../models/UsuarioModel.php';

// Inicializar modelo
$usuarioModel = new UsuarioModel();

// Obter ID do usuário logado
$usuarioId = $_SESSION['usuario_id'] ?? null;

// Verificar se o usuário está logado
if (!$usuarioId) {
    header('Location: ../login.php');
    exit();
}

// Processar ações do formulário
$acao = $_GET['acao'] ?? null;
$mensagem = '';
$tipoMensagem = '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        switch ($acao) {
            case 'atualizar_perfil':
                $dadosAtualizacao = [
                    'nome' => $_POST['nome'] ?? null,
                    'email' => $_POST['email'] ?? null,
                    'telefone' => $_POST['telefone'] ?? null
                ];

                $resultado = $usuarioModel->atualizarPerfil($usuarioId, $dadosAtualizacao);
                
                if ($resultado) {
                    $mensagem = "Perfil atualizado com sucesso!";
                    $tipoMensagem = 'success';
                    
                    // Atualizar dados na sessão
                    $_SESSION['usuario_nome'] = $dadosAtualizacao['nome'];
                } else {
                    $mensagem = "Erro ao atualizar perfil.";
                    $tipoMensagem = 'danger';
                }
                break;

            case 'alterar_senha':
                $senhaAtual = $_POST['senha_atual'] ?? null;
                $novaSenha = $_POST['nova_senha'] ?? null;
                $confirmacaoSenha = $_POST['confirmar_senha'] ?? null;

                if ($novaSenha !== $confirmacaoSenha) {
                    $mensagem = "Nova senha e confirmação não coincidem.";
                    $tipoMensagem = 'danger';
                } else {
                    $resultado = $usuarioModel->alterarSenha($usuarioId, $senhaAtual, $novaSenha);
                    
                    if ($resultado) {
                        $mensagem = "Senha alterada com sucesso!";
                        $tipoMensagem = 'success';
                    } else {
                        $mensagem = "Erro ao alterar senha. Verifique a senha atual.";
                        $tipoMensagem = 'danger';
                    }
                }
                break;
        }
    }

    // Buscar dados atualizados do usuário
    $usuario = $usuarioModel->obterUsuarioPorId($usuarioId);
} catch (Exception $e) {
    $mensagem = "Erro: " . $e->getMessage();
    $tipoMensagem = 'danger';
}

// Incluir cabeçalho
require_once '../templates/header.php';
?>

<div class="content">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Meu Perfil</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Meu Perfil</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($mensagem)): ?>
    <div class="alert alert-<?php echo $tipoMensagem; ?> alert-dismissible fade show" role="alert">
        <?php echo $mensagem; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
    <?php endif; ?>

    <div class="content-body">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Informações Pessoais</h3>
                        </div>
                        <form method="POST" action="?acao=atualizar_perfil">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="nome">Nome Completo</label>
                                    <input type="text" class="form-control" id="nome" name="nome" 
                                           value="<?php echo htmlspecialchars($usuario['nome'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">E-mail</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="telefone">Telefone</label>
                                    <input type="tel" class="form-control" id="telefone" name="telefone" 
                                           value="<?php echo htmlspecialchars($usuario['telefone'] ?? ''); ?>" 
                                           placeholder="(00) 00000-0000">
                                </div>
                                <div class="form-group">
                                    <label>Nível de Acesso</label>
                                    <input type="text" class="form-control" readonly 
                                           value="<?php 
                                           switch($usuario['nivel_acesso'] ?? 1) {
                                               case 1: echo 'Usuário'; break;
                                               case 2: echo 'Revendedor'; break;
                                               case 3: echo 'Administrador'; break;
                                               default: echo 'Não definido';
                                           }
                                           ?>">
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Atualizar Perfil
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">Alterar Senha</h3>
                        </div>
                        <form method="POST" action="?acao=alterar_senha">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="senha_atual">Senha Atual</label>
                                    <input type="password" class="form-control" id="senha_atual" name="senha_atual" required>
                                </div>
                                <div class="form-group">
                                    <label for="nova_senha">Nova Senha</label>
                                    <input type="password" class="form-control" id="nova_senha" name="nova_senha" 
                                           minlength="8" required>
                                    <small class="form-text text-muted">
                                        A senha deve ter no mínimo 8 caracteres
                                    </small>
                                </div>
                                <div class="form-group">
                                    <label for="confirmar_senha">Confirmar Nova Senha</label>
                                    <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" 
                                           minlength="8" required>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-lock"></i> Alterar Senha
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir scripts personalizados
require_once '../templates/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Máscara de telefone
    const telefoneInput = document.getElementById('telefone');
    telefoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        
        if (value.length > 11) {
            value = value.slice(0, 11);
        }
        
        if (value.length <= 10) {
            value = value.replace(/^(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3');
        } else {
            value = value.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
        }
        
        e.target.value = value;
    });

    // Validação de senha
    const novaSenha = document.getElementById('nova_senha');
    const confirmarSenha = document.getElementById('confirmar_senha');

    confirmarSenha.addEventListener('input', function() {
        if (novaSenha.value !== confirmarSenha.value) {
            confirmarSenha.setCustomValidity('As senhas não coincidem');
        } else {
            confirmarSenha.setCustomValidity('');
        }
    });
});
</script> 