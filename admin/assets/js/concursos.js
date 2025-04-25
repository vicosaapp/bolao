/**
 * JavaScript para a página de Concursos
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar contador regressivo
    inicializarContador();
    
    // Configurar modais de detalhes
    configurarModalDetalhes();
});

/**
 * Inicializa o contador regressivo para a data de sorteio
 */
function inicializarContador() {
    const countdownElements = document.querySelectorAll('.countdown');
    
    countdownElements.forEach(function(element) {
        const dataFinal = new Date(element.dataset.date).getTime();
        
        // Atualizar a cada segundo
        const interval = setInterval(function() {
            // Data atual
            const agora = new Date().getTime();
            
            // Diferença entre as datas
            const diferenca = dataFinal - agora;
            
            // Cálculos de tempo
            const dias = Math.floor(diferenca / (1000 * 60 * 60 * 24));
            const horas = Math.floor((diferenca % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutos = Math.floor((diferenca % (1000 * 60 * 60)) / (1000 * 60));
            const segundos = Math.floor((diferenca % (1000 * 60)) / 1000);
            
            // Atualizar elementos
            element.querySelector('.days').textContent = formatarNumero(dias);
            element.querySelector('.hours').textContent = formatarNumero(horas);
            element.querySelector('.minutes').textContent = formatarNumero(minutos);
            element.querySelector('.seconds').textContent = formatarNumero(segundos);
            
            // Se o contador chegou ao fim
            if (diferenca < 0) {
                clearInterval(interval);
                element.innerHTML = '<div class="alert alert-info mb-0">O sorteio já ocorreu!</div>';
            }
        }, 1000);
    });
}

/**
 * Formata um número para sempre ter 2 dígitos
 * @param {number} numero - Número a ser formatado
 * @return {string} Número formatado com 2 dígitos
 */
function formatarNumero(numero) {
    return numero < 10 ? '0' + numero : numero;
}

/**
 * Configura o modal de detalhes dos concursos
 */
function configurarModalDetalhes() {
    const btnDetalhes = document.querySelectorAll('.ver-concurso');
    const modal = document.getElementById('modalDetalhes');
    const modalInstance = new bootstrap.Modal(modal);
    const btnComprar = document.getElementById('btn-comprar-bilhete');
    
    btnDetalhes.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const concursoId = this.dataset.id;
            
            // Configurar elementos iniciais
            document.getElementById('carregando').style.display = 'block';
            document.getElementById('detalhes-concurso').style.display = 'none';
            document.getElementById('erro-carregamento').style.display = 'none';
            
            // Configurar botão de compra
            btnComprar.href = 'comprar-bilhete.php?id=' + concursoId;
            
            // Abrir modal
            modalInstance.show();
            
            // Carregar dados do concurso
            carregarDetalhesConcurso(concursoId);
        });
    });
}

/**
 * Carrega os detalhes do concurso via AJAX
 * @param {number} concursoId - ID do concurso
 */
function carregarDetalhesConcurso(concursoId) {
    fetch('../api/get-concurso-detalhes.php?id=' + concursoId)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao carregar detalhes do concurso');
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('carregando').style.display = 'none';
            
            if (data.success) {
                renderizarDetalhesConcurso(data.concurso);
                document.getElementById('detalhes-concurso').style.display = 'block';
            } else {
                document.getElementById('erro-carregamento').style.display = 'block';
                document.getElementById('erro-carregamento').textContent = data.message || 'Erro ao carregar detalhes do concurso';
            }
        })
        .catch(error => {
            document.getElementById('carregando').style.display = 'none';
            document.getElementById('erro-carregamento').style.display = 'block';
            document.getElementById('erro-carregamento').textContent = 'Erro de conexão: ' + error.message;
        });
}

/**
 * Renderiza os detalhes do concurso no modal
 * @param {Object} concurso - Objeto com dados do concurso
 */
