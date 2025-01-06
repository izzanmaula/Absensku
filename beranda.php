<?php
session_start();
require_once 'koneksi.php';

// Cek session dulu
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}




// Ambil user_id dari session
$user_id = $_SESSION['user_id'];


// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// // Debug session
// echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px; font-family: monospace;'>";
// echo "Session ID: " . session_id();
// echo "Session Contents: <pre>";
// print_r($_SESSION);
// echo "</pre>";
// echo "</div>";

// // File konfigurasi PHP
// $phpinfo = array(
//     'session.save_handler' => ini_get('session.save_handler'),
//     'session.save_path' => ini_get('session.save_path'),
//     'session.use_cookies' => ini_get('session.use_cookies'),
//     'session.name' => ini_get('session.name')
// );

// // Tampilkan informasi PHP
// echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px; font-family: monospace;'>";
// echo "PHP Session Configuration:<pre>";
// print_r($phpinfo);
// echo "</pre></div>";

// // Cek login
// if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
//     header("Location: login.php");
//     exit();
// }



// Cek apakah user_id valid (ada di database)
$check_user = $conn->prepare("SELECT id FROM users WHERE id = ?");
$check_user->bind_param("i", $user_id);
$check_user->execute();
$result_user = $check_user->get_result();

if ($result_user->num_rows === 0) {
    // Jika user_id tidak valid, hapus session dan redirect
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}
$check_user->close();


// Ambil namaLengkap dari tabel users
$query = "SELECT namaLengkap FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($namaLengkap);
$stmt->fetch();
$stmt->close();

// ambil untuk jam kerja
// ambil jam datang dari database
$id_user = $_SESSION['user_id'];

// Konversi hari ke bahasa Indonesia
function getHariIndonesia() {
    $hari_inggris = strtolower(date('l'));
    $hari_indonesia = [
        'sunday' => 'minggu',
        'monday' => 'senin',
        'tuesday' => 'selasa',
        'wednesday' => 'rabu',
        'thursday' => 'kamis',
        'friday' => 'jumat',
        'saturday' => 'sabtu'
    ];
    return $hari_indonesia[$hari_inggris];
}

$hari_ini_db = getHariIndonesia();
$query_jam = "SELECT {$hari_ini_db}_datang as jam_datang, {$hari_ini_db}_pulang as jam_pulang FROM users WHERE id = ?";

$stmt_jam = $conn->prepare($query_jam);
if (!$stmt_jam){
    die("error : " .$conn->error);
}
$stmt_jam->bind_param("i", $id_user);
$stmt_jam->execute();
$result = $stmt_jam->get_result();

if($result->num_rows > 0) {
    $row_jam = $result->fetch_assoc();
    
    // Cek apakah jam datang dan pulang NULL
    if ($row_jam["jam_datang"] === NULL || $row_jam["jam_pulang"] === NULL) {
        $awal_absen = "Libur";
        $akhir_absen = "Libur";
        $akhir_kerja = "Libur";
    } else {
        $awal_absen = (new DateTime($row_jam["jam_datang"]))->modify('-1 hour')->format("H:i"); // Hanya jam dan menit
        $akhir_absen = (new DateTime($row_jam["jam_datang"]))->format("H:i"); // Hanya jam dan menit
        $akhir_kerja = (new DateTime($row_jam["jam_pulang"]))->format("H:i"); // Hanya jam dan menit
    }
} else {
    header('Location: index.php');
    exit(); // Pastikan script berhenti setelah redirect
}

// Ambil hari ini
$hari = date('N'); // Dapatkan hari dalam seminggu (1 = Senin, ..., 7 = Ahad)


// ringasakn kehadiran
// ambil terlambat di database
$query_terlambat = "SELECT COUNT(*) as count FROM datang WHERE user_id =? AND TRIM(LOWER(status)) = 'terlambat'";
$stmt_terlambat = $conn->prepare($query_terlambat);
$stmt_terlambat->bind_param("i", $user_id);
$stmt_terlambat-> execute();
$result_terlambat = $stmt_terlambat->get_result();
$row_terlambat = $result_terlambat->fetch_assoc();

$jumlah_terlambat = $row_terlambat["count"];
$stmt_terlambat->close();

// ambil hadir di database 
$query_hadir = "SELECT COUNT(*) as count FROM datang WHERE user_id =? AND TRIM(LOWER(status)) = 'tepat waktu'";
$stmt_hadir= $conn->prepare($query_hadir);
$stmt_hadir->bind_param("i", $user_id);
$stmt_hadir-> execute();
$result_hadir = $stmt_hadir -> get_result();
$row_hadir = $result_hadir -> fetch_assoc();

$jumlah_hadir= $row_hadir["count"];
$stmt_hadir->close();



// Misalkan user ID kepala sekolah adalah '12345' atau username 'kepala_sekolah'
$allowed_user_ids = ['2']; // Ganti dengan ID yang sesuai
$allowed_usernames = ['fauzinugroho']; // Atau username

// Cek apakah pengguna yang masuk memiliki user ID atau username yang diizinkan
if (isset($_SESSION['user_id']) && in_array($_SESSION['user_id'], $allowed_user_ids) ||
    isset($_SESSION['username']) && in_array($_SESSION['username'], $allowed_usernames)) {
    // Arahkan pengguna ke beranda_superUser.php
    header("Location: beranda_superUser.php");
    exit();
}

// Ambil jumlah_poin user untuk menentukan badge saat ini
$user_id = $_SESSION['user_id'];
$query_poin = "SELECT jumlah_poin FROM poin_user WHERE user_id = ?";
$stmt_poin = $conn->prepare($query_poin);
$stmt_poin->bind_param("i", $user_id);
$stmt_poin->execute();
$result_poin = $stmt_poin->get_result();
$jumlah_poin = ($result_poin->num_rows > 0) ? $result_poin->fetch_assoc()['jumlah_poin'] : 0;

// Ambil semua data pencapaian
$query_pencapaian = "SELECT * FROM pencapaian ORDER BY strike ASC";
$result_pencapaian = $conn->query($query_pencapaian);
$pencapaian_list = [];
$current_badge = null;

// Masukkan data pencapaian ke array dan tentukan badge saat ini
if ($result_pencapaian && $result_pencapaian->num_rows > 0) {
    while ($row = $result_pencapaian->fetch_assoc()) {
        $pencapaian_list[] = $row;
        // Tentukan badge saat ini berdasarkan jumlah_poin
        if ($jumlah_poin >= $row['strike']) {
            $current_badge = $row;
        }
    }
}


// Tambahkan pengecekan error
if (!$result) {
    echo "Error: " . mysqli_error($conn);
    die();
}

// Tutup statement
$conn->close();

error_log("Session check: " . print_r($_SESSION, true));
?>


<!DOCTYPE html>
<html lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/ol@v10.2.1/dist/ol.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ol@v10.2.1/ol.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@48,400,1,0&icon_names=check_circle" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ol@v7.4.0/ol.css">
    <script src="https://cdn.jsdelivr.net/npm/ol@v7.4.0/dist/ol.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <title>Halosmaga - Absen</title>
</head>


