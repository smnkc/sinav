<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

// Yetki kontrolü
if(!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

// JSON verisini al
$json = file_get_contents('php://input');
if(!$json) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Veri gönderilmedi']);
    exit;
}

// JSON'ı decode et
$input = json_decode($json, true);
if(json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Geçersiz JSON verisi']);
    exit;
}

// Gerekli alanları kontrol et
if(!isset($input['takvim']) || !is_array($input['takvim'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Geçersiz veri formatı']);
    exit;
}

try {
    $db->beginTransaction();

    // Önce tüm takvim verilerini sil
    $stmt = $db->prepare("DELETE FROM sinav_takvimi");
    if(!$stmt->execute()) {
        throw new Exception('Mevcut takvim verileri silinirken hata oluştu');
    }

    // Yeni verileri ekle
    $stmt = $db->prepare("INSERT INTO sinav_takvimi (sinav_turu, tarih, aciklama, created_at) VALUES (?, ?, ?, NOW())");
    
    foreach($input['takvim'] as $item) {
        // Zorunlu alanları kontrol et
        if(empty($item['sinav_turu']) || empty($item['tarih'])) {
            throw new Exception('Sınav türü ve tarih alanları zorunludur');
        }

        // Tarih formatını kontrol et
        $tarih = date('Y-m-d', strtotime($item['tarih']));
        if($tarih === false) {
            throw new Exception('Geçersiz tarih formatı');
        }

        if(!$stmt->execute([
            $item['sinav_turu'],
            $tarih,
            $item['aciklama'] ?? ''
        ])) {
            throw new Exception('Takvim verisi eklenirken hata oluştu');
        }
    }

    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Takvim başarıyla güncellendi']);

} catch(Exception $e) {
    $db->rollBack();
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
} catch(PDOException $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Veritabanı hatası oluştu'
    ]);
} 