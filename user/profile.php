<?php
/**
 * GameDev Academy - Perfil do Usuário
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// ============================================================================
// FUNÇÃO HELPER PARA ESCAPE SEGURO
// ============================================================================

if (!function_exists('esc')) {
    function esc($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

// Verifica autenticação
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . url('login.php'));
    exit;
}

// Configurações da página
$pageTitle = 'Meu Perfil';
$currentPage = 'profile';
$userId = (int) $_SESSION['user_id'];
$activeTab = $_GET['tab'] ?? 'personal';

// Buscar dados do usuário
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        session_destroy();
        header('Location: ' . url('login.php'));
        exit;
    }
} catch (PDOException $e) {
    die("Erro ao carregar dados do usuário");
}

// Valores padrão - garante que nenhum campo seja null
$defaults = [
    'name' => '',
    'nickname' => '',
    'email' => '',
    'bio' => '',
    'avatar' => '',
    'location' => '',
    'occupation' => '',
    'skills' => '',
    'github' => '',
    'gitlab' => '',
    'bitbucket' => '',
    'linkedin' => '',
    'twitter' => '',
    'youtube' => '',
    'twitch' => '',
    'discord' => '',
    'instagram' => '',
    'itch_io' => '',
    'website' => '',
    'created_at' => date('Y-m-d'),
    'password' => ''
];

// Mescla com valores do banco, substituindo nulls por strings vazias
foreach ($defaults as $key => $default) {
    $user[$key] = $user[$key] ?? $default;
}

// Mensagens
$message = '';
$messageType = '';

// ============================================================================
// PROCESSAMENTO DOS FORMULÁRIOS
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Atualizar informações pessoais
    if (isset($_POST['update_personal'])) {
        try {
            $name = trim($_POST['name'] ?? '');
            $nickname = trim($_POST['nickname'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $location = trim($_POST['location'] ?? '');
            $occupation = trim($_POST['occupation'] ?? '');
            
            if (strlen($name) < 3) {
                throw new Exception('Nome deve ter pelo menos 3 caracteres.');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email inválido.');
            }
            
            // Verifica email duplicado
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                throw new Exception('Este email já está em uso.');
            }
            
            // Atualiza campos básicos
            $sql = "UPDATE users SET name = ?, email = ?";
            $params = [$name, $email];
            
            // Adiciona campos opcionais
            $optionalFields = ['nickname' => $nickname, 'location' => $location, 'occupation' => $occupation];
            foreach ($optionalFields as $field => $value) {
                $check = $pdo->query("SHOW COLUMNS FROM users LIKE '$field'");
                if ($check->rowCount() > 0) {
                    $sql .= ", $field = ?";
                    $params[] = $value;
                }
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $userId;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            // Atualiza dados locais
            $user['name'] = $name;
            $user['nickname'] = $nickname;
            $user['email'] = $email;
            $user['location'] = $location;
            $user['occupation'] = $occupation;
            $_SESSION['user_name'] = $name;
            
            $message = 'Informações atualizadas com sucesso!';
            $messageType = 'success';
            
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = 'danger';
        }
    }
    
    // Atualizar avatar
    if (isset($_POST['update_avatar']) && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        try {
            $file = $_FILES['avatar'];
            
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new Exception('A imagem deve ter no máximo 5MB.');
            }
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
            if (!isset($allowedMimes[$mimeType])) {
                throw new Exception('Formato não permitido. Use: JPG, PNG, GIF ou WEBP.');
            }
            
            $extension = $allowedMimes[$mimeType];
            $newFileName = 'avatar_' . $userId . '_' . time() . '.' . $extension;
            
            $uploadDir = dirname(__DIR__) . '/uploads/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            if (!empty($user['avatar']) && file_exists($uploadDir . $user['avatar'])) {
                unlink($uploadDir . $user['avatar']);
            }
            
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $newFileName)) {
                throw new Exception('Erro ao salvar a imagem.');
            }
            
            $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
            $stmt->execute([$newFileName, $userId]);
            
            $user['avatar'] = $newFileName;
            $_SESSION['user_avatar'] = $newFileName;
            
            $message = 'Avatar atualizado com sucesso!';
            $messageType = 'success';
            
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = 'danger';
        }
        $activeTab = 'avatar';
    }
    
    // Remover avatar
    if (isset($_POST['remove_avatar'])) {
        try {
            $uploadDir = dirname(__DIR__) . '/uploads/avatars/';
            if (!empty($user['avatar']) && file_exists($uploadDir . $user['avatar'])) {
                unlink($uploadDir . $user['avatar']);
            }
            
            $stmt = $pdo->prepare("UPDATE users SET avatar = NULL WHERE id = ?");
            $stmt->execute([$userId]);
            
            $user['avatar'] = '';
            unset($_SESSION['user_avatar']);
            
            $message = 'Avatar removido!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Erro ao remover avatar.';
            $messageType = 'danger';
        }
        $activeTab = 'avatar';
    }
    
    // Atualizar biografia
    if (isset($_POST['update_bio'])) {
        try {
            $bio = trim($_POST['bio'] ?? '');
            $skills = trim($_POST['skills'] ?? '');
            
            $sql = "UPDATE users SET bio = ?";
            $params = [$bio];
            
            $check = $pdo->query("SHOW COLUMNS FROM users LIKE 'skills'");
            if ($check->rowCount() > 0) {
                $sql .= ", skills = ?";
                $params[] = $skills;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $userId;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $user['bio'] = $bio;
            $user['skills'] = $skills;
            
            $message = 'Biografia atualizada!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = 'danger';
        }
        $activeTab = 'bio';
    }
    
    // Atualizar redes sociais
    if (isset($_POST['update_social'])) {
        try {
            $socialFields = [
                'twitter' => trim($_POST['twitter'] ?? ''),
                'instagram' => trim($_POST['instagram'] ?? ''),
                'linkedin' => trim($_POST['linkedin'] ?? ''),
                'youtube' => trim($_POST['youtube'] ?? ''),
                'twitch' => trim($_POST['twitch'] ?? ''),
                'discord' => trim($_POST['discord'] ?? ''),
                'website' => trim($_POST['website'] ?? '')
            ];
            
            $setParts = [];
            $params = [];
            
            foreach ($socialFields as $field => $value) {
                $check = $pdo->query("SHOW COLUMNS FROM users LIKE '$field'");
                if ($check->rowCount() > 0) {
                    $setParts[] = "$field = ?";
                    $params[] = $value;
                    $user[$field] = $value;
                }
            }
            
            if (!empty($setParts)) {
                $params[] = $userId;
                $sql = "UPDATE users SET " . implode(', ', $setParts) . " WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            }
            
            $message = 'Redes sociais atualizadas!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = 'danger';
        }
        $activeTab = 'social';
    }
    
    // Atualizar repositórios
    if (isset($_POST['update_repositories'])) {
        try {
            $repoFields = [
                'github' => preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['github'] ?? ''),
                'gitlab' => preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['gitlab'] ?? ''),
                'bitbucket' => preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['bitbucket'] ?? ''),
                'itch_io' => preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['itch_io'] ?? '')
            ];
            
            $setParts = [];
            $params = [];
            
            foreach ($repoFields as $field => $value) {
                $check = $pdo->query("SHOW COLUMNS FROM users LIKE '$field'");
                if ($check->rowCount() > 0) {
                    $setParts[] = "$field = ?";
                    $params[] = $value;
                    $user[$field] = $value;
                }
            }
            
            if (!empty($setParts)) {
                $params[] = $userId;
                $sql = "UPDATE users SET " . implode(', ', $setParts) . " WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            }
            
            $message = 'Repositórios atualizados!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = 'danger';
        }
        $activeTab = 'repositories';
    }
    
    // Alterar senha
    if (isset($_POST['update_password'])) {
        try {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (!password_verify($currentPassword, $user['password'])) {
                throw new Exception('Senha atual incorreta.');
            }
            
            if (strlen($newPassword) < 6) {
                throw new Exception('Nova senha deve ter pelo menos 6 caracteres.');
            }
            
            if ($newPassword !== $confirmPassword) {
                throw new Exception('As senhas não coincidem.');
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);
            
            $message = 'Senha alterada com sucesso!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = 'danger';
        }
        $activeTab = 'security';
    }
}

// ============================================================================
// PREPARAÇÃO DOS DADOS
// ============================================================================

$displayName = !empty($user['nickname']) ? $user['nickname'] : $user['name'];

$avatarUrl = !empty($user['avatar']) 
    ? url('uploads/avatars/' . $user['avatar'])
    : 'https://ui-avatars.com/api/?name=' . urlencode($displayName) . '&background=6366f1&color=fff&size=200&bold=true';

$memberSince = formatDate($user['created_at'], 'd/m/Y');

// ============================================================================
// INCLUI O HEADER
// ============================================================================

require_once __DIR__ . '/includes/header.php';
?>

<!-- Conteúdo Principal -->
<main class="main-content">
    <div class="container py-4">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url() ?>">Início</a></li>
                <li class="breadcrumb-item active">Meu Perfil</li>
            </ol>
        </nav>
        
        <!-- Título -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-user-cog text-primary me-2"></i>
                Configurações do Perfil
            </h1>
        </div>
        
        <!-- Mensagens -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
                <?= esc($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            
            <!-- SIDEBAR -->
            <div class="col-lg-3 col-md-4 mb-4">
                
                <!-- Card do Perfil -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center">
                        <img src="<?= $avatarUrl ?>" 
                             alt="<?= esc($displayName) ?>" 
                             class="rounded-circle border border-3 border-primary mb-3 profile-avatar-preview">
                        
                        <h5 class="mb-1"><?= esc($displayName) ?></h5>
                        
                        <?php if (!empty($user['nickname']) && $user['nickname'] !== $user['name']): ?>
                            <small class="text-muted d-block"><?= esc($user['name']) ?></small>
                        <?php endif; ?>
                        
                        <p class="text-muted small mb-2">
                            <i class="fas fa-envelope me-1"></i><?= esc($user['email']) ?>
                        </p>
                        
                        <?php if (!empty($user['location'])): ?>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i><?= esc($user['location']) ?>
                            </p>
                        <?php endif; ?>
                        
                        <!-- Redes Sociais -->
                        <div class="d-flex justify-content-center flex-wrap gap-2 mt-3">
                            <?php if (!empty($user['github'])): ?>
                                <a href="https://github.com/<?= esc($user['github']) ?>" target="_blank" class="btn btn-sm btn-outline-dark rounded-circle" title="GitHub">
                                    <i class="fab fa-github"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($user['linkedin'])): ?>
                                <a href="https://linkedin.com/in/<?= esc($user['linkedin']) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-circle" title="LinkedIn">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($user['twitter'])): ?>
                                <a href="https://twitter.com/<?= esc($user['twitter']) ?>" target="_blank" class="btn btn-sm btn-outline-info rounded-circle" title="Twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($user['youtube'])): ?>
                                <a href="https://youtube.com/<?= esc($user['youtube']) ?>" target="_blank" class="btn btn-sm btn-outline-danger rounded-circle" title="YouTube">
                                    <i class="fab fa-youtube"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <hr>
                        
                        <small class="text-muted">
                            <i class="fas fa-calendar-alt me-1"></i>Membro desde <?= $memberSince ?>
                        </small>
                    </div>
                </div>
                
                <!-- Menu de Navegação -->
                <div class="card shadow-sm">
                    <div class="list-group list-group-flush">
                        <a href="?tab=personal" class="list-group-item list-group-item-action d-flex align-items-center <?= $activeTab === 'personal' ? 'active' : '' ?>">
                            <i class="fas fa-user fa-fw me-3"></i>Informações Pessoais
                        </a>
                        <a href="?tab=avatar" class="list-group-item list-group-item-action d-flex align-items-center <?= $activeTab === 'avatar' ? 'active' : '' ?>">
                            <i class="fas fa-camera fa-fw me-3"></i>Foto de Perfil
                        </a>
                        <a href="?tab=bio" class="list-group-item list-group-item-action d-flex align-items-center <?= $activeTab === 'bio' ? 'active' : '' ?>">
                            <i class="fas fa-file-alt fa-fw me-3"></i>Biografia
                        </a>
                        <a href="?tab=social" class="list-group-item list-group-item-action d-flex align-items-center <?= $activeTab === 'social' ? 'active' : '' ?>">
                            <i class="fas fa-share-alt fa-fw me-3"></i>Redes Sociais
                        </a>
                        <a href="?tab=repositories" class="list-group-item list-group-item-action d-flex align-items-center <?= $activeTab === 'repositories' ? 'active' : '' ?>">
                            <i class="fas fa-code-branch fa-fw me-3"></i>Repositórios
                        </a>
                        <a href="?tab=security" class="list-group-item list-group-item-action d-flex align-items-center <?= $activeTab === 'security' ? 'active' : '' ?>">
                            <i class="fas fa-shield-alt fa-fw me-3"></i>Segurança
                        </a>
                        <a href="<?= url('logout.php') ?>" class="list-group-item list-group-item-action d-flex align-items-center text-danger">
                            <i class="fas fa-sign-out-alt fa-fw me-3"></i>Sair da Conta
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- CONTEÚDO PRINCIPAL -->
            <div class="col-lg-9 col-md-8">
                
                <!-- TAB: Informações Pessoais -->
                <?php if ($activeTab === 'personal'): ?>
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0"><i class="fas fa-user text-primary me-2"></i>Informações Pessoais</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="?tab=personal">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?= esc($user['name']) ?>" required minlength="3">
                                        <div class="form-text">Seu nome real.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="nickname" class="form-label">Apelido / Nickname</label>
                                        <input type="text" class="form-control" id="nickname" name="nickname" 
                                               value="<?= esc($user['nickname']) ?>" placeholder="Como quer ser chamado?">
                                        <div class="form-text">Será exibido no lugar do nome.</div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= esc($user['email']) ?>" required>
                                    <div class="form-text">Usado para login e notificações.</div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="location" class="form-label"><i class="fas fa-map-marker-alt text-muted me-1"></i>Localização</label>
                                        <input type="text" class="form-control" id="location" name="location" 
                                               value="<?= esc($user['location']) ?>" placeholder="Cidade, Estado, País">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="occupation" class="form-label"><i class="fas fa-briefcase text-muted me-1"></i>Ocupação</label>
                                        <input type="text" class="form-control" id="occupation" name="occupation" 
                                               value="<?= esc($user['occupation']) ?>" placeholder="Ex: Desenvolvedor de Games">
                                    </div>
                                </div>
                                
                                <hr class="my-4">
                                <button type="submit" name="update_personal" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Salvar Alterações
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- TAB: Avatar -->
                <?php if ($activeTab === 'avatar'): ?>
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0"><i class="fas fa-camera text-primary me-2"></i>Foto de Perfil</h5>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-4 text-center mb-4 mb-md-0">
                                    <img src="<?= $avatarUrl ?>" alt="Avatar" 
                                         class="rounded-circle border border-3 border-primary shadow profile-avatar-large" 
                                         id="avatarPreview">
                                    
                                    <?php if (!empty($user['avatar'])): ?>
                                        <form method="POST" action="?tab=avatar" class="mt-3">
                                            <button type="submit" name="remove_avatar" class="btn btn-outline-danger btn-sm"
                                                    onclick="return confirm('Remover avatar?')">
                                                <i class="fas fa-trash-alt me-1"></i>Remover
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-8">
                                    <form method="POST" action="?tab=avatar" enctype="multipart/form-data">
                                        <div class="mb-4">
                                            <label for="avatar" class="form-label fw-bold">Escolher nova foto</label>
                                            <input type="file" class="form-control" id="avatar" name="avatar" 
                                                   accept="image/jpeg,image/png,image/gif,image/webp" required>
                                            <div class="form-text">
                                                <strong>Formatos:</strong> JPG, PNG, GIF, WEBP | <strong>Máximo:</strong> 5MB
                                            </div>
                                        </div>
                                        <button type="submit" name="update_avatar" class="btn btn-primary">
                                            <i class="fas fa-upload me-2"></i>Enviar Foto
                                        </button>
                                    </form>
                                    
                                    <hr class="my-4">
                                    <div class="alert alert-info mb-0">
                                        <h6 class="alert-heading"><i class="fas fa-lightbulb me-1"></i>Dicas</h6>
                                        <ul class="mb-0 small">
                                            <li>Use uma imagem quadrada para melhor resultado</li>
                                            <li>Prefira fotos com boa iluminação</li>
                                            <li>Dimensões mínimas: 100x100 pixels</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- TAB: Biografia -->
                <?php if ($activeTab === 'bio'): ?>
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0"><i class="fas fa-file-alt text-primary me-2"></i>Biografia</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="?tab=bio">
                                <div class="mb-4">
                                    <label for="bio" class="form-label fw-bold">Sobre você</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="6" maxlength="1000"
                                              placeholder="Conte sobre você, sua experiência com games, objetivos..."><?= esc($user['bio']) ?></textarea>
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="form-text">Uma boa biografia ajuda outros a conhecer você.</small>
                                        <small class="text-muted"><span id="bioCounter"><?= strlen($user['bio']) ?></span>/1000</small>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="skills" class="form-label fw-bold">
                                        <i class="fas fa-tools text-muted me-1"></i>Habilidades e Tecnologias
                                    </label>
                                    <textarea class="form-control" id="skills" name="skills" rows="3" maxlength="500"
                                              placeholder="Ex: Unity, C#, Pixel Art, Game Design, Unreal Engine..."><?= esc($user['skills']) ?></textarea>
                                    <div class="form-text">Separe por vírgulas suas principais habilidades.</div>
                                </div>
                                
                                <hr class="my-4">
                                <button type="submit" name="update_bio" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Salvar Biografia
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- TAB: Redes Sociais -->
                <?php if ($activeTab === 'social'): ?>
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0"><i class="fas fa-share-alt text-primary me-2"></i>Redes Sociais</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-4">Conecte suas redes sociais para que outros possam encontrar você.</p>
                            
                            <form method="POST" action="?tab=social">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="twitter" class="form-label"><i class="fab fa-twitter text-info me-1"></i>Twitter / X</label>
                                        <div class="input-group">
                                            <span class="input-group-text">@</span>
                                            <input type="text" class="form-control" id="twitter" name="twitter" 
                                                   value="<?= esc($user['twitter']) ?>" placeholder="seu_usuario">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="instagram" class="form-label"><i class="fab fa-instagram text-danger me-1"></i>Instagram</label>
                                        <div class="input-group">
                                            <span class="input-group-text">@</span>
                                            <input type="text" class="form-control" id="instagram" name="instagram" 
                                                   value="<?= esc($user['instagram']) ?>" placeholder="seu_usuario">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="linkedin" class="form-label"><i class="fab fa-linkedin text-primary me-1"></i>LinkedIn</label>
                                        <div class="input-group">
                                            <span class="input-group-text">linkedin.com/in/</span>
                                            <input type="text" class="form-control" id="linkedin" name="linkedin" 
                                                   value="<?= esc($user['linkedin']) ?>" placeholder="seu-perfil">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="youtube" class="form-label"><i class="fab fa-youtube text-danger me-1"></i>YouTube</label>
                                        <div class="input-group">
                                            <span class="input-group-text">youtube.com/</span>
                                            <input type="text" class="form-control" id="youtube" name="youtube" 
                                                   value="<?= esc($user['youtube']) ?>" placeholder="@seucanal">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="twitch" class="form-label"><i class="fab fa-twitch me-1"></i>Twitch</label>
                                        <div class="input-group">
                                            <span class="input-group-text">twitch.tv/</span>
                                            <input type="text" class="form-control" id="twitch" name="twitch" 
                                                   value="<?= esc($user['twitch']) ?>" placeholder="seu_canal">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="discord" class="form-label"><i class="fab fa-discord me-1"></i>Discord</label>
                                        <input type="text" class="form-control" id="discord" name="discord" 
                                               value="<?= esc($user['discord']) ?>" placeholder="usuario ou link do servidor">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="website" class="form-label"><i class="fas fa-globe text-secondary me-1"></i>Website Pessoal</label>
                                    <input type="url" class="form-control" id="website" name="website" 
                                           value="<?= esc($user['website']) ?>" placeholder="https://seusite.com">
                                </div>
                                
                                <hr class="my-4">
                                <button type="submit" name="update_social" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Salvar Redes Sociais
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- TAB: Repositórios -->
                <?php if ($activeTab === 'repositories'): ?>
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0"><i class="fas fa-code-branch text-primary me-2"></i>Repositórios de Código</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-4">Conecte seus repositórios para compartilhar seu código e projetos.</p>
                            
                            <form method="POST" action="?tab=repositories">
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label for="github" class="form-label"><i class="fab fa-github fa-lg me-2"></i>GitHub</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-dark text-white">github.com/</span>
                                            <input type="text" class="form-control" id="github" name="github" 
                                                   value="<?= esc($user['github']) ?>" placeholder="seu-usuario">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label for="gitlab" class="form-label"><i class="fab fa-gitlab me-2"></i>GitLab</label>
                                        <div class="input-group">
                                            <span class="input-group-text">gitlab.com/</span>
                                            <input type="text" class="form-control" id="gitlab" name="gitlab" 
                                                   value="<?= esc($user['gitlab']) ?>" placeholder="seu-usuario">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label for="bitbucket" class="form-label"><i class="fab fa-bitbucket me-2"></i>Bitbucket</label>
                                        <div class="input-group">
                                            <span class="input-group-text">bitbucket.org/</span>
                                            <input type="text" class="form-control" id="bitbucket" name="bitbucket" 
                                                   value="<?= esc($user['bitbucket']) ?>" placeholder="seu-usuario">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label for="itch_io" class="form-label"><i class="fab fa-itch-io me-2"></i>Itch.io</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="itch_io" name="itch_io" 
                                                   value="<?= esc($user['itch_io']) ?>" placeholder="seu-usuario">
                                            <span class="input-group-text">.itch.io</span>
                                        </div>
                                        <div class="form-text">Plataforma para publicar jogos indie.</div>
                                    </div>
                                </div>
                                
                                <hr class="my-4">
                                <button type="submit" name="update_repositories" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Salvar Repositórios
                                </button>
                            </form>
                        </div>
                        
                        <!-- Preview GitHub -->
                        <?php if (!empty($user['github'])): ?>
                            <div class="card-footer bg-light">
                                <h6><i class="fab fa-github me-2"></i>Seus Repositórios Públicos</h6>
                                <div id="githubRepos" class="row mt-3">
                                    <div class="col-12 text-center py-2">
                                        <span class="spinner-border spinner-border-sm"></span>
                                        <span class="ms-2 text-muted">Carregando...</span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- TAB: Segurança -->
                <?php if ($activeTab === 'security'): ?>
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0"><i class="fas fa-shield-alt text-primary me-2"></i>Segurança</h5>
                        </div>
                        <div class="card-body">
                            <h6 class="mb-3"><i class="fas fa-key text-muted me-2"></i>Alterar Senha</h6>
                            
                            <form method="POST" action="?tab=security">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="current_password" class="form-label">Senha Atual <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="new_password" class="form-label">Nova Senha <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                                            <div class="form-text">Mínimo 6 caracteres.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="confirm_password" class="form-label">Confirmar Nova Senha <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                                        </div>
                                        <button type="submit" name="update_password" class="btn btn-danger">
                                            <i class="fas fa-key me-2"></i>Alterar Senha
                                        </button>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="alert alert-info">
                                            <h6 class="alert-heading"><i class="fas fa-info-circle me-1"></i>Dicas</h6>
                                            <ul class="mb-0 small ps-3">
                                                <li>Use uma senha forte</li>
                                                <li>Combine letras, números e símbolos</li>
                                                <li>Não reutilize senhas</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            
                            <hr class="my-4">
                            
                            <h6><i class="fas fa-info-circle text-muted me-2"></i>Informações da Conta</h6>
                            <table class="table table-bordered mt-3">
                                <tr>
                                    <th class="bg-light" style="width:200px">ID da Conta</th>
                                    <td><?= $userId ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Membro desde</th>
                                    <td><?= $memberSince ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</main>

<!-- JavaScript -->
<script>
// Preview do avatar
document.getElementById('avatar')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
        reader.readAsDataURL(file);
    }
});

// Contador de caracteres
document.getElementById('bio')?.addEventListener('input', function() {
    document.getElementById('bioCounter').textContent = this.value.length;
});

// Validação de senha
document.getElementById('confirm_password')?.addEventListener('input', function() {
    const newPass = document.getElementById('new_password').value;
    this.setCustomValidity(this.value !== newPass ? 'As senhas não coincidem' : '');
});

// Carregar repos do GitHub
<?php if (!empty($user['github'])): ?>
fetch('https://api.github.com/users/<?= esc($user['github']) ?>/repos?sort=updated&per_page=6')
    .then(r => r.json())
    .then(repos => {
        const container = document.getElementById('githubRepos');
        if (Array.isArray(repos) && repos.length > 0) {
            container.innerHTML = repos.map(repo => `
                <div class="col-md-6 mb-2">
                    <div class="card h-100">
                        <div class="card-body py-2">
                            <a href="${repo.html_url}" target="_blank" class="fw-bold text-decoration-none">${repo.name}</a>
                            <p class="small text-muted mb-1">${repo.description || 'Sem descrição'}</p>
                            <small>⭐ ${repo.stargazers_count} | ${repo.language || 'N/A'}</small>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div class="col-12"><p class="text-muted mb-0">Nenhum repositório encontrado.</p></div>';
        }
    }).catch(() => {
        document.getElementById('githubRepos').innerHTML = '<div class="col-12"><p class="text-muted mb-0">Erro ao carregar repositórios.</p></div>';
    });
<?php endif; ?>
</script>

<?php
// Inclui o Footer
require_once __DIR__ . '/../includes/footer.php';
?>
