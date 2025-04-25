$(document).ready(function() {
    // Inicializa os tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Inicializa o datepicker para campos de data
    $('.datepicker').datepicker({
        format: 'dd/mm/yyyy',
        language: 'pt-BR',
        autoclose: true
    });
    
    // Inicializa o select2 para selects com busca
    $('.select2').select2({
        theme: 'bootstrap4',
        language: 'pt-BR',
        width: '100%'
    });
    
    // Funções do checkbox geral para marcar/desmarcar todos
    $('#checkTodos').change(function() {
        $('.comissao-check').prop('checked', $(this).prop('checked'));
        atualizarBotaoPagamento();
    });
    
    // Função para quando os checkboxes individuais são alterados
    $(document).on('change', '.comissao-check', function() {
        atualizarBotaoPagamento();
        
        // Verifica se todos estão selecionados para atualizar o checkbox geral
        let todos = $('.comissao-check').length;
        let selecionados = $('.comissao-check:checked').length;
        
        $('#checkTodos').prop('checked', todos === selecionados && todos > 0);
    });
    
    // Função para atualizar o botão de pagamento baseado nas seleções
    function atualizarBotaoPagamento() {
        let selecionados = $('.comissao-check:checked').length;
        let total = 0;
        
        $('.comissao-check:checked').each(function() {
            total += parseFloat($(this).data('valor') || 0);
        });
        
        $('#btnPagarSelecionadas').prop('disabled', selecionados === 0);
        $('#numSelecionadas').text(selecionados);
        $('#valorTotalSelecionadas').text(formatarMoeda(total));
    }
    
    // Função para abrir o modal de detalhes da comissão
    $('.btn-detalhes').click(function() {
        let id = $(this).data('id');
        let vendedor = $(this).data('vendedor');
        let valor = $(this).data('valor');
        let data = $(this).data('data');
        let status = $(this).data('status');
        let concurso = $(this).data('concurso');
        let cotas = $(this).data('cotas');
        
        $('#modalDetalhes .comissao-id').text(id);
        $('#modalDetalhes .comissao-vendedor').text(vendedor);
        $('#modalDetalhes .comissao-valor').text(formatarMoeda(valor));
        $('#modalDetalhes .comissao-data').text(data);
        $('#modalDetalhes .comissao-status').text(status);
        $('#modalDetalhes .comissao-concurso').text(concurso);
        $('#modalDetalhes .comissao-cotas').text(cotas);
        
        $('#modalDetalhes').modal('show');
    });
    
    // Função para abrir o modal de pagamento
    $('#btnPagarSelecionadas').click(function() {
        let ids = [];
        let total = 0;
        
        $('.comissao-check:checked').each(function() {
            ids.push($(this).val());
            total += parseFloat($(this).data('valor') || 0);
        });
        
        $('#pagamentoIdsComissoes').val(ids.join(','));
        $('#pagamentoTotal').text(formatarMoeda(total));
        $('#pagamentoNumComissoes').text(ids.length);
        
        $('#modalPagamento').modal('show');
    });
    
    // Função para confirmar o pagamento
    $('#formPagamento').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'actions/pagar_comissoes.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend: function() {
                $('#btnConfirmarPagamento').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processando...');
            },
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pagamento realizado!',
                        text: response.message,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: response.message || 'Ocorreu um erro ao processar o pagamento.',
                        confirmButtonText: 'OK'
                    });
                    $('#btnConfirmarPagamento').prop('disabled', false).html('Confirmar Pagamento');
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Ocorreu um erro de comunicação com o servidor.',
                    confirmButtonText: 'OK'
                });
                $('#btnConfirmarPagamento').prop('disabled', false).html('Confirmar Pagamento');
            }
        });
    });
    
    // Função para abrir o modal de cancelamento
    $('.btn-cancelar').click(function() {
        let id = $(this).data('id');
        let vendedor = $(this).data('vendedor');
        let valor = $(this).data('valor');
        
        $('#cancelamentoIdComissao').val(id);
        $('#cancelamentoVendedor').text(vendedor);
        $('#cancelamentoValor').text(formatarMoeda(valor));
        
        $('#modalCancelamento').modal('show');
    });
    
    // Função para confirmar o cancelamento
    $('#formCancelamento').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'actions/cancelar_comissao.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend: function() {
                $('#btnConfirmarCancelamento').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processando...');
            },
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Comissão cancelada!',
                        text: response.message,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: response.message || 'Ocorreu um erro ao cancelar a comissão.',
                        confirmButtonText: 'OK'
                    });
                    $('#btnConfirmarCancelamento').prop('disabled', false).html('Confirmar Cancelamento');
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Ocorreu um erro de comunicação com o servidor.',
                    confirmButtonText: 'OK'
                });
                $('#btnConfirmarCancelamento').prop('disabled', false).html('Confirmar Cancelamento');
            }
        });
    });
    
    // Função para lidar com o botão de limpar filtros
    $('#btnLimparFiltros').click(function() {
        window.location.href = 'comissoes.php';
    });
    
    // Função para formatação de valores monetários
    function formatarMoeda(valor) {
        return 'R$ ' + parseFloat(valor).toFixed(2).replace('.', ',').replace(/(\d)(?=(\d{3})+\,)/g, '$1.');
    }
    
    // Inicializa a tabela de comissões com DataTables
    var tabelaComissoes = $('#tabelaComissoes').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json'
        },
        responsive: true,
        order: [[1, 'desc']], // Ordena por data de forma descendente
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: [0, 7] } // Desativa ordenação para colunas de checkbox e ações
        ]
    });
    
    // Aplica a busca por coluna
    $('#tabelaComissoes tfoot th').each(function (i) {
        if (i > 0 && i < 7) { // Ignora a primeira e última coluna
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm" placeholder="Buscar ' + title + '" />');
        }
    });
 
    // Aplica a busca ao digitar
    tabelaComissoes.columns().every(function (index) {
        if (index > 0 && index < 7) { // Ignora a primeira e última coluna
            var that = this;
            $('input', this.footer()).on('keyup change', function () {
                if (that.search() !== this.value) {
                    that.search(this.value).draw();
                }
            });
        }
    });
}); 