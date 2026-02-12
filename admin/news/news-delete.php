<?php
session_start();
require_once '../config/database.php';

$news_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($news_id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
        $stmt->execute([$news_id]);
        $_SESSION['success_message'] = "Notícia excluída com sucesso!";
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Erro ao excluir notícia: " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "ID inválido.";
}

header('Location: news-list.php');
exit;