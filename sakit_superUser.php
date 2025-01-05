<?php
session_start();
include 'koneksi.php'; // Pastikan ini mengarah ke koneksi database Anda

if (!isset($_SESSION['user_id']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Ambil data izin sakit
$query_sakit = "SELECT u.namaLengkap, s.id, s.tanggal_mulai, s.tanggal_selesai, s.alasan 
FROM sakit s 
JOIN users u ON s.user_id = u.id"; 
$stmt_sakit = $conn->prepare($query_sakit);
$stmt_sakit->execute();
$result_sakit = $stmt_sakit->get_result();

$sakit_data = [];

while ($row = $result_sakit->fetch_assoc()) {
    $sakit_data[] = $row; // Menyimpan hasil ke dalam array
}

// Tutup statement
$stmt_sakit->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Daftar Izin Sakit</title>
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
        <div class="row">
            <a href="beranda_superUser.php" class="col-1">
                <div>
                    <img src="assets/back.png" alt="Kembali" width="30px">
                </div>
            </a>
            <div class="col">
                <h4 style="font-weight: bold; margin: 0; padding: 0;">Perizinan Sakit Karyawan</h4>
            </div>
        </div>
    </div>

    <!-- Menampilkan Data Izin Sakit -->
    <?php if (empty($sakit_data)): ?>
        <p class="text-center" style="color: grey;">Absensi Kosong</p>
    <?php else: ?>
        <?php foreach ($sakit_data as $sakit): ?>
        <div class="mt-3 me-3 ms-3 p-2 rounded-4" style="background-color: white;">
            <div class="pt-3 ps-3 pe-3 pb-1">
                <p class="ps-2"><strong><?= htmlspecialchars($sakit['namaLengkap']) ?></strong></p>
                <div class="container pb-3">
                    <div class="row">
                        <div class="col">Tanggal Mulai Izin</div>
                        <div class="col"><?= date('d F Y', strtotime($sakit['tanggal_mulai'])) ?></div>
                    </div>
                    <div class="row">
                        <div class="col">Tanggal Selesai Izin</div>
                        <div class="col"><?= date('d F Y', strtotime($sakit['tanggal_selesai'])) ?></div>
                    </div>
                    <div class="row">
                        <div class="col">Alasan</div>
                        <div class="col"><?= htmlspecialchars($sakit['alasan']) ?></div>
                    </div>
                </div>
                <div class="d-grid pe-2 ps-2 pb-3">
                    <button type="button" class="btn btn-danger" onclick="confirmDelete(<?= $sakit['id'] ?>)">
                        <i class="bi bi-x-circle pe-1"></i>
                        Hapus Izin Sakit
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Modal Konfirmasi Hapus -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus izin sakit ini?
                </div>
                <div class="modal-footer btn-group  justify-content-between" role="group">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
                </div>
            </div>
        </div>
    </div>

        <!-- Modal Konfirmasi Hapus -->
        <div class="modal fade" id="hapusBerhasil" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmModalLabel">Peringatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Perizinan telah di hapus
                </div>
                <div class="modal-footer d-grid" role="group">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ok</button>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let idSakitToDelete = null; // Menyimpan id izin sakit yang akan dihapus

        function confirmDelete(id) {
            idSakitToDelete = id; // Simpan ID izin sakit yang ingin dihapus
            const deleteConfirmModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            deleteConfirmModal.show(); // Tampilkan modal konfirmasi
        }

        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            // Kirim permintaan untuk menghapus izin sakit menggunakan fetch
            fetch('hapus_izinSakit.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: idSakitToDelete }) // Kirim ID yang ingin dihapus
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    modal = new bootstrap.Modal(document.getElementById('hapusBerhasil'));
                    location.reload(); // Refresh halaman untuk melihat pembaruan
                } else {
                    alert('Gagal menghapus izin sakit: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });

    </script>
</body>
</html>