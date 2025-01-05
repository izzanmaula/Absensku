<?php

session_start();
include "koneksi.php";

// jika belum login 
if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// timezone
date_default_timezone_set('Asia/Jakarta');

$limit = 15; // Jumlah baris per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Halaman saat ini
$offset = ($page - 1) * $limit; // Offset untuk query

$total_query = "SELECT COUNT(*) as total FROM sakit";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_rows = $total_row['total'];
$total_pages = ceil($total_rows / $limit); // Total halaman

$query_terlambat = "
    SELECT d.tanggal_mulai, d.tanggal_selesai, u.namaLengkap, d.alasan, d.created_at, d.surat_keterangan_dokter
    FROM sakit d 
    JOIN users u ON d.user_id = u.id 
    ORDER BY d.tanggal_mulai ASC
    LIMIT ? OFFSET ?
";

$stmt_terlambat = $conn->prepare($query_terlambat);
$stmt_terlambat->bind_param("ii", $limit, $offset);
$stmt_terlambat->execute();
$result_terlambat = $stmt_terlambat->get_result();

// close statement
$stmt_terlambat->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Tabel Laporan Sakit</title>
</head>
<style>
.circle {
    width: 100px;           /* Lebar bulatan */
    height: 30px;          /* Tinggi bulatan */
    background-color: #fff; /* Warna latar belakang */
    border-radius: 50px;
    display: flex;          /* Mengatur posisi item dalam div */
    justify-content: center; /* Memposisikan ikon di tengah */
    align-items: center;     /* Memposisikan ikon di tengah secara vertikal */
    text-align: right;
}

.circle i {
    font-size: 36px; /* Ukuran ikon */
    color: #007bff;  /* Warna ikon */
}
</style>

<body style="background-color: rgb(238, 238, 238);">

    <!-- Header Halaman -->
    <div class="d-flex mt-3 me-4 ms-3">
        <div class="row w-100">
            <a href="beranda_superUser.php" class="col-1">
                <div>
                    <img src="assets/back.png" alt="Kembali" width="30px">
                </div>
            </a>
            <div class="col">
                <h4 style="font-weight: bold; margin: 0; padding: 0;">Data Laporan Sakit</h4>
            </div>
        </div>
    </div>

    <div class="container mt-5" style="font-size: 12px;">
                <!-- ikon untuk opsi  -->
                <div class="d-flex gap-2">
                <div class="circle" id="circle">
                <img src="assets/refresh.png" alt="" width="20px">
                <p class="p-0 m-0 me-1 ms-1">Segarkan</p>
            </div>
            <script>
                const refreshButton =document.getElementById("circle");
                refreshButton.addEventListener('click', function(){
                    location.reload();
                });

            </script>
                <div class="circle bg-danger text-white" id="circle">
                <img src="assets/sampah_white.png" alt="" width="20px">
                <p class="p-0 m-0 me-1 ms-1">Hapus</p>
            </div>
         </div>

        <table class="table table-bordered mt-1">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Waktu Ijin</th>
                    <th>Alasan</th>
                    <th>Tanggal Dibuat</th>
                    <th>Surat Keterangan Dokter</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_terlambat->num_rows > 0): ?>
                    <?php while ($row = $result_terlambat->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['namaLengkap']) ?></td>
                            <td><?= htmlspecialchars($row['tanggal_mulai']) ?> sampai <?= htmlspecialchars($row['tanggal_selesai']) ?></td>
                            <td><?= htmlspecialchars($row['alasan']) ?></td>
                            <td><?= htmlspecialchars($row['created_at']) ?></td>
                            <td>
                                <?php if (!empty($row['surat_keterangan_dokter'])): ?>
                                    <a href="<?= htmlspecialchars($row['surat_keterangan_dokter']) ?>" target="_blank">Lihat Surat</a>
                                <?php else: ?>
                                    Tidak Ada Surat
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data laporan sakit.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Tombol Lihat Lebih Lanjut -->
        <?php if ($total_rows > $limit): ?>
            <div class="text-center">
                <a href="?page=<?= $page + 1; ?>" class="btn btn-primary">
                    Lihat Lebih Lanjut
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
