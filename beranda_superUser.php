<?php
session_start();
include 'koneksi.php'; // Pastikan ini mengarah ke koneksi database Anda

if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}



// Mendapatkan user ID dari session
$user_id = $_SESSION['user_id'];
date_default_timezone_set('Asia/Jakarta');

// Mendapatkan tanggal hari ini
$today = date('Y-m-d');

// back sekilas kehadiran 

// Format tanggal untuk hari ini dalam format yang sama dengan database
$today = date('Y-m-d');

// Reset kehadiran di memori jika sekarang sudah lewat jam 00:00
if (date('H') == '00') {
    $_SESSION['absensi'] = [];
}

// Query untuk mendapatkan semua user yang sudah absen hari ini
$sql = "SELECT u.id, u.namaLengkap,
        CASE 
            WHEN d.user_id IS NOT NULL THEN 'Sudah Hadir'
            ELSE 'Belum Hadir'
        END as status
        FROM users u
        LEFT JOIN datang d ON u.id = d.user_id AND d.tanggal = '$today'
        WHERE u.id IS NOT NULL 
        GROUP BY u.id, u.namaLengkap
        ORDER BY u.namaLengkap ASC";

$result = $conn->query($sql);



if ($result) {
    // Reset session absensi
    $_SESSION['absensi'] = [];
    
    $absensiData = [];
    
    while ($row = $result->fetch_assoc()) {
        $absensiData[] = [
            'no' => count($absensiData) + 1,
            'namaLengkap' => htmlspecialchars($row['namaLengkap']),
            'status' => $row['status']
        ];
        
        // Simpan ke session jika diperlukan
        $_SESSION['absensi'][] = [
            'namaLengkap' => $row['namaLengkap'],
            'status' => $row['status']
        ];
    }

    // Debug: Tampilkan query SQL dan tanggal
    $debugSQL = htmlspecialchars($sql);
    $debugDate = htmlspecialchars($today);
    
    // Debug: Cek total users dan absensi
    $users_query = "SELECT COUNT(*) as total FROM users";
    $users_result = $conn->query($users_query);
    $users_count = $users_result->fetch_assoc()['total'];
    
    $attendance_query = "SELECT COUNT(*) as total FROM datang WHERE tanggal = '$today'";
    $attendance_result = $conn->query($attendance_query);
    $attendance_count = $attendance_result->fetch_assoc()['total'];

} else {
    echo "Error: " . htmlspecialchars($conn->error);
}

// Set timezone dan tanggal hari ini
date_default_timezone_set('Asia/Jakarta');
$today = date('Y-m-d');

// Query untuk total guru (excluding admin)
$total_guru_query = "SELECT COUNT(*) as total FROM users WHERE id != '1'";
$total_guru_result = $conn->query($total_guru_query);
$total_guru = $total_guru_result->fetch_assoc()['total'];

// Query untuk yang tepat waktu hari ini
$tepat_waktu_query = "SELECT COUNT(*) as count FROM datang 
                      WHERE tanggal = '$today' 
                      AND status = 'tepat waktu'";
$tepat_waktu_result = $conn->query($tepat_waktu_query);
$tepat_waktu = $tepat_waktu_result->fetch_assoc()['count'];

// Query untuk yang terlambat hari ini
$terlambat_query = "SELECT COUNT(*) as count FROM datang 
                    WHERE tanggal = '$today' 
                    AND status = 'terlambat'";
$terlambat_result = $conn->query($terlambat_query);
$terlambat = $terlambat_result->fetch_assoc()['count'];

// Hitung total yang hadir (tepat waktu + terlambat)
$total_hadir = $tepat_waktu + $terlambat;

// query untuk guru yang izin
$query_ijin = "SELECT COUNT(*) as total_ijin FROM ijin WHERE tanggal_mulai <= CURDATE() AND tanggal_selesai >= CURDATE()";
$result_ijin = mysqli_query($conn, $query_ijin);
$row_ijin = mysqli_fetch_assoc($result_ijin);
$total_ijin = $row_ijin['total_ijin'];

// $conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Halosmaga - Absen</title>
</head>
<style>
    body {
        font-family:merriweather ;
    }

    /* style buat primary color */
    .buttonAbsen{
            background-color: rgb(218, 119, 86);
            color: white;
            transform: background-color ease 0.3, color ease 0.3;
        }
        .buttonAbsen:hover{
            background-color: white;
            color: black;
        }
    
    /* style animasi container */
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

