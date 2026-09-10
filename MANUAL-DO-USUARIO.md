# MANUAL DO USUÁRIO — Central de Pedidos Escolares

Bem-vindo à **Central de Pedidos Escolares**, o sistema da Secretaria de Educação para o registro de pedidos de materiais escolares.

Este manual explica o funcionamento do sistema para os três perfis de usuário:

- **Escola** — entra, monta o pedido e envia para a secretaria.
- **Administrador (secretaria)** — acessa tudo: pedidos, produtos, estoque, categorias, usuários e escolas.
- **Supervisor (secretaria)** — ajuda no atendimento (pedidos, produtos, estoque, categorias e configurações), mas **não** acessa o módulo de usuários nem cadastra escolas.

> Importante: o sistema **não tem preço nem pagamento**. Ele serve apenas para *requisitar* materiais de forma organizada.

---

## 1. Como entrar no sistema

### Escola

1. Abra o endereço do sistema no navegador (celular, tablet ou computador).
2. Você verá a tela de **Entrar** (Usuário / e-mail e Senha).
3. Digite o usuário e a senha fornecidos pela secretaria.
4. Clique em **Entrar**.

💡 *Esqueceu a senha? Clique em **Esqueci a senha** na própria tela de login (veja o item 2.8).*

💡 *Contas de escola são criadas apenas pela secretaria — não existe cadastro pelo próprio usuário.*

### Administrador e Supervisor

1. Acesse o endereço do sistema adicionando `/admin` no final: `https://SEU_DOMINIO/admin`.
2. Informe o e-mail e a senha de administrador (ou supervisor).
3. Clique em **Entrar**.

💡 *Aqui também existe o link **Esqueci a senha**, válido para usuários do painel (administradores e supervisores).*

---

## 2. O sistema pela visão da ESCOLA

### 2.1 Catálogo de materiais

Após entrar, a escola vê o **catálogo de produtos** (estilo loja, em grade de cartões).

Cada cartão mostra:
- Imagem do produto (ou um ícone padrão);
- Nome do produto;
- Categoria;
- Descrição (inclui unidade/medida, ex.: “UN”, “CX c/ 50 un”, “500g”);
- Campo de quantidade;
- Botão **Adicionar**.

### 2.2 Buscar e filtrar

No topo do catálogo há:
- **Barra de busca** — digite o nome (ou trecho) do produto;
- **Filtro por categoria** — escolha “Todas as categorias” ou uma específica;
- Botão **Buscar** e botão **Limpar** (para remover a busca/filtro).

Os resultados são paginados em **20 produtos por página** (navegação “Anterior / números / Próxima” no fim da lista).

### 2.3 Disposição do produto (estoque)

- **Disponível**: aparece o campo de quantidade e o botão **Adicionar**.
- **Indisponível** (estoque zero): o produto continua visível, mas com a etiqueta **Indisponível** e sem botão de adicionar.

> O número “Disponível: X” mostra quanto há em estoque naquele momento.

### 2.4 Montando o pedido (carrinho)

1. Informe a quantidade no cartão do produto.
2. Clique em **Adicionar** — aparecerá a mensagem *“Produto adicionado, carrinho atualizado.”*
3. No canto inferior da tela há o botão **🛒 Meu pedido — N itens**, sempre visível (inclusive no celular).
4. Clique nele para abrir o carrinho.

No carrinho você pode:
- Alterar a quantidade de um item (campo + **Atualizar**);
- Remover um item (botão **✕**);
- **Continuar escolhendo** (voltar ao catálogo);
- **Finalizar pedido** quando estiver pronto.

> O sistema **não aceita** quantidade maior que o estoque disponível.

### 2.5 Revisão e confirmação do pedido

1. Na tela **Confirme seu pedido**, confira a escola e a lista de produtos com as quantidades.
2. Se precisar corrigir, clique em **← Voltar e corrigir**.
3. Se estiver tudo certo, clique em **Confirmar pedido ✓**.

