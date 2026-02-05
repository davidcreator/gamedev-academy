<?php
/**
 * GameDev Academy - Perfil do Usuário
 * 
 * Página de perfil com edição de dados, avatar, cursos e progresso
 * @author David Creator
 * @version 2.0.0
 */

// Carrega configurações
require_once __DIR__ . '/../includes/config.php';

// Verifica se o usuário está logado
if (!isLoggedIn()) {
    $_SESSION['flash_messages'][] = ['type' => 'warning', 'message' => 'Você precisa fazer login para acessar seu perfil.'];
    redirect(BASE_URL . 'user/login.php');
}

// ====================================================================
// PROCESSAMENTO DE FORMULÁRIOS
// ====================================================================

$successMessage = '';
$errorMessage = '';
$activeTab = $_GET['tab'] ?? 'overview';

// Busca dados completos do usuário
try {
    $stmt = $pdo->prepare("
        SELECT u.*, 
               COUNT(DISTINCT uc.course_id) as enrolled_courses,
               COUNT(DISTINCT c.id) as completed_courses,
               COALESCE(SUM(up.progress), 0) as total_progress,
               COUNT(DISTINCT up.course_id) as courses_in_progress
        FROM users u
        LEFT JOIN user_courses uc ON u.id = uc.user_id
        LEFT JOIN user_progress up ON u.id = up.user_id
        LEFT JOIN courses c ON uc.course_id = c.id AND uc.status = 'completed'
        WHERE u.id = ?
        GROUP BY u.id
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $userData = $stmt->fetch();
    
    if (!$userData) {
        throw new Exception('Usuário não encontrado');
    }
} catch (Exception $e) {
    error_log("Profile Error: " . $e->getMessage());
    $_SESSION['flash_messages'][] = ['type' => 'error', 'message' => 'Erro ao carregar dados do perfil.'];
    redirect(BASE_URL);
}

// ====================================================================
// 1. ATUALIZAR INFORMAÇÕES BÁSICAS
// ====================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Verifica token CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errorMessage = 'Token de segurança inválido. Por favor, tente novamente.';
    } else {
        
        switch ($_POST['action']) {
            
            case 'update_profile':
                try {
                    $name = sanitize($_POST['name']);
                    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                    $bio = sanitize($_POST['bio']);
                    $website = filter_var($_POST['website'], FILTER_SANITIZE_URL);
                    $github = sanitize($_POST['github']);
                    $linkedin = sanitize($_POST['linkedin']);
                    $twitter = sanitize($_POST['twitter']);
                    
                    // Validações
                    if (empty($name) || strlen($name) < 3) {
                        throw new Exception('Nome deve ter pelo menos 3 caracteres.');
                    }
                    
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception('Email inválido.');
                    }
                    
                    // Verifica se email já existe (outro usuário)
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                    $stmt->execute([$email, $_SESSION['user_id']]);
                    if ($stmt->fetch()) {
                        throw new Exception('Este email já está em uso.');
                    }
                    
                    // Atualiza dados
                    $stmt = $pdo->prepare("
                        UPDATE users SET 
                            name = ?,
                            email = ?,
                            bio = ?,
                            website = ?,
                            github = ?,
                            linkedin = ?,
                            twitter = ?,
                            updated_at = NOW()
                        WHERE id = ?
                    ");
                    
                    $stmt->execute([
                        $name, $email, $bio, $website, 
                        $github, $linkedin, $twitter, 
                        $_SESSION['user_id']
                    ]);
                    
                    $successMessage = 'Perfil atualizado com sucesso!';
                    $userData['name'] = $name;
                    $userData['email'] = $email;
                    $_SESSION['user_name'] = $name;
                    
                } catch (Exception $e) {
                    $errorMessage = $e->getMessage();
                }
                break;
            
            // ====================================================================
            // 2. ATUALIZAR SENHA
            // ====================================================================
            
            case 'update_password':
                try {
                    $currentPassword = $_POST['current_password'];
                    $newPassword = $_POST['new_password'];
                    $confirmPassword = $_POST['confirm_password'];
                    
                    // Validações
                    if (empty($currentPassword) || empty($newPassword)) {
                        throw new Exception('Preencha todos os campos de senha.');
                    }
                    
                    if (strlen($newPassword) < 6) {
                        throw new Exception('A nova senha deve ter pelo menos 6 caracteres.');
                    }
                    
                    if ($newPassword !== $confirmPassword) {
                        throw new Exception('As senhas não coincidem.');
                    }
                    
                    // Verifica senha atual
                    if (!password_verify($currentPassword, $userData['password'])) {
                        throw new Exception('Senha atual incorreta.');
                    }
                    
                    // Atualiza senha
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashedPassword, $_SESSION['user_id']]);
                    
                    $successMessage = 'Senha alterada com sucesso!';
                    $activeTab = 'security';
                    
                } catch (Exception $e) {
                    $errorMessage = $e->getMessage();
                    $activeTab = 'security';
                }
                break;
            
            // ====================================================================
            // 3. UPLOAD DE AVATAR
            // ====================================================================
            
            case 'update_avatar':
                try {
                    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
                        throw new Exception('Erro no upload do arquivo.');
                    }
                    
                    $file = $_FILES['avatar'];
                    $fileName = $file['name'];
                    $fileTmp = $file['tmp_name'];
                    $fileSize = $file['size'];
                    
                    // Validações
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (!in_array($fileExt, $allowedExts)) {
                        throw new Exception('Formato de imagem não permitido. Use: ' . implode(', ', $allowedExts));
                    }
                    
                    if ($fileSize > MAX_UPLOAD_SIZE) {
                        throw new Exception('Arquivo muito grande. Máximo: ' . (MAX_UPLOAD_SIZE / 1024 / 1024) . 'MB');
                    }
                    
                    // Gera nome único
                    $newFileName = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $fileExt;
                    $uploadPath = ROOT_PATH . 'uploads/avatars/' . $newFileName;
                    
                    // Cria diretório se não existir
                    $avatarDir = ROOT_PATH . 'uploads/avatars/';
                    if (!is_dir($avatarDir)) {
                        mkdir($avatarDir, 0755, true);
                    }
                    
                    // Remove avatar antigo se existir
                    if (!empty($userData['avatar']) && file_exists(ROOT_PATH . 'uploads/avatars/' . $userData['avatar'])) {
                        unlink(ROOT_PATH . 'uploads/avatars/' . $userData['avatar']);
                    }
                    
                    // Move arquivo
                    if (!move_uploaded_file($fileTmp, $uploadPath)) {
                        throw new Exception('Erro ao salvar arquivo.');
                    }
                    
                    // Redimensiona imagem (opcional)
                    // resizeImage($uploadPath, 200, 200);
                    
                    // Atualiza banco
                    $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                    $stmt->execute([$newFileName, $_SESSION['user_id']]);
                    
                    $userData['avatar'] = $newFileName;
                    $_SESSION['user_avatar'] = $newFileName;
                    $successMessage = 'Avatar atualizado com sucesso!';
                    
                } catch (Exception $e) {
                    $errorMessage = $e->getMessage();
                }
                break;
            
            // ====================================================================
            // 4. REMOVER AVATAR
            // ====================================================================
            
            case 'remove_avatar':
                try {
                    if (!empty($userData['avatar'])) {
                        $avatarPath = ROOT_PATH . 'uploads/avatars/' . $userData['avatar'];
                        if (file_exists($avatarPath)) {
                            unlink($avatarPath);
                        }
                        
                        $stmt = $pdo->prepare("UPDATE users SET avatar = NULL WHERE id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        
                        $userData['avatar'] = null;
                        unset($_SESSION['user_avatar']);
                        $successMessage = 'Avatar removido com sucesso!';
                    }
                } catch (Exception $e) {
                    $errorMessage = $e->getMessage();
                }
                break;
            
            // ====================================================================
            // 5. CONFIGURAÇÕES DE NOTIFICAÇÃO
            // ====================================================================
            
            case 'update_notifications':
                try {
                    $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
                    $newsletterSubscribed = isset($_POST['newsletter']) ? 1 : 0;
                    $courseUpdates = isset($_POST['course_updates']) ? 1 : 0;
                    
                    $stmt = $pdo->prepare("
                        UPDATE users SET 
                            email_notifications = ?,
                            newsletter_subscribed = ?,
                            course_updates = ?
                        WHERE id = ?
                    ");
                    
                    $stmt->execute([
                        $emailNotifications,
                        $newsletterSubscribed,
                        $courseUpdates,
                        $_SESSION['user_id']
                    ]);
                    
                    $successMessage = 'Configurações de notificação atualizadas!';
                    $activeTab = 'notifications';
                    
                } catch (Exception $e) {
                    $errorMessage = $e->getMessage();
                    $activeTab = 'notifications';
                }
                break;
        }
    }
}

