# FAQ

## O que exatamente e este projeto hoje?

Uma plataforma PHP server-rendered para cursos de desenvolvimento de jogos, com area publica, area do aluno, painel administrativo, instalador web, gamificacao e modulo financeiro opcional.

## O projeto usa framework?

Nao como runtime principal. Existe uma camada parcial em `core/`, `routes/` e `views/`, mas a aplicacao em uso hoje e guiada por paginas PHP na raiz, em `admin/` e em `user/`, com servicos em `classes/`.

## Qual versao de PHP devo usar?

Use PHP 8.1 ou superior. Embora parte do instalador ainda aceite 7.4, o baseline real do projeto atual vem do `composer.json` e do codigo que roda em producao.

## Por que o sistema redireciona para `/install/`?

Porque `config.php` esta ausente, vazio ou invalido. O arquivo `includes/install-state.php` faz essa verificacao antes de carregar o sistema.

## O `config.php` vazio no repositorio e normal?

Sim. Ele funciona como placeholder local e deve ser gerado pelo instalador ou criado manualmente a partir de `config dist.php`.

## Preciso de Node.js para rodar o projeto?

Nao para o runtime normal. Os assets importantes ja sao versionados no repositorio e o projeto se apoia em Composer, nao em uma pipeline Node obrigatoria.

## Qual e a forma mais rapida de subir um ambiente de desenvolvimento?

```bash
composer install
php scripts/install-db.php
php scripts/seed-demo-data.php
```

Depois configure o servidor web para a raiz do projeto.

## Quais sao as credenciais demo?

- Admin: `admin@gamedev.test` / `admin123`
- Aluno: `aluno@gamedev.test` / `123456`

Use apenas em ambiente local.

## Qual schema eu altero quando mudo banco?

Hoje, os dois:

- `install/sql/create_tables.php`
- `install/database/schema.sql`

A comunidade ainda precisa unificar essa duplicidade.

## Existe API REST pronta para integrar app mobile?

Nao de forma estavel. `routes/api.php` existe, mas nao representa uma API de producao consolidada. A superficie principal e a propria aplicacao web server-rendered.

## Onde eu mexo para ajustar cursos, modulos e licoes?

- regra de negocio: `classes/Course.php`
- tela publica: `courses.php`, `course.php`, `learn.php`
- admin: `admin/courses/`, `admin/modules/`, `admin/lessons/`

## Onde eu mexo para ajustar noticias?

- regra de negocio: `classes/News.php`
- admin: `admin/news/`
- front: `news.php` e `news-detail.php`

## Como habilito o financeiro completo em bancos antigos?

Rode:

```bash
php scripts/install-business-finance-upgrade.php
```

## O projeto tem testes automatizados?

Ainda nao em nivel suficiente para confiar apenas neles. Hoje a manutencao depende bastante de teste manual e verificacao dirigida por fluxo.

## Por que existem `core/`, `routes/` e `views/` se a aplicacao roda por paginas?

Porque o repositorio convive com uma camada parcial de modernizacao. Ela pode ser reutilizada no futuro, mas nao deve ser assumida como runtime principal sem confirmacao.

## O que devo fazer antes de publicar em producao?

- desligar `DEBUG_MODE`;
- proteger `config.php`;
- proteger ou remover `/install/`;
- revisar permissao de `uploads`, `cache` e `logs`;
- configurar HTTPS e cookies seguros;
- revisar estrategia de backup.
