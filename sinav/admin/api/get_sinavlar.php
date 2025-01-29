<?php
session_start();
require_once '../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

// Yönetici girişi kontrolü
if(!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

try {
    $type = $_GET['type'] ?? 'aktif';
    $where = "1=1";
    
    if($type === 'aktif') {
        $where = "s.aktif = 1 AND s.sinav_tarihi > NOW() AND s.son_basvuru_tarihi > NOW()";
    }

    $stmt = $db->prepare("
        SELECT s.*, ss.sinav_adi, ss.gozetmen_ucret, ss.yedek_ucret, ss.baskan_ucret, ss.basvuru_link 
        FROM sinavlar s 
        LEFT JOIN sinav_sablonlari ss ON s.sablon_id = ss.id 
        WHERE {$where} 
        ORDER BY s.sinav_tarihi
    ");
    $stmt->execute();
    $sinavlar = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'sinavlar' => $sinavlar
    ]);

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 