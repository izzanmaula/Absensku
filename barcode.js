// Inisialisasi variabel scanner
let scanner = null;
let currentCameraIndex = 0;
let availableCameras = [];

// Fungsi untuk memulai scanner
function initializeScanner() {
    scanner = new Instascan.Scanner({
        video: document.getElementById('preview'),
        scanPeriod: 5,
        mirror: false
    });

    scanner.addListener('scan', function(content) {
        // Stop scanner sementara
        scanner.stop();
        
        // Kirim data barcode ke server
        fetch('absen_barcode.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                barcode: content
            })
        })
        .then(response => response.json())
        .then(data => {
            // Tutup modal barcode
            const barcodeModal = bootstrap.Modal.getInstance(document.getElementById('barcodeModal'));
            barcodeModal.hide();

            // Tampilkan modal sesuai response
            if (data.status === "success") {
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
            } else {
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                errorModal.show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memproses barcode.');
        });
    });

    // Dapatkan daftar kamera yang tersedia
    Instascan.Camera.getCameras()
        .then(cameras => {
            availableCameras = cameras;
            if (cameras.length > 0) {
                startCamera(0);
                console.log('kamera tersedia');
            } else {
                alert('Tidak ada kamera yang tersedia');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error mengakses kamera: ' + error.message);
        });
}

// Fungsi untuk memulai kamera
function startCamera(cameraIndex) {
    if (availableCameras[cameraIndex]) {
        scanner.start(availableCameras[cameraIndex]);
    }
}

// Event listener saat dokumen dimuat
document.addEventListener('DOMContentLoaded', function() {
    const barcodeModal = document.getElementById('barcodeModal');
    const switchCameraBtn = document.getElementById('switchCamera');
  
    if (barcodeModal) {
      barcodeModal.addEventListener('show.bs.modal', function() {
        initializeScanner();  
      });
  
      barcodeModal.addEventListener('hidden.bs.modal', function() {
        if (scanner) {
          scanner.stop();
        }
      });
    }
    if (switchCameraBtn) {
        switchCameraBtn.addEventListener('click', function() {
        if (availableCameras.length > 1) {
            currentCameraIndex = (currentCameraIndex + 1) % availableCameras.length;
            startCamera(currentCameraIndex);
        }
    });
});

// Cleanup saat halaman ditutup
window.addEventListener('beforeunload', function() {
    if (scanner) {
        scanner.stop();
    }
});