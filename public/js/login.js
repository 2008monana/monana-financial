document.addEventListener('DOMContentLoaded', function () {
  const form            = document.getElementById('form-login');
  const btnSubmit       = document.getElementById('btn-submit');
  const btnTogglePass   = document.getElementById('btn-toggle-pass');
  const campoSenha      = document.getElementById('senha');

  const modal           = document.getElementById('modal-login');
  const modalIcone      = document.getElementById('modal-icone');
  const modalTitulo     = document.getElementById('modal-titulo');
  const modalMensagem   = document.getElementById('modal-mensagem');

  // Mostrar/ocultar palavra-passe
  btnTogglePass.addEventListener('click', function () {
    const aMostrar = campoSenha.type === 'password';
    campoSenha.type = aMostrar ? 'text' : 'password';
    btnTogglePass.innerHTML = aMostrar
      ? '<i class="fa-regular fa-eye-slash"></i>'
      : '<i class="fa-regular fa-eye"></i>';
  });

  function abrirModalProcessando() {
    modal.hidden = false;
    modalIcone.className = 'modal-icone processando';
    modalIcone.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    modalTitulo.textContent = 'A processar...';
    modalMensagem.textContent = 'Estamos a validar as suas credenciais.';
  }

  function mostrarModalSucesso(mensagem) {
    modalIcone.className = 'modal-icone sucesso';
    modalIcone.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
    modalTitulo.textContent = mensagem;
    modalMensagem.textContent = 'A redireccionar para o painel...';
  }

  function mostrarModalErro(mensagem) {
    modalIcone.className = 'modal-icone erro';
    modalIcone.innerHTML = '<i class="fa-solid fa-circle-xmark"></i>';
    modalTitulo.textContent = 'Credenciais inválidas';
    modalMensagem.textContent = mensagem;
  }

  function fecharModal() {
    modal.hidden = true;
  }

  form.addEventListener('submit', async function (evento) {
    evento.preventDefault();
    btnSubmit.disabled = true;
    abrirModalProcessando();

    const dados = new FormData(form);
    const inicio = Date.now();
    const DURACAO_MINIMA_PROCESSANDO_MS = 5000; // 5 segundos, conforme especificação

    try {
      const resposta = await fetch(URL_BASE + '/auth/autenticar', {
        method: 'POST',
        body: dados,
      });
      const resultado = await resposta.json();

      const decorrido = Date.now() - inicio;
      if (decorrido < DURACAO_MINIMA_PROCESSANDO_MS) {
        await new Promise(r => setTimeout(r, DURACAO_MINIMA_PROCESSANDO_MS - decorrido));
      }

      if (resultado.sucesso) {
        mostrarModalSucesso(resultado.mensagem);
        setTimeout(function () {
          window.location.href = resultado.redirecionar;
        }, 1600);
      } else {
        mostrarModalErro(resultado.mensagem || 'Credenciais inválidas. Tente novamente.');
        btnSubmit.disabled = false;
        campoSenha.value = '';
        setTimeout(function () {
          fecharModal();
          campoSenha.focus();
        }, 2200);
      }
    } catch (erro) {
      mostrarModalErro('Não foi possível ligar ao servidor. Tente novamente.');
      btnSubmit.disabled = false;
      setTimeout(fecharModal, 2200);
    }
  });
});
