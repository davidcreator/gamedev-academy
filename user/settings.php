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

// Processar alteração de email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_email'])) {
    $new_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['current_password'];
    
    // Verificar senha atual
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!password_verify($password, $user['password'])) {
        $error_message = 'Senha atual incorreta.';
    } else {
        // Verificar se email já existe
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$new_email, $user_id]);
        
        if ($stmt->fetch()) {
            $error_message = 'Este email já está em uso.';
        } else {
            // Atualizar email
            $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
            if ($stmt->execute([$new_email, $user_id])) {
                $success_message = 'Email atualizado com sucesso!';
                $_SESSION['email'] = $new_email;
            }
        }
    }
}

// Processar alteração de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $error_message = 'As senhas não coincidem.';
    } elseif (strlen($new_password) < 6) {
        $error_message = 'A nova senha deve ter pelo menos 6 caracteres.';
    } else {
        // Verificar senha atual
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!password_verify($current_password, $user['password'])) {
            $error_message = 'Senha atual incorreta.';
        } else {
            // Atualizar senha
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashed_password, $user_id])) {
                $success_message = 'Senha alterada com sucesso!';
            }
        }
    }
}

// Processar preferências
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_preferences'])) {
    // Aqui você pode adicionar preferências como notificações, tema, etc.
    $success_message = 'Preferências atualizadas!';
}

// Processar exclusão de conta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $password = $_POST['delete_password'];
    
    // Verificar senha
    $stmt = $conn->prepare("SELECT password, avatar FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!password_verify($password, $user['password'])) {
        $error_message = 'Senha incorreta. A conta não foi excluída.';
    } else {
        // Deletar avatar se existir
        if ($user['avatar'] && $user['avatar'] !== 'default.png') {
            $avatar_path = 'uploads/avatars/' . $user['avatar'];
            if (file_exists($avatar_path)) {
                unlink($avatar_path);
            }
        }
        
        // Deletar conta (as FKs com CASCADE cuidarão das tabelas relacionadas)
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        
        // Fazer logout
        session_destroy();
        header('Location: index.php?account_deleted=1');
        exit;
    }
}

