<?php
session_start();
require 'koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Query untuk data absensi sesuai user yang login
$user_id = $_SESSION['user_id'];
$limit = 10; // Jumlah baris per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Halaman saat ini
$offset = ($page - 1) * $limit; // Offset untuk query

// Query untuk mendapatkan data absensi dengan pagination
$query = "SELECT * FROM datang 
          WHERE user_id = ? 
          ORDER BY tanggal DESC, waktu_absen DESC
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $user_id, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Hitung total halaman
$total_query = "SELECT COUNT(*) as total FROM datang WHERE user_id = ?";
$total_stmt = $conn->prepare($total_query);
$total_stmt->bind_param("i", $user_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_pages = ceil($total_row["total"] / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Absensi</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://rsms.me/">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">

    <style>
        .container-absen {
            border-radius: 20px;
        }
        #loadMoreBtn {
            display: <?php echo ($page >= $total_pages) ? 'none' : 'block'; ?>;
        }
    </style>
</head>
<body style="background-color: rgb(238, 238, 238); font-family: merriweather">

    <!-- Header Halaman -->
    <div class="d-flex mt-3 me-4 ms-3" style="margin-left:10px;">
        <div class="row w-100">
            <a href="beranda.php" class="col-1">
                <div>
                    <img src="assets/back.png" alt="Kembali" width="30px">
                </div>
            </a>
            <div class="col">
                <h4 style="font-weight: bold; margin: 0; padding: 0;">Daftar Absensi Saya</h4>
            </div>
        </div>
    </div>

    <!-- Notifikasi -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <img src="assets/ok.png" alt="" width="20px">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- List kehadiran -->
    <div id="absensiContainer">
        <?php
        
        while ($row = $result->fetch_assoc()) : 
        ?>
        <div class="container-absen p-2 m-3" style="background-color: white; margin:10px;">
            <div class="container">
                <div class="row d-flex align-items-center justify-content-between">
                    <div class="col">
                        <div>
                            <h1 class="display-1 p-0 m-0" style="font-size:40px;"><strong><?php echo $row['waktu_absen']; ?></strong></h1>
                        </div>
                        <div class="d-flex justify-content-start" style="font-size:12px;">
                            <p><?php echo date('d-m-Y', strtotime($row['tanggal'])); ?></p>
                            <p class="text-white">p</p>
                            <p class="ms-3"><?php echo $row['metode_absen']; ?></p>
                            <p class="text-white">p</p>
                            <span class="text-white">
                                <?php 
                                    if ($row['status'] == 'terlambat') {
                                        echo '<span class="badge bg-danger text-white">Terlambat</span>';
                                    } if ($row ['status'] == 'pulang') {
                                        echo'<span class="badge bg-warning">Pulang</span>';
                                    } if ($row ['status'] == 'tepat waktu') {
                                        echo '<span class="badge bg-success text-white">Tepat Waktu</span>';
                                    }
                                ?>
                            </span>
                        </div>
                        <div>
                            <!-- <button type="button" style="padding-bottom:0px; padding-top:0px;" class="btn btn-danger btn-sm rounded-pill d-grid" data-toggle="modal" data-target="#deleteModal<?php echo $row['id']; ?>">
                                <i class="bi bi-trash" style="font-size:10px;"></i> <span style="font-size:10px;">Hapus</span>
                            </button> -->
                        </div>
                    </div>
                    <div class="">
                        <?php if (!empty($row['foto'])) : ?>
                            <img src="<?php echo $row['foto']; ?>" alt="Foto Absen" class="ms-auto" style="width: 80px; height: auto; border-radius: 18px;" loading="lazy" data-toggle="modal" data-target="#fotoModal<?php echo $row['id']; ?>">
                        <?php else : ?>
                            <span class="text-muted" style="font-size:10px; padding-right:10px;">Tidak ada foto</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Konfirmasi Hapus -->
        <div class="modal fade" id="deleteModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
            <!-- Modal hapus content -->
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteModalLabel<?php echo $row['id']; ?>">
                            <i class="fas fa-exclamation-triangle"></i> Konfirmasi Hapus
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus data absensi ini?</p>
                        <div class="alert alert-danger">
                            <div class="container">
                                <div class="row">
                                    <div class="col d-flex align-items-center" style="font-size:12px;">
                                        Tanggal: <?php echo date('d-m-Y', strtotime($row['tanggal'])); ?><br>
                                        Waktu: <?php echo $row['waktu_absen']; ?> <br>
                                        Metode: <?php echo $row['metode_absen']; ?>
                                    </div>
                                    <div>
                                        <?php if (!empty($row['foto'])) : ?>
                                            <img src="<?php echo $row['foto']; ?>" alt="Foto Absen" class="ms-auto rounded" style="width: 60px; height: auto;" loading="lazy" data-toggle="modal" data-target="#fotoModal<?php echo $row['id']; ?>">
                                        <?php else : ?>
                                            <span class="text-muted" style="font-size:10px; padding-right:10px;">Tidak ada foto</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <small class=""></small>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-item-center">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            Batal
                        </button>
                        <form action="hapus_absensi.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="btn btn-danger rounded-end">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal untuk menampilkan gambar besar -->
        <div class="modal fade" id="fotoModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="fotoModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered text-start" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <img src="<?php echo $row['foto']; ?>" alt="Foto Absen" class="img-fluid rounded">
                    </div>
                    <div class="modal-footer btn-group justify-content-between">
                        <button type="button" class="btn btn-success" data-dismiss="modal">Ok</button>                                                                
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <?php if ($result->num_rows == 0) : ?>
        <div class="text-center mt-3">Tidak ada data absensi</div>
    <?php endif; ?>

    <!-- Tombol Muat Lebih Banyak -->
    <?php if ($page < $total_pages) : ?>
        <div class="text-center mt-3">
            <a href="?page=<?php echo $page + 1; ?>" id="loadMoreBtn" class="btn" style="background-color:rgb(235, 219, 188);">
                Muat Lebih Banyak
            </a>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('loadMoreBtn')?.addEventListener('click', function(e) {
        e.preventDefault();
        const nextPage = <?php echo $page + 1; ?>;
        fetch(`?page=${nextPage}`)
            .then(response => response.text())
            .then(html => {
                // Create a temporary div to parse the new content
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                
                // Extract new absensi items
                const newItems = tempDiv.querySelector('#absensiContainer').innerHTML;
                
                // Append new items to existing container
                document.getElementById('absensiContainer').innerHTML += newItems;
                
                // Update load more button visibility
                const loadMoreBtn = document.getElementById('loadMoreBtn');
                if (nextPage >= <?php echo $total_pages; ?>) {
                    loadMoreBtn.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memuat data');
            });
    });
    </script>
</body>
</html>

<?php
// Tutup koneksi dan statement di akhir file
$stmt->close();
$total_stmt->close();
$conn->close();
?>