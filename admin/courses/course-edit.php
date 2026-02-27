<?php
// admin/course-edit.php - Criar/Editar Curso

$pageTitle = 'Editar Curso';
include '../includes/header.php';

$db = Database::getInstance();
$courseModel = new Course();

$id = intval($_GET['id'] ?? 0);
$course = $id ? $courseModel->find($id) : null;
$isEdit = $course !== null;
$pageTitle = $isEdit ? 'Editar Curso' : 'Novo Curso';

$categories = $courseModel->getCategories();
$instructors = $db->fetchAll("SELECT id, full_name FROM users WHERE role IN ('instructor','admin') ORDER BY full_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'category_id' => intval($_POST['category_id'] ?? 0) ?: null,
        'instructor_id' => intval($_POST['instructor_id'] ?? 0) ?: null,
        'short_description' => trim($_POST['short_description'] ?? ''),
        'description' => $_POST['description'] ?? '',
        'level' => $_POST['level'] ?? 'beginner',
        'language' => $_POST['language'] ?? 'pt-BR',
        'duration_hours' => intval($_POST['duration_hours'] ?? 0),
        'xp_reward' => intval($_POST['xp_reward'] ?? 100),
        'coin_reward' => intval($_POST['coin_reward'] ?? 10),
        'is_free' => isset($_POST['is_free']) ? 1 : 0,
        'price' => floatval($_POST['price'] ?? 0),
        'is_published' => isset($_POST['is_published']) ? 1 : 0,
        'thumbnail' => trim($_POST['thumbnail'] ?? ''),
        'image' => trim($_POST['cover_image'] ?? ''),
        'preview_video' => trim($_POST['preview_video'] ?? ''),
        'trailer_url' => trim($_POST['trailer_url'] ?? ''),
    ];

    if (!$data['title']) {
        flash('error', 'Informe o título do curso.');
    } elseif (!$data['instructor_id']) {
        flash('error', 'Selecione um instrutor para o curso.');
    } else {
        try {
            if ($isEdit) {
                $courseModel->update($id, $data);
                flash('success', 'Curso atualizado com sucesso!');
            } else {
                $newId = $courseModel->create($data);
                flash('success', 'Curso criado com sucesso!');
                redirect(url('admin/courses/course-edit.php?id=' . $newId));
            }
        } catch (PDOException $e) {
            flash('error', 'Erro ao salvar no banco de dados: ' . $e->getMessage());
        }
    }
}

// Renderizar estilos do EditorJS
EditorJSLoader::renderStyles();
?>

<?= showFlashMessages() ?>

