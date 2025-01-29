<?php
session_start();
require_once '../config/db.php';

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// URL'den gelen tab parametresini al
$activeTab = $_GET['tab'] ?? 'sinavlar';

// Aktif sekmeyi JavaScript'e aktar
$initialActiveTab = json_encode($activeTab);

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

// Görevli ücretleri getir
$stmt = $db->query("SELECT * FROM ucretler ORDER BY sinav_turu");
$ucretler = $stmt->fetchAll();
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
    <style>
        .nav-link { cursor: pointer; }
        .section { display: none; }
        .section.active { display: block; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Yönetici Paneli</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" data-section="sinavlar">Sınavlar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-section="sablonlar">Şablonlar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-section="ucretler">Ücret Yönetimi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-section="takvim">Sınav Takvimi</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
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
        <!-- Sınavlar Sekmesi -->
        <div id="sinavlar" class="section active">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
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
                                            <option value="<?= $sablon['id'] ?>"><?= htmlspecialchars($sablon['sinav_adi']) ?></option>
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
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-plus-circle"></i> Sınavı Ekle
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
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
                                        <?php foreach($sinavlar as $sinav): 
                                            $sinavTarihi = new DateTime($sinav['sinav_tarihi']);
                                            $sonBasvuru = new DateTime($sinav['son_basvuru_tarihi']);
                                            $now = new DateTime();
                                            
                                            $durum = '<span class="badge bg-success">Aktif</span>';
                                            if(!$sinav['aktif'] || $sonBasvuru < $now || $sinavTarihi < $now) {
                                                $durum = '<span class="badge bg-secondary">Pasif</span>';
                                            }
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($sinav['sinav_adi']) ?></td>
                                            <td><?= $sinavTarihi->format('d.m.Y H:i') ?></td>
                                            <td><?= $sonBasvuru->format('d.m.Y H:i') ?></td>
                                            <td><?= $durum ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-danger" onclick="sinavSil(<?= $sinav['id'] ?>)">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Şablonlar Sekmesi -->
        <div id="sablonlar" class="section">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Sınav Şablonları</h5>
                    <button type="button" class="btn btn-sm btn-success" onclick="yeniSablonModal()">
                        <i class="fas fa-plus"></i> Yeni Şablon
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Sınav Adı</th>
                                    <th>Başvuru Linki</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($sablonlar as $sablon): ?>
                                <tr>
                                    <td><?= htmlspecialchars($sablon['sinav_adi']) ?></td>
                                    <td><a href="<?= htmlspecialchars($sablon['basvuru_link']) ?>" target="_blank"><?= htmlspecialchars($sablon['basvuru_link']) ?></a></td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-primary" onclick="sablonDuzenle(<?= htmlspecialchars(json_encode($sablon)) ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="sablonSil(<?= $sablon['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ücret Yönetimi Sekmesi -->
        <div id="ucretler" class="section">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Görevli Ücretleri</h5>
                    <button type="button" class="btn btn-sm btn-success" onclick="yeniUcretEkle()">
                        <i class="fas fa-plus"></i> Yeni Ücret Ekle
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Sınav Türü</th>
                                    <th style="width: 200px;">Başkan (₺)</th>
                                    <th style="width: 200px;">Gözetmen (₺)</th>
                                    <th style="width: 200px;">Yedek Gözetmen (₺)</th>
                                    <th style="width: 100px;">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($ucretler as $ucret): ?>
                                <tr>
                                    <td>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($ucret['sinav_turu']) ?>">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" value="<?= $ucret['baskan_ucret'] ?>" step="0.01">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" value="<?= $ucret['gozetmen_ucret'] ?>" step="0.01">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" value="<?= $ucret['yedek_ucret'] ?>" step="0.01">
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="ucretSatirSil(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <button type="button" class="btn btn-primary" onclick="ucretleriKaydet()">
                            <i class="fas fa-save"></i> Tümünü Kaydet
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sınav Takvimi Sekmesi -->
        <div id="takvim" class="section">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Sınav Takvimi</h5>
                    <button type="button" class="btn btn-sm btn-success" onclick="yeniTakvimSatiri()">
                        <i class="fas fa-plus"></i> Yeni Ekle
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Sınav Türü</th>
                                    <th style="width: 200px;">Tarih</th>
                                    <th>Açıklama</th>
                                    <th style="width: 100px;">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $stmt = $db->query("SELECT * FROM sinav_takvimi ORDER BY tarih");
                                $takvim = $stmt->fetchAll();
                                foreach($takvim as $item): 
                                    $tarih = new DateTime($item['tarih']);
                                ?>
                                <tr>
                                    <td>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($item['sinav_turu']) ?>">
                                    </td>
                                    <td>
                                        <input type="date" class="form-control" value="<?= $tarih->format('Y-m-d') ?>">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($item['aciklama']) ?>">
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="takvimSatirSil(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <button type="button" class="btn btn-primary" onclick="takvimKaydet()">
                            <i class="fas fa-save"></i> Tümünü Kaydet
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Şablon Düzenleme Modal -->
    <div class="modal fade" id="sablonDuzenleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Şablon Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="sablonDuzenleForm">
                        <input type="hidden" id="duzenle_id" name="id">
                        <div class="mb-3">
                            <label for="duzenle_sinav_adi" class="form-label">Sınav Adı</label>
                            <input type="text" class="form-control" id="duzenle_sinav_adi" name="sinav_adi" required>
                        </div>
                        <div class="mb-3">
                            <label for="duzenle_basvuru_link" class="form-label">Başvuru Linki</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-link"></i></span>
                                <input type="url" class="form-control" id="duzenle_basvuru_link" name="basvuru_link" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="sablonKaydet()">
                        <i class="fas fa-save"></i> Kaydet
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Aktif sekmeyi localStorage'da saklamak için
        function setActiveTab(sectionId) {
            localStorage.setItem('activeTab', sectionId);
            showSection(sectionId);
        }

        // Sekmeyi göster/gizle
        function showSection(sectionId) {
            // Tüm sekmeleri gizle
            document.querySelectorAll('.section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Tüm nav-link'leri pasif yap
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Seçili sekmeyi göster
            document.getElementById(sectionId).classList.add('active');
            
            // Nav-link'i aktif yap
            document.querySelector(`.nav-link[data-section="${sectionId}"]`).classList.add('active');
        }

        // Sayfa yüklendiğinde URL'den gelen sekmeyi aktif et
        const initialActiveTab = <?= $initialActiveTab ?>;
        document.addEventListener('DOMContentLoaded', function() {
            setActiveTab(initialActiveTab);
        });

        // Sekme değiştirme
        document.querySelectorAll('.nav-link[data-section]').forEach(link => {
            link.addEventListener('click', function() {
                const sectionId = this.dataset.section;
                setActiveTab(sectionId);
            });
        });

        // Sayfayı yenile ve aktif sekmeyi koru
        function reloadWithActiveTab() {
            const activeTab = localStorage.getItem('activeTab') || 'sinavlar';
            window.location.href = window.location.pathname + '?tab=' + activeTab;
        }

        // Yeni ücret satırı ekleme
        function yeniUcretEkle() {
            const tbody = document.querySelector('#ucretler table tbody');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="text" class="form-control" placeholder="Sınav Türü"></td>
                <td><input type="number" class="form-control" step="0.01" placeholder="0.00"></td>
                <td><input type="number" class="form-control" step="0.01" placeholder="0.00"></td>
                <td><input type="number" class="form-control" step="0.01" placeholder="0.00"></td>
                <td>
                    <button class="btn btn-sm btn-danger" onclick="ucretSatirSil(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        }

        // Ücret satırı silme
        function ucretSatirSil(button) {
            button.closest('tr').remove();
        }

        // Ücretleri kaydetme
        function ucretleriKaydet() {
            const ucretler = [];
            let hataVar = false;
            
            document.querySelectorAll('#ucretler table tbody tr').forEach(tr => {
                const inputs = tr.querySelectorAll('input');
                const sinav_turu = inputs[0].value.trim();
                const baskan_ucret = parseFloat(inputs[1].value) || 0;
                const gozetmen_ucret = parseFloat(inputs[2].value) || 0;
                const yedek_ucret = parseFloat(inputs[3].value) || 0;

                // Boş sınav türü kontrolü
                if(sinav_turu === '') {
                    hataVar = true;
                    inputs[0].classList.add('is-invalid');
                } else {
                    inputs[0].classList.remove('is-invalid');
                }

                // Negatif değer kontrolü
                [inputs[1], inputs[2], inputs[3]].forEach(input => {
                    if(parseFloat(input.value) < 0) {
                        hataVar = true;
                        input.classList.add('is-invalid');
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });

                ucretler.push({
                    sinav_turu,
                    baskan_ucret,
                    gozetmen_ucret,
                    yedek_ucret
                });
            });

            if(hataVar) {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'Lütfen tüm alanları doğru şekilde doldurun. Sınav türü boş olamaz ve ücretler negatif olamaz.'
                });
                return;
            }

            if(ucretler.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'En az bir ücret girmelisiniz.'
                });
                return;
            }

            // Kaydetme işlemi başlamadan önce loading göster
            Swal.fire({
                title: 'Kaydediliyor...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('api/ucretler_kaydet.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ ucretler })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Başarılı!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        reloadWithActiveTab();
                    });
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: error.message
                });
            });
        }

        // Yeni takvim satırı ekleme
        function yeniTakvimSatiri() {
            const tbody = document.querySelector('#takvim table tbody');
            const tr = document.createElement('tr');
            const bugun = new Date().toISOString().split('T')[0]; // Bugünün tarihi
            
            tr.innerHTML = `
                <td>
                    <input type="text" class="form-control" placeholder="Sınav Türü">
                </td>
                <td>
                    <input type="date" class="form-control" value="${bugun}">
                </td>
                <td>
                    <input type="text" class="form-control" placeholder="Açıklama">
                </td>
                <td>
                    <button class="btn btn-sm btn-danger" onclick="takvimSatirSil(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        }

        // Takvim satırı silme
        function takvimSatirSil(button) {
            button.closest('tr').remove();
        }

        // Takvim kaydetme
        function takvimKaydet() {
            const takvim = [];
            let hataVar = false;
            
            document.querySelectorAll('#takvim table tbody tr').forEach(tr => {
                const inputs = tr.querySelectorAll('input');
                const sinav_turu = inputs[0].value.trim();
                const tarih = inputs[1].value;
                const aciklama = inputs[2].value.trim();

                // Boş sınav türü kontrolü
                if(sinav_turu === '') {
                    hataVar = true;
                    inputs[0].classList.add('is-invalid');
                } else {
                    inputs[0].classList.remove('is-invalid');
                }

                // Tarih kontrolü
                if(!tarih) {
                    hataVar = true;
                    inputs[1].classList.add('is-invalid');
                } else {
                    inputs[1].classList.remove('is-invalid');
                }

                takvim.push({
                    sinav_turu,
                    tarih,
                    aciklama
                });
            });

            if(hataVar) {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'Lütfen sınav türü ve tarih alanlarını doldurun.'
                });
                return;
            }

            if(takvim.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'En az bir sınav tarihi girmelisiniz.'
                });
                return;
            }

            // Kaydetme işlemi başlamadan önce loading göster
            Swal.fire({
                title: 'Kaydediliyor...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('api/takvim_kaydet.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ takvim })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Başarılı!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        reloadWithActiveTab();
                    });
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: error.message
                });
            });
        }

        // Sınav silme
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
                        body: JSON.stringify({ id: id })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Başarılı!',
                                text: 'Sınav başarıyla silindi.',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                reloadWithActiveTab();
                            });
                        } else {
                            throw new Error(data.message);
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Hata!',
                            text: error.message
                        });
                    });
                }
            });
        }

        // Sınav listesini güncelleme
        function showAktifSinavlar() {
            fetch('api/get_sinavlar.php?type=aktif')
                .then(response => response.json())
                .then(data => updateSinavlarTable(data.sinavlar));
        }

        function showTumSinavlar() {
            fetch('api/get_sinavlar.php?type=tum')
                .then(response => response.json())
                .then(data => updateSinavlarTable(data.sinavlar));
        }

        function updateSinavlarTable(sinavlar) {
            const tbody = document.getElementById('sinavlarTbody');
            tbody.innerHTML = '';

            sinavlar.forEach(sinav => {
                const sinavTarihi = new Date(sinav.sinav_tarihi);
                const sonBasvuru = new Date(sinav.son_basvuru_tarihi);
                const now = new Date();

                let durum = '<span class="badge bg-success">Aktif</span>';
                if(!sinav.aktif || sonBasvuru < now || sinavTarihi < now) {
                    durum = '<span class="badge bg-secondary">Pasif</span>';
                }

                tbody.innerHTML += `
                    <tr>
                        <td>${sinav.sinav_adi}</td>
                        <td>${sinavTarihi.toLocaleString('tr-TR')}</td>
                        <td>${sonBasvuru.toLocaleString('tr-TR')}</td>
                        <td>${durum}</td>
                        <td>
                            <button class="btn btn-sm btn-danger" onclick="sinavSil(${sinav.id})">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }

        // Süresi geçen sınavları toplu silme
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
                                icon: 'success',
                                title: 'Başarılı!',
                                text: `${data.count} adet süresi geçmiş sınav silindi.`,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                reloadWithActiveTab();
                            });
                        } else {
                            throw new Error(data.message);
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Hata!',
                            text: error.message
                        });
                    });
                }
            });
        }

        // Şablon işlemleri
        function yeniSablonModal() {
            document.getElementById('sablonDuzenleForm').reset();
            document.getElementById('duzenle_id').value = '';
            new bootstrap.Modal(document.getElementById('sablonDuzenleModal')).show();
        }

        function sablonDuzenle(sablon) {
            document.getElementById('duzenle_id').value = sablon.id;
            document.getElementById('duzenle_sinav_adi').value = sablon.sinav_adi;
            document.getElementById('duzenle_basvuru_link').value = sablon.basvuru_link;
            new bootstrap.Modal(document.getElementById('sablonDuzenleModal')).show();
        }

        function sablonSil(id) {
            Swal.fire({
                title: 'Emin misiniz?',
                text: "Bu şablonu silmek istediğinizden emin misiniz?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Evet, sil!',
                cancelButtonText: 'İptal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('api/sablon_sil.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id: id })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Başarılı!',
                                text: 'Şablon başarıyla silindi.',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                reloadWithActiveTab();
                            });
                        } else {
                            throw new Error(data.message);
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Hata!',
                            text: error.message
                        });
                    });
                }
            });
        }

        function sablonKaydet() {
            const form = document.getElementById('sablonDuzenleForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            fetch('api/sablon_duzenle.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Başarılı!',
                        text: 'Şablon başarıyla kaydedildi.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        reloadWithActiveTab();
                    });
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: error.message
                });
            });
        }

        // Sınav ekleme formu submit
        document.getElementById('sinavForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            // Tarih kontrolü
            const sinavTarihi = new Date(data.sinav_tarihi);
            const sonBasvuru = new Date(data.son_basvuru);
            const now = new Date();
            
            if(sonBasvuru >= sinavTarihi) {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'Son başvuru tarihi, sınav tarihinden önce olmalıdır!'
                });
                return;
            }
            
            if(sonBasvuru < now) {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'Son başvuru tarihi geçmiş bir tarih olamaz!'
                });
                return;
            }

            // Loading göster
            Swal.fire({
                title: 'Kaydediliyor...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // API'ye gönder
            fetch('api/sinav_ekle.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Sunucu hatası: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Başarılı!',
                        text: 'Sınav başarıyla eklendi.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        reloadWithActiveTab();
                    });
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: error.message
                });
            });
        });
    </script>
</body>
</html> 