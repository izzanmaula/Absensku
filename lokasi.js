    // Tunggu sampai dokumen sepenuhnya dimuat
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi variabel untuk peta
        let map;
        let marker;
    
        // Handler untuk tombol absen lokasi
        document.getElementById('absenLokasiButton').addEventListener('click', function() {
            // Tampilkan loading state
            const button = this;
            const normalText = button.querySelector('.normal-text');
            const spinner = button.querySelector('.spinner');
            
            normalText.classList.add('d-none');
            spinner.classList.remove('d-none');
            button.disabled = true;
    
            // Cek apakah geolocation tersedia
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    // Dapatkan koordinat
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;
    
                    // Kirim data ke server
                    fetch('absen_lokasi.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            latitude: latitude,
                            longitude: longitude
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Sembunyikan modal lokasi
                        const lokasiModal = bootstrap.Modal.getInstance(document.getElementById('lokasiModal'));
                        lokasiModal.hide();
    
                        if (data.status === "success") {
                            // Tampilkan modal sukses
                            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                            successModal.show();
                        } else if (data.message === "Tidak ada jadwal untuk hari ini") {
                            // Tampilkan modal tidak ada jadwal
                            const noScheduleModal = new bootstrap.Modal(document.getElementById('noScheduleModal'));
                            noScheduleModal.show();
                        } else {
                            // Tampilkan modal error dengan peta
                            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                            
                            // Inisialisasi peta jika belum ada
                            if (!map) {
                                map = new ol.Map({
                                    target: 'map',
                                    layers: [
                                        new ol.layer.Tile({
                                            source: new ol.source.OSM()
                                        })
                                    ],
                                    view: new ol.View({
                                        center: ol.proj.fromLonLat([longitude, latitude]),
                                        zoom: 15
                                    })
                                });
    
                                // Tambah marker untuk posisi user
                                const markerElement = document.createElement('div');
                                markerElement.className = 'marker';
                                markerElement.style.backgroundColor = 'red';
                                markerElement.style.width = '20px';
                                markerElement.style.height = '20px';
                                markerElement.style.borderRadius = '50%';
    
                                marker = new ol.Overlay({
                                    element: markerElement,
                                    position: ol.proj.fromLonLat([longitude, latitude]),
                                    positioning: 'center-center'
                                });
                                map.addOverlay(marker);
                            }
    
                            // Update lokasi di modal
                            document.getElementById('currentLocation').textContent = 
                                `Lokasi Anda: ${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;
                            
                            errorModal.show();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat melakukan absensi.');
                    })
                    .finally(() => {
                        // Kembalikan tombol ke keadaan normal
                        normalText.classList.remove('d-none');
                        spinner.classList.add('d-none');
                        button.disabled = false;
                    });
                }, 
                function(error) {
                    // Handle error geolocation
                    let errorMessage;
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage = "Anda perlu mengizinkan akses lokasi untuk menggunakan fitur ini.";
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage = "Informasi lokasi tidak tersedia.";
                            break;
                        case error.TIMEOUT:
                            errorMessage = "Waktu permintaan lokasi habis.";
                            break;
                        default:
                            errorMessage = "Terjadi kesalahan saat mengakses lokasi.";
                    }
                    alert(errorMessage);
                    
                    // Kembalikan tombol ke keadaan normal
                    normalText.classList.remove('d-none');
                    spinner.classList.add('d-none');
                    button.disabled = false;
                });
            } else {
                alert("Browser Anda tidak mendukung geolokasi.");
                // Kembalikan tombol ke keadaan normal
                normalText.classList.remove('d-none');
                spinner.classList.add('d-none');
                button.disabled = false;
            }
        });
    
        // Handler untuk modal events
        document.getElementById('errorModal').addEventListener('shown.bs.modal', function () {
            if (map) {
                // Perbarui ukuran peta ketika modal ditampilkan
                setTimeout(() => {
                    map.updateSize();
                }, 200);
            }
        });
    });
    