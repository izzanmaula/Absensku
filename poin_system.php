<?php
// Fungsi untuk mengecek dan menambah poin
function tambahPoin($user_id, $status, $conn) {
    // Tentukan poin berdasarkan status
    $poin = 0;
    switch($status) {
        case 'tepat waktu':
            $poin = 10; // User mendapat 10 poin jika tepat waktu
            break;
        case 'terlambat':
            $poin = 2; // User mendapat 2 poin meski terlambat (tetap dihargai karena hadir)
            break;
        default:
            $poin = 0;
    }

    // Cek apakah user sudah ada di tabel poin_user
    $query = "SELECT * FROM poin_user WHERE user_id = $user_id";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) > 0) {
        // Update poin jika user sudah ada
        $query = "UPDATE poin_user 
                  SET jumlah_poin = jumlah_poin + $poin 
                  WHERE user_id = $user_id";
        mysqli_query($conn, $query);
    } else {
        // Insert data baru jika user belum ada
        $query = "INSERT INTO poin_user (user_id, jumlah_poin, level) 
                  VALUES ($user_id, $poin, 1)";
        mysqli_query($conn, $query);
    }

    // Update level berdasarkan jumlah poin
    updateLevel($user_id, $conn);
}

// Fungsi untuk update level
function updateLevel($user_id, $conn) {
    $query = "SELECT jumlah_poin FROM poin_user WHERE user_id = $user_id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $poin = $row['jumlah_poin'];

    // Tentukan level berdasarkan poin
    $level = floor($poin / 100) + 1; // Naik level setiap 100 poin

    // Update level
    $query = "UPDATE poin_user SET level = $level WHERE user_id = $user_id";
    mysqli_query($conn, $query);
}

// Implementasi pada saat absen
// Tambahkan ini di bagian setelah menyimpan data absen
if(isset($user_id) && isset($status)) {
    tambahPoin($user_id, $status, $conn);
}
?>