# Changelog

O projeto segue a estrutura do Keep a Changelog e usa SemVer como referencia.

Observacao: o historico anterior a 23/03/2026 nao foi reconstruido em detalhe a partir do Git. A partir desta data, a documentacao de mudancas passa a registrar o estado do projeto de forma mais fiel.

## [Unreleased]

### Added

- `docs/PROJECT-HANDOFF.md` com snapshot tecnico para continuidade comunitaria.

### Changed

- `README.md` reescrito para refletir a plataforma real.
- `docs/INSTALLATION.md` atualizado com fluxo web, fluxo manual, seed e upgrade financeiro.
- `docs/GETTING-STARTED.md` atualizado para onboarding de mantenedores.
- `docs/ARCHITECTURE.md` atualizado com a arquitetura atual baseada em paginas PHP e servicos em `classes/`.
- `docs/API.md` refeito para documentar a superficie HTTP e os servicos internos que existem hoje.
- `docs/CONTRIBUTING.md`, `docs/SECURITY.md` e `docs/FAQ.md` alinhados ao estado atual do repositorio.

## [1.0.0]

### Added

- base funcional da plataforma com area publica, autenticacao, area do aluno e painel administrativo;
- instalador web;
- gestao de cursos, modulos, licoes e noticias;
- gamificacao com XP, niveis e leaderboard;
- emissao de certificados;
- modulo financeiro e plano de negocio associado.

### Notes

- esta entrada representa a baseline historica do codigo existente no repositorio, sem detalhamento retroativo de cada iteracao intermediaria.
