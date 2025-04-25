$(document).ready(function() {
    // Inicializar elementos da UI
    initUI();
    
    // Lidar com alteração no tipo de relatório
    $('#tipoRelatorio').change(function() {
        atualizarTituloRelatorio();
    });
    
    // Lidar com mudança no período
    $('#periodoRelatorio').change(function() {
        let periodo = $(this).val();
        if (periodo === 'personalizado') {
            $('.periodo-personalizado').show();
        } else {
            $('.periodo-personalizado').hide();
        }
        atualizarPeriodoExibicao();
    });
    
    // Lidar com exportação
    $('#btnExportar').click(function() {
        $('#modalExportar').modal('show');
    });
    
    // Confirmar exportação
    $('#btnConfirmarExportacao').click(function() {
        // Simulação de exportação (será implementado com backend)
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Exportando...');
        
        setTimeout(function() {
            // Simular conclusão da exportação
            $('#btnConfirmarExportacao').prop('disabled', false).html('<i class="fas fa-download"></i> Baixar');
            $('#modalExportar').modal('hide');
            
            // Mostrar alerta de sucesso
            Swal.fire({
                icon: 'success',
                title: 'Relatório Exportado!',
                text: 'O relatório foi exportado com sucesso.',
                confirmButtonText: 'OK'
            });
        }, 2000);
    });
    
    // Carregar dados do relatório ao enviar o formulário
    $('form').submit(function(e) {
        // Para fins de demonstração, este exemplo carrega dados simulados
        // Em ambiente real, isso seria feito com requisição AJAX
        
        e.preventDefault();
        carregarDadosRelatorio();
    });
    
    // Para a versão de demonstração, carregar dados iniciais
    carregarDadosRelatorio();
});

/**
 * Inicializa os elementos da UI
 */
function initUI() {
    // Verificar se devemos mostrar campos de data personalizada
    if ($('#periodoRelatorio').val() === 'personalizado') {
        $('.periodo-personalizado').show();
    } else {
        $('.periodo-personalizado').hide();
    }
    
    // Atualizar título do relatório e período de exibição
    atualizarTituloRelatorio();
    atualizarPeriodoExibicao();
    
    // Verificar se há parâmetros na URL para carregar os dados automaticamente
    if (window.location.search) {
        carregarDadosRelatorio();
    }
}

/**
 * Atualiza o título do relatório com base no tipo selecionado
 */
function atualizarTituloRelatorio() {
    let tipo = $('#tipoRelatorio').val();
    let titulo = '';
    
    switch(tipo) {
        case 'vendas':
            titulo = 'Relatório de Vendas';
            break;
        case 'comissoes':
            titulo = 'Relatório de Comissões';
            break;
        case 'resultados':
            titulo = 'Relatório de Resultados';
            break;
        default:
            titulo = 'Relatório';
    }
    
    $('#tituloRelatorio').text(titulo);
}

/**
 * Atualiza o texto de período no topo do relatório
 */
function atualizarPeriodoExibicao() {
    let periodo = $('#periodoRelatorio').val();
    let textoExibicao = '';
    
    switch(periodo) {
        case 'hoje':
            textoExibicao = 'Período: Hoje';
            break;
        case 'ontem':
            textoExibicao = 'Período: Ontem';
            break;
        case '7dias':
            textoExibicao = 'Período: Últimos 7 dias';
            break;
        case '30dias':
            textoExibicao = 'Período: Últimos 30 dias';
            break;
        case 'mes':
            textoExibicao = 'Período: Este mês';
            break;
        case 'mesanterior':
            textoExibicao = 'Período: Mês anterior';
            break;
        case 'personalizado':
            let dataInicio = $('input[name="data_inicio"]').val();
            let dataFim = $('input[name="data_fim"]').val();
            if (dataInicio && dataFim) {
                textoExibicao = 'Período: ' + formatarData(dataInicio) + ' até ' + formatarData(dataFim);
            } else {
                textoExibicao = 'Período personalizado';
            }
            break;
        default:
            textoExibicao = 'Período: Todos os dados';
    }
    
    $('#periodoExibicao').text(textoExibicao);
}

/**
 * Carrega os dados do relatório (simulação para demonstração)
 */
