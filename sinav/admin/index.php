<?php
session_start();
require_once '../config/db.php';

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Aktif sınavları getir
$stmt = $db->query("SELECT s.*, ss.sinav_adi, ss.gozetmen_ucret, ss.yedek_ucret, ss.baskan_ucret, ss.basvuru_link 
                    FROM sinavlar s 
                    LEFT JOIN sinav_sablonlari ss ON s.sablon_id = ss.id 
                    WHERE s.aktif = 1 
                    AND s.sinav_tarihi > NOW() 
                    AND s.son_basvuru_tarihi > NOW()
                    ORDER BY s.sinav_tarihi");
$sinavlar = $stmt->fetchAll();

// Şablonları getir
$stmt = $db->query("SELECT * FROM sinav_sablonlari ORDER BY sinav_adi");
$sablonlar = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Paneli - Sınav Görevleri Takip Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                        <a class="nav-link" href="../index.php" target="_blank">Siteyi Görüntüle</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profil.php">Profil Ayarları</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Çıkış Yap</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Yeni Sınav Ekle</h5>
                    </div>
                    <div class="card-body">
                        <form id="sinavForm">
                            <div class="mb-3">
                                <label for="sablon" class="form-label">Sınav Şablonu</label>
                                <select class="form-select" id="sablon" name="sablon_id" required>
                                    <option value="">Şablon Seçin</option>
                                    <?php foreach($sablonlar as $sablon): ?>
                                        <option value="<?php echo $sablon['id']; ?>"><?php echo $sablon['sinav_adi']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="sinav_tarihi" class="form-label">Sınav Tarihi</label>
                                <input type="datetime-local" class="form-control" id="sinav_tarihi" name="sinav_tarihi" required>
                            </div>
                            <div class="mb-3">
                                <label for="son_basvuru" class="form-label">Son Başvuru Tarihi</label>
                                <input type="datetime-local" class="form-control" id="son_basvuru" name="son_basvuru" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Sınavı Ekle</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Şablon Yönetimi</h5>
                    </div>
                    <div class="card-body">
                        <form id="sablonForm">
                            <div class="mb-3">
                                <label for="sinav_adi" class="form-label">Sınav Adı</label>
                                <input type="text" class="form-control" id="sinav_adi" name="sinav_adi" required>
                            </div>
                            <div class="mb-3">
                                <label for="gozetmen_ucret" class="form-label">Gözetmen Ücreti</label>
                                <input type="number" step="0.01" class="form-control" id="gozetmen_ucret" name="gozetmen_ucret" required>
                            </div>
                            <div class="mb-3">
                                <label for="yedek_ucret" class="form-label">Yedek Gözetmen Ücreti</label>
                                <input type="number" step="0.01" class="form-control" id="yedek_ucret" name="yedek_ucret" required>
                            </div>
                            <div class="mb-3">
                                <label for="baskan_ucret" class="form-label">Salon Başkanı Ücreti</label>
                                <input type="number" step="0.01" class="form-control" id="baskan_ucret" name="baskan_ucret" required>
                            </div>
                            <div class="mb-3">
                                <label for="basvuru_link" class="form-label">Başvuru Linki</label>
                                <input type="url" class="form-control" id="basvuru_link" name="basvuru_link" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Şablonu Kaydet</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Aktif Sınavlar</h5>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-primary active" onclick="showAktifSinavlar()">
                                <i class="fas fa-check-circle"></i> Aktif
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="showTumSinavlar()">
                                <i class="fas fa-list"></i> Tümü
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="topluSil()">
                                <i class="fas fa-trash"></i> Süresi Geçenleri Temizle
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Sınav Adı</th>
                                        <th>Sınav Tarihi</th>
                                        <th>Son Başvuru</th>
                                        <th>Durum</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody id="sinavlarTbody">
                                    <?php
                                    $stmt = $db->query("SELECT s.*, ss.sinav_adi 
                                                       FROM sinavlar s 
                                                       LEFT JOIN sinav_sablonlari ss ON s.sablon_id = ss.id 
                                                       WHERE s.aktif = 1 
                                                       AND s.sinav_tarihi > NOW() 
                                                       AND s.son_basvuru_tarihi > NOW()
                                                       ORDER BY s.sinav_tarihi DESC");
                                    while($sinav = $stmt->fetch()) {
                                        $sinavTarihi = new DateTime($sinav['sinav_tarihi']);
                                        $sonBasvuru = new DateTime($sinav['son_basvuru_tarihi']);
                                        $now = new DateTime();
                                        
                                        $durum = '<span class="badge bg-secondary">Pasif</span>';
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($sinav['sinav_adi']) ?></td>
                                            <td><?= $sinavTarihi->format('d.m.Y H:i') ?></td>
                                            <td><?= $sonBasvuru->format('d.m.Y H:i') ?></td>
                                            <td><?= $durum ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-danger" onclick="sinavSil(<?= $sinav['id'] ?>)">
                                                    <i class="fas fa-trash-alt"></i> Sil
                                                </button>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/admin.js"></script>
    <script>
    function sinavSil(id) {
        Swal.fire({
            title: 'Emin misiniz?',
            text: "Bu sınavı silmek istediğinizden emin misiniz?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Evet, sil!',
            cancelButtonText: 'İptal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('api/sinav_sil.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire(
                            'Silindi!',
                            'Sınav başarıyla silindi.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Hata!',
                            'Sınav silinirken bir hata oluştu: ' + data.message,
                            'error'
                        );
                    }
                })
                .catch(error => {
                    Swal.fire(
                        'Hata!',
                        'Bir hata oluştu: ' + error,
                        'error'
                    );
                });
            }
        });
    }

    function showAktifSinavlar() {
        fetch('api/get_sinavlar.php?type=aktif')
            .then(response => response.json())
            .then(data => updateSinavlarTable(data.sinavlar, true));
    }

    function showTumSinavlar() {
        fetch('api/get_sinavlar.php?type=tum')
            .then(response => response.json())
            .then(data => updateSinavlarTable(data.sinavlar, false));
    }

    function updateSinavlarTable(sinavlar, onlyAktif) {
        const tbody = document.getElementById('sinavlarTbody');
        tbody.innerHTML = '';

        sinavlar.forEach(sinav => {
            const sinavTarihi = new Date(sinav.sinav_tarihi);
            const sonBasvuru = new Date(sinav.son_basvuru_tarihi);
            const now = new Date();

            let durum = '';
            if(!sinav.aktif) {
                durum = '<span class="badge bg-secondary">Pasif</span>';
            } else if(sonBasvuru < now) {
                durum = '<span class="badge bg-secondary">Pasif</span>';
            } else if(sinavTarihi < now) {
                durum = '<span class="badge bg-secondary">Pasif</span>';
            } else {
                durum = '<span class="badge bg-success">Aktif</span>';
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${sinav.sinav_adi}</td>
                <td>${sinavTarihi.toLocaleString('tr-TR')}</td>
                <td>${sonBasvuru.toLocaleString('tr-TR')}</td>
                <td>${durum}</td>
                <td>
                    <button class="btn btn-sm btn-danger" onclick="sinavSil(${sinav.id})">
                        <i class="fas fa-trash-alt"></i> Sil
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function topluSil() {
        Swal.fire({
            title: 'Emin misiniz?',
            text: "Süresi geçen tüm sınavlar silinecek. Bu işlem geri alınamaz!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Evet, temizle!',
            cancelButtonText: 'İptal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('api/toplu_sil.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire({
                            title: 'Başarılı!',
                            text: `${data.count} adet süresi geçmiş sınav silindi.`,
                            icon: 'success'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Hata!',
                            'Silme işlemi sırasında bir hata oluştu: ' + data.message,
                            'error'
                        );
                    }
                })
                .catch(error => {
                    Swal.fire(
                        'Hata!',
                        'Bir hata oluştu: ' + error,
                        'error'
                    );
                });
            }
        });
    }
    </script>
</body>
</html> 