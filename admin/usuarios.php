<?php
// Definir o título da página
$page_title = "Gerenciamento de Usuários";

// Verificar permissões
require_once 'auth.php';
requireAccess(3); // Nível mínimo: administrador (3)

// Incluir modelos necessários
require_once 'models/UsuarioModel.php';

// Incluir o cabeçalho
require_once 'templates/header.php';

// Inicializar modelo de usuários
$usuarioModel = new UsuarioModel();

// Buscar usuários
$usuarios = $usuarioModel->listarUsuarios();

// Estatísticas de usuários
$total_usuarios = count($usuarios);
$usuarios_ativos = count(array_filter($usuarios, function($usuario) {
    return $usuario['status'] == 'ativo';
}));
$usuarios_inativos = $total_usuarios - $usuarios_ativos;
?>

<div class="content">
    <div class="content-header">
        <h1>Gerenciamento de Usuários</h1>
        <p class="text-muted">Gerencie os usuários do sistema, adicione, edite ou remova acessos.</p>
    </div>
    
    <!-- Cards de estatísticas -->
    <div class="dashboard-stats">
        <div class="stat-card primary">
            <div class="stat-card-header">
                <h5 class="stat-card-title">Total de Usuários</h5>
                <div class="stat-card-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <h2 class="stat-card-value"><?php echo number_format($total_usuarios, 0, ',', '.'); ?></h2>
            <p class="stat-card-description">
                <i class="fas fa-arrow-up"></i> 
                <span>Usuários cadastrados no sistema</span>
            </p>
            <button class="mt-3 btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#novoUsuarioModal">
                Adicionar Usuário <i class="fas fa-plus"></i>
            </button>
        </div>
        
        <div class="stat-card success">
            <div class="stat-card-header">
                <h5 class="stat-card-title">Usuários Ativos</h5>
                <div class="stat-card-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <h2 class="stat-card-value"><?php echo number_format($usuarios_ativos, 0, ',', '.'); ?></h2>
            <p class="stat-card-description">
                <i class="fas fa-arrow-up"></i> 
                <span>Usuários com acesso liberado</span>
            </p>
        </div>
        
        <div class="stat-card danger">
            <div class="stat-card-header">
                <h5 class="stat-card-title">Usuários Inativos</h5>
                <div class="stat-card-icon">
                    <i class="fas fa-ban"></i>
                </div>
            </div>
            <h2 class="stat-card-value"><?php echo number_format($usuarios_inativos, 0, ',', '.'); ?></h2>
            <p class="stat-card-description">
                <i class="fas fa-arrow-down"></i> 
                <span>Usuários sem acesso</span>
            </p>
        </div>
    </div>
    
    <!-- Tabela de Usuários -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">Lista de Usuários</h5>
                    <div class="card-actions">
                        <input type="text" class="form-control form-control-sm" placeholder="Buscar usuário..." id="buscarUsuario">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Nível de Acesso</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($usuarios as $usuario): ?>
                                <tr>
                                    <td><?php echo $usuario['id']; ?></td>
                                    <td><?php echo $usuario['nome']; ?></td>
                                    <td><?php echo $usuario['email']; ?></td>
                                    <td>
                                        <?php 
                                        $niveis = [
                                            1 => 'Apostador',
                                            2 => 'Revendedor',
                                            3 => 'Administrador',
                                            4 => 'Super Admin'
                                        ];
                                        echo $niveis[$usuario['nivel']]; 
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php 
                                            echo $usuario['status'] == 'ativo' ? 'bg-success' : 'bg-danger'; 
                                        ?>">
                                            <?php echo ucfirst($usuario['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editarUsuarioModal<?php echo $usuario['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger btn-excluir" data-id="<?php echo $usuario['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <span>Mostrando <?php echo count($usuarios); ?> de <?php echo $total_usuarios; ?> usuários</span>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item disabled">
                                <a class="page-link" href="#">Anterior</a>
                            </li>
                            <li class="page-item active">
                                <a class="page-link" href="#">1</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="#">Próximo</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo Usuário -->
<div class="modal fade" id="novoUsuarioModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adicionar Novo Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNovoUsuario">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" name="nome" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Senha</label>
                            <input type="password" class="form-control" name="senha" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nível de Acesso</label>
                            <select class="form-select" name="nivel" required>
                                <option value="1">Apostador</option>
                                <option value="2">Revendedor</option>
                                <option value="3">Administrador</option>
                                <option value="4">Super Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" value="ativo" checked>
                            <label class="form-check-label">Ativo</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" value="inativo">
                            <label class="form-check-label">Inativo</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="salvarNovoUsuario">Salvar</button>
            </div>
        </div>
    </div>
</div>

<?php
// Scripts específicos para a página
$page_scripts = [
    'assets/js/usuarios.js'
];

// Incluir o rodapé
require_once 'templates/footer.php';
?> 