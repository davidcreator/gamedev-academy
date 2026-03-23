# Getting Started

Este guia e para quem vai manter o projeto, nao apenas usa-lo.

## Suba o projeto em ate 15 minutos

```bash
composer install
php scripts/install-db.php
php scripts/seed-demo-data.php
```

Depois:

1. aponte o servidor web para a raiz do repositorio;
2. confirme que `config.php` esta configurado;
3. entre com o admin demo em `/login.php`.

Credenciais de desenvolvimento:

- `admin@gamedev.test` / `admin123`
- `aluno@gamedev.test` / `123456`

## Ordem recomendada de leitura

1. `README.md`
2. `docs/PROJECT-HANDOFF.md`
3. `docs/ARCHITECTURE.md`
4. `docs/INSTALLATION.md`
5. `docs/CONTRIBUTING.md`

Depois disso, va para o codigo conforme o tipo de tarefa.

## Onde comecar no codigo

### Fluxos publicos

- `index.php`: landing page atual.
- `courses.php`: listagem publica de cursos.
- `course.php`: pagina publica do curso e matricula.
- `news.php` e `news-detail.php`: noticias.
- `login.php`, `register.php`, `forgot-password.php`, `reset-password.php`: autenticacao.

### Fluxos do aluno

- `learn.php`: player de estudo, progresso, quiz, certificado e gamificacao.
- `user/index.php`: dashboard.
- `user/profile.php`: perfil.
- `user/courses.php`: cursos do aluno.
- `user/achievements.php` e `user/leaderboard.php`: engagement.

### Fluxos administrativos

- `admin/index.php`: dashboard.
- `admin/courses/courses.php`: cursos.
- `admin/modules/modules.php`: modulos.
- `admin/lessons/`: licoes.
- `admin/news/`: noticias.
- `admin/users/`: usuarios.
- `admin/settings/settings.php`: configuracoes.
- `admin/finance/index.php`: relatorios e despesas.

### Servicos centrais

- `classes/Auth.php`
- `classes/Course.php`
- `classes/User.php`
- `classes/News.php`
- `classes/Gamification.php`
- `classes/CertificateService.php`
- `classes/FinanceService.php`
- `classes/Setting.php`

## Como navegar sem se perder

O runtime principal ainda e baseado em paginas PHP server-rendered. Na pratica:

- a pagina recebe a request;
- carrega `config/database.php`, `includes/config.php` e helpers;
- instancia classes em `classes/`;
- acessa o banco via `classes/Database.php` ou PDO;
- renderiza HTML direto.

Existe tambem uma camada parcial em `core/`, `routes/` e `views/`, mas ela nao e o caminho principal de execucao da plataforma hoje.

## Tarefas comuns

### Adicionar ou corrigir um curso

1. verifique a tela publica em `course.php`;
2. revise regras em `classes/Course.php`;
3. teste no admin em `admin/courses/courses.php`;
4. se houver impacto de schema, atualize os dois caminhos de instalacao.

### Mexer em licoes e progresso

1. revise `learn.php`;
2. confirme comportamento em `classes/Course.php` e `classes/Gamification.php`;
3. teste criacao/edicao em `admin/lessons/`;
4. valide se certificado continua sendo emitido ao concluir.

### Ajustar noticias

1. revise `classes/News.php`;
2. teste `admin/news/`;
3. confira `news.php` e `news-detail.php`.

### Trabalhar no financeiro

1. confirme se o banco tem `financial_expenses`;
2. se nao tiver, rode `php scripts/install-business-finance-upgrade.php`;
3. revise `classes/FinanceService.php` e `admin/finance/index.php`.

## Riscos conhecidos antes de abrir PR

- `install/sql/create_tables.php` e `install/database/schema.sql` coexistem.
- `includes/functions.php` e `includes/config.php` carregam compatibilidade e codigo legado.
- `routes.php`, `routes/*.php`, `core/` e `views/` nao sao a fonte principal de verdade do runtime atual.
- O repositorio ainda nao tem uma suite forte de testes automatizados.

## Primeiras contribuicoes boas para a comunidade

- normalizar texto e mensagens do instalador;
- cobrir fluxos criticos com testes;
- reduzir divergencias entre schema web e schema CLI;
- limpar duplicidade de helpers e configuracao;
- melhorar observabilidade de erros em producao;
- revisar docs especificos de modulo quando houver mudanca de comportamento.

## Regras praticas para nao quebrar o projeto

- nao refatore arquitetura e comportamento de negocio no mesmo PR;
- mantenha entrypoints e URLs legadas enquanto o front atual depender delas;
- descreva sempre o passo a passo de teste manual na entrega;
- quando alterar banco, atualize schema, installer e seed quando necessario.
