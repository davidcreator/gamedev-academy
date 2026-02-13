<?php
$db = Database::getInstance();
$courseModel = new Course();

$moduleId = intval($_GET['module_id'] ?? 0);
$courseId = intval($_GET['course_id'] ?? 0);

$module = $db->fetch("SELECT * FROM modules WHERE id = ?", [$moduleId]);
$course = $courseModel->find($courseId);
if (!$module || !$course) {
    flash('error', 'Módulo ou curso não encontrado.');
    redirect(url('admin/courses.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $data = [
            'module_id' => $moduleId,
            'title' => trim($_POST['title'] ?? ''),
            'content_type' => $_POST['content_type'] ?? 'text',
            'order_index' => intval($_POST['order_index'] ?? 0),
            'xp_reward' => intval($_POST['xp_reward'] ?? 10),
            'coin_reward' => intval($_POST['coin_reward'] ?? 1),
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
            'is_free_preview' => isset($_POST['is_free_preview']) ? 1 : 0,
            'video_url' => trim($_POST['video_url'] ?? ''),
            'video_provider' => $_POST['video_provider'] ?? 'youtube',
            'duration_minutes' => intval($_POST['duration_minutes'] ?? 0),
        ];
        if (!$data['title']) {
            flash('error', 'Informe o título da lição.');
        } else {
            $db->insert('lessons', $data);
            flash('success', 'Lição criada com sucesso!');
        }
        redirect(url('admin/lessons.php?module_id=' . $moduleId . '&course_id=' . $courseId));
    }

    if ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'content_type' => $_POST['content_type'] ?? 'text',
                'order_index' => intval($_POST['order_index'] ?? 0),
                'xp_reward' => intval($_POST['xp_reward'] ?? 10),
                'coin_reward' => intval($_POST['coin_reward'] ?? 1),
                'is_published' => isset($_POST['is_published']) ? 1 : 0,
                'is_free_preview' => isset($_POST['is_free_preview']) ? 1 : 0,
                'video_url' => trim($_POST['video_url'] ?? ''),
                'video_provider' => $_POST['video_provider'] ?? 'youtube',
                'duration_minutes' => intval($_POST['duration_minutes'] ?? 0),
            ];
            $db->update('lessons', $data, 'id = :id', ['id' => $id]);
            flash('success', 'Lição atualizada!');
        }
        redirect(url('admin/lessons.php?module_id=' . $moduleId . '&course_id=' . $courseId));
    }

    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $db->delete('lessons', 'id = :id', ['id' => $id]);
            flash('success', 'Lição removida!');
        }
        redirect(url('admin/lessons.php?module_id=' . $moduleId . '&course_id=' . $courseId));
    }
}

$lessons = $courseModel->getLessons($moduleId);
?>