<?php
session_start();
ob_start();
require 'koneksi.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

function logProcess($message) {
    error_log(date('Y-m-d H:i:s') . " - " . $message);
}

$debug_log = [];

// Ambil dan validasi data lokasi
$data_raw = file_get_contents("php://input");
$debug_log['raw_input'] = $data_raw;
logProcess('Raw input: ' . $data_raw);

$data = json_decode($data_raw, true);
$debug_log['decoded_data'] = $data;
logProcess('Decoded data: ' . print_r($data, true));

if (!$data || !isset($data['latitude'], $data['longitude'])) {
    ob_clean();
    echo json_encode([
        "status" => "error", 
        "message" => "Data lokasi tidak valid.",
        "debug" => $debug_log
    ]);
    exit;
}

// Validasi session
if (!isset($_SESSION['user_id'])) {
    ob_clean();
    echo json_encode([
        "status" => "error", 
        "message" => "User belum login.",
        "debug" => $debug_log
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$userLatitude = $data['latitude'];
$userLongitude = $data['longitude'];
$debug_log['user_coordinates'] = ['latitude' => $userLatitude, 'longitude' => $userLongitude];

// Ambil informasi jadwal
function getHariIndonesia() {
    $hari_inggris = strtolower(date('l'));
    $hari_indonesia = [
        'sunday' => 'minggu', 'monday' => 'senin', 'tuesday' => 'selasa',
        'wednesday' => 'rabu', 'thursday' => 'kamis', 'friday' => 'jumat',
        'saturday' => 'sabtu'
    ];
    return $hari_indonesia[$hari_inggris];
}

$hari_ini = getHariIndonesia();
$debug_log['hari'] = $hari_ini;

// Ambil jadwal hari ini
$query_jam = "SELECT {$hari_ini}_datang as jam_datang, {$hari_ini}_pulang as jam_pulang FROM users WHERE id = ?";
$stmt_jam = $conn->prepare($query_jam);
if (!$stmt_jam) {
    ob_clean();
    echo json_encode([
        "status" => "error", 
        "message" => $conn->error,
        "debug" => $debug_log
    ]);
    exit;
}

$stmt_jam->bind_param("i", $user_id);
$stmt_jam->execute();
$result = $stmt_jam->get_result();
$row_jam = $result->fetch_assoc();
$debug_log['jadwal'] = $row_jam;
logProcess("Jadwal user: " . json_encode($row_jam));

if (!$result->num_rows || $row_jam["jam_datang"] === NULL || $row_jam["jam_pulang"] === NULL) {
    ob_clean();
    echo json_encode([
        "status" => "error", 
        "message" => "Tidak ada jadwal untuk hari ini",
        "debug" => $debug_log
    ]);
    exit;
}

// Set waktu absen
$awal_absen = (new DateTime($row_jam["jam_datang"]))->modify('-1 hour')->format("H:i:s");
$akhir_absen = $row_jam["jam_datang"];
$akhir_kerja = $row_jam["jam_pulang"];

$debug_log['waktu_absen'] = [
    'awal' => $awal_absen,
    'akhir' => $akhir_absen,
    'pulang' => $akhir_kerja
];

// Koordinat dan range sekolah
$schoolLatitude = -7.593255;
$schoolLongitude = 110.731340;
$range = 0.0100;

$debug_log['school_coordinates'] = [
    'latitude' => $schoolLatitude,
    'longitude' => $schoolLongitude,
    'range' => $range
];

// Cek lokasi
function isWithinRange($userLat, $userLong, $schoolLat, $schoolLong, $range) {
    $lat_diff = abs($userLat - $schoolLat);
    $long_diff = abs($userLong - $schoolLong);
    return [
        'in_range' => ($lat_diff <= $range && $long_diff <= $range),
        'lat_diff' => $lat_diff,
        'long_diff' => $long_diff
    ];
}

$range_check = isWithinRange($userLatitude, $userLongitude, $schoolLatitude, $schoolLongitude, $range);
$debug_log['range_check'] = $range_check;

if ($range_check['in_range']) {
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
    logProcess("Status absen: $status pada $waktu_absen");

    // Cek total absen hari ini
    $check_today = "SELECT COUNT(*) as total FROM datang 
                   WHERE user_id = ? AND DATE(tanggal) = CURRENT_DATE";
    $stmt_check = $conn->prepare($check_today);
    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    $total_today = $stmt_check->get_result()->fetch_assoc()['total'];
    logProcess("Total absen hari ini: $total_today");

    // Simpan absensi
    $metode_absen = 'lokasi';
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $sql_absensi = "INSERT INTO datang (user_id, waktu_absen, tanggal, status, ip_address, metode_absen)
                    VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_absen = $conn->prepare($sql_absensi);
    $stmt_absen->bind_param("isssss", $user_id, $waktu_absen, $tanggal, $status, $ip_address, $metode_absen);

    if (!$stmt_absen->execute()) {
        ob_clean();
        echo json_encode([
            "status" => "error", 
            "message" => $stmt_absen->error,
            "debug" => $debug_log
        ]);
        exit;
    }

    logProcess('Absensi berhasil disimpan');

    // Proses poin hanya jika ini absen pertama
    if ($total_today <= 1) {
        // Cek/buat record di poin_user
        $check_poin = "SELECT * FROM poin_user WHERE user_id = ?";
        $stmt_poin = $conn->prepare($check_poin);
        $stmt_poin->bind_param("i", $user_id);
        $stmt_poin->execute();
        $result_poin = $stmt_poin->get_result();

        if ($status === 'tepat waktu') {
            if ($result_poin->num_rows === 0) {
                // Buat record baru dengan 1 poin
                $create_poin = "INSERT INTO poin_user (user_id, jumlah_poin) VALUES (?, 1)";
                $stmt_create = $conn->prepare($create_poin);
                $stmt_create->bind_param("i", $user_id);
                $stmt_create->execute();
                logProcess("Created new poin_user record with 1 point");
            } else {
                // Tambah 1 poin
                $update_poin = "UPDATE poin_user SET jumlah_poin = jumlah_poin + 1 WHERE user_id = ?";
                $stmt_update = $conn->prepare($update_poin);
                $stmt_update->bind_param("i", $user_id);
                $stmt_update->execute();
                logProcess("Added 1 point to existing user");
            }
        } else {
            // Reset poin jika terlambat
            if ($result_poin->num_rows === 0) {
                $create_poin = "INSERT INTO poin_user (user_id, jumlah_poin) VALUES (?, 0)";
                $stmt_create = $conn->prepare($create_poin);
                $stmt_create->bind_param("i", $user_id);
                $stmt_create->execute();
            } else {
                $reset_poin = "UPDATE poin_user SET jumlah_poin = 0 WHERE user_id = ?";
                $stmt_reset = $conn->prepare($reset_poin);
                $stmt_reset->bind_param("i", $user_id);
                $stmt_reset->execute();
            }
            logProcess("Reset points due to late attendance");
        }
    }

    ob_clean();
    echo json_encode([
        "status" => "success",
        "message" => "Absensi berhasil disimpan",
        "data" => [
            "status" => $status,
            "waktu" => $waktu_absen,
            "tanggal" => $tanggal
        ],
        "debug" => $debug_log
    ]);
} else {
    ob_clean();
    echo json_encode([
        "status" => "error",
        "message" => "Lokasi tidak sesuai dengan lokasi sekolah.",
        "current_location" => [
            "latitude" => $userLatitude, 
            "longitude" => $userLongitude
        ],
        "debug" => $debug_log
    ]);
}

$conn->close();
?>