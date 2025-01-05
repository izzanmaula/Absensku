<?php
require 'config.php';
require 'koneksi.php';

// Cek session
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data streak user
$query = "SELECT current_streak, max_streak FROM poin_user WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Set nilai default untuk streak
$current_streak = 0;
$max_streak = 0;

// Cek hasil query streak
if ($result && $result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
    $current_streak = $user_data['current_streak'] ?? 0;
    $max_streak = $user_data['max_streak'] ?? 0;
}

// Ambil data pencapaian
$query_pencapaian = "SELECT * FROM pencapaian ORDER BY strike ASC";
$result_pencapaian = $conn->query($query_pencapaian);
$pencapaian_list = [];

// Cek hasil query pencapaian
if ($result_pencapaian && $result_pencapaian->num_rows > 0) {
    $pencapaian_list = $result_pencapaian->fetch_all(MYSQLI_ASSOC);
}

// Inisialisasi variabel
$current_badge = null;
$next_badge = null;
$remaining_strike = 0;

// Proses data pencapaian jika ada
if (!empty($pencapaian_list)) {
    // Cari badge saat ini dan badge selanjutnya
    foreach ($pencapaian_list as $pencapaian) {
        // Jika current_streak = 0, belum memiliki badge apapun
        if ($current_streak == 0) {
            $current_badge = null;  
            $next_badge = reset($pencapaian_list); // Ambil badge pertama (Pemula)
            $remaining_strike = $next_badge['strike']; // Strike yang dibutuhkan untuk badge pertama
            break;
        } 
        // Jika current_streak = 1, sedang mengejar badge pertama
        else if ($current_streak == 1) {
            $current_badge = null;
            $next_badge = reset($pencapaian_list); // Target masih badge Pemula
            $remaining_strike = $next_badge['strike'] - $current_streak;
            break;
        }
        // Logika untuk current_streak > 1 tetap sama
        else if ($current_streak >= $pencapaian['strike']) {
            $current_badge = $pencapaian;
        } else {
            $next_badge = $pencapaian;
            $remaining_strike = $pencapaian['strike'] - $current_streak;
            break;
        }
    }
    
    // Jika sudah mencapai pencapaian terakhir
    if (!$next_badge && $current_badge) {
        $next_badge = end($pencapaian_list);
        $remaining_strike = $next_badge['strike'] - $current_streak;
    }
}

?>