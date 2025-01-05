<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Ambil data kehadiran dari database hanya dengan status 'tepat waktu'
$limit = 15; // Jumlah baris per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Halaman saat ini
$offset = ($page - 1) * $limit; // Offset untuk query

// Query untuk menghitung total jumlah kehadiran
$total_query = "SELECT COUNT(*) as total FROM datang WHERE TRIM(LOWER(status)) = 'tepat waktu'";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_rows = $total_row['total'];
$total_pages = ceil($total_rows / $limit); // Total halaman

// Query untuk mengambil data dengan batasan
$query_absen = "
    SELECT d.waktu_absen, d.status, d.metode_absen, d.foto, u.namaLengkap, d.tanggal as tanggal_asli
    FROM datang d 
    JOIN users u ON d.user_id = u.id 
    WHERE TRIM(LOWER(d.status)) = 'terlambat'
    ORDER BY d.tanggal DESC, d.waktu_absen DESC
    LIMIT ? OFFSET ?
";

$stmt_absen = $conn->prepare($query_absen);
$stmt_absen->bind_param("ii", $limit, $offset);
$stmt_absen->execute();
$result_absen = $stmt_absen->get_result();

// Tutup statement
$stmt_absen->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Tabel Kehadiran Guru Tepat Waktu</title>
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
<body style="background-color: rgb(238, 238, 238);" class=" mb-5">

    <!-- Header Halaman -->
    <div class="d-flex mt-3 me-4 ms-3">
        <div class="row w-100">
            <a href="beranda_superUser.php" class="col-1">
                <div>
                    <img src="assets/back.png" alt="Kembali" width="30px">
                </div>
            </a>
            <div class="col">
                <h4 style="font-weight: bold; margin: 0; padding: 0;">Data Kepulangan</h4>
            </div>
        </div>
    </div>

    <div class="container mt-5" style="font-size:12px;">

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
            <table class="table mt-2">
            <thead class="rounded-top">
                <tr>
                    <th>Nama</th>
                    <th>Waktu</th>
                    <th>Tanggal</th>
                    <th>Metode</th>
                    <th>Foto</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_absen->num_rows > 0): ?>
                    <?php while ($row = $result_absen->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['namaLengkap']) ?></td>
                            <td><?= date('H:i', strtotime($row['waktu_absen'])) ?></td>
                            <td><?= htmlspecialchars($row['tanggal_asli']) ?></td>
                            <td><?= htmlspecialchars($row['metode_absen']) ?></td>
                            <td>
                                <?php if (!empty($row['foto'])): ?>
                                    <img src="<?= htmlspecialchars($row['foto']) ?>" alt="Foto Absen" class="rounded" width="80px" data-toggle="modal" data-target="#modalzoom" data-src="<?= htmlspecialchars($row['foto']) ?>" data-alt="Foto Absen">
                                <?php else: ?>
                                    Tidak Ada Foto
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data kehadiran tepat waktu.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- modal lihat foto -->
                 <!-- Modal untuk lihat foto lebih jelas -->
        <div id="modalzoom" class="modal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered text-start">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Foto Absen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <img id="img01" class="img-fluid" alt="">
                    </div>
                    <div class="modal-footer btn-group justify-content-between" role="group">
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Ok</button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Tombol Lihat Lebih Lanjut -->
        <?php if ($total_rows > $limit): ?>
            <div class="d-flex justify-content-center align-items-center gap-3">
                <a href="?page=<?php echo $page - 1; ?>" class="btn btn-success">
                    <div class="bi bi-arrow-left"></div>
                </a>
                <span><?php echo $page ?> dari <?php echo $total_pages ?> </span>
                <a href="?page=<?php echo $page + 1; ?>" class="btn btn-success">
                    <div class="bi bi-arrow-right"></div>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
            // Ambil semua gambar dengan atribut data-toggle="modal"
            var images = document.querySelectorAll('img[data-toggle="modal"]');

// Loop melalui semua gambar dan tambahkan event listener
images.forEach(function(image) {
    image.addEventListener('click', function() {
        var src = this.getAttribute('data-src');
        var alt = this.getAttribute('data-alt');
        var modalImg = document.getElementById('img01');
        // Tampilkan gambar dan teks di modal
        modalImg.src = src;
        modalImg.alt = alt;

        // Tampilkan modal
        var myModal = new bootstrap.Modal(document.getElementById('modalzoom'));
        myModal.show();
    });
});

// Close the modal when the user clicks outside of the modal
document.getElementById('modalzoom').addEventListener('click', function(event) {
    if (event.target === this) {
        var myModal = bootstrap.Modal.getInstance(this);
        myModal.hide();
    }
});

</script>
<style>
        /* Style the Image Used to Trigger the Modal */
#foto {
  border-radius: 5px;
  cursor: pointer;
  transition: 0.3s;
}

#myImg:hover {opacity: 0.7;}

The Modal (background) */
.modal {
  display: none; /* Hidden by default */
  position: fixed; /* Stay in place */
  z-index: 1; /* Sit on top */
  padding-top: 100px; /* Location of the box */
  left: 0;
  top: 0;
  width: 100%; /* Full width */
  height: 100%; /* Full height */
  overflow: auto; /* Enable scroll if needed */
  background-color: rgb(0,0,0); /* Fallback color */
  /* opacity */
  background-color: rgba(0,0,0,0.9);
 }


/* Modal Content (Image) */
.modal-content {
  margin: auto;
  display: block;
  width: 80%;
  max-width: 700px;
}

/* Caption of Modal Image (Image Text) - Same Width as the Image */
#caption {
  margin: auto;
  display: block;
  width: 80%;
  max-width: 700px;
  text-align: center;
  color: #ccc;
  padding: 10px 0;
  height: 150px;
}

/* Add Animation - Zoom in the Modal */
.modal-content, #caption {
  animation-name: zoom;
  animation-duration: 0.6s;
}

@keyframes zoom {
  from {transform:scale(0)}
  to {transform:scale(1)}
}

/* The Close Button */
.close {
  position: absolute;
  top: 15px;
  right: 35px;
  color: #f1f1f1;
  font-size: 40px;
  font-weight: bold;
  transition: 0.3s;
}

.close:hover,
.close:focus {
  color: #bbb;
  text-decoration: none;
  cursor: pointer;
}

/* 100% Image Width on Smaller Screens */
@media only screen and (max-width: 700px){
  .modal-content {
    width: 100%;
  }
}

</style>
</body>
</html>