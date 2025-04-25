<?php
// Definir o título da página
$page_title = "Meu Perfil";

// CSS específico para esta página
$extraCSS = [
    '../assets/css/perfil.css'
];

// Verificar permissões
require_once '../auth.php';
requireAccess(3); // Nível mínimo: administrador (3)

// Incluir modelos necessários
require_once '../models/UsuarioModel.php';

// Incluir o cabeçalho
require_once '../templates/header.php';

// Inicializar modelo
$usuarioModel = new UsuarioModel();

// Obter ID do usuário logado
$usuarioId = $_SESSION['usuario_id'] ?? null;

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

    // Buscar dados do usuário
    $usuario = $usuarioModel->obterUsuarioPorId($usuarioId);
    
} catch (Exception $e) {
    $mensagem = "Erro: " . $e->getMessage();
    $tipoMensagem = 'danger';
}
?>

<div class="content">
    <div class="content-header">
        <h1>Meu Perfil</h1>
        <p class="text-muted">Visualize e atualize suas informações pessoais</p>
    </div>

    <?php if (!empty($mensagem)): ?>
    <div class="alert alert-<?php echo $tipoMensagem; ?> alert-dismissible fade show" role="alert">
        <?php echo $mensagem; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
    <?php endif; ?>

    <!-- Informações do Perfil -->
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                    <h5 class="m-0">Informações Pessoais</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="?acao=atualizar_perfil">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="nome" name="nome" 
                                   value="<?php echo htmlspecialchars($usuario['nome'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="tel" class="form-control" id="telefone" name="telefone" 
                                   value="<?php echo htmlspecialchars($usuario['telefone'] ?? ''); ?>" 
                                   placeholder="(00) 00000-0000">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nível de Acesso</label>
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
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Atualizar Perfil
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center bg-warning text-dark">
                    <h5 class="m-0">Alterar Senha</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="?acao=alterar_senha">
                        <div class="mb-3">
                            <label for="senha_atual" class="form-label">Senha Atual</label>
                            <input type="password" class="form-control" id="senha_atual" name="senha_atual" required>
                        </div>
                        <div class="mb-3">
                            <label for="nova_senha" class="form-label">Nova Senha</label>
                            <input type="password" class="form-control" id="nova_senha" name="nova_senha" 
                                   minlength="8" required>
                            <small class="form-text text-muted">
                                A senha deve ter no mínimo 8 caracteres
                            </small>
                        </div>
                        <div class="mb-3">
                            <label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label>
                            <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" 
                                   minlength="8" required>
                        </div>
                        <div class="d-grid gap-2">
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

<?php
// Scripts específicos para a página
$page_scripts = [
    '../assets/js/perfil.js'
];

// Incluir o rodapé
require_once '../templates/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Máscara de telefone
    const telefoneInput = document.getElementById('telefone');
    if (telefoneInput) {
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
    }

    // Validação de senha
    const novaSenha = document.getElementById('nova_senha');
    const confirmarSenha = document.getElementById('confirmar_senha');
    
    if (novaSenha && confirmarSenha) {
        confirmarSenha.addEventListener('input', function() {
            if (novaSenha.value !== confirmarSenha.value) {
                confirmarSenha.setCustomValidity('As senhas não coincidem');
            } else {
                confirmarSenha.setCustomValidity('');
            }
        });
    }
});
</script> 