function renderizarDetalhesConcurso(concurso) {
    const container = document.getElementById('detalhes-concurso');
    
    // Formatar datas
    const dataInicio = formatarData(concurso.data_inicio);
    const dataFim = formatarData(concurso.data_sorteio || concurso.data_fim);
    
    // Calcular progresso
    const totalBilhetes = parseInt(concurso.total_bilhetes) || 0;
    const bilhetesVendidos = parseInt(concurso.bilhetes_vendidos) || 0;
    const progresso = totalBilhetes > 0 ? Math.min(100, (bilhetesVendidos / totalBilhetes) * 100) : 0;
    
    // Obter classe de status
    let statusClass = 'bg-secondary';
    let statusText = concurso.status;
    
    switch(concurso.status) {
        case 'pendente':
            statusClass = 'bg-warning';
            statusText = 'Pendente';
            break;
        case 'em_andamento':
            statusClass = 'bg-success';
            statusText = 'Em Andamento';
            break;
        case 'finalizado':
            statusClass = 'bg-info';
            statusText = 'Finalizado';
            break;
        case 'cancelado':
            statusClass = 'bg-danger';
            statusText = 'Cancelado';
            break;
    }
    
    // Construir HTML
    let html = `
        <div class="concurso-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="concurso-title">${concurso.titulo || concurso.nome}</h4>
                    <p class="concurso-subtitle">${concurso.descricao || ''}</p>
                </div>
                <span class="badge ${statusClass}">${statusText}</span>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Informações Gerais</h5>
                <p><strong>Número:</strong> ${concurso.numero}</p>
                <p><strong>Início:</strong> ${dataInicio}</p>
                <p><strong>Sorteio:</strong> ${dataFim}</p>
                <p><strong>Valor do Bilhete:</strong> R$ ${formatarValor(concurso.valor_bilhete)}</p>
            </div>
            
            <div class="col-md-6">
                <h5>Premiação</h5>
                <div class="premio-detalhe">
                    <div class="premio-detalhe-title">Prêmio Total</div>
                    <div class="premio-detalhe-valor">R$ ${formatarValor(concurso.valor_premios)}</div>
                    <div class="premio-detalhe-desc">Valor total destinado aos prêmios</div>
                </div>
                
                <p><strong>Bilhetes Vendidos:</strong> ${bilhetesVendidos} de ${totalBilhetes}</p>
                <div class="progress mb-3" style="height: 10px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: ${progresso}%" 
                         aria-valuenow="${progresso}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
    `;
    
    // Adicionar regras do concurso se disponível
    if (concurso.regras) {
        html += `
            <div class="mb-4">
                <h5>Regras do Concurso</h5>
                <div class="card">
                    <div class="card-body">
                        ${concurso.regras}
                    </div>
                </div>
            </div>
        `;
    }
    
    // Adicionar resultados se o concurso estiver finalizado
    if (concurso.status === 'finalizado' && concurso.numeros_sorteados) {
        html += `
            <div class="mb-4">
                <h5>Resultado do Sorteio</h5>
                <div class="card bg-light">
                    <div class="card-body">
                        <p><strong>Números Sorteados:</strong> ${concurso.numeros_sorteados}</p>
                        <p><strong>Data do Sorteio:</strong> ${formatarData(concurso.data_sorteio)}</p>
                        <p><strong>Bilhetes Premiados:</strong> ${concurso.bilhetes_premiados || 0}</p>
                    </div>
                </div>
            </div>
        `;
    }
    
    container.innerHTML = html;
}

/**
 * Formata uma data para o formato brasileiro
 * @param {string} data - Data no formato ISO
 * @return {string} Data formatada
 */
function formatarData(data) {
    if (!data) return 'N/A';
    
    const dt = new Date(data);
    return dt.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

/**
 * Formata um valor para o formato brasileiro de moeda
 * @param {number} valor - Valor a ser formatado
 * @return {string} Valor formatado
 */
function formatarValor(valor) {
    if (!valor) return '0,00';
    
    return parseFloat(valor).toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
} 