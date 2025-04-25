/**
 * Meus Bilhetes - Funcionalidades da página
 */
document.addEventListener('DOMContentLoaded', function() {
    // Selecionar elementos
    const modalDetalhesBilhete = document.getElementById('modalDetalhesBilhete');
    const modalInstance = new bootstrap.Modal(modalDetalhesBilhete);
    const btnVerBilhetes = document.querySelectorAll('.ver-bilhete');
    const btnImprimirBilhete = document.getElementById('btnImprimirBilhete');
    const loadingIndicator = modalDetalhesBilhete.querySelector('.text-center');
    const detalhesBilheteDiv = document.getElementById('detalhesBilhete');
    
    let bilheteAtualId = null;
    
    // Adicionar event listeners aos botões de visualização
    btnVerBilhetes.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const bilheteId = this.dataset.id;
            bilheteAtualId = bilheteId;
            
            // Configurar estado inicial do modal
            loadingIndicator.classList.remove('d-none');
            detalhesBilheteDiv.classList.add('d-none');
            detalhesBilheteDiv.innerHTML = '';
            
            // Configurar URL para impressão
            btnImprimirBilhete.href = '../imprimir-bilhete.php?id=' + bilheteId;
            
            // Abrir modal
            modalInstance.show();
            
            // Carregar detalhes do bilhete
            carregarDetalhesBilhete(bilheteId);
        });
    });
    
    /**
     * Carrega os detalhes do bilhete via AJAX
     * @param {number} bilheteId - ID do bilhete
     */
    function carregarDetalhesBilhete(bilheteId) {
        // Fazer requisição AJAX para obter detalhes do bilhete
        fetch('../api/get-bilhete-detalhes.php?id=' + bilheteId)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro ao carregar dados do bilhete');
                }
                return response.json();
            })
            .then(data => {
                // Ocultar indicador de carregamento
                loadingIndicator.classList.add('d-none');
                
                // Se os dados foram carregados com sucesso
                if (data.success) {
                    // Renderizar os detalhes do bilhete
                    renderizarDetalhesBilhete(data.bilhete);
                    detalhesBilheteDiv.classList.remove('d-none');
                } else {
                    // Exibir mensagem de erro
                    detalhesBilheteDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> ${data.message || 'Não foi possível carregar os detalhes do bilhete.'}
                        </div>
                    `;
                    detalhesBilheteDiv.classList.remove('d-none');
                }
            })
            .catch(error => {
                // Ocultar indicador de carregamento
                loadingIndicator.classList.add('d-none');
                
                // Exibir mensagem de erro
                detalhesBilheteDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> Erro ao carregar detalhes: ${error.message}
                    </div>
                `;
                detalhesBilheteDiv.classList.remove('d-none');
            });
    }
    
    /**
     * Renderiza os detalhes do bilhete no modal
     * @param {Object} bilhete - Dados do bilhete
     */
    function renderizarDetalhesBilhete(bilhete) {
        // Formatar data
        const dataCompra = new Date(bilhete.data_compra);
        const dataFormatada = dataCompra.toLocaleDateString('pt-BR') + ' ' + 
                             dataCompra.toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'});
        
        // Criar HTML para as dezenas
        let dezenasHtml = '';
        if (bilhete.dezenas) {
            const dezenas = bilhete.dezenas.split(',');
            dezenasHtml = '<div class="bilhete-dezenas mt-3">';
            dezenas.forEach(dezena => {
                dezenasHtml += `<span class="bilhete-dezena">${dezena.trim()}</span>`;
            });
            dezenasHtml += '</div>';
        }
        
        // Determinar classe e texto para o status
        let statusClass = 'bg-secondary';
        let statusText = bilhete.status;
        
        switch(bilhete.status) {
            case 'aguardando':
                statusClass = 'bg-info';
                statusText = 'Aguardando sorteio';
                break;
            case 'premiado':
                statusClass = 'bg-success';
                statusText = 'Premiado';
                break;
            case 'nao_premiado':
                statusClass = 'bg-danger';
                statusText = 'Não premiado';
                break;
        }
        
        // Renderizar template do bilhete
        detalhesBilheteDiv.innerHTML = `
            <div class="bilhete-header mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="bilhete-numero mb-1">
                            <span class="text-muted">Bilhete:</span> ${bilhete.numero}
                        </h4>
                        <p class="bilhete-concurso mb-0">
                            <span class="text-muted">Concurso:</span> ${bilhete.concurso_nome} (#${bilhete.concurso_numero})
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <span class="badge ${statusClass} bilhete-status">${statusText}</span>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="info-item mb-3">
                        <p class="info-label mb-1 text-muted">Data da Compra</p>
                        <p class="info-value mb-0 fw-bold">${dataFormatada}</p>
                    </div>
                    
                    <div class="info-item mb-3">
                        <p class="info-label mb-1 text-muted">Valor da Aposta</p>
                        <p class="info-value mb-0 fw-bold">R$ ${parseFloat(bilhete.valor).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</p>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="info-item mb-3">
                        <p class="info-label mb-1 text-muted">Valor do Prêmio</p>
                        <p class="info-value mb-0 fw-bold">
                            ${bilhete.valor_premio > 0 
                                ? 'R$ ' + parseFloat(bilhete.valor_premio).toLocaleString('pt-BR', {minimumFractionDigits: 2})
                                : '-'}
                        </p>
                    </div>
                    
                    <div class="info-item mb-3">
                        <p class="info-label mb-1 text-muted">Status de Pagamento</p>
                        <p class="info-value mb-0">
                            <span class="badge ${bilhete.status_pagamento === 'pago' ? 'bg-success' : 'bg-warning'}">
                                ${bilhete.status_pagamento === 'pago' ? 'Pago' : 'Aguardando Pagamento'}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <h5>Números Apostados</h5>
                ${dezenasHtml}
            </div>
            
            ${bilhete.jogos && bilhete.jogos.length > 0 ? `
                <div class="mt-4">
                    <h5>Jogos</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Jogo</th>
                                    <th>Números</th>
                                    <th>Resultado</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${bilhete.jogos.map(jogo => `
                                    <tr>
                                        <td>${jogo.tipo}</td>
                                        <td>${jogo.numeros}</td>
                                        <td>${jogo.resultado || '-'}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            ` : ''}
        `;
    }
}); 