<?php
include"koneksi.php";
session_start();

// debug
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// var_dump($_SESSION);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Pendaftaran Karyawan</title>
</head>
<style>
    .modal-body {
    display: flex;
    justify-content: center; /* Untuk memusatkan konten */
    align-items: center; /* Untuk memusatkan konten secara vertikal */
}

.text-justify {
    text-align: justify; /* Justifikasi teks */
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
                <h4 style="font-weight: bold; margin: 0; padding: 0;">Pendaftaran Karyawan</h4>
            </div>
        </div>
    </div>

    <!-- form registrasi -->
     <div class="mt-3 me-4 ms-4 mb-3">
        <div class="alert alert-success" role="alert">
        <div d-grid>
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col justify-content-center text-center">
                            <img src="assets/screenshot.png" alt="" width="40px">
                        </div>
                        <div class="col-9">
                            <p class="p-0 m-0"><strong>Screenshot Formulir</strong></p>
                            <p style="font-size:10px;" class="p-0 m-0">Silahkan untuk screenshot formulir Anda terlebih dahulu sebelum mengirimkannya ke database dan bagikan kepada Guru atau karyawan Anda.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
     </div>


    <!-- form pendaftaran karyawan -->
     <div class="ms-4 me-4 p-3 rounded" style="background-color:white;">
        <form action="tambahGuru_back_superUser.php" method="post" id="formDaftar">
        <div class="mb-3">
            <label for="namalengkap" class="form-label">Nama Lengkap Karyawan Baru</label>
            <input type="text" class="form-control" id="namalengkap" name="namalengkap" aria-describedby="emailHelp" required>
            <!-- <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div> -->
        </div>
        <div class="mb-3">
            <label for="namalengkap" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" aria-describedby="emailHelp" required>
            <div id="emailHelp" class="form-text">Anda bebas memilihkan username karyawan Anda</div>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label" aria-describedby="passwordDesc">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
            <div id="passwordDesc" class="form-text">Anda bebas memilihkan password karyawan Anda</div>
        </div>
        <!-- status honor atau peuh -->
         <div class="mb-3">
            <label for="radioStatus">Status</label>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="flexRadioDefault" id="radioStatus">
                <label class="form-check-label" for="flexRadioDefault1">
                    Penuh
                </label>
                </div>
                <div class="form-check">
                <input class="form-check-input" type="radio" name="flexRadioDefault" id="radioStatus" checked>
                <label class="form-check-label" for="flexRadioDefault2">
                    Honor
                </label>
            </div>
         </div>
         <div class="btn-group d-flex justify-content-between" role="group">
            <button type="button" class="btn btn-success" id="buttonDaftarkan">Daftarkan</button>
        </div>
        </form>
     </div>

<!-- modal untuk konfirmasi pendaftaran karyawan -->
<div class="modal fade text-black" id="confirmModalDaftar" tabindex="-1" aria-labelledby="modalSuksesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalSuksesLabel">Konfirmasi Pendaftaran</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <div d-grid>
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col justify-content-center text-center">
                            <img src="assets/tambah_karyawan.png" alt="" width="40px">
                        </div>
                        <div class="col-9">
                            <p class="p-0 m-0"><strong>Tambah Karyawan?</strong></p>
                            <p style="font-size:14px;">Formulir Anda akan kami rekam ke dalam database kami.</p>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <div class="modal-footer btn-group justify-content-between" role="group">
                <button type="button" class="btn btn-outline-success" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" data-bs-dismiss="modal" id="confirmDaftar">Daftar</button>
            </div>
        </div>
    </div>
</div>

    <script>
/ deklarasi untuk pendaftaran karyawan
const btn = document.getElementById('buttonDaftarkan');
const modal = document.getElementById('confirmModalDaftar');
const daftar = document.getElementById('confirmDaftar');
const form = document.getElementById('formDaftar'); // Tambahkan ini, sesuaikan dengan ID form Anda

// klik button untuk muncul modal
btn.onclick = function() {
    // modal muncul
    var modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
};

// handle submit form
daftar.onclick = function(e) {
    e.preventDefault(); // Mencegah halaman refresh
    
    // Ambil semua data form
    const formData = new FormData(form);
    
    // Kirim data ke backend menggunakan fetch
    fetch('tambahGuru_back_superUser.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            alert('Data Karyawan berhasil disimpan!');
        } else {
            alert('Terjadi kesalahan: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengirim data');
    });
};    </script>




    <!-- Modal untuk berhasil -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered text-start">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">Sukses Menambahkan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Data guru berhasil ditambahkan!
                </div>
                <div class="modal-footer btn-group justify-content-between" role="group">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    


<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if(isset($_SESSION['success_message'])): ?>
        var myModal = new bootstrap.Modal(document.getElementById('successModal'));
        myModal.show();
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
});
</script>
</body>
</html>