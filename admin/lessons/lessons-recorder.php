<?php
/**
 * Reorder Lessons
 * Script para reordenar lições via AJAX (drag & drop)
 */

require_once '../../includes/init.php';

// Verificar autenticação
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Obter dados JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['lesson_ids']) || !is_array($data['lesson_ids'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

$lessonIds = $data['lesson_ids'];
$db = Database::getInstance();

try {
    // Atualizar ordem de cada lição
    foreach ($lessonIds as $order => $lessonId) {
        $lessonId = intval($lessonId);
        $newOrder = $order + 1; // Começar de 1, não de 0
        
        $db->update('lessons', [
            'order_position' => $newOrder
        ], 'id = :id', ['id' => $lessonId]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Ordem atualizada com sucesso'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar ordem: ' . $e->getMessage()
    ]);
}