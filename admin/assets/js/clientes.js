/**
 * Arquivo JavaScript para gerenciar as operações da página de clientes
 */

document.addEventListener('DOMContentLoaded', function() {
    // Elementos do DOM
    const formNovoCliente = document.getElementById('formNovoCliente');
    const formEditarCliente = document.getElementById('formEditarCliente');
    const btnSalvarNovoCliente = document.getElementById('salvarNovoCliente');
    const btnSalvarEdicaoCliente = document.getElementById('salvarEdicaoCliente');
    const btnConfirmarExclusao = document.getElementById('confirmarExclusao');
    const filtroTipo = document.getElementById('filtroTipo');
    const filtroStatus = document.getElementById('filtroStatus');
    const buscarCliente = document.getElementById('buscarCliente');
    
    // Gerar código do cliente ao abrir o modal de novo cliente
    const novoClienteModal = document.getElementById('novoClienteModal');
    novoClienteModal.addEventListener('show.bs.modal', function() {
        gerarCodigoCliente();
    });
    
    // Alternar rótulo CPF/CNPJ com base no tipo de pessoa
    const tipoSelects = document.querySelectorAll('#novoTipo, #editarTipo');
    tipoSelects.forEach(select => {
        select.addEventListener('change', function() {
            const isNovo = this.id === 'novoTipo';
            const labelEl = document.getElementById(isNovo ? 'labelCpfCnpj' : 'editarLabelCpfCnpj');
            const inputEl = document.getElementById(isNovo ? 'novoCpfCnpj' : 'editarCpfCnpj');
            
            if (this.value === 'pessoa_fisica') {
                labelEl.textContent = 'CPF';
                inputEl.placeholder = '000.000.000-00';
                inputEl.maxLength = 14;
            } else {
                labelEl.textContent = 'CNPJ';
                inputEl.placeholder = '00.000.000/0000-00';
                inputEl.maxLength = 18;
            }
        });
    });
    
    // Adicionar máscaras nos campos
    const cpfCnpjInputs = document.querySelectorAll('#novoCpfCnpj, #editarCpfCnpj');
    cpfCnpjInputs.forEach(input => {
        input.addEventListener('input', function() {
            const tipo = document.getElementById(this.id === 'novoCpfCnpj' ? 'novoTipo' : 'editarTipo').value;
            
            if (tipo === 'pessoa_fisica') {
                this.value = formatarCPF(this.value);
            } else {
                this.value = formatarCNPJ(this.value);
            }
        });
    });
    
    const telefoneInputs = document.querySelectorAll('input[name="telefone"]');
    telefoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = formatarTelefone(this.value);
        });
    });
    
    const cepInputs = document.querySelectorAll('#novoCep, #editarCep');
    cepInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = formatarCEP(this.value);
        });
        
        // Consultar CEP ao perder o foco
        input.addEventListener('blur', function() {
            if (this.value.length === 9) {
                consultarCEP(this.value, this.id === 'novoCep');
            }
        });
    });
    
    // Botão de salvar novo cliente
    btnSalvarNovoCliente.addEventListener('click', function() {
        if (validarFormulario(formNovoCliente)) {
            salvarCliente(formNovoCliente, true);
        }
    });
    
    // Botão de salvar edição de cliente
    btnSalvarEdicaoCliente.addEventListener('click', function() {
        if (validarFormulario(formEditarCliente)) {
            salvarCliente(formEditarCliente, false);
        }
    });
    
    // Carregar dados do cliente ao abrir modal de edição
    document.querySelectorAll('.btn-editar').forEach(btn => {
        btn.addEventListener('click', function() {
            const clienteId = this.getAttribute('data-id');
            carregarCliente(clienteId);
        });
    });
    
    // Abrir modal de confirmação ao clicar em excluir
    document.querySelectorAll('.btn-excluir').forEach(btn => {
        btn.addEventListener('click', function() {
            const clienteId = this.getAttribute('data-id');
            const clienteNome = this.getAttribute('data-nome');
            
            document.getElementById('idClienteExcluir').value = clienteId;
            document.getElementById('nomeClienteExcluir').textContent = clienteNome;
            
            // Abrir modal de confirmação
            new bootstrap.Modal(document.getElementById('confirmarExclusaoModal')).show();
        });
    });
    
    // Confirmar exclusão de cliente
    btnConfirmarExclusao.addEventListener('click', function() {
        const clienteId = document.getElementById('idClienteExcluir').value;
        excluirCliente(clienteId);
    });
    
    // Filtros da tabela
    filtroTipo.addEventListener('change', aplicarFiltros);
    filtroStatus.addEventListener('change', aplicarFiltros);
    buscarCliente.addEventListener('input', debounce(aplicarFiltros, 300));
    
    // Funções auxiliares
    
    /**
     * Gerar código automático para o cliente
     */
    function gerarCodigoCliente() {
        fetch('../ajax/clientes_ajax.php?acao=gerar_codigo')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('novoCodigo').value = data.codigo;
                } else {
                    console.error('Erro ao gerar código:', data.message);
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
            });
    }
    
    /**
     * Carregar dados do cliente para edição
     * @param {number} id ID do cliente
     */
    function carregarCliente(id) {
        fetch(`../ajax/clientes_ajax.php?acao=buscar&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const cliente = data.cliente;
                    // Preencher campos do formulário
                    document.getElementById('editarId').value = cliente.id;
                    document.getElementById('editarCodigo').value = cliente.codigo;
                    document.getElementById('editarNome').value = cliente.nome;
                    document.getElementById('editarTipo').value = cliente.tipo;
                    document.getElementById('editarCpfCnpj').value = cliente.cpf_cnpj;
                    document.getElementById('editarTelefone').value = cliente.telefone;
                    document.getElementById('editarEmail').value = cliente.email;
                    document.getElementById('editarCep').value = cliente.cep;
                    document.getElementById('editarEndereco').value = cliente.endereco;
                    document.getElementById('editarCidade').value = cliente.cidade;
                    document.getElementById('editarEstado').value = cliente.estado;
                    
                    // Ajustar label CPF/CNPJ com base no tipo
                    const label = document.getElementById('editarLabelCpfCnpj');
                    if (cliente.tipo === 'pessoa_fisica') {
                        label.textContent = 'CPF';
                    } else {
                        label.textContent = 'CNPJ';
                    }
                    
                    // Selecionar o status correto
                    if (cliente.status === 'ativo') {
                        document.getElementById('editarStatusAtivo').checked = true;
                    } else {
                        document.getElementById('editarStatusInativo').checked = true;
                    }
                } else {
                    exibirAlerta('Erro ao carregar dados do cliente', 'danger');
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                exibirAlerta('Erro ao carregar dados do cliente', 'danger');
            });
    }
    
    /**
     * Salvar cliente (novo ou edição)
     * @param {HTMLFormElement} form Formulário com os dados
     * @param {boolean} isNovo Indica se é um novo cliente
     */
    function salvarCliente(form, isNovo) {
        const formData = new FormData(form);
        const acao = isNovo ? 'adicionar' : 'atualizar';
        
        fetch('../ajax/clientes_ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                exibirAlerta(data.message, 'success');
                
                // Fechar modal
                const modalId = isNovo ? 'novoClienteModal' : 'editarClienteModal';
                const modalEl = document.getElementById(modalId);
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                
                // Recarregar página após breve delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                exibirAlerta(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            exibirAlerta('Erro ao salvar cliente', 'danger');
        });
    }
    
    /**
     * Excluir cliente
     * @param {number} id ID do cliente
     */
    function excluirCliente(id) {
        const formData = new FormData();
        formData.append('acao', 'excluir');
        formData.append('id', id);
        
        fetch('../ajax/clientes_ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                exibirAlerta(data.message, 'success');
                
                // Fechar modal
                const modalEl = document.getElementById('confirmarExclusaoModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                
                // Recarregar página após breve delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                exibirAlerta(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            exibirAlerta('Erro ao excluir cliente', 'danger');
        });
    }
    
    /**
     * Aplicar filtros à tabela de clientes
     */
    function aplicarFiltros() {
        const tipo = filtroTipo.value;
        const status = filtroStatus.value;
        const busca = buscarCliente.value;
        
        const formData = new FormData();
        formData.append('acao', 'filtrar');
        formData.append('tipo', tipo);
        formData.append('status', status);
        formData.append('busca', busca);
        
        fetch('../ajax/clientes_ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                atualizarTabela(data.clientes);
            } else {
                console.error('Erro ao aplicar filtros:', data.message);
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
        });
    }
    
    /**
     * Atualizar tabela com os clientes filtrados
     * @param {Array} clientes Lista de clientes
     */
    function atualizarTabela(clientes) {
        const tbody = document.getElementById('tabelaClientes');
        tbody.innerHTML = '';
        
        if (clientes.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td colspan="8" class="text-center py-3">
                    <p class="text-muted mb-0">Nenhum cliente encontrado com os filtros aplicados.</p>
                </td>
            `;
            tbody.appendChild(tr);
            return;
        }
        
        clientes.forEach(cliente => {
            const tr = document.createElement('tr');
            
            const tipoBadge = cliente.tipo === 'pessoa_fisica' 
                ? '<span class="badge bg-info">Pessoa Física</span>' 
                : '<span class="badge bg-warning">Pessoa Jurídica</span>';
                
            const statusBadge = cliente.status === 'ativo'
                ? '<span class="badge bg-success">Ativo</span>'
                : '<span class="badge bg-danger">Inativo</span>';
            
            tr.innerHTML = `
                <td>${cliente.codigo}</td>
                <td>${cliente.nome}</td>
                <td>${cliente.cpf_cnpj}</td>
                <td>${cliente.telefone}</td>
                <td>${cliente.email}</td>
                <td>${tipoBadge}</td>
                <td>${statusBadge}</td>
                <td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-primary btn-editar" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editarClienteModal" 
                                data-id="${cliente.id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-excluir" 
                                data-id="${cliente.id}"
                                data-nome="${cliente.nome}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            
            tbody.appendChild(tr);
        });
        
        // Reativar os eventos dos botões
        document.querySelectorAll('.btn-editar').forEach(btn => {
            btn.addEventListener('click', function() {
                const clienteId = this.getAttribute('data-id');
                carregarCliente(clienteId);
            });
        });
        
        document.querySelectorAll('.btn-excluir').forEach(btn => {
            btn.addEventListener('click', function() {
                const clienteId = this.getAttribute('data-id');
                const clienteNome = this.getAttribute('data-nome');
                
                document.getElementById('idClienteExcluir').value = clienteId;
                document.getElementById('nomeClienteExcluir').textContent = clienteNome;
                
                // Abrir modal de confirmação
                new bootstrap.Modal(document.getElementById('confirmarExclusaoModal')).show();
            });
        });
    }
    
    /**
     * Validar formulário antes de enviar
     * @param {HTMLFormElement} form Formulário a ser validado
     * @returns {boolean} Se o formulário é válido
     */
    function validarFormulario(form) {
        const nome = form.querySelector('input[name="nome"]').value.trim();
        
        if (nome === '') {
            exibirAlerta('O nome/razão social é obrigatório', 'danger');
            return false;
        }
        
        return true;
    }
    
    /**
     * Consultar CEP via API
     * @param {string} cep CEP a ser consultado
     * @param {boolean} isNovo Indica se é um novo cliente
     */
    function consultarCEP(cep, isNovo) {
        // Remover caracteres não numéricos
        cep = cep.replace(/\D/g, '');
        
        if (cep.length !== 8) return;
        
        // Exibir indicador de carregamento
        const prefix = isNovo ? '' : 'editar';
        document.getElementById(`${prefix}Endereco`).value = 'Carregando...';
        document.getElementById(`${prefix}Cidade`).value = '';
        document.getElementById(`${prefix}Estado`).value = '';
        
        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => response.json())
            .then(data => {
                if (!data.erro) {
                    document.getElementById(`${prefix}Endereco`).value = 
                        `${data.logradouro}${data.complemento ? ', ' + data.complemento : ''}`;
                    document.getElementById(`${prefix}Cidade`).value = data.localidade;
                    document.getElementById(`${prefix}Estado`).value = data.uf;
                } else {
                    document.getElementById(`${prefix}Endereco`).value = '';
                    exibirAlerta('CEP não encontrado', 'warning');
                }
            })
            .catch(error => {
                console.error('Erro na consulta de CEP:', error);
                document.getElementById(`${prefix}Endereco`).value = '';
                exibirAlerta('Erro ao consultar CEP', 'danger');
            });
    }
    
    // Funções de formatação
    
    /**
     * Formatar CPF
     * @param {string} cpf CPF a ser formatado
     * @returns {string} CPF formatado
     */
    function formatarCPF(cpf) {
        cpf = cpf.replace(/\D/g, '');
        if (cpf.length > 11) cpf = cpf.substring(0, 11);
        
        if (cpf.length > 9) {
            cpf = cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/, '$1.$2.$3-$4');
        } else if (cpf.length > 6) {
            cpf = cpf.replace(/(\d{3})(\d{3})(\d{1,3})/, '$1.$2.$3');
        } else if (cpf.length > 3) {
            cpf = cpf.replace(/(\d{3})(\d{1,3})/, '$1.$2');
        }
        
        return cpf;
    }
    
    /**
     * Formatar CNPJ
     * @param {string} cnpj CNPJ a ser formatado
     * @returns {string} CNPJ formatado
     */
    function formatarCNPJ(cnpj) {
        cnpj = cnpj.replace(/\D/g, '');
        if (cnpj.length > 14) cnpj = cnpj.substring(0, 14);
        
        if (cnpj.length > 12) {
            cnpj = cnpj.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{1,2})/, '$1.$2.$3/$4-$5');
        } else if (cnpj.length > 8) {
            cnpj = cnpj.replace(/(\d{2})(\d{3})(\d{3})(\d{1,4})/, '$1.$2.$3/$4');
        } else if (cnpj.length > 5) {
            cnpj = cnpj.replace(/(\d{2})(\d{3})(\d{1,3})/, '$1.$2.$3');
        } else if (cnpj.length > 2) {
            cnpj = cnpj.replace(/(\d{2})(\d{1,3})/, '$1.$2');
        }
        
        return cnpj;
    }
    
    /**
     * Formatar telefone
     * @param {string} telefone Telefone a ser formatado
     * @returns {string} Telefone formatado
     */
    function formatarTelefone(telefone) {
        telefone = telefone.replace(/\D/g, '');
        if (telefone.length > 11) telefone = telefone.substring(0, 11);
        
        if (telefone.length > 10) {
            telefone = telefone.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        } else if (telefone.length > 6) {
            telefone = telefone.replace(/(\d{2})(\d{4})(\d{1,4})/, '($1) $2-$3');
        } else if (telefone.length > 2) {
            telefone = telefone.replace(/(\d{2})(\d{1,4})/, '($1) $2');
        }
        
        return telefone;
    }
    
    /**
     * Formatar CEP
     * @param {string} cep CEP a ser formatado
     * @returns {string} CEP formatado
     */
    function formatarCEP(cep) {
        cep = cep.replace(/\D/g, '');
        if (cep.length > 8) cep = cep.substring(0, 8);
        
        if (cep.length > 5) {
            cep = cep.replace(/(\d{5})(\d{1,3})/, '$1-$2');
        }
        
        return cep;
    }
    
    /**
     * Exibir alerta na tela
     * @param {string} mensagem Mensagem a ser exibida
     * @param {string} tipo Tipo do alerta (success, danger, warning, info)
     */
    function exibirAlerta(mensagem, tipo) {
        const alertaDiv = document.createElement('div');
        alertaDiv.className = `alert alert-${tipo} alert-dismissible fade show fixed-top mx-auto mt-3`;
        alertaDiv.style.maxWidth = '500px';
        alertaDiv.style.zIndex = '9999';
        
        alertaDiv.innerHTML = `
            ${mensagem}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        `;
        
        document.body.appendChild(alertaDiv);
        
        // Remover alerta após 5 segundos
        setTimeout(() => {
            if (alertaDiv.parentNode) {
                alertaDiv.parentNode.removeChild(alertaDiv);
            }
        }, 5000);
    }
    
    /**
     * Função debounce para controlar a frequência de chamadas
     * @param {Function} func Função a ser executada
     * @param {number} wait Tempo de espera em ms
     * @returns {Function} Função com debounce
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}); 