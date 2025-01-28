document.addEventListener('DOMContentLoaded', function() {
    // Sınav ekleme formu
    const sinavForm = document.getElementById('sinavForm');
    if(sinavForm) {
        sinavForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('api/sinav_ekle.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Başarılı!',
                        text: 'Sınav başarıyla eklendi.',
                        confirmButtonText: 'Tamam'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Hata!',
                        text: data.message || 'Bir hata oluştu.',
                        confirmButtonText: 'Tamam'
                    });
                }
            });
        });
    }

    // Şablon ekleme formu
    const sablonForm = document.getElementById('sablonForm');
    if(sablonForm) {
        sablonForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('api/sablon_ekle.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Başarılı!',
                        text: 'Şablon başarıyla kaydedildi.',
                        confirmButtonText: 'Tamam'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Hata!',
                        text: data.message || 'Bir hata oluştu.',
                        confirmButtonText: 'Tamam'
                    });
                }
            });
        });
    }
});

// Sınav silme fonksiyonu
function sinavSil(id) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: "Bu sınavı silmek istediğinizden emin misiniz?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('api/sinav_sil.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({id: id})
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Başarılı!',
                        text: 'Sınav başarıyla silindi.',
                        confirmButtonText: 'Tamam'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Hata!',
                        text: data.message || 'Bir hata oluştu.',
                        confirmButtonText: 'Tamam'
                    });
                }
            });
        }
    });
} 