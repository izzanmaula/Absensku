<?php
// absen selfie
session_start();
require 'koneksi.php';

error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

// Function untuk logging
function logProcess($message) {
    error_log(date('Y-m-d H:i:s') . " - " . $message);
}

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logProcess('Invalid request method');
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Cek session
if (!isset($_SESSION['user_id'])) {
    logProcess('User not authenticated');
    echo json_encode(['success' => false, 'error' => 'User is not authenticated']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    logProcess("Processing absensi for user_id: $user_id");

    // Ambil hari ini dalam bahasa Indonesia
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

    $hari_ini = getHariIndonesia();
    logProcess("Hari ini: $hari_ini");

    // Ambil jadwal dari database
    $query_jadwal = "SELECT {$hari_ini}_datang as jam_datang, {$hari_ini}_pulang as jam_pulang 
                    FROM users WHERE id = ?";
    $stmt_jadwal = $conn->prepare($query_jadwal);
    if (!$stmt_jadwal) {
        throw new Exception('Failed to prepare jadwal statement');
    }

    $stmt_jadwal->bind_param("i", $user_id);
    $stmt_jadwal->execute();
    $result_jadwal = $stmt_jadwal->get_result();
    
    if ($result_jadwal->num_rows === 0) {
        throw new Exception('User tidak ditemukan');
    }

    $jadwal = $result_jadwal->fetch_assoc();
    logProcess("Jadwal user: " . json_encode($jadwal));

    // Cek apakah ada jadwal hari ini
    if ($jadwal['jam_datang'] === null || $jadwal['jam_pulang'] === null) {
        logProcess('Tidak ada jadwal hari ini');
        echo json_encode(['success' => false, 'message' => 'Tidak ada jadwal hari ini']);
        exit;
    }

    // Set waktu absen
    $awal_absen = (new DateTime($jadwal['jam_datang']))->modify('-1 hour')->format('H:i:s');
    $akhir_absen = $jadwal['jam_datang'];
    $jam_pulang = $jadwal['jam_pulang'];
    
    logProcess("Waktu absen: awal=$awal_absen, akhir=$akhir_absen, pulang=$jam_pulang");

    // Proses foto absen
    if (!isset($_POST['foto']) || !isset($_POST['id'])) {
        throw new Exception('Data foto tidak lengkap');
    }

    $imgData = base64_decode($_POST['foto']);
    if ($imgData === false) {
        throw new Exception('Invalid image data');
    }

    // Simpan foto
    $uploadDir = 'uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filePath = $uploadDir . $user_id . '_' . time() . '.png';
    if (!file_put_contents($filePath, $imgData)) {
        throw new Exception('Gagal menyimpan foto');
    }
    logProcess("Foto disimpan di: $filePath");

    // Tentukan status absen
    date_default_timezone_set('Asia/Jakarta');
    $jam_absen = date('H:i:s');
    $tanggal = date('Y-m-d');

    // Cek apakah waktu absen berada dalam rentang waktu yang diizinkan
    if ($jam_absen >= $awal_absen && $jam_absen <= $akhir_absen) {  // Menggunakan $jam_absen
        // Absen dalam batas waktu yang tepat
        $status = 'tepat waktu';
    } elseif ($jam_absen > $akhir_absen && $jam_absen <= $jam_pulang) { // Menggunakan $jam_absen dan $jam_pulang
        // Absen terlambat
        $status = 'terlambat';
    } else {
        // Jika di luar rentang waktu, set status ke 'absen di luar jam kerja'
        $status = 'pulang';
    }

    logProcess("Status absen: $status pada $jam_absen");

    // Simpan absensi
    $insert_absen = "INSERT INTO datang (user_id, waktu_absen, tanggal, status, ip_address, metode_absen, foto) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_absen = $conn->prepare($insert_absen);
    if (!$stmt_absen) {
        throw new Exception('Failed to prepare absen statement');
    }

    $ip_address = $_SERVER['REMOTE_ADDR'];
    $metode_absen = 'Selfie';
    $stmt_absen->bind_param("issssss", $user_id, $jam_absen, $tanggal, $status, $ip_address, $metode_absen, $filePath);
    
    if (!$stmt_absen->execute()) {
        throw new Exception('Gagal menyimpan absensi');
    }
    logProcess('Absensi berhasil disimpan');

    // Cek apakah ini absen pertama hari ini
    $check_today = "SELECT COUNT(*) as total FROM datang 
                   WHERE user_id = ? AND DATE(tanggal) = CURRENT_DATE";
    $stmt_check = $conn->prepare($check_today);
    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    $total_today = $stmt_check->get_result()->fetch_assoc()['total'];
    logProcess("Total absen hari ini: $total_today");

    // Proses poin berdasarkan status dan jumlah absen
    if ($total_today <= 1) { // Absen pertama hari ini
        if ($status === 'tepat waktu') {
            // Cek apakah user sudah ada di tabel poin_user
            $check_poin = "SELECT jumlah_poin FROM poin_user WHERE user_id = ?";
            $stmt_poin = $conn->prepare($check_poin);
            $stmt_poin->bind_param("i", $user_id);
            $stmt_poin->execute();
            $result_poin = $stmt_poin->get_result();

            if ($result_poin->num_rows === 0) {
                // User belum ada, buat record baru dengan 1 poin
                $insert_poin = "INSERT INTO poin_user (user_id, jumlah_poin) VALUES (?, 1)";
                $stmt_insert = $conn->prepare($insert_poin);
                $stmt_insert->bind_param("i", $user_id);
                $stmt_insert->execute();
                logProcess("Created new poin_user record with 1 point");
            } else {
                // User sudah ada, tambah 1 poin
                $update_poin = "UPDATE poin_user SET jumlah_poin = jumlah_poin + 1 WHERE user_id = ?";
                $stmt_update = $conn->prepare($update_poin);
                $stmt_update->bind_param("i", $user_id);
                $stmt_update->execute();
                logProcess("Added 1 point to existing user");
            }
        } else {
            // Terlambat, reset poin
            $reset_poin = "UPDATE poin_user SET jumlah_poin = 0 WHERE user_id = ?";
            $stmt_reset = $conn->prepare($reset_poin);
            $stmt_reset->bind_param("i", $user_id);
            $stmt_reset->execute();
            logProcess("Reset points due to late attendance");
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Absensi berhasil',
        'data' => [
            'status' => $status,
            'waktu' => $jam_absen,
            'tanggal' => $tanggal
        ]
    ]);

} catch (Exception $e) {
    logProcess('Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>