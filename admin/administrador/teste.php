<?php
// Definir o título da página
$page_title = "Teste - Dashboard do Administrador";

// Verificar permissões
require_once '../auth.php';
requireAccess(3); // Nível mínimo: administrador

// Incluir o cabeçalho
require_once '../templates/header.php';
?>

<div class="dashboard-wrapper">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Página de Teste - Administrador</h5>
                </div>
                <div class="card-body">
                    <p>Se você está vendo esta mensagem, o painel de administrador está funcionando corretamente!</p>
                    
                    <h6>Informações da Sessão:</h6>
                    <ul>
                        <li><strong>ID do Usuário:</strong> <?php echo $_SESSION['usuario_id'] ?? 'Não definido'; ?></li>
                        <li><strong>Nome:</strong> <?php echo $_SESSION['usuario_nome'] ?? 'Não definido'; ?></li>
                        <li><strong>Email:</strong> <?php echo $_SESSION['usuario_email'] ?? 'Não definido'; ?></li>
                        <li><strong>Nível:</strong> <?php echo $_SESSION['usuario_nivel'] ?? 'Não definido'; ?></li>
                    </ul>
                </div>
                <div class="card-footer">
                    <a href="index.php" class="btn btn-primary">Voltar para o Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir o rodapé
require_once '../templates/footer.php';
?> 