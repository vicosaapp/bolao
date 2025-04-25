/**
 * Gerenciamento de Usuários
 * Sistema de Bolão
 */

// Variáveis globais para paginação e filtros
let paginaAtual = 1;
let itensPorPagina = 10;
let filtroNivel = '';
let filtroStatus = '';
let termoBusca = '';

$(document).ready(function() {
    // Ajustar o layout para evitar que o conteúdo fique atrás do menu
    ajustarLayout();

    // Inicializar carregamento de usuários
    carregarUsuarios();

    // Inicializar eventos de filtro
    initFiltros();

    // Inicializar eventos de formulários
    initFormularios();

    // Inicializar tooltips e outros componentes
    inicializarComponentes();
});

/**
 * Ajusta o layout da página para evitar que o conteúdo fique atrás do menu
 */
function ajustarLayout() {
    // Adiciona a classe 'content' ao container principal se ainda não existir
    if (!$('.container-fluid').hasClass('content')) {
        $('.container-fluid').wrap('<div class="content"></div>');
    }
    
    // Garante que o CSS específico esteja carregado
    if ($('link[href="../assets/css/usuarios.css"]').length === 0) {
        $('head').append('<link rel="stylesheet" href="../assets/css/usuarios.css">');
    }
}

/**
 * Inicializa componentes visuais
 */
function inicializarComponentes() {
    // Inicializar tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });
}

/**
 * Inicializa eventos de filtro
 */
function initFiltros() {
    // Filtro por nível
    const filtroNivelSelect = document.getElementById('filtroNivel');
    if (filtroNivelSelect) {
        filtroNivelSelect.addEventListener('change', function() {
            filtroNivel = this.value;
            paginaAtual = 1; // Volta para a primeira página ao filtrar
            carregarUsuarios();
        });
    }

    // Filtro por status
    const filtroStatusSelect = document.getElementById('filtroStatus');
    if (filtroStatusSelect) {
        filtroStatusSelect.addEventListener('change', function() {
            filtroStatus = this.value;
            paginaAtual = 1;
            carregarUsuarios();
        });
    }

    // Campo de busca
    const buscaInput = document.getElementById('termoBusca');
    const btnBuscar = document.getElementById('btnBuscar');
    
    if (buscaInput && btnBuscar) {
        // Botão de busca
        btnBuscar.addEventListener('click', function() {
            termoBusca = buscaInput.value.trim();
            paginaAtual = 1;
            carregarUsuarios();
        });

        // Busca ao pressionar Enter
        buscaInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                termoBusca = this.value.trim();
                paginaAtual = 1;
                carregarUsuarios();
                e.preventDefault();
            }
        });
    }
}

/**
 * Inicializa eventos para formulários
 */
function initFormularios() {
    // Formulário de adição de usuário
    const formAdicionarUsuario = document.getElementById('formAdicionarUsuario');
    const btnAdicionarUsuario = document.getElementById('btnAdicionarUsuario');
    
    if (formAdicionarUsuario && btnAdicionarUsuario) {
        formAdicionarUsuario.addEventListener('submit', function(e) {
            e.preventDefault();
            adicionarUsuario();
        });
    }

    // Botão para abrir modal de adição
    const btnAbrirModalAdicionar = document.getElementById('btnAbrirModalAdicionar');
    if (btnAbrirModalAdicionar) {
        btnAbrirModalAdicionar.addEventListener('click', function() {
            // Resetar formulário
            if (formAdicionarUsuario) {
                formAdicionarUsuario.reset();
            }
            // Abrir modal
            const modal = new bootstrap.Modal(document.getElementById('adicionarUsuarioModal'));
            modal.show();
        });
    }

    // Formulário de edição de usuário
    const formEditarUsuario = document.getElementById('formEditarUsuario');
    const btnSalvarEdicao = document.getElementById('btnSalvarEdicao');
    
    if (formEditarUsuario && btnSalvarEdicao) {
        formEditarUsuario.addEventListener('submit', function(e) {
            e.preventDefault();
            editarUsuario();
        });
    }
}

/**
 * Carrega a lista de usuários
 */
