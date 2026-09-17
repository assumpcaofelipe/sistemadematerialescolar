# DOCUMENTAÇÃO TÉCNICA — Central de Pedidos Escolares

Documentação para **desenvolvedores** que vão manter ou evoluir o sistema.

Stack: PHP 8.3+ (compatível até 8.4), MySQL 8.x, PDO, Apache (`.htaccess`), Bootstrap 5, **Chart.js 4 (dashboard)**, **Inter (Google Fonts)**, PHPMailer, PWA. **Sem framework** — arquitetura própria (front controller + rotas + controllers tratados como classes e views em PHP puro).

---

## 1. Visão geral da arquitetura

Fluxo de uma requisição:

```text
Navegador
  → public/index.php            (front controller: define constantes, autoload, session, inicia Router)
      → routes/web.php          (registra todas as rotas GET/POST)
          → Router::dispatch    (resolve método/controller, injeta params {id}/{numero})
              → Controller      (valida auth/CSRF, usa Models/Services)
                  → View (layout + conteúdo, dados escapados com e())
```

### Pastas

```text
app/
  Controllers/         Controllers da área da escola (e do fluxo público)
    Admin/             Controllers exclusivos do painel administrativo
  Core/                Infraestrutura: Router, Database, Session, Auth, Csrf, Model, helpers...
  Models/              Camada de dados (PDO, prepared statements)
  Services/            Regras de negócio (carrinho, pedido, notificações)
bootstrap/autoload.php Carregador de classes + helpers (é o ponto de entrada do PHP)
config/config.php      Configuração + loadEnv (variáveis de ambiente)
database/schema.sql    Banco de dados completo
database/limpar_nomes.php  CLI de limpeza/normalização dos nomes de produtos
public/                Única pasta pública (index.php, assets, uploads, PWA)
routes/web.php         Mapa de rotas
views/                 Layouts + views da escola e do admin
```

---

## 2. Bootstrap, autoload e helpers

