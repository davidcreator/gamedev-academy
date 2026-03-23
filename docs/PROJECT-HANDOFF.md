# Project Handoff

## Snapshot

- Data de consolidacao: 2026-03-23
- Objetivo: deixar o projeto pronto para continuidade pela comunidade, com documentacao clara sobre o que esta estavel, o que e legado e quais sao as prioridades tecnicas.

## O que esta funcional hoje

- landing page publica com destaques, ranking e noticias;
- catalogo de cursos e pagina publica de detalhe;
- autenticacao, cadastro e recuperacao de senha;
- area do aluno com dashboard, perfil, cursos, ranking e conquistas;
- ambiente de estudo com progresso, quiz, XP, moedas e certificado;
- painel administrativo para usuarios, cursos, modulos, licoes, noticias, configuracoes e financeiro;
- instalador web;
- scripts de seed e upgrade para desenvolvimento.

## Fonte principal de verdade hoje

### Runtime da aplicacao

- paginas PHP na raiz;
- `admin/`;
- `user/`;
- `classes/`;
- `includes/`.

### Instalacao

- web: `install/index.php` + `install/sql/create_tables.php`
- CLI: `scripts/install-db.php` + `install/database/schema.sql`

### Setup de desenvolvimento

- `scripts/seed-demo-data.php`
- `scripts/install-business-finance-upgrade.php`

### Documentacao principal

- `README.md`
- `docs/INSTALLATION.md`
- `docs/GETTING-STARTED.md`
- `docs/ARCHITECTURE.md`
- `docs/API.md`

## Partes do repositorio que precisam de leitura cuidadosa

### Arquitetura parcial em paralelo

Estas pastas existem, mas nao sao o centro do runtime atual:

- `core/`
- `routes/`
- `views/`
- `bootstrap.php`
- `routes.php`

Elas sugerem um caminho de modernizacao, mas ainda nao substituem a aplicacao server-rendered baseada em entrypoints PHP.

### Schema duplicado

Hoje ha duas trilhas de schema que precisam andar juntas:

- `install/sql/create_tables.php`
- `install/database/schema.sql`

Enquanto isso nao for unificado, toda mudanca de banco deve considerar ambos os arquivos e, quando necessario, o seed demo.

### Bootstrap legado

`includes/config.php` e `includes/functions.php` concentram bastante compatibilidade. Eles sustentam o runtime atual, mas tambem acumulam responsabilidade demais para o medio prazo.

## Limites conhecidos

- ainda nao existe suite robusta de testes automatizados;
- alguns textos do instalador ainda refletem uma estrutura antiga com `/public`;
- ha coexistencia de estilos de acesso a banco e de bootstrap;
- a API publica nao esta consolidada;
- a observabilidade de erros ainda depende bastante de validacao manual e logs locais.

## Prioridades recomendadas para a comunidade

### Prioridade 1: confiabilidade

- adicionar testes para login, matricula, progresso e admin;
- revisar hardening de uploads, sessao e instalador;
- padronizar validacao e escaping nos fluxos mais sensiveis.

### Prioridade 2: banco de dados

- escolher um unico source of truth para schema;
- alinhar instalador web, import CLI e seed;
- documentar migrations futuras com mais rigor.

### Prioridade 3: arquitetura

- extrair regras de negocio dos entrypoints grandes, comecando por `learn.php`;
- reduzir dependencia de helpers globais;
- decidir se `core/` sera concluido ou aposentado.

### Prioridade 4: operacao

- criar um checklist de release;
- melhorar logs e visibilidade de falhas;
- preparar CI para lint e verificacoes basicas.

## Sequencia segura para evolucao

1. Cubra o comportamento atual com testes.
2. Reduza duplicidade de schema e bootstrap.
3. Refatore por fluxo de negocio, nao por pasta.
4. So depois remova camadas legadas ou paralelas.

## Acordos recomendados para novos mantenedores

- PR pequeno e focado;
- sem misturar refactor estrutural com nova feature em uma unica entrega;
- toda mudanca de banco deve citar impacto em instalacao, seed e docs;
- qualquer alteracao em auth, install, upload ou config deve vir com roteiro de teste manual.

## Rotas e fluxos mais criticos para nao quebrar

- `/login.php`
- `/register.php`
- `/course.php`
- `/learn.php`
- `/admin/`
- `/install/`

Esses fluxos merecem validacao sempre que houver alteracao em sessao, banco, bootstrap ou permissao.

## Sinal verde para handoff

O projeto esta pronto para continuidade comunitaria porque:

- ha uma base funcional navegavel de ponta a ponta;
- existe instalacao web e caminho CLI para dev;
- as regras de negocio principais estao mapeadas em classes claras;
- a documentacao principal agora descreve o estado real do codigo.

O proximo salto de maturidade nao depende de reescrever o produto, e sim de consolidar o que ja esta funcional com testes, schema unico e endurecimento operacional.