function carregarUsuarios() {
    const tableBody = document.getElementById('usuariosTableBody');
    const paginacao = document.getElementById('paginacao');
    
    if (!tableBody) return;
    
    // Construir URL com parâmetros
    let url = `ajax/usuarios.php?acao=listar&pagina=${paginaAtual}&itens=${itensPorPagina}`;
    
    if (filtroNivel) {
        url += `&nivel=${filtroNivel}`;
    }
    
    if (filtroStatus) {
        url += `&status=${filtroStatus}`;
    }
    
    if (termoBusca) {
        url += `&busca=${encodeURIComponent(termoBusca)}`;
    }
    
    // Exibir loading
    tableBody.innerHTML = '<tr><td colspan="6" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Carregando...</span></div></td></tr>';
    
    // Fazer a requisição AJAX
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Limpar tabela
                tableBody.innerHTML = '';
                
                // Verificar se tem usuários
                if (data.usuarios.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Nenhum usuário encontrado</td></tr>';
                    return;
                }
                
                // Adicionar usuários à tabela
                data.usuarios.forEach(usuario => {
                    const row = document.createElement('tr');
                    
                    // Definir classe para usuários inativos
                    if (usuario.status === '0') {
                        row.classList.add('table-secondary');
                    }
                    
                    // Definir tipo de usuário
                    let tipoUsuario = '';
                    switch (usuario.nivel_acesso) {
                        case '1': tipoUsuario = 'Operador'; break;
                        case '2': tipoUsuario = 'Gerente'; break;
                        case '3': tipoUsuario = 'Administrador'; break;
                        case '4': tipoUsuario = 'Superadmin'; break;
                        default: tipoUsuario = 'Desconhecido';
                    }
                    
                    // Badge de status
                    const statusBadge = usuario.status === '1' 
                        ? '<span class="badge bg-success">Ativo</span>' 
                        : '<span class="badge bg-secondary">Inativo</span>';
                    
                    row.innerHTML = `
                        <td>${usuario.id}</td>
                        <td>${usuario.nome}</td>
                        <td>${usuario.email}</td>
                        <td>${tipoUsuario}</td>
                        <td>${statusBadge}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary btn-editar" data-id="${usuario.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-excluir" data-id="${usuario.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    `;
                    
                    tableBody.appendChild(row);
                });
                
                // Configurar eventos para botões de edição e exclusão
                configurarBotoesAcao();
                
                // Atualizar paginação
                atualizarPaginacao(data.totalPaginas);
            } else {
                // Exibir mensagem de erro
                exibirAlerta('danger', 'Erro ao carregar usuários: ' + data.message);
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Erro ao carregar dados</td></tr>';
            }
        })
        .catch(error => {
            console.error('Erro ao carregar usuários:', error);
            exibirAlerta('danger', 'Erro ao carregar usuários. Verifique o console para mais detalhes.');
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Erro ao carregar dados</td></tr>';
        });
}

/**
 * Configura eventos para botões de ação na tabela
 */
function configurarBotoesAcao() {
    // Botões de edição
    const botoesEditar = document.querySelectorAll('.btn-editar');
    botoesEditar.forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            carregarUsuario(userId);
        });
    });
    
    // Botões de exclusão
    const botoesExcluir = document.querySelectorAll('.btn-excluir');
    botoesExcluir.forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            confirmarExclusao(userId);
        });
    });
}

/**
 * Atualiza os controles de paginação
 */
function atualizarPaginacao(totalPaginas) {
    const paginacao = document.getElementById('paginacao');
    if (!paginacao) return;
    
    paginacao.innerHTML = '';
    
    if (totalPaginas <= 1) return;
    
    // Controles de paginação
    const ul = document.createElement('ul');
    ul.className = 'pagination justify-content-center';
    
    // Botão anterior
    const liAnterior = document.createElement('li');
    liAnterior.className = `page-item ${paginaAtual === 1 ? 'disabled' : ''}`;
    
    const btnAnterior = document.createElement('button');
    btnAnterior.className = 'page-link';
    btnAnterior.innerHTML = '&laquo;';
    btnAnterior.addEventListener('click', function() {
        if (paginaAtual > 1) {
            paginaAtual--;
            carregarUsuarios();
        }
    });
    
    liAnterior.appendChild(btnAnterior);
    ul.appendChild(liAnterior);
    
    // Páginas
    for (let i = 1; i <= totalPaginas; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${paginaAtual === i ? 'active' : ''}`;
        
        const btn = document.createElement('button');
        btn.className = 'page-link';
        btn.innerText = i;
        btn.addEventListener('click', function() {
            paginaAtual = i;
            carregarUsuarios();
        });
        
        li.appendChild(btn);
        ul.appendChild(li);
    }
    
    // Botão próximo
    const liProximo = document.createElement('li');
    liProximo.className = `page-item ${paginaAtual === totalPaginas ? 'disabled' : ''}`;
    
    const btnProximo = document.createElement('button');
    btnProximo.className = 'page-link';
    btnProximo.innerHTML = '&raquo;';
    btnProximo.addEventListener('click', function() {
        if (paginaAtual < totalPaginas) {
            paginaAtual++;
            carregarUsuarios();
        }
    });
    
    liProximo.appendChild(btnProximo);
    ul.appendChild(liProximo);
    
    paginacao.appendChild(ul);
}

/**
 * Carrega os dados de um usuário para edição
 */
function carregarUsuario(userId) {
    // Fazer requisição AJAX para obter dados do usuário
    fetch(`ajax/usuarios.php?acao=obter&id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Preencher formulário de edição
                const form = document.getElementById('formEditarUsuario');
                form.querySelector('input[name="id"]').value = data.usuario.id;
                form.querySelector('input[name="nome"]').value = data.usuario.nome;
                form.querySelector('input[name="email"]').value = data.usuario.email;
                form.querySelector('select[name="nivel_acesso"]').value = data.usuario.nivel_acesso;
                form.querySelector('select[name="status"]').value = data.usuario.status;
                
                // Abrir modal de edição
                const modal = new bootstrap.Modal(document.getElementById('editarUsuarioModal'));
                modal.show();
            } else {
                exibirAlerta('danger', 'Erro ao carregar dados do usuário: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro ao carregar usuário:', error);
            exibirAlerta('danger', 'Erro ao carregar dados do usuário');
        });
}