<style>

    .main-container {
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.4s ease-out forwards;
        position: relative;
        z-index: 1;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

/* iOS-like zoom animation untuk semua modal */
.modal.fade {
    transition: opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal.fade .modal-dialog {
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    transform: scale(0.85);
}

.modal.show .modal-dialog {
    transform: scale(1);
}

/* Backdrop yang smooth */
.modal-backdrop {
    transition: opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

.modal-backdrop.show {
    opacity: 0.5;
}

/* Modal content */
.modal-content {
    background-color: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}
</style>

<!-- script untuk modal -->
 <script>
    document.addEventListener('DOMContentLoaded', function() {
    // Handle backdrop untuk semua modal
    document.addEventListener('show.bs.modal', function(event) {
        setTimeout(() => {
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.style.transition = 'opacity 0.3s ease-out';
            }
        }, 0);
    });

    // Tambahkan touch event untuk iOS-like swipe down to close
    document.querySelectorAll('.modal').forEach(modal => {
        let touchStart = 0;
        let touchEnd = 0;
        
        modal.addEventListener('touchstart', function(e) {
            touchStart = e.changedTouches[0].screenY;
        }, false);
        
        modal.addEventListener('touchend', function(e) {
            touchEnd = e.changedTouches[0].screenY;
            if (touchEnd > touchStart && (touchEnd - touchStart) > 50) {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }
        }, false);
    });
});
 </script>

<body style="background-color: rgb(238, 236, 226); font-family: merriweather;
  font-feature-settings: 'liga' 1, 'calt' 1;" data-user-id="<?php echo $_SESSION['user_id']; ?>">
    <!-- informasi profil -->
    <div class="d-flex mt-3 me-4 ms-3 align-items-center gap-2">
        <div>
            <img src="assets/smagaedu.png" alt="" width="40px" class="bg-white rounded-circle p-1" loading="lazy">
        </div>
        <div class="flex-grow-1">
            <a href="logout.php" class="text-decoration-none text-black">
            <p style="font-size: 12px; margin: 0; padding: 0;">Selamat Datang,</p>
            </a>
            <p style="font-weight: bold; margin: 0; padding: 0; font-size:14px"><?php echo htmlspecialchars($namaLengkap); ?></p>
        </div>
    </div>

    <script>
        // Tambahkan di event listener modal show
        document.addEventListener('show.bs.modal', function() {
            document.body.classList.add('modal-open');
        });

        // Dan hapus saat modal tertutup
        document.addEventListener('hidden.bs.modal', function() {
            document.body.classList.remove('modal-open');
        });
    </script>

    <!-- informasi header
    <div class="alert alert-success alert-dismissible fade show pt-2 pb-2 ps-1 pe-4 m-3 rounded-4" role="alert">
    <div d-grid>
        <div class="container">
            <div class="row align-items-center">
                <div class="col justify-content-center text-center pe-3">
                    <img src="assets/love.png" alt="" width="40px">
                </div>
                <div class="col-9 p-0">
                    <p class="p-0 m-0" style="font-size:15px;"><strong>Selamat Hari Guru!</strong></p>
                    <p style="font-size:12px;">Pembaruan absensi khusus Hari Guru, 
                        kami terus berkomitmen memberikan fasilitas terbaik untuk pahlawan tanpa jasa, Anda.</p>
                </div>
            </div>
        </div>
    </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div> -->

<!-- Kilas Balik Absensi
<div class="d-flex me-3 mt-3 ms-3 rounded-4 p-2 aura btn-waktu position-relative overflow-hidden">
    <div class="btn text-start position-relative" style="z-index: 1;">
        <a href="kilasbalik.php" class="text-decoration-none d-flex gap-4 align-items-center">
            <div>
                <p id="aiText" class="p-0 m-0 text-white" style="font-size: 16px;"><strong>Kilas Balik Absensi 2024</strong></p>        
                <p class="text-white" style="font-size: 12px;">Mari cek perjalanan Absensi Anda bersama kami dalam tahun 2024!</p>
            </div>
            <div>
                <span class="bi-gift-fill" style="font-size: 3rem; color:white;"></span>
            </div>
        </a>
    </div>
</div> -->

<style>
.aura {
    border-radius: 20px;
    padding: 20px;
    color: #fff;
    background: linear-gradient(
        45deg, 
        #DA7756,
        #A95342,
        #753730,
        #DA7756
    );
    background-size: 300% 300%;
    animation: gradientBackground 6s ease infinite;
    position: relative;
    overflow: hidden;
}

/* Menambahkan elemen bunga dengan pseudo-elements */
.aura::before,
.aura::after {
    content: '';
    position: absolute;
    width: 150px;
    height: 150px;
    background: radial-gradient(circle at center, transparent 30%, rgba(255,255,255,0.1) 40%, transparent 70%);
    border-radius: 50%;
    animation: floatingFlowers 15s infinite linear;
}

.aura::before {
    top: -75px;
    left: -75px;
    animation-delay: 0s;
}

.aura::after {
    bottom: -75px;
    right: -75px;
    animation-delay: -7.5s;
}

/* Tambahan elemen bunga */
.aura .btn::before,
.aura .btn::after {
    content: '';
    position: absolute;
    width: 100px;
    height: 100px;
    background: radial-gradient(circle at center, transparent 30%, rgba(255,255,255,0.15) 40%, transparent 70%);
    border-radius: 50%;
    animation: floatingFlowers 12s infinite linear;
}

.aura .btn::before {
    top: -50px;
    right: -50px;
    animation-delay: -3.75s;
}

.aura .btn::after {
    bottom: -50px;
    left: -50px;
    animation-delay: -11.25s;
}

@keyframes gradientBackground {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

@keyframes floatingFlowers {
    0% {
        transform: rotate(0deg) translate(20px) rotate(0deg) scale(1);
        opacity: 0.5;
    }
    50% {
        transform: rotate(180deg) translate(20px) rotate(-180deg) scale(1.2);
        opacity: 0.7;
    }
    100% {
        transform: rotate(360deg) translate(20px) rotate(-360deg) scale(1);
        opacity: 0.5;
    }
}

.aura:hover {
    transform: scale(1.02);
    transition: transform 0.3s ease-in-out;
}

/* Tambahan efek glitter */
.aura::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle at center, 
        rgba(255,255,255,0.1) 0%,
        rgba(255,255,255,0.05) 25%,
        transparent 50%);
    animation: sparkle 4s infinite linear;
}

@keyframes sparkle {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>

    
    <!-- absen -->
    <div class="mt-3 pt-2 pb-2 me-3 ms-3 rounded-4 text-white main-container bg-white" style="background-color:white;animation-delay: 0.0s">
        <!-- Container dengan fixed width -->
        <div class="container-fluid px-2">
            <div class="row gx-2">
                <!-- Menggunakan col-3 untuk memaksa 4 kolom sejajar -->
                <div class="col-3">
                    <button class="btn buttonAbsen_menu w-100 d-flex flex-column align-items-center p-2 rounded-4" data-bs-toggle="modal" data-bs-target="#lokasiModal">
                        <div class="d-flex align-items-center justify-content-center bg-white rounded-circle icon-circle">
                            <i class="icon-button bi bi-radar"></i>
                        </div>
                        <span class="mt-1 text-white button-text">Lokasi</span>
                    </button>
                </div>
                <div class="col-3">
                    <button class="btn buttonAbsen_menu w-100 d-flex flex-column align-items-center p-2 rounded-4" data-bs-toggle="modal" data-bs-target="#kebijakanBarcode">
                        <div class="d-flex align-items-center justify-content-center bg-white rounded-circle icon-circle">
                            <i class="icon-button bi bi-upc"></i>
                        </div>
                        <span class="mt-1 text-white button-text">Barcode</span>
                    </button>
                </div>
                <div class="col-3">
                    <button class="btn buttonAbsen_menu w-100 d-flex flex-column align-items-center p-2 rounded-4" data-bs-toggle="modal" data-bs-target="#kebijakanAbsen">
                        <div class="d-flex align-items-center justify-content-center bg-white rounded-circle icon-circle">
                            <i class="icon-button bi bi-camera"></i>
                        </div>
                        <span class="mt-1 text-white button-text">Kamera</span>
                    </button>
                </div>
                <div class="col-3">
                    <button class="btn buttonAbsen_menu w-100 d-flex flex-column align-items-center p-2 rounded-4" data-bs-toggle="modal" data-bs-target="#modalIjin">
                        <div class="d-flex align-items-center justify-content-center bg-white rounded-circle icon-circle">
                            <i class="icon-button bi bi-envelope"></i>
                        </div>
                        <span class="mt-1 text-white button-text">Izin</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
.buttonAbsen_menu {
    background-color: rgb(218, 119, 86);
    border: none;
    padding: 1rem !important;
    height: 120px; /* Menambah tinggi button */
    display: flex;
    justify-content: space-between;
    flex-direction: column;
    align-items: center;
}

.buttonAbsen_menu:hover {
    background-color: #d35400;
}

.icon-circle {
    width: 50px;
    height: 50px;
}

.icon-circle i {
    font-size: 1.5rem;
    color: #000;
}

.button-text {
    font-size: 12px;
    white-space: nowrap;
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .icon-circle {
        width: 50px;
        height: 50px;
        margin-top: 10px;
    }
    
    .icon-circle i {
        font-size: 1.5rem;
    }
    
    .button-text {
        font-size: 12px;
        margin-bottom: 20px;
    }
    
    .buttonAbsen_menu {
        padding: 0.25rem !important;
    }
}
</style>

    <!-- style button absen -->
     <style>
        .buttonAbsen{
            background-color: rgb(218, 119, 86);
            color: white;
            transform: background-color ease 0.3, color ease 0.3;
        }
        .buttonAbsen:hover{
            background-color: white;
            color: black;
        }
     </style>

     <!-- modal untuk ijin -->
                  <div class="modal fade text-black" id="modalIjin" tabindex="-1" aria-hidden="true" data-bs-keyboard="false" data-bs-backdrop="static">
                <div class="modal-dialog modal-lg modal-dialog-centered text-start">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Permohonan Perizinan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="bg-white p-3 rounded-4">
                                <p class="">Alasan Permohonan</p>
                                <div class="dropdown d-flex m-1 mt-2">
                                    <button class="btn dropdown-toggle flex-fill" style="background-color:rgb(238, 236, 226);" type="button" id="jenisIzin" data-bs-toggle="dropdown" aria-expanded="false">
                                        Pilih Alasan Anda
                                    </button>
                                    <ul class="dropdown-menu flex-fill" aria-labelledby="jenisIzin">
                                        <li><a class="dropdown-item">Sakit</a></li>
                                        <li><a class="dropdown-item">Cuti</a></li>
                                        <li><a class="dropdown-item">Izin Lainya</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="bg-white p-3 mt-3 rounded-4">
                                <div class="">
                                    <div class="">
                                        <p style="font-size: 12px;" class="p-0 m-0 mb-2">Tanggal Mulai</p>
                                        <input type="date" class="form-control" id="tanggalIzin" placeholder="Tanggal Mulai" name="tanggalIzin">
                                    </div>
                                    <div class="mt-4">
                                        <p style="font-size: 12px;" class="p-0 m- mb-2">Tanggal Selesai</p>
                                        <input type="date" class="form-control" id="tanggalIzin" placeholder="Tanggal Selesai" name="tanggalIzin">
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white p-3 mt-3 rounded-4">
                                <p class="p-0 m-0">Keterangan</p>
                                <div class="mt-2">
                                    <textarea class="form-control" id="keteranganIzin" rows="3" placeholder="Keterangan"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer btn-group justify-content-between d-flex" role="group">
                            <button type="button" class="btn flex-fill" style="background-color: #DA7756; color:white;" id="kirimPerizinan">
                                <span class="normal-text">Ajukan Perizinan</span>
                                <span class="spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                    Mengirim...
                                </span>
                            </button>                        
                        </div>
                    </div>
                </div>
            </div>

            <!-- modal respons perijinan -->

             <!-- Modal Sukses -->
            <div class="modal fade" id="modalijinSukses" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body">
                                <div d-grid>
                                    <div class="container">
                                        <div class="row align-items-center">
                                            <div class="col justify-content-center text-center">
                                                <div class="bi-check-circle-fill" style="font-size: 30px; color:#DA7756;"></div>
                                            </div>
                                            <div class="col-9">
                                                <p class="p-0 m-0"><strong>Perizinan Terkirim</strong></p>
                                                <p style="font-size:14px;">Catatan perizinan Anda telah kami terima. Anda akan di alihkan ke bukti perizinan sebentar lagi.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
            </div>



            <!-- Modal Error -->
            <div class="modal fade" id="modalIjinError" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body">
                                <div d-grid>
                                    <div class="container">
                                        <div class="row align-items-center">
                                            <div class="col justify-content-center text-center">
                                                <div class="bi bi-exclamation-circle-fill" style="font-size: 30px; color:#DA7756;"></div>
                                            </div>
                                            <div class="col-9">
                                                <p class="p-0 m-0"><strong>Perizinan Ditolak</strong></p>
                                                <p style="font-size:14px;">Catatan perizinan Anda kami tolak, hubungi admin.</p>
                                                <div class="bg-white rounded-4 p-3">
                                                    <p style="font-size: 12px;" class="p-0 m-0 mb-1">error :</p>
                                                    <p class="text-muted p-0 m-0" style="font-size: 12px;" id="errorMessage"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer d-flex" role="group">
                                <button type="button" id="okButton" class="btn buttonAbsen flex-fill" data-bs-dismiss="modal">Tutup</button>
                            </div>
                    </div>
                </div>
            </div>

            <!-- style backdrop problem untuk modal error -->
            <style>
            /* Style untuk backdrop modal */
            #modalIjinError.modal {
                background: rgba(0, 0, 0, 0.8); /* Background hitam dengan opacity 0.8 */
            }

            /* Pastikan modal content tetap solid */
            #modalIjinError .modal-content {
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            }

            /* Animasi fade untuk modal */
            #modalIjinError.fade .modal-dialog {
                transition: transform .2s ease-out;
            }
        </style>

            <!-- modal untuk ringkasan perizinan -->
            <div class="modal fade" id="modalBuktiIzin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bukti Pengajuan Perizinan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card rounded-4 position-relative overflow-hidden">
                    <div class="card-body">
                        <div class="text-start mb-2">
                            <i class="bi bi-file-earmark-text" style="font-size: 3rem; color: #DA7756;"></i>
                        </div>

                        <div class="mb-3">
                            <label class="text-muted">Nama</label>
                            <p class="fw-bold"><?php echo htmlspecialchars($namaLengkap); ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="text-muted">Jenis Perizinan</label>
                            <p class="fw-bold" id="buktiJenisIzin">-</p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="text-muted">Tanggal Mulai</label>
                            <p class="fw-bold" id="buktiTanggalMulai">-</p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="text-muted">Tanggal Selesai</label>
                            <p class="fw-bold" id="buktiTanggalSelesai">-</p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="text-muted">Keterangan</label>
                            <p class="fw-bold" id="buktiKeterangan">-</p>
                        </div>
                        
                        <div class="text-start mt-4">
                            <small class="text-muted" style="font-size: 10px;">Diajukan pada: <span id="buktiWaktuPengajuan">-</span></small>
                            <p style="font-size: 10px;" class="text-muted">Ini adalah bukti perizinan Anda, silahkan simpan untuk keperluan administrasi Anda</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex">      
                <button type="button" class="btn buttonAbsen flex-fill" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


        
        <!-- script untuk modal perijinan -->
