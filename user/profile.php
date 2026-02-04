<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Verificar se está logado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = getConnection();
$success_message = '';
$error_message = '';

// Processar upload de avatar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $upload_dir = 'uploads/avatars/';
    
    // Criar diretório se não existir
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file = $_FILES['avatar'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Validar tipo de arquivo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types)) {
            $error_message = 'Tipo de arquivo inválido. Use JPG, PNG, GIF ou WebP.';
        } elseif ($file['size'] > $max_size) {
            $error_message = 'Arquivo muito grande. Máximo 5MB.';
        } else {
            // Gerar nome único
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = 'avatar_' . $user_id . '_' . time() . '.' . $extension;
            $upload_path = $upload_dir . $new_filename;
            
            // Deletar avatar antigo
            $stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $old_avatar = $stmt->fetchColumn();
            
            if ($old_avatar && $old_avatar !== 'default.png' && file_exists($upload_dir . $old_avatar)) {
                unlink($upload_dir . $old_avatar);
            }
            
            // Mover arquivo
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Atualizar banco de dados
                $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                $stmt->execute([$new_filename, $user_id]);
                $success_message = 'Avatar atualizado com sucesso!';
                
                // Atualizar sessão
                $_SESSION['avatar'] = $new_filename;
            } else {
                $error_message = 'Erro ao fazer upload do arquivo.';
            }
        }
    } else {
        $error_message = 'Erro no upload: ' . $file['error'];
    }
}

// Processar atualização de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize($_POST['full_name']);
    $bio = sanitize($_POST['bio']);
    $github_url = filter_var($_POST['github_url'], FILTER_SANITIZE_URL);
    $linkedin_url = filter_var($_POST['linkedin_url'], FILTER_SANITIZE_URL);
    $portfolio_url = filter_var($_POST['portfolio_url'], FILTER_SANITIZE_URL);
    
    $stmt = $conn->prepare("
        UPDATE users 
        SET full_name = ?, bio = ?, github_url = ?, linkedin_url = ?, portfolio_url = ?
        WHERE id = ?
    ");
    
    if ($stmt->execute([$full_name, $bio, $github_url, $linkedin_url, $portfolio_url, $user_id])) {
        $success_message = 'Perfil atualizado com sucesso!';
    } else {
        $error_message = 'Erro ao atualizar perfil.';
    }
}

