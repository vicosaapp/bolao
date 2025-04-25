<?php
// Definir o título da página
$page_title = "Gerenciamento de Usuários";

// CSS específico para esta página
$extraCSS = [
    '../assets/css/usuarios.css'
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

// Estatísticas
$totalUsuarios = $usuarioModel->contarUsuarios();
$usuariosAtivos = $usuarioModel->contarUsuariosAtivos();
$usuariosInativos = $totalUsuarios - $usuariosAtivos;
$admins = $usuarioModel->contarUsuariosPorNivel(3) + $usuarioModel->contarUsuariosPorNivel(4);
?>

<div class="content">
    <div class="content-header">
        <h1>Gerenciamento de Usuários</h1>
        <p class="text-muted">Gerencie usuários, defina níveis de acesso e monitore atividades.</p>
    </div>
    
    <!-- Alertas -->
    <div id="alertContainer"></div>
    
    <!-- Estatísticas -->
    <div class="dashboard-stats mb-4">
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card primary">
                    <div class="stat-card-body">
                        <h5 class="stat-card-title">Total de Usuários</h5>
                        <div class="stat-card-value"><?= $totalUsuarios ?></div>
                        <div class="stat-card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card success">
                    <div class="stat-card-body">
                        <h5 class="stat-card-title">Usuários Ativos</h5>
                        <div class="stat-card-value"><?= $usuariosAtivos ?></div>
                        <div class="stat-card-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card danger">
                    <div class="stat-card-body">
                        <h5 class="stat-card-title">Usuários Inativos</h5>
                        <div class="stat-card-value"><?= $usuariosInativos ?></div>
                        <div class="stat-card-icon">
                            <i class="fas fa-user-times"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card primary">
                    <div class="stat-card-body">
                        <h5 class="stat-card-title">Administradores</h5>
                        <div class="stat-card-value"><?= $admins ?></div>
                        <div class="stat-card-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Barra de ferramentas -->
    <div class="row mb-3">
        <div class="col-md-8">
            <div class="btn-toolbar">
                <button id="btnAbrirModalAdicionar" class="btn btn-primary me-2">
                    <i class="fas fa-plus"></i> Adicionar Usuário
                </button>
            </div>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="m-0">Filtros</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filtroStatus">Status</label>
                        <select id="filtroStatus" class="form-select">
                            <option value="">Todos</option>
                            <option value="1">Ativos</option>
                            <option value="0">Inativos</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filtroNivel">Tipo de Usuário</label>
                        <select id="filtroNivel" class="form-select">
                            <option value="">Todos</option>
                            <option value="1">Operador</option>
                            <option value="2">Gerente</option>
                            <option value="3">Administrador</option>
                            <?php if ($_SESSION['usuario_nivel'] == 4): ?>
                            <option value="4">Super Admin</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="termoBusca">Buscar</label>
                        <div class="input-group">
                            <input type="text" id="termoBusca" class="form-control" placeholder="Buscar por nome ou email">
                            <button id="btnBuscar" class="btn btn-outline-secondary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabela de Usuários -->
    <div class="card">
        <div class="card-header">
            <h5 class="m-0">Lista de Usuários</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Nível</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="usuariosTableBody">
                        <tr class="loading-row">
                            <td colspan="6" class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Carregando...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer">
            <div id="paginacao" class="pagination pagination-sm justify-content-end"></div>
        </div>
    </div>
</div>

<!-- Modal Adicionar Usuário -->
<div class="modal fade" id="adicionarUsuarioModal" tabindex="-1" aria-labelledby="adicionarUsuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adicionarUsuarioModalLabel">Adicionar Novo Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formAdicionarUsuario">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label for="nivel_acesso" class="form-label">Nível de Acesso</label>
                        <select class="form-select" id="nivel_acesso" name="nivel_acesso" required>
                            <option value="1">Operador</option>
                            <option value="2">Gerente</option>
                            <option value="3">Administrador</option>
                            <?php if ($_SESSION['usuario_nivel'] == 4): ?>
                            <option value="4">Super Admin</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formAdicionarUsuario" class="btn btn-primary" id="btnAdicionarUsuario">Adicionar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Usuário -->
<div class="modal fade" id="editarUsuarioModal" tabindex="-1" aria-labelledby="editarUsuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarUsuarioModalLabel">Editar Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarUsuario">
                    <input type="hidden" name="id">
                    <div class="mb-3">
                        <label for="nomeEdit" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nomeEdit" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="emailEdit" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="emailEdit" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="senhaEdit" class="form-label">Nova Senha (deixe em branco para manter a atual)</label>
                        <input type="password" class="form-control" id="senhaEdit" name="senha" minlength="6">
                    </div>
                    <div class="mb-3">
                        <label for="nivel_acessoEdit" class="form-label">Nível de Acesso</label>
                        <select class="form-select" id="nivel_acessoEdit" name="nivel_acesso" required>
                            <option value="1">Operador</option>
                            <option value="2">Gerente</option>
                            <option value="3">Administrador</option>
                            <?php if ($_SESSION['usuario_nivel'] == 4): ?>
                            <option value="4">Super Admin</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="statusEdit" class="form-label">Status</label>
                        <select class="form-select" id="statusEdit" name="status" required>
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formEditarUsuario" class="btn btn-primary" id="btnSalvarEdicao">Salvar Alterações</button>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir o rodapé
require_once '../templates/footer.php';
?>

<!-- Custom JS -->
<script src="../assets/js/usuarios.js"></script> 