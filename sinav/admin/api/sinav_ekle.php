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
if(!isset($input['sablon_id']) || !isset($input['sinav_tarihi']) || !isset($input['son_basvuru'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Eksik veri']);
    exit;
}

try {
    $db->beginTransaction();

    // Şablonun var olduğunu kontrol et
    $stmt = $db->prepare("SELECT id FROM sinav_sablonlari WHERE id = ?");
    $stmt->execute([$input['sablon_id']]);
    if(!$stmt->fetch()) {
        throw new Exception('Geçersiz şablon seçimi');
    }

    // Tarihleri kontrol et
    $sinavTarihi = new DateTime($input['sinav_tarihi']);
    $sonBasvuru = new DateTime($input['son_basvuru']);
    $now = new DateTime();

    if($sonBasvuru >= $sinavTarihi) {
        throw new Exception('Son başvuru tarihi, sınav tarihinden önce olmalıdır');
    }

    if($sonBasvuru < $now) {
        throw new Exception('Son başvuru tarihi geçmiş bir tarih olamaz');
    }

    // Sınavı ekle
    $stmt = $db->prepare("INSERT INTO sinavlar (sablon_id, sinav_tarihi, son_basvuru_tarihi, aktif, created_at) VALUES (?, ?, ?, 1, NOW())");
    if(!$stmt->execute([
        $input['sablon_id'],
        $input['sinav_tarihi'],
        $input['son_basvuru']
    ])) {
        throw new Exception('Sınav eklenirken bir hata oluştu');
    }

    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Sınav başarıyla eklendi']);

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