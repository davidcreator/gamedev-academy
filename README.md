# GameDev Academy

Plataforma open-source de ensino para desenvolvimento de jogos, com catalogo publico de cursos, area do aluno, painel administrativo, gamificacao, emissao de certificados e instalador web.

Este repositorio foi consolidado para handoff comunitario em 23/03/2026. A partir deste ponto, a documentacao principal passa a refletir o estado real da aplicacao e os principais pontos de continuidade tecnica.

## O que a aplicacao entrega hoje

- Paginas publicas para landing page, catalogo de cursos, detalhes do curso, noticias e autenticacao.
- Area do aluno com dashboard, perfil, ranking, conquistas e ambiente de estudo por curso.
- Painel administrativo com gestao de usuarios, cursos, modulos, licoes, noticias, configuracoes e financeiro.
- Instalador web em cinco etapas para configurar banco, gerar `config.php` e criar o primeiro admin.
- Scripts de apoio para importar schema, popular dados demo e aplicar upgrade do modulo financeiro.
- Base de gamificacao com XP, moedas, niveis, streaks, leaderboard semanal e conquista de certificados.

## Stack principal

- PHP 8.1+ como requisito real de desenvolvimento e runtime.
- MySQL ou MariaDB.
- Composer para dependencias PHP e assets do EditorJS.
- Frontend server-rendered em PHP, sem etapa obrigatoria de build em Node.
- WAMP/Apache funciona bem para desenvolvimento local, mas a aplicacao tambem pode rodar em outros ambientes PHP tradicionais.

## Inicio rapido

### Fluxo recomendado

```bash
git clone https://github.com/davidcreator/gamedev-academy.git
cd gamedev-academy
composer install
```

1. Aponte o servidor web para a raiz do projeto.
2. Garanta permissao de escrita na raiz e nas pastas `config`, `uploads`, `cache` e `logs`.
3. Acesse `/install/` no navegador e conclua o instalador.
4. Entre com a conta de administrador criada na etapa 4 do instalador.

### Fluxo manual ou para desenvolvimento local

```bash
composer install
php scripts/install-db.php
php scripts/seed-demo-data.php
```

Depois do seed, os acessos de desenvolvimento sao:

- Admin: `admin@gamedev.test` / `admin123`
- Aluno: `aluno@gamedev.test` / `123456`

Use esses usuarios somente em ambiente local.

## Scripts uteis

- `composer install`: instala dependencias e prepara assets do EditorJS.
- `php scripts/install-db.php`: importa `install/database/schema.sql`.
- `php scripts/seed-demo-data.php`: cria usuarios, cursos, modulos, licoes e ranking de exemplo.
- `php scripts/install-business-finance-upgrade.php`: habilita a tabela `financial_expenses` e configuracoes extras para o painel financeiro em bancos antigos.

## Estrutura do projeto

```text
gamedev-academy/
|-- admin/                  Painel administrativo
|-- assets/                 CSS, JS, imagens e libs frontend
|-- classes/                Servicos e modelos em uso no fluxo principal
|-- config/                 Bootstrap e conexao de banco
|-- core/                   Estrutura MVC parcial/experimental
|-- docs/                   Documentacao principal e handoff
|-- includes/               Bootstrap legado, helpers e mailer
|-- install/                Instalador web e SQL auxiliar
|-- routes/                 Definicoes de rotas nao integradas ao runtime principal
|-- scripts/                Scripts CLI de setup, import e manutencao
|-- user/                   Area do aluno
|-- views/                  Views de uma camada MVC parcial
|-- course.php              Detalhe publico do curso
|-- courses.php             Catalogo publico
|-- index.php               Landing page atual
|-- learn.php               Ambiente de estudo e progresso
|-- login.php               Login
|-- news.php                Lista publica de noticias
`-- register.php            Cadastro
```

## Fonte de verdade para a comunidade

- Runtime atual: paginas PHP na raiz, `admin/`, `user/`, `classes/` e `includes/`.
- Instalador web: `install/index.php` + `install/sql/create_tables.php`.
- Instalacao via script: `scripts/install-db.php` + `install/database/schema.sql`.
- Handoff tecnico: `docs/PROJECT-HANDOFF.md`.

Observacao importante: hoje existem dois caminhos de schema em paralelo. Enquanto nao houver consolidacao, qualquer alteracao estrutural no banco deve considerar tanto `install/sql/create_tables.php` quanto `install/database/schema.sql`.

## Mapa da documentacao

- `docs/INSTALLATION.md`: instalacao detalhada.
- `docs/GETTING-STARTED.md`: onboarding tecnico rapido para novos mantenedores.
- `docs/ARCHITECTURE.md`: arquitetura atual, camadas e pontos de tensao.
- `docs/API.md`: superficie HTTP e servicos expostos pelo projeto.
- `docs/CONTRIBUTING.md`: como contribuir com seguranca para a continuidade do projeto.
- `docs/FAQ.md`: respostas para as duvidas mais comuns de setup e manutencao.
- `docs/SECURITY.md`: politica e checklist de seguranca.
- `docs/PROJECT-HANDOFF.md`: estado atual, limites conhecidos e roadmap recomendado.
- `docs/BUSINESS_FINANCE_PLAN.md`: racional do modulo financeiro e de certificados.
- `docs/LESSONS.md`, `docs/NEWS.md` e `docs/EDITORJS.md`: referencias especificas de modulo.

## Estado atual do projeto

O sistema esta funcional e organizado o suficiente para continuidade comunitaria, mas ainda convive com algumas camadas legadas e experimentais no mesmo repositorio. Os principais pontos de atencao para proximas iteracoes sao:

- consolidar a arquitetura entre `classes/` e `core/`;
- unificar o source of truth do schema;
- cobrir fluxos criticos com testes automatizados;
- endurecer a operacao de producao, especialmente upload, seguranca do instalador e configuracao de ambiente.

Esses pontos estao detalhados em `docs/PROJECT-HANDOFF.md`.

## Licenca

Este projeto usa a licenca MIT. Consulte `LICENSE.md`.
