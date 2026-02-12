<?php
// includes/upload-handler.php
session_start();
require_once '../config/database.php';

// Verificar se o usuário está autenticado e é admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Acesso negado');
}

$accepted_origins = array("http://localhost", "http://127.0.0.1", "https://yourdomain.com");

$imageFolder = "../uploads/content/";

if (!file_exists($imageFolder)) {
    mkdir($imageFolder, 0777, true);
}

reset($_FILES);
$temp = current($_FILES);

if (is_uploaded_file($temp['tmp_name'])) {
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        if (in_array($_SERVER['HTTP_ORIGIN'], $accepted_origins)) {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        } else {
            header("HTTP/1.1 403 Origin Denied");
            exit;
        }
    }
    
    // Sanitizar nome do arquivo
    $filename = $temp['name'];
    $filename = strtolower($filename);
    $filename = preg_replace('/[^a-z0-9_\-\.]/i', '_', $filename);
    
    // Adicionar timestamp para evitar duplicatas
    $filename = time() . '_' . $filename;
    
    // Validar extensão
    $allowed = array('png', 'jpg', 'jpeg', 'gif', 'webp');
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        header("HTTP/1.1 400 Invalid extension.");
        exit;
    }
    
    // Validar tamanho (máximo 5MB)
    if ($temp['size'] > 5242880) {
        header("HTTP/1.1 400 File too large.");
        exit;
    }
    
    // Mover arquivo
    if (move_uploaded_file($temp['tmp_name'], $imageFolder . $filename)) {
        echo json_encode(array('location' => $imageFolder . $filename));
    } else {
        header("HTTP/1.1 500 Server Error");
    }
} else {
    header("HTTP/1.1 500 Server Error");
}
?>