</style>
<body style="background-color: rgb(238, 238, 238);">
    <!-- informasi profil -->
    <div class="d-flex mt-3 me-4 ms-3 align-items-center gap-2">
        <div>
            <img src="assets/smagaedu.png" alt="" width="40px" class="bg-white rounded-circle p-1" loading="lazy">
        </div>
        <div class="flex-grow-1">
            <a href="logout.php" class="text-decoration-none text-black">
            <p style="font-size: 12px; margin: 0; padding: 0;">Selamat Datang,</p>
            </a>
            <p style="font-weight: bold; margin: 0; padding: 0; font-size:14px">Fauzi Nugroho</p>
        </div>
    </div>
    </div>

    <!-- informasi header
    <div class="alert alert-success alert-dismissible fade show m-3 rounded-4" role="alert">
    <div d-grid>
        <div class="container">
            <div class="row align-items-center">
                <div class="col justify-content-center text-center pe-3">
                    <img src="assets/update.png" alt="" width="40px">
                </div>
                <div class="col-9 p-0">
                    <p class="p-0 m-0"><strong>Fitur Baru Untuk Anda!</strong></p>
                    <p style="font-size:12px;">Saat ini, Anda dapat melihat kehadiran karyawan Anda secara sekilas di bagian Sekilas Kehadiran!. Papan tersebut kami tautkan di paling bawah.</p>
                </div>
            </div>
        </div>
    </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div> -->

            <!-- error header
            <div class="alert alert-success alert-dismissible fade show m-3 rounded-4" role="alert">
    <div d-grid>
        <div class="container">
            <div class="row align-items-center">
                <div class="col justify-content-center text-center pe-3">
                    <img src="assets/maintenance.png" alt="" width="40px">
                </div>
                <div class="col-9 p-0">
                    <p class="p-0 m-0"><strong>Maintenance Mode Aktif</strong></p>
                    <p style="font-size:12px;">Anda mungkin akan melihat beberapa peringatan error, silahkan untuk absensi seperti biasa.</p>
                </div>
            </div>
        </div>
    </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
 -->

    
    <!-- absen -->
    <div class="mt-3 me-3 ms-3 p-2 pb-4 rounded-4 main-container" style="background-color: white; animation-delay: 0.2s;">
        <div style="" class=" pt-3 ps-3 pe-3 pb-1">
            <h5 class="display-6 mb-1 fw-semibold" style="font-size: 20px;">Ikhtisar Hari Ini</h5>
            <p class="mb-1 p-0" style="font-size: 12px;">Berikut adalah laporan hari ini mengenai status karyawan Anda.</p>
            <div class="row d-flex justify-content-center align-items-center">
            </div>
        </div>

        <div class="container mt-1">
        <!-- Baris pertama -->
        <div class="d-flex flex-wrap">
            <div class="col-6 p-1">
                <!-- Isi konten di sini -->
                <div class="p-3 pb-1 rounded-4 text-start" style="background-color:rgb(235, 219, 188); cursor:pointer;" data-bs-toggle="modal" data-bs-target="#modalTepatWaktu">
                    <p class="p-0 m-0" style="font-size: 12px;">Tepat Waktu</p>
                    <div>
                        <p class="fw-bold p-0 m-0" style="font-size: 40px;"><?php echo $tepat_waktu; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-6 p-1">
                <!-- Isi konten di sini -->
                <div class="p-3 pb-1 rounded-4 text-start" style="background-color:rgb(235, 219, 188); cursor:pointer;" data-bs-toggle="modal" data-bs-target="#modalTerlambat">
                    <p class="p-0 m-0" style="font-size: 12px;">Terlambat</p>
                    <div>
                        <p class="fw-bold p-0 m-0" style="font-size: 40px;"><?php echo $terlambat; ?></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Baris kedua -->
        <div class="d-flex flex-wrap">
            <div class="col-6 p-1">
                <!-- Isi konten di sini -->
                <div class="p-3 pb-1 rounded-4 text-start" style="background-color:rgb(235, 219, 188); cursor: pointer;" data-bs-toggle="modal" data-bs-target="#modalTelahHadir">
                    <p class="p-0 m-0" style="font-size: 12px;">Telah Hadir</p>
                    <div>
                        <p class="fw-bold p-0 m-0" style="font-size: 40px;"><?php echo $total_hadir; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-6 p-1">
                <!-- Isi konten di sini --> 
                <div class="p-3 pb-1 rounded-4 text-start" style="background-color:rgb(235, 219, 188);; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#modalSemuaGuru">
                    <p class="p-0 m-0" style="font-size: 12px;">Total Guru</p>
                    <div>
                        <p class="fw-bold p-0 m-0" style="font-size: 40px;"><?php echo $total_guru; ?></p>
                    </div>
                </div>
            </div>
            <div class="col p-1">
                <div class="p-3 pb-1 rounded-4 text-start flex-fill" style="background-color:rgb(235, 219, 188);; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#modalIjin">
                        <p class="p-0 m-0" style="font-size: 12px;">Total Izin</p>
                        <div>
                            <p class="fw-bold p-0 m-0" style="font-size: 40px;" id="total-ijin"><?php echo $total_ijin; ?></p>
                        </div>
                    </div>
            </div>
        </div>
    </div>
    </div>

    <!-- dibawah ini modal setiap ikhtisar -->
