$(document).ready(function() {
    // Inicializar seletores
    initSelectors();
    
    // Inicializar datepickers
    initDatePickers();
    
    // Aplicar filtros quando o formulário for enviado
    $('#filtroForm').on('submit', function(e) {
        e.preventDefault();
        aplicarFiltros();
    });
    
    // Limpar filtros
    $('a[href="log-atividades.php"]').on('click', function() {
        limparFiltros();
    });
    
    // Ajustar layout para menu
    ajustarLayout();
    
    // Quando a janela for redimensionada
    $(window).resize(function() {
        ajustarLayout();
    });
    
    // Evento de clique nas linhas da tabela
    $(document).on('click', '.log-row', function() {
        const logId = $(this).data('id');
        mostrarDetalhesLog(logId);
    });
});

/**
 * Inicializa os seletores select2
 */
function initSelectors() {
    // Inicializar select para usuário e tipo de atividade com Select2
    $('#filtroUsuario').select2({
        placeholder: 'Selecione um usuário',
        allowClear: true,
        width: '100%'
    });
    
    $('#filtroTipo').select2({
        placeholder: 'Selecione um tipo',
        allowClear: true,
        width: '100%'
    });
}

/**
 * Inicializa os datepickers
 */
function initDatePickers() {
    // Configurar flatpickr para os campos de data
    flatpickr('#filtroDataInicio', {
        dateFormat: 'd/m/Y',
        locale: 'pt',
        allowInput: true,
        maxDate: 'today'
    });
    
    flatpickr('#filtroDataFim', {
        dateFormat: 'd/m/Y',
        locale: 'pt',
        allowInput: true,
        maxDate: 'today'
    });
}

/**
 * Aplica os filtros e redireciona para a página com os filtros selecionados
 */
function aplicarFiltros() {
    const usuario = $('#filtroUsuario').val();
    const tipo = $('#filtroTipo').val();
    const dataInicio = $('#filtroDataInicio').val();
    const dataFim = $('#filtroDataFim').val();
    
    let url = 'log-atividades.php?';
    let params = [];
    
    if (usuario) {
        params.push('usuario_id=' + usuario);
    }
    
    if (tipo) {
        params.push('tipo=' + tipo);
    }
    
    if (dataInicio) {
        params.push('data_inicio=' + dataInicio);
    }
    
    if (dataFim) {
        params.push('data_fim=' + dataFim);
    }
    
    // Adicionar página 1 ao aplicar novos filtros
    params.push('pagina=1');
    
    window.location.href = url + params.join('&');
}

/**
 * Limpa todos os filtros e retorna para a página inicial de logs
 */
function limparFiltros() {
    $('#filtroUsuario').val(null).trigger('change');
    $('#filtroTipo').val(null).trigger('change');
    $('#filtroDataInicio').val('');
    $('#filtroDataFim').val('');
    
    window.location.href = 'log-atividades.php';
}

/**
 * Ajusta o layout da página considerando o menu lateral
 */
function ajustarLayout() {
    // Verificar se o CSS está carregado corretamente
    let cssLink = document.querySelector('link[href*="log-atividades.css"]');
    if (!cssLink) {
        cssLink = document.createElement('link');
        cssLink.rel = 'stylesheet';
        cssLink.type = 'text/css';
        cssLink.href = '../assets/css/log-atividades.css';
        document.head.appendChild(cssLink);
    }
    
    // Verificar se a div content existe, senão criar
    if ($('.content').length === 0) {
        $('#main-content').wrap('<div class="content fade-in"></div>');
    }
}

/**
 * Mostra os detalhes de um log de atividade
 */
function mostrarDetalhesLog(logId) {
    // Fazer uma requisição AJAX para obter detalhes do log
    $.ajax({
        url: '../actions/obter_detalhes_log.php',
        type: 'GET',
        data: { id: logId },
        dataType: 'json',
        beforeSend: function() {
            // Mostrar loading
            exibirAlerta('Carregando detalhes...', 'info');
        },
        success: function(response) {
            if (response.status === 'success') {
                // Preencher o modal com os detalhes
                $('#modalDetalhesLog .modal-title').text('Detalhes do Log #' + logId);
                
                let html = '<table class="table table-bordered">';
                html += '<tr><th style="width: 150px;">ID</th><td>' + response.data.id + '</td></tr>';
                html += '<tr><th>Usuário</th><td>' + response.data.usuario_nome + '</td></tr>';
                html += '<tr><th>Tipo</th><td><span class="badge ' + response.data.badge_class + '">' + response.data.tipo + '</span></td></tr>';
                html += '<tr><th>Descrição</th><td>' + response.data.descricao + '</td></tr>';
                html += '<tr><th>IP</th><td>' + response.data.ip + '</td></tr>';
                html += '<tr><th>Data/Hora</th><td>' + response.data.data_hora + '</td></tr>';
                
                if (response.data.dados_adicionais) {
                    html += '<tr><th>Dados Adicionais</th><td><pre>' + JSON.stringify(JSON.parse(response.data.dados_adicionais), null, 2) + '</pre></td></tr>';
                }
                
                html += '</table>';
                
                $('#modalDetalhesLog .modal-body').html(html);
                $('#modalDetalhesLog').modal('show');
                
                // Limpar alerta
                limparAlerta();
            } else {
                exibirAlerta('Erro ao carregar detalhes: ' + response.message, 'danger');
            }
        },
        error: function() {
            exibirAlerta('Erro ao conectar com o servidor', 'danger');
        }
    });
}

/**
 * Exibe um alerta na página
 */
function exibirAlerta(mensagem, tipo) {
    $('#alertaContainer').html('<div class="alert alert-' + tipo + ' alert-dismissible fade show" role="alert">' +
                              mensagem +
                              '<button type="button" class="close" data-dismiss="alert" aria-label="Fechar">' +
                              '<span aria-hidden="true">&times;</span></button></div>');
}

/**
 * Limpa os alertas da página
 */
function limparAlerta() {
    $('#alertaContainer').html('');
} 