/**
 * Admin Panel JavaScript
 * Sistema de Bolão
 */

document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileToggle = document.getElementById('mobileToggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
            // Salvar preferência do usuário
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebar_collapsed', isCollapsed ? 'true' : 'false');
        });
    }
    
    // Mobile menu toggle
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('mobile-open');
            
            // Adicionar overlay se o menu estiver aberto no mobile
            if (sidebar.classList.contains('mobile-open')) {
                const overlay = document.createElement('div');
                overlay.className = 'sidebar-overlay';
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('mobile-open');
                    this.remove();
                });
                document.body.appendChild(overlay);
            } else {
                const overlay = document.querySelector('.sidebar-overlay');
                if (overlay) overlay.remove();
            }
        });
    }
    
    // Restaurar estado do menu a partir do localStorage
    const savedCollapsed = localStorage.getItem('sidebar_collapsed');
    if (savedCollapsed === 'true') {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('expanded');
    }
    
    // Dropdown toggles
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const dropdown = this.closest('.dropdown');
            
            // Fechar outros dropdowns
            dropdownToggles.forEach(otherToggle => {
                const otherDropdown = otherToggle.closest('.dropdown');
                if (otherDropdown !== dropdown) {
                    otherDropdown.classList.remove('open');
                }
            });
            
            // Toggle do dropdown atual
            dropdown.classList.toggle('open');
        });
    });
    
    // Fechar menu mobile ao clicar em links da navegação
    const navLinks = document.querySelectorAll('.nav-item a');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 992) {
                sidebar.classList.remove('mobile-open');
                document.body.classList.remove('sidebar-mobile-open');
            }
        });
    });
    
    // Adicionar classe ativa ao item de menu atual
    const currentPath = window.location.pathname;
    const menuItems = document.querySelectorAll('.nav-item a');
    
    menuItems.forEach(item => {
        const href = item.getAttribute('href');
        if (href && currentPath.includes(href) && href !== '/') {
            item.closest('.nav-item').classList.add('active');
        }
    });
    
    // Verificar tamanho da tela para auto-colapsar em telas pequenas
    function checkScreenSize() {
        if (window.innerWidth < 992) {
            sidebar.classList.add('collapsed');
            if (mainContent) {
                mainContent.classList.add('expanded');
            }
        } else if (savedCollapsed !== 'true') {
            sidebar.classList.remove('collapsed');
            if (mainContent) {
                mainContent.classList.remove('expanded');
            }
        }
    }
    
    // Verificar ao carregar e ao redimensionar
    window.addEventListener('resize', checkScreenSize);
    checkScreenSize();
    
    // Inicializar tooltips (se você estiver usando Bootstrap)
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Inicializar gráficos (se você estiver usando Chart.js)
    initCharts();
    
    // Efeito de hover nos itens do menu
    const navItems = document.querySelectorAll('.nav-item a');
    
    navItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            if (!sidebar.classList.contains('collapsed')) {
                this.style.transform = 'translateX(3px)';
            }
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
    
    // Corrigir altura da área de conteúdo em dispositivos móveis
    function adjustContentHeight() {
        const windowHeight = window.innerHeight;
        const headerHeight = document.querySelector('.main-header')?.offsetHeight || 60;
        const contentArea = document.querySelector('.content');
        
        if (contentArea) {
            contentArea.style.minHeight = `${windowHeight - headerHeight - 40}px`;
        }
    }
    
    // Ajustar altura inicial e ao redimensionar
    adjustContentHeight();
    window.addEventListener('resize', adjustContentHeight);
});

/**
 * Inicializa os gráficos do dashboard (requer Chart.js)
 */
function initCharts() {
    // Verifica se Chart.js está disponível
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js não está disponível');
        return;
    }
    
    // Configuração de cores
    const chartColors = {
        primary: '#3498db',
        secondary: '#2ecc71',
        warning: '#f39c12',
        danger: '#e74c3c',
        dark: '#343a40',
        light: '#f8f9fa',
        grey: '#6c757d'
    };
    
    // Configurações globais do Chart.js
    Chart.defaults.font.family = "'Roboto', 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#6c757d';
    
    // Gráfico de Vendas
    const salesChartEl = document.getElementById('salesChart');
    if (salesChartEl) {
        const salesData = {
            labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            datasets: [{
                label: 'Vendas',
                data: [15, 20, 35, 45, 38, 55, 62, 68, 59, 72, 80, 95],
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                borderColor: chartColors.primary,
                borderWidth: 2,
                tension: 0.3,
                pointBackgroundColor: '#fff',
                pointBorderColor: chartColors.primary,
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        };
        
        new Chart(salesChartEl, {
            type: 'line',
            data: salesData,
            options: {
                responsive: false,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                }
            }
        });
    }
    
    // Gráfico de Usuários Ativos
    const usersChartEl = document.getElementById('usersChart');
    if (usersChartEl) {
        const usersData = {
            labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
            datasets: [{
                label: 'Novos usuários',
                data: [12, 19, 15, 28, 32, 25],
                backgroundColor: chartColors.secondary,
                borderRadius: 4
            }]
        };
        
        new Chart(usersChartEl, {
            type: 'bar',
            data: usersData,
            options: {
                responsive: false,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                }
            }
        });
    }
    
    // Gráfico de Distribuição
    const distributionChartEl = document.getElementById('distributionChart');
    if (distributionChartEl) {
        const distributionData = {
            labels: ['Bolão da Copa', 'Bolão do Brasileirão', 'Bolão da Loteria', 'Outros'],
            datasets: [{
                data: [42, 28, 18, 12],
                backgroundColor: [
                    chartColors.primary,
                    chartColors.secondary,
                    chartColors.warning,
                    chartColors.grey
                ],
                borderWidth: 0
            }]
        };
        
        new Chart(distributionChartEl, {
            type: 'doughnut',
            data: distributionData,
            options: {
                responsive: false,
                maintainAspectRatio: true,
                cutout: '75%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    }
                }
            }
        });
    }
}

