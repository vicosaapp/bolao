/**
 * Script para a página de perfil
 */
document.addEventListener('DOMContentLoaded', function() {
    // Remover alertas automaticamente após 5 segundos
    const alertas = document.querySelectorAll('.alert');
    alertas.forEach(function(alerta) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alerta);
            bsAlert.close();
        }, 5000);
    });

    // Máscara de telefone
    const telefoneInput = document.getElementById('telefone');
    if (telefoneInput) {
        telefoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length > 11) {
                value = value.slice(0, 11);
            }
            
            if (value.length <= 10) {
                value = value.replace(/^(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3');
            } else {
                value = value.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
            }
            
            e.target.value = value;
        });
    }

    // Validação de senha em tempo real
    const novaSenha = document.getElementById('nova_senha');
    const confirmarSenha = document.getElementById('confirmar_senha');
    
    if (novaSenha && confirmarSenha) {
        // Verificar senha ao digitar
        confirmarSenha.addEventListener('input', function() {
            if (novaSenha.value !== confirmarSenha.value) {
                confirmarSenha.setCustomValidity('As senhas não coincidem');
            } else {
                confirmarSenha.setCustomValidity('');
            }
        });

        // Garantir que a validação seja atualizada se a nova senha for alterada
        novaSenha.addEventListener('input', function() {
            if (confirmarSenha.value) {
                if (novaSenha.value !== confirmarSenha.value) {
                    confirmarSenha.setCustomValidity('As senhas não coincidem');
                } else {
                    confirmarSenha.setCustomValidity('');
                }
            }
        });
    }
}); 