<?php
session_start();
require_once '../../config/db.php';

header('Content-Type: application/json');

if(!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim!']);
    exit();
}

try {
    if(empty($_POST['sablon_id']) || empty($_POST['sinav_tarihi']) || empty($_POST['son_basvuru'])) {
        throw new Exception('Tüm alanları doldurun!');
    }

    $sablon_id = $_POST['sablon_id'];
    $sinav_tarihi = $_POST['sinav_tarihi'];
    $son_basvuru = $_POST['son_basvuru'];

    // Şablonun var olduğunu kontrol et
    $stmt = $db->prepare("SELECT id FROM sinav_sablonlari WHERE id = ?");
    $stmt->execute([$sablon_id]);
    if(!$stmt->fetch()) {
        throw new Exception('Geçersiz şablon!');
    }

    // Sınavı ekle
    $stmt = $db->prepare("INSERT INTO sinavlar (sablon_id, sinav_tarihi, son_basvuru_tarihi, aktif) VALUES (?, ?, ?, 1)");
    $stmt->execute([$sablon_id, $sinav_tarihi, $son_basvuru]);

    echo json_encode(['success' => true]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 