// Buscar dados do usuário
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - GameDev Academy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .tab-active {
            border-bottom: 2px solid #6366f1;
            color: #6366f1;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'includes/navbar.php'; ?>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-cog mr-2 text-indigo-600"></i>
                Configurações
            </h1>
            <p class="mt-2 text-gray-600">Gerencie suas configurações de conta e preferências</p>
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

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button onclick="showTab('account')" id="tab-account" class="tab-active py-2 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-user mr-2"></i>Conta
                </button>
                <button onclick="showTab('security')" id="tab-security" class="py-2 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700">
                    <i class="fas fa-shield-alt mr-2"></i>Segurança
                </button>
                <button onclick="showTab('preferences')" id="tab-preferences" class="py-2 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700">
                    <i class="fas fa-sliders-h mr-2"></i>Preferências
                </button>
                <button onclick="showTab('danger')" id="tab-danger" class="py-2 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Zona de Perigo
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <!-- Conta Tab -->
        <div id="content-account" class="tab-content">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Informações da Conta</h2>
                
                <!-- Informações Básicas -->
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nome de Usuário</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled class="mt-1 block w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-500">
                        <p class="mt-1 text-sm text-gray-500">O nome de usuário não pode ser alterado</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Membro desde</label>
                        <input type="text" value="<?php echo date('d/m/Y', strtotime($user['created_at'])); ?>" disabled class="mt-1 block w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-500">
                    </div>
                </div>

                <!-- Alterar Email -->
                <form method="POST" class="border-t pt-6">
                    <h3 class="text-lg font-medium mb-4">Alterar Email</h3>
                    <input type="hidden" name="update_email" value="1">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Novo Email</label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>"
                                   required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        
                        <div>
                            <label for="current_password_email" class="block text-sm font-medium text-gray-700">Senha Atual</label>
                            <input type="password" 
                                   id="current_password_email" 
                                   name="current_password" 
                                   required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        
                        <button type="submit" class="bg-indigo-600 text-white font-medium py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Atualizar Email
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Segurança Tab -->
        <div id="content-security" class="tab-content hidden">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Segurança</h2>
                
                <!-- Alterar Senha -->
                <form method="POST">
                    <h3 class="text-lg font-medium mb-4">Alterar Senha</h3>
                    <input type="hidden" name="update_password" value="1">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700">Senha Atual</label>
                            <input type="password" 
                                   id="current_password" 
                                   name="current_password" 
                                   required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700">Nova Senha</label>
                            <input type="password" 
                                   id="new_password" 
                                   name="new_password" 
                                   required
                                   minlength="6"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="mt-1 text-sm text-gray-500">Mínimo de 6 caracteres</p>
                        </div>
                        
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirmar Nova Senha</label>
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   required
                                   minlength="6"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        
                        <button type="submit" class="bg-indigo-600 text-white font-medium py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Alterar Senha
                        </button>
                    </div>
                </form>

                <!-- Sessões Ativas -->
                <div class="mt-8 pt-8 border-t">
                    <h3 class="text-lg font-medium mb-4">Sessões Ativas</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium">Sessão Atual</p>
                                <p class="text-sm text-gray-600">
                                    IP: <?php echo $_SERVER['REMOTE_ADDR']; ?><br>
                                    Último acesso: <?php echo date('d/m/Y H:i'); ?>
                                </p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Ativa
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preferências Tab -->
        <div id="content-preferences" class="tab-content hidden">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Preferências</h2>
                
                <form method="POST">
                    <input type="hidden" name="update_preferences" value="1">
                    
                    <div class="space-y-6">
                        <!-- Notificações -->
                        <div>
                            <h3 class="text-lg font-medium mb-3">Notificações por Email</h3>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-3">
                                    <span>Novos cursos e atualizações</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-3">
                                    <span>Conquistas desbloqueadas</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-3">
                                    <span>Newsletter semanal</span>
                                </label>
                            </div>
                        </div>

                        <!-- Privacidade -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-medium mb-3">Privacidade</h3>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-3">
                                    <span>Mostrar perfil publicamente</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-3">
                                    <span>Mostrar conquistas no perfil</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-3">
                                    <span>Aparecer no ranking</span>
                                </label>
                            </div>
                        </div>

                        <!-- Aparência -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-medium mb-3">Aparência</h3>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tema</label>
                                <select class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    <option>Claro</option>
                                    <option>Escuro</option>
                                    <option>Automático (Sistema)</option>
                                </select>
                            </div>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="bg-indigo-600 text-white font-medium py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Salvar Preferências
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Zona de Perigo Tab -->
        <div id="content-danger" class="tab-content hidden">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4 text-red-600">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Zona de Perigo
                </h2>
                
                <!-- Exportar Dados -->
                <div class="border border-gray-200 rounded-lg p-4 mb-4">
                    <h3 class="font-medium mb-2">Exportar Dados</h3>
                    <p class="text-sm text-gray-600 mb-3">Baixe todos os seus dados em formato JSON</p>
                    <button class="bg-gray-600 text-white font-medium py-2 px-4 rounded-md hover:bg-gray-700">
                        <i class="fas fa-download mr-2"></i>
                        Exportar Dados
                    </button>
                </div>

                <!-- Excluir Conta -->
                <div class="border border-red-200 bg-red-50 rounded-lg p-4">
                    <h3 class="font-medium text-red-800 mb-2">Excluir Conta Permanentemente</h3>
                    <p class="text-sm text-red-600 mb-3">
                        <strong>Atenção:</strong> Esta ação é irreversível. Todos os seus dados, progresso, conquistas e certificados serão permanentemente excluídos.
                    </p>
                    
                    <button onclick="document.getElementById('deleteModal').classList.remove('hidden')" 
                            class="bg-red-600 text-white font-medium py-2 px-4 rounded-md hover:bg-red-700">
                        <i class="fas fa-trash mr-2"></i>
                        Excluir Minha Conta
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Exclusão -->
    <div id="deleteModal" class="hidden fixed z-10 inset-0 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form method="POST">
                    <input type="hidden" name="delete_account" value="1">
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-exclamation-triangle text-red-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Excluir Conta Permanentemente
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 mb-4">
                                        Esta ação não pode ser desfeita. Por favor, digite sua senha para confirmar.
                                    </p>
                                    <input type="password" 
                                           name="delete_password" 
                                           placeholder="Digite sua senha"
                                           required
                                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Excluir Permanentemente
                        </button>
                        <button type="button" onclick="document.getElementById('deleteModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Esconder todas as tabs
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remover classe ativa de todas as tabs
            document.querySelectorAll('[id^="tab-"]').forEach(tab => {
                tab.classList.remove('tab-active');
                tab.classList.add('text-gray-500');
            });
            
            // Mostrar tab selecionada
            document.getElementById('content-' + tabName).classList.remove('hidden');
            document.getElementById('tab-' + tabName).classList.add('tab-active');
            document.getElementById('tab-' + tabName).classList.remove('text-gray-500');
        }
    </script>
</body>
</html>