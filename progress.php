<?php
require 'config.php';
require 'koneksi.php';

// Cek session
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Periksa dulu apakah user sudah ada di tabel poin_user
$check_user = "SELECT COUNT(*) as count FROM poin_user WHERE user_id = ?";
$stmt_check = $conn->prepare($check_user);
if (!$stmt_check) {
    error_log("Error preparing check statement: " . $conn->error);
    die("Database error");
}

$stmt_check->bind_param("i", $user_id);
$stmt_check->execute();
$user_exists = $stmt_check->get_result()->fetch_assoc()['count'] > 0;

// Buat record baru jika belum ada
if (!$user_exists) {
    $insert_query = "INSERT INTO poin_user (user_id, jumlah_poin) VALUES (?, 0)";
    $stmt_insert = $conn->prepare($insert_query);
    if (!$stmt_insert) {
        error_log("Error preparing insert statement: " . $conn->error);
        die("Database error");
    }
    $stmt_insert->bind_param("i", $user_id);
    $stmt_insert->execute();
}

// Ambil data kehadiran dan poin
$query = "SELECT p.jumlah_poin,
            COUNT(d.id) as total_kehadiran,
            COUNT(CASE WHEN d.status = 'tepat waktu' THEN 1 END) as total_tepat_waktu
          FROM poin_user p
          LEFT JOIN datang d ON p.user_id = d.user_id
          WHERE p.user_id = ?
          GROUP BY p.user_id, p.jumlah_poin";

$stmt = $conn->prepare($query);
if (!$stmt) {
    error_log("Error preparing main query: " . $conn->error);
    die("Database error");
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Set nilai default
$current_streak = 0;
$total_kehadiran = 0;
$total_tepat_waktu = 0;

// Ambil data jika ada
if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
    $current_streak = $data['jumlah_poin']; // Menggunakan jumlah_poin sebagai streak
    $total_kehadiran = $data['total_kehadiran'] ?? 0;
    $total_tepat_waktu = $data['total_tepat_waktu'] ?? 0;
}

// Ambil semua pencapaian
$query_pencapaian = "SELECT * FROM pencapaian ORDER BY strike ASC";
$result_pencapaian = $conn->query($query_pencapaian);
$pencapaian_list = [];
$current_badge = null;
$next_badge = null;

if ($result_pencapaian) {
    while ($row = $result_pencapaian->fetch_assoc()) {
        $pencapaian_list[] = $row;
        
        if ($current_streak == 0) {
            $current_badge = null;
            $next_badge = reset($pencapaian_list);
            $remaining_strike = $next_badge['strike'];
            break;
        } else if ($current_streak == 1) {
            $current_badge = null;
            $next_badge = reset($pencapaian_list);
            $remaining_strike = $next_badge['strike'] - $current_streak;
            break;
        } else if ($current_streak >= $row['strike']) {
            $current_badge = $row;
        } else if (!$next_badge) {
            $next_badge = $row;
            $remaining_strike = $row['strike'] - $current_streak;
        }
    }
}

// Jika sudah mencapai pencapaian terakhir
if (!$next_badge && $current_badge) {
    $next_badge = end($pencapaian_list);
    $remaining_strike = max(0, $next_badge['strike'] - $current_streak);
}

// Hitung persentase progress
$progress_percentage = 0;
if ($next_badge) {
    $previous_strike = $current_badge ? $current_badge['strike'] : 0;
    $target_strike = $next_badge['strike'];
    $progress_percentage = min(100, max(0, 
        (($current_streak - $previous_strike) / ($target_strike - $previous_strike)) * 100
    ));
}

// Debug log
error_log("Current streak (jumlah_poin): $current_streak");
error_log("Current badge: " . ($current_badge ? $current_badge['nama_pencapaian'] : 'None'));
error_log("Next badge: " . ($next_badge ? $next_badge['nama_pencapaian'] : 'None'));
error_log("Remaining strike: $remaining_strike");
error_log("Progress percentage: $progress_percentage");

// Return semua data yang dibutuhkan
return [
    'current_streak' => $current_streak,  // Ini adalah jumlah_poin
    'total_kehadiran' => $total_kehadiran,
    'total_tepat_waktu' => $total_tepat_waktu,
    'current_badge' => $current_badge,
    'next_badge' => $next_badge,
    'remaining_strike' => $remaining_strike,
    'progress_percentage' => $progress_percentage,
    'pencapaian_list' => $pencapaian_list
];
?>