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

  // Fallback: se a via AJAX falhar (ex.: ficheiro JS do servidor nao
  // carregou / endpoint indisponivel), envia o formulario nativamente
  // por POST para o endereco absoluto — nunca por GET com credenciais na URL.
  let submissaoNativaEmCurso = false;
  function submeterNativamente() {
    if (submissaoNativaEmCurso) return;
    submissaoNativaEmCurso = true;
    fecharModal();
    btnSubmit.disabled = false;
    try { form.submit(); } catch (e) { /* nada a fazer */ }
  }

  form.addEventListener('submit', async function (evento) {
    // Impede SEMPRE o envio GET/relativo do formulario, mesmo que o
    // browser tenha ignorado parte do script anteriormente.
    evento.preventDefault();

    const emailCampo = document.getElementById('email');
    const email = (emailCampo.value || '').trim();
    const senha = campoSenha.value || '';

    // Valiacoes locais simples (evitam pedidos invalidos ao servidor)
    if (email === '' || senha === '') {
      abrirModalProcessando();
      mostrarModalErro('Preencha o utilizador e a palavra-passe.');
      setTimeout(function () {
        fecharModal();
        (email === '' ? emailCampo : campoSenha).focus();
      }, 2200);
      return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      abrirModalProcessando();
      mostrarModalErro('O e-mail inserido parece incompleto ou invalido. Verifique se falta o nome de dominio (ex.: @empresa.com).');
      setTimeout(function () {
        fecharModal();
        emailCampo.focus();
      }, 3000);
      return;
    }

    btnSubmit.disabled = true;
    abrirModalProcessando();

    const params = new URLSearchParams();
    params.append('email', email);
    params.append('senha', senha);
    const inicio = Date.now();
    const DURACAO_MINIMA_PROCESSANDO_MS = 5000; // 5 segundos, conforme especificacao

    try {
      const resposta = await fetch(URL_BASE + '/auth/autenticar', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: params.toString(),
      });

      const texto = await resposta.text();
      let resultado;
      try {
        resultado = JSON.parse(texto);
      } catch (e) {
        // O servidor devolveu algo que nao e JSON (pagina de erro, HTML, etc.)
        throw new Error('resposta-invalida');
      }

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
      // Sem ligacao / resposta inesperada: usa o envio POST nativo do
      // formulario (nunca GET) para que o utilizador consiga iniciar sessao.
      submeterNativamente();
    }
  });
});
