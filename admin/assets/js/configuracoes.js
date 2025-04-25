/**
 * Script para gerenciar configurações do sistema
 */
document.addEventListener('DOMContentLoaded', function() {
    inicializarComponentes();
});

/**
 * Inicializar todos os componentes e eventos
 */
function inicializarComponentes() {
    // Inicializar os tabs
    initTabs();
    
    // Inicializar preview de cores
    initCorPreview();
    
    // Inicializar formulário
    initFormulario();
    
    // Inicializar tooltips
    initTooltips();
}

/**
 * Inicializar navegação por tabs
 */
function initTabs() {
    // Guardar o tab ativo no localStorage
    const tabLinks = document.querySelectorAll('.nav-link[data-bs-toggle="pill"]');
    if (tabLinks.length > 0) {
        tabLinks.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function (event) {
                localStorage.setItem('configuracoes_tab_ativo', event.target.id);
            });
        });
        
        // Recuperar e ativar o último tab
        const activeTab = localStorage.getItem('configuracoes_tab_ativo');
        if (activeTab) {
            const tabToActivate = document.getElementById(activeTab);
            if (tabToActivate) {
                const tab = new bootstrap.Tab(tabToActivate);
                tab.show();
            }
        }
    }
}

/**
 * Inicializar preview de cores
 */
function initCorPreview() {
    // Atualizar preview de cores quando o valor mudar
    const corInputs = document.querySelectorAll('input[type="color"]');
    if (corInputs.length > 0) {
        corInputs.forEach(input => {
            // Configurar preview inicial
            const previewElement = input.previousElementSibling;
            if (previewElement && previewElement.classList.contains('cor-preview')) {
                previewElement.style.backgroundColor = input.value;
            }
            
            // Atualizar preview quando a cor mudar
            input.addEventListener('input', function() {
                if (previewElement) {
                    previewElement.style.backgroundColor = this.value;
                }
            });
        });
    }
}

/**
 * Inicializar o formulário
 */
function initFormulario() {
    const formConfiguracoes = document.getElementById('formConfiguracoes');
    
    if (formConfiguracoes) {
        formConfiguracoes.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Desabilitar botão de salvar e mostrar loader
            const btnSalvar = document.querySelector('button[type="submit"]');
            if (btnSalvar) {
                btnSalvar.disabled = true;
                btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvando...';
            }
            
            // Coletar todos os dados do formulário
            const formData = new FormData(this);
            
            // Processar checkboxes (que não são enviados quando não marcados)
            document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                if (!checkbox.checked) {
                    formData.append(checkbox.name, 'false');
                } else {
                    formData.set(checkbox.name, 'true');
                }
            });
            
            // Enviar dados via AJAX
            fetch('../actions/salvar_configuracoes.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    exibirAlerta('success', data.mensagem || 'Configurações salvas com sucesso!');
                } else {
                    throw new Error(data.erro || 'Erro ao salvar configurações');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                exibirAlerta('danger', 'Erro ao salvar configurações: ' + error.message);
            })
            .finally(() => {
                // Restaurar botão
                if (btnSalvar) {
                    btnSalvar.disabled = false;
                    btnSalvar.innerHTML = 'Salvar Configurações';
                }
            });
        });
    }
}

/**
 * Inicializar tooltips
 */
function initTooltips() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
}

/**
 * Exibir alerta na página
 * @param {string} tipo Tipo do alerta (success, danger, warning, info)
 * @param {string} mensagem Mensagem a ser exibida
 * @param {number} duracao Duração em ms (padrão: 5000)
 */
function exibirAlerta(tipo, mensagem, duracao = 5000) {
    const alertPlaceholder = document.getElementById('alertPlaceholder');
    
    if (alertPlaceholder) {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = [
            `<div class="alert alert-${tipo} alert-dismissible fade show" role="alert">`,
            `   <i class="fas fa-${tipo === 'success' ? 'check-circle' : tipo === 'danger' ? 'exclamation-circle' : 'info-circle'}"></i> ${mensagem}`,
            '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>',
            '</div>'
        ].join('');
        
        alertPlaceholder.append(wrapper);
        
        // Auto-remover após a duração
        if (duracao > 0) {
            setTimeout(() => {
                const alert = bootstrap.Alert.getOrCreateInstance(wrapper.querySelector('.alert'));
                alert.close();
            }, duracao);
        }
    }
} 