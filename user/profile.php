<?php
/**
 * GameDev Academy - Perfil do Usuário
 * 
 * Página de personalização completa do perfil
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Verifica se está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . url('login.php'));
    exit;
}

// ====================================================================
// BUSCA DADOS DO USUÁRIO
// ====================================================================

$userId = $_SESSION['user_id'];
$message = '';
$error = '';
$activeTab = $_GET['tab'] ?? 'profile';

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
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

// ====================================================================
// PROCESSAMENTO DOS FORMULÁRIOS
// ====================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Atualizar informações básicas
    if (isset($_POST['update_profile'])) {
        try {
            $name = sanitize($_POST['name']);
            $nickname = sanitize($_POST['nickname'] ?? '');
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            $bio = sanitize($_POST['bio'] ?? '');
            
            // Validações
            if (strlen($name) < 3) {
                throw new Exception('O nome deve ter pelo menos 3 caracteres.');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email inválido.');
            }
            
            // Verifica se email já existe (outro usuário)
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                throw new Exception('Este email já está em uso por outro usuário.');
            }
            
            // Atualiza no banco
            $stmt = $pdo->prepare("
                UPDATE users SET 
                    name = ?, 
                    nickname = ?, 
                    email = ?, 
                    bio = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$name, $nickname, $email, $bio, $userId]);
            
            // Atualiza sessão
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            
            // Atualiza dados locais
            $user['name'] = $name;
            $user['nickname'] = $nickname;
            $user['email'] = $email;
            $user['bio'] = $bio;
            
            $message = 'Perfil atualizado com sucesso!';
            
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
    
    // Atualizar redes sociais
    if (isset($_POST['update_social'])) {
        try {
            $github = sanitize($_POST['github'] ?? '');
            $linkedin = sanitize($_POST['linkedin'] ?? '');
            $twitter = sanitize($_POST['twitter'] ?? '');
            $youtube = sanitize($_POST['youtube'] ?? '');
            $website = filter_var($_POST['website'] ?? '', FILTER_SANITIZE_URL);
            $gitlab = sanitize($_POST['gitlab'] ?? '');
            $bitbucket = sanitize($_POST['bitbucket'] ?? '');
            $discord = sanitize($_POST['discord'] ?? '');
            $twitch = sanitize($_POST['twitch'] ?? '');
            $itch_io = sanitize($_POST['itch_io'] ?? '');
            
            $stmt = $pdo->prepare("
                UPDATE users SET 
                    github = ?, 
                    linkedin = ?, 
                    twitter = ?,
                    youtube = ?,
                    website = ?,
                    gitlab = ?,
                    bitbucket = ?,
                    discord = ?,
                    twitch = ?,
                    itch_io = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $github, $linkedin, $twitter, $youtube, $website,
                $gitlab, $bitbucket, $discord, $twitch, $itch_io,
                $userId
            ]);
            
            // Atualiza dados locais
            $user['github'] = $github;
            $user['linkedin'] = $linkedin;
            $user['twitter'] = $twitter;
            $user['youtube'] = $youtube;
            $user['website'] = $website;
            $user['gitlab'] = $gitlab;
            $user['bitbucket'] = $bitbucket;
            $user['discord'] = $discord;
            $user['twitch'] = $twitch;
            $user['itch_io'] = $itch_io;
            
            $message = 'Redes sociais atualizadas com sucesso!';
            $activeTab = 'social';
            
        } catch (Exception $e) {
            $error = $e->getMessage();
            $activeTab = 'social';
        }
    }
    
    // Upload de avatar
    if (isset($_POST['update_avatar']) && isset($_FILES['avatar'])) {
        try {
            $file = $_FILES['avatar'];
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Erro no upload do arquivo.');
            }
            
            // Validações
            $maxSize = 5 * 1024 * 1024; // 5MB
            if ($file['size'] > $maxSize) {
                throw new Exception('O arquivo deve ter no máximo 5MB.');
            }
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                throw new Exception('Formato não permitido. Use: JPG, PNG, GIF ou WEBP.');
            }
            
            // Gera nome único
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newFileName = 'avatar_' . $userId . '_' . time() . '.' . $extension;
            
            // Diretório de upload
            $uploadDir = ROOT_PATH . 'uploads/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Remove avatar antigo
            if (!empty($user['avatar']) && file_exists($uploadDir . $user['avatar'])) {
                unlink($uploadDir . $user['avatar']);
            }
            
            // Move o arquivo
            $destination = $uploadDir . $newFileName;
            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                throw new Exception('Erro ao salvar o arquivo.');
            }
            
            // Atualiza no banco
            $stmt = $pdo->prepare("UPDATE users SET avatar = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$newFileName, $userId]);
            
            $user['avatar'] = $newFileName;
            $_SESSION['user_avatar'] = $newFileName;
            
            $message = 'Avatar atualizado com sucesso!';
            $activeTab = 'avatar';
            
        } catch (Exception $e) {
            $error = $e->getMessage();
            $activeTab = 'avatar';
        }
    }
    
    // Remover avatar
    if (isset($_POST['remove_avatar'])) {
        try {
            $uploadDir = ROOT_PATH . 'uploads/avatars/';
            
            if (!empty($user['avatar']) && file_exists($uploadDir . $user['avatar'])) {
                unlink($uploadDir . $user['avatar']);
            }
            
            $stmt = $pdo->prepare("UPDATE users SET avatar = NULL, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$userId]);
            
            $user['avatar'] = null;
            unset($_SESSION['user_avatar']);
            
            $message = 'Avatar removido com sucesso!';
            $activeTab = 'avatar';
            
        } catch (Exception $e) {
            $error = $e->getMessage();
            $activeTab = 'avatar';
        }
    }
    
    // Alterar senha
    if (isset($_POST['update_password'])) {
        try {
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
            
            if (!password_verify($currentPassword, $user['password'])) {
                throw new Exception('Senha atual incorreta.');
            }
            
            if (strlen($newPassword) < 6) {
                throw new Exception('A nova senha deve ter pelo menos 6 caracteres.');
            }
            
            if ($newPassword !== $confirmPassword) {
                throw new Exception('As senhas não coincidem.');
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);
            
            $message = 'Senha alterada com sucesso!';
            $activeTab = 'security';
            
        } catch (Exception $e) {
            $error = $e->getMessage();
            $activeTab = 'security';
        }
    }
    
    // Preferências
    if (isset($_POST['update_preferences'])) {
        try {
            $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
            $newsletter = isset($_POST['newsletter']) ? 1 : 0;
            $profilePublic = isset($_POST['profile_public']) ? 1 : 0;
            $showEmail = isset($_POST['show_email']) ? 1 : 0;
            $theme = sanitize($_POST['theme'] ?? 'light');
            
            $stmt = $pdo->prepare("
                UPDATE users SET 
                    email_notifications = ?,
                    newsletter = ?,
                    profile_public = ?,
                    show_email = ?,
                    theme = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$emailNotifications, $newsletter, $profilePublic, $showEmail, $theme, $userId]);
            
            $user['email_notifications'] = $emailNotifications;
            $user['newsletter'] = $newsletter;
            $user['profile_public'] = $profilePublic;
            $user['show_email'] = $showEmail;
            $user['theme'] = $theme;
            
            $message = 'Preferências atualizadas com sucesso!';
            $activeTab = 'preferences';
            
        } catch (Exception $e) {
            $error = $e->getMessage();
            $activeTab = 'preferences';
        }
    }
}

// ====================================================================
// PREPARAÇÃO DOS DADOS PARA EXIBIÇÃO
// ====================================================================

// Avatar URL
$avatarUrl = !empty($user['avatar']) 
    ? url('uploads/avatars/' . $user['avatar'])
    : 'https://ui-avatars.com/api/?name=' . urlencode($user['nickname'] ?: $user['name']) . '&background=6366f1&color=fff&size=200&bold=true';

// Nome de exibição (apelido ou nome)
$displayName = !empty($user['nickname']) ? $user['nickname'] : $user['name'];

// Definições da página
$pageTitle = 'Meu Perfil';
$currentPage = 'profile';

// Inclui o header
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
    
    <!-- Mensagens de feedback -->
    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        
        <!-- Sidebar com informações do usuário -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <img src="<?= $avatarUrl ?>" 
                         alt="<?= escape($displayName) ?>" 
                         class="rounded-circle mb-3 border" 
                         width="150" 
                         height="150"
                         style="object-fit: cover;">
                    
                    <h5 class="mb-1"><?= escape($displayName) ?></h5>
                    
                    <?php if (!empty($user['nickname']) && $user['nickname'] !== $user['name']): ?>
                        <small class="text-muted d-block"><?= escape($user['name']) ?></small>
                    <?php endif; ?>
                    
                    <p class="text-muted small mb-3"><?= escape($user['email']) ?></p>
                    
                    <?php if (!empty($user['bio'])): ?>
                        <p class="small"><?= nl2br(escape(truncate($user['bio'], 100))) ?></p>
                    <?php endif; ?>
                    
                    <!-- Redes sociais -->
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <?php if (!empty($user['github'])): ?>
                            <a href="https://github.com/<?= escape($user['github']) ?>" 
                               target="_blank" 
                               class="btn btn-sm btn-outline-dark" 
                               title="GitHub">
                                <i class="fab fa-github"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($user['linkedin'])): ?>
                            <a href="https://linkedin.com/in/<?= escape($user['linkedin']) ?>" 
                               target="_blank" 
                               class="btn btn-sm btn-outline-primary" 
                               title="LinkedIn">
                                <i class="fab fa-linkedin"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($user['twitter'])): ?>
                            <a href="https://twitter.com/<?= escape($user['twitter']) ?>" 
                               target="_blank" 
                               class="btn btn-sm btn-outline-info" 
                               title="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($user['youtube'])): ?>
                            <a href="https://youtube.com/<?= escape($user['youtube']) ?>" 
                               target="_blank" 
                               class="btn btn-sm btn-outline-danger" 
                               title="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($user['website'])): ?>
                            <a href="<?= escape($user['website']) ?>" 
                               target="_blank" 
                               class="btn btn-sm btn-outline-secondary" 
                               title="Website">
                                <i class="fas fa-globe"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Menu de navegação -->
                <div class="list-group list-group-flush">
                    <a href="?tab=profile" 
                       class="list-group-item list-group-item-action <?= $activeTab === 'profile' ? 'active' : '' ?>">
                        <i class="fas fa-user me-2"></i> Informações Pessoais
                    </a>
                    <a href="?tab=avatar" 
                       class="list-group-item list-group-item-action <?= $activeTab === 'avatar' ? 'active' : '' ?>">
                        <i class="fas fa-camera me-2"></i> Foto de Perfil
                    </a>
                    <a href="?tab=social" 
                       class="list-group-item list-group-item-action <?= $activeTab === 'social' ? 'active' : '' ?>">
                        <i class="fas fa-share-alt me-2"></i> Redes Sociais
                    </a>
                    <a href="?tab=repositories" 
                       class="list-group-item list-group-item-action <?= $activeTab === 'repositories' ? 'active' : '' ?>">
                        <i class="fas fa-code-branch me-2"></i> Repositórios
                    </a>
                    <a href="?tab=preferences" 
                       class="list-group-item list-group-item-action <?= $activeTab === 'preferences' ? 'active' : '' ?>">
                        <i class="fas fa-cog me-2"></i> Preferências
                    </a>
                    <a href="?tab=security" 
                       class="list-group-item list-group-item-action <?= $activeTab === 'security' ? 'active' : '' ?>">
                        <i class="fas fa-lock me-2"></i> Segurança
                    </a>
                    <a href="<?= url('logout.php') ?>" 
                       class="list-group-item list-group-item-action text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i> Sair
                    </a>
                </div>
            </div>
            
            <!-- Membro desde -->
            <div class="text-center mt-3">
                <small class="text-muted">
                    <i class="fas fa-calendar-alt me-1"></i>
                    Membro desde <?= formatDate($user['created_at'] ?? date('Y-m-d')) ?>
                </small>
            </div>
        </div>

        <!-- Conteúdo principal -->
        <div class="col-lg-9 col-md-8">
            
            <!-- Tab: Informações Pessoais -->
            <?php if ($activeTab === 'profile'): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2 text-primary"></i>
                            Informações Pessoais
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="?tab=profile">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Nome Completo *</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="name" 
                                           name="name" 
                                           value="<?= escape($user['name']) ?>" 
                                           required 
                                           minlength="3">
                                    <small class="text-muted">Seu nome real</small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="nickname" class="form-label">Apelido / Nickname</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="nickname" 
                                           name="nickname" 
                                           value="<?= escape($user['nickname'] ?? '') ?>" 
                                           placeholder="Como quer ser chamado?">
                                    <small class="text-muted">Será exibido no lugar do seu nome</small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?= escape($user['email']) ?>" 
                                       required>
                                <small class="text-muted">Usado para login e notificações</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="bio" class="form-label">Biografia</label>
                                <textarea class="form-control" 
                                          id="bio" 
                                          name="bio" 
                                          rows="4" 
                                          maxlength="500"
                                          placeholder="Conte um pouco sobre você, seus interesses e experiência com games..."><?= escape($user['bio'] ?? '') ?></textarea>
                                <small class="text-muted">
                                    <span id="bioCounter"><?= strlen($user['bio'] ?? '') ?></span>/500 caracteres
                                </small>
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Salvar Alterações
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Tab: Foto de Perfil -->
            <?php if ($activeTab === 'avatar'): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-camera me-2 text-primary"></i>
                            Foto de Perfil
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center mb-4 mb-md-0">
                                <img src="<?= $avatarUrl ?>" 
                                     alt="Avatar atual" 
                                     class="rounded-circle border shadow-sm" 
                                     width="200" 
                                     height="200"
                                     style="object-fit: cover;"
                                     id="avatarPreview">
                                
                                <?php if (!empty($user['avatar'])): ?>
                                    <form method="POST" action="?tab=avatar" class="mt-3">
                                        <button type="submit" 
                                                name="remove_avatar" 
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Tem certeza que deseja remover o avatar?')">
                                            <i class="fas fa-trash me-1"></i> Remover Avatar
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-8">
                                <form method="POST" action="?tab=avatar" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="avatar" class="form-label">Escolher nova foto</label>
                                        <input type="file" 
                                               class="form-control" 
                                               id="avatar" 
                                               name="avatar" 
                                               accept="image/jpeg,image/png,image/gif,image/webp"
                                               required>
                                        <small class="text-muted">
                                            Formatos aceitos: JPG, PNG, GIF, WEBP. Tamanho máximo: 5MB.
                                        </small>
                                    </div>
                                    
                                    <button type="submit" name="update_avatar" class="btn btn-primary">
                                        <i class="fas fa-upload me-2"></i> Enviar Nova Foto
                                    </button>
                                </form>
                                
                                <hr class="my-4">
                                
                                <h6>Dicas para uma boa foto:</h6>
                                <ul class="small text-muted">
                                    <li>Use uma imagem quadrada para melhor resultado</li>
                                    <li>Prefira fotos com boa iluminação</li>
                                    <li>Evite imagens muito pequenas (mínimo 200x200px)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Tab: Redes Sociais -->
            <?php if ($activeTab === 'social'): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-share-alt me-2 text-primary"></i>
                            Redes Sociais
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="?tab=social">
                            <p class="text-muted mb-4">
                                Adicione seus perfis nas redes sociais para que outros usuários possam te encontrar.
                            </p>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="twitter" class="form-label">
                                        <i class="fab fa-twitter text-info me-1"></i> Twitter / X
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">@</span>
                                        <input type="text" 
                                               class="form-control" 
                                               id="twitter" 
                                               name="twitter" 
                                               value="<?= escape($user['twitter'] ?? '') ?>"
                                               placeholder="seu_usuario">
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="linkedin" class="form-label">
                                        <i class="fab fa-linkedin text-primary me-1"></i> LinkedIn
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">linkedin.com/in/</span>
                                        <input type="text" 
                                               class="form-control" 
                                               id="linkedin" 
                                               name="linkedin" 
                                               value="<?= escape($user['linkedin'] ?? '') ?>"
                                               placeholder="seu-perfil">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="youtube" class="form-label">
                                        <i class="fab fa-youtube text-danger me-1"></i> YouTube
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">youtube.com/</span>
                                        <input type="text" 
                                               class="form-control" 
                                               id="youtube" 
                                               name="youtube" 
                                               value="<?= escape($user['youtube'] ?? '') ?>"
                                               placeholder="@seucanal ou c/seucanal">
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="twitch" class="form-label">
                                        <i class="fab fa-twitch text-purple me-1" style="color: #9146FF;"></i> Twitch
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">twitch.tv/</span>
                                        <input type="text" 
                                               class="form-control" 
                                               id="twitch" 
                                               name="twitch" 
                                               value="<?= escape($user['twitch'] ?? '') ?>"
                                               placeholder="seu_canal">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="discord" class="form-label">
                                        <i class="fab fa-discord me-1" style="color: #5865F2;"></i> Discord
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="discord" 
                                           name="discord" 
                                           value="<?= escape($user['discord'] ?? '') ?>"
                                           placeholder="usuario#1234 ou servidor">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="website" class="form-label">
                                        <i class="fas fa-globe text-secondary me-1"></i> Website Pessoal
                                    </label>
                                    <input type="url" 
                                           class="form-control" 
                                           id="website" 
                                           name="website" 
                                           value="<?= escape($user['website'] ?? '') ?>"
                                           placeholder="https://seusite.com">
                                </div>
                            </div>
                            
                            <button type="submit" name="update_social" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Salvar Redes Sociais
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Tab: Repositórios -->
            <?php if ($activeTab === 'repositories'): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-code-branch me-2 text-primary"></i>
                            Repositórios de Código
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="?tab=social">
                            <p class="text-muted mb-4">
                                Conecte seus repositórios para compartilhar seu código e projetos.
                            </p>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="github" class="form-label">
                                        <i class="fab fa-github me-1"></i> GitHub
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">github.com/</span>
                                        <input type="text" 
                                               class="form-control" 
                                               id="github" 
                                               name="github" 
                                               value="<?= escape($user['github'] ?? '') ?>"
                                               placeholder="seu-usuario">
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="gitlab" class="form-label">
                                        <i class="fab fa-gitlab me-1" style="color: #FC6D26;"></i> GitLab
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">gitlab.com/</span>
                                        <input type="text" 
                                               class="form-control" 
                                               id="gitlab" 
                                               name="gitlab" 
                                               value="<?= escape($user['gitlab'] ?? '') ?>"
                                               placeholder="seu-usuario">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="bitbucket" class="form-label">
                                        <i class="fab fa-bitbucket me-1" style="color: #0052CC;"></i> Bitbucket
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">bitbucket.org/</span>
                                        <input type="text" 
                                               class="form-control" 
                                               id="bitbucket" 
                                               name="bitbucket" 
                                               value="<?= escape($user['bitbucket'] ?? '') ?>"
                                               placeholder="seu-usuario">
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="itch_io" class="form-label">
                                        <i class="fab fa-itch-io me-1" style="color: #FA5C5C;"></i> Itch.io
                                    </label>
                                    <div class="input-group">
                                        <input type="text" 
                                               class="form-control" 
                                               id="itch_io" 
                                               name="itch_io" 
                                               value="<?= escape($user['itch_io'] ?? '') ?>"
                                               placeholder="seu-usuario">
                                        <span class="input-group-text">.itch.io</span>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" name="update_social" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Salvar Repositórios
                            </button>
                        </form>
                        
                        <?php if (!empty($user['github'])): ?>
                            <hr class="my-4">
                            <h6><i class="fab fa-github me-2"></i>Seus repositórios públicos do GitHub</h6>
                            <div id="github-repos" class="mt-3">
                                <p class="text-muted small">
                                    <i class="fas fa-spinner fa-spin me-1"></i> Carregando repositórios...
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Tab: Preferências -->
            <?php if ($activeTab === 'preferences'): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-cog me-2 text-primary"></i>
                            Preferências
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="?tab=preferences">
                            
                            <h6 class="mb-3">Notificações</h6>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="email_notifications" 
                                       name="email_notifications"
                                       <?= ($user['email_notifications'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="email_notifications">
                                    Receber notificações por email
                                </label>
                            </div>
                            
                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="newsletter" 
                                       name="newsletter"
                                       <?= ($user['newsletter'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="newsletter">
                                    Receber newsletter semanal
                                </label>
                            </div>
                            
                            <hr>
                            
                            <h6 class="mb-3">Privacidade</h6>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="profile_public" 
                                       name="profile_public"
                                       <?= ($user['profile_public'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="profile_public">
                                    Tornar meu perfil público
                                </label>
                            </div>
                            
                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="show_email" 
                                       name="show_email"
                                       <?= ($user['show_email'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="show_email">
                                    Mostrar email no perfil público
                                </label>
                            </div>
                            
                            <hr>
                            
                            <h6 class="mb-3">Aparência</h6>
                            <div class="mb-4">
                                <label for="theme" class="form-label">Tema</label>
                                <select class="form-select" id="theme" name="theme" style="max-width: 200px;">
                                    <option value="light" <?= ($user['theme'] ?? 'light') === 'light' ? 'selected' : '' ?>>
                                        ☀️ Claro
                                    </option>
                                    <option value="dark" <?= ($user['theme'] ?? 'light') === 'dark' ? 'selected' : '' ?>>
                                        🌙 Escuro
                                    </option>
                                    <option value="auto" <?= ($user['theme'] ?? 'light') === 'auto' ? 'selected' : '' ?>>
                                        🔄 Automático
                                    </option>
                                </select>
                            </div>
                            
                            <button type="submit" name="update_preferences" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Salvar Preferências
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Tab: Segurança -->
            <?php if ($activeTab === 'security'): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-lock me-2 text-primary"></i>
                            Segurança
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="?tab=security">
                            <h6 class="mb-3">Alterar Senha</h6>
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Senha Atual</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="current_password" 
                                       name="current_password" 
                                       required
                                       style="max-width: 400px;">
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Nova Senha</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="new_password" 
                                       name="new_password" 
                                       required 
                                       minlength="6"
                                       style="max-width: 400px;">
                                <small class="text-muted">Mínimo de 6 caracteres</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmar Nova Senha</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       required 
                                       minlength="6"
                                       style="max-width: 400px;">
                            </div>
                            
                            <button type="submit" name="update_password" class="btn btn-danger">
                                <i class="fas fa-key me-2"></i> Alterar Senha
                            </button>
                        </form>
                        
                        <hr class="my-4">
                        
                        <h6>Informações de Segurança</h6>
                        <ul class="list-unstyled text-muted small">
                            <li><i class="fas fa-info-circle me-2"></i> Última atualização: <?= formatDate($user['updated_at'] ?? $user['created_at'], 'd/m/Y H:i') ?></li>
                            <li><i class="fas fa-desktop me-2"></i> Dispositivo atual: <?= escape($_SERVER['HTTP_USER_AGENT'] ?? 'Desconhecido') ?></li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>

<script>
// Contador de caracteres da biografia
document.getElementById('bio')?.addEventListener('input', function() {
    document.getElementById('bioCounter').textContent = this.value.length;
});

// Preview do avatar
document.getElementById('avatar')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Validação de senha
document.getElementById('confirm_password')?.addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    if (this.value !== newPassword) {
        this.setCustomValidity('As senhas não coincidem');
    } else {
        this.setCustomValidity('');
    }
});

// Carregar repositórios do GitHub (se tiver username)
<?php if (!empty($user['github'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    const reposContainer = document.getElementById('github-repos');
    if (reposContainer) {
        fetch('https://api.github.com/users/<?= escape($user['github']) ?>/repos?sort=updated&per_page=6')
            .then(response => response.json())
            .then(repos => {
                if (repos.length > 0) {
                    let html = '<div class="row">';
                    repos.forEach(repo => {
                        html += `
                            <div class="col-md-6 mb-2">
                                <div class="card card-body py-2 px-3">
                                    <a href="${repo.html_url}" target="_blank" class="fw-bold text-decoration-none">
                                        ${repo.name}
                                    </a>
                                    <small class="text-muted">${repo.description || 'Sem descrição'}</small>
                                    <small>
                                        <span class="me-2">⭐ ${repo.stargazers_count}</span>
                                        <span>${repo.language || 'N/A'}</span>
                                    </small>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    reposContainer.innerHTML = html;
                } else {
                    reposContainer.innerHTML = '<p class="text-muted">Nenhum repositório encontrado.</p>';
                }
            })
            .catch(() => {
                reposContainer.innerHTML = '<p class="text-muted">Erro ao carregar repositórios.</p>';
            });
    }
});
<?php endif; ?>
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>