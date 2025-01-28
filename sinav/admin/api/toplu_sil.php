<?php
session_start();
require_once '../../config/db.php';

header('Content-Type: application/json');

// Yönetici girişi kontrolü
if(!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

try {
    // Süresi geçen sınavları sil
    $stmt = $db->prepare("DELETE FROM sinavlar 
                         WHERE (son_basvuru_tarihi < NOW() OR sinav_tarihi < NOW())
                         AND aktif = 1");
    $stmt->execute();
    
    $silinensayisi = $stmt->rowCount();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Süresi geçen sınavlar başarıyla silindi',
        'count' => $silinensayisi
    ]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 