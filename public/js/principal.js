/**
 * MonanaFinancial - JavaScript Principal
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('MonanaFinancial carregado com sucesso!');
    
    // Inicializar tooltips
    initTooltips();
    
    // Inicializar máscaras de input
    initMasks();
});

/**
 * Toggle para mostrar/ocultar senha
 */
function toggleSenha() {
    const input = document.getElementById('senha');
    const icon = document.querySelector('.toggle-senha i');
    
    if (!input) return;
    
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
            icon.className = 'fas fa-eye-slash';
        }
    } else {
        input.type = 'password';
        if (icon) {
            icon.className = 'fas fa-eye';
        }
    }
}

/**
 * Inicializar tooltips
 */
function initTooltips() {
    document.querySelectorAll('[data-tooltip]').forEach(function(el) {
        el.addEventListener('mouseenter', function(e) {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip-custom';
            tooltip.textContent = this.dataset.tooltip;
            tooltip.style.cssText = `
                position: fixed;
                background: #0a1930;
                color: #fff;
                padding: 6px 12px;
                border-radius: 6px;
                font-size: 12px;
                z-index: 9999;
                pointer-events: none;
                max-width: 250px;
                text-align: center;
                font-family: 'Inter', sans-serif;
            `;
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.bottom + 6 + 'px';
            
            this._tooltip = tooltip;
        });
        
        el.addEventListener('mouseleave', function() {
            if (this._tooltip) {
                this._tooltip.remove();
                delete this._tooltip;
            }
        });
    });
}

/**
 * Inicializar máscaras de input
 */
function initMasks() {
    // Máscara de moeda
    document.querySelectorAll('.currency-input').forEach(function(input) {
        input.addEventListener('input', function() {
            let value = this.value.replace(/[^0-9,]/g, '');
            if (value) {
                let parts = value.split(',');
                let integer = parts[0];
                let decimal = parts[1] || '';
                
                integer = integer.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                
                if (decimal) {
                    this.value = integer + ',' + decimal.substring(0, 2);
                } else {
                    this.value = integer;
                }
            }
        });
    });
    
    // Máscara de telefone
    document.querySelectorAll('.phone-input').forEach(function(input) {
        input.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length <= 9) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})/, '$1 $2 $3');
            } else {
                value = '+244 ' + value.replace(/(\d{3})(\d{3})(\d{3})(\d{3})/, '$1 $2 $3 $4');
            }
            this.value = value.trim();
        });
    });
}