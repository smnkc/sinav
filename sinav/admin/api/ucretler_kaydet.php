<?php
// Hata raporlamasını aktif et (geliştirme aşamasında)
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
if(!isset($input['ucretler']) || !is_array($input['ucretler'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Geçersiz veri formatı']);
    exit;
}

try {
    $db->beginTransaction();

    // Önce tüm ücret verilerini sil
    $stmt = $db->prepare("DELETE FROM ucretler");
    if(!$stmt->execute()) {
        throw new Exception('Mevcut ücret verileri silinirken hata oluştu');
    }

    // Yeni verileri ekle
    $stmt = $db->prepare("INSERT INTO ucretler (sinav_turu, baskan_ucret, gozetmen_ucret, yedek_ucret, created_at) VALUES (?, ?, ?, ?, NOW())");
    
    foreach($input['ucretler'] as $ucret) {
        // Zorunlu alanları kontrol et
        if(empty($ucret['sinav_turu'])) {
            throw new Exception('Sınav türü alanı zorunludur');
        }

        // Ücretlerin sayısal ve pozitif olduğunu kontrol et
        $baskan_ucret = floatval($ucret['baskan_ucret'] ?? 0);
        $gozetmen_ucret = floatval($ucret['gozetmen_ucret'] ?? 0);
        $yedek_ucret = floatval($ucret['yedek_ucret'] ?? 0);

        if($baskan_ucret < 0 || $gozetmen_ucret < 0 || $yedek_ucret < 0) {
            throw new Exception('Ücretler negatif olamaz');
        }

        if(!$stmt->execute([
            $ucret['sinav_turu'],
            $baskan_ucret,
            $gozetmen_ucret,
            $yedek_ucret
        ])) {
            throw new Exception('Ücret verisi eklenirken hata oluştu');
        }
    }

    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Ücretler başarıyla güncellendi']);

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