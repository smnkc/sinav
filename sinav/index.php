<?php
require_once 'config/db.php';

// Aktif sınavları getir
$stmt = $db->query("SELECT s.*, ss.sinav_adi, ss.basvuru_link 
                    FROM sinavlar s 
                    LEFT JOIN sinav_sablonlari ss ON s.sablon_id = ss.id 
                    WHERE s.aktif = 1 
                    AND s.sinav_tarihi > NOW() 
                    AND s.son_basvuru_tarihi > NOW()
                    ORDER BY s.sinav_tarihi");
$sinavlar = $stmt->fetchAll();

// Görevli ücretlerini getir
$stmt = $db->query("SELECT * FROM ucretler ORDER BY sinav_turu");
$ucretler = $stmt->fetchAll();

// Sınav takvimini getir
$stmt = $db->query("SELECT * FROM sinav_takvimi ORDER BY tarih");
$takvim = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sınav Görevleri Takip Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #3b82f6;
            --success-color: #059669;
            --danger-color: #dc2626;
            --warning-color: #fbbf24;
        }
        
        body {
            background: #f8fafc;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 1rem 0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .card {
            background: white;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            margin-bottom: 2rem;
            position: relative;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .card-header::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: linear-gradient(135deg, transparent, rgba(255, 255, 255, 0.1));
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            position: relative;
            z-index: 1;
        }

        .card-body {
            padding: 1.5rem;
        }

        .ucret-item {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            background: #f8fafc;
            transition: all 0.2s ease;
        }

        .ucret-item:hover {
            background: #f1f5f9;
            transform: translateX(5px);
        }

        .ucret-item i {
            color: var(--primary-color);
            width: 24px;
        }

        .countdown {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: var(--danger-color);
            padding: 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            text-align: center;
            margin: 1rem 0;
            position: relative;
            overflow: hidden;
        }

        .countdown.expired {
            background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
            color: #6b7280;
        }

        .btn-basvuru {
            background: linear-gradient(135deg, var(--success-color), #047857);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 2rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .btn-basvuru::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: linear-gradient(135deg, transparent, rgba(255, 255, 255, 0.1));
            transition: all 0.3s ease;
        }

        .btn-basvuru:hover {
            transform: translateY(-2px);
            color: white;
            box-shadow: 0 10px 15px -3px rgba(5, 150, 105, 0.3);
        }

        .btn-basvuru:hover::after {
            opacity: 0;
        }

        .info-badge {
            display: none;
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.875rem;
            backdrop-filter: blur(4px);
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .countdown i {
            animation: pulse 2s infinite;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .card-header {
                padding: 1rem;
            }
            
            .card-title {
                font-size: 1.25rem;
            }

            .ucret-item {
                padding: 0.75rem;
            }
        }

        .ucret-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .ucret-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
        }

        .ucret-card h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .ucret-table {
            width: 100%;
            margin-bottom: 0;
        }

        .ucret-table td {
            padding: 0.5rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .ucret-table td:last-child {
            text-align: right;
            font-weight: 600;
            color: var(--success-color);
        }

        .ucret-table tr:last-child td {
            border-bottom: none;
        }

        .nav-link { cursor: pointer; }
        .section { display: none; }
        .section.active { display: block; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-graduation-cap me-2"></i>Sınav Görevleri
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" data-section="sinavlar">Sınavlar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-section="takvim">Sınav Takvimi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-section="ucretler">Görevli Ücretleri</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login.php">
                            <i class="fas fa-user-shield me-1"></i>Yönetici Girişi
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div id="sinavlar" class="section active">
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach($sinavlar as $sinav): 
                    $sinavTarihi = new DateTime($sinav['sinav_tarihi']);
                    $sonBasvuru = new DateTime($sinav['son_basvuru_tarihi']);
                ?>
                <div class="col">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><?= htmlspecialchars($sinav['sinav_adi']) ?></h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">
                                <strong>Sınav Tarihi:</strong><br>
                                <?= $sinavTarihi->format('d.m.Y H:i') ?>
                            </p>
                            <p class="card-text">
                                <strong>Son Başvuru:</strong><br>
                                <?= $sonBasvuru->format('d.m.Y H:i') ?>
                            </p>
                        </div>
                        <div class="card-footer">
                            <a href="<?= htmlspecialchars($sinav['basvuru_link']) ?>" class="btn btn-primary w-100" target="_blank">
                                <i class="fas fa-external-link-alt"></i> Başvuru Yap
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if(empty($sinavlar)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Şu anda aktif sınav bulunmamaktadır.
            </div>
            <?php endif; ?>
        </div>

        <div id="takvim" class="section">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">2024 Sınav Takvimi</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Sınav Türü</th>
                                    <th>Tarih</th>
                                    <th>Açıklama</th>
                                    <th>Kalan Süre</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($takvim as $item): 
                                    $tarih = new DateTime($item['tarih']);
                                    $now = new DateTime();
                                    $interval = $now->diff($tarih);
                                    $kalanGun = $interval->days;
                                    
                                    $durumClass = '';
                                    if($tarih < $now) {
                                        $durumClass = 'table-secondary';
                                    } elseif($kalanGun <= 30) {
                                        $durumClass = 'table-warning';
                                    }
                                ?>
                                <tr class="<?= $durumClass ?>">
                                    <td><?= htmlspecialchars($item['sinav_turu']) ?></td>
                                    <td><?= $tarih->format('d.m.Y') ?></td>
                                    <td><?= htmlspecialchars($item['aciklama']) ?></td>
                                    <td>
                                        <?php if($tarih < $now): ?>
                                            <span class="text-muted">Sınav Yapıldı</span>
                                        <?php else: ?>
                                            <?= $kalanGun ?> gün
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if(empty($takvim)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Henüz sınav takvimi yayınlanmamış.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div id="ucretler" class="section">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Görevli Ücretleri</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Sınav Türü</th>
                                    <th class="text-end">Başkan</th>
                                    <th class="text-end">Gözetmen</th>
                                    <th class="text-end">Yedek Gözetmen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($ucretler as $ucret): ?>
                                <tr>
                                    <td><?= htmlspecialchars($ucret['sinav_turu']) ?></td>
                                    <td class="text-end"><?= number_format($ucret['baskan_ucret'], 2, ',', '.') ?> ₺</td>
                                    <td class="text-end"><?= number_format($ucret['gozetmen_ucret'], 2, ',', '.') ?> ₺</td>
                                    <td class="text-end"><?= number_format($ucret['yedek_ucret'], 2, ',', '.') ?> ₺</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if(empty($ucretler)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Henüz ücret bilgisi girilmemiş.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/tr.js"></script>
    <script>
        moment.locale('tr');
        
        function updateCountdowns() {
            document.querySelectorAll('.countdown').forEach(el => {
                const deadline = moment(el.dataset.deadline);
                const now = moment();
                const diff = deadline.diff(now);
                
                if (diff > 0) {
                    const duration = moment.duration(diff);
                    const days = Math.floor(duration.asDays());
                    const hours = duration.hours();
                    const minutes = duration.minutes();
                    
                    let timeText = '';
                    if (days > 0) timeText += `${days} gün `;
                    if (hours > 0) timeText += `${hours} saat `;
                    if (minutes > 0) timeText += `${minutes} dakika`;
                    
                    el.innerHTML = `
                        <i class="fas fa-hourglass-half me-2"></i>
                        Son başvuruya kalan süre:<br>
                        <strong>${timeText}</strong>
                    `;
                } else {
                    el.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>Başvuru süresi doldu!';
                    el.classList.add('expired');
                }
            });
        }

        setInterval(updateCountdowns, 60000); // Her dakika güncelle

        // Sekme değişikliğini dinle
        document.querySelectorAll('.nav-link[data-section]').forEach(link => {
            link.addEventListener('click', function() {
                // Aktif sekmeyi değiştir
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');

                // Sekme içeriğini göster/gizle
                const sectionId = this.dataset.section;
                document.querySelectorAll('.section').forEach(section => {
                    section.classList.remove('active');
                });
                document.getElementById(sectionId).classList.add('active');
            });
        });
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html> 