/**
 * admin.js - JavaScript para o painel administrativo
 * 
 * Este arquivo contém funções comuns utilizadas no painel admin
 */

// Função para confirmar ações destrutivas (delete, etc)
function confirmarAcao(mensagem) {
    return confirm(mensagem || 'Tem certeza que deseja realizar esta ação?');
}

// Formatar valores monetários
function formatarMoeda(valor) {
    return parseFloat(valor).toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    });
}

// Formatar data no padrão brasileiro
function formatarData(data) {
    if (!data) return '';
    
    const dataObj = new Date(data);
    return dataObj.toLocaleDateString('pt-BR');
}

// Função para inicializar tooltips do Bootstrap
function inicializarTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Função para mostrar mensagens de alerta
function mostrarAlerta(mensagem, tipo = 'success', duracao = 3000) {
    // Criar elemento de alerta
    const alerta = document.createElement('div');
    alerta.className = `alert alert-${tipo} alert-dismissible fade show position-fixed`;
    alerta.style.top = '20px';
    alerta.style.right = '20px';
    alerta.style.zIndex = '9999';
    
    // Conteúdo do alerta
    alerta.innerHTML = `
        ${mensagem}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    `;
    
    // Adicionar ao body
    document.body.appendChild(alerta);
    
    // Inicializar o alerta Bootstrap
    const bsAlerta = new bootstrap.Alert(alerta);
    
    // Remover após o tempo especificado
    setTimeout(() => {
        bsAlerta.close();
    }, duracao);
}

// Função para atualizar contador de notificações
function atualizarContadorNotificacoes(quantidade) {
    const badge = document.querySelector('.notifications .badge');
    if (badge) {
        badge.textContent = quantidade;
        badge.style.display = quantidade > 0 ? 'inline-flex' : 'none';
    }
}

// Função para mostrar spinner de carregamento
function mostrarSpinner(elementoId) {
    const elemento = document.getElementById(elementoId);
    if (elemento) {
        elemento.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Carregando...</span></div>';
    }
}

// Função para esconder spinner de carregamento
function esconderSpinner(elementoId, conteudoOriginal = '') {
    const elemento = document.getElementById(elementoId);
    if (elemento) {
        elemento.innerHTML = conteudoOriginal;
    }
}

// Inicializar elementos comuns quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips
    inicializarTooltips();
    
    // Adicionar listener para formulários com confirmação
    const formsConfirmacao = document.querySelectorAll('form[data-confirmar="true"]');
    formsConfirmacao.forEach(form => {
        form.addEventListener('submit', function(e) {
            const mensagem = this.getAttribute('data-mensagem') || 'Tem certeza que deseja enviar este formulário?';
            if (!confirmarAcao(mensagem)) {
                e.preventDefault();
            }
        });
    });
    
    // Adicionar listener para botões de exclusão
    const botoesExcluir = document.querySelectorAll('.btn-excluir');
    botoesExcluir.forEach(botao => {
        botao.addEventListener('click', function(e) {
            const mensagem = this.getAttribute('data-mensagem') || 'Tem certeza que deseja excluir este item?';
            if (!confirmarAcao(mensagem)) {
                e.preventDefault();
            }
        });
    });
});

// Função para alternar o tema claro/escuro
function alternarTema() {
    document.body.classList.toggle('dark-mode');
    
    // Salvar preferência do usuário
    const temaDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('tema-dark', temaDark ? 'true' : 'false');
}

// Verificar e aplicar o tema salvo
function aplicarTemaSalvo() {
    const temaDark = localStorage.getItem('tema-dark') === 'true';
    if (temaDark) {
        document.body.classList.add('dark-mode');
    } else {
        document.body.classList.remove('dark-mode');
    }
}

// Aplicar tema salvo quando a página carrega
document.addEventListener('DOMContentLoaded', aplicarTemaSalvo); 