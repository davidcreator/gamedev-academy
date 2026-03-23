# Instalacao

Este guia descreve a instalacao real da aplicacao como ela existe hoje no repositorio.

## Requisitos reais

- PHP 8.1 ou superior.
- MySQL 8+ ou MariaDB equivalente.
- Composer.
- Servidor web apontando para a raiz do projeto.
- Extensoes PHP obrigatorias: `pdo`, `pdo_mysql`, `mysqli`, `json`, `mbstring`, `openssl`, `session`.
- Extensoes recomendadas: `curl`, `gd`, `zip`, `fileinfo`, `xml`, `intl`.

Observacao: o instalador ainda valida PHP 7.4 como minimo em parte do codigo, mas o `composer.json` e o runtime atual exigem tratar PHP 8.1 como baseline oficial.

## Pastas com escrita

Garanta permissao de escrita para:

- raiz do projeto, para o instalador criar `config.php`;
- `config/`;
- `uploads/`, `uploads/avatars/`, `uploads/courses/`, `uploads/news/`;
- `cache/`;
- `logs/`.

## Fluxo recomendado: instalador web

```bash
git clone https://github.com/davidcreator/gamedev-academy.git
cd gamedev-academy
composer install
```

1. Crie um banco vazio no MySQL/MariaDB.
2. Configure o host virtual ou document root para a raiz do projeto.
3. Acesse `http://seu-host/install/`.
4. Conclua as cinco etapas:
   - validacao de requisitos;
   - conexao com banco;
   - criacao das tabelas;
   - configuracao do primeiro admin;
   - finalizacao.
5. Entre no painel com a conta criada.

### O que o instalador faz

- valida extensoes, permissoes e configuracao basica do servidor;
- grava o arquivo `config.php` na raiz;
- cria a estrutura principal do banco a partir de `install/sql/create_tables.php`;
- cria a conta administrativa inicial.

## Fluxo manual ou de desenvolvimento local

Quando voce quiser subir o projeto sem passar pela interface do instalador, use o template de configuracao e os scripts CLI.

### 1. Criar `config.php`

Use `config dist.php` como base:

```bash
copy "config dist.php" config.php
```

Depois ajuste pelo menos:

- `DB_HOST`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`
- `SITE_URL`
- `SITE_NAME`
- `TIMEZONE`
- `DEBUG_MODE`

### 2. Importar o schema

```bash
php scripts/install-db.php
```

Esse script usa `install/database/schema.sql`.

### 3. Popular dados de desenvolvimento

```bash
php scripts/seed-demo-data.php
```

Credenciais criadas pelo seed:

- Admin: `admin@gamedev.test` / `admin123`
- Aluno: `aluno@gamedev.test` / `123456`

Esses dados sao exclusivos para ambiente local.

## Upgrade opcional do modulo financeiro

Se o banco foi criado em uma versao anterior do projeto e ainda nao possui a tabela de despesas, rode:

```bash
php scripts/install-business-finance-upgrade.php
```

O script:

- cria `financial_expenses` caso ela nao exista;
- registra chaves de negocio e financeiro em `settings`.

## Observacoes sobre schema

Hoje existem dois caminhos de instalacao de banco:

- `install/sql/create_tables.php`, usado pelo instalador web;
- `install/database/schema.sql`, usado por `scripts/install-db.php`.

Eles precisam permanecer alinhados ate a comunidade unificar o source of truth do banco. Se voce alterar estrutura de tabela, revisao de indices ou seeds essenciais, atualize os dois caminhos.

## Checklist apos instalar

- confirme que `config.php` foi gerado com valores corretos;
- acesse `/`, `/login.php`, `/admin/` e `/user/`;
- valide upload em um fluxo administrativo, se ele fizer parte da sua entrega;
- se for producao, bloqueie ou remova o acesso a `/install/`;
- deixe `DEBUG_MODE` desligado em producao;
- revise permissao de escrita apenas nas pastas necessarias.

## Problemas comuns

### A aplicacao redireciona sempre para `/install/`

Normalmente significa uma destas situacoes:

- `config.php` nao existe;
- `config.php` existe, mas esta vazio;
- alguma constante obrigatoria nao foi definida;
- o arquivo existe, mas a URL base (`SITE_URL`) esta invalida.

### `composer install` falha nos assets do EditorJS

Verifique acesso ao repositorio do Composer e se o ambiente consegue baixar dependencias. O projeto usa `asset-packagist.org` no `composer.json`.

### Erro de conexao com o banco

Confirme:

- host, porta, usuario e senha;
- permissao do usuario MySQL para criar/alterar tabelas;
- extensao `pdo_mysql` ativa.

### O instalador conclui, mas a tela final fala em `/public`

Esse texto ainda reflete uma estrutura antiga. O runtime atual responde a partir da raiz do projeto, com entrypoints como `index.php`, `course.php`, `learn.php`, `admin/` e `user/`.

### Uploads ou logs nao funcionam

Revise permissoes de:

- `uploads/`
- `cache/`
- `logs/`

## Ambiente recomendado para a comunidade

Para contribuicoes e manutencao, o fluxo mais pratico hoje e:

```bash
composer install
php scripts/install-db.php
php scripts/seed-demo-data.php
```

Esse caminho reduz dependencia da interface do instalador e facilita reproduzir bugs rapidamente.
