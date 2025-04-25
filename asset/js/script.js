/**
 * Bolão Sistema - Scripts Principais
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializa os componentes da página
    initConsultaBilhete();
    initTabelas();
});

/**
 * Inicializa o formulário de consulta de bilhete
 */
function initConsultaBilhete() {
    const formConsulta = document.getElementById('consulta-form');
    if (!formConsulta) return;

    formConsulta.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const numeroBilhete = document.getElementById('numero-bilhete').value.trim();
        if (!numeroBilhete) {
            showAlert('Por favor, informe o número do bilhete', 'danger');
            return;
        }
        
        // Redireciona para a página de detalhes do bilhete
        window.location.href = 'consulta_bilhete.php?bilhete=' + numeroBilhete;
    });
}

/**
 * Inicializa as tabelas responsivas
 */
function initTabelas() {
    // Adiciona a classe de tabela responsiva em todas as tabelas
    const tabelas = document.querySelectorAll('table');
    tabelas.forEach(tabela => {
        const wrapper = document.createElement('div');
        wrapper.classList.add('table-container');
        
        // Insere o wrapper antes da tabela
        tabela.parentNode.insertBefore(wrapper, tabela);
        
        // Move a tabela para dentro do wrapper
        wrapper.appendChild(tabela);
    });
}

/**
 * Exibe uma mensagem de alerta
 * 
 * @param {string} mensagem - A mensagem a ser exibida
 * @param {string} tipo - O tipo de alerta (success, danger, info)
 */
function showAlert(mensagem, tipo = 'info') {
    // Verifica se já existe um alerta
    const alertaExistente = document.querySelector('.alert');
    if (alertaExistente) {
        alertaExistente.remove();
    }
    
    // Cria o elemento de alerta
    const alerta = document.createElement('div');
    alerta.classList.add('alert', `alert-${tipo}`);
    alerta.textContent = mensagem;
    
    // Adiciona o alerta no início do conteúdo
    const conteudo = document.querySelector('.container');
    conteudo.insertBefore(alerta, conteudo.firstChild);
    
    // Remove o alerta após 5 segundos
    setTimeout(() => {
        alerta.remove();
    }, 5000);
}

/**
 * Função para realizar requisições AJAX
 * 
 * @param {string} url - URL para a requisição
 * @param {object} dados - Dados a serem enviados
 * @param {function} callback - Função de callback para o sucesso
 * @param {string} metodo - Método HTTP (GET, POST)
 */
function ajaxRequest(url, dados, callback, metodo = 'POST') {
    // Exibe o loader
    showLoader(true);
    
    // Cria o objeto FormData se os dados forem fornecidos
    const formData = new FormData();
    if (dados) {
        Object.keys(dados).forEach(key => {
            formData.append(key, dados[key]);
        });
    }
    
    // Configura a requisição
    const options = {
        method: metodo,
        credentials: 'same-origin'
    };
    
    // Adiciona os dados ao corpo da requisição para POST
    if (metodo === 'POST') {
        options.body = formData;
    }
    
    // Realiza a requisição fetch
    fetch(url, options)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na requisição: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            // Esconde o loader
            showLoader(false);
            
            // Chama o callback com os dados
            if (callback) {
                callback(data);
            }
        })
        .catch(error => {
            // Esconde o loader
            showLoader(false);
            
            // Exibe mensagem de erro
            console.error('Erro na requisição:', error);
            showAlert('Erro ao processar a solicitação. Por favor, tente novamente.', 'danger');
        });
}

/**
 * Exibe ou esconde o loader
 * 
 * @param {boolean} mostrar - True para mostrar, False para esconder
 */
function showLoader(mostrar) {
    // Verifica se o loader já existe
    let loader = document.querySelector('.loader-container');
    
    if (mostrar) {
        // Se não existir, cria o loader
        if (!loader) {
            loader = document.createElement('div');
            loader.classList.add('loader-container');
            
            const spinnerElement = document.createElement('div');
            spinnerElement.classList.add('loader');
            
            loader.appendChild(spinnerElement);
            document.body.appendChild(loader);
        }
        
        // Exibe o loader
        loader.style.display = 'flex';
    } else if (loader) {
        // Esconde o loader se existir
        loader.style.display = 'none';
    }
}

/**
 * Formata um valor para o formato de moeda
 * 
 * @param {number} valor - Valor a ser formatado
 * @return {string} Valor formatado
 */
function formatarDinheiro(valor) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(valor);
}

/**
 * Verifica se um número foi sorteado
 * 
 * @param {number} numero - Número a verificar
 * @param {array} numerosSorteados - Array com os números sorteados
 * @return {boolean} True se o número foi sorteado, False caso contrário
 */
function numeroFoiSorteado(numero, numerosSorteados) {
    return numerosSorteados.includes(parseInt(numero));
}

/**
 * Inicializa a visualização dos detalhes do bilhete
 * 
 * @param {array} numerosSorteados - Array com os números sorteados no concurso
 */
function initDetalheBilhete(numerosSorteados) {
    if (!numerosSorteados || !numerosSorteados.length) return;
    
    // Marca os números sorteados nos jogos do bilhete
    const numerosJogo = document.querySelectorAll('.jogo-numero');
    numerosJogo.forEach(elemento => {
        const numero = parseInt(elemento.textContent.trim());
        if (numeroFoiSorteado(numero, numerosSorteados)) {
            elemento.classList.add('numero-acertado');
        }
    });
} 