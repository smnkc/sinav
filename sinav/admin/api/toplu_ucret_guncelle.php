<?php
session_start();
require_once '../../config/db.php';

if(!isset($_SESSION['admin_id'])) {
    die(json_encode(['success' => false, 'message' => 'Yetkisiz erişim']));
}

$input = json_decode(file_get_contents('php://input'), true);

if(!isset($input['ucretler']) || !is_array($input['ucretler'])) {
    die(json_encode(['success' => false, 'message' => 'Geçersiz veri formatı']));
}

try {
    $db->beginTransaction();

    $stmt = $db->prepare("UPDATE sinav_sablonlari SET 
        gozetmen_ucret = :gozetmen_ucret,
        yedek_ucret = :yedek_ucret,
        baskan_ucret = :baskan_ucret
        WHERE id = :id");

    foreach($input['ucretler'] as $id => $ucret) {
        $stmt->execute([
            'gozetmen_ucret' => $ucret['gozetmen_ucret'],
            'yedek_ucret' => $ucret['yedek_ucret'],
            'baskan_ucret' => $ucret['baskan_ucret'],
            'id' => $id
        ]);
    }

    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Ücretler başarıyla güncellendi']);
} catch(PDOException $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
} 