<!-- Modal untuk ikhtisar izin -->
<div class="modal fade" id="modalIjin" tabindex="-1" aria-labelledby="modalIjinLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalIjinLabel">Daftar Perizinan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                    <?php
                    // Buat koneksi database baru khusus untuk modal
                    include 'koneksi.php';
                    
                    // Ambil tanggal hari ini
                    $today = date('Y-m-d');
                    
                    // Query untuk mendapatkan daftar izin yang aktif hari ini
                    $query_modal = "SELECT i.*, u.namaLengkap 
                                  FROM ijin i
                                  INNER JOIN users u ON i.user_id = u.id 
                                  WHERE ? BETWEEN i.tanggal_mulai AND i.tanggal_selesai
                                  ORDER BY i.tanggal_mulai DESC";
                    
                    $stmt = $conn->prepare($query_modal);
                    $stmt->bind_param("s", $today);
                    $stmt->execute();
                    $result_modal = $stmt->get_result();
                    
                    if ($result_modal && $result_modal->num_rows > 0) {
                        while($row = $result_modal->fetch_assoc()) {
                            echo '<div class="list-group-item">';
                            echo '<div class="d-flex w-100 justify-content-between">';
                            echo '<h6 class="mb-1">' . htmlspecialchars($row['namaLengkap']) . '</h6>';
                            echo '<small class="text-muted">' . htmlspecialchars($row['perizinan']) . '</small>';
                            echo '</div>';
                            echo '<p class="mb-1" style="font-size: 12px;">Durasi: ' . 
                                 date('d/m/Y', strtotime($row['tanggal_mulai'])) . ' - ' . 
                                 date('d/m/Y', strtotime($row['tanggal_selesai'])) . '</p>';
                            echo '<small class="text-muted">' . htmlspecialchars($row['keterangan']) . '</small>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="text-center p-3">Tidak ada perizinan aktif hari ini</div>';
                    }
                    
                    // Tutup koneksi
                    $stmt->close();
                    $conn->close();
                    ?>
                </div>
            </div>
            <div class="modal-footer d-flex">
                <button type="button" class="btn buttonAbsen flex-fill" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

    <!-- Modal untuk ikhtisar tepat waktu -->
<div class="modal fade" id="modalTepatWaktu" tabindex="-1" aria-labelledby="modalTepatWaktuLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTepatWaktuLabel">Daftar Tepat Waktu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                <?php
                    // Buat koneksi database baru khusus untuk modal
                    include 'koneksi.php';
                    
                    // Query untuk mendapatkan daftar user tepat waktu dengan JOIN ke tabel users
                    $query_modal = "SELECT u.namaLengkap, d.waktu_absen 
                                  FROM datang d
                                  INNER JOIN users u ON d.user_id = u.id 
                                  WHERE d.tanggal = '$today' 
                                  AND d.status = 'tepat waktu'
                                  ORDER BY d.waktu_absen ASC";
                    
                    $result_modal = $conn->query($query_modal);
                    
                    if ($result_modal && $result_modal->num_rows > 0) {
                        while($row = $result_modal->fetch_assoc()) {
                            echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
                            echo '<p style="font-size: 12px;" class="p-0 m-0">' . htmlspecialchars($row['namaLengkap']) . '</p>';
                            echo '<span class="badge bg-success rounded-pill">' . date('H:i', strtotime($row['waktu_absen'])) . '</span>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="text-center p-3">Tidak ada karyawan yang tepat waktu hari ini</div>';
                        // Debug info
                        echo "<!-- Query: " . htmlspecialchars($query_modal) . " -->";
                        echo "<!-- Tanggal: " . $today . " -->";
                    }
                    
                    // Tutup koneksi
                    $conn->close();
                    ?>
                </div>
            </div>
            <div class="modal-footer d-flex">
                <button type="button" class="btn buttonAbsen flex-fill" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Terlambat -->
<div class="modal fade" id="modalTerlambat" tabindex="-1" aria-labelledby="modalTerlambatLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTerlambatLabel">Daftar Karyawan Terlambat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                    <?php
                    // Buat koneksi database baru khusus untuk modal
                    include 'koneksi.php';
                    
                    // Query untuk mendapatkan daftar user terlambat
                    $query_modal = "SELECT u.namaLengkap, d.waktu_absen 
                                  FROM datang d
                                  INNER JOIN users u ON d.user_id = u.id 
                                  WHERE d.tanggal = '$today' 
                                  AND d.status = 'terlambat'
                                  ORDER BY d.waktu_absen ASC";
                    
                    $result_modal = $conn->query($query_modal);
                    
                    if ($result_modal && $result_modal->num_rows > 0) {
                        while($row = $result_modal->fetch_assoc()) {
                            echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
                            echo '<p style="font-size: 12px;" class="p-0 m-0">' . htmlspecialchars($row['namaLengkap']) . '</p>';
                            echo '<span class="badge bg-danger rounded-pill">' . date('H:i', strtotime($row['waktu_absen'])) . '</span>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="text-center p-3">Tidak ada karyawan yang terlambat hari ini</div>';
                    }
                    
                    // Tutup koneksi
                    $conn->close();
                    ?>
                </div>
            </div>
            <div class="modal-footer d-flex">
                <button type="button" class="btn buttonAbsen flex-fill" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


        <!-- button lainya -->
        <div class="mt-3 me-3 ms-3 p-2 rounded-4 main-container" style="background-color: white; animation-delay: 0.3s;">
            <!-- button aplikasi lainya -->
            <div class="d-flex justify-content-around me-3 mt-3 mb-3 ms-3">
            <!-- laporan kehadiran -->
            <div class="text-center d-flex flex-column align-items-center" onclick="window.location.href='hadir_superUser.php'">
                <div style="width: 50px; height: 50px; background-color: rgb(235, 219, 188);" 
                    class="d-flex align-items-center justify-content-center rounded-4">
                    <i class="bi bi-check-circle" style="font-size: 25px; color: black;"></i>
                </div>
                <p style="margin: 0; font-size: 12px; margin-top: 8px; color: black;">Laporan <br> Kehadiran</p>
            </div>
            <!-- laporan terlambat -->
            <div class="text-center d-flex flex-column align-items-center" onclick="window.location.href='terlambat_superUser.php'">
                <div style="width: 50px; height: 50px; background-color: rgb(235, 219, 188);" 
                    class="d-flex align-items-center justify-content-center rounded-4">
                    <i class="bi bi-exclamation-circle" style="font-size: 25px; color: black;"></i>
                </div>
                <p style="margin: 0; font-size: 12px; margin-top: 8px; color: black;">Laporan <br> Terlambat</p>
                </a>
            </div>
            <!-- laporan pulang -->
            <div class="text-center d-flex flex-column align-items-center" onclick="window.location.href='laporanPulang_superUser.php'">
                <div style="width: 50px; height: 50px; background-color: rgb(235, 219, 188);" 
                    class="d-flex align-items-center justify-content-center rounded-4">
                    <i class="bi bi-house" style="font-size: 25px; color: black;"></i>
                </div>
                <p style="margin: 0; font-size: 12px; margin-top: 8px; color: black;">Laporan <br> Pulang</p>
                </a>
            </div>
        </div>

    </div>

  <!-- Modal Telah Hadir -->
<div class="modal fade" id="modalTelahHadir" tabindex="-1" aria-labelledby="modalTelahHadirLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTelahHadirLabel">Daftar Kehadiran Hari Ini</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                    <?php
                    // Buat koneksi database baru khusus untuk modal
                    include 'koneksi.php';
                    
                    // Query untuk mendapatkan semua data kehadiran
                    $query_modal = "SELECT u.namaLengkap, d.waktu_absen, d.status 
                                  FROM datang d
                                  INNER JOIN users u ON d.user_id = u.id 
                                  WHERE d.tanggal = '$today' 
                                  ORDER BY d.waktu_absen ASC";
                    
                    $result_modal = $conn->query($query_modal);
                    
                    if ($result_modal && $result_modal->num_rows > 0) {
                        while($row = $result_modal->fetch_assoc()) {
                            $badge_class = ($row['status'] == 'tepat waktu') ? 'bg-success' : 'bg-danger';
                            echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
                            echo '<p style="font-size: 12px;" class="p-0 m-0">' . htmlspecialchars($row['namaLengkap']) . '</p>';
                            echo '<div class="d-flex align-items-center gap-2">';
                            echo '<p class="text-muted m-0 p-0" style="font-size: 12px;">' . ucfirst($row['status']) . '</p>';
                            echo '<span class="badge ' . $badge_class . ' rounded-pill">' . date('H:i', strtotime($row['waktu_absen'])) . '</span>';
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="text-center p-3">Belum ada data kehadiran hari ini</div>';
                    }
                    
                    // Tutup koneksi
                    $conn->close();
                    ?>
                </div>
            </div>
            <div class="modal-footer d-flex">
                <button type="button" class="btn buttonAbsen flex-fill" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>  

<!-- Modal Semua Guru -->
<div class="modal fade" id="modalSemuaGuru" tabindex="-1" aria-labelledby="modalSemuaGuruLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSemuaGuruLabel">Daftar Kehadiran Semua Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                    <?php
                    // Buat koneksi database baru khusus untuk modal
                    include 'koneksi.php';
                    
                    // Query untuk mendapatkan semua guru dan status kehadiran mereka
                    $query_modal = "SELECT 
                                    u.namaLengkap,
                                    d.waktu_absen,
                                    CASE
                                        WHEN d.status IS NULL THEN 'Belum Hadir'
                                        ELSE d.status
                                    END as status
                                  FROM users u
                                  LEFT JOIN datang d ON u.id = d.user_id AND d.tanggal = '$today'
                                  WHERE u.id != 1  -- Mengabaikan admin
                                  ORDER BY 
                                    CASE 
                                        WHEN d.status = 'tepat waktu' THEN 1
                                        WHEN d.status = 'terlambat' THEN 2
                                        ELSE 3
                                    END,
                                    d.waktu_absen ASC,
                                    u.namaLengkap ASC";
                    
                    $result_modal = $conn->query($query_modal);
                    
                    if ($result_modal && $result_modal->num_rows > 0) {
                        while($row = $result_modal->fetch_assoc()) {
                            // Menentukan warna badge berdasarkan status
                            $badge_class = '';
                            $status_text = '';
                            switch($row['status']) {
                                case 'tepat waktu':
                                    $badge_class = 'bg-success';
                                    $status_text = 'Tepat Waktu';
                                    break;
                                case 'terlambat':
                                    $badge_class = 'bg-danger';
                                    $status_text = 'Terlambat';
                                    break;
                                default:
                                    $badge_class = 'bg-secondary';
                                    $status_text = 'Belum Hadir';
                                    break;
                            }
                            
                            echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
                            echo '<p style="font-size: 12px;" class="p-0 m-0">' . htmlspecialchars($row['namaLengkap']) . '</p style="font-size: 12px;" class="p-0 m-0">';
                            echo '<div class="d-flex align-items-center gap-2">';
                            if ($row['waktu_absen']) {
                                echo '<p class="text-muted m-0 p-0" style="font-size: 12px;">' . $status_text . '</p>';
                                echo '<span class="badge ' . $badge_class . ' rounded-pill">' . date('H:i', strtotime($row['waktu_absen'])) . '</span>';
                            } else {
                                echo '<span class="badge ' . $badge_class . ' rounded-pill">' . $status_text . '</span>';
                            }
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="text-center p-3">Tidak dapat memuat data</div>';
                    }
                    
                    // Tutup koneksi
                    $conn->close();
                    ?>
                </div>
            </div>
            <div class="modal-footer d-flex">
                <button type="button" class="btn buttonAbsen flex-fill" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>




            <!-- aplikasi lainya -->
            <div class="mt-3 me-3 ms-3 p-2 rounded-4 main-container" style="background-color: white; animation-delay: 0.4s;">
            <!-- button aplikasi lainya -->
            <div class="d-flex justify-content-around me-3 mt-3 mb-3 ms-3">
            <!-- unduh file absensi -->
            <div class="text-center d-flex flex-column align-items-center" id="downloadBtn">
                <div style="width: 50px; height: 50px; background-color: rgb(235, 219, 188);" 
                    class="d-flex align-items-center justify-content-center rounded-4">
                    <i class="bi bi-cloud-download" style="font-size: 25px; color: black;"></i>
                </div>
                <p style="margin: 0; font-size: 12px; margin-top: 8px; color: black;">Unduh <br> Absensi</p>
            </div>
            <!-- tambah karyawan -->
            <div class="text-center d-flex flex-column align-items-center" onclick="window.location.href='tambahGuru_superUser.php'">
                <div style="width: 50px; height: 50px; background-color: rgb(235, 219, 188);" 
                    class="d-flex align-items-center justify-content-center rounded-4">
                    <i class="bi bi-person-plus" style="font-size: 25px; color: black;"></i>
                </div>
                <p style="margin: 0; font-size: 12px; margin-top: 8px; color: black;">Tambah <br> SDM</p>
                </a>
            </div>
            <!-- keluar -->
            <div class="text-center d-flex flex-column align-items-center" onclick="window.location.href='logout.php'">
                <div style="width: 50px; height: 50px; background-color: rgb(235, 219, 188);" 
                    class="d-flex align-items-center justify-content-center rounded-4">
                    <i class="bi bi-door-closed" style="font-size: 25px; color: black;"></i>
                </div>
                <p style="margin: 0; font-size: 12px; margin-top: 8px; color: black;">Keluar <br> Absen</p>
                </a>
            </div>
        </div>

    </div>



        <!-- segarkan -->
        <div class="mt-3 me-3 ms-3 mb-0 rounded-4 p-2 main-container" style="background-color: white; animation-delay: 0.5s;">
            <div class="pt-3 ps-3 pe-3 pb-1">
                <a href="logout_back.php" class="text-decoration-none">
                    <p style="font-size: 16px; padding: 0; margin: 0; font-weight: bold; color:black;">Segarkan Absensi</p>
                </a>
                <p style="font-size: 12px;">Segarkan absensi untuk mendapatkan data terbaru dari karyawan Anda</p>
                <div class="d-grid gap-2">
                    <button id="refreshButton" class="btn buttonAbsen  btn-block">Segarkan Sekarang</button>
                </div>
            </div>
            <script>
                document.getElementById('refreshButton').addEventListener('click', function() {
                    location.reload(); // Refresh halaman
                });
            </script>
        </div>



    <!-- modal untuk konfirmasi unduhan -->
            <!-- Modal untuk konfirmasi unduhan -->
                <div class="modal fade text-black" id="confirmModalDownload" tabindex="-1" aria-labelledby="modalSuksesLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered text-start">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="modalSuksesLabel">Konfirmasi Unduhan</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                            <div d-grid>
                                    <div class="container">
                                        <div class="row align-items-center">
                                            <div class="col justify-content-center text-center">
                                                <img src="assets/database.png" alt="" width="40px">
                                            </div>
                                            <div class="col-9">
                                            <p class="p-0 m-0"><strong>Unduh Database?</strong></p>
                                                <p style="font-size:14px;">Yakin untuk mengunduh database? Format file unduhan akan berbentuk CSV (Excel)</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer btn-group justify-content-between" role="group">
                                <button type="button" class="btn btn-outline-success" data-bs-dismiss="modal">Batal</button>
                                <button type="button" class="btn btn-success" data-bs-dismiss="modal" id="confirmUnduh">Unduh</button>
                            </div>
                        </div>
                    </div>
                </div>


    
<!-- Grafik Kehadiran sebulan -->
<div class="mt-3 me-3 ms-3 p-2 rounded-4 main-container" style="background-color: white; animation-delay: 0.6s;">
    <div class="pt-3 ps-3 pe-3 pb-1">
        <h5 class="display-6 mb-1 fw-semibold" style="font-size: 20px;">Grafik Kehadiran Harian</h5>
        <p class="mb-2" style="font-size: 12px;">Statistik seluruh kehadiran berdasarkan periode maksimal satu bulan</p>
        <div class="mt-4 mb-3">
                <select class="form-select form-select-sm border rounded" id="periodFilter" style="background-color:white;">
                    <option value="7">7 Hari Terakhir</option>
                    <option value="14">14 Hari Terakhir</option>
                    <option value="30">30 Hari Terakhir</option>
                </select>
        </div>
        <!-- Loading spinner -->
        <div id="chartLoading" class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <!-- Error message -->
        <div id="chartError" class="alert alert-danger d-none" role="alert">
            Terjadi kesalahan saat memuat data
        </div>
        <!-- Chart canvas -->
        <div class="chart-container" style="position: relative; height:300px; width:100%">
            <canvas id="attendanceChart"></canvas>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const loadingElement = document.getElementById('chartLoading');
    const errorElement = document.getElementById('chartError');
    const periodFilter = document.getElementById('periodFilter');
    let attendanceChart = null;

    // Function to fetch and update chart data
    async function updateChart(period) {
        try {
            loadingElement.classList.remove('d-none');
            errorElement.classList.add('d-none');

            const response = await fetch(`get_weekly_attendance.php?period=${period}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const jsonData = await response.json();

            if (!jsonData.success) {
                throw new Error('Failed to load data');
            }

            const data = jsonData.data;

            // Destroy existing chart if it exists
            if (attendanceChart) {
                attendanceChart.destroy();
            }

            // Create new chart
            attendanceChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(item => item.date),
                    datasets: [
                        {
                            label: 'Tepat Waktu',
                            data: data.map(item => item.tepatWaktu),
                            borderColor: 'rgb(40, 167, 69)',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Terlambat',
                            data: data.map(item => item.terlambat),
                            borderColor: 'rgb(220, 53, 69)',
                            backgroundColor: 'rgba(220, 53, 69, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Tidak Hadir',
                            data: data.map(item => item.tidakHadir),
                            borderColor: 'rgb(52, 58, 64)',
                            backgroundColor: 'rgba(52, 58, 64, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += context.parsed.y + ' orang';
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                callback: function(value) {
                                    return value + ' orang';
                                }
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });

            loadingElement.classList.add('d-none');
        } catch (error) {
            console.error('Error:', error);
            loadingElement.classList.add('d-none');
            errorElement.classList.remove('d-none');
            errorElement.textContent = 'Terjadi kesalahan saat memuat data: ' + error.message;
        }
    }

    // Event listener for period change
    periodFilter.addEventListener('change', function() {
        updateChart(this.value);
    });

    // Initial chart update
    updateChart(periodFilter.value);

    // Update chart every 5 minutes
    setInterval(() => updateChart(periodFilter.value), 5 * 60 * 1000);
});
</script>

<!-- Statistik Mingguan -->
<div class="mt-3 me-3 ms-3 p-2 rounded-4 main-container" style="background-color: white; animation-delay: 0.8s;">
    <div class="pt-3 ps-3 pe-3 pb-1">
        <h5 class="display-6 mb-1 fw-semibold" style="font-size: 20px;">Statistik Kehadiran Bulanan</h5>
        <p class="mb-2" style="font-size: 12px;">Statistik seluruh kehadiran berdasarkan periode satu bulan</p>
        
        <!-- Month Navigation -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <button class="btn p-0" id="prevMonth">
                <i class="bi bi-chevron-left fs-4"></i>
            </button>
            <div class="text-center">
                <span class="fw-semibold" id="currentMonth" style="font-size: 14px;">Januari 2024</span>
            </div>
            <button class="btn p-0" id="nextMonth">
                <i class="bi bi-chevron-right fs-4"></i>
            </button>
        </div>

        <!-- Chart Canvas -->
        <div class="chart-container" style="position: relative; height:300px; width:100%">
            <canvas id="weeklyChart"></canvas>
        </div>

        <!-- Legend -->
        <div class="mt-3 d-flex justify-content-center gap-3">
            <div class="d-flex align-items-center">
                <div style="width: 12px; height: 12px; background: #28a745; margin-right: 5px;"></div>
                <span style="font-size: 12px;">Tepat Waktu</span>
            </div>
            <div class="d-flex align-items-center">
                <div style="width: 12px; height: 12px; background: #dc3545; margin-right: 5px;"></div>
                <span style="font-size: 12px;">Terlambat</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Loading weekly statistics...');
    
    const ctx = document.getElementById('weeklyChart').getContext('2d');
    let weeklyChart = null;
    
    // Current date tracking
    let currentDate = new Date();
    const monthNames = [
        "Januari", "Februari", "Maret", "April", "Mei", "Juni",
        "Juli", "Agustus", "September", "Oktober", "November", "Desember"
    ];

    // Function to update month display
    function updateMonthDisplay() {
        const monthLabel = document.getElementById('currentMonth');
        monthLabel.textContent = `${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;
    }

    // Button handlers
    document.getElementById('prevMonth').addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        updateMonthDisplay();
        updateData();
    });

    document.getElementById('nextMonth').addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        updateMonthDisplay();
        updateData();
    });

    // Fungsi untuk mengambil data
    async function fetchData() {
        try {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth() + 1;
            
            const url = `get_weekly_stats.php?year=${year}&month=${month}`;
            console.log('Fetching from:', url);

            const response = await fetch(url);
            const data = await response.json();
            
            if (data.success) {
                return data;
            } else {
                console.error('Error:', data.error);
                return null;
            }
        } catch (error) {
            console.error('Network error:', error);
            return null;
        }
    }

    // Fungsi untuk update chart
    function updateChart(data) {
        const config = {
            type: 'bar',
            data: {
                labels: data.weeks,
                datasets: [
                    {
                        label: 'Tepat Waktu',
                        data: data.onTime,
                        backgroundColor: '#28a745',
                        borderColor: '#28a745',
                        borderWidth: 1
                    },
                    {
                        label: 'Terlambat',
                        data: data.late,
                        backgroundColor: '#dc3545',
                        borderColor: '#dc3545',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.parsed.y} orang`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 5,
                            callback: function(value) {
                                return value + ' orang';
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                }
            }
        };

        if (weeklyChart) {
            weeklyChart.destroy();
        }
        weeklyChart = new Chart(ctx, config);
    }

    // Fungsi untuk update data
    async function updateData() {
        const data = await fetchData();
        if (data) {
            updateChart(data);
        }
    }

    // Initialize
    updateMonthDisplay();
    updateData();
});
</script>

<!-- Pola Kehadiran -->
<div class="mt-3 me-3 ms-3 p-2 rounded-4 main-container" style="background-color: white; animation-delay: 0.8s;">
    <div class="pt-3 ps-3 pe-3 pb-1">
        <h5 class="display-6 mb-1 fw-semibold" style="font-size: 20px;">Pola Jam Kedatangan</h5>
        <p class="mb-2" style="font-size: 12px;">Statistik tren kehadiran berdasarkan periode waktu</p>
        
        <!-- Month Navigation -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <button class="btn p-0" id="prevMonthPola">
                <i class="bi bi-chevron-left fs-4"></i>
            </button>
            <div class="text-center">
                <span class="fw-semibold" id="currentMonthPola" style="font-size: 14px;">Januari 2024</span>
            </div>
            <button class="btn p-0" id="nextMonthPola">
                <i class="bi bi-chevron-right fs-4"></i>
            </button>
        </div>

        <!-- Chart Canvas -->
        <div class="chart-container" style="position: relative; height:300px; width:100%">
            <canvas id="attendancePatternChart"></canvas>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Loading attendance chart...');
    
    const ctxPola = document.getElementById('attendancePatternChart').getContext('2d');
    let myChart = null;
    
    // Current date tracking
    let currentDatePola = new Date();
    const monthNamesPola = [
        "Januari", "Februari", "Maret", "April", "Mei", "Juni",
        "Juli", "Agustus", "September", "Oktober", "November", "Desember"
    ];

    // Function to update month display
    function updateMonthDisplayPola() {
        const monthLabel = document.getElementById('currentMonthPola');
        monthLabel.textContent = `${monthNamesPola[currentDatePola.getMonth()]} ${currentDatePola.getFullYear()}`;
    }

    // Button handlers
    document.getElementById('prevMonthPola').addEventListener('click', () => {
        currentDatePola.setMonth(currentDatePola.getMonth() - 1);
        updateMonthDisplayPola();
        updateDataPola();
    });

    document.getElementById('nextMonthPola').addEventListener('click', () => {
        currentDatePola.setMonth(currentDatePola.getMonth() + 1);
        updateMonthDisplayPola();
        updateDataPola();
    });

    // Fungsi untuk mengambil data
    async function fetchData() {
        try {
            const year = currentDatePola.getFullYear();
            const month = currentDatePola.getMonth() + 1; // JavaScript months are 0-based
            
            const url = `get_attendance_data.php?year=${year}&month=${month}`;
            console.log('Fetching from:', url);

            const response = await fetch(url);
            const text = await response.text();
            
            try {
                const data = JSON.parse(text);
                console.log('Received data:', data);
                
                if (data.success) {
                    return {
                        labels: data.labels,
                        data: data.data
                    };
                } else {
                    console.error('Error in response:', data.error);
                    return null;
                }
            } catch (parseError) {
                console.error('Failed to parse response:', text);
                console.error('Parse error:', parseError);
                return null;
            }
        } catch (error) {
            console.error('Network error:', error);
            return null;
        }
    }

    // Fungsi untuk update chart
    function updateChart(labels, data) {
        const config = {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Kedatangan',
                    data: data,
                    borderColor: 'rgb(218, 119, 86)',
                    backgroundColor: 'rgba(218, 119, 86, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' orang';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 5,
                            callback: function(value) {
                                return value + ' orang';
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        };

        if (myChart) {
            myChart.destroy();
        }
        myChart = new Chart(ctxPola, config);
    }

    // Fungsi untuk update data
    async function updateDataPola() {
        console.log('Updating data for:', monthNamesPola[currentDatePola.getMonth()], currentDatePola.getFullYear());
        const chartData = await fetchData();
        if (chartData) {
            updateChart(chartData.labels, chartData.data);
        }
    }

    // Initialize
    updateMonthDisplayPola();
    updateDataPola();
});
</script>


    <!-- sekilas kehadiran sekarang  -->
    <div class="mt-3 me-3 ms-3 mb-5 rounded-4 p-2 main-container" style="background-color: white; animation-delay: 0.7s;">
        <div class=" pt-3 ps-3 pe-3 pb-1">
                <p class="mt-1" style="font-size: 16px; padding: 0; margin: 0; font-weight: bold;">Kehadiran Hari ini</p>
                <p style="font-size: 12px;">Berikut sekilas kehadiran karyawan Anda hari ini</p>
        </div>
        <!-- pemberitahuan kehadiran -->
        <?php foreach ($absensiData as $data): ?>
            <div class="container px-3 pb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div style="font-size:12px;"><?= $data['namaLengkap'] ?></div>
                    <?php if ($data['status'] === 'Sudah Hadir'): ?>
                        <span class="badge bg-success"><?= $data['status'] ?></span>
                    <?php else: ?>
                        <span class="badge bg-danger"><?= $data['status'] ?></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>    
    </div>


    <div class="text-center mt-5">
        <p style="font-size: 12px;">Tata Usaha SMAGA Gatak - 2024</p>
    </div>

    <script>
    // deklarasi variabel untuk modal konfirmasi unduhan
    const btn = document.getElementById("downloadBtn");
    const modal = document.getElementById("confirmModalDownload");
    const unduh = document.getElementById("confirmUnduh");

    // button diklik muncul modal 
    btn.onclick = function () {
        // Tampilkan modal menggunakan Bootstrap
        var modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();  
    };

    // Aksi untuk tombol konfirmasi unduh di modal
    unduh.onclick = function () {
        window.location.href = 'unduh.php'; // Ganti dengan URL unduhan Anda
    };
</script>
</body>
</html>