<script>
document.querySelectorAll('.dropdown-item').forEach(item => {
    item.addEventListener('click', function() {
        document.querySelector('#jenisIzin').textContent = this.textContent;
    });
});

function showErrorModal(message) {
    const errorModal = new bootstrap.Modal(document.getElementById('modalIjinError'));
    if (message) {
        document.getElementById('errorMessage').textContent = message;
    }
    errorModal.show();
}

// Fungsi showSuccessModal yang benar
function showSuccessModal(formData) {
    const modalIjin = bootstrap.Modal.getInstance(document.getElementById('modalIjin'));
    modalIjin.hide();
    
    const successModal = new bootstrap.Modal(document.getElementById('modalijinSukses'));
    successModal.show();
    
    // Format tanggal
    const formatDate = (dateString) => {
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('id-ID', options);
    };
    
    // Isi data ke modal bukti
    document.getElementById('buktiJenisIzin').textContent = formData.perizinan;
    document.getElementById('buktiTanggalMulai').textContent = formatDate(formData.tanggal_mulai);
    document.getElementById('buktiTanggalSelesai').textContent = formatDate(formData.tanggal_selesai);
    document.getElementById('buktiKeterangan').textContent = formData.keterangan;
    document.getElementById('buktiWaktuPengajuan').textContent = formatDate(new Date());
    
    // Otomatis tutup modal sukses setelah 2 detik dan tampilkan bukti
    setTimeout(() => {
        successModal.hide();
        setTimeout(() => {
            const modalBukti = new bootstrap.Modal(document.getElementById('modalBuktiIzin'));
            modalBukti.show();
        }, 500);
    }, 2000);
}

function submitIjin() {
    const button = document.getElementById('kirimPerizinan');
    const normalText = button.querySelector('.normal-text');
    const spinner = button.querySelector('.spinner');

    const perizinan = document.querySelector('#jenisIzin').textContent.trim();
    const tanggalMulai = document.querySelector('input[name="tanggalIzin"]').value;
    const tanggalSelesai = document.querySelectorAll('input[name="tanggalIzin"]')[1].value;
    const keterangan = document.querySelector('#keteranganIzin').value;

    if (perizinan === 'Pilih Alasan Anda' || !tanggalMulai || !tanggalSelesai || !keterangan) {
        showErrorModal('Mohon lengkapi semua data');
        return;
    }

    normalText.classList.add('d-none');
    spinner.classList.remove('d-none');
    button.disabled = true;

    const formData = {
        user_id: <?php echo $_SESSION['user_id']; ?>,
        perizinan: perizinan,
        tanggal_mulai: tanggalMulai,
        tanggal_selesai: tanggalSelesai,
        keterangan: keterangan
    };

    fetch('ijin_back.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            // Reset form
            document.querySelector('#jenisIzin').textContent = 'Pilih Alasan Anda';
            document.querySelector('input[name="tanggalIzin"]').value = '';
            document.querySelectorAll('input[name="tanggalIzin"]')[1].value = '';
            document.querySelector('#keteranganIzin').value = '';
            
            // Kirim data form ke showSuccessModal
            showSuccessModal(formData);
        } else {
            showErrorModal(result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorModal('Terjadi kesalahan sistem. Silakan coba lagi.');
    })
    .finally(() => {
        normalText.classList.remove('d-none');
        spinner.classList.add('d-none');
        button.disabled = false;
    });
}

