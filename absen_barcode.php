<?php
session_start();
require 'koneksi.php'; // Pastikan koneksi database Anda


// Tampilkan semua error untuk debugging
error_reporting(E_ALL);
header('Content-Type: application/json');

// Cek apakah user sudah login
// if (!isset($_SESSION['user_id'])) {
//     header("Location: index.php");
//     exit;
// }

// Ubah konversi hari ke bahasa Indonesia
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

// Ambil hari dalam bahasa Indonesia
$hari_ini = getHariIndonesia();
$debug_log['hari'] = $hari_ini;

 
// ambil jam datang dari database
$id_user = $_SESSION['user_id'];
$query_jam = "SELECT {$hari_ini}_datang as jam_datang, {$hari_ini}_pulang as jam_pulang FROM users WHERE id = ?";
$stmt_jam = $conn->prepare($query_jam);
if (!$stmt_jam) {
    echo json_encode([
        "status" => "error", 
        "message" => $conn->error,
        "debug" => $debug_log
    ]);
    exit;
}
$stmt_jam->bind_param("i", $id_user);
$stmt_jam->execute();
$result = $stmt_jam->get_result();

if($result->num_rows > 0) {
    $row_jam = $result->fetch_assoc();
    $debug_log['jadwal'] = $row_jam;
    
    if ($row_jam["jam_datang"] === NULL || $row_jam["jam_pulang"] === NULL) {
        echo json_encode([
            "status" => "error", 
            "message" => "Tidak ada jadwal untuk hari ini",
            "debug" => $debug_log
        ]);
        exit;
    }
    $awal_absen = (new DateTime($row_jam["jam_datang"]))->modify('-1 hour')->format("H:i:s"); //set durasi kedatangan
    $akhir_absen = $row_jam["jam_datang"];
    $akhir_kerja = $row_jam["jam_pulang"];
} else {
    echo json_encode([
        "status" => "error", 
        "message" => "Data user tidak ditemukan.",
        "debug" => $debug_log
    ]);
    exit;
}




// Ambil data dari request
$data = json_decode(file_get_contents("php://input"), true);
$barcode = trim($data['barcode']);

// Konversi $waktu_absen dari format JavaScript ke format Unix timestamp
date_default_timezone_set('Asia/Jakarta');
$waktu_absen = date('H:i:s');
$tanggal = date('Y-m-d');


// Cek apakah waktu absen berada dalam rentang waktu yang diizinkan
if ($waktu_absen >= $awal_absen && $waktu_absen <= $akhir_absen) {
    // Absen dalam batas waktu yang tepat
    $status = 'tepat waktu';
} elseif ($waktu_absen > $akhir_absen && $waktu_absen <= $akhir_kerja) {
    // Absen terlambat
    $status = 'terlambat';
} else {
    // Jika di luar rentang waktu, set status ke 'absen di luar jam kerja'
    $status = 'pulang';
}


// generator acak tanggal
$tanggalHariIni = date('Y-m-d');

// Barcode yang valid
$validBarcode = "AAAAA";

// Cek barcode
if ($barcode === $validBarcode) {
    // Ambil user_id dari sesi atau informasi login
    $user_id = $_SESSION['user_id'];

    // Simpan absensi ke tabel datang
    $sql_absensi = "INSERT INTO datang (user_id, waktu_absen, tanggal, status, ip_address, metode_absen)
                    VALUES ('$user_id', '$waktu_absen', '$tanggal' ,'$status', '$_SERVER[REMOTE_ADDR]', 'barcode')";

    if ($conn->query($sql_absensi) === TRUE) {
        echo json_encode(array("status" => "success"));
    } else {
        echo json_encode(array("status" => "error", "message" => $conn->error));
    }
} else {
    echo json_encode(array("status" => "error", "message" => "Barcode tidak valid."));
}

$conn->close();
?>