/**
 * Adiciona um novo usuário
 */
function adicionarUsuario() {
    const form = document.getElementById('formAdicionarUsuario');
    const btnAdicionar = document.getElementById('btnAdicionarUsuario');
    
    // Validar formulário
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Desabilitar botão e mostrar loading
    btnAdicionar.disabled = true;
    btnAdicionar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando...';
    
    // Obter dados do formulário
    const formData = new FormData(form);
    formData.append('acao', 'adicionar');
    
    // Enviar requisição AJAX
    fetch('ajax/usuarios.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('adicionarUsuarioModal'));
            modal.hide();
            
            // Exibir mensagem de sucesso
            exibirAlerta('success', 'Usuário adicionado com sucesso!');
            
            // Recarregar lista de usuários
            carregarUsuarios();
        } else {
            exibirAlerta('danger', 'Erro ao adicionar usuário: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro ao adicionar usuário:', error);
        exibirAlerta('danger', 'Erro ao adicionar usuário');
    })
    .finally(() => {
        // Restaurar botão
        btnAdicionar.disabled = false;
        btnAdicionar.innerHTML = 'Adicionar';
    });
}

/**
 * Edita um usuário existente
 */
function editarUsuario() {
    const form = document.getElementById('formEditarUsuario');
    const btnSalvar = document.getElementById('btnSalvarEdicao');
    
    // Validar formulário
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Desabilitar botão e mostrar loading
    btnSalvar.disabled = true;
    btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvando...';
    
    // Obter dados do formulário
    const formData = new FormData(form);
    formData.append('acao', 'editar');
    
    // Enviar requisição AJAX
    fetch('ajax/usuarios.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('editarUsuarioModal'));
            modal.hide();
            
            // Exibir mensagem de sucesso
            exibirAlerta('success', 'Usuário atualizado com sucesso!');
            
            // Recarregar lista de usuários
            carregarUsuarios();
        } else {
            exibirAlerta('danger', 'Erro ao atualizar usuário: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro ao atualizar usuário:', error);
        exibirAlerta('danger', 'Erro ao atualizar usuário');
    })
    .finally(() => {
        // Restaurar botão
        btnSalvar.disabled = false;
        btnSalvar.innerHTML = 'Salvar Alterações';
    });
}

/**
 * Confirma e executa a exclusão de um usuário
 */
function confirmarExclusao(userId) {
    if (confirm('Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.')) {
        // Enviar requisição AJAX para excluir
        fetch('ajax/usuarios.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `acao=excluir&id=${userId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                exibirAlerta('success', 'Usuário excluído com sucesso!');
                carregarUsuarios();
            } else {
                exibirAlerta('danger', 'Erro ao excluir usuário: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro ao excluir usuário:', error);
            exibirAlerta('danger', 'Erro ao excluir usuário');
        });
    }
}

/**
 * Exibe um alerta na página
 */
function exibirAlerta(tipo, mensagem) {
    const alertContainer = document.getElementById('alertContainer');
    if (!alertContainer) return;
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${tipo} alert-dismissible fade show`;
    alert.innerHTML = `
        ${mensagem}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    alertContainer.appendChild(alert);
    
    // Auto-fechar após 5 segundos
    setTimeout(() => {
        alert.classList.remove('show');
        setTimeout(() => alert.remove(), 150);
    }, 5000);
} 