### 2.6 O que acontece ao confirmar (em ordem)

1. O estoque de **todos** os produtos é validado novamente;
2. O pedido é **salvo** no banco;
3. O **estoque é debitado**;
4. É gerado o **número do pedido** (ex.: **#2048**);
5. Um **e-mail automático** é enviado para a secretaria;
6. O sistema abre a **mensagem pronta do WhatsApp** para você enviar à secretaria (envio manual);
7. Aparece a **tela de sucesso**.

> Se algum item ficou indisponível ou sem estoque nesse momento final, você verá uma mensagem amigável e poderá ajustar as quantidades antes de tentar de novo.

### 2.7 Tela de sucesso e WhatsApp

A tela de sucesso mostra o número do pedido. Habitualmente há um botão **💬 Enviar mensagem no WhatsApp** que abre o WhatsApp (app ou web) com a mensagem pronta, já preenchida:

```text
Olá! A escola [Nome da Escola] registrou o pedido #2048 pelo sistema.

Produtos:
- 10x Caderno universitário
- 5x Caixa de lápis de cor
- 2x Resma de papel A4

Aguardamos a confirmação/processamento.
```

Você apenas verifica e envia. Isso garante a formalização do pedido mesmo que o e-mail demore.

### 2.8 Histórico de pedidos

No menu lateral da escola há **Histórico de pedidos** (ou **Meus pedidos**):

- Lista os pedidos da escola com número, data, quantidade de itens e status;
- Você pode **filtrar por status** (todos / realizado / em andamento / concluído / cancelado);
- Clique em **Ver pedido** para acompanhar os itens e o status atual do pedido.

> O status muda quando a secretaria atualiza o pedido no painel — o histórico reflete isso automaticamente.

### 2.9 Esqueci a senha

Na tela de login, clique em **Esqueci a senha** e digite:

1. O **e-mail** cadastrado da escola;
2. A **nova senha** desejada (mínimo de 4 caracteres);
3. A **confirmação** da nova senha.

Se o e-mail estiver correto, a senha é atualizada na hora e você volta a entrar normalmente. Esse recurso só funciona com o e-mail **exato** cadastrado no sistema.

---

## 3. O sistema pela visão do ADMINISTRADOR e do SUPERVISOR (secretaria)

URL: `https://SEU_DOMINIO/admin`

Ambos entram pelo mesmo endereço `/admin`. A **única diferença**: o **supervisor não vê** os itens **Escolas** e **Usuários do sistema** no menu lateral (essas telas são exclusivas do administrador).

### 3.1 Dashboard

Resumo visual com:
- Contadores de pedidos: **Realizados**, **Em processamento**, **Concluídos** e **Cancelados**;
- **Produtos mais pedidos**;
- **Escolas que mais pedem**.

> O estoque baixo/zerado não fica no painel: veja a página **Estoque** (item 3.3).

### 3.2 Pedidos (`/admin/pedidos`)

- Lista todos os pedidos com número, escola, quantidade de itens, status e data;
- **Filtro por status** no topo;
- Confira o pedido completo (itens) na opção **Ver pedido**.

**Status de um pedido:**

| Status | Significado |
|---|---|
| 🟡 Realizado | Recém-criado pela escola |
| 🔵 Em andamento | A secretaria está processando/separando |
| ✅ Concluído | Entregue/atendido |
| 🔴 Cancelado | Cancelado (disponível a qualquer momento) |

O fluxo normal é: **realizado → em andamento → concluído**. O cancelamento pode ser feito a qualquer momento.

Para mudar o status: abra o pedido, escolha o novo status e clique em **Atualizar status**.

### 3.3 Estoque (`/admin/estoque`)

- Lista apenas os produtos com **estoque baixo (5 ou menos) ou zerado**, com busca, filtro por categoria e filtro por situação (abaixo do limite / zerado);
- Na própria linha é possível **corrigir o estoque** digitando o valor e clicando em **OK** — útil para reposição;
- Cada zero recebe o destaque 🟥 **Sem estoque**.

### 3.4 Produtos (`/admin/produtos`)

- Lista paginada (**20 por página**) com **busca** (nome/descrição) e **filtro por categoria**;
- Na própria lista é possível **ajustar o estoque** digitando o número e clicando em **OK**;
- Botão **+ Novo produto** abre o formulário de cadastro com:
  - Categoria (obrigatória);
  - Nome (obrigatório);
  - **Descrição** (obrigatória — deve conter a unidade/medida, ex.: “UN”, “CX c/ 50 un”, “500g”);
  - Estoque inicial;
  - Status ativo/inativo;
  - Imagem (opcional, até 2MB).
- Botões **Editar** e **Excluir** em cada linha;
- Formulário possui slug automático (campo opcional). Produto **inativo** não aparece no catálogo da escola.

### 3.5 Categorias (`/admin/categorias`)

- Cadastro de nome, slug, status (ativa/inativa) e ordem;
- Categoria **inativa não aparece** no catálogo da escola;
- Não é possível excluir categoria que possua produtos vinculados.

### 3.6 Escolas (`/admin/usuarios`)

- O administrador cadastra as escolas: nome da escola, responsável, e-mail/login, senha, status;
- Existe botão **Gerar** para sugerir uma senha aleatória;
- Ao editar, a senha só é alterada se for preenchida;
- **Status inativo** impede o login da escola;
- Contas de **administrador e supervisor** não ficam nesta lista — são gerenciadas em **Usuários do sistema** (item 3.7).

### 3.7 Usuários do sistema (administradores e supervisores)

Acessível pelo menu **Configurações → Usuários do sistema** *(somente administrador)*:

- Lista os usuários do painel com o **tipo** (Administrador ou Supervisor);
- **Adicionar usuário do sistema**: informe nome, e-mail, senha e escolha o tipo (Administrador ou Supervisor);
- O **Supervisor** recebe acesso ao painel (pedidos, produtos, estoque, categorias e configurações), mas **não** ao módulo de usuários/escolas;
- Recomenda-se manter **poucos usuários do tipo administrador**; para o dia a dia, crie supervisores.

### 3.8 Configurações (`/admin/configuracoes`)

- **Número de WhatsApp da secretaria**: somente números, com código do país e DDD (ex.: `5511998877665`). É usado para montar o link `wa.me` da tela de sucesso.
- **E-mail da secretaria**: é quem recebe os avisos automáticos de novo pedido.

Esses dados ficam no banco e são usados dinamicamente (nunca fixos no código).

---

## 4. Perguntas frequentes

**O pedido é criado antes ou depois do WhatsApp?**
Antes. Pedido, e-mail e WhatsApp são eventos de um pedido que **já existe** no sistema. O WhatsApp é só uma mensagem pronta para você formalizar o aviso.

**Posso passar do estoque disponível?**
Não. Em três momentos o sistema confere o estoque: ao exibir o produto, ao adicionar ao carrinho e novamente na confirmação.

**Posso alterar a quantidade depois de adicionar?**
Sim, no carrinho (“Atualizar”) e também voltando da revisão (“Voltar e corrigir”).

**Esqueci minha senha.**
Na tela de login há o link **Esqueci a senha**: informe o e-mail cadastrado e defina uma nova senha (mínimo de 4 caracteres). Vale para escolas e para usuários do painel (administrador/supervisor). Se preferir, peça à secretaria que redefina a sua senha em **Escolas** → **Editar**.

**Qual a diferença entre administrador e supervisor?**
O supervisor usa o mesmo painel `/admin`, mas **não** vê os menus **Escolas** e **Usuários do sistema** (exclusivos do administrador).

**Como a secretaria é avisada de um novo pedido?**
Por e-mail automático e, adicionalmente, pela mensagem do WhatsApp que a escola envia.

**Preciso de internet para fazer pedido?**
Sim. O sistema também pode ser instalado no celular (“Adicionar à tela inicial”), o que deixa o acesso mais rápido, mas ainda exige internet.