document.getElementById('kirimPerizinan').addEventListener('click', submitIjin);document.getElementById('kirimPerizinan').addEventListener('click', submitIjin);
</script>



     


        <!-- kumpulan modal respons absen, di bawah ini ya -->


            <!-- modal konfirmasi absen lokasi -->
            <div class="modal fade text-black" id="lokasiModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered text-start">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Absensi Lokasi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                        <p>Sebelum melanjutkan, pastikan Anda telah memahami ketentuan berikut :</p>
                                <div d-grid>
                                    <div class="container">
                                        <div class="row align-items-center">
                                            <div class="col justify-content-center text-center">
                                                <div class="bi-crosshair2" style="font-size: 40px; color:#DA7756;"></div>
                                            </div>
                                            <div class="col-9">
                                            <p class="p-0 m-0"><strong>Lokasi Telah Aktif</strong></p>
                                                <p style="font-size:14px;">Pastikan Anda telah mengaktifkan lokasi dan mengizinkan kami untuk melacak lokasi Anda.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                        <div class="modal-footer btn-group justify-content-between d-flex" role="group">
                            <button type="button" class="btn flex-fill" style="background-color: #DA7756; color:white;" id="absenLokasiButton">
                                <span class="normal-text">Lacak Saya Sekarang</span>
                                <span class="spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                    Melacak...
                                </span>
                            </button>                        
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal absen lokasi Gagal -->
            <div class="modal fade text-black" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered text-start">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="errorModalLabel">Absen Ditolak</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Lokasi Anda saat ini tidak sesuai dengan lokasi sekolah, atau gunakan metode absen lainya<br>
                            <div id="map" class="mt-3" style="width: 100%; height: 200px;"></div>
                            <p id="currentLocation" style="font-size:12px;" class="mt-2 m-0 p-0"></p>
                        </div>
                        <div class="modal-footer btn-group justify-content-between" role="group">
                                <button type="button" class="btn buttonAbsen" data-bs-dismiss="modal">Ok</button>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Modal absen lokasi Sukses -->
            <div class="modal fade text-black" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered text-start">
                    <div class="modal-content">
                        <div class="modal-body">
                                <div d-grid>
                                    <div class="container">
                                        <div class="row align-items-center">
                                            <div class="col justify-content-center text-center">
                                                <div class="bi-check-circle-fill" style="font-size: 30px; color:#DA7756;"></div>
                                            </div>
                                            <div class="col-9">
                                                <p class="p-0 m-0"><strong>Absen Diterima</strong></p>
                                                <p style="font-size:14px;">Catatan kehadiran Anda telah kami terima.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer btn-group justify-content-between" role="group">
                                <button type="button" class="btn" style="background-color: #DA7756; color:white;" data-bs-dismiss="modal">Ok</button>
                            </div>
                            </div>
                        </div>
                    </div>


                        <!-- Modal absen lokasi tidak ada jadwal-->
                        <div class="modal fade text-black" id="noScheduleModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered text-start">
                    <div class="modal-content">
                        <div class="modal-body">
                                <div d-grid>
                                    <div class="container">
                                        <div class="row align-items-center">
                                            <div class="col justify-content-center text-center">
                                                <div class="bi-cup-hot-fill" style="font-size: 30px; color:#DA7756;"></div>
                                            </div>
                                            <div class="col-9">
                                                <p class="p-0 m-0"><strong>Tidak ada jadwal hari ini</strong></p>
                                                <p style="font-size:14px;">Kami cek Anda tidak mempunyai jadwal masuk sekolah, selamat beristirahat.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer btn-group justify-content-between" role="group">
                                <button type="button" class="btn buttonAbsen" data-bs-dismiss="modal">Ok</button>
                            </div>
                            </div>
                        </div>
                    </div>


        <!-- penringatan untuk absen barcode -->
        <div class="modal fade text-black" id="kebijakanBarcode" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered text-start">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Absensi Barcode</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                        <p>Sebelum melanjutkan, pastikan Anda telah memahami ketentuan berikut :</p>
                                <div d-grid>
                                    <div class="container">
                                        <div class="row align-items-center">
                                            <div class="col justify-content-center text-center">
                                                <div class="bi-person-circle" style="font-size: 40px; color:#DA7756;"></div>
                                            </div>
                                            <div class="col-9">
                                            <p class="p-0 m-0"><strong>Hubungi Bagian Tata Usaha</strong></p>
                                                <p style="font-size:14px;">Harap menghubungi Bagian Tata Usaha SMAGA untuk mendapatkan barcode absensi.</p>
                                            </div>
                                        </div>
                                        <div class="row align-items-center">
                                            <div class="col justify-content-center text-center">
                                                <div class="bi-x-circle-fill" style="font-size: 40px; color:#DA7756;"></div>
                                            </div>
                                            <div class="col-9">
                                            <p class="p-0 m-0"><strong>Peringatan: Dilarang Menyebarluaskan</strong></p>
                                                <p style="font-size:14px;">Barcode absensi bersifat rahasia dan tidak diperkenankan untuk disebarluaskan kepada pihak manapun.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                        <div class="modal-footer btn-group justify-content-between d-flex" role="group">
                            <button type="button" class="btn flex-fill" style="background-color: #DA7756; color:white;" id="absenbarcodesekarang" data-bs-target="#barcodeModal">
                                Scan Barcode Sekarang
                            </button>                        
                        </div>
                    </div>
                </div>
            </div>



                <!-- Modal untuk absen barcode -->
                <div class="modal fade text-black" id="barcodeModal" tabindex="-1" aria-labelledby="barcodeModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <!-- <div class="modal-header">
                                <h5 class="modal-title" id="barcodeModalLabel">Scan Barcode Absensi</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div> -->
                            <div class="modal-body">
                                <video id="preview" width="100%" class="rounded-4"></video>
                            </div>
                            <div class="modal-footer btn-group justify-content-between" role="group">
                                <button id="switchCamera" type="button" class="btn buttonAbsen" >Ganti Kamera</button>                        
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            </div>
                        </div>
                    </div>
                </div>            



    <!-- event listener untuk barcode modal -->
     <script>
        document.getElementById("absenbarcodesekarang").addEventListener('click', function(){
            // Dapatkan instance modal yang sedang aktif
            const modalKebijakanBarcode = bootstrap.Modal.getInstance(document.getElementById('kebijakanBarcode'));
            // Tutup modal kebijakan terlebih dahulu
            modalKebijakanBarcode.hide();
            
            // Tunggu sebentar sebelum membuka modal baru
            setTimeout(() => {
                const modalBarcode = new bootstrap.Modal(document.getElementById('barcodeModal'));
                modalBarcode.show();
            }, 300); // Delay 300ms untuk animasi closing modal sebelumnya
        });
     </script>

    <!-- container aplikasi lainnya -->
    <div class="mt-3 me-3 ms-3 mb-0 rounded-4 p-4 main-container" style="background-color: white; position:relative;z-index:2;animation-delay: 0.2s">
        <!-- button fitur -->
        <div class="d-flex justify-content-between me-3 ms-3">
            <!-- refresh -->
            <div class="text-center d-flex flex-column align-items-center" id="refresh">
                <div style="width: 50px; height: 50px; background-color: rgb(235, 219, 188);" 
                    class="d-flex align-items-center justify-content-center rounded-4">
                    <i class="bi bi-arrow-clockwise" style="font-size: 25px; color: black;"></i>
                </div>
                <p style="margin: 0; font-size: 12px; margin-top: 8px; color: black;">Segarkan</p>
            </div>
            <script>
                document.getElementById('refresh').addEventListener('click', function(){
                    location.reload();
                });
            </script>
            <!-- riwayat -->
            <div class="text-center d-flex flex-column align-items-center">
                <div style="width: 50px; height: 50px; background-color: rgb(235, 219, 188);" 
                    class="d-flex align-items-center justify-content-center rounded-4">
                    <a href="pembaharuan.php" class="text-decoration-none">
                    <i class="bi bi-arrow-down-circle" style="font-size: 25px; color: black;"></i>
                </div>
                <p style="margin: 0; font-size: 12px; margin-top: 8px; color: black;">Pembaruan</p>
                </a>
            </div>
            <!-- kebijakan -->
            <div class="text-center d-flex flex-column align-items-center">
                <div style="width: 50px; height: 50px; background-color: rgb(235, 219, 188);" 
                    class="d-flex align-items-center justify-content-center rounded-4">
                    <a href="kebijakan.php" class="text-decoration-none">
                    <i class="bi bi-clipboard-check" style="font-size: 25px; color: black;"></i>
                </div>
                <p style="margin: 0; font-size: 12px; margin-top: 8px; color: black;">Kebijakan</p>
                </a>
            </div>
        </div>
    </div>



    <!-- Modal untuk Absensi Berhasil -->
