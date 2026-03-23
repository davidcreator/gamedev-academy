# Superficie da Aplicacao

O projeto nao expoe hoje uma API REST publica, versionada e estavel. A interface principal e feita por paginas PHP server-rendered e alguns endpoints de formulario ou instalacao.

Este documento registra a superficie real que a comunidade precisa manter.

## Endpoints publicos

- `/`
  - landing page atual.
- `/courses.php`
  - catalogo publico de cursos.
- `/course.php?slug={slug}`
  - detalhe do curso, grade e matricula.
- `/news.php`
  - listagem publica de noticias.
- `/news-detail.php?id={slug-ou-id}`
  - detalhe de noticia.
- `/login.php`
  - login.
- `/register.php`
  - cadastro.
- `/logout.php`
  - encerramento de sessao.
- `/forgot-password.php`
  - solicitacao de recuperacao.
- `/reset-password.php`
  - redefinicao de senha.

## Endpoints da area do aluno

- `/user/`
  - dashboard.
- `/user/profile.php`
  - perfil.
- `/user/courses.php`
  - cursos do aluno.
- `/user/achievements.php`
  - conquistas.
- `/user/leaderboard.php`
  - ranking.
- `/user/settings.php`
  - configuracoes do aluno.
- `/learn.php?course={slug}&lesson={id}`
  - ambiente de estudo.

## Endpoints administrativos

- `/admin/`
  - dashboard principal.
- `/admin/users/users.php`
  - usuarios.
- `/admin/courses/courses.php`
  - cursos.
- `/admin/modules/modules.php?course_id={id}`
  - modulos por curso.
- `/admin/lessons/lessons-list.php`
  - listagem geral de licoes.
- `/admin/news/news-list.php`
  - noticias.
- `/admin/settings/settings.php`
  - configuracoes.
- `/admin/finance/index.php`
  - indicadores e despesas.

## Acoes POST mais importantes

### Matricula em curso

- endpoint: `/course.php?slug={slug}`
- payload:
  - `action=enroll`
- efeito:
  - cria matricula ou confirma matricula existente;
  - redireciona para `learn.php`.

### Conclusao de licao

- endpoint: `/learn.php?course={slug}&lesson={id}`
- payload:
  - `action=complete_lesson`
  - `lesson_id`
- efeito:
  - atualiza `lesson_progress`;
  - recalcula progresso em `enrollments`;
  - adiciona XP e moedas;
  - pode concluir o curso e emitir certificado.

### Envio de quiz

- endpoint: `/learn.php?course={slug}&lesson={id}`
- payload:
  - `action=submit_quiz`
  - `lesson_id`
  - `answers[...]`
- efeito:
  - corrige respostas;
  - grava score;
  - recompensa XP proporcional;
  - recalcula progresso.

### Registro de despesa

- endpoint: `/admin/finance/index.php`
- payload:
  - `action=add_expense`
  - `title`, `category`, `amount`, `expense_date`, `status`, `vendor_name`, `notes`
- efeito:
  - grava item em `financial_expenses`.

## Instalador e endpoints auxiliares

- `/install/`
  - fluxo web de instalacao.
- `/install/ajax/...`
  - validacoes e criacao de estrutura durante o instalador.

## Servicos que funcionam como API interna

### `classes/Auth.php`

- `register(array $data)`
- `login(string $identifier, string $password, bool $remember = false)`
- `logout()`
- `isLoggedIn()`
- `getCurrentUser()`
- `requireLogin()`
- `requireAdmin()`

### `classes/Course.php`

- `find(int $id)`
- `findBySlug(string $slug)`
- `getAll(bool $publishedOnly = true, int $limit = 50)`
- `getFeatured(int $limit = 6)`
- `getModules(int $courseId)`
- `getLessons(int $moduleId)`
- `isEnrolled(int $userId, int $courseId)`
- `enroll(int $userId, int $courseId)`
- `getUserCourses(int $userId)`

### `classes/User.php`

- `find(int $id)`
- `findByEmail(string $email)`
- `getAll(int $limit = 50, int $offset = 0)`
- `getStats(int $userId)`
- `getLeaderboard(int $limit = 10)`

### `classes/News.php`

- `getAll($page = 1, $perPage = 12, $category = null, $search = null)`
- `getLatest($limit = 6)`
- `getFeatured($limit = 3)`
- `getRelated($newsId, $categoryId, $limit = 4)`
- `getById($id)`
- `create($data)`
- `update($id, $data)`
- `delete($id)`

### `classes/Gamification.php`

- `addXP(...)`
- `addCoins(...)`
- `checkLevelUp(...)`
- `checkAchievements(...)`
- `getUserAchievements(...)`
- `getXPHistory(...)`
- `getProgressToNextLevel(...)`
- `getWeeklyLeaderboard(...)`

### `classes/CertificateService.php`

- `issueForEnrollment(int $enrollmentId)`

### `classes/FinanceService.php`

- `getOverview(...)`
- `getRevenueBreakdown(...)`
- `getInstructorPayoutPreview(...)`
- `getRecentExpenses(...)`
- `getExpenseCategorySummary(...)`
- `createExpense(...)`

## O que ainda nao deve ser tratado como API estavel

- `routes.php`
- `routes/api.php`
- `routes/web.php`
- `routes/admin.php`
- `core/`
- `views/`

Esses artefatos existem, mas nao representam sozinhos a interface realmente usada pela aplicacao hoje.

## Recomendacao para futuras evolucoes

Se a comunidade decidir expor uma API publica de verdade, o caminho mais seguro e:

1. mapear o comportamento atual por fluxo;
2. extrair casos de uso dos entrypoints;
3. criar endpoints REST apenas depois de haver testes para login, matricula, progresso e admin.
