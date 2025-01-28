<?php
session_start();
require_once '../../config/db.php';

header('Content-Type: application/json');

if(!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim!']);
    exit();
}

try {
    if(empty($_POST['sinav_adi']) || empty($_POST['gozetmen_ucret']) || 
       empty($_POST['yedek_ucret']) || empty($_POST['baskan_ucret']) || 
       empty($_POST['basvuru_link'])) {
        throw new Exception('Tüm alanları doldurun!');
    }

    $sinav_adi = $_POST['sinav_adi'];
    $gozetmen_ucret = floatval($_POST['gozetmen_ucret']);
    $yedek_ucret = floatval($_POST['yedek_ucret']);
    $baskan_ucret = floatval($_POST['baskan_ucret']);
    $basvuru_link = $_POST['basvuru_link'];

    // Ücretlerin pozitif olduğunu kontrol et
    if($gozetmen_ucret <= 0 || $yedek_ucret <= 0 || $baskan_ucret <= 0) {
        throw new Exception('Ücretler pozitif olmalıdır!');
    }

    // Başvuru linkinin geçerli bir URL olduğunu kontrol et
    if(!filter_var($basvuru_link, FILTER_VALIDATE_URL)) {
        throw new Exception('Geçersiz başvuru linki!');
    }

    // Şablonu ekle
    $stmt = $db->prepare("INSERT INTO sinav_sablonlari (sinav_adi, gozetmen_ucret, yedek_ucret, baskan_ucret, basvuru_link) 
                         VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$sinav_adi, $gozetmen_ucret, $yedek_ucret, $baskan_ucret, $basvuru_link]);

    echo json_encode(['success' => true]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 