<div class="modal fade text-black" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="successModalLabel">Absensi Berhasil</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Anda telah berhasil melakukan absensi, semoga hari Anda menyenangkan
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal untuk Barcode Tidak Dikenali -->
<div class="modal fade text-black" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="errorModalLabel">Barcode Tidak Dikenali</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Barcode yang Anda gunakan tidak sesuai, gunakan barcode sesuai dengan arahan tim Administrator atau gunakan metode absen lainya
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

        <!-- Modal selfie -->
        <div class="modal fade text-black" id="selfieModal" tabindex="-1" aria-labelledby="selfieModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered text-start">
                <div class="modal-content">
                    <div class="modal-body p-0 position-relative">
                        <div class="video-wrapper">
                            <!-- Video container -->
                            <video id="video" width="100%" class="rounded-3" autoplay></video>
                            <div class="sweep-effect"></div>
                            <div class="glow-effect"></div>
                            <canvas id="faceCanvas" class="position-absolute top-0 start-0" style="width: 100%; height: 100%;"></canvas>

                            <!-- Alert overlay -->
                            <div id="detectionAlert" class="detection-alert">
                                Biarkan kami memuat Pendeteksi Wajah
                            </div>

                            <!-- Button overlay -->
                            <button id="takeSelfieBtn" type="button" class="camera-button" disabled>
                                <i class="bi bi-camera-fill"></i>
                            </button>
                        </div>
                        <canvas id="canvas" style="display:none;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- style untuk ambil foto modal selfie -->
         <style>

            /* Modal styling */
            /* .modal-content {
                background: none;
                box-shadow: none;
            } */

            /* Video wrapper */
            .video-wrapper {
                position: relative;
                overflow: hidden;
                border-radius: 12px;
                /* aspect-ratio: 4/3; */
                background: black;
            }

            /* Alert styling untuk semua state */
            .detection-alert {
                position: absolute;
                top: 20px;
                left: 50%;
                transform: translateX(-50%) translateY(0);
                background: rgba(0, 0, 0, 0.7);
                color: white;
                padding: 10px 20px;
                border-radius: 20px;
                font-size: 14px;
                text-align: center;
                opacity: 0;
                pointer-events: none;
                transition: all 0.3s ease;
                z-index: 10;
            }

            .detection-alert.visible {
                opacity: 1;
            }

            /* Optional: Tambahkan efek fade untuk text */
            .detection-alert .alert-text {
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .detection-alert.visible .alert-text {
                opacity: 1;
            }
            /* Camera button styling */
            .camera-button {
                position: absolute;
                bottom: 30px;
                left: 50%;
                transform: translateX(-50%) scale(0.9);
                width: 70px;
                height: 70px;
                border-radius: 50%;
                background: rgba(218, 119, 86, 0.9);
                border: 3px solid white;
                color: white;
                font-size: 24px;
                cursor: pointer;
                transition: all 0.3s ease;
                z-index: 10;
                display: flex;          /* Tambahan untuk centering icon */
                align-items: center;    /* Tambahan untuk centering icon */
                justify-content: center;/* Tambahan untuk centering icon */
                opacity: 0;
            }

            .camera-button:disabled {
                background: rgba(150, 150, 150, 0.9);
                cursor: not-allowed;
            }

            .camera-button.visible {
                opacity: 1;
                transform: translateX(-50%) scale(1);
            }

            .camera-button:not(:disabled):hover {
                transform: translateX(-50%) scale(1.1);
                background: rgb(218, 119, 86);
            }
            /* Video styling */
            #video {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transform: scaleX(-1);
            }

        /* Efek sweep */
        .sweep-effect {
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.2),
                transparent
            );
            pointer-events: none;
            opacity: 0; /* Mulai dengan opacity 0 */
            transition: opacity 0.3s ease;
        }

        .sweep-active {
            opacity: 1;
            animation: sweep 2s infinite;
        }

        /* Efek glow */
        .glow-effect {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.3s ease, box-shadow 0.3s ease;
        }

        /* Class untuk glow merah (wajah tidak terdeteksi) */
        .glow-error {
            box-shadow: 0 0 20px rgba(255, 0, 0, 0.5),
                        inset 0 0 20px rgba(255, 0, 0, 0.5);
            opacity: 1;
        }

        /* Class untuk glow hijau (wajah terdeteksi) */
        .glow-success {
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.5),
                        inset 0 0 20px rgba(0, 255, 0, 0.5);
            opacity: 1;
        }

        @keyframes sweep {
            0% {
                left: -100%;
            }
            50% {
                left: 100%;
            }
            100% {
                left: 100%;
            }
        }         
        </style>


    <!-- Modal Preview Selfie -->
    <div class="modal fade" id="previewModalSelfie" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Preview Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body position-relative">
                    <img id="previewImage" src="" width="100%" class="rounded-4" alt="Preview Foto Selfie" loading="lazy">
                </div>
                <div class="modal-footer btn-group justify-content-between" role="group">
                    <button type="button" class="btn btn-secondary" id="retakePhotoBtn">
                        Ambil Ulang
                    </button>
                    <button type="button" class="btn buttonAbsen" id="confirmPhotoBtn">
                        <span class="button-text">Gunakan Foto</span>
                        <div class="spinner-border spinner-border-sm d-none" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
        <!-- loader -->
        <script>
            document.getElementById('confirmPhotoBtn').addEventListener('click', function() {
                const buttonText = this.querySelector('.button-text');
                const spinner = this.querySelector('.spinner-border');
                
                buttonText.classList.add('d-none');
                spinner.classList.remove('d-none');
                this.classList.add('buttonAbsen'); // Gunakan classList untuk menambah class
                this.disabled = true; // Optional: disable button saat loading
            });
        </script>

        <!-- backdrop modal preview selfie -->
        <style>
        /* Backdrop manual untuk preview modal */
        #previewModalSelfie.show {
            background-color: rgba(0, 0, 0, 0.5) !important;
        }

        #previewModalSelfie {
            background-color: transparent;
            transition: background-color 0.3s ease;
        }

        /* Pastikan modal content tetap di atas backdrop */
        #previewModalSelfie .modal-dialog {
            position: relative;
            z-index: 1056;
        }
        </style>




            <div class="modal fade" id="kebijakanAbsen" tabindex="-1" data-bs-keyboard="true" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Absensi Selfie</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">                        
                            <p>Sebelum melanjutkan, pastikan Anda telah memahami ketentuan berikut :</p>
                                <div d-grid>
                                    <div class="container">
                                        <div class="row align-items-center">
                                            <div class="col justify-content-center text-center">
                                                <div class="bi-lightbulb-fill" style="font-size: 40px; color:#DA7756;"></div>
                                            </div>
                                            <div class="col-9">
                                            <p class="p-0 m-0"><strong>Pencahayaan Optimal</strong></p>
                                                <p style="font-size:14px;">Mohon pastikan pencahayaan ruangan memadai agar kami dapat melihat Anda dengan lebih jelas.</p>
                                            </div>
                                        </div>
                                        <div class="row align-items-center">
                                            <div class="col justify-content-center text-center">
                                                <div class="bi-eye-fill" style="font-size: 40px; color:#DA7756;"></div>
                                            </div>
                                            <div class="col-9">
                                            <p class="p-0 m-0"><strong>Perlihatkan Wajah Anda</strong></p>
                                                <p style="font-size:14px;">Mohon pastikan wajah Anda terlihat jelas di depan kamera.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                        <div class="modal-footer d-flex">
                            <button type="button" class="btn flex-fill" style="background-color: #DA7756; color:white;" id="absenselfiesekarang">
                                Foto Saya Sekarang
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                const modalKebijakanSelfie = document.getElementById('kebijakanAbsen');
                const modalSelfie = document.getElementById('selfieModal');

                // instance
                const instanceModalKebijakanSelfie = new bootstrap.Modal(modalKebijakanSelfie);
                const instanceModalSelfie = new bootstrap.Modal(modalSelfie);

                // event listener untuk tampil modal selfie
                document.getElementById('absenselfiesekarang').addEventListener('click', ()=>{
                    instanceModalKebijakanSelfie.hide();
                    instanceModalSelfie.show();
                })
            </script>






        <!-- Modal untuk Absensi selfie Berhasil -->
        <div class="modal fade text-black" id="successModalSelfie" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-body">
                                <div d-grid>
                                    <div class="container">
                                        <div class="row align-items-center">
                                            <div class="col justify-content-center text-center bi-check-circle-fill" style="font-size: 40px; color:#DA7756;">
                                            </div>
                                            <div class="col-9">
                                                <p class="p-0 m-0"><strong>Absensi Berhasil</strong></p>
                                                <p style="font-size:14px;">Foto Anda berhasil kami simpan di database, kehadiran Anda telah tercatat.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer btn-group justify-content-between" role="group">
                                <button type="button" id="okButton" class="btn buttonAbsen" data-bs-dismiss="modal">Ok</button>
                            </div>
            </div>
            </div>
        </div>
        </div>

        <!-- Modal untuk Absensi selfie gagal -->
        <div class="modal fade text-black" id="errorModalSelfie" tabindex="-1" aria-labelledby="errorModalSelfieLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="errorModalSelfieLabel">Absensi Selfie Gagal, silahkan gunakan metode lainya</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Absensi gagal, silahkan menggunakan metode absen lainya
            </div>
            <div class="modal-footer btn-group justify-content-between" role="group">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Ok</button>
            </div>
            </div>
        </div>

    </div>
