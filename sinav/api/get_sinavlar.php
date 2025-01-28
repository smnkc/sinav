<?php
require_once '../config/db.php';

header('Content-Type: application/json');

try {
    // Aktif ve süresi geçmemiş sınavları getir
    $stmt = $db->query("SELECT s.*, ss.sinav_adi, ss.gozetmen_ucret, ss.yedek_ucret, ss.baskan_ucret, ss.basvuru_link 
                        FROM sinavlar s 
                        LEFT JOIN sinav_sablonlari ss ON s.sablon_id = ss.id 
                        WHERE s.aktif = 1 
                        AND s.sinav_tarihi > NOW() 
                        AND s.son_basvuru_tarihi > NOW() 
                        ORDER BY s.sinav_tarihi");
    $sinavlar = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'sinavlar' => $sinavlar]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 