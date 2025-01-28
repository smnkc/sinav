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
    $type = $_GET['type'] ?? 'aktif';
    
    $sql = "SELECT s.*, ss.sinav_adi 
            FROM sinavlar s 
            LEFT JOIN sinav_sablonlari ss ON s.sablon_id = ss.id";
    
    if($type === 'aktif') {
        $sql .= " WHERE s.aktif = 1 
                  AND s.sinav_tarihi > NOW() 
                  AND s.son_basvuru_tarihi > NOW()";
    }
    
    $sql .= " ORDER BY s.sinav_tarihi DESC";
    
    $stmt = $db->query($sql);
    $sinavlar = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'sinavlar' => $sinavlar]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 