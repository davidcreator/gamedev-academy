<?php
// Arquivo temporário para simular login
require_once __DIR__ . '/../includes/config.php';

// Simula um login para teste
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Usuário Teste';
$_SESSION['user_email'] = 'teste@example.com';
$_SESSION['user_role'] = 'user';

echo "<h1>✅ Sessão criada para teste!</h1>";
echo "<p>Agora você pode acessar o perfil:</p>";
echo "<a href='profile.php'>Ir para o Perfil</a>";
?>