</div>



<!-- rincian waktu -->
<div class="mt-3 me-3 ms-3 mb-0 rounded-4 p-2 z-3 main-container" style="background-color: white;position:relative ;padding-bottom:10px; z-index:2;animation-delay: 0.5s">
    <div class="pt-3 ps-3 pe-3 pb-3">
            <p style="font-size: 16px; padding: 0; margin: 0; font-weight: bold;">Jadwal Kehadiran</p>
            <p style="font-size: 12px;">Berikut adalah jadwal kehadiran Anda hari ini.</p>
            <div class="container">
                <div class="row text-start gap-2">
                    <div class="col pt-2 rounded-4 btn-waktu">
                        <div style="background-color:white; display:inline-block;" class="rounded-pill">
                            <img src="assets/ok.png" alt="" width="30px" style="display:block; padding:2px;" loading="lazy">
                        </div>
                        <p style="font-size:12px;">Kehadiran dibuka</p>
                        <h1 class="display-1" style="font-size:20px"><strong><?= $awal_absen ?></strong></h1>
                    </div>
                    <div class="col pt-2 rounded-4 btn-waktu">
                        <div style="background-color:white; display:inline-block;" class="rounded-pill">
                            <img src="assets/warning_red.png" alt="" width="30px" style="display:block; padding:2px;" loading="lazy">
                        </div>
                        <p style="font-size:12px;">Waktu Terlambat</p>
                        <h1 class="display-1" style="font-size:20px"><strong><?= $akhir_absen ?></strong></h1>
                    </div>
                    <div class="col pt-2 rounded-4 btn-waktu">
                        <div style="background-color:white; display:inline-block;" class="rounded-pill">
                            <img src="assets/pulang.png" alt="" width="30px" style="display:block; padding:2px;" loading="lazy">
                        </div>
                        <p style="font-size:12px;">Waktu Kepulangan</p>
                        <h1 class="display-1" style="font-size:20px"><strong><?= $akhir_kerja?></strong></h1>
                    </div>
                </div>
            </div>
            <!-- style efek klik button -->
             <style>
                .btn-waktu {
                    background-color: #EBDBBC;
                    transition: background-color 0.3s ease;
                }
                .btn-waktu:hover{
                    background-color: #DA7756;
                    color: white;
                }
             </style>
    </div>
</div>


<!-- CSS untuk kalender -->
<style>
    .attendance-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 3px;
        margin: 15px auto;
        width: 100%;
    }
    
    .attendance-box {
        aspect-ratio: 1;
        border: 1px solid #ddd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        cursor: pointer;
        transition: transform 0.2s;
        background-color: #fff;
        min-height: 30px;
        padding: 2px;
        border-radius: 10px;
    }
    
    .attendance-box:hover {
        transform: scale(1.1);
    }
    
    .on-time {
        background-color: #DA7756;
        color: white;
    }
    
    .late {
        background-color: #dc3545;
        color: white;
    }
    
    .empty-box {
        visibility: hidden;
    }
    
    #calendarContainer {
        transition: opacity 0.2s ease-in-out;
    }
    
    .btn-outline-secondary {
        transition: all 0.2s ease-in-out;
    }
    
    .btn-outline-secondary:hover {
        transform: scale(1.1);
    }
</style>

<!-- HTML untuk kalender -->
<div class="mt-3 me-3 ms-3 mb-0 rounded-4 p-2 z-3 main-container" style="background-color: white;position:relative;padding-bottom:10px; z-index:2;animation-delay: 0.6s">
    <div class="pt-3 ps-3 pe-3 pb-3" id="calendarContainer">
        <!-- navigasi kalender -->
        <div class="month-navigator d-flex justify-content-between align-items-center">
            <span class="btn btn-sm bi bi-arrow-left" style="background-color:rgb(238, 236, 226); border-radius:10px;" id="prevMonth"></span>
            <h2 class="mb-0" id="monthYear" style="font-size: 16px;"></h2>
            <span class="btn btn-sm bi bi-arrow-right" style="background-color:rgb(238, 236, 226); border-radius:10px;" id="nextMonth"></span>
        </div>
        <!-- grid kalender -->
        <div class="attendance-grid" id="calendarGrid"></div>
    </div>
    
    <!-- lagenda kalender -->
    <div class="text-center mb-2">
        <div class="d-inline-block me-3">
            <div class="d-inline-block on-time" style="border-radius:10px; width: 10px; height: 10px;"></div>
            <small>Tepat Waktu</small>
        </div>
        <div class="d-inline-block me-3">
            <div class="d-inline-block late" style="border-radius:10px; width: 10px; height: 10px;"></div>
            <small>Terlambat</small>
        </div>
    </div>
</div>

<!-- JavaScript untuk kalender -->
<script>
let currentMonth = <?= date('n') ?>;
let currentYear = <?= date('Y') ?>;

async function changeMonth(month, year) {
    try {
        const container = document.getElementById('calendarContainer');
        container.style.opacity = '0';
        
        const response = await fetch(`attendance.php?ajax=1&month=${month}&year=${year}`);
        const data = await response.json();
        
        if (data.error) {
            console.error('Error:', data.error);
            return;
        }
        
        setTimeout(() => {
            // Update month and year display
            document.getElementById('monthYear').textContent = `${data.month} ${data.year}`;
            
            // Generate calendar grid
            const grid = document.getElementById('calendarGrid');
            grid.innerHTML = ''; // Clear existing content
            
            // Add day headers
            const days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
            days.forEach(day => {
                const dayBox = document.createElement('div');
                dayBox.className = 'text-center';
                dayBox.style.fontSize = '12px';
                dayBox.innerHTML = `<small>${day}</small>`;
                grid.appendChild(dayBox);
            });
            
            // Add empty boxes for first day offset
            for (let i = 0; i < data.firstDay; i++) {
                const emptyBox = document.createElement('div');
                emptyBox.className = 'attendance-box empty-box';
                grid.appendChild(emptyBox);
            }
            
            // Add day boxes with attendance status
            for (let day = 1; day <= data.daysInMonth; day++) {
                const status = data.attendance[day];
                const dayBox = document.createElement('div');
                dayBox.className = 'attendance-box';
                dayBox.textContent = day;
                
                if (status === 'tepat waktu') {
                    dayBox.classList.add('on-time');
                    dayBox.title = 'Tepat Waktu';
                } else if (status === 'terlambat') {
                    dayBox.classList.add('late');
                    dayBox.title = 'Terlambat';
                } else {
                    dayBox.title = 'Tidak Hadir';
                }
                
                grid.appendChild(dayBox);
            }
            
            container.style.opacity = '1';
            
            // Update current month and year
            currentMonth = month;
            currentYear = year;
        }, 200);
        
    } catch (error) {
        console.error('Error fetching calendar:', error);
    }
}

// Event listeners for navigation buttons
document.getElementById('prevMonth').addEventListener('click', () => {
    let newMonth = currentMonth - 1;
    let newYear = currentYear;
    if (newMonth < 1) {
        newMonth = 12;
        newYear--;
    }
    changeMonth(newMonth, newYear);
});

document.getElementById('nextMonth').addEventListener('click', () => {
    let newMonth = currentMonth + 1;
    let newYear = currentYear;
    if (newMonth > 12) {
        newMonth = 1;
        newYear++;
    }
    changeMonth(newMonth, newYear);
});

// Load initial calendar
document.addEventListener('DOMContentLoaded', () => {
    changeMonth(currentMonth, currentYear);
});
</script>


