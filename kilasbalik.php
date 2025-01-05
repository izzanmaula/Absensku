<?php
// Koneksi ke database
require_once 'koneksi.php';

// Pastikan session sudah dimulai
if(!isset($_SESSION)) {
    session_start();
}

// Ambil user_id dari session
$user_id = $_SESSION['user_id'];

// Query untuk menghitung total absensi
$query = "SELECT COUNT(*) as total_absensi FROM datang WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Simpan total absensi ke dalam variabel
$totalAbsensi = $row['total_absensi'];

// Query untuk menghitung persentase tepat waktu
$query_tepat_waktu = "SELECT 
    (COUNT(CASE WHEN status = 'Tepat Waktu' THEN 1 END) * 100.0 / COUNT(*)) as persentase_tepat_waktu 
    FROM datang 
    WHERE user_id = ?";
$stmt = $conn->prepare($query_tepat_waktu);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Simpan persentase tepat waktu ke dalam variabel
$persentaseTepatWaktu = round($row['persentase_tepat_waktu'], 1);

// Query untuk mendapatkan metode absensi favorit
$query_metode = "SELECT metode_absen, COUNT(*) as total 
                 FROM datang 
                 WHERE user_id = ? 
                 GROUP BY metode_absen 
                 ORDER BY total DESC 
                 LIMIT 1";
$stmt = $conn->prepare($query_metode);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Simpan metode favorit ke dalam variabel
$metodeFavorit = $row['metode_absen'] ?? 'Belum ada data';

// Query untuk mendapatkan rekor terpagi
$query_terpagi = "SELECT waktu_absen as waktu_rekor,
                         tanggal as tanggal_rekor 
                  FROM datang 
                  WHERE user_id = ? 
                    AND status = 'tepat waktu'
                  ORDER BY CAST(waktu_absen AS TIME) ASC 
                  LIMIT 1";

$stmt = $conn->prepare($query_terpagi);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Simpan ke dalam variabel
$waktuRekor = $row['waktu_rekor'] ?? '-';
$tanggalRekor = $row['tanggal_rekor'] ?? '-';

