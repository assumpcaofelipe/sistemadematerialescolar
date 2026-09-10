# Central de Pedidos Escolares

Sistema web para a **Secretaria de Educação** que permite que escolas façam pedidos de materiais escolares de forma organizada — catálogo + carrinho + registro de pedido + notificação (e-mail e WhatsApp assistido) + painel administrativo. **Sem preço e sem pagamento.**

## Requisitos

- PHP **8.3** ou superior (testado em 8.3 e 8.4)
- MySQL 8.x
- Apache com `mod_rewrite` (ou qualquer servidor com rewrite; o Laravel-like `.htaccess` cobre o Apache)
- Composer (apenas para o PHPMailer)
- Extensões PHP: `pdo_mysql`, `mbstring`, `gd` (gerar/redimensionar imagem opcional), `fileinfo`

## Instalação

1. Clone/copie os arquivos para a raiz da hospedagem (ex: `public_html/`).

2. Crie o banco:
   ```bash
   mysql -u usuario -p < database/schema.sql
   ```

3. Instale as dependências:
   ```bash
   composer install
   ```

4. Configure o ambiente:
   ```bash
   cp .env.example .env
   ```
   Preencha os dados do banco e do SMTP (ver `.env.example`). Se `APP_URL` ficar vazio, a base URL é detectada automaticamente (funciona em subpasta e domínio).

5. Acesse com o Apache apontando para a pasta do projeto. O `.htaccess` já redireciona para `public/index.php`.

> Os produtos e categorias são cadastrados **pelo painel administrativo** (**Produtos → Novo produto** e **Categorias → Nova categoria**) — não há importação via arquivo.
## Acesso inicial

| Papel | E-mail | Senha |
|---|---|---|
| Administrador | `teste@teste.com.br` | `teste@123` |

> Credencial de **primeiro acesso**, criada automaticamente pelo `schema.sql`.
> **Troque a senha antes de usar em produção** (pelo link "Esqueci a senha" da tela de login ou via SQL com `password_hash`).
> Os demais usuários — outras escolas, administradores e supervisores — são criados pelo painel administrativo.

### Perfis de acesso

| Papel | O que acessa |
|---|---|
| **Administrador** | Tudo, inclusive o módulo de usuários e escolas |
| **Supervisor** | Painel administrativo (dashboard, pedidos, produtos, estoque, categorias, configurações) — **sem** acesso ao módulo de usuários nem cadastro de escolas |
| **Escola** | Catálogo, carrinho, confirmação do pedido e histórico com acompanhamento de status |

## Estrutura

```text
app/
  Controllers/   (escola e Admin/)
  Core/          (Router, Database, Controller, Session, Auth, Csrf, StatusPedido)
  Models/
  Services/      (CarrinhoService, PedidoService, NotificacaoService)
config/
database/        (schema.sql)
public/          (index.php, assets, uploads, manifest.json, service-worker.js)
routes/web.php
views/           (escola/, admin/, layouts/)
```

## Notificações

- **E-mail**: PHPMailer via SMTP da hospedagem. Credenciais no `.env` (`MAIL_*`), nunca hardcoded.
- **WhatsApp**: link `https://wa.me/NUMERO?text=MENSAGEM` com mensagem pronta (envio assistido/manual — **não** usa API paga). O número vem das **Configurações** do painel admin; se vazio, usa `WHATSAPP_FALLBACK` do `.env`.
- Falhas de envio de e-mail **não quebram o pedido** (o pedido já está salvo antes da notificação).

## Fluxo do pedido

Revisão → validar estoque novamente → salvar pedido (transação) → debitar estoque → gerar número (#) → e-mail → mensagem/WhatsApp → tela de sucesso.

## URLs principais

```text
/                     -> login da escola
/catalogo             -> catálogo (busca + filtro + paginação 20/pág)
/carrinho
/pedido/revisao
/pedido/sucesso
/pedidos              -> histórico de pedidos da escola (filtro por status)
/pedidos/{numero}     -> detalhe do pedido com acompanhamento

/admin                -> dashboard
/admin/pedidos
/admin/estoque        -> estoque baixo/zerado (filtro + ajuste rápido)
/admin/produtos       -> busca + filtro + paginação 20/pág + ajuste rápido de estoque
/admin/categorias
/admin/usuarios
/admin/configuracoes
```

## Segurança

- PDO com prepared statements (100%)
- CSRF em todos os formulários
- `e()` (htmlspecialchars) em toda saída dinâmica
- Sessões com `httponly`/`samesite` e regeneração de ID no login
- Uploads de imagem validados (mime, extensão, tamanho máx 2MB)
- Rotas protegidas por papel (`escola` / `admin` / `supervisor`; o módulo de usuários e escolas exige `admin`)
- Mensagens/alertas do sistema exibidos em modal (Bootstrap)

## PWA

`manifest.json` + `service-worker.js` (cache de assets estáticos). Instalável e com carregamento mais rápido; não exige funcionamento offline para pedidos.

## Documentação

- `MANUAL-DO-USUARIO.md` — manual completo para secretaria e escolas (como usar o sistema).
- `DOCUMENTACAO-TECNICA.md` — arquitetura, banco, rotas, serviços, segurança e como estender o código.

## MELHORIA FUTURA

- Restauração de estoque ao **cancelar** pedido (hoje só a exclusão de um pedido restaura o estoque)
- Notificação por push (se integrar ao PWA no futuro)
- Impressão/exportação de pedidos (PDF/CSV) no admin
- Categorias com subcategorias
- Duas etapas de aprovação/valorização interna
- Bloqueio por tentativas de login (brute force)