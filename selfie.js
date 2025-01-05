// Variabel global
let stream = null;
let faceDetectionInterval = null;
let modelLoaded = false;

// Fungsi untuk memuat model face detection
async function loadFaceDetectionModels() {
    try {
        console.log('Mulai memuat model face detection...');
        // Sesuaikan path dengan struktur folder yang benar
        const modelPath = '/models/tiny_face_detector_model';
        await faceapi.nets.tinyFaceDetector.load(modelPath);
        console.log('Model face detection berhasil dimuat');
        modelLoaded = true;
    } catch (error) {
        console.error('Error loading face detection models:', error);
    }
}

// Fungsi untuk memulai kamera
async function startCamera() {
    const video = document.getElementById('video');
    const takeSelfieBtn = document.getElementById('takeSelfieBtn');
    
    try {
        console.log('Memulai kamera...');
        // Minta izin akses kamera
        stream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: 'user',
                width: { ideal: 640 },
                height: { ideal: 480 }
            }
        });
        
        video.srcObject = stream;
        
        // Tunggu video benar-benar siap
        video.addEventListener('loadedmetadata', async () => {
            await video.play();
            console.log('Video dimulai, memulai deteksi wajah...');
            startFaceDetection();
        });
        
    } catch (err) {
        console.error('Error accessing camera:', err);
        alert('Tidak dapat mengakses kamera: ' + err.message);
    }
}

// Fungsi untuk memulai deteksi wajah
function startFaceDetection() {
    const video = document.getElementById('video');
    const canvas = document.getElementById('faceCanvas');
    const takeSelfieBtn = document.getElementById('takeSelfieBtn');
    const detectionAlert = document.getElementById('detectionAlert');

    // Set ukuran canvas sesuai video
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    // Mulai interval deteksi wajah
    faceDetectionInterval = setInterval(async () => {
        if (!modelLoaded) {
            return;
        }

        try {
            const detections = await faceapi.detectSingleFace(
                video,
                new faceapi.TinyFaceDetectorOptions({
                    inputSize: 320,
                    scoreThreshold: 0.5
                })
            );

            const context = canvas.getContext('2d');
            context.clearRect(0, 0, canvas.width, canvas.height);

            if (detections) {
                // Wajah terdeteksi
                detectionAlert.textContent = "Wajah Terdeteksi";
                detectionAlert.className = "alert alert-success mt-3";
                takeSelfieBtn.disabled = false;

                // Gambar kotak di sekitar wajah
                const box = detections.box;
                context.beginPath();
                context.rect(box.x, box.y, box.width, box.height);
                context.strokeStyle = "#00ff00";
                context.lineWidth = 3;
                context.stroke();
            } else {
                console.log('Tidak ada wajah terdeteksi');
                // Tidak ada wajah terdeteksi
                detectionAlert.textContent = "Arahkan Wajah Anda ke Kamera";
                detectionAlert.className = "alert alert-warning mt-3";
                takeSelfieBtn.disabled = true;
            }
        } catch (error) {
            console.error('Error in face detection:', error);
        }
    }, 200); // Interval diubah menjadi 200ms untuk performa lebih baik
}

// Fungsi untuk membersihkan
function stopAll() {
    if (faceDetectionInterval) {
        clearInterval(faceDetectionInterval);
        faceDetectionInterval = null;
    }
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
}

// Event listener untuk modal
document.getElementById('absenselfiesekarang').addEventListener('click', async function() {
    const selfieModal = new bootstrap.Modal(document.getElementById('selfieModal'));
    selfieModal.show();
    // Pastikan model dimuat sebelum memulai kamera
    if (!modelLoaded) {
        await loadFaceDetectionModels();
    }
    startCamera();
});

// Fungsi untuk mengambil dan memproses foto
async function capturePhoto() {
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const previewImage = document.getElementById('previewImage');

    // Set ukuran canvas sesuai video
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    // Ambil foto dari video stream
    const context = canvas.getContext('2d');
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    // Tampilkan di preview
    previewImage.src = canvas.toDataURL('image/png');

    // Tutup modal selfie
    const selfieModal = bootstrap.Modal.getInstance(document.getElementById('selfieModal'));
    selfieModal.hide();

    // Tampilkan modal preview
    const previewModal = new bootstrap.Modal(document.getElementById('previewModalSelfie'));
    previewModal.show();
}

// Fungsi untuk mengirim foto ke server
async function uploadPhoto() {
    console.log('Memulai upload foto...');
    const button = document.getElementById('confirmPhotoBtn');
    const buttonText = button.querySelector('.button-text');
    const spinner = button.querySelector('.spinner-border');
    const canvas = document.getElementById('canvas');

    try {
        // Tampilkan loading state
        buttonText.classList.add('d-none');
        spinner.classList.remove('d-none');
        button.disabled = true;

        // Konversi foto ke base64
        const imageData = canvas.toDataURL('image/png').split(',')[1];

        // Ambil user ID dari data attribute di body
        const userId = document.body.dataset.userId;

        // Kirim ke server
        const response = await fetch('absen_selfie.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `foto=${encodeURIComponent(imageData)}&id=${userId}`
        });

        const data = await response.json();

        // Tutup modal preview
        const previewModal = new bootstrap.Modal(document.getElementById('previewModalSelfie'));
        previewModal.hide();

        if (data.success) {
            console.log('Upload berhasil');
            // Tampilkan modal sukses
            const successModal = new bootstrap.Modal(document.getElementById('successModalSelfie'));
            successModal.show();
            $('.modal-backdrop').remove();

        } else {
            console.error('Upload gagal:', data.error);
            // Tampilkan modal error
            const errorModal = new bootstrap.Modal(document.getElementById('errorModalSelfie'));
            errorModal.show();
            $('.modal-backdrop').remove();
        }
    } catch (error) {
        console.error('Error saat upload:', error);
        alert('Terjadi kesalahan saat mengirim foto.');
    } finally {
        // Kembalikan button ke kondisi normal
        buttonText.classList.remove('d-none');
        spinner.classList.add('d-none');
        button.disabled = false;
    }
}

// Event listener untuk tombol ambil foto
document.getElementById('takeSelfieBtn').addEventListener('click', capturePhoto);

// Event listener untuk tombol konfirmasi foto
document.getElementById('confirmPhotoBtn').addEventListener('click', uploadPhoto);

// Event listener untuk tombol ambil ulang foto
document.getElementById('retakePhotoBtn').addEventListener('click', function() {
    const previewModal = bootstrap.Modal.getInstance(document.getElementById('previewModalSelfie'));
    previewModal.hide();

    // Beri jeda sebelum membuka modal selfie kembali
    setTimeout(() => {
        const selfieModal = new bootstrap.Modal(document.getElementById('selfieModal'));
        selfieModal.show();
        startCamera(); // Mulai ulang kamera
    }, 500);
});