<!-- ringkasan  -->
    <div class="mt-3 me-3 ms-3 mb-3 rounded-4 p-2 z-0 main-container" style="background-color: white; position:relative; z-index:2;animation-delay: 0.7s">
        <div class=" pt-3 ps-3 pe-3 pb-1">
            <p style="font-size: 16px; padding: 0; margin: 0; font-weight: bold;">Sekilas Kehadiran</p>
            <p style="font-size: 12px;">Berikut adalah ringkasan kehadiran Anda.</p>
        </div>
        <div class="container text-center">
                <div class="row text-start gap-2  p-3 pt-0 pb-0 mb-0">
                    <div class="col btn-waktu pt-2 rounded-4">
                        <p style="font-size:12px;">Tepat Waktu</p>
                        <h1 class="display-1" style="font-size:50px"><strong><?= $jumlah_hadir ?></strong></h1>
                    </div>
                    <div class="col btn-waktu pt-2 rounded-4">
                        <p style="font-size:12px;">Terlambat</p>
                        <h1 class="display-1" style="font-size:50px"><strong><?= $jumlah_terlambat ?></strong></h1>
                    </div>
                </div>
            </div>

            <!-- button lainya -->
             <div>
                <a href="kehadiran_lengkap.php" class="text-decoration-none d-grid mt-3 pe-3 ps-3">
                    <button class="btn fw-100" style="border-radius:10px; margin-bottom:10px; background-color:rgb(235, 219, 188);">Lihat selengkapnya</button>
                </a>
             </div>
        </div>
    </div>

    <div class="text-center mt-5">
        <p style="font-size: 12px;">Tim IT SMAGA - 2024</p>
    </div>


<!-- ini script untuk lokasi -->
<script>
    // Tunggu sampai dokumen sepenuhnya dimuat
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Dokumen dimuat sepenuhnya');

        // Inisialisasi variabel untuk peta
        let map;
        let marker;

        // Handler untuk tombol absen lokasi
        document.getElementById('absenLokasiButton').addEventListener('click', function() {
            console.log('Tombol absen lokasi diklik');

            // Tampilkan loading state
            const button = this;
            const normalText = button.querySelector('.normal-text');
            const spinner = button.querySelector('.spinner');
            
            normalText.classList.add('d-none');
            spinner.classList.remove('d-none');
            button.disabled = true;

            // Cek apakah geolocation tersedia
            if ("geolocation" in navigator) {
                console.log('Geolocation tersedia');
                navigator.geolocation.getCurrentPosition(function(position) {
                    console.log('Geolocation berhasil didapatkan', position);

                    // Dapatkan koordinat
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;

                    console.log('Koordinat:', { latitude, longitude });

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
                    .then(response => {
                        console.log('Response status:', response.status);
                        console.log('Response headers:', response.headers);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response dari server:', data);

                        // Sembunyikan modal lokasi
                        const lokasiModal = bootstrap.Modal.getInstance(document.getElementById('lokasiModal'));
                        lokasiModal.hide();

                        if (data.status === "success") {
                            console.log('Absen lokasi berhasil');
                            // Tampilkan modal sukses
                            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                            successModal.show();
                        } else if (data.message === "Tidak ada jadwal untuk hari ini") {
                            console.log('Tidak ada jadwal untuk hari ini');
                            // Tampilkan modal tidak ada jadwal
                            const noScheduleModal = new bootstrap.Modal(document.getElementById('noScheduleModal'));
                            noScheduleModal.show();
                        } else {
                            console.log('Terjadi kesalahan saat absen lokasi');
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
                        console.error('Error saat mengirim data ke server:', error);
                        alert('Terjadi kesalahan saat melakukan absensi.');
                    })
                    .finally(() => {
                        // Kembalikan tombol ke keadaan normal
                        normalText.classList.remove('d-none');
                        spinner.classList.add('d-none');
                        button.disabled = false;
                        console.log('Proses absen lokasi selesai');
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
                    console.error('Error geolocation:', errorMessage);
                    alert(errorMessage);
                    
                    // Kembalikan tombol ke keadaan normal
                    normalText.classList.remove('d-none');
                    spinner.classList.add('d-none');
                    button.disabled = false;
                });
            } else {
                console.error('Browser tidak mendukung geolokasi');
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
                    console.log('Ukuran peta diperbarui');
                }, 200);
            }
        });
    });
</script>

<!-- ini script untuk absen barcode -->
 <script>
document.addEventListener('DOMContentLoaded', function() {
    const switchCameraButton = document.getElementById('switchCamera');
    const preview = document.getElementById('preview');
    let scanner;
    let cameras = [];
    let currentCameraIndex = 0;

    // Inisialisasi scanner
    function initScanner() {
        // Hapus scanner yang ada jika sudah ada
        if (scanner) {
            scanner.stop();
        }

        // Buat scanner baru
        scanner = new Instascan.Scanner({ 
            video: preview,
            scanPeriod: 5,
            refractoryPeriod: 5000
        });

        // Tambahkan listener untuk hasil scan
        scanner.addListener('scan', function (content) {
            console.log('Barcode scanned:', content);
            // Kirim barcode ke server untuk verifikasi
            fetch('absen_barcode.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ barcode: content })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Tampilkan modal sukses
                    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();
                } else {
                    // Tampilkan modal error
                    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                    errorModal.show();
                }
                
                // Tutup modal barcode
                const barcodeModal = bootstrap.Modal.getInstance(document.getElementById('barcodeModal'));
                barcodeModal.hide();
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });

        // Dapatkan daftar kamera
        Instascan.Camera.getCameras().then(function (availableCameras) {
            cameras = availableCameras;
            if (cameras.length > 0) {
                scanner.start(cameras[currentCameraIndex]);
            } else {
                console.error('No cameras found.');
                alert('Tidak ada kamera yang tersedia');
            }
        }).catch(function (e) {
            console.error(e);
        });
    }

    // Event listener untuk modal barcode
    document.getElementById('barcodeModal').addEventListener('shown.bs.modal', function () {
        initScanner();
    });

    // Event listener untuk modal barcode ditutup
    document.getElementById('barcodeModal').addEventListener('hidden.bs.modal', function () {
        if (scanner) {
            scanner.stop();
        }
    });

    // Tambahkan event listener untuk tombol ganti kamera
    switchCameraButton.addEventListener('click', function() {
        if (cameras.length > 1) {
            currentCameraIndex = (currentCameraIndex + 1) % cameras.length;
            scanner.stop();
            scanner.start(cameras[currentCameraIndex]);
        } else {
            alert('Hanya satu kamera yang tersedia');
        }
    });
});
  </script>

