# Arquitetura Atual

## Visao geral

O projeto roda hoje como uma aplicacao PHP server-rendered com entrypoints em paginas `.php` espalhadas na raiz, em `admin/` e em `user/`.

O fluxo principal em producao nao depende de um framework completo. Em vez disso, ele combina:

- paginas controlador;
- classes de servico em `classes/`;
- bootstrap e helpers legados em `includes/`;
- conexao PDO e helper de banco em `classes/Database.php`.

## Camadas em uso no runtime principal

### 1. Entry points HTTP

Sao os arquivos que recebem a request diretamente:

- `index.php`
- `courses.php`
- `course.php`
- `learn.php`
- `news.php`
- `login.php`, `register.php`, `logout.php`
- `forgot-password.php`, `reset-password.php`
- `admin/...`
- `user/...`

### 2. Bootstrap e ambiente

- `includes/install-state.php`: garante que existe uma instalacao valida.
- `config/database.php`: carrega constantes e cria `$pdo`.
- `includes/config.php`: bootstrap de compatibilidade para o runtime legado.
- `includes/functions.php`: helpers de URL, sessao, HTML, flash, seguranca e utilitarios.

### 3. Servicos e modelos

Os comportamentos de negocio mais usados hoje ficam em `classes/`:

- `Auth`: login, logout, sessao, remember me, regras de acesso.
- `Course`: catalogo, slug, matricula, modulos e licoes.
- `User`: perfil, contagem, leaderboard, niveis.
- `News`: listagem, destaque, busca, criacao e edicao de noticias.
- `Gamification`: XP, moedas, achievements e leaderboard semanal.
- `CertificateService`: elegibilidade e emissao de certificados.
- `FinanceService`: indicadores financeiros, repasse e despesas.
- `Setting`: leitura de configuracoes dinamicas.

### 4. Persistencia

Ha dois estilos de acesso a dados convivendo:

- `classes/Database.php`: wrapper simples sobre PDO, muito usado nas paginas;
- uso direto de `$pdo` e prepared statements em varios fluxos.

## Fluxo de request mais importante: `learn.php`

`learn.php` resume bem a arquitetura atual:

1. carrega bootstrap e exige usuario autenticado;
2. busca o curso por slug;
3. garante matricula, inclusive autoenroll em curso gratis;
4. responde a acoes `complete_lesson` e `submit_quiz`;
5. atualiza progresso em `lesson_progress` e `enrollments`;
6. dispara XP, moedas, leaderboard e certificado;
7. renderiza a pagina de estudo diretamente.

Esse fluxo toca autenticacao, cursos, progresso, gamificacao e certificados no mesmo entrypoint. Por isso, qualquer refactor nessa area deve ser incremental e bem testado.

## Organizacao por dominios

### Catalogo e conteudo

- cursos: `courses.php`, `course.php`, `classes/Course.php`, `admin/courses/`, `admin/modules/`, `admin/lessons/`
- noticias: `news.php`, `news-detail.php`, `classes/News.php`, `admin/news/`

### Conta e acesso

- `login.php`, `register.php`, `logout.php`
- `forgot-password.php`, `reset-password.php`
- `classes/Auth.php`
- `includes/auth/`

### Experiencia do aluno

- `user/`
- `learn.php`
- `classes/Gamification.php`
- `classes/CertificateService.php`

### Administracao e operacao

- `admin/`
- `classes/Setting.php`
- `classes/FinanceService.php`

### Instalacao e setup

- `install/index.php`
- `install/includes/`
- `install/steps/`
- `install/sql/create_tables.php`
- `scripts/install-db.php`
- `scripts/seed-demo-data.php`

## Partes do repositorio que existem, mas nao sao a fonte principal de verdade

### `core/`, `routes/` e `views/`

Essas pastas sugerem uma migracao ou experimento de MVC/Router. Hoje elas nao comandam o runtime principal da plataforma. Use essas pastas com cautela e so depois de confirmar que a request real passa por elas.

### `bootstrap.php`

Tambem aponta para uma estrutura mais moderna, mas nao e o bootstrap dominante do fluxo atual.

### `routes.php`

Existe um mapa simples de rotas, mas ele nao representa sozinho a superficie real da aplicacao em uso.

## Banco de dados

Hoje existem duas trilhas de schema:

- instalador web: `install/sql/create_tables.php`;
- import CLI: `install/database/schema.sql`.

Essa duplicidade e o principal ponto estrutural que a comunidade deve resolver no medio prazo.

## Tensoes arquiteturais conhecidas

- codigo legado e codigo parcialmente modernizado coexistem na mesma base;
- helpers e configuracoes ainda concentram muita responsabilidade;
- fluxos importantes misturam controller, regra de negocio e renderizacao no mesmo arquivo;
- ainda nao existe uma malha consistente de testes automatizados;
- alguns textos do instalador e alguns artefatos do repositorio ainda refletem estruturas antigas, como `/public`.

## Diretriz de evolucao recomendada

1. Definir o source of truth do schema.
2. Cobrir login, matricula, progresso e admin com testes.
3. Extrair casos de uso dos entrypoints grandes, comecando por `learn.php`.
4. Consolidar gradualmente a arquitetura, em vez de tentar reescrever tudo de uma vez.
5. Remover codigo paralelo somente quando houver substituto validado em runtime.

## Regra pratica para contribuidores

Se voce esta em duvida sobre onde mexer, priorize:

- arquivos da raiz;
- `admin/`;
- `user/`;
- `classes/`;
- `includes/`.

Trate `core/`, `routes/`, `views/` e `bootstrap.php` como contexto adicional, nao como a referencia principal do comportamento atual.