<div class="wrapper-container">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <!-- <li class="breadcrumb-item"><a href="<?= url('admin/dashboard.php') ?>">Dashboard</a></li> -->
                    <!-- <li class="btn btn-secondary breadcrumb-item"><a href="<?= url('admin/courses/courses.php') ?>">Cursos</a></li> -->
                    <!-- <li class="btn btn-secondary breadcrumb-item active"><?= $isEdit ? 'Editar' : 'Novo' ?></li> -->
                </ol>
            </nav>
            <div class="d-flex gap-2">
                <a href="<?= url('admin/courses/courses.php') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>← Voltar
                </a>
                <?php if ($isEdit): ?>
                    <a href="<?= url('admin/modules/modules.php?course_id=' . $course['id']) ?>" class="btn btn-secondary">
                        <i class="fas fa-cubes me-1"></i> Módulos
                    </a>
                    <a href="<?= url('courses/course.php?slug=' . $course['slug']) ?>" target="_blank" class="btn btn-primary">
                        <i class="fas fa-eye me-1"></i> Ver no Site
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <form method="POST" id="courseForm">
            <div class="row">
                <!-- Coluna Principal -->
                <div class="col-lg-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h3 class="p-5 mb-0 fw-bold"><i class="fas fa-info-circle me-2 text-primary"></i>Informações do Curso</h3>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Título do Curso <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control form-control-lg border-2" value="<?= escape($course['title'] ?? '') ?>" required placeholder="Ex: Masterizando Unity 3D">
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Descrição Curta</label>
                                <textarea name="short_description" class="form-control" rows="2" placeholder="Um resumo atrativo para as listagens..."><?= escape($course['short_description'] ?? '') ?></textarea>
                            </div>

                            <div class="mb-0">
                                <label class="form-label fw-bold">Descrição Completa</label>
                                <div id="editorjs" class="border rounded bg-light" style="min-height: 400px;"></div>
                                <textarea name="description" id="content" class="d-none"><?= $course['description'] ?? '' ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-photo-video me-2 text-primary"></i>Mídia e Recursos</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">URL da Thumbnail</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-image"></i></span>
                                        <input type="text" name="thumbnail" class="form-control" value="<?= escape($course['thumbnail'] ?? '') ?>" placeholder="https://...">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Imagem de Capa</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-desktop"></i></span>
                                        <input type="text" name="cover_image" class="form-control" value="<?= escape($course['cover_image'] ?? $course['image'] ?? '') ?>" placeholder="https://...">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Vídeo de Prévia (URL)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-play-circle"></i></span>
                                        <input type="text" name="preview_video" class="form-control" value="<?= escape($course['preview_video'] ?? '') ?>" placeholder="YouTube/Vimeo URL">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">URL do Trailer</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-film"></i></span>
                                        <input type="text" name="trailer_url" class="form-control" value="<?= escape($course['trailer_url'] ?? '') ?>" placeholder="URL do trailer">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Barra Lateral -->
                <div class="col-lg-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-cog me-2 text-primary"></i>Configurações</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <label class="form-label fw-bold d-block">Status e Visibilidade</label>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_published" id="is_published" <?= ($course['is_published'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_published">Publicado</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_free" id="is_free" <?= ($course['is_free'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_free">Curso Grátis</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Categoria</label>
                                <select name="category_id" class="form-select border-2">
                                    <option value="">Selecione uma categoria</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= ($course['category_id'] ?? null) == $cat['id'] ? 'selected' : '' ?>>
                                            <?= escape($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Instrutor</label>
                                <select name="instructor_id" class="form-select border-2">
                                    <option value="">Selecione um instrutor</option>
                                    <?php foreach ($instructors as $ins): ?>
                                        <option value="<?= $ins['id'] ?>" <?= ($course['instructor_id'] ?? null) == $ins['id'] ? 'selected' : '' ?>>
                                            <?= escape($ins['full_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Nível</label>
                                    <select name="level" class="form-select border-2">
                                        <?php
                                            $diffs = ['beginner' => 'Iniciante', 'intermediate' => 'Intermediário', 'advanced' => 'Avançado', 'expert' => 'Especialista'];
                                            foreach ($diffs as $val => $label):
                                        ?>
                                            <option value="<?= $val ?>" <?= ($course['level'] ?? 'beginner') === $val ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Idioma</label>
                                    <select name="language" class="form-select border-2">
                                        <option value="pt-BR" <?= ($course['language'] ?? 'pt-BR') === 'pt-BR' ? 'selected' : '' ?>>Português</option>
                                        <option value="en" <?= ($course['language'] ?? 'pt-BR') === 'en' ? 'selected' : '' ?>>Inglês</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-coins me-2 text-primary"></i>Valores e Recompensas</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Preço do Curso</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold text-success">R$</span>
                                    <input type="number" name="price" class="form-control border-2" step="0.01" min="0" value="<?= floatval($course['price'] ?? 0) ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-bold">Duração (h)</label>
                                    <input type="number" name="duration_hours" class="form-control border-2" min="0" value="<?= intval($course['duration_hours'] ?? 0) ?>">
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-bold">XP Reward</label>
                                    <input type="number" name="xp_reward" class="form-control border-2" min="0" value="<?= intval($course['xp_reward'] ?? 100) ?>">
                                </div>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-bold">Coin Reward</label>
                                <input type="number" name="coin_reward" class="form-control border-2" min="0" value="<?= intval($course['coin_reward'] ?? 10) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm sticky-top" style="top: 20px; z-index: 100;">
                        <div class="card-body p-3">
                            <button type="submit" class="btn btn-primary w-100 btn-lg fw-bold shadow-sm">
                                <i class="fas fa-save me-2"></i> <?= $isEdit ? 'Salvar Alterações' : 'Criar Curso' ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
// Carregar Scripts do EditorJS
EditorJSLoader::renderScripts();
EditorJSLoader::init($course['description'] ?? '', 'editorjs', 'content');
?>

<?php include '../includes/footer.php'; ?>