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
if(!isset($input['sinav_adi']) || !isset($input['basvuru_link'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Eksik veri']);
    exit;
}

try {
    $db->beginTransaction();

    if(empty($input['id'])) {
        // Yeni şablon ekleme
        $stmt = $db->prepare("INSERT INTO sinav_sablonlari (sinav_adi, basvuru_link, created_at) VALUES (?, ?, NOW())");
        if(!$stmt->execute([
            $input['sinav_adi'],
            $input['basvuru_link']
        ])) {
            throw new Exception('Şablon eklenirken bir hata oluştu');
        }
    } else {
        // Mevcut şablonu güncelleme
        $stmt = $db->prepare("UPDATE sinav_sablonlari SET sinav_adi = ?, basvuru_link = ? WHERE id = ?");
        if(!$stmt->execute([
            $input['sinav_adi'],
            $input['basvuru_link'],
            $input['id']
        ])) {
            throw new Exception('Şablon güncellenirken bir hata oluştu');
        }

        if($stmt->rowCount() === 0) {
            throw new Exception('Şablon bulunamadı');
        }
    }

    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Şablon başarıyla kaydedildi']);

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