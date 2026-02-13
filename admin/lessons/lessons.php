<?php
// admin/lessons/lessons.php - Gerenciar Lições de um Módulo

$pageTitle = 'Gerenciar Lições do Módulo';
include '../includes/header.php';
require_once '../modules/module-lessons.php';
?>

<?= showFlashMessages() ?>
<!-- Funções -->
 <div class="d-flex justify-beteween align-center mb-8">
    <div class="d-flex justify-beteween align-right gap-2">
        <a href="<?= url('admin/modules/modules.php?course_id=' . $courseId) ?>" class="btn btn-secondary">← Voltar </a>
        <h2><?= escape($course['title']) ?> &nbsp;.&nbsp; <?= escape($module['title']) ?>&nbsp;&nbsp;</h2>
    </div>
    <div class="d-flex justify-beteween align-right gap-2">
        <button class="btn btn-success" onclick="document.getElementById('create-lesson').removeAttribute('hidden')">Nova Lição</button>
    </div>
 </div>
 <br>
 <!-- Fim das Funções -->
 <!-- Criar Lição -->
 <div id="create-lesson" class="card mb-4">
    <div class="card-body">
        <h3 class="card-title">Criar Lição</h3>
        <!-- Inicio do Formulário -->
        <form method="POST" class="grid-cols-2 gap-2">
            <input type="hidden" name="action" value="create">
            <label>Título
                <input type="text" name="title" class="form-control" required>
            </label>            
            <div class="d-flex cols-2 gap-10px">
                <label>Tipo de Conteúdo
                <select name="content_type" class="form-control">
                    <?php
                    $types = ['text'=>'Texto','video'=>'Vídeo','quiz'=>'Quiz','exercise'=>'Exercício','project'=>'Projeto','live'=>'Live','download'=>'Download'];
                    foreach ($types as $k=>$v): ?>
                        <option value="<?= $k ?>"><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
             <label>Ordem
                    <input type="number" name="order_index" class="form-control" value="<?= count($lessons) ?>">
                </label>
                <label>XP
                    <input type="number" name="xp_reward" class="form-control" value="10">
                </label>
                <label>Moedas
                    <input type="number" name="coin_reward" class="form-control" value="1">
                </label>
                <label>Vídeo URL
                    <input type="text" name="video_url" class="form-control">
                </label>
                <label>Provedor
                    <select name="video_provider" class="form-control">
                        <?php foreach (['youtube','vimeo','cloudflare','bunny','self'] as $p): ?>
                            <option value="<?= $p ?>"><?= ucfirst($p) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Duração (min)
                    <input type="number" name="duration_minutes" class="form-control" value="0">
                </label>                               
            </div>
            <div class="d-flex gap-2">
                <label class="d-flex align-center gap-1">
                    <input type="checkbox" name="is_published"> Publicado
                </label>
                <label class="d-flex align-center gap-1">
                    <input type="checkbox" name="is_free_preview"> Prévia grátis
                </label>
                <label class="d-flex align-center gap-1">
                    <button class="btn btn-success" type="submit">Salvar</button>
                </label>
                <label class="d-flex align-center gap-1">
                    <button class="btn btn-secondary" type="button" onclick="this.closest('#create-lesson').setAttribute('hidden','')">Cancelar</button>
                </label>
            </div>            
        </form>
        <!-- Fim do Formulário -->
    </div>
 </div>
 <!-- Fim da Criar Lição -->
