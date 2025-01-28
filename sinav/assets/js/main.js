document.addEventListener('DOMContentLoaded', function() {
    fetchSinavlar();
});

function fetchSinavlar() {
    fetch('api/get_sinavlar.php')
        .then(response => response.json())
        .then(data => {
            const sinavlarDiv = document.getElementById('sinavlar');
            sinavlarDiv.innerHTML = '';

            if (!data.success) {
                throw new Error(data.message || 'Bir hata oluştu');
            }

            if (data.sinavlar.length === 0) {
                sinavlarDiv.innerHTML = `
                    <div class="col-12">
                        <div class="empty-state">
                            <i class="fas fa-calendar-xmark"></i>
                            <h3>Aktif Sınav Bulunmuyor</h3>
                            <p class="text-muted">Şu anda başvuruya açık sınav bulunmamaktadır.</p>
                        </div>
                    </div>
                `;
                return;
            }

            data.sinavlar.forEach(sinav => {
                const col = document.createElement('div');
                col.className = 'col-md-6 col-lg-4';

                const card = document.createElement('div');
                card.className = 'card h-100';
                
                const formattedSinavTarihi = moment(sinav.sinav_tarihi).format('DD MMMM YYYY HH:mm');
                const formattedSonBasvuru = moment(sinav.son_basvuru_tarihi).format('DD MMMM YYYY HH:mm');
                const sinavGunu = moment(sinav.sinav_tarihi).format('DD');
                const sinavAyi = moment(sinav.sinav_tarihi).format('MMMM');

                card.innerHTML = `
                    <div class="card-header">
                        <h5 class="card-title">${sinav.sinav_adi}</h5>
                        <span class="info-badge">
                            <i class="fas fa-calendar-day me-1"></i>${sinavGunu} ${sinavAyi}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="ucret-item">
                            <i class="fas fa-calendar-alt"></i>
                            <strong>Sınav:</strong> ${formattedSinavTarihi}
                        </div>
                        <div class="ucret-item">
                            <i class="fas fa-clock"></i>
                            <strong>Son Başvuru:</strong> ${formattedSonBasvuru}
                        </div>
                        <div class="ucret-item">
                            <i class="fas fa-user-tie"></i>
                            <strong>Gözetmen:</strong> ${sinav.gozetmen_ucret} ₺
                        </div>
                        <div class="ucret-item">
                            <i class="fas fa-user-clock"></i>
                            <strong>Yedek:</strong> ${sinav.yedek_ucret} ₺
                        </div>
                        <div class="ucret-item">
                            <i class="fas fa-user-graduate"></i>
                            <strong>Salon Başkanı:</strong> ${sinav.baskan_ucret} ₺
                        </div>
                        <div class="countdown" data-deadline="${sinav.son_basvuru_tarihi}"></div>
                        <a href="${sinav.basvuru_link}" target="_blank" class="btn btn-basvuru">
                            <i class="fas fa-external-link-alt me-2"></i>Başvuru Yap
                        </a>
                    </div>
                `;

                col.appendChild(card);
                sinavlarDiv.appendChild(col);
            });
            
            updateCountdowns();
        })
        .catch(error => {
            console.error('Sınavlar yüklenirken hata oluştu:', error);
            document.getElementById('sinavlar').innerHTML = `
                <div class="col-12">
                    <div class="empty-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>Bir Hata Oluştu</h3>
                        <p class="text-muted">Sınavlar yüklenirken bir sorun oluştu. Lütfen sayfayı yenileyin veya daha sonra tekrar deneyin.</p>
                    </div>
                </div>
            `;
        });
} 