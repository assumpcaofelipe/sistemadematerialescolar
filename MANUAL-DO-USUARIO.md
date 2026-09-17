# MANUAL DO USUÁRIO — Central de Pedidos Escolares

Bem-vindo à **Central de Pedidos Escolares**, o sistema da Secretaria de Educação para o registro de pedidos de materiais escolares.

Este manual explica o funcionamento do sistema para os três perfis de usuário:

- **Escola** — entra, monta o pedido e envia para a secretaria.
- **Administrador (secretaria)** — acessa tudo: pedidos, produtos, estoque, categorias, usuários e escolas.
- **Supervisor (secretaria)** — ajuda no atendimento (pedidos, produtos, estoque e categorias), mas **não** acessa o módulo de usuários/escolas nem as configurações da secretaria.

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

### 2.3 Quantidade e estoque

A escola **não vê** a quantidade em estoque. Todo produto ativo aparece no catálogo com o campo de quantidade e o botão **Adicionar**, independentemente de haver estoque naquele momento.

O controle de estoque é feito apenas pela secretaria: ao **concluir** o pedido o sistema dá baixa nos produtos e, se não houver saldo suficiente, a conclusão é bloqueada (veja o item 3.2).

### 2.4 Montando o pedido (carrinho)

1. Informe a quantidade no cartão do produto.
2. Clique em **Adicionar** — aparecerá a mensagem *“Produto adicionado, carrinho atualizado.”* e o contador **Carrinho** do menu lateral aumenta.
3. Clique em **Carrinho** no menu lateral (o número amarelo ao lado mostra quantos itens há no pedido).
4. Pronto: você está no carrinho.

No carrinho você pode:
- Alterar a quantidade de um item (campo + **Atualizar**);
- Remover um item (botão **✕**);
- **Continuar escolhendo** (voltar ao catálogo);
- **Finalizar pedido** quando estiver pronto.

> A escola pode pedir qualquer quantidade. O estoque é conferido apenas no momento em que a secretaria **conclui** o pedido (item 3.2).

### 2.5 Revisão e confirmação do pedido

1. Na tela **Confirme seu pedido**, confira a escola e a lista de produtos com as quantidades.
2. Se precisar corrigir, clique em **← Voltar e corrigir**.
3. Se estiver tudo certo, clique em **Confirmar pedido ✓**.

### 2.6 O que acontece ao confirmar (em ordem)

