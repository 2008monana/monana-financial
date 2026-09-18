/**
 * MonanaFinancial - Sistema de Notificações
 */

// ============================================
// SISTEMA DE NOTIFICAÇÕES TOAST
// ============================================
const Notificacao = {
    mostrar: function(tipo, titulo, mensagem, duracao = 4000) {
        const toastExistente = document.querySelector('.toast-notificacao');
        if (toastExistente) {
            toastExistente.classList.remove('mostrar');
            setTimeout(() => toastExistente.remove(), 400);
        }

        const toast = document.createElement('div');
        toast.className = `toast-notificacao toast-${tipo}`;
        
        const icones = {
            sucesso: 'fa-check-circle',
            erro: 'fa-times-circle',
            info: 'fa-info-circle',
            aviso: 'fa-exclamation-triangle'
        };
        
        toast.innerHTML = `
            <span class="icone-toast"><i class="fas ${icones[tipo] || icones.info}"></i></span>
            <div class="texto-toast">
                <span class="titulo-toast">${titulo}</span>
                <span class="mensagem-toast">${mensagem}</span>
            </div>
            <button class="fechar-toast"><i class="fas fa-times"></i></button>
        `;

        toast.querySelector('.fechar-toast').addEventListener('click', function() {
            toast.classList.remove('mostrar');
            setTimeout(() => toast.remove(), 400);
        });

        document.body.appendChild(toast);

        setTimeout(() => toast.classList.add('mostrar'), 50);

        if (duracao > 0) {
            setTimeout(() => {
                toast.classList.remove('mostrar');
                setTimeout(() => toast.remove(), 400);
            }, duracao);
        }

        return toast;
    },

    sucesso: function(titulo, mensagem, duracao = 4000) {
        return this.mostrar('sucesso', titulo, mensagem, duracao);
    },

    erro: function(titulo, mensagem, duracao = 5000) {
        return this.mostrar('erro', titulo, mensagem, duracao);
    },

    info: function(titulo, mensagem, duracao = 3000) {
        return this.mostrar('info', titulo, mensagem, duracao);
    },

    aviso: function(titulo, mensagem, duracao = 4000) {
        return this.mostrar('aviso', titulo, mensagem, duracao);
    }
};

// ============================================
// MODAL DE PROCESSAMENTO
// ============================================
const Processamento = {
    mostrar: function(mensagem = 'A processar, por favor aguarde...') {
        const modalExistente = document.querySelector('.modal-processamento');
        if (modalExistente) {
            modalExistente.remove();
        }

        const modal = document.createElement('div');
        modal.className = 'modal-processamento ativo';
        modal.id = 'modal-processamento';
        modal.innerHTML = `
            <div class="conteudo-modal">
                <span class="icone-status carregando">
                    <i class="fas fa-spinner"></i>
                </span>
                <h3 class="titulo-modal">A processar</h3>
                <p class="subtitulo-modal">${mensagem}</p>
                <div class="barra-progresso">
                    <div class="progresso"></div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        return modal;
    },

    fechar: function(status = 'sucesso', titulo = '', mensagem = '', duracao = 1500) {
        const modal = document.getElementById('modal-processamento');
        if (!modal) return;

        const icone = modal.querySelector('.icone-status');
        const tituloEl = modal.querySelector('.titulo-modal');
        const subtituloEl = modal.querySelector('.subtitulo-modal');
        const progresso = modal.querySelector('.barra-progresso');

        if (progresso) progresso.style.display = 'none';

        if (status === 'sucesso') {
            icone.className = 'icone-status sucesso';
            icone.innerHTML = '<i class="fas fa-check-circle"></i>';
            tituloEl.textContent = titulo || 'Sucesso!';
            subtituloEl.textContent = mensagem || 'Operação concluída com sucesso.';
            icone.style.animation = 'pulse 0.5s ease';
        } else if (status === 'erro') {
            icone.className = 'icone-status erro';
            icone.innerHTML = '<i class="fas fa-times-circle"></i>';
            tituloEl.textContent = titulo || 'Erro!';
            subtituloEl.textContent = mensagem || 'Ocorreu um erro na operação.';
        }

        setTimeout(() => {
            modal.classList.remove('ativo');
            setTimeout(() => modal.remove(), 400);
        }, duracao);
    },

    executar: async function(acao, opcoes = {}) {
        const {
            mensagemCarregando = 'A processar, por favor aguarde...',
            mensagemSucesso = 'Operação concluída com sucesso!',
            mensagemErro = 'Ocorreu um erro na operação.',
            tituloSucesso = 'Sucesso!',
            tituloErro = 'Erro!',
            duracaoSucesso = 1500,
            duracaoErro = 3000
        } = opcoes;

        this.mostrar(mensagemCarregando);

        try {
            const resultado = await acao();
            this.fechar('sucesso', tituloSucesso, mensagemSucesso, duracaoSucesso);
            return resultado;
        } catch (error) {
            console.error(error);
            this.fechar('erro', tituloErro, mensagemErro, duracaoErro);
            throw error;
        }
    }
};

window.Notificacao = Notificacao;
window.Processamento = Processamento;