// Buscar dados do usuário
$stmt = $conn->prepare("
    SELECT u.*, 
           l.title as level_title, 
           l.badge_icon,
           (SELECT COUNT(*) FROM enrollments WHERE user_id = u.id) as courses_count,
           (SELECT COUNT(*) FROM lesson_progress WHERE user_id = u.id AND is_completed = 1) as completed_lessons,
           (SELECT COUNT(*) FROM user_achievements WHERE user_id = u.id) as achievements_count
    FROM users u
    LEFT JOIN levels l ON l.level_number = u.level
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Buscar conquistas recentes
$stmt = $conn->prepare("
    SELECT a.*, ua.unlocked_at 
    FROM user_achievements ua
    JOIN achievements a ON a.id = ua.achievement_id
    WHERE ua.user_id = ?
    ORDER BY ua.unlocked_at DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar cursos em progresso
$stmt = $conn->prepare("
    SELECT c.*, e.progress_percentage, e.last_accessed
    FROM enrollments e
    JOIN courses c ON c.id = e.course_id
    WHERE e.user_id = ? AND e.progress_percentage < 100
    ORDER BY e.last_accessed DESC
    LIMIT 3
");
$stmt->execute([$user_id]);
$courses_in_progress = $stmt->fetchAll(PDO::FETCH_ASSOC);

$avatar_url = 'uploads/avatars/' . ($user['avatar'] ?? 'default.png');
if (!file_exists($avatar_url)) {
    $avatar_url = 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name']) . '&background=6366f1&color=fff&size=200';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - GameDev Academy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .avatar-upload {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto;
        }
        .avatar-upload input[type="file"] {
            display: none;
        }
        .avatar-upload label {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #6366f1;
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.3s;
        }
        .avatar-upload label:hover {
            background: #4f46e5;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'includes/navbar.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header do Perfil -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
            <div class="gradient-bg h-32"></div>
            <div class="px-6 pb-6">
                <div class="-mt-20 flex flex-col sm:flex-row items-center sm:items-end space-y-4 sm:space-y-0 sm:space-x-6">
                    <!-- Avatar Upload -->
                    <form id="avatarForm" method="POST" enctype="multipart/form-data" class="avatar-upload">
                        <img src="<?php echo $avatar_url; ?>" 
                             alt="Avatar" 
                             class="w-36 h-36 rounded-full border-4 border-white shadow-lg object-cover">
                        <label for="avatarInput" title="Alterar foto">
                            <i class="fas fa-camera"></i>
                        </label>
                        <input type="file" 
                               id="avatarInput" 
                               name="avatar" 
                               accept="image/jpeg,image/png,image/gif,image/webp"
                               onchange="document.getElementById('avatarForm').submit()">
                    </form>

                    <!-- Info do Usuário -->
                    <div class="flex-1 text-center sm:text-left">
                        <h1 class="text-3xl font-bold text-gray-900 flex items-center justify-center sm:justify-start gap-2">
                            <?php echo htmlspecialchars($user['full_name']); ?>
                            <?php if($user['badge_icon']): ?>
                                <span class="text-2xl"><?php echo $user['badge_icon']; ?></span>
                            <?php endif; ?>
                        </h1>
                        <p class="text-gray-600">@<?php echo htmlspecialchars($user['username']); ?></p>
                        <div class="mt-2 flex flex-wrap gap-2 justify-center sm:justify-start">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                                <i class="fas fa-layer-group mr-1"></i> Nível <?php echo $user['level']; ?> - <?php echo $user['level_title'] ?? 'Iniciante'; ?>
                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                <i class="fas fa-bolt mr-1"></i> <?php echo number_format($user['xp_total']); ?> XP
                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                <i class="fas fa-coins mr-1"></i> <?php echo number_format($user['coins']); ?> Moedas
                            </span>
                            <?php if($user['streak_days'] > 0): ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                <i class="fas fa-fire mr-1"></i> <?php echo $user['streak_days']; ?> dias de streak
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Botão de Configurações -->
                    <div>
                        <a href="settings.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-cog mr-2"></i>
                            Configurações
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mensagens de Feedback -->
        <?php if($success_message): ?>
        <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded">
            <p class="font-medium"><i class="fas fa-check-circle mr-2"></i><?php echo $success_message; ?></p>
        </div>
        <?php endif; ?>

        <?php if($error_message): ?>
        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded">
            <p class="font-medium"><i class="fas fa-exclamation-circle mr-2"></i><?php echo $error_message; ?></p>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Coluna Principal -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Estatísticas -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="stat-card rounded-lg p-4 text-center card-hover">
                        <div class="text-3xl font-bold"><?php echo $user['courses_count']; ?></div>
                        <div class="text-sm opacity-90">Cursos</div>
                    </div>
                    <div class="stat-card rounded-lg p-4 text-center card-hover">
                        <div class="text-3xl font-bold"><?php echo $user['completed_lessons']; ?></div>
                        <div class="text-sm opacity-90">Aulas</div>
                    </div>
                    <div class="stat-card rounded-lg p-4 text-center card-hover">
                        <div class="text-3xl font-bold"><?php echo $user['achievements_count']; ?></div>
                        <div class="text-sm opacity-90">Conquistas</div>
                    </div>
                    <div class="stat-card rounded-lg p-4 text-center card-hover">
                        <div class="text-3xl font-bold"><?php echo $user['streak_days']; ?></div>
                        <div class="text-sm opacity-90">Streak</div>
                    </div>
                </div>

                <!-- Formulário de Edição -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold mb-4 flex items-center">
                        <i class="fas fa-user-edit mr-2 text-indigo-600"></i>
                        Editar Perfil
                    </h2>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="update_profile" value="1">
                        
                        <div>
                            <label for="full_name" class="block text-sm font-medium text-gray-700">Nome Completo</label>
                            <input type="text" 
                                   id="full_name" 
                                   name="full_name" 
                                   value="<?php echo htmlspecialchars($user['full_name']); ?>"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label for="bio" class="block text-sm font-medium text-gray-700">Biografia</label>
                            <textarea id="bio" 
                                      name="bio" 
                                      rows="4"
                                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>

                        <div>
                            <label for="github_url" class="block text-sm font-medium text-gray-700">
                                <i class="fab fa-github mr-1"></i> GitHub
                            </label>
                            <input type="url" 
                                   id="github_url" 
                                   name="github_url" 
                                   value="<?php echo htmlspecialchars($user['github_url'] ?? ''); ?>"
                                   placeholder="https://github.com/seu-usuario"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label for="linkedin_url" class="block text-sm font-medium text-gray-700">
                                <i class="fab fa-linkedin mr-1"></i> LinkedIn
                            </label>
                            <input type="url" 
                                   id="linkedin_url" 
                                   name="linkedin_url" 
                                   value="<?php echo htmlspecialchars($user['linkedin_url'] ?? ''); ?>"
                                   placeholder="https://linkedin.com/in/seu-perfil"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label for="portfolio_url" class="block text-sm font-medium text-gray-700">
                                <i class="fas fa-globe mr-1"></i> Portfolio
                            </label>
                            <input type="url" 
                                   id="portfolio_url" 
                                   name="portfolio_url" 
                                   value="<?php echo htmlspecialchars($user['portfolio_url'] ?? ''); ?>"
                                   placeholder="https://seu-portfolio.com"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <button type="submit" class="w-full bg-indigo-600 text-white font-medium py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                            <i class="fas fa-save mr-2"></i>
                            Salvar Alterações
                        </button>
                    </form>
                </div>

                <!-- Cursos em Progresso -->
                <?php if(!empty($courses_in_progress)): ?>
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold mb-4 flex items-center">
                        <i class="fas fa-book-open mr-2 text-indigo-600"></i>
                        Cursos em Progresso
                    </h2>
                    <div class="space-y-4">
                        <?php foreach($courses_in_progress as $course): ?>
                        <div class="border rounded-lg p-4 hover:shadow-md transition card-hover">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-semibold text-lg">
                                        <a href="course.php?id=<?php echo $course['id']; ?>" class="text-indigo-600 hover:text-indigo-800">
                                            <?php echo htmlspecialchars($course['title']); ?>
                                        </a>
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Último acesso: <?php echo date('d/m/Y', strtotime($course['last_accessed'])); ?>
                                    </p>
                                </div>
                                <span class="text-2xl font-bold text-indigo-600">
                                    <?php echo round($course['progress_percentage']); ?>%
                                </span>
                            </div>
                            <div class="mt-3">
                                <div class="bg-gray-200 rounded-full h-2">
                                    <div class="bg-indigo-600 h-2 rounded-full" style="width: <?php echo $course['progress_percentage']; ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Coluna Lateral -->
            <div class="space-y-6">
                <!-- Conquistas Recentes -->
                <?php if(!empty($recent_achievements)): ?>
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold mb-4 flex items-center">
                        <i class="fas fa-trophy mr-2 text-yellow-500"></i>
                        Conquistas Recentes
                    </h2>
                    <div class="space-y-3">
                        <?php foreach($recent_achievements as $achievement): ?>
                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                            <div class="text-2xl"><?php echo $achievement['icon']; ?></div>
                            <div class="flex-1">
                                <div class="font-semibold"><?php echo htmlspecialchars($achievement['name']); ?></div>
                                <div class="text-xs text-gray-500">
                                    <?php echo date('d/m/Y', strtotime($achievement['unlocked_at'])); ?>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-semibold text-indigo-600">+<?php echo $achievement['xp_reward']; ?> XP</div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="achievements.php" class="mt-4 block text-center text-indigo-600 hover:text-indigo-800 font-medium">
                        Ver todas as conquistas →
                    </a>
                </div>
                <?php endif; ?>

                <!-- Links Sociais -->
                <?php if($user['github_url'] || $user['linkedin_url'] || $user['portfolio_url']): ?>
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold mb-4 flex items-center">
                        <i class="fas fa-link mr-2 text-indigo-600"></i>
                        Links
                    </h2>
                    <div class="space-y-2">
                        <?php if($user['github_url']): ?>
                        <a href="<?php echo htmlspecialchars($user['github_url']); ?>" target="_blank" class="flex items-center p-3 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition">
                            <i class="fab fa-github text-xl mr-3"></i>
                            <span>GitHub</span>
                        </a>
                        <?php endif; ?>
                        
                        <?php if($user['linkedin_url']): ?>
                        <a href="<?php echo htmlspecialchars($user['linkedin_url']); ?>" target="_blank" class="flex items-center p-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <i class="fab fa-linkedin text-xl mr-3"></i>
                            <span>LinkedIn</span>
                        </a>
                        <?php endif; ?>
                        
                        <?php if($user['portfolio_url']): ?>
                        <a href="<?php echo htmlspecialchars($user['portfolio_url']); ?>" target="_blank" class="flex items-center p-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                            <i class="fas fa-globe text-xl mr-3"></i>
                            <span>Portfolio</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Preview da imagem antes do upload
        document.getElementById('avatarInput').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const maxSize = 5 * 1024 * 1024; // 5MB
                if (e.target.files[0].size > maxSize) {
                    alert('Arquivo muito grande! Máximo 5MB.');
                    e.target.value = '';
                    return;
                }
            }
        });
    </script>
</body>
</html>