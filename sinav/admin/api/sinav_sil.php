<?php
session_start();
require_once '../../config/db.php';

// Yönetici girişi kontrolü
if(!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

// JSON verisini al
$data = json_decode(file_get_contents('php://input'), true);

if(!isset($data['id']) || !is_numeric($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz sınav ID']);
    exit;
}

try {
    // Sınavı sil
    $stmt = $db->prepare("DELETE FROM sinavlar WHERE id = ?");
    $stmt->execute([$data['id']]);
    
    if($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Sınav başarıyla silindi']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Sınav bulunamadı']);
    }
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 