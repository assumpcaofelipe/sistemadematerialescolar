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

> O `config/config.php` (define `APP_NAME`, `BASE_URL`, fuso horário e lê o `.env`) é carregado pelo `public/index.php`, junto com `Session::start()` e o `Router`.

> ⚠️ **Importante:** o `helpers.php` é **obrigatório** — sem ele funções como `e()`, `slugify()` e `csrf_field()` ficam undefined.

### `app/Core/helpers.php` — globais
| Função | O que faz |
|---|---|
| `e($valor)` | `htmlspecialchars` (XSS-safe) para impressão **sempre** que houver saída dinâmica |
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
- `whatsapp_secretaria`
- `email_secretaria`

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
| `/logout` | GET | `AuthController::logout` | escola |
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
| `/admin/logout` | GET | `Admin\AuthController::logout` | painel |
| `/admin`, `/admin/dashboard` | GET | `Admin\DashboardController::index` (filtros `periodo`/`status`/`escola`/`categoria` + Chart.js) | painel |
| `/admin/pedidos` | GET | `Admin\PedidoController::index` (filtros status + escola + paginação) | painel |
| `/admin/pedidos/{numero}` | GET | `Admin\PedidoController::show` | painel |
| `/admin/pedidos/status/{id}` | POST | `Admin\PedidoController::updateStatus` | painel + CSRF |
| `/admin/pedidos/excluir/{id}` | POST | `Admin\PedidoController::destroy` | painel + CSRF |
| `/admin/produtos` | GET (`?pagina`, `?busca`, `?categoria`) | `Admin\ProdutoController::index` | painel |
| `/admin/produtos/novo` | GET/POST | `Admin\ProdutoController::create/store` | painel + CSRF |
| `/admin/produtos/editar/{id}` | GET/POST | `Admin\ProdutoController::edit/update` | painel + CSRF |
| `/admin/produtos/excluir/{id}` | POST | `Admin\ProdutoController::destroy` | painel + CSRF |
| `/admin/produtos/estoque/{id}` | POST | `Admin\ProdutoController::ajustarEstoque` (JSON) | painel + CSRF |
| `/admin/estoque` | GET (`?busca`, `?categoria`, `?situacao`) | `Admin\ProdutoController::estoque` | painel |
| `/admin/categorias` | GET/POST | `Admin\CategoriaController` | painel + CSRF |
| `/admin/usuarios` | GET/POST | `Admin\UsuarioController` | **admin** + CSRF |
| `/admin/usuarios/admin/novo` | GET/POST | `Admin\UsuarioController::createAdmin/storeAdmin` | **admin** + CSRF |
| `/admin/configuracoes` | GET/POST | `Admin\ConfiguracaoController::index/update` | painel + CSRF |

**Papéis ("Proteção"):** `escola` = contas de escola; `painel` = **admin ou supervisor** (`Auth::requireAcessoAdmin()`); **admin** = somente administrador (`Auth::requireAdmin()`) — aplicado ao módulo de usuários e escolas. A sidebar oculta "Escolas"/"Configurações" para supervisores.

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
- `flash($chave, $mensagem)` / `getFlash($chave)` (lê e remove) e `old($chave, $default)`;
- `sanitize($texto)`.

### `app/Core/Session.php`
- `start()` (uma vez), `set/get/remove/has`, `flash($chave,$valor)` + `getFlash($chave)` (lê e remove);
- Configura cookie com `httponly`, `samesite=Lax`;
- As mensagens `flash` ficam em `$_SESSION['flash']` e são exibidas como **modal Bootstrap** pelo partial `views/layouts/alertas.php`, incluído nos footers (`admin_footer.php`/`escola_footer.php`) e nas páginas de login.

### `app/Core/Auth.php`
- Constantes de papel: `TIPO_ESCOLA`, `TIPO_ADMIN`, `TIPO_SUPERVISOR`;
- `login(Usuario)`, `logout()`, `check()`, `user()` (com cache estático por request), `id()`;
- Verificadores: `isEscola()`, `isAdmin()`, `isSupervisor()`, `isAdminArea()` (admin **ou** supervisor);
- Guards: `requireEscola()`, `requireAdmin()` (admin estrito), `requireAcessoAdmin()` (admin ou supervisor) — redirecionam para o login se não autenticado com o papel exigido;
- No login: `session_regenerate_id(true)`.

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
- `proximoId()`, `quantidadeItem()`, `itens()` (monta com dados do produto), `totalItens()`, `vazio()`;
- Associado sempre ao **usuário logado** (carrega/busca pela sessão do usuário);
- `atualizarQuantidade()` respeita o limite do estoque.

