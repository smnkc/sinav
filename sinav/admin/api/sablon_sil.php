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

// ID kontrolü
if(!isset($input['id']) || !is_numeric($input['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Geçersiz şablon ID']);
    exit;
}

try {
    $db->beginTransaction();

    // Önce bu şablonu kullanan aktif sınav var mı kontrol et
    $stmt = $db->prepare("SELECT COUNT(*) FROM sinavlar WHERE sablon_id = ? AND aktif = 1");
    $stmt->execute([$input['id']]);
    $aktifSinavSayisi = $stmt->fetchColumn();

    if($aktifSinavSayisi > 0) {
        throw new Exception('Bu şablon aktif sınavlarda kullanılıyor. Önce ilgili sınavları silmelisiniz.');
    }

    // Şablonu sil
    $stmt = $db->prepare("DELETE FROM sinav_sablonlari WHERE id = ?");
    if(!$stmt->execute([$input['id']])) {
        throw new Exception('Şablon silinirken bir hata oluştu');
    }

    $db->commit();
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Şablon başarıyla silindi']);

} catch(Exception $e) {
    $db->rollBack();
    http_response_code(500);
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