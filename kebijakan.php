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
    <title>Kebijakan - AbsenSMAGA</title>
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
                <h4 style="font-weight: bold; margin: 0; padding: 0;">Kebijakan Penggunaan</h4>
            </div>
        </div>
    </div>

    <!-- ini kontennya -->
     <div class="ms-4 me-4 mt-4 mb-4">
        <div class="">
            <div>
                <span class="bi-shield-lock-fill p-0 m-0" style="font-size: 40px; color: black;"></span>
            </div>
            <h3><strong>Antara Kami dan Anda</strong></h3>
            <p style="font-size:14px;">Kebijakan yang berlaku antara pihak kami dan Anda, termasuk kewajiban serta hak masing-masing pihak dalam penggunaan aplikasi Absenku.</p>
            </div>

        <!-- container kebijakan -->
        <div class="bg-white p-3 m-1 mb-3 rounded-4" style="margin-bottom:10px;">
            <div>
                <h4 class="m-0 p-0">Kebijakan Privasi</h4>
                <p style="font-size:14px;" class="m-0 p-0">Mencakup bagaimana Data Anda kami kumpulkan, gunakan, dan lindungi. Berikut rinciannya:</p>
            </div>
            <div style="font-size:12px">
                <ol>
                    <li class="mt-3" style="font-weight:bold">
                        Data yang dikumpulkan
                    </li>
                    <p class="p-0 m-0">Beberapa data yang kami kumpulkan dari Anda seperti, data IP Adress, Lokasi, dan Foto Anda. Hal ini kami terima dari metode absensi yang Anda gunakan.</p>
                    <li class="mt-3" style="font-weight:bold">
                        Penggunaan Data
                    </li>
                    <p class="p-0 m-0">Tujuan dari pengambilan data Anda yaitu kami gunakan sebagai penanda bahwa Anda telah absen yang telah disesuaikan dengan jam absen Anda. Kemudian data Anda akan kami olah dan kami berikan kepada pihak sekolah.</p>
                    <li class="mt-3" style="font-weight:bold">
                        Keamanan Data
                    </li>
                    <p class="p-0 m-0">Kami berkomitmen untuk melindungi seluruh data yang telah Anda berikan kepada kami. Aplikasi Absenku telah menggunakan enskripsi dan protokol keamanan \
                        untuk mencegah akses anonim tidak sah, perubahan, dan pengungkapan data pribadi Anda.
                    </p>
                    <li class="mt-3" style="font-weight:bold">
                        Penyimpanan Data
                    </li>
                    <p class="p-0 m-0">Saat ini, data pribadi Anda dikumpulkan dan disimpan di server Rumahweb, yang hanya dapat diakses oleh Tim IT SMAGA. Salah satu pertimbangan utama kami dalam memilih server Rumahweb adalah kecepatan respons terhadap aduan serta penanganan yang cepat apabila terjadi gangguan pada server. Server akan diperbarui setiap enam bulan sekali, dan data absensi Anda akan diunduh sebagai laporan kehadiran yang akan disimpan oleh Tata Usaha SMAGA.</p>
                    </p>
                </ol>
            </div>
        </div>

        <!-- container kebijakan -->
        <div class="bg-white p-3 m-1 mb-3 rounded-4" style="margin-bottom:10px;">
            <div>
                <h4 class="m-0 p-0">Kebijakan Penggunaan</h4>
                <p style="font-size:14px;" class="m-0 p-0">Absenku dirancang untuk memudahkan proses absensi secara digital. Pengguna diwajibkan untuk menggunakan aplikasi ini sesuai dengan tujuan dan arahan yang ditetapkan, yaitu untuk memantau kehadiran dan absensi di lingkungan SMAGA.</p>
            </div>
            <div style="font-size:12px">
                <ol>
                    <li class="mt-3" style="font-weight:bold">
                        Hak Pengguna
                    </li>
                    <ol>
                        <li>
                            Pengguna berhak mengakses fitur absensi yang tersedia dalam aplikasi sesuai dengan arahan.
                        </li>
                        <li>
                            Pengguna berhak menerima laporan absensi yang akurat dan tepat waktu.
                        </li>
                    </ol>
                    <li class="mt-3" style="font-weight:bold">
                        Kewajiban Pengguna
                    </li>
                    <ol>
                        <li>
                            Pengguna diwajibkan untuk melakukan absensi sesuai dengan prosedur yang telah ditentukan.
                        </li>
                        <li>
                            Pengguna wajib menjaga kerahasiaan data pribadi terkait dengan akun aplikasi.
                        </li>
                    </ol>
                    <li class="mt-3" style="font-weight:bold">
                        Larangan Penggunaan
                    </li>
                    <ol>
                        <li>
                        Pengguna dilarang menggunakan aplikasi untuk tujuan yang tidak sah, termasuk tetapi tidak terbatas pada manipulasi data absensi atau akses tidak sah ke akun Anda lain.
                        </li>
                        <li>
                        Pengguna dilarang membagikan informasi akun (seperti username dan password) kepada pihak lain yang tidak berwenang.
                        </li>
                    </ol>
                </ol>
            </div>
        </div>


                <!-- container kebijakan -->
                <div class="bg-white p-3 m-1 mb-3 rounded-4" style="margin-bottom:10px;">
            <div>
                <h4 class="m-0 p-0">Artificial intelligence / AI</h4>
                <p style="font-size:14px;" class="m-0 p-0"> Absenku menyematkan teknologi kecerdasan buatan (AI) untuk meningkatkan pengalaman Anda dalam berinteraksi dengan aplikasi. Fitur AI yang saat ini disematkan dalam Absenku menggunakan teknologi dari Google Gemini ini dirancang untuk memberikan bantuan dan informasi terkait absensi secara real-time.</p>
            </div>
            <div style="font-size:12px">
                <ol>
                    <li class="mt-3" style="font-weight:bold">
                        Ketersediaan Fitur
                    </li>
                    <p class="p-0 m-0">Fitur ini masih bersifat ekperimental dan dapat dihentikan atau dihapus sewaktu-waktu tenpa pemberitahuan sebelumnya.
                    </p>
                    <li class="mt-3" style="font-weight:bold">
                        Pengumpulan dan Penyimpanan Data
                    </li>
                    <ol>
                        <li>
                            Data interaksi Anda dengan Gemini tidak akan disimpan atau digunakan untuk keperluan lain kecualu selain dari memberikan respons langsung terkait permintaan Anda.
                        </li>
                        <li>
                        Setiap percakapan yang terjadi melalui fitur AI akan segera diproses dan tidak ada riwayat percakapan yang disimpan setelah sesi berakhir.
                        </li>
                    </ol>
                    <li class="mt-3" style="font-weight:bold">
                    Keterbatasan Fitur AI
                    </li>
                    <ol>
                        <li>
                        Fitur AI ini hanya dapat memberikan respons berdasarkan data dan algoritma yang tersedia pada saat itu. Terkadang, respons yang diberikan mungkin tidak akurat atau memadai, mengingat sifat eksperimental dari teknologi yang digunakan.
                        </li>
                        <li>
                        Kami tidak dapat menjamin bahwa fitur AI akan selalu tersedia atau berfungsi dengan sempurna, karena dapat terjadi gangguan teknis atau perubahan dalam sistem yang digunakan.
                        </li>
                    </ol>
                    <li class="mt-3" style="font-weight:bold">
                    Tanggung Jawab Pengguna
                    </li>
                    <p class="p-0 m-0">Pengguna bertanggung jawab untuk menggunakan fitur AI sesuai dengan tujuan yang sah dan tidak mengajukan permintaan yang melanggar kebijakan atau ketentuan yang berlaku dalam aplikasi Absenku.</p>
                    <li class="mt-3" style="font-weight:bold">
                    Perubahan dan Penghentian Fitur
                    </li>
                    <p class="p-0 m-0">Karena sifat eksperimental dari fitur ini, kami berhak untuk melakukan perubahan atau penghentian fitur AI ini kapan saja tanpa pemberitahuan sebelumnya.</p>
                </ol>
            </div>
        </div>



    </body>
</html>