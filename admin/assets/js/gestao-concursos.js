/**
 * Gestão de Concursos
 * Sistema de Bolão
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar os eventos
    initNovoConcurso();
    initRealizarSorteio();
    initCancelarConcurso();
    initConfigurarPremios();
    
    // Inicializa tooltips e outros componentes
    inicializarComponentes();
});

/**
 * Inicializa componentes visuais
 */
function inicializarComponentes() {
    // Inicializar tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });
    
    // Inicializar ordenação de tabela
    if (document.querySelector('.dataTable')) {
        new DataTable('.dataTable', {
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json'
            }
        });
    }
}

/**
 * Inicializa evento de criação de novo concurso
 */
function initNovoConcurso() {
    const btnNovoConcurso = document.getElementById('btnConfirmarNovoConcurso');
    const formNovoConcurso = document.getElementById('formNovoConcurso');
    
    if (btnNovoConcurso && formNovoConcurso) {
        btnNovoConcurso.addEventListener('click', function() {
            // Validar formulário
            if (!formNovoConcurso.checkValidity()) {
                formNovoConcurso.reportValidity();
                return;
            }
            
            // Mostrar confirmação
            if (confirm('Confirma a criação deste novo concurso?')) {
                // Exibir spinner
                btnNovoConcurso.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando...';
                btnNovoConcurso.disabled = true;
                
                // Enviar formulário
                formNovoConcurso.submit();
            }
        });
    }
}

/**
 * Inicializa evento para realizar sorteio
 */
function initRealizarSorteio() {
    const btnRealizarSorteio = document.querySelectorAll('.btn-realizar-sorteio');
    const btnConfirmarSorteio = document.getElementById('btnConfirmarSorteio');
    const formRealizarSorteio = document.getElementById('formRealizarSorteio');
    const metodoSorteio = document.querySelector('select[name="metodo_sorteio"]');
    const camposSorteioManual = document.getElementById('camposSorteioManual');

    // Botões para abrir modal
    if (btnRealizarSorteio.length > 0) {
        btnRealizarSorteio.forEach(btn => {
            btn.addEventListener('click', function() {
                const concursoId = this.getAttribute('data-id');
                document.getElementById('sorteioConcursoId').value = concursoId;
                
                // Abrir modal
                const modal = new bootstrap.Modal(document.getElementById('realizarSorteioModal'));
                modal.show();
            });
        });
    }
    
    // Alternar campos com base no método de sorteio
    if (metodoSorteio && camposSorteioManual) {
        metodoSorteio.addEventListener('change', function() {
            if (this.value === 'manual') {
                camposSorteioManual.style.display = 'block';
            } else {
                camposSorteioManual.style.display = 'none';
            }
        });
    }
    
    // Confirmar sorteio
    if (btnConfirmarSorteio && formRealizarSorteio) {
        btnConfirmarSorteio.addEventListener('click', function() {
            // Validar formulário
            if (!formRealizarSorteio.checkValidity()) {
                formRealizarSorteio.reportValidity();
                return;
            }
            
            // Confirmação adicional
            if (confirm('ATENÇÃO: Esta ação irá encerrar as vendas e realizar o sorteio. Esta operação não pode ser desfeita. Deseja continuar?')) {
                // Exibir spinner
                btnConfirmarSorteio.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando...';
                btnConfirmarSorteio.disabled = true;
                
                // Enviar formulário
                formRealizarSorteio.submit();
            }
        });
    }
}

/**
 * Inicializa evento para cancelar concurso
 */
