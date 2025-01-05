<?php
include "koneksi.php";
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Pembaharuan - AbsenSMAGA</title>
</head>
<body>
    <style>
        body{ 
            font-family: merriweather;
            background-color: rgb(238, 236, 226);
        }
    </style>
    <!-- Header Halaman -->
    <div class="d-flex mt-3 me-4 ms-3">
        <div class="row w-100">
            <a href="beranda.php" class="col-1">
                <div>
                    <img src="assets/back.png" alt="Kembali" width="30px">
                </div>
            </a>
            <div class="col">
                <h4 style="font-weight: bold; margin: 0; padding: 0;">Catatan Pembaruan</h4>
            </div>
        </div>
    </div>

    <!-- ini kontennya -->
     <div class="ms-4 me-4 mt-4 mb-4">
        <div class="">
            <div>
                <span class="bi-arrow-up-circle p-0 m-0" style="font-size: 40px; color: black;"></span>
            </div>
            <h3><strong>Catatan Pembaruan</strong></h3>
            <p style="font-size:14px;">Penentuan versi aplikasi kini menggunakan standar Semantic Versioning. Laporan pembaruan ini hanya dapat diakses dan diisi oleh tim pengembang.</p>
        </div>


                <!-- container versi -->
                <div class="bg-white p-3 m-1 mb-3 rounded-4" style="margin-bottom:10px;">
            <div>
                <h4 class="m-0 p-0">v.2.5.3</h4>
                <p style="font-size:14px;" class="m-0 p-0">05 Januari 2025 - Pembaruan Minor</p>
                <!-- tag pembaharuan -->
                 <div class="d-flex mt-2 mb-3">
                 <span class="bg-success rounded-pill text-white p-1 fw-bold me-1" style="font-size:7px;">FEATURES</span>
                 </div>
            </div>
            <div>
                <p style="font-size:12px">
                    Berikut adalah peningkatan yang kami hadirkan dalam update ini:
                </p>
            </div>
            <div style="font-size:12px">
                <ol>
                    <li class="mt-3">
                        Penambahan fitur izin yang berfungsi untuk memfasilitasi pengajuan perizinan serta mendukung kelengkapan administrasi.  </li>                  
                        <li class="mt-3">
                        Penghapusan fitur penghargaan yang dianggap membebani kinerja server, sehingga kami memutuskan untuk menonaktifkannya demi meningkatkan efisiensi sistem.</li>
                </ol>
            </div>
        </div>




        <!-- container versi -->
        <div class="bg-white p-3 m-1 mb-3 rounded-4" style="margin-bottom:10px;">
            <div>
                <h4 class="m-0 p-0">v.2.4.3</h4>
                <p style="font-size:14px;" class="m-0 p-0">01 Januari 2025 - Pembaruan Minor</p>
                <!-- tag pembaharuan -->
                 <div class="d-flex mt-2 mb-3">
                 <span class="bg-success rounded-pill text-white p-1 fw-bold me-1" style="font-size:7px;">FEATURES</span>
                 <span class="bg-success rounded-pill text-white p-1 fw-bold me-1" style="font-size:7px;">DATABASE</span>
                 </div>
            </div>
            <div>
                <p style="font-size:12px">
                    Berikut adalah peningkatan yang kami hadirkan dalam update ini:
                </p>
            </div>
            <div style="font-size:12px">
                <ol>
                    <li class="mt-3">
                    Penghargaan absensi kini telah tersedia! Sistem akan menghitung jumlah kehadiran Anda secara berurutan, sehingga Anda berkesempatan memperoleh penghargaan absensi.</li>
                    <li class="mt-3">
                    Fitur Kalender Absensi telah ditambahkan. Kalender ini memudahkan Anda untuk melihat dan mengevaluasi status absensi secara visual tanpa perlu menelusuri seluruh data absensi.</li>
                    <li class="mt-3">
                    Sesuai dengan instruksi Kepala Sekolah, jadwal jam absensi kehadiran telah diperbarui. Beberapa guru kini memiliki jadwal absensi kedatangan yang diubah dari pukul 07.15 menjadi pukul 07.00. Silakan cek jadwal kehadiran Anda pada menu Jadwal Kehadiran.</li>
                </ol>
            </div>
        </div>



                        <!-- container versi -->
                        <div class="bg-white p-3 m-1 mb-3 rounded-4" style="margin-bottom:10px;">
            <div>
                <h4 class="m-0 p-0">v.2.3.3</h4>
                <p style="font-size:14px;" class="m-0 p-0">24 Desember 2024 - Pembaruan Patch</p>
                <!-- tag pembaharuan -->
                 <div class="d-flex mt-2 mb-3">
                 <span class="bg-success rounded-pill text-white p-1 fw-bold me-1" style="font-size:7px;">BUG</span>
                 <span class="bg-success rounded-pill text-white p-1 fw-bold me-1" style="font-size:7px;">SECURITY</span>
                 </div>
            </div>
            <div>
                <p style="font-size:12px">
                    Berikut adalah peningkatan yang kami hadirkan dalam update ini:
                </p>
            </div>
            <div style="font-size:12px">
                <ol>
                    <li class="mt-3">
                    Penyelesaian bug khusus pada Android 14 yang menyebabkan aplikasi mengalami crash saat proses pemuatan.
                    </li>
                    <li class="mt-3">
                    Penambahan fitur face recognition yang berfungsi untuk mendeteksi wajah pengguna saat melakukan absensi menggunakan selfie.
                    </li>
                    <li class="mt-3">
                        Menambah dukungan instalasi aplikasi dalam penyimpanan Eksternal
                    </li>
                </ol>
            </div>
        </div>



                <!-- container versi -->
                <div class="bg-white p-3 m-1 mb-3 rounded-4" style="margin-bottom:10px;">
            <div>
                <h4 class="m-0 p-0">v.2.3.2</h4>
                <p style="font-size:14px;" class="m-0 p-0">18 Desember 2024 - Pembaruan Minor</p>
                <!-- tag pembaharuan -->
                 <div class="d-flex mt-2 mb-3">
                    <span class="bg-success rounded-pill text-white p-1 fw-bold me-1" style="font-size:7px;">FEATURES</span>
                    <span class="bg-success rounded-pill text-white p-1 fw-bold me-1" style="font-size:7px;">SECURITY</span>
                 </div>
            </div>
            <div>
                <p style="font-size:12px">
                    Berikut adalah peningkatan yang kami hadirkan dalam update ini:
                </p>
            </div>
            <div style="font-size:12px">
                <ol>
                    <li class="mt-3">
                    Penambahan fitur notifikasi lokal yang telah disesuaikan dengan jadwal absensi. Waktu notifikasi telah ditetapkan pada pukul 06.30 WIB dan 15.30 WIB setiap harinya.
                    </li>
                    <li class="mt-3">
                    Penerapan kode yang lebih aman, yang secara signifikan meningkatkan keamanan aplikasi. Langkah ini juga memastikan aplikasi memenuhi standar Google Play Secure yang sebelumnya sempat mengidentifikasi aplikasi dalam daftar merah.
                    </li>
                    <li class="mt-3">
                    Peningkatan keamanan dibandingkan versi sebelumnya melalui implementasi SDK 35, yang saat ini mendukung sistem operasi Android terbaru.
                    </li>

                </ol>
            </div>
        </div>


        <!-- container versi -->
        <div class="bg-white p-3 m-1 mb-3 rounded-4" style="margin-bottom:10px;">
            <div>
                <h4 class="m-0 p-0">v.2.2.2</h4>
                <p style="font-size:14px;" class="m-0 p-0">15 Desember 2024 - Pembaruan Major</p>
                <!-- tag pembaharuan -->
                 <div class="d-flex mt-2 mb-3">
                    <span class="bg-success rounded-pill text-white p-1 fw-bold me-1" style="font-size:7px;">UI INTERFACES</span>
                    <span class="bg-success rounded-pill text-white p-1 fw-bold me-1" style="font-size:7px;">FEATURES</span>
                    <span class="bg-success rounded-pill text-white p-1 fw-bold me-1" style="font-size:7px;">SECURITY</span>
                 </div>
            </div>
            <div>
                <p style="font-size:12px">
                    Dalam pembaruan kali ini, kami berfokus pada integrasi fitur absensi dengan SMAGAEdu. Berikut adalah peningkatan yang kami hadirkan dalam update ini:
                </p>
            </div>
            <div style="font-size:12px">
                <ol>
                    <li class="mt-3">
                        Perubahan pada antarmuka pengguna (User Interface/ UI) absensi agar lebih selaras dengan desain SMAGAEdu termasuk dalam penggunaan
                        tone warna aplikasi, font family, dan seluruh interaksi pengguna dengan aplikasi. Pembaruan UI akan diterapkan secara bertahap.
                    </li>
                    <li class="mt-3">
                        Penambahan fitur AI (Artificial Intelligence) yang menggunakan teknologi Gemini AI (versi BETA). Karena masih dalam tahap pengujian, fitur ini akan terus dimonitor. Jika di masa mendatang dinilai kurang optimal atau tidak diperlukan, fitur ini dapat dihapus untuk menghemat sumber daya sistem.
                    </li>
                    <li class="mt-3">
                        Penghapusan fitur tampilan jam, karena waktu server telah disesuaikan secara otomatis dengan zona waktu WIB.
                    </li>
                    </li>
                    <li class="mt-3">
                        Penghapusan salam pembuka, karena fungsinya telah digantikan oleh petunjuk waktu absensi yang lebih relevan.                    
                    </li>
                    <li class="mt-3">
                        Penyederhanaan tombol "Segarkan Absensi" untuk memberikan pengalaman yang lebih ringkas dan efisien.
                    </li>
                    <li class="mt-3">
                        Peningkatan keamanan dengan penghapusan kode debug untuk menjaga stabilitas dan performa aplikasi.
                    </li>
                </ol>
            </div>
        </div>


                <!-- container versi -->
                <div class="bg-white p-3 m-1 mb-3 rounded-4" style="margin-bottom:10px;">
            <div>
                <h4 class="m-0 p-0">v.1.2.2</h4>
                <p style="font-size:14px;" class="m-0 p-0">25 November 2024 - Pembaruan Patch</p>
                <!-- tag pembaharuan -->
                 <div class="d-flex mt-2 mb-3">
                 <span class="bg-success rounded-pill text-white p-1 fw-bold me-1" style="font-size:7px;">SECURITY</span>
                 </div>
            </div>
            <div>
                <p style="font-size:12px">Pembaharuan kali ini beberapa kami tingkatkan berupa :
                </p>
            </div>
            <div style="font-size:12px">
                <ol>
                    <li class="mt-3">Peningkatan keamanan pada aplikasi Android dicapai dengan memperbarui versi toleransi SDK Android sehingga aplikasi tersebut aman dari pemindaian Google Play Protect.
                        Kami akan selalu menjamin aplikasi kami selalu aman untuk perangkat Anda
                    </li>
                    <li class="mt-3">Dalam upaya meningkatkan fungsionalitas, akan ditambahkan fitur "Tampilkan Lainnya" pada daftar kehadiran. Daftar kehadiran akan dibatasi hingga maksimal 10 data absen per tampilan awal. Jika tombol "Tampilkan Lainnya" diklik, maka akan ditampilkan 10 data absen tambahan. Fitur ini akan berlaku untuk seluruh data yang tersedia.</li>
                </ol>
            </div>
        </div>



        <!-- container versi -->
        <div class="bg-white p-3 m-1 mb-3 rounded-4" style="margin-bottom:10px;">
            <div>
                <h4 class="m-0 p-0">v.1.2.1</h4>
                <p style="font-size:14px;" class="m-0 p-0">24 November 2024 - Pembaruan Minor</p>
                <!-- tag pembaharuan -->
                 <div class="d-flex mt-2 mb-3">
                 <span class="bg-success rounded-pill text-white p-1 fw-bold me-1" style="font-size:7px;">UI INTERFACES</span>
                 <span class="bg-success rounded-pill text-white p-1 fw-bold me-1" style="font-size:7px;">SECURITY</span>
                 <span class="bg-success rounded-pill text-white p-1 fw-bold me-1" style="font-size:7px;">SYSTEM</span>
                 </div>
            </div>
            <div>
                <p style="font-size:12px">Pembaharuan kali ini mendesain ulang beberapa unit dan peningkatan keamanan. Beberapa kami
                    adakan pembaharuan berupa:
                </p>
            </div>
            <div style="font-size:12px">
                <ol>
                    <li class="mt-3">Mengoptimalkan tampilan visual halaman beranda dengan meredesain tombol-tombol utama untuk meningkatkan interaktivitas pengguna.</li>
                    <li class="mt-3">Menyajikan informasi kehadiran secara lebih ringkas dan modern pada container "sekilas kehadiran", sehingga pengguna dapat dengan cepat memahami data yang relevan.</li>
                    <li class="mt-3">Memperbaiki bug yang mengganggu pengalaman pengguna, seperti penumpukan pop up absensi, untuk memastikan kelancaran operasional sistem.</li>
                    <li class="mt-3">Menerapkan langkah-langkah keamanan tambahan dengan menghapus kode debug yang berpotensi membahayakan kerahasiaan data lokasi pengguna.</li>
                    <li class="mt-3">Memperbarui sistem database dalam penerapan absensi, mendukung pengguna untuk mempunyai waktu datang dan pulang yang berbeda, dengan hari yang berbeda.</li>
                    <li class="mt-3">Sehubungan dengan rendahnya tingkat penggunaan, fitur absensi Wi-Fi telah dinonaktifkan.</li>
                </ol>
            </div>
        </div>


                <!-- container versi -->
                <div class="bg-white p-3 m-1 mb-3 rounded-4" style="margin-bottom:10px;">
            <div>
                <h4 class="m-0 p-0">v.1.1.1</h4>
                <p style="font-size:14px;" class="m-0 p-0">23 November 2024 - Pembaruan Minor</p>
                <!-- tag pembaharuan -->
                 <div class="d-flex mt-2 mb-3">
                    <span class="bg-success rounded-pill text-white p-1 fw-bold me-1" style="font-size:7px;">UI INTERFACES</span>
                    <span class="bg-success rounded-pill text-white p-1 fw-bold me-1" style="font-size:7px;">STABILITY</span>
                 </div>
            </div>
            <div>
                <p style="font-size:12px">Pembaharuan kali ini memperbarui User Interface dan kecepatan respon Absensi. Beberapa kami
                    adakan pembaharuan berupa:
                </p>
            </div>
            <div style="font-size:12px">
                <ol>
                <li class="mt-3"> Jenis font pada aplikasi telah diperbarui dari keluarga font Roboto ke Inter. Perubahan ini dilakukan untuk meningkatkan kenyamanan pengguna dalam membaca informasi pada sistem absensi. </li>                    
                <li class="mt-3"> Respons pada fitur absensi telah ditingkatkan agar lebih interaktif dan memberikan pengalaman pengguna yang lebih baik. </li>
                <li class="mt-3"> Tampilan antarmuka petunjuk waktu absensi telah didesain ulang menjadi lebih ringkas namun tetap jelas dan informatif. </li> 
                <li class="mt-3"> Opsi perizinan terkait keterlambatan, sakit, dan cuti telah dihapus sesuai dengan kebijakan Kepala Sekolah, yang menetapkan prosedur perizinan dilakukan secara langsung melalui tatap muka atau pesan. </li>
                <li class="mt-3"> Tata letak dan desain antarmuka pada laman Daftar Absensi Saya telah disederhanakan dan diperbarui agar terlihat lebih modern dan minimalis. </li>
                <li class="mt-3"> Menerapkan fitur penghapusan data absensi pengguna melalui penambahan tombol 'Hapus' pada antarmuka pengguna.</li>
                </ol>
            </div>
        </div>


        <!-- container versi -->
        <div class="bg-white p-3 m-1 rounded-4">
            <div>
                <h4 class="p-0 m-0">v.1.0.1</h4>
                <p style="font-size:14px;" class="p-0 m-0">22 November 2024 - Pembaruan Patch</p>
                <!-- tag pembaharuan -->
                <div class="d-flex mt-2 mb-3">
                    <span class="bg-success rounded-pill text-white p-1 fw-bold me-1" style="font-size:7px;">STABILITY</span>
                </div>

            </div>
            <div>
                <p style="font-size:12px">Pembaharuan kali ini hanya stabilitas dan performa kecepatan absensi. Beberapa kami
                    adakan pembaharuan berupa:
                </p>

            </div>
            <div style="font-size:12px">
                <ol>
                    <li class="mt-3">
                    Telah dilakukan optimasi kinerja situs web dengan menghapus perpustakaan JavaScript usang seperti HTML5-QR (Absen Barcode) dan Leaflet (Absen Lokasi). 
                    </li>
                    <li class="mt-3">
                    Penggantian dengan perpustakaan OpenMap (Absen Lokasi) dan Instacam (Absen Barcode) yang lebih responsif diharapkan dapat meningkatkan kecepatan loading halaman.
                    </li>
                </ol>
            </div>
        </div>

    </body>
</html>