// ====================================================================
// BUSCAR CURSOS DO USUÁRIO
// ====================================================================

try {
    // Cursos em andamento
    $stmt = $pdo->prepare("
        SELECT c.*, uc.enrolled_at, uc.progress, uc.last_accessed,
               COUNT(DISTINCT l.id) as total_lessons,
               COUNT(DISTINCT ul.lesson_id) as completed_lessons
        FROM courses c
        INNER JOIN user_courses uc ON c.id = uc.course_id
        LEFT JOIN lessons l ON c.id = l.course_id
        LEFT JOIN user_lessons ul ON l.id = ul.lesson_id AND ul.user_id = ? AND ul.completed = 1
        WHERE uc.user_id = ? AND uc.status = 'active'
        GROUP BY c.id
        ORDER BY uc.last_accessed DESC
        LIMIT 6
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $activeCourses = $stmt->fetchAll();
    
    // Cursos completados
    $stmt = $pdo->prepare("
        SELECT c.*, uc.completed_at, uc.certificate_code
        FROM courses c
        INNER JOIN user_courses uc ON c.id = uc.course_id
        WHERE uc.user_id = ? AND uc.status = 'completed'
        ORDER BY uc.completed_at DESC
        LIMIT 6
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $completedCourses = $stmt->fetchAll();
    
    // Conquistas/Badges
    $stmt = $pdo->prepare("
        SELECT b.*, ub.earned_at
        FROM badges b
        INNER JOIN user_badges ub ON b.id = ub.badge_id
        WHERE ub.user_id = ?
        ORDER BY ub.earned_at DESC
        LIMIT 12
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $userBadges = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Course fetch error: " . $e->getMessage());
    $activeCourses = [];
    $completedCourses = [];
    $userBadges = [];
}

// ====================================================================
// ESTATÍSTICAS DO USUÁRIO
// ====================================================================

$userStats = [
    'total_courses' => $userData['enrolled_courses'] ?? 0,
    'completed_courses' => $userData['completed_courses'] ?? 0,
    'total_hours' => 0,
    'certificates' => 0,
    'badges' => count($userBadges),
    'streak_days' => 0
];

// Calcula dias de streak
try {
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT DATE(created_at)) as streak
        FROM user_activity
        WHERE user_id = ? 
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    $userStats['streak_days'] = $result['streak'] ?? 0;
} catch (Exception $e) {
    // Ignora erro se tabela não existir
}

// Token CSRF para formulários
$csrfToken = generateCSRFToken();

// Avatar padrão
$avatarUrl = !empty($userData['avatar']) 
    ? UPLOADS_URL . 'avatars/' . $userData['avatar'] 
    : ASSETS_URL . 'images/default-avatar.png';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - <?= SITE_NAME ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #48bb78;
            --danger-color: #f56565;
            --warning-color: #ed8936;
            --dark-color: #2d3748;
            --light-color: #f7fafc;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }

        .profile-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 15px;
        }

        .profile-header {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            object-fit: cover;
        }

        .avatar-upload {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto;
        }

        .avatar-upload-btn {
            position: absolute;
            bottom: 0;
            right: 0;
            background: var(--primary-color);
            color: white;
            border: 3px solid white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .avatar-upload-btn:hover {
            background: var(--secondary-color);
            transform: scale(1.1);
        }

        .profile-tabs {
            background: white;
            border-radius: 20px;
            padding: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .nav-tabs {
            border: none;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #718096;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            margin: 0 0.25rem;
            transition: all 0.3s;
            font-weight: 500;
        }

        .nav-tabs .nav-link:hover {
            background: #f7fafc;
            color: var(--primary-color);
        }

        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .profile-content {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea22, #764ba222);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            border: 2px solid transparent;
            transition: all 0.3s;
        }

        .stat-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.2);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .course-card {
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .course-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.1);
        }

        .course-progress {
            height: 6px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 1rem;
        }

        .course-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 3px;
            transition: width 1s ease;
        }

        .badge-item {
            display: inline-block;
            margin: 0.5rem;
            text-align: center;
            transition: transform 0.3s;
        }

        .badge-item:hover {
            transform: scale(1.1);
        }

        .badge-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin: 0 auto 0.5rem;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 0.75rem 1rem;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-gradient {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .alert {
            border-radius: 15px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #718096;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e0;
            margin-bottom: 1rem;
        }

        .activity-timeline {
            position: relative;
            padding-left: 40px;
        }

        .activity-timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e2e8f0;
        }

        .activity-item {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .activity-item::before {
            content: '';
            position: absolute;
            left: -30px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary-color);
            border: 3px solid white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .certificate-card {
            background: linear-gradient(135deg, #ffd89b, #19547b);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .certificate-card::before {
            content: '🏆';
            position: absolute;
            font-size: 100px;
            opacity: 0.1;
            right: -20px;
            top: 50%;
            transform: translateY(-50%);
        }

        @media (max-width: 768px) {
            .profile-header {
                text-align: center;
            }

            .nav-tabs .nav-link {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }

            .stat-card {
                margin-bottom: 1rem;
            }
        }

        /* Dark mode (opcional) */
        @media (prefers-color-scheme: dark) {
            body {
                background: linear-gradient(135deg, #1a202c, #2d3748);
            }
        }
    </style>
</head>
<body>

<div class="profile-container">
    
    <!-- Header do Perfil -->
    <div class="profile-header">
        <div class="row align-items-center">
            <div class="col-md-2 text-center">
                <div class="avatar-upload">
                    <img src="<?= $avatarUrl ?>" alt="Avatar" class="profile-avatar" id="avatarPreview">
                    <label for="avatarInput" class="avatar-upload-btn">
                        <i class="fas fa-camera"></i>
                    </label>
                    <form id="avatarForm" method="POST" enctype="multipart/form-data" style="display:none;">
                        <input type="hidden" name="action" value="update_avatar">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="file" id="avatarInput" name="avatar" accept="image/*" onchange="document.getElementById('avatarForm').submit();">
                    </form>
                </div>
            </div>
            <div class="col-md-7">
                <h2 class="mb-1"><?= htmlspecialchars($userData['name']) ?></h2>
                <p class="text-muted mb-2">
                    <i class="fas fa-envelope"></i> <?= htmlspecialchars($userData['email']) ?>
                </p>
                <p class="text-muted mb-0">
                    <i class="fas fa-calendar"></i> Membro desde <?= formatDate($userData['created_at'], 'd/m/Y') ?>
                </p>
                <?php if (!empty($userData['bio'])): ?>
                    <p class="mt-2"><?= nl2br(htmlspecialchars($userData['bio'])) ?></p>
                <?php endif; ?>
                
                <!-- Links sociais -->
                <div class="mt-3">
                    <?php if (!empty($userData['website'])): ?>
                        <a href="<?= htmlspecialchars($userData['website']) ?>" target="_blank" class="btn btn-sm btn-outline-primary me-2">
                            <i class="fas fa-globe"></i> Website
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($userData['github'])): ?>
                        <a href="https://github.com/<?= htmlspecialchars($userData['github']) ?>" target="_blank" class="btn btn-sm btn-outline-dark me-2">
                            <i class="fab fa-github"></i> GitHub
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($userData['linkedin'])): ?>
                        <a href="https://linkedin.com/in/<?= htmlspecialchars($userData['linkedin']) ?>" target="_blank" class="btn btn-sm btn-outline-info me-2">
                            <i class="fab fa-linkedin"></i> LinkedIn
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-3 text-end">
                <a href="<?= BASE_URL ?>user/public-profile.php?id=<?= $_SESSION['user_id'] ?>" class="btn btn-outline-primary mb-2 w-100">
                    <i class="fas fa-eye"></i> Ver Perfil Público
                </a>
                <a href="<?= BASE_URL ?>user/logout.php" class="btn btn-outline-danger w-100">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </div>
    </div>

    <!-- Mensagens de Feedback -->
    <?php if ($successMessage): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= $successMessage ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?= $errorMessage ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Estatísticas Rápidas -->
    <div class="row mb-4">
        <div class="col-md-2 col-6">
            <div class="stat-card">
                <div class="stat-number"><?= $userStats['total_courses'] ?></div>
                <div class="text-muted">Cursos</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="stat-card">
                <div class="stat-number"><?= $userStats['completed_courses'] ?></div>
                <div class="text-muted">Completos</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="stat-card">
                <div class="stat-number"><?= $userStats['badges'] ?></div>
                <div class="text-muted">Conquistas</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="stat-card">
                <div class="stat-number"><?= $userStats['certificates'] ?></div>
                <div class="text-muted">Certificados</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="stat-card">
                <div class="stat-number"><?= $userStats['streak_days'] ?></div>
                <div class="text-muted">Dias de Streak</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="stat-card">
                <div class="stat-number"><?= $userStats['total_hours'] ?>h</div>
                <div class="text-muted">Estudadas</div>
            </div>
        </div>
    </div>

    <!-- Navegação por Tabs -->
    <div class="profile-tabs">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'overview' ? 'active' : '' ?>" data-bs-toggle="tab" href="#overview">
                    <i class="fas fa-home"></i> Visão Geral
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'courses' ? 'active' : '' ?>" data-bs-toggle="tab" href="#courses">
                    <i class="fas fa-graduation-cap"></i> Meus Cursos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'achievements' ? 'active' : '' ?>" data-bs-toggle="tab" href="#achievements">
                    <i class="fas fa-trophy"></i> Conquistas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'settings' ? 'active' : '' ?>" data-bs-toggle="tab" href="#settings">
                    <i class="fas fa-cog"></i> Configurações
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'security' ? 'active' : '' ?>" data-bs-toggle="tab" href="#security">
                    <i class="fas fa-shield-alt"></i> Segurança
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'notifications' ? 'active' : '' ?>" data-bs-toggle="tab" href="#notifications">
                    <i class="fas fa-bell"></i> Notificações
                </a>
            </li>
        </ul>
    </div>

    <!-- Conteúdo das Tabs -->
    <div class="profile-content">
        <div class="tab-content">
            
            <!-- Tab: Visão Geral -->
            <div class="tab-pane fade <?= $activeTab === 'overview' ? 'show active' : '' ?>" id="overview">
                <h4 class="mb-4">Visão Geral da Conta</h4>
                
                <div class="row">
                    <div class="col-md-8">
                        <h5 class="mb-3">Cursos em Andamento</h5>
                        
                        <?php if (!empty($activeCourses)): ?>
                            <?php foreach ($activeCourses as $course): ?>
                                <div class="course-card">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h6><?= htmlspecialchars($course['title']) ?></h6>
                                            <small class="text-muted">
                                                <?= $course['completed_lessons'] ?>/<?= $course['total_lessons'] ?> aulas completas
                                            </small>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <a href="<?= BASE_URL ?>courses/view.php?id=<?= $course['id'] ?>" class="btn btn-sm btn-gradient">
                                                Continuar
                                            </a>
                                        </div>
                                    </div>
                                    <div class="course-progress">
                                        <?php 
                                        $progress = $course['total_lessons'] > 0 
                                            ? ($course['completed_lessons'] / $course['total_lessons'] * 100) 
                                            : 0;
                                        ?>
                                        <div class="course-progress-bar" style="width: <?= $progress ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-book-open"></i>
                                <p>Você ainda não iniciou nenhum curso</p>
                                <a href="<?= BASE_URL ?>courses/" class="btn btn-gradient">
                                    Explorar Cursos
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-4">
                        <h5 class="mb-3">Atividade Recente</h5>
                        <div class="activity-timeline">
                            <div class="activity-item">
                                <small class="text-muted">Hoje</small>
                                <p class="mb-0">Login realizado</p>
                            </div>
                            <div class="activity-item">
                                <small class="text-muted">Ontem</small>
                                <p class="mb-0">Completou 2 aulas</p>
                            </div>
                            <div class="activity-item">
                                <small class="text-muted">3 dias atrás</small>
                                <p class="mb-0">Iniciou novo curso</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Meus Cursos -->
            <div class="tab-pane fade <?= $activeTab === 'courses' ? 'show active' : '' ?>" id="courses">
                <h4 class="mb-4">Meus Cursos</h4>
                
                <ul class="nav nav-pills mb-4">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="pill" href="#active-courses">
                            Em Andamento (<?= count($activeCourses) ?>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="pill" href="#completed-courses">
                            Completados (<?= count($completedCourses) ?>)
                        </a>
                    </li>
                </ul>
                
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="active-courses">
                        <div class="row">
                            <?php if (!empty($activeCourses)): ?>
                                <?php foreach ($activeCourses as $course): ?>
                                    <div class="col-md-6">
                                        <div class="course-card">
                                            <h5><?= htmlspecialchars($course['title']) ?></h5>
                                            <p class="text-muted"><?= htmlspecialchars($course['description'] ?? '') ?></p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-primary">
                                                    <?= $course['completed_lessons'] ?>/<?= $course['total_lessons'] ?> aulas
                                                </span>
                                                <a href="<?= BASE_URL ?>courses/view.php?id=<?= $course['id'] ?>" class="btn btn-sm btn-gradient">
                                                    Acessar
                                                </a>
                                            </div>
                                            <div class="course-progress mt-3">
                                                <?php 
                                                $progress = $course['total_lessons'] > 0 
                                                    ? ($course['completed_lessons'] / $course['total_lessons'] * 100) 
                                                    : 0;
                                                ?>
                                                <div class="course-progress-bar" style="width: <?= $progress ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="empty-state">
                                        <i class="fas fa-book"></i>
                                        <p>Nenhum curso em andamento</p>
                                        <a href="<?= BASE_URL ?>courses/" class="btn btn-gradient">
                                            Começar um Curso
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="completed-courses">
                        <div class="row">
                            <?php if (!empty($completedCourses)): ?>
                                <?php foreach ($completedCourses as $course): ?>
                                    <div class="col-md-6">
                                        <div class="certificate-card">
                                            <h5><?= htmlspecialchars($course['title']) ?></h5>
                                            <p>Completado em <?= formatDate($course['completed_at'], 'd/m/Y') ?></p>
                                            <?php if (!empty($course['certificate_code'])): ?>
                                                <a href="<?= BASE_URL ?>certificates/view.php?code=<?= $course['certificate_code'] ?>" class="btn btn-light btn-sm">
                                                    <i class="fas fa-certificate"></i> Ver Certificado
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="empty-state">
                                        <i class="fas fa-graduation-cap"></i>
                                        <p>Nenhum curso completado ainda</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Conquistas -->
            <div class="tab-pane fade <?= $activeTab === 'achievements' ? 'show active' : '' ?>" id="achievements">
                <h4 class="mb-4">Minhas Conquistas</h4>
                
                <?php if (!empty($userBadges)): ?>
                    <div class="row">
                        <?php foreach ($userBadges as $badge): ?>
                            <div class="col-md-2 col-4">
                                <div class="badge-item">
                                    <div class="badge-icon">
                                        <i class="<?= $badge['icon'] ?? 'fas fa-award' ?>"></i>
                                    </div>
                                    <h6><?= htmlspecialchars($badge['name']) ?></h6>
                                    <small class="text-muted">
                                        <?= formatDate($badge['earned_at'], 'd/m/Y') ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-trophy"></i>
                        <p>Você ainda não conquistou nenhum badge</p>
                        <p class="text-muted">Continue estudando para desbloquear conquistas!</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tab: Configurações -->
            <div class="tab-pane fade <?= $activeTab === 'settings' ? 'show active' : '' ?>" id="settings">
                <h4 class="mb-4">Configurações do Perfil</h4>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_profile">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($userData['name']) ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($userData['email']) ?>" required>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="3" 
                                      placeholder="Conte um pouco sobre você..."><?= htmlspecialchars($userData['bio'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="website" class="form-label">Website</label>
                            <input type="url" class="form-control" id="website" name="website" 
                                   value="<?= htmlspecialchars($userData['website'] ?? '') ?>" 
                                   placeholder="https://seu-site.com">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="github" class="form-label">GitHub</label>
                            <div class="input-group">
                                <span class="input-group-text">github.com/</span>
                                <input type="text" class="form-control" id="github" name="github" 
                                       value="<?= htmlspecialchars($userData['github'] ?? '') ?>" 
                                       placeholder="seu-usuario">
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="linkedin" class="form-label">LinkedIn</label>
                            <div class="input-group">
                                <span class="input-group-text">linkedin.com/in/</span>
                                <input type="text" class="form-control" id="linkedin" name="linkedin" 
                                       value="<?= htmlspecialchars($userData['linkedin'] ?? '') ?>" 
                                       placeholder="seu-perfil">
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="twitter" class="form-label">Twitter/X</label>
                            <div class="input-group">
                                <span class="input-group-text">@</span>
                                <input type="text" class="form-control" id="twitter" name="twitter" 
                                       value="<?= htmlspecialchars($userData['twitter'] ?? '') ?>" 
                                       placeholder="seu-usuario">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-gradient">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                </form>
                
                <hr class="my-4">
                
                <h5>Avatar</h5>
                <div class="d-flex align-items-center gap-3">
                    <img src="<?= $avatarUrl ?>" alt="Avatar" width="80" height="80" class="rounded-circle">
                    <div>
                        <form method="POST" enctype="multipart/form-data" class="d-inline">
                            <input type="hidden" name="action" value="update_avatar">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <label for="avatarFile" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-upload"></i> Enviar Nova Foto
                            </label>
                            <input type="file" id="avatarFile" name="avatar" accept="image/*" 
                                   style="display:none;" onchange="this.form.submit()">
                        </form>
                        
                        <?php if (!empty($userData['avatar'])): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="remove_avatar">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm" 
                                        onclick="return confirm('Remover avatar?')">
                                    <i class="fas fa-trash"></i> Remover
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tab: Segurança -->
            <div class="tab-pane fade <?= $activeTab === 'security' ? 'show active' : '' ?>" id="security">
                <h4 class="mb-4">Segurança da Conta</h4>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_password">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Senha Atual</label>
                                <input type="password" class="form-control" id="current_password" 
                                       name="current_password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Nova Senha</label>
                                <input type="password" class="form-control" id="new_password" 
                                       name="new_password" required minlength="6">
                                <small class="text-muted">Mínimo 6 caracteres</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmar Nova Senha</label>
                                <input type="password" class="form-control" id="confirm_password" 
                                       name="confirm_password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-gradient">
                                <i class="fas fa-key"></i> Alterar Senha
                            </button>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Dicas de Segurança</h6>
                                <ul class="mb-0">
                                    <li>Use uma senha forte e única</li>
                                    <li>Não compartilhe sua senha</li>
                                    <li>Ative a autenticação de dois fatores</li>
                                    <li>Mantenha seu email atualizado</li>
                                </ul>
                            </div>
                            
                            <h6 class="mt-4">Sessões Ativas</h6>
                            <p class="text-muted">
                                <i class="fas fa-desktop"></i> Este dispositivo - Agora<br>
                                <small>IP: <?= $_SERVER['REMOTE_ADDR'] ?></small>
                            </p>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tab: Notificações -->
            <div class="tab-pane fade <?= $activeTab === 'notifications' ? 'show active' : '' ?>" id="notifications">
                <h4 class="mb-4">Preferências de Notificação</h4>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_notifications">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="email_notifications" 
                               name="email_notifications" <?= ($userData['email_notifications'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="email_notifications">
                            Receber notificações por email
                        </label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="newsletter" 
                               name="newsletter" <?= ($userData['newsletter_subscribed'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="newsletter">
                            Newsletter semanal com novidades
                        </label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="course_updates" 
                               name="course_updates" <?= ($userData['course_updates'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="course_updates">
                            Atualizações dos cursos matriculados
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-gradient">
                        <i class="fas fa-save"></i> Salvar Preferências
                    </button>
                </form>
            </div>
            
        </div>
    </div>
    
</div>

<!-- Scripts -->
<script src="<?= ASSETS_URL ?>js/bootstrap.bundle.min.js"></script>
<script>
// Preview de avatar antes do upload
document.getElementById('avatarInput')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});

// Auto-hide alerts após 5 segundos
setTimeout(function() {
    document.querySelectorAll('.alert').forEach(function(alert) {
        if (!alert.classList.contains('alert-info')) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }
    });
}, 5000);

// Animação das barras de progresso
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.course-progress-bar').forEach(function(bar) {
        const width = bar.style.width;
        bar.style.width = '0';
        setTimeout(() => bar.style.width = width, 100);
    });
});

// Confirmação ao deletar conta (se implementar)
function confirmDelete() {
    return confirm('Tem certeza? Esta ação não pode ser desfeita!');
}

// Validação de senha
document.getElementById('new_password')?.addEventListener('input', function() {
    const password = this.value;
    const confirm = document.getElementById('confirm_password');
    
    if (password.length < 6) {
        this.classList.add('is-invalid');
    } else {
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
    }
    
    if (confirm.value && confirm.value !== password) {
        confirm.classList.add('is-invalid');
    } else if (confirm.value) {
        confirm.classList.remove('is-invalid');
        confirm.classList.add('is-valid');
    }
});

document.getElementById('confirm_password')?.addEventListener('input', function() {
    const password = document.getElementById('new_password').value;
    if (this.value !== password) {
        this.classList.add('is-invalid');
    } else {
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
    }
});

// Tooltip Bootstrap
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});
</script>

</body>
</html>