### `bootstrap/autoload.php`
- Carrega o autoload do Composer (PHPMailer) quando presente;
- Carrega as funções globais de `app/Core/helpers.php` (`e()`, `slugify()`, `csrf_field()`, …);
- Registra autoload PSR-4 simples das classes em `app/` (Namespace `App\`).

> O `config/config.php` (define `APP_NAME`, `BASE_URL`, fuso horário e lê o `.env`) é carregado pelo `public/index.php`, junto com `Session::iniciar(Session::areaDaRequisicao())` e o `Router`.

> ⚠️ **Importante:** o `helpers.php` é **obrigatório** — sem ele funções como `e()`, `slugify()` e `csrf_field()` ficam undefined.

### `app/Core/helpers.php` — globais
| Função | O que faz |
|---|---|
| `e($valor)` | `htmlspecialchars` (XSS-safe) para impressão **sempre** que houver saída dinâmica. Aceita `int\|float\|string\|null` (faz cast para string) |
| `slugify($texto)` | Gera slug permitindo `[a-z0-9-]`, limite de 180 (suporta todas as strings do catálogo, que vêm com acentos/“ç”) |
| `csrf_field()` | Retorna `<input type="hidden" name="csrf_token" value="...">` |
| `pagina_atual()` | Lê `$_GET['pagina']` (mín. 1) |
| `paginacao_html($total, $porPagina, $urlBase, $queryAtual)` | Links “Anterior/Próxima/números” preservando querystring |
| `arquivo_url($caminho)` | Converte caminho de upload salvo em URL pública |

### `config/config.php`
- `loadEnv()` lê o `.env` se existir (sem sobrescrever variáveis já definidas);
- Define `BASE_URL` automaticamente (protocolo + host + diretório do script) **quando** `APP_URL` não estiver definido — o que permite rodar em subpasta e em domínio;
- Define `APP_NAME`, `BASE_URL` e `APP_TIMEZONE` e aplica `date_default_timezone_set()`/`mb_internal_encoding('UTF-8')`.

---

## 3. Banco de dados

### Tabelas

**`usuarios`**
| campo | tipo |
|---|---|
| id | PK auto |
| nome | varchar(150) |
| email | varchar(190) unique |
| senha | varchar(255) (bcrypt) |
| tipo | enum('escola','admin','supervisor') |
| nome_escola | varchar(150) null (escolas) |
| status | tinyint(1) (0 = login bloqueado) |
| created_at / updated_at | timestamp |

**`categorias`** — id, nome, slug (unique), status, ordem, created_at, updated_at

**`produtos`**
| campo | tipo | observação |
|---|---|---|
| id | PK | |
| categoria_id | FK → categorias (ON DELETE RESTRICT) | |
| nome | varchar(600) | |
| slug | varchar(190) | unique, truncado no `slugify` |
| descricao | text | |
| quantidade_estoque | int | |
| imagem | varchar(255) | caminho relativo `public/uploads` (opcional) |
| status | tinyint(1) | inativo não listado |
| created_at / updated_at | timestamp | |

**`pedidos`**
| campo | tipo |
|---|---|
| id | PK |
| numero | int **unique** (numeração contínua) |
| usuario_id | FK → usuarios (ON DELETE RESTRICT) |
| status | enum('realizado','em_andamento','concluido','cancelado') |
| created_at / updated_at | timestamp |

**`itens_pedido`**
| campo | tipo |
|---|---|
| id | PK |
| pedido_id | FK → pedidos (ON DELETE CASCADE) |
| produto_id | FK → produtos (ON DELETE RESTRICT) |
| produto_nome | varchar(200) (snapshot) |
| quantidade | int |

**`configuracoes`** (chave/valor: id, chave unique, valor, updated_at)
- `whatsapp_secretaria` — **editável** pelo admin em `/admin/configuracoes` (usado no link `wa.me`);
- `email_secretaria` — destinatário dos avisos automáticos; **continua sendo lido** por `NotificacaoService::emailNovoPedido`, mas **não é mais editado** pela tela de configurações (configurado por fora).

### Convenções
- **Nome do produto é “empacotado” no item** (`produto_nome`): se o produto for excluído depois, o histórico do pedido continua legível.
- **Numeração do pedido**: `COALESCE(MAX(numero), 2047) + 1` → o primeiro pedido recebe **#2048** (`Pedido::ultimoNumero()` + `PedidoService::confirmar`).
- FK de `produtos.categoria_id` é **ON DELETE RESTRICT** (categoria não pode ser excluída se tiver produtos — regra também validada no controller).

---

## 4. Rotas (`routes/web.php`)

A assinatura é: `$router->get('/caminho', Classe::class, 'metodo')` e `$router->post(...)`.

| Rota | Método | Controller | Proteção |
|---|---|---|---|
| `/` | GET | `AuthController::showLogin` | — |
| `/login` | POST | `AuthController::login` | — |
| `/recuperar-senha` | POST | `AuthController::recuperarSenha` (JSON) | — |
| `/logout` | GET | `AuthController::logout` (redireciona para `/?saiu=1`) | escola |
| `/catalogo` | GET | `CatalogoController::index` | escola |
| `/carrinho` | GET | `CarrinhoController::index` | escola |
| `/carrinho/adicionar` | POST | `CarrinhoController::adicionar` (JSON) | escola + CSRF |
| `/carrinho/atualizar` | POST | `CarrinhoController::atualizar` (JSON) | escola + CSRF |
| `/carrinho/remover` | POST | `CarrinhoController::remover` (JSON) | escola + CSRF |
| `/pedido/revisao` | GET | `PedidoController::revisao` | escola |
| `/pedido/confirmar` | POST | `PedidoController::confirmar` | escola + CSRF |
| `/pedido/sucesso` | GET | `PedidoController::sucesso` | escola |
| `/pedidos` | GET | `PedidoController::historico` (filtro status + paginação) | escola |
| `/pedidos/{numero}` | GET | `PedidoController::detalhe` | escola |
| `/admin/login` | GET/POST | `Admin\AuthController::showLogin/login` | — |
| `/admin/recuperar-senha` | POST | `Admin\AuthController::recuperarSenha` (JSON) | — |
| `/admin/logout` | GET | `Admin\AuthController::logout` (redireciona para `/admin/login?saiu=1`) | painel |
| `/admin`, `/admin/dashboard` | GET | `Admin\DashboardController::index` (filtros `periodo`/`status`/`escola`/`categoria` + Chart.js) | painel |
| `/admin/pedidos` | GET | `Admin\PedidoController::index` (filtros status + escola + paginação) | painel |
| `/admin/pedidos/{numero}` | GET | `Admin\PedidoController::show` | painel |
| `/admin/pedidos/status/{id}` | POST | `Admin\PedidoController::updateStatus` | painel + CSRF |
| `/admin/pedidos/excluir/{id}` | POST | `Admin\PedidoController::destroy` | painel + CSRF |
| `/admin/produtos` | GET (`?pagina`, `?busca`, `?categoria`) | `Admin\ProdutoController::index` | painel |
| `/admin/produtos/novo` | GET/POST | `Admin\ProdutoController::create/store` | painel + CSRF |
| `/admin/produtos/editar/{id}` | GET/POST | `Admin\ProdutoController::edit/update` | painel + CSRF |
| `/admin/produtos/excluir/{id}` | POST | `Admin\ProdutoController::destroy` | painel + CSRF |
| `/admin/estoque` | GET (`?busca`, `?categoria`, `?situacao`) | `Admin\ProdutoController::estoque` | painel |
| `/admin/categorias` | GET/POST | `Admin\CategoriaController` | painel + CSRF |
| `/admin/usuarios` | GET/POST | `Admin\UsuarioController` | **admin** + CSRF |
| `/admin/usuarios/novo` `editar/{id}` `excluir/{id}` | GET/POST | `Admin\UsuarioController::create/edit/destroy` — escolas | **admin** + CSRF |
| `/admin/usuarios/admin/novo` | GET/POST | `Admin\UsuarioController::createAdmin/storeAdmin` | **admin** + CSRF |
| `/admin/usuarios/admin/editar/{id}` | GET/POST | `Admin\UsuarioController::editAdmin/updateAdmin` (pode trocar tipo/status) | **admin** + CSRF |
| `/admin/usuarios/admin/excluir/{id}` | POST | `Admin\UsuarioController::destroyAdmin` (permissões: não exclui a si, mantém ≥1 admin) | **admin** + CSRF |
| `/admin/configuracoes` | GET/POST | `Admin\ConfiguracaoController::index/update` (campo WhatsApp **somente admin**) | painel + CSRF |

**Papéis ("Proteção"):** `escola` = contas de escola; `painel` = **admin ou supervisor** (`Auth::requireAcessoAdmin()`); **admin** = somente administrador (`Auth::requireAdmin()`) — aplicado ao módulo de usuários e escolas. A sidebar oculta o grupo **Configurações** (Escolas, Usuários do sistema e Secretaria/WhatsApp) para supervisores. Em `/admin/configuracoes`, o campo/gravação do WhatsApp é ainda validado por `Auth::isAdmin()` dentro do controller.

**Sessões por área:** a URL decide qual cookie de sessão é usado (`SESS_EDUCA_ADMIN` em `/admin*`, `SESS_EDUCA_ESCOLA` nas demais). Por isso os dois painéis podem ficar logados **ao mesmo tempo** no mesmo navegador e o logout de um não afeta o outro (detalhes no item 5, `Session`).

O `Router::dispatch` suporta dois placeholders: `{id}` e `{numero}` (padrão `([0-9]+)`), coercidos para `int` antes de chamar o método. Rotas inexistentes → view `404.php`. Detalhes: `routes/web.php:1`, `app/Core/Router.php`.

---

## 5. Core (infraestrutura)

### `app/Core/Router.php`
- `get()/post()` acumulam rotas;
- `dispatch($uri, $method)` — primeiro tenta matching **literal**, depois com placeholders;
- Executa o controller com `call_user_func`, passando as variáveis capturadas da URL;
- Imprime resposta como JSON se o controller retornar array (endpoints AJAX).

### `app/Core/Database.php`
- Singleton PDO (`mysql:host;dbname;charset=utf8mb4`), options: `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, `EMULATE_PREPARES=false`.
- Credenciais do `.env` (ou defaults para dev).

### `app/Core/Model.php`
- `db(): PDO` — acesso ao singleton;
- `all()`, `find($id)`, `count()` genéricos via `$tabela`.

### `app/Core/Controller.php`
- `view($arquivo, $dados)` — base, faz `extract` e inclui a view;
- `viewEscola($arquivo, $dados)` — injeta `usuario` logado e `escolaCarrinhoItens` antes de renderizar a view da escola;
- `redirect($caminho)`;
- `jsonResponse($dados, $status)` com `Content-Type: application/json`;
- `flash($chave, $mensagem)` / `getFlash($chave)` (lê e remove) — a mensagem pode ser `string` **ou `array`** (array vira lista no modal, usado por `pedido_detalhes`) — e `old($chave, $default)`;
- `sanitize($texto)`.

### `app/Core/Session.php` — sessões separadas por área
- Duas sessões independentes (dois cookies): **`SESS_EDUCA_ESCOLA`** (área da escola) e **`SESS_EDUCA_ADMIN`** (painel). A área é resolvida pela URL (`/admin*` → admin; resto → escola) em `areaDaRequisicao()`;
- `iniciar($area)` fecha qualquer sessão ativa, define `session_name()`, aplica `session.gc_maxlifetime` e faz `session_start()`. Chamado **uma vez** no front controller: `Session::iniciar(Session::areaDaRequisicao())`;
- **Tempo de vida**: `TEMPO_SESSAO = 86400` (24h), usado no cookie (`lifetime`) e no `gc_maxlifetime`. Renovação deslizante: `$_SESSION['last_activity']` é atualizado a cada request; se `expirada()` (mais de 24h sem uso), a sessão é limpa, derrubando o login;
- Cookie com `httponly`, `samesite=Lax` e `secure` quando HTTPS;
- `set/get/remove/has`, `regenerateId()`, `destroy()` (limpa dados e expira o cookie da **área ativa**, sem tocar na outra);
- `areaAtiva()` informa a área corrente. Permite **acessos simultâneos** de escola e admin e logout independente;
- As mensagens `flash` ficam em `$_SESSION['flash']` (da sessão da área) e são exibidas como **modal Bootstrap** pelo partial `views/layouts/alertas.php`, incluído nos footers (`admin_footer.php`/`escola_footer.php`) e nas páginas de login.

> ⚠️ Ao subir esta versão, os cookies antigos (`PHPSESSID`) deixam de valer — todos precisam logar **uma vez** de novo.

### `app/Core/Auth.php`
- Constantes de papel: `TIPO_ESCOLA`, `TIPO_ADMIN`, `TIPO_SUPERVISOR`;
- `login(Usuario)`, `logout()`, `check()`, `user()` (com cache estático por request), `id()`;
- Verificadores: `isEscola()`, `isAdmin()`, `isSupervisor()`, `isAdminArea()` (admin **ou** supervisor);
- Guards: `requireEscola()`, `requireAdmin()` (admin estrito), `requireAcessoAdmin()` (admin ou supervisor) — redirecionam para o login da área se não autenticado com o papel exigido;
- `redirectToLogin()`: se existir o **cookie da sessão da área** (ou seja, havia sessão) grava `flash('auth_error', 'Sua sessão expirou. Faça login novamente.')` antes de redirecionar;
- No login: `session_regenerate_id(true)`.

### Gestão de usuários do sistema (`Admin\UsuarioController`)
- **`editAdmin($id)` / `updateAdmin($id)`**: edição de admin/supervisor com o formulário `form_admin.php`. O `update` do model suporta a coluna `tipo` — é possível **mudar a função** do usuário (admin ↔ supervisor) e ativar/desativar (`status`).
  - Se o usuário alterar a **própria função**, `updateAdmin` encerra a sessão (`Auth::logout()`) e pede login com o novo papel;
  - Proteções: impede rebaixar/desativar o **último administrador ativo** (regra `countByTipo(TIPO_ADMIN) <= 1`).
- **`destroyAdmin($id)`**: exclui admin/supervisor com as mesmas proteções — não exclui a si mesmo (`$id === Auth::id()`), exige ≥1 admin restante, valida que o usuário é realmente do painel.
- Lista de usuários do sistema renderizada na própria tela `form_admin.php` (tabela com Função, Status e ações Editar/Excluir).

### `app/Core/Csrf.php`
- Gera e valida token de 64 hex;
- `validate(true)` encerra com 419 em falha;
- No admin o CSRF é checado no construtor dos controllers (padrão em cada POST).

### `app/Core/StatusPedido.php`
- Constantes + método `labels()`/`badge()` usados nas views admin (tradução enum → texto/cores).

---

## 6. Services — regras de negócio

### `CarrinhoService`
- Carrinho fica **na sessão**: `$_SESSION['carrinho'][$produto_id] = quantidade`;
- `adicionar()`, `atualizar()` (quantidade `<= 0` remove o item), `remover()`, `limpar()`, `totalItens()`, `vazio()`, `itensDetalhados()` (monta com `findMany`, preservando a ordem);
- **Não há mais validação de estoque no carrinho** — a escola vê o catálogo sem saldo e pode pedir qualquer quantidade. O estoque é validado somente na **conclusão** do pedido pela secretaria.

### `PedidoService::confirmar(int $usuarioId, array $itens): ?array`
Cria o pedido **sem movimentar estoque**, de forma atômica:
1. `BEGIN`;
2. Gera o número: `Pedido::ultimoNumero() + 1` (`COALESCE(MAX(numero), 2047) + 1` → primeiro pedido é **#2048**);
3. `INSERT` do pedido (status `realizado`) + `INSERT` de cada item (`produto_nome` = snapshot);
4. `COMMIT`; em erro faz `ROLLBACK` e retorna `null`.

O controller devolve a tela de sucesso com o número e o link de WhatsApp. **Nenhuma quantidade é debitada aqui.**

### Baixa e restauração de estoque (`App\Models\Pedido`)
- `debitarItensEstoque($pedidoId)` / `restaurarItensEstoque($pedidoId)` → `movimentarItensEstoque()` com transação única; o débito usa `UPDATE ... AND quantidade_estoque >= ?` e **lança exceção** se algum produto não tiver saldo (impede estoque negativo). Como o método já abre a transação, o débito ocorre **fora** da transação de criação do pedido;
- `problemasDeEstoqueParaConclusao($pedidoId)` — lista de mensagens (`array`) quando algum item está sem produto, **zerado** ou com estoque **insuficiente** (`pedido: N, disponível: M`);
- `excluirComRestauro($id)` — exclui o pedido em transação e **devolve o estoque apenas se o status era `concluido`**.
- **Gatilho**: `Admin\PedidoController::updateStatus` — ao entrar em `concluido` valida `problemasDeEstoqueParaConclusao()`, mostra `flash('pedido_erro')` + `flash('pedido_detalhes', $problemas)` e **não** altera o status se houver problema; só então chama `debitarItensEstoque()`. Ao **sair** de `concluido` (ou cancelar/excluir), `restaurarItensEstoque()` devolve o saldo.

### `NotificacaoService`
- `emailNovoPedido(int $numero, string $escola, array $itens)` — PHPMailer (SMTP). **Guarda de ambiente:** se `MAIL_HOST` estiver vazio/placeholder (`seudominio`), **pula o envio** (sem travar o fluxo em dev). Falha ao enviar não derruba o pedido (`try/catch`). Usa `e((string) (int) ...)` para evitar `TypeError` em produção.
- `montarMensagemWhatsApp(string $escola, int $numero, array $itens)` — monta a mensagem com o formato atual: *"Olá! A escola {escola} realizou o pedido #{n} pelo Sistema de Pedidos Escolares."*, lista `• {qtd}x {produto}`, *"Aguardamos o recebimento e o processamento do pedido."* e *"Obrigado!"*;
- `linkWhatsApp(string $mensagem)` — retorna `https://wa.me/NUMERO?text=MENSAGEM` (`rawurlencode`). Lê o número da tabela `configuracoes`; se ausente, usa `WHATSAPP_FALLBACK` do `.env`.

---

## 7. Dashboard administrativo (Chart.js + filtros)

### Front-end
- **Chart.js 4** é carregado por CDN no `views/layouts/admin_footer.php` (v4.4.3, `chart.umd.min.js`) e fica **disponível em todas as páginas do admin**.
- `views/admin/dashboard.php` recebe os dados **pré-serializados** (`json_encode` com `JSON_HEX_TAG | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE`) e monta os gráficos em `DOMContentLoaded`.
- Gráficos: linha **Pedidos por dia**, rosca **Pedidos por status**, barras horizontais **Produtos mais pedidos** e **Escolas que mais pedem**; cards de números de destaque usam a classe `.destaque-numero`.
- Sem gráfico, sem erro: `typeof Chart === 'undefined'` → `console.warn` e segue.

### Filtros (GET, re-renderizam a página)
| Parâmetro | Valores | Aplica em |
|---|---|---|
| `periodo` | `7`, `15`, `30`, `90`, `0` (todos) | cards, série, status, top |
| `status` | `realizado`, `em_andamento`, `concluido`, `cancelado`, vazio | série + top produtos/escolas |
| `escola` | id da escola ou `0` | cards + todos os gráficos |
| `categoria` | id da categoria ou `0` | gráfico de produtos mais pedidos |

`periodo` fora da lista cai em `0` (todo o período). Quando `periodo=0`, a série diária é construída entre o primeiro e o último dia com pedidos.

### Agregações nos models (todas com prepared statements)
- `Pedido::contarPorStatusPeriodo($dias, $escolaId)` — 4 contagens agrupadas por status;
- `Pedido::seriePorDia($dias, $status, $escolaId)` — `['labels' => [d/m], 'data' => [contagens]]`, preenchendo dias sem pedido com 0;
- `Pedido::escolasQueMaisPedem($limite, $dias, $status)` — top escolas excluindo cancelados;
- `Produto::maisPedidos($limite, $dias, $status, $escolaId, $categoriaId)` — top por `SUM(ip.quantidade)`, **agrupando por snapshot** `ip.produto_nome` (funciona mesmo se o produto for excluído).

> O acompanhamento de **estoque baixo/zerado** não fica no dashboard: está na página própria `/admin/estoque` (`Produto::paginarEstoque()`, filtros busca/categoria/situação, `LIMITE_BAIXO_ESTOQUE = 7`).

### Gestão de estoque (`/admin/estoque`)

- Página `Admin\ProdutoController::estoque` lista **todos os produtos ativos** (busca + categoria + situação + paginação). `situacao` aceita `todos` (padrão, maior→menor), `asc` (menor→maior), `baixo` (`0 < qtd < 7`) e `zerado` (`qtd = 0`); valores fora da lista caem em `todos`;
- A tabela exibe **Produto**, **Categoria**, **Estoque** e **Status** apenas para **consulta**: os campos são `disabled` (sem formulário e sem botão Salvar). A edição de quantidade/status é feita no **formulário do produto** (`/admin/produtos/novo` e `/admin/produtos/editar/{id}`), campos `quantidade_estoque` e `status`;
- `ProdutoController::dadosDoFormulario()` inclui `quantidade_estoque` (int) e `Produto::update()`/`create()` gravam essa coluna; o status do produto só muda por esse formulário;
- Não há importação/exportação por CSV.

> Compatibilidade PHP 8.3: o projeto NÃO usa `mb_str_contains` (inexistente) — usar `str_contains`. Dados dos gráficos são sempre escapados/convertidos para int no PHP antes do `json_encode`.

---

## 8. Segurança

| Ameaça | Mitigação |
|---|---|
| SQL Injection | PDO prepared statements (sem concatenação) |
| XSS | `e()` (htmlspecialchars) em toda saída dinâmica nas views |
| CSRF | `csrf_field()` nos forms + endpoints AJAX validam token; admin valida no construtor |
| Sessão | dois cookies por área (`SESS_EDUCA_ESCOLA`/`SESS_EDUCA_ADMIN`), `httponly` + `samesite=Lax` (+ `secure` em HTTPS), validade deslizante de 24h e `session_regenerate_id(true)` no login; logout expira apenas a sessão da área ativa |
| Upload | validação de mime (imagem), extensão permitida e tamanho ≤ 2MB; nome gerado com `random_bytes` (hex de 16 chars) |
| Traversal | upload salvo em `public/uploads` com nome aleatório |
| Brute force | sem bloqueio dedicado (melhoria futura) — senha bcrypt |
| Cache / PWA | `Cache-Control: no-store, no-cache, must-revalidate, max-age=0` e `Pragma: no-cache` definidos no `public/index.php` para todas as respostas HTML; service worker (`central-pedidos-v3`) **não armazena HTML** — apenas assets estáticos (CSS/JS/ícones), com purge automático de versões antigas. Garante que dados sensíveis não fiquem no cache do navegador. |

---

## 9. Configuração por ambiente (`config/config.php` + `.env`)

Variáveis suportadas (`.env.example` contém todas):

```dotenv
APP_TIMEZONE=America/Sao_Paulo
APP_URL=                 # vazio => BASE_URL automática
DB_HOST=localhost
DB_NAME=central_pedidos
DB_USER=root
DB_PASS=
MAIL_HOST=               # vazio/placeholder => NotificacaoService ignora e-mail
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
MAIL_USERNAME=pedidos@seudominio.com.br   # também usado como remetente
MAIL_PASSWORD=
MAIL_FROM_NAME="Central de Pedidos Escolares"
WHATSAPP_FALLBACK=5511998877665           # usado se configuracoes estiver vazia
```

> `APP_URL` tem precedência sobre a detecção automática. Definir é recomendado em produção.

---

## 10. Como rodar em desenvolvimento

```powershell
# Apache/Laragon: servir a pasta do projeto (public/ é o docroot)
php -S localhost:8080 -t public
```

MySQL: `mysql -u root < database/schema.sql` (cria o banco + usuário administrador padrão `teste@teste.com.br`/`teste@123`).

---

## 11. Como adicionar uma nova página (passo a passo)

Ex.: criar `/relatorios` no admin.

1. **Rota**: em `routes/web.php:…` → `$router->get('/admin/relatorios', RelatorioController::class, 'index');`
2. **Controller**: `app/Controllers/Admin/RelatorioController.php` com `use App\Core\Controller;` e, no `__construct`, o guard de acesso — `Auth::requireAcessoAdmin()` (admin **ou** supervisor) ou `Auth::requireAdmin()` (somente admin).
3. **Model** (se precisar de consulta): estender `App\Core\Model` definindo `$tabela`.
4. **View**: `views/admin/relatorios/index.php` usando o layout
   ```php
   require __DIR__ . '/../../layouts/admin_header.php';
   // ... conteúdo usando e($var) ...
   require __DIR__ . '/../../layouts/admin_footer.php';
   ```
5. Chamar pelo controller: `$this->view('admin/relatorios/index', ['relatorios' => $rows]);`

**Para POST** adicione `csrf_field()` no form e `Csrf::validate()` no controller.

---

## 12. Views: caminhos de layout

| View | Caminho do layout |
|---|---|
| `views/escola/*` | `__DIR__ . '/../layouts/escola_header.php'` |
| `views/admin/*` (nível 2) | `__DIR__ . '/../../layouts/admin_header.php'` |
| `views/admin/login.php` | página standalone (HTML próprio) que inclui `layouts/alertas.php` |

Os footers (`admin_footer.php`/`escola_footer.php`) incluem `layouts/alertas.php`, que transforma as mensagens `flash` em **modal Bootstrap**; as respostas AJAX também abrem modal via `mostrarAlerta()` em `public/assets/js/app.js`. O mesmo vale para os footers. Views acessíveis apenas sob `views/` — todo HTML público é renderizado a partir delas.

---

## 13. PWA

- `public/manifest.json` — nome, ícones, tema, standalone;
- `public/service-worker.js` — cache **v3** (`central-pedidos-v3`): somente assets estáticos (CSS/JS); **páginas HTML nunca são gravadas em cache** (sempre rede, cache apenas como fallback offline) — somado aos headers `Cache-Control: no-store` do `public/index.php`;
- Registro é feito no `escola_footer.php` (somente onde o PWA faz sentido — área da escola);
- Ao subir versões que mudem HTML/CSS/JS, **bump na constante `CACHE`** do service worker para forçar purge do cache antigo no cliente;
- Instalação exige HTTPS (ou localhost);

## 14. Testes

Não há framework de testes automatizados no projeto. A validação de ponta a ponta é feita por scripts manuais em PowerShell/curl (no diretório `C:\Users\Felipe\AppData\Local\Temp\opencode\`), que exercitam no servidor real:

- **Fluxo escola**: login → catálogo (busca/filtro/paginação, sem exibir estoque) → adicionar/atualizar/remover carrinho (sem bloqueio de estoque; produto inexistente e CSRF) → revisão → confirmar → sucesso (link/mensagem do WhatsApp) → histórico → detalhe → logout;
- **Fluxo admin**: login → dashboard (gráficos) → pedidos (mudança de status com validação de estoque na conclusão e restauração ao sair de concluído; excluir com restauro) → categorias/produtos/usuários (CRUD completo; produto edita nome/descrição/categoria/**estoque**/status/imagem) → estoque (filtros `todos`/`asc`/`baixo`/`zerado`, **campos `disabled` — só consulta**) → configurações (WhatsApp admin-only) → proteção de rotas por papel → CSRF inválido (419) → 404 → logout;
- **Sessões**: verificação de `SESS_EDUCA_ESCOLA`/`SESS_EDUCA_ADMIN` simultâneos, logout independente e mensagens de expiração/desconexão.

Cada execução cria um usuário temporário (admin + escola), testa e **remove tudo no final**, restaurando estoques alterados. Não deixar scripts/sondas temporários dentro de `public/`.

---

## 15. Melhorias futuras (roadmap)

- Impressão/exportação (PDF) dos pedidos;
- Notificação push via PWA;
- Categorias com subcategorias;
- Rate limiting/brute-force protection no login.