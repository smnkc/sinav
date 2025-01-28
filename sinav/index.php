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
                <ul class="navbar-nav ms-auto">
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
        <div class="row" id="sinavlar">
            <!-- Sınav kartları buraya dinamik olarak eklenecek -->
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
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html> 