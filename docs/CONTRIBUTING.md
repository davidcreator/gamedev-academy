# Contribuindo

Obrigado por ajudar a manter a GameDev Academy viva para a comunidade.

## Antes de abrir qualquer PR

Leia nesta ordem:

1. `README.md`
2. `docs/PROJECT-HANDOFF.md`
3. `docs/ARCHITECTURE.md`
4. `docs/INSTALLATION.md`

Esses arquivos registram o estado real do projeto e evitam mudancas baseadas em suposicoes antigas.

## Setup local

```bash
composer install
php scripts/install-db.php
php scripts/seed-demo-data.php
```

Garanta tambem:

- `config.php` valido na raiz;
- servidor web apontando para a raiz do projeto;
- permissao de escrita em `uploads`, `cache` e `logs`.

## Como escolher uma boa contribuicao

Boas primeiras contribuicoes:

- documentacao;
- pequenos bugs de interface no admin e no front;
- mensagens do instalador;
- correcao de consultas SQL pontuais;
- melhoria de seed e scripts de desenvolvimento;
- testes para fluxos criticos.

Contribuicoes que pedem alinhamento maior:

- refactor de autenticacao;
- alteracao de schema;
- mudanca de rotas e URLs publicas;
- migracao de `classes/` para `core/`;
- alteracao de instalacao e configuracao.

## Fluxo de trabalho recomendado

1. Crie uma branch curta e focada.
2. Entenda o fluxo real no browser antes de editar.
3. Implemente a mudanca em pequenos passos.
4. Teste manualmente o comportamento afetado.
5. Atualize a documentacao se o comportamento mudar.
6. Abra um PR com contexto, risco e checklist de validacao.

## Convencoes de branch e commit

Sugestao de branch:

- `fix/nome-curto`
- `feat/nome-curto`
- `docs/nome-curto`
- `refactor/nome-curto`

Conventional Commits sao recomendados:

- `feat: adiciona filtro por categoria no admin`
- `fix: corrige emissao de certificado em curso gratuito`
- `docs: atualiza fluxo de instalacao`

## Regras tecnicas importantes

### 1. Nao assuma que `core/` e o runtime principal

Hoje o fluxo em uso esta majoritariamente em:

- paginas da raiz;
- `admin/`;
- `user/`;
- `classes/`;
- `includes/`.

### 2. Alteracao de banco precisa cuidar de dois caminhos

Enquanto o projeto mantiver schema duplicado, atualize:

- `install/sql/create_tables.php`
- `install/database/schema.sql`

E, quando fizer sentido, revise tambem:

- `scripts/seed-demo-data.php`
- documentacao relevante.

### 3. Auth, install e config exigem teste extra

Se o PR tocar:

- `classes/Auth.php`
- `includes/config.php`
- `config/database.php`
- `install/`
- `forgot-password.php`
- `reset-password.php`

teste pelo menos:

- login;
- logout;
- cadastro;
- redirecionamento de usuario nao autenticado;
- acesso admin;
- fluxo de instalacao, se aplicavel.

### 4. Mantenha saida HTML escapada

Sempre que exibir dados dinamicos, prefira helpers como:

- `escape()`
- `e()`
- `esc()`

### 5. Use queries parametrizadas

Novas consultas devem usar prepared statements ou os wrappers existentes do projeto. Evite interpolar entrada do usuario diretamente em SQL.

## Testes e verificacao

Hoje o repositorio nao possui uma suite automatizada forte o suficiente para substituir validacao manual. Por isso, cada PR deve vir com um checklist de verificacao.

Inclua no PR algo como:

- fluxo testado;
- passos executados;
- dados usados;
- resultado esperado;
- resultado obtido.

Quando fizer sentido, rode tambem:

```bash
php -l caminho/do/arquivo.php
```

## O que descrever no PR

- problema original;
- abordagem escolhida;
- arquivos principais alterados;
- risco de regressao;
- passos de teste manual;
- impacto em banco, instalacao ou configuracao.

## O que evitar

- PR gigante misturando refactor, feature e schema;
- apagar codigo legado sem confirmar que ele nao esta em uso;
- mudar URLs publicas sem plano de compatibilidade;
- mexer so em um dos schemas;
- criar dependencia nova sem explicar necessidade operacional.

## Checklist de entrega

- codigo ou docs alterados com foco claro;
- comportamento validado manualmente;
- documentacao atualizada quando necessario;
- schema duplo revisado, se houver impacto em banco;
- sem secrets ou credenciais reais no commit.

## Seguranca e responsabilidade

Para bugs com potencial de seguranca, nao abra issue publica primeiro. Siga `docs/SECURITY.md`.