function initCancelarConcurso() {
    const btnCancelarConcurso = document.querySelectorAll('.btn-cancelar-concurso');
    const btnConfirmarCancelamento = document.getElementById('btnConfirmarCancelamento');
    const formCancelarConcurso = document.getElementById('formCancelarConcurso');

    // Botões para abrir modal
    if (btnCancelarConcurso.length > 0) {
        btnCancelarConcurso.forEach(btn => {
            btn.addEventListener('click', function() {
                const concursoId = this.getAttribute('data-id');
                document.getElementById('cancelarConcursoId').value = concursoId;
                
                // Abrir modal
                const modal = new bootstrap.Modal(document.getElementById('cancelarConcursoModal'));
                modal.show();
            });
        });
    }
    
    // Confirmar cancelamento
    if (btnConfirmarCancelamento && formCancelarConcurso) {
        btnConfirmarCancelamento.addEventListener('click', function() {
            // Validar formulário
            if (!formCancelarConcurso.checkValidity()) {
                formCancelarConcurso.reportValidity();
                return;
            }
            
            // Verificar se tem motivo preenchido
            const motivo = formCancelarConcurso.querySelector('textarea[name="motivo"]').value.trim();
            if (!motivo) {
                alert('Por favor, informe o motivo do cancelamento.');
                return;
            }
            
            // Confirmação adicional
            if (confirm('ATENÇÃO: Esta ação irá CANCELAR o concurso. Todos os bilhetes vendidos serão cancelados. Esta operação não pode ser desfeita. Deseja continuar?')) {
                // Exibir spinner
                btnConfirmarCancelamento.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando...';
                btnConfirmarCancelamento.disabled = true;
                
                // Preparar dados para envio
                const concursoId = document.getElementById('cancelarConcursoId').value;
                const observacao = formCancelarConcurso.querySelector('textarea[name="motivo"]').value;
                
                // Enviar via AJAX em vez do formulário
                fetch('../actions/cancelar_concurso.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${encodeURIComponent(concursoId)}&observacao=${encodeURIComponent(observacao)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        // Exibir mensagem de sucesso
                        alert(data.mensagem);
                        
                        // Fechar modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('cancelarConcursoModal'));
                        modal.hide();
                        
                        // Recarregar a página para atualizar a lista
                        window.location.reload();
                    } else {
                        throw new Error(data.erro || 'Erro ao cancelar concurso');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Falha ao cancelar concurso: ' + error.message);
                })
                .finally(() => {
                    // Restaurar botão
                    btnConfirmarCancelamento.innerHTML = 'Confirmar Cancelamento';
                    btnConfirmarCancelamento.disabled = false;
                });
            }
        });
    }
}

/**
 * Inicializa eventos para configurar prêmios
 */
function initConfigurarPremios() {
    const btnAdicionarPremio = document.getElementById('btnAdicionarPremio');
    const premiosAdicionais = document.getElementById('premiosAdicionais');
    const btnSalvarPremios = document.getElementById('btnSalvarPremios');
    const formConfigurarPremios = document.getElementById('formConfigurarPremios');
    
    // Adicionar novo prêmio
    if (btnAdicionarPremio && premiosAdicionais) {
        let premioCount = 1;
        
        btnAdicionarPremio.addEventListener('click', function() {
            premioCount++;
            const novoPremioDiv = document.createElement('div');
            novoPremioDiv.className = 'premio-item row mb-3';
            novoPremioDiv.innerHTML = `
                <div class="col-md-3">
                    <label class="form-label">${premioCount}º Prêmio</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="number" class="form-control" name="premio_valor[]" min="0" step="0.01" required>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Quantidade</label>
                    <input type="number" class="form-control" name="premio_quantidade[]" min="1" value="1" required>
                </div>
                
                <div class="col-md-5">
                    <label class="form-label">Descrição</label>
                    <input type="text" class="form-control" name="premio_descricao[]" required>
                </div>
                
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-danger mb-2 btn-remover-premio">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            
            premiosAdicionais.appendChild(novoPremioDiv);
            
            // Adicionar evento para remover prêmio
            const btnRemover = novoPremioDiv.querySelector('.btn-remover-premio');
            if (btnRemover) {
                btnRemover.addEventListener('click', function() {
                    premiosAdicionais.removeChild(novoPremioDiv);
                });
            }
        });
    }
    
    // Salvar configuração de prêmios
    if (btnSalvarPremios && formConfigurarPremios) {
        btnSalvarPremios.addEventListener('click', function() {
            // Validar formulário
            if (!formConfigurarPremios.checkValidity()) {
                formConfigurarPremios.reportValidity();
                return;
            }
            
            // Mostrar confirmação
            if (confirm('Confirma a configuração de prêmios para este concurso?')) {
                // Exibir spinner
                btnSalvarPremios.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvando...';
                btnSalvarPremios.disabled = true;
                
                // Enviar formulário
                formConfigurarPremios.submit();
            }
        });
    }
}

/**
 * Exibe detalhes de um concurso
 */
function exibirDetalhesConcurso(concursoId) {
    // Redirecionar para a página de detalhes
    window.location.href = `detalhes-concurso.php?id=${concursoId}`;
}

/**
 * Formata um valor monetário
 */
function formatarMoeda(valor) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(valor);
}

/**
 * Formata uma data
 */
function formatarData(data) {
    if (!data) return '';
    const date = new Date(data);
    return date.toLocaleDateString('pt-BR');
} 