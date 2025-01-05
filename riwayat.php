<?php
session_start();
include 'koneksi.php'; // Pastikan ini mengarah ke koneksi database Anda

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data riwayat
$riwayat = [];

// Ambil data izin terlambat
$query_tl = "SELECT 'Terlambat' as jenis, waktu_kedatangan, DATE_FORMAT(tanggal_izin, '%W, %d %M %Y') as tanggal, alasan_terlambat as alasan, NULL as tanggal_selesai FROM terlambat WHERE user_id = ?";
$stmt_tl = $conn->prepare($query_tl);
$stmt_tl->bind_param("i", $user_id);
$stmt_tl->execute();
$result_tl = $stmt_tl->get_result();

while ($row = $result_tl->fetch_assoc()) {
    $riwayat[] = $row;
}

// Ambil data izin sakit
$query_skt = "SELECT 'Sakit' as jenis, NULL as waktu_kedatangan, DATE_FORMAT(tanggal_mulai, '%W, %d %M %Y') as tanggal, alasan, DATE_FORMAT(tanggal_selesai, '%W, %d %M %Y') as tanggal_selesai FROM sakit WHERE user_id = ?";
$stmt_skt = $conn->prepare($query_skt);
$stmt_skt->bind_param("i", $user_id);
$stmt_skt->execute();
$result_skt = $stmt_skt->get_result();

while ($row = $result_skt->fetch_assoc()) {
    $riwayat[] = $row;
}

// Ambil data izin lainnya
$query_il = "SELECT izin_type as jenis, NULL as waktu_kedatangan, DATE_FORMAT(tanggal_mulai, '%W, %d %M %Y') as tanggal, alasan, DATE_FORMAT(tanggal_selesai, '%W, %d %M %Y') as tanggal_selesai FROM izin_lain WHERE user_id = ?";
$stmt_il = $conn->prepare($query_il);
$stmt_il->bind_param("i", $user_id);
$stmt_il->execute();
$result_il = $stmt_il->get_result();

while ($row = $result_il->fetch_assoc()) {
    $riwayat[] = $row;
}

// Tutup statement
$stmt_tl->close();
$stmt_skt->close();
$stmt_il->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Halosmaga - Riwayat Absen</title>
</head>
<body style="background-color: rgb(238, 238, 238);">
        <!-- Header Halaman -->
        <div class="d-flex mt-3 me-4 ms-3">
        <div class="row">
            <a href="beranda.php" class="col-1">
                <div>
                    <img src="assets/back.png" alt="Kembali" width="30px">
                </div>
            </a>
            <div class="col">
                <h4 style="font-weight: bold; margin: 0; padding: 0;">Riwayat Perizinan</h4>
            </div>
        </div>
    </div>

        <?php foreach ($riwayat as $record): ?>
            <div class="mt-3 me-3 ms-3 p-2 rounded-4" style="background-color: white;">
                <div class="pt-3 ps-3 pe-3 pb-1">
                    <div class="container">
                        <div class="row position-relative">
                            <div class="col text-center rounded-3" style="padding-top:25%; padding-left:5%; ;background-color: <?= $record['jenis'] === 'Terlambat' ? 'rgba(255, 145, 149, 0.28);' : ($record['jenis'] === 'Sakit' ? 'rgba(255, 255, 30, 0.28);' : 'rgba(186, 255, 206, 0.28);'); ?>;">
                                <?php 
                                // Menentukan ikon berdasarkan jenis
                                if ($record['jenis'] === 'Terlambat') {
                                    $icon = '<i class="bi bi-cup-hot-fill icon-status text-danger" style="width: 50px;"></i>';
                                } elseif ($record['jenis'] === 'Sakit') {
                                    $icon = '<i class="bi bi-virus2 icon-status text-warning" style="width: 50px;"></i>';
                                } else {
                                    $icon = '<i class="bi bi-bookmark-x-fill icon-status text-info" style="width: 50px;"></i>';
                                }
                                echo $icon; 
                                ?>
                            </div>
                            <div class="ms-2 col-8">
                                <p style="font-weight: bold; margin: 0; padding: 0; color: <?= $record['jenis'] === 'Terlambat' ? 'red' : ($record['jenis'] === 'Sakit' ? 'orange' : 'green'); ?>; font-size: 20px;">
                                    <?= $record['jenis'] ?>
                                </p>
                                <?php if ($record['jenis'] === 'Terlambat'): ?>
                                    <p style="font-size: 15px;">Anda absen pukul <?= $record['waktu_kedatangan'] ? date('H:i', strtotime($record['waktu_kedatangan'])) : ''; ?></p>
                                <?php endif; ?>
                                <p style="font-size: 15px;">Izin dari <?= $record['tanggal'] ?> sampai <?= $record['tanggal_selesai'] ? $record['tanggal_selesai'] : 'tidak ada'; ?></p>
                                <p style="font-size: 15px;">Alasan: <?= $record['alasan'] ?></p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>