### `PedidoService::confirmar(array $itens, int $usuarioId): ?Pedido`
Executa toda a transação de forma **atômica**:
1. `BEGIN`;
2. Gera o número: `SELECT COALESCE(MAX(numero), 2046) + 1` (com `FOR UPDATE` no último pedido para evitar corrida);
3. Valida cada item: produto ativo, estoque suficiente (**`quando compra`** — re-leitura dentro da transação) — se falhar faz `ROLLBACK` e retorna null;
4. `INSERT` pedido (status `realizado`) + `INSERT` de cada item (`produto_nome` = snapshot);
5. `UPDATE produtos SET quantidade_estoque = quantidade_estoque - qtd` para cada item;
6. `COMMIT`.

O controller em caso de `null` usa `flash('pedido_erro', ...)` e mostra a revisão com os produtos indisponíveis.

### `NotificacaoService`
- `emailNovoPedido(Pedido, itens)` — PHPMailer (SMTP). **Guarda de ambiente:** se `MAIL_HOST` estiver vazio/placeholder (`seudominio`), **pula o envio** (sem travar o fluxo em dev). Falha ao enviar não derruba o pedido (`try/catch`).
- `linkWhatsApp(Pedido, itens, numeroDestino)` — monta `https://wa.me/NUMERO?text=MENSAGEM` com `rawurlencode` + `%0A` para quebras de linha. Lê o número da tabela `configuracoes`; se ausente, usa `WHATSAPP_FALLBACK` do `.env`.

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

> O acompanhamento de **estoque baixo/zerado** não fica no dashboard: está na página própria `/admin/estoque` (`Produto::paginarEstoque()`, filtros busca/categoria/situação, ajuste rápido via AJAX confirmado em modal — `LIMITE_BAIXO_ESTOQUE = 5`).

> Compatibilidade PHP 8.3: o projeto NÃO usa `mb_str_contains` (inexistente) — usar `str_contains`. Dados dos gráficos são sempre escapados/convertidos para int no PHP antes do `json_encode`.

---

## 8. Segurança

| Ameaça | Mitigação |
|---|---|
| SQL Injection | PDO prepared statements (sem concatenação) |
| XSS | `e()` (htmlspecialchars) em toda saída dinâmica nas views |
| CSRF | `csrf_field()` nos forms + endpoints AJAX validam token; admin valida no construtor |
| Sessão | cookie `httponly` + `samesite=Lax`; `session_regenerate_id` no login |
| Upload | validação de mime (imagem), extensão permitida e tamanho ≤ 2MB; nome gerado com `uniqid` |
| Traversal | upload salvo em `public/uploads` com nome aleatório |
| Brute force | sem bloqueio dedicado (melhoria futura) — senha bcrypt |

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
2. **Controller**: `app/Controllers/Admin/RelatorioController.php` com `use App\Core\Controller;` e, no `__construct`, `parent::__construct(true)` para exigir papel **admin** (o parâmetro `true` ativa a proteção).
3. **Model** (se precisar de consulta): estender `App\Core\Model` definindo `$tabela`.
4. **View**: `views/admin/relatorios/index.php` usando o layout
   ```php
   require __DIR__ . '/../../layouts/admin_header.php';
   // ... conteúdo usando e($var) ...
   require __DIR__ . '/../../layouts/admin_footer.php';
   ```
5. Chamar pelo controller: `$this->viewAdmin('admin/relatorios/index', ['relatorios' => $rows]);`

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
- `public/service-worker.js` — cache estático (shell) de assets CSS/JS;
- Registro é feito no `escola_footer.php` (somente onde o PWA faz sentido — área da escola);
- Instalação exige HTTPS (ou localhost);

## 14. Testes

Não há framework de testes. Existem scripts temporários de validação HTTP via cURL (`teste_fluxo.php`, `teste_admin.php`) fora do projeto, em
`C:\Users\Felipe\AppData\Local\Temp\opencode\`. Eles exercitam: login → catálogo (busca/filtro/paginação) → carrinho → revisão → confirmação → sucesso/#/wa.me; e o painel admin completo.

---

## 15. Melhorias futuras (roadmap)

- Restauração de estoque ao **cancelar** pedido (a exclusão de pedido já restaura);
- Impressão/exportação (PDF/CSV) dos pedidos;
- Notificação push via PWA;
- Categorias com subcategorias;
- Rate limiting/brute-force protection no login.