<!-- modal untuk absen selfie -->
  <script>
    let stream = null;
    let modelLoaded = false;
    let faceDetectionInterval = null;
    let videoTrack = null;

    // Fungsi untuk menutup kamera dengan lebih agresif
    function forceStopCamera() {
        return new Promise((resolve) => {
            try {
                // Hentikan video track secara spesifik
                if (videoTrack) {
                    videoTrack.stop();
                    videoTrack = null;
                }

                // Hentikan semua track dalam stream
                if (stream) {
                    stream.getTracks().forEach(track => {
                        track.stop();
                        track.enabled = false;
                    });
                    stream = null;
                }

                // Bersihkan video element
                const video = document.getElementById('video');
                if (video) {
                    video.srcObject = null;
                    video.load(); // Force reload video element
                }

                // Double check untuk memastikan semua media tracks sudah berhenti
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then(newStream => {
                        newStream.getTracks().forEach(track => {
                            track.stop();
                        });
                        resolve();
                    })
                    .catch(() => resolve());

            } catch (error) {
                console.error('Error saat menutup kamera:', error);
                resolve();
            }
        });
    }

    // 1. Pengecekan Event Listener
    document.getElementById('absenselfiesekarang').addEventListener('click', async function() {
        const selfieModal = new bootstrap.Modal(document.getElementById('selfieModal'));
        selfieModal.show();
        // Pastikan model dimuat sebelum memulai kamera
        if (!modelLoaded) {
            await loadFaceDetectionModels();
        }
        startCamera();
    });

    // Fungsi untuk memulai kamera
    async function startCamera() {
        try {
            // Hentikan stream yang sudah ada jika ada
            stopAll();

               // Tambahkan constraint yang lebih spesifik
            const constraints = {
                video: { 
                facingMode: 'user',
                width: { ideal: 640 },
                height: { ideal: 480 }
                }
            };

            // Dapatkan akses kamera depan
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user' }
            });

            const video = document.getElementById('video');
            video.srcObject = stream;

            // Tambahkan error handling untuk video
            video.onerror = (err) => {
                console.error('Error pada element video:', err);
            };
            
            video.onloadedmetadata = () => {
            console.log('Video metadata loaded');
            video.play()
                .then(() => {
                    console.log('Video mulai diputar');
                    startFaceDetection();
                })
                .catch(err => {
                    console.error('Error saat memulai video:', err);
                });
        };
    } catch (error) {
        console.error('Error detail saat mengakses kamera:', error);
        alert('Gagal mengakses kamera. Error: ' + error.message);
    }
}

    // Fungsi untuk memuat model face detection
    async function loadFaceDetectionModels() {
        try {
            // Hanya muat tiny face detector
            await faceapi.nets.tinyFaceDetector.loadFromUri('models/tiny_face_detector_model/');            modelLoaded = true;
            console.log('Face detection model (tiny) loaded successfully');
        } catch (error) {
            console.error('Error loading face detection model:', error);
            alert('Gagal memuat model deteksi wajah. Silakan refresh halaman.');
        }
    }
    function startFaceDetection() {
    const video = document.getElementById('video');
    const canvas = document.getElementById('faceCanvas');
    const takeSelfieBtn = document.getElementById('takeSelfieBtn');
    const detectionAlert = document.getElementById('detectionAlert');
    const glowEffect = document.querySelector('.glow-effect');
    const sweepEffect = document.querySelector('.sweep-effect');

    // Fungsi untuk mengupdate text alert dengan animasi
    function updateAlertText(text) {
        const textSpan = detectionAlert.querySelector('.alert-text');
        if (!textSpan) {
            // Jika span belum ada, buat baru
            const newSpan = document.createElement('span');
            newSpan.className = 'alert-text';
            detectionAlert.textContent = '';
            detectionAlert.appendChild(newSpan);
            newSpan.style.opacity = '0';
            
            setTimeout(() => {
                newSpan.textContent = text;
                newSpan.style.opacity = '1';
            }, 100);
        } else {
            // Jika span sudah ada
            textSpan.style.opacity = '0';
            setTimeout(() => {
                textSpan.textContent = text;
                textSpan.style.opacity = '1';
            }, 300);
        }
    }

    // Tampilkan alert awal setelah memastikan span sudah dibuat
    detectionAlert.classList.add('visible');
    setTimeout(() => {
        updateAlertText("Biarkan kami memuat Pendeteksi Wajah");
    }, 100);

    // Tampilkan button
    takeSelfieBtn.classList.add('visible');

    let lastDetectionState = null;

    faceDetectionInterval = setInterval(async () => {
        if (!modelLoaded) {
            detectionAlert.classList.add('visible');
            updateAlertText("Memuat pendeteksi wajah...");
            takeSelfieBtn.disabled = true;
            return;
        }

        try {
            const detections = await faceapi.detectSingleFace(
                video,
                new faceapi.TinyFaceDetectorOptions({
                    inputSize: 160,
                    scoreThreshold: 0.3
                })
            );

            if (detections && lastDetectionState !== true) {
                detectionAlert.classList.add('visible');
                updateAlertText("Wajah Terdeteksi");
                takeSelfieBtn.disabled = false;
                
                sweepEffect.classList.remove('sweep-active');
                glowEffect.classList.remove('glow-error');
                glowEffect.classList.add('glow-success');

                setTimeout(() => {
                    detectionAlert.classList.remove('visible');
                }, 2000);

                lastDetectionState = true;
            } else if (!detections && lastDetectionState !== false) {
                detectionAlert.classList.add('visible');
                updateAlertText("Arahkan Wajah Anda ke Kamera");
                takeSelfieBtn.disabled = true;
                
                sweepEffect.classList.add('sweep-active');
                glowEffect.classList.remove('glow-success');
                glowEffect.classList.add('glow-error');

                lastDetectionState = false;
            }
        } catch (error) {
            console.error('Error in face detection:', error);
        }
    }, 500);

    function cleanup() {
        if (faceDetectionInterval) {
            clearInterval(faceDetectionInterval);
        }
        detectionAlert.classList.remove('visible');
        takeSelfieBtn.classList.remove('visible');
        lastDetectionState = null;
    }

    document.getElementById('selfieModal').addEventListener('hidden.bs.modal', cleanup);
}

// Fungsi untuk membersihkan semua camera 
    async function stopAll() {
    console.log('Menghentikan semua proses...');
    
    // Hentikan interval deteksi wajah
    if (typeof faceDetectionInterval !== 'undefined' && faceDetectionInterval) {
        console.log('Menghentikan interval deteksi wajah');
        clearInterval(faceDetectionInterval);
        faceDetectionInterval = null;
    }
    
    // Panggil fungsi forceStopCamera
    await forceStopCamera();
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
        stopAll();

        // Tampilkan modal preview
        const previewModal = new bootstrap.Modal(document.getElementById('previewModalSelfie'));
        previewModal.show();
    }

    // Ganti dengan kode berikut
    function initializeModals() {
        const modalElement = document.getElementById('previewModalSelfie');
        if (!modalElement) return;

        const previewModal = new bootstrap.Modal(modalElement, {
            keyboard: true,
            backdrop: 'static' // Prevent closing by clicking outside
        });

        // Handle ESC key properly
        modalElement.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                cleanupModal();
            }
        });

        // Cleanup function
        function cleanupModal() {
            // Remove backdrop manually if needed
            const backdrops = document.getElementsByClassName('modal-backdrop');
            Array.from(backdrops).forEach(backdrop => {
                backdrop.remove();
            });
            
            // Reset body classes
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            document.body.style.removeProperty('overflow');
            
            // Reset modal state
            modalElement.style.display = 'none';
            modalElement.classList.remove('show');
            modalElement.removeAttribute('aria-modal');
            modalElement.setAttribute('aria-hidden', 'true');
        }

        // Handle modal hiding
        modalElement.addEventListener('hidden.bs.modal', function() {
            cleanupModal();
        });
    }



    async function uploadPhoto() {
    const previewModal = document.getElementById('previewModalSelfie');
    const buttonConfirm = document.getElementById('confirmPhotoBtn');
    const buttonText = buttonConfirm.querySelector('.button-text');
    const spinner = buttonConfirm.querySelector('.spinner-border');

    try {
        console.log('Mulai proses upload foto');
        buttonText.classList.add('d-none');
        spinner.classList.remove('d-none');
        buttonConfirm.disabled = true;

        const canvas = document.getElementById('canvas');
        const imageData = canvas.toDataURL('image/png').split(',')[1];
        const userId = document.body.dataset.userId;

        console.log('Data gambar dan user ID diambil:', { imageData, userId });

        // Ubah format pengiriman data menggunakan FormData
        const formData = new FormData();
        formData.append('foto', imageData);
        formData.append('id', userId);

        console.log('FormData siap untuk dikirim:', formData);

        const response = await fetch('absen_selfie.php', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        });

        console.log('Response diterima dari server:', response);

        // Tambahkan pengecekan status response
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const contentType = response.headers.get('content-type');
        console.log('Content-Type dari response:', contentType);
        if (!contentType || !contentType.includes('application/json')) {
            throw new TypeError("Oops, we haven't got JSON!");
        }

        const responseText = await response.text();
        console.log('Response text dari server:', responseText);

        const data = JSON.parse(responseText);
        console.log('Data JSON diterima dari server:', data);
        
        if (previewModal) {
            const modalInstance = bootstrap.Modal.getInstance(previewModal);
            if (modalInstance) {
                modalInstance.hide();
                
                await new Promise(resolve => {
                    previewModal.addEventListener('hidden.bs.modal', function handler() {
                        this.removeEventListener('hidden.bs.modal', handler);
                        resolve();
                    });
                });
            }
        }

        console.log('Server response:', data);
        // Show appropriate response modal
        if (data.message && data.message.includes("Tidak ada jadwal")) {
            const noScheduleModal = new bootstrap.Modal(document.getElementById('noScheduleModal'));
            noScheduleModal.show();
        } else if (data.success || data.status === "success") {
            const successModal = new bootstrap.Modal(document.getElementById('successModalSelfie'));
            successModal.show();
        } else {
            const errorModal = new bootstrap.Modal(document.getElementById('errorModalSelfie'));
            errorModal.show();
            console.error('Server response:', data); // Tambahkan log untuk debugging
        }

    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan: ' + error.message);
    } finally {
        buttonText.classList.remove('d-none');
        spinner.classList.add('d-none');
        buttonConfirm.disabled = false;
        console.log('Proses upload foto selesai');
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

    // Initialize modals when document is ready
    document.addEventListener('DOMContentLoaded', initializeModals);

    // Add proper cleanup for selfie modal
    document.getElementById('selfieModal')?.addEventListener('hidden.bs.modal', async function() {
        await stopAll(); // your existing camera cleanup function
        
        // Additional cleanup
        const backdrops = document.getElementsByClassName('modal-backdrop');
        Array.from(backdrops).forEach(backdrop => backdrop.remove());
        
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('padding-right');
        document.body.style.removeProperty('overflow');
    });
  </script>