function carregarDadosRelatorio() {
    // Mostrar indicador de carregamento
    Swal.fire({
        title: 'Gerando relatório...',
        html: 'Aguarde enquanto o relatório é processado.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Simular requisição de dados (em ambiente real, isso seria uma requisição AJAX)
    setTimeout(() => {
        // Fechar indicador de carregamento
        Swal.close();
        
        // Obter tipo de relatório
        let tipoRelatorio = $('#tipoRelatorio').val();
        let vendedorId = $('select[name="vendedor_id"]').val();
        
        // Dados simulados para diferentes tipos de relatórios
        let dadosSimulados;
        
        switch(tipoRelatorio) {
            case 'vendas':
                dadosSimulados = gerarDadosVendas(vendedorId);
                break;
            case 'comissoes':
                dadosSimulados = gerarDadosComissoes(vendedorId);
                break;
            case 'resultados':
                dadosSimulados = gerarDadosResultados(vendedorId);
                break;
            default:
                dadosSimulados = gerarDadosVendas(vendedorId);
        }
        
        // Atualizar a interface com os dados
        atualizarInterfaceComDados(dadosSimulados);
    }, 1500);
}

/**
 * Gera dados simulados para relatório de vendas
 * @param {string} vendedorId ID do vendedor selecionado (opcional)
 * @returns {object} Dados simulados para o relatório
 */
function gerarDadosVendas(vendedorId) {
    // Dados simulados para vendas
    let vendedores = ['João Silva', 'Maria Oliveira', 'Carlos Santos', 'Ana Souza', 'Pedro Lima'];
    let dadosVendas = {
        totalVendas: 12500.75,
        totalComissoes: 1250.08,
        percentualMedio: 10.00,
        totalBilhetes: 250,
        valorMedioBilhete: 50.00,
        totalVendedores: 5,
        vendedorDestaque: 'João Silva',
        grafico: {
            labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
            datasets: [
                {
                    label: 'Vendas',
                    data: [4500, 5200, 6000, 8200, 9800, 12500],
                    backgroundColor: 'rgba(13, 110, 253, 0.2)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 1
                }
            ]
        },
        detalhamento: []
    };
    
    // Gerar detalhamento
    for (let i = 1; i <= 20; i++) {
        let vendedor = vendedores[Math.floor(Math.random() * vendedores.length)];
        let valor = Math.random() * 1000 + 100;
        let comissao = valor * 0.1;
        let status = Math.random() > 0.7 ? 'Pago' : (Math.random() > 0.5 ? 'Pendente' : 'Cancelado');
        
        dadosVendas.detalhamento.push({
            id: i,
            data: formatarData(gerarDataAleatoria()),
            vendedor: vendedor,
            valor: valor.toFixed(2),
            comissao: comissao.toFixed(2),
            status: status
        });
    }
    
    return dadosVendas;
}

/**
 * Gera dados simulados para relatório de comissões
 * @param {string} vendedorId ID do vendedor selecionado (opcional)
 * @returns {object} Dados simulados para o relatório
 */
function gerarDadosComissoes(vendedorId) {
    // Dados simulados similares a vendas, mas com foco em comissões
    let dadosComissoes = gerarDadosVendas(vendedorId);
    
    // Modificar alguns campos específicos para comissões
    dadosComissoes.grafico.datasets[0].label = 'Comissões';
    dadosComissoes.grafico.datasets[0].backgroundColor = 'rgba(25, 135, 84, 0.2)';
    dadosComissoes.grafico.datasets[0].borderColor = 'rgba(25, 135, 84, 1)';
    
    // Ajustar dados do gráfico
    for (let i = 0; i < dadosComissoes.grafico.datasets[0].data.length; i++) {
        dadosComissoes.grafico.datasets[0].data[i] = dadosComissoes.grafico.datasets[0].data[i] * 0.1;
    }
    
    return dadosComissoes;
}

/**
 * Gera dados simulados para relatório de resultados
 * @param {string} vendedorId ID do vendedor selecionado (opcional)
 * @returns {object} Dados simulados para o relatório
 */
function gerarDadosResultados(vendedorId) {
    // Dados simulados com foco em resultados
    let dadosResultados = gerarDadosVendas(vendedorId);
    
    // Modificar alguns campos específicos para resultados
    dadosResultados.grafico.labels = ['1º', '2º', '3º', '4º', '5º', '6º'];
    dadosResultados.grafico.datasets[0].label = 'Pontuação';
    dadosResultados.grafico.datasets[0].backgroundColor = 'rgba(13, 202, 240, 0.2)';
    dadosResultados.grafico.datasets[0].borderColor = 'rgba(13, 202, 240, 1)';
    
    // Ajustar dados do gráfico
    for (let i = 0; i < dadosResultados.grafico.datasets[0].data.length; i++) {
        dadosResultados.grafico.datasets[0].data[i] = Math.floor(Math.random() * 100);
    }
    
    return dadosResultados;
}

/**
 * Atualiza a interface com os dados do relatório
 * @param {object} dados Dados a serem exibidos
 */
function atualizarInterfaceComDados(dados) {
    // Atualizar cards de resumo
    $('#totalVendas').text('R$ ' + formatarNumero(dados.totalVendas));
    $('#totalComissoes').text('R$ ' + formatarNumero(dados.totalComissoes));
    $('#percentualMedio').text(formatarNumero(dados.percentualMedio) + '%');
    $('#totalBilhetes').text(formatarNumero(dados.totalBilhetes));
    $('#valorMedioBilhete').text('R$ ' + formatarNumero(dados.valorMedioBilhete));
    $('#totalVendedores').text(formatarNumero(dados.totalVendedores));
    $('#vendedorDestaque').text(dados.vendedorDestaque);
    
    // Atualizar gráfico
    atualizarGrafico(dados.grafico);
    
    // Atualizar tabela de detalhamento
    atualizarTabelaDetalhamento(dados.detalhamento);
}

/**
 * Atualiza o gráfico com novos dados
 * @param {object} dadosGrafico Dados para o gráfico
 */
function atualizarGrafico(dadosGrafico) {
    // Destruir gráfico existente se houver
    if (window.graficoRelatorio) {
        window.graficoRelatorio.destroy();
    }
    
    // Criar novo gráfico
    let ctx = document.getElementById('graficoRelatorio').getContext('2d');
    window.graficoRelatorio = new Chart(ctx, {
        type: 'bar',
        data: dadosGrafico,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

/**
 * Atualiza a tabela de detalhamento
 * @param {array} dados Array com dados para a tabela
 */
function atualizarTabelaDetalhamento(dados) {
    let tabela = $('#tabelaRelatorio tbody');
    tabela.empty();
    
    if (dados.length === 0) {
        tabela.append(`
            <tr>
                <td colspan="7" class="text-center py-3">
                    <p class="text-muted mb-0">Nenhum dado encontrado para o período selecionado.</p>
                </td>
            </tr>
        `);
        return;
    }
    
    // Preencher tabela com dados
    dados.forEach(item => {
        let statusClass = '';
        if (item.status === 'Pago') {
            statusClass = 'bg-success';
        } else if (item.status === 'Pendente') {
            statusClass = 'bg-warning';
        } else {
            statusClass = 'bg-danger';
        }
        
        tabela.append(`
            <tr>
                <td>${item.id}</td>
                <td>${item.data}</td>
                <td>${item.vendedor}</td>
                <td>R$ ${formatarNumero(item.valor)}</td>
                <td>R$ ${formatarNumero(item.comissao)}</td>
                <td><span class="badge ${statusClass}">${item.status}</span></td>
                <td>
                    <button class="btn btn-sm btn-primary btn-detalhes" data-id="${item.id}">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `);
    });
    
    // Adicionar evento para botões de detalhes
    $('.btn-detalhes').click(function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Detalhes do Registro #' + id,
            text: 'Detalhes completos seriam exibidos aqui.',
            icon: 'info'
        });
    });
}

/**
 * Formata um número para exibição
 * @param {number} valor Valor a ser formatado
 * @returns {string} Valor formatado
 */
function formatarNumero(valor) {
    return parseFloat(valor).toFixed(2).replace('.', ',').replace(/(\d)(?=(\d{3})+\,)/g, '$1.');
}

/**
 * Formata uma data YYYY-MM-DD para DD/MM/YYYY
 * @param {string} data Data no formato YYYY-MM-DD
 * @returns {string} Data no formato DD/MM/YYYY
 */
function formatarData(data) {
    if (!data) return '';
    
    let partes = data.split('-');
    if (partes.length !== 3) return data;
    
    return partes[2] + '/' + partes[1] + '/' + partes[0];
}

/**
 * Gera uma data aleatória para demonstração
 * @returns {string} Data no formato YYYY-MM-DD
 */
function gerarDataAleatoria() {
    // Data entre 1 e 180 dias atrás
    let dias = Math.floor(Math.random() * 180) + 1;
    let data = new Date();
    data.setDate(data.getDate() - dias);
    
    let ano = data.getFullYear();
    let mes = (data.getMonth() + 1).toString().padStart(2, '0');
    let dia = data.getDate().toString().padStart(2, '0');
    
    return `${ano}-${mes}-${dia}`;
} 