// Ambil namaLengkap dari tabel users
$query = "SELECT namaLengkap FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($namaLengkap);
$stmt->fetch();
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kilas Balik Absensi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* Global styling */
        body {
            margin: 0;
            padding: 0;
            font-family: merriweather;
            background-color: #F8F9FA;
        }

        .story-container {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
            position: relative;
            overflow: hidden; /* Memastikan bunga tidak keluar dari container */
            z-index: 1;
        }
        
        /* Latar belakang bunga */
        .story-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('flower-background.svg') no-repeat center center;
            background-size: cover;
            opacity: 0.3;
            z-index: -1;
        }

        /* Progress bar styling */
        .progress-container {
            display: flex;
            width: 90%;
            margin: 10px auto;
            position: absolute;
            top: 20px;
            z-index: 3;
        }

        .progress-bar {
            flex-grow: 1;
            height: 4px;
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 2px;
            margin: 0 2px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            width: 0%;
            background-color: #FFFFFF;
            border-radius: 2px;
            transition: width 0.3s ease-out;
        }

        /* Story content styling */
        .story-content {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: linear-gradient(
                45deg,
                #DA7756 0%,
                #A95342 25%,
                #753730 50%,
                #A95342 75%,
                #DA7756 100%
            );            
            background-size: 300% 300%;
            animation: gradientFlow 15s ease infinite;
            color: white;
            text-align: center;
            padding: 20px;
            z-index: 2; /* Memastikan konten di atas animasi bunga */
        }

        /* Navigation styling */
        .navigation {
            position: absolute;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 3;
            pointer-events: none; /* Penting: memungkinkan klik melewati area kosong */
        }

        .nav-button {
            width: 20%;
            height: 100%;
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: auto; /* Aktifkan kembali pointer events hanya untuk tombol */
        }

        .nav-icon {
            color: white;
            font-size: 24px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .nav-button:hover .nav-icon {
            opacity: 0.7;
        }

        /* Content animations */
        .fade-in {
            animation: fadeIn 0.5s ease-in forwards;
        }

        .fade-out {
            animation: fadeOut 0.5s ease-out forwards;
        }

        /* Keyframes */
        @keyframes gradientFlow {
            0% {
                background-position: 0% 50%;
                background-color: rgba(218, 119, 86, 0.95); /* #DA7756 with opacity */
            }
            25% {
                background-position: 50% 100%;
                background-color: rgba(169, 83, 66, 0.95); /* #A95342 with opacity */
            }
            50% {
                background-position: 100% 50%;
                background-color: rgba(117, 55, 48, 0.95); /* #753730 with opacity */
            }
            75% {
                background-position: 50% 0%;
                background-color: rgba(169, 83, 66, 0.95); /* #A95342 with opacity */
            }
            100% {
                background-position: 0% 50%;
                background-color: rgba(218, 119, 86, 0.95); /* #DA7756 with opacity */
            }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-20px); }
        }

        /* Story content styling */
        .story-title {
            font-size: 2.5em;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .story-description {
            font-size: 1.2em;
            max-width: 80%;
            line-height: 1.6;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .story-title {
                font-size: 3em;
                margin-bottom: 1rem;
                padding: 0;
            }
            .story-description {
                font-size: 1.1em;
                margin: 0;
                padding: 0;
            }
        }
        
        /* Pastikan story-content memiliki z-index yang tepat */
        .story-content {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>
    <audio id="bgMusic" autoplay muted style="display: none;">
        <source src="sangsurya.mp3" type="audio/mp3">
    </audio>
    <div class="story-container">
        <div class="progress-container">
            <div class="progress-bar">
                <div class="progress-fill" id="progress1"></div>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" id="progress2"></div>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" id="progress3"></div>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" id="progress4"></div>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" id="progress5"></div>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" id="progress6"></div>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" id="progress7"></div>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" id="progress8"></div>
            </div>
        </div>
        <div class="story-content" id="storyContent">
            <h1 class="story-title" id="storyTitle"></h1>
            <p class="story-description" id="storyDescription"></p>
        </div>

        <div class="navigation">
            <div class="nav-button" id="prevStory">
                <i class="nav-icon fas fa-chevron-left"></i>
            </div>
            <div class="nav-button" id="nextStory">
                <i class="nav-icon fas fa-chevron-right"></i>
            </div>
        </div>
    </div>

    <style>
        .logo{
            width: 80px; /* sesuaikan ukuran */
            height: auto;
            margin-bottom: 15px;
        }
        .story-icon {
            font-size: 1em;
            margin-bottom: 20px;
            color: white;
        }

        .story-icon img {
            width: 80px;
            height: auto;
            filter: brightness(0) invert(1); /* Membuat logo menjadi putih jika diperlukan */
        }
        /* Grid Summary Styling */
        .grid-summary {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .grid-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .grid-item:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.2);
        }

        .grid-icon {
            font-size: 2em;
            margin-bottom: 10px;
            color: rgba(255, 255, 255, 0.9);
        }

        .grid-value {
            font-size: 1.5em;
            font-weight: bold;
            margin: 10px 0;
            color: white;
        }

        .grid-desc {
            font-size: 0.9em;
            color: rgba(255, 255, 255, 0.8);
        }

        /* Update story-description untuk grid */
        .story-description {
            font-size: 1.2em;
            max-width: 90%;
            line-height: 1.6;
        }

        /* Responsive design untuk grid */
        @media (max-width: 768px) {
            .grid-summary {
                grid-template-columns: repeat(2, 1fr); /* Tetap 2 kolom di mobile */
                gap: 10px;
                padding: 10px;
                max-width: 320px; /* Lebih kecil untuk mobile */
                }
            
            .grid-item {
                padding: 10px;
            }
            
            .grid-icon {
                font-size: 1.8em;
            }
            
            .grid-value {
                font-size: 1.3em;
            }
        }

        /* Animasi untuk grid items */
        .grid-item {
            animation: fadeInUp 0.5s ease forwards;
            opacity: 0;
        }

        .grid-item:nth-child(1) { animation-delay: 0.1s; }
        .grid-item:nth-child(2) { animation-delay: 0.2s; }
        .grid-item:nth-child(3) { animation-delay: 0.3s; }
        .grid-item:nth-child(4) { animation-delay: 0.4s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            z-index: 4; /* Lebih tinggi dari navigation */
        }

        .action-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            pointer-events: auto; /* Pastikan tombol bisa diklik */
        }

        .action-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .action-btn i {
            font-size: 1.2em;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .action-buttons {
                margin-top: 20px;
            }
            
            .action-btn {
                padding: 10px 20px;
                font-size: 0.9em;
            }
        }

        /* kontrol musik */
        .music-control {
        position: fixed;
        top: 20px;
        right: 20px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        cursor: pointer;
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .music-control:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }

    .music-control.playing {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.1);
        }
        100% {
            transform: scale(1);
        }
    }
    </style>

    <script>
        // Data Wrapped User
        const wrappedData = [
            {
                title: "Siap melihat performa absensi Anda?",
                description: "Klik pojok kanan untuk menuju kilasan selanjutnya",
                icon: "<img src='/assets/smagaedu.png' class='logo'/>"
            },
            {
                title: "",
                description: "Waktu begitu cepat berlalu, termasuk masa-masa indah bersama Anda.",
                icon: ""
            },
            {
                title: "<?php echo $totalAbsensi; ?> kali",
                description: "Kami telah melayani kebutuhan Absensi Anda.",
                icon: "bi-calendar-check-fill"
            },
            {
                title: "<?php echo $persentaseTepatWaktu; ?>%",
                description: "absensi Anda dilakukan tepat waktu, terus tingkatkan Bapak/Ibu!",
                icon: "bi-alarm-fill"
            },
            {
                title: <?php echo json_encode($metodeFavorit); ?>,
                description: "merupakan metode yang paling sering digunakan oleh Anda untuk absensi",
                icon: "bi-camera-fill"
            },
            {
                title: <?php echo json_encode($waktuRekor); ?>,
                description: "Rekor terpagi Anda hadir pada tanggal <?php echo $tanggalRekor; ?>. Pertahankan semangat pagi Anda!",
                icon: "bi-trophy-fill"
            },
            {
                title: "",
                description: "Kami berharap, di tahun 2025, kami dapat terus memberikan kemudahan bagi Anda di Anda.",
                icon: ""
            },
            {
                title: "", 
                description: `  <div class="grid-summary">
                                <div class="grid-item">
                                    <div class="grid-icon"><i class="fas fa-calendar-check"></i></div>
                                    <div class="grid-value"><?php echo $totalAbsensi; ?> kali</div>
                                    <div class="grid-desc">Total Absensi</div>
                                </div>
                                <div class="grid-item">
                                    <div class="grid-icon"><i class="fas fa-clock"></i></div>
                                    <div class="grid-value"><?php echo $persentaseTepatWaktu; ?>%</div>
                                    <div class="grid-desc">Ketepatan Waktu</div>
                                </div>
                                <div class="grid-item">
                                    <div class="grid-icon"><i class="fas fa-medal"></i></div>
                                    <div class="grid-value"><?php echo $metodeFavorit; ?></div>
                                    <div class="grid-desc">Metode Favorit</div>
                                </div>
                                <div class="grid-item">
                                    <div class="grid-icon"><i class="fas fa-sun"></i></div>
                                    <div class="grid-value"><?php echo $waktuRekor; ?></div>
                                    <div class="grid-desc">Rekor Terpagi</div>
                                </div>
                            </div>`,
                icon: "fas fa-chart-simple"
            }
        ];

        let currentStory = 0;
        const storyTitle = document.getElementById('storyTitle');
        const storyDescription = document.getElementById('storyDescription');
        const progressBars = document.querySelectorAll('.progress-fill');
        const STORY_DURATION = 7000;
        let progressInterval;
        let storyTimer;
        let startTime;

        // Fungsi untuk mengupdate progress bar
        function updateProgressBar(index) {
            // Reset semua progress bar sebelumnya ke 100%
            for (let i = 0; i < index; i++) {
                progressBars[i].style.width = "100%";
            }
            
            // Reset semua progress bar setelahnya ke 0%
            for (let i = index + 1; i < progressBars.length; i++) {
                progressBars[i].style.width = "0%";
            }

            // Mulai progress bar untuk story saat ini
            startTime = Date.now();
            if (progressInterval) clearInterval(progressInterval);
            
            progressInterval = setInterval(() => {
                const elapsedTime = Date.now() - startTime;
                const progress = (elapsedTime / STORY_DURATION) * 100;
                
                if (progress <= 100) {
                    progressBars[index].style.width = `${progress}%`;
                } else {
                    clearInterval(progressInterval);
                    if (currentStory < wrappedData.length - 1) {
                        currentStory++;
                        updateStoryContent(currentStory);
                    }
                }
            }, 16);
        }
      
        function updateStoryContent(index) {
            updateProgressBar(index);
            
            storyTitle.classList.add('fade-out');
            storyDescription.classList.add('fade-out');

            setTimeout(() => {
                if (index === wrappedData.length - 1) { // Jika ini adalah story terakhir
                    // Tampilkan judul ringkasan
                    storyTitle.innerHTML = `
                        <div class="story-icon">
                            <i class="fas fa-chart-simple"></i>
                        </div>
                        <div class="mb-1" style="font-size:22px">Ringkasan Kilas Balik 2024</div>
                        <div class="" style="font-size:18px; font-weight:normal;"><?php echo htmlspecialchars($namaLengkap); ?></div>
                    `;
                    // Tampilkan grid ringkasan
                    storyDescription.innerHTML = `
                        <div class="grid-summary">
                            <div class="grid-item">
                                <div class="grid-icon"><i class="fas fa-calendar-check"></i></div>
                                <div class="grid-value"><?php echo $totalAbsensi; ?> kali</div>
                                <div class="grid-desc">Total Absensi</div>
                            </div>
                            <div class="grid-item">
                                <div class="grid-icon"><i class="fas fa-clock"></i></div>
                                <div class="grid-value"><?php echo $persentaseTepatWaktu; ?>%</div>
                                <div class="grid-desc">Ketepatan Waktu</div>
                            </div>
                            <div class="grid-item">
                                <div class="grid-icon"><i class="fas fa-medal"></i></div>
                                <div class="grid-value"><?php echo $metodeFavorit; ?></div>
                                <div class="grid-desc">Metode Favorit</div>
                            </div>
                            <div class="grid-item">
                                <div class="grid-icon"><i class="fas fa-sun"></i></div>
                                <div class="grid-value"><?php echo $waktuRekor; ?></div>
                                <div class="grid-desc">Rekor Terpagi</div>
                            </div>
                        </div>
                        <div class="action-buttons">
                            <button class="action-btn home-btn" onclick="goToHome()">
                                <i class="fas fa-home"></i> Beranda
                            </button>
                        </div>
                    `;
                } else {
                    // Tampilan normal untuk story lainnya
                    if (wrappedData[index].icon.includes('<img')) {
                        storyTitle.innerHTML = `
                            <div class="story-icon">
                                ${wrappedData[index].icon}
                            </div>
                            <div class="highlight-number">${wrappedData[index].title}</div>
                        `;
                    } else {
                        storyTitle.innerHTML = `
                            <div class="story-icon">
                                <i class="${wrappedData[index].icon}"></i>
                            </div>
                            <div class="highlight-number">${wrappedData[index].title}</div>
                        `;
                    }
                    storyDescription.textContent = wrappedData[index].description;
                }
                                    
                storyTitle.classList.remove('fade-out');
                storyDescription.classList.remove('fade-out');
                storyTitle.classList.add('fade-in');
                storyDescription.classList.add('fade-in');
            }, 500);

            if (storyTimer) clearTimeout(storyTimer);
            storyTimer = setTimeout(() => {
                if (currentStory < wrappedData.length - 1) {
                    currentStory++;
                    updateStoryContent(currentStory);
                }
            }, STORY_DURATION);
        }
        // Event Listeners untuk navigasi
        document.getElementById('prevStory').addEventListener('click', () => {
            if (currentStory > 0) {
                currentStory--;
                updateStoryContent(currentStory);
            }
        });

        document.getElementById('nextStory').addEventListener('click', () => {
            if (currentStory < wrappedData.length - 1) {
                currentStory++;
                updateStoryContent(currentStory);
            }
        });

        // Pause timer on hover
        document.querySelector('.story-container').addEventListener('mouseenter', () => {
            clearTimeout(storyTimer);
            clearInterval(progressInterval);
        });

        document.querySelector('.story-container').addEventListener('mouseleave', () => {
            updateStoryContent(currentStory);
        });

        // Initialize first story
        updateStoryContent(currentStory);
        // Pindahkan fungsi createFlowers ke scope global
        function createFlowers() {
            const container = document.querySelector('.story-container');
            if (!container) return; // Tambah pengecekkan untuk mencegah error
            
            const flowerCount = 15;
            
            // Hapus bunga yang lama
            const oldFlowers = container.querySelectorAll('.flower');
            oldFlowers.forEach(flower => flower.remove());
            
            for (let i = 0; i < flowerCount; i++) {
                const flower = document.createElement('div');
                flower.className = 'flower';
                flower.style.left = `${Math.random() * 100}%`;
                flower.style.animationDelay = `${Math.random() * 10}s`;
                container.appendChild(flower);
            }
        }

            // Inisialisasi bunga saat DOM sudah siap
            let flowerInterval; // Tambahkan variable untuk interval

            document.addEventListener('DOMContentLoaded', function() {
                const bgMusic = document.getElementById('bgMusic');
                bgMusic.muted = false;
            
            // Fungsi untuk memulai musik
            function playMusic() {
                bgMusic.play().catch(function(error) {
                    console.log("Audio play failed:", error);
                });
            }

            // Tambahkan tombol kontrol musik (optional)
            const musicControl = document.createElement('div');
            musicControl.innerHTML = `
                <button id="musicToggle" class="music-control">
                    <i class="fas fa-music"></i>
                </button>
            `;
            document.body.appendChild(musicControl);

            // Toggle musik saat tombol diklik
            document.getElementById('musicToggle').addEventListener('click', function() {
                if (bgMusic.paused) {
                    bgMusic.play();
                    this.classList.add('playing');
                } else {
                    bgMusic.pause();
                    this.classList.remove('playing');
                }
            });

            // Coba putar musik saat interaksi pertama dengan page
            document.body.addEventListener('click', function() {
                if (bgMusic.paused) {
                    playMusic();
                }
            }, { once: true });
        });

            // Update event listener mouseenter untuk menghentikan animasi bunga
            document.querySelector('.story-container').addEventListener('mouseenter', () => {
                clearTimeout(storyTimer);
                clearInterval(progressInterval);
                clearInterval(flowerInterval); // Tambahkan ini
            });

            // Update event listener mouseleave untuk memulai kembali animasi
            document.querySelector('.story-container').addEventListener('mouseleave', () => {
                updateStoryContent(currentStory);
                flowerInterval = setInterval(createFlowers, 20000); // Tambahkan ini
            });

            // klik beranda di ringkasan kilas balik
            function goToHome() {
                // Fungsi untuk kembali ke beranda
                window.location.href = 'beranda.php';  // Atau URL yang sesuai
            }

    </script>
    </body>
</html>