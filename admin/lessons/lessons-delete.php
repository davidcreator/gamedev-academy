<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';

// Verificar se é admin
checkAdmin();

$lesson_id = $_GET['id'] ?? 0;

if ($lesson_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM lessons WHERE id = ?");
        $stmt->execute([$lesson_id]);
        $_SESSION['success_message'] = "Aula excluída com sucesso!";
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Erro ao excluir aula: " . $e->getMessage();
    }
}

header('Location: index.php');
exit;
?>