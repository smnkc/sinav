<?php
session_start();
require_once '../config/db.php';

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Admin bilgilerini al
$stmt = $db->prepare("SELECT * FROM admin WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();

$success_message = '';
$error_message = '';

if(isset($_POST['update_profile'])) {
    $yeni_kullanici_adi = trim($_POST['username']);
    $mevcut_sifre = trim($_POST['current_password']);
    $yeni_sifre = trim($_POST['new_password']);
    $yeni_sifre_tekrar = trim($_POST['confirm_password']);

    try {
        // Mevcut şifreyi kontrol et
        if(!password_verify($mevcut_sifre, $admin['password'])) {
            throw new Exception("Mevcut şifre yanlış!");
        }

        // Kullanıcı adı değişikliği
        if($yeni_kullanici_adi != $admin['username']) {
            // Kullanıcı adının başka biri tarafından kullanılıp kullanılmadığını kontrol et
            $stmt = $db->prepare("SELECT id FROM admin WHERE username = ? AND id != ?");
            $stmt->execute([$yeni_kullanici_adi, $admin['id']]);
            if($stmt->fetch()) {
                throw new Exception("Bu kullanıcı adı zaten kullanılıyor!");
            }
        }

        // Şifre değişikliği
        if(!empty($yeni_sifre)) {
            if(strlen($yeni_sifre) < 6) {
                throw new Exception("Yeni şifre en az 6 karakter olmalıdır!");
            }
            if($yeni_sifre !== $yeni_sifre_tekrar) {
                throw new Exception("Yeni şifreler eşleşmiyor!");
            }
            $yeni_sifre_hash = password_hash($yeni_sifre, PASSWORD_DEFAULT);
        } else {
            $yeni_sifre_hash = $admin['password'];
        }

        // Güncelleme işlemi
        $stmt = $db->prepare("UPDATE admin SET username = ?, password = ? WHERE id = ?");
        $stmt->execute([$yeni_kullanici_adi, $yeni_sifre_hash, $admin['id']]);

        $success_message = "Profil bilgileri başarıyla güncellendi!";
        
        // Admin bilgilerini yeniden al
        $stmt = $db->prepare("SELECT * FROM admin WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch();

    } catch(Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Ayarları - Sınav Görevleri Takip Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Yönetici Paneli</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Panele Dön</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Çıkış Yap</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Profil Ayarları</h5>
                    </div>
                    <div class="card-body">
                        <?php if($success_message): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>

                        <?php if($error_message): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Kullanıcı Adı</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Mevcut Şifre</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Yeni Şifre (Boş bırakılabilir)</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Yeni Şifre Tekrar</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">Bilgileri Güncelle</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 