1. O pedido é **salvo** no banco de dados (status **realizado**);
2. É gerado o **número do pedido** (ex.: **#2048**);
3. Um **e-mail automático** é enviado para a secretaria;
4. Aparece a **tela de sucesso**, com o botão que abre a **mensagem pronta do WhatsApp** (envio manual).

> O estoque **não** é alterado neste momento. A baixa acontece somente quando a secretaria marca o pedido como **concluído**.

### 2.7 Tela de sucesso e WhatsApp

A tela de sucesso mostra o número do pedido e um botão **💬 Enviar mensagem no WhatsApp** que abre o WhatsApp (app ou web) com a mensagem pronta, já preenchida:

```text
Olá! A escola [Nome da Escola] realizou o pedido #2048 pelo Sistema de Pedidos Escolares.

Produtos solicitados:
• 10x Caderno universitário
• 5x Caixa de lápis de cor
• 2x Resma de papel A4

Aguardamos o recebimento e o processamento do pedido.

Obrigado!
```

Você apenas verifica e envia. Isso garante a formalização do pedido mesmo que o e-mail demore.

> O número que recebe essas mensagens é definido pela secretaria em **Configurações → Secretaria (WhatsApp)** (item 3.8).

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

Ambos entram pelo mesmo endereço `/admin`. A **diferença**: o **supervisor não vê** no menu lateral o grupo **Configurações** (que reúne **Escolas**, **Usuários do sistema** e **Secretaria (WhatsApp)**) — essas telas são exclusivas do administrador.

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

> **Baixa de estoque só na conclusão.** Ao marcar um pedido como **concluído**, o sistema dá baixa (subtrai) das quantidades no estoque. Se algum item estiver **zerado** ou **insuficiente**, a conclusão é bloqueada e aparece a lista dos itens com problema — corrija o estoque em **Produtos → Editar** e tente de novo.
>
> Ao **tirar** um pedido de concluído (ex.: voltar para em andamento ou cancelar), o estoque baixado é **restaurado** automaticamente. Excluir um pedido concluído também devolve o estoque.

### 3.3 Estoque (`/admin/estoque`)

**Tela de consulta** — serve para acompanhar as quantidades e identificar itens baixos/zerados. Aqui **não** se edita.

- Lista **todos os produtos ativos**, com **busca**, **filtro por categoria** e **filtro por situação**:
  - *Todos os produtos (maior para o menor)* — padrão;
  - *Todos os produtos (menor para o maior)* — ajuda a achar os mais críticos;
  - *Estoque baixo (menor que 7)* — itens entre 1 e 6;
  - *Estoque zerado*.
- A tabela mostra **Produto**, **Categoria**, **Estoque** e **Status**, mas os campos estão **desabilitados** (somente leitura).

> Para alterar a quantidade ou o status, use **Produtos → Novo/Editar** (item 3.4). Não há importação/exportação por planilha.

### 3.4 Produtos (`/admin/produtos`)

- Lista paginada (**20 por página**) com **busca** (nome/descrição) e **filtro por categoria**. A listagem **não** mostra estoque nem status;
- Botão **+ Novo produto** abre o formulário de cadastro com:
  - Categoria (obrigatória);
  - Nome (obrigatório);
  - **Descrição** (obrigatória — deve conter a unidade/medida, ex.: “UN”, “CX c/ 50 un”, “500g”);
  - **Estoque** (obrigatório — quantidade disponível para os pedidos);
  - Status ativo/inativo;
  - Imagem (opcional, até 2MB).
- No cadastro/edição é possível alterar **nome, descrição, categoria, estoque, status, slug e imagem**; é aqui que se repõe ou ajusta o estoque;
- Botões **Editar** e **Excluir** em cada linha;
- Formulário possui slug automático (campo opcional). Produto **inativo** não aparece no catálogo da escola.

### 3.5 Categorias (`/admin/categorias`)

- Cadastro de nome, slug, status (ativa/inativa) e ordem;
- Categoria **inativa não aparece** no catálogo da escola;
- Não é possível excluir categoria que possua produtos vinculados.

### 3.6 Escolas (`/admin/usuarios`)

- O administrador cadastra as escolas: **nome da escola**, **e-mail/login** e **senha**;
- Existe botão **Gerar** para sugerir uma senha aleatória de 8 caracteres (e a sugestão também aparece antes de salvar);
- Ao editar, a senha só é alterada se for preenchida (deixar em branco mantém a atual);
- A escola entra no catálogo (área da escola) e o login está disponível imediatamente após o cadastro;
- Contas de **administrador e supervisor** não ficam nesta lista — são gerenciadas em **Usuários do sistema** (item 3.7).

### 3.7 Usuários do sistema (administradores e supervisores)

Acessível pelo menu **Configurações → Usuários do sistema** *(somente administrador)*:

- Lista os usuários do painel com o **tipo** (Administrador ou Supervisor) e o status;
- **Adicionar usuário do sistema**: informe nome, e-mail, senha e escolha o tipo (Administrador ou Supervisor);
- **Editar**: além de nome/e-mail/senha, é possível **trocar a função** do usuário (Administrador ↔ Supervisor) e ativar/desativar o acesso. Se você mudar a **sua própria função**, o sistema encerra a sessão e você entrará novamente com o novo perfil;
- **Excluir**: remove o usuário do painel. Proteções do sistema: você **não pode excluir a si mesmo**, e o sistema **não permite excluir ou desativar o último administrador** (sempre precisa sobrar pelo menos um administrador ativo);
- O **Supervisor** recebe acesso ao painel (pedidos, produtos, estoque e categorias), mas **não** ao grupo **Configurações** (usuários/escolas e secretaria);
- Recomenda-se manter **poucos usuários do tipo administrador**; para o dia a dia, crie supervisores.

### 3.8 Secretaria — WhatsApp (`/admin/configuracoes`)

Acessível pelo menu **Configurações → Secretaria (WhatsApp)** *(somente administrador)*:

- **Número de WhatsApp da secretaria**: informe **apenas dígitos**, no formato **DDI + DDD + número** (ex.: `5511998877665`). É o número que **recebe os pedidos** — usado para montar o link `wa.me` da tela de sucesso.
- O campo mostra o formato esperado no próprio exemplo e aceita somente números (letras e símbolos são descartados ao salvar).

> O **e-mail** que recebe os avisos automáticos de novo pedido é configurado separadamente (não é alterado nesta tela).

---

## 4. Perguntas frequentes

**O pedido é criado antes ou depois do WhatsApp?**
Antes. Pedido, e-mail e WhatsApp são eventos de um pedido que **já existe** no sistema. O WhatsApp é só uma mensagem pronta para você formalizar o aviso.

**Posso passar do estoque disponível?**
Sim. A escola não é bloqueada pelo estoque e não vê a quantidade disponível. O estoque é conferido pela secretaria **na conclusão** do pedido: se faltar, o sistema avisa e não deixa concluir até o estoque ser corrigido.

**Posso alterar a quantidade depois de adicionar?**
Sim, no carrinho (“Atualizar”) e também voltando da revisão (“Voltar e corrigir”).

**Por que apareceu a mensagem de sessão expirada ou de desconexão?**
As sessões duram **24 horas** sem uso. Depois disso, ou ao clicar em **Sair**, o sistema mostra *“Sua sessão expirou”* / *“Você foi desconectado”* e pede o login novamente. Isso é normal e protege os dados.

**Posso estar conectado como escola e como admin ao mesmo tempo?**
Sim. A área da escola e o painel `/admin` usam **sessões independentes**. Você pode manter os dois abertos no mesmo navegador; sair de um **não** desconecta o outro.

**Esqueci minha senha.**
Na tela de login há o link **Esqueci a senha**: informe o e-mail cadastrado e defina uma nova senha (mínimo de 4 caracteres). Vale para escolas e para usuários do painel (administrador/supervisor). Se preferir, peça à secretaria que redefina a sua senha em **Escolas** → **Editar**.

**Qual a diferença entre administrador e supervisor?**
O supervisor usa o mesmo painel `/admin`, mas **não** vê o grupo **Configurações** do menu (Escolas, Usuários do sistema e Secretaria/WhatsApp), que é exclusivo do administrador.

**Como a secretaria é avisada de um novo pedido?**
Por e-mail automático e, adicionalmente, pela mensagem do WhatsApp que a escola envia.

**Preciso de internet para fazer pedido?**
Sim. O sistema também pode ser instalado no celular (“Adicionar à tela inicial”), o que deixa o acesso mais rápido, mas ainda exige internet.