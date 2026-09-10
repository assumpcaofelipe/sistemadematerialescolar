document.addEventListener('DOMContentLoaded', function () {
    // Submenu colapsável da sidebar do admin
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.sidebar-submenu-toggle');
        if (!btn) return;
        e.preventDefault();
        var target = document.querySelector(btn.getAttribute('data-target'));
        if (!target) return;
        var aberto = target.classList.toggle('aberto');
        btn.classList.toggle('expanded', aberto);
        btn.setAttribute('aria-expanded', aberto ? 'true' : 'false');
    });

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    function enviar(form, url, sucesso, erro) {
        var btn = form.querySelector('button[type="submit"]');
        var original = btn ? btn.textContent : '';
        if (btn) { btn.disabled = true; }

        var fd = new FormData(form);
        fd.append('csrf_token', csrfToken());

        fetch(url, { method: 'POST', body: fd })
            .then(function (resp) {
                return resp.json().then(function (data) {
                    if (!resp.ok) throw data;
                    return data;
                });
            })
            .then(function (data) {
                if (sucesso) sucesso(data);
            })
            .catch(function (err) {
                var msg = (err && err.mensagem) ? err.mensagem : 'Falha na operação.';
                if (erro) erro(msg);
            })
            .finally(function () {
                if (btn) {
                    btn.textContent = original;
                    btn.disabled = false;
                }
            });
    }

    // Adicionar ao carrinho (catálogo)
    document.querySelectorAll('.js-adicionar-carrinho').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var input = form.querySelector('input[name="quantidade"]');
            var btn = form.querySelector('button[type="submit"]');

            enviar(form, '/carrinho/adicionar', function (data) {
                mostrarAlerta(data.mensagem, 'success');
                atualizarBotaoCarrinho(data.total);
                if (btn) { btn.classList.replace('btn-primary', 'btn-success'); }
            }, function (msg) {
                mostrarAlerta(msg, 'danger');
                if (input) input.select();
                if (btn) { btn.classList.replace('btn-primary', 'btn-danger'); }
            });
        });
    });

    // Atualizar quantidade (carrinho)
    document.querySelectorAll('.js-atualizar-carrinho').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            enviar(form, '/carrinho/atualizar', function (data) {
                mostrarAlerta(data.mensagem, 'success');
                atualizarBotaoCarrinho(data.total);
                setTimeout(function () { location.reload(); }, 1600);
            }, function (msg) {
                mostrarAlerta(msg, 'danger');
            });
        });
    });

    // Remover item (carrinho)
    document.querySelectorAll('.js-remover-carrinho').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!confirm('Remover este produto do pedido?')) return;
            enviar(form, '/carrinho/remover', function (data) {
                mostrarAlerta(data.mensagem, 'success');
                atualizarBotaoCarrinho(data.total);
                setTimeout(function () { location.reload(); }, 1600);
            }, function (msg) {
                mostrarAlerta(msg, 'danger');
            });
        });
    });

    // Ajuste rápido de estoque (admin)
    document.querySelectorAll('.js-ajuste-estoque').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = form.querySelector('button[type="submit"]');
            var input = form.querySelector('input[name="quantidade"]');

            enviar(form, form.action, function () {
                btn.textContent = '✓';
                input.classList.remove('is-invalid');
                setTimeout(function () { btn.textContent = 'OK'; }, 1200);
            }, function (msg) {
                btn.textContent = '!';
                input.classList.add('is-invalid');
                mostrarAlerta(msg, 'danger');
            });
        });
    });

    // Atualiza o contador do carrinho na sidebar
    function atualizarBotaoCarrinho(total) {
        var el = document.getElementById('navCarrinho');
        var badge = document.getElementById('navCarrinhoContador');
        if (!el && !badge) return;
        if (el) { el.classList.toggle('nav-carrinho-ativo', total > 0); }
        if (badge) {
            badge.textContent = total;
            badge.classList.toggle('d-none', total <= 0);
        }
    }

    // Mostra a resposta (sucesso/erro) em um modal Bootstrap
    function mostrarAlerta(mensagem, tipo) {
        var el = document.createElement('div');
        el.className = 'modal fade';
        el.setAttribute('tabindex', '-1');
        el.setAttribute('aria-hidden', 'true');
        var titulo = tipo === 'success' ? 'Tudo certo!' : 'Atenção';
        var corpo = document.createElement('div');
        corpo.className = 'alert alert-' + (tipo === 'success' ? 'success' : 'danger') + ' py-2 mb-0';
        corpo.textContent = mensagem;
        el.innerHTML =
            '<div class="modal-dialog modal-dialog-centered" role="document">' +
                '<div class="modal-content">' +
                    '<div class="modal-header">' +
                        '<h1 class="modal-title fs-5">' + titulo + '</h1>' +
                        '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>' +
                    '</div>' +
                    '<div class="modal-body"></div>' +
                    '<div class="modal-footer">' +
                        '<button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>' +
                    '</div>' +
                '</div>' +
            '</div>';
        el.querySelector('.modal-body').appendChild(corpo);
        document.body.appendChild(el);

        var modal = new bootstrap.Modal(el);
        el.addEventListener('hidden.bs.modal', function () { el.remove(); });
        modal.show();

        if (tipo === 'success') {
            setTimeout(function () {
                if (document.body.contains(el)) modal.hide();
            }, 2200);
        }
    }
});