<?php
session_start();
require 'koneksi.php';

error_reporting(E_ALL);
header('Content-Type: application/json');

// Cek apakah user sudah login
$user_id = $_SESSION['user_id'];
if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'User ID tidak ditemukan']);
    exit;
}

// Fungsi untuk mengecek IP dalam range
function isIPInRange($ip, $startIP, $endIP) {
    $ip = ip2long($ip);
    $startIP = ip2long($startIP);
    $endIP = ip2long($endIP);
    
    return ($ip >= $startIP && $ip <= $endIP);
}

// ambil jam datang dari database
$id_user = $_SESSION['user_id'];
$query_jam="SELECT jam_datang, jam_pulang FROM users WHERE id =?";
$stmt_jam = $conn->prepare($query_jam);
if (!$stmt_jam){
    die("error : " .$conn->error);
}
$stmt_jam->bind_param("i", $id_user);
$stmt_jam->execute();
$result = $stmt_jam->get_result();

if($result->num_rows > 0) {
    $row_jam = $result->fetch_assoc();
    $awal_absen = (new DateTime($row_jam["jam_datang"]))->modify('-1 hour')->format("H:i:s"); //set durasi kedatangan
    $akhir_absen = $row_jam["jam_datang"];
    $akhir_kerja = $row_jam["jam_pulang"];
} else {
    die("data user tidak di temukan, silahkan untuk login ulang.");
}


// Range IP yang diizinkan



$allowed_ranges = [
    ['start' => '117.20.48.0', 'end' => '117.20.48.255'],
    ['start' => '117.20.49.0', 'end' => '117.20.49.255']
];

// Dapatkan alamat IP pengguna
$user_ip = $_SERVER['REMOTE_ADDR'];

// Flag untuk validasi IP
$valid_ip = false;

// Cek IP dalam setiap range yang diizinkan
foreach ($allowed_ranges as $range) {
    if (isIPInRange($user_ip, $range['start'], $range['end'])) {
        $valid_ip = true;
        break;
    }
}

// Debug: tampilkan informasi IP
error_log("User IP: " . $user_ip);
error_log("IP Valid: " . ($valid_ip ? 'true' : 'false'));

// Jika IP tidak valid
if (!$valid_ip) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Anda tidak terkoneksi dengan WiFi sekolah',
        'your_ip' => $user_ip // Tambahkan ini untuk debugging
    ]);
    exit;
}

// Set timezone dan waktu
date_default_timezone_set('Asia/Jakarta');
$waktu_absen = date('H:i:s');
$tanggal = date('Y-m-d');


// Tentukan status absen
if ($waktu_absen >= $awal_absen && $waktu_absen <= $akhir_absen) {
    $status = 'tepat waktu';
} elseif ($waktu_absen > $akhir_absen && $waktu_absen <= $akhir_kerja) {
    $status = 'terlambat';
} else {
    $status = 'pulang';
}

// Metode absen
$metode_absen = 'wifi';

// Query database
$query = "INSERT INTO datang (user_id, waktu_absen, tanggal, status, ip_address, metode_absen) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);

if ($stmt === false) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Gagal menyiapkan statement: ' . $conn->error
    ]);
    exit;
}

$stmt->bind_param('issss', $user_id, $waktu_absen, $status, $user_ip, $metode_absen);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success', 
        'message' => 'Anda telah absen',
        'debug_info' => [
            'your_ip' => $user_ip,
            'status' => $status,
            'waktu' => $waktu_absen
        ]
    ]);
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Gagal menyimpan data absen: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>