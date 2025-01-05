<?php
session_start();
require 'koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Pastikan ada ID yang dikirim
if (!isset($_POST['id']) || empty($_POST['id'])) {
    $_SESSION['error'] = "ID absensi tidak valid!";
    header("Location: data_absensi.php");
    exit();
}

$id = $_POST['id'];
$user_id = $_SESSION['user_id'];

// Persiapkan query untuk mengecek kepemilikan data dan menghapus
$query = "DELETE FROM datang WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id, $user_id);

// Eksekusi query
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Hapus foto jika ada
        $query_foto = "SELECT foto FROM datang WHERE id = ? AND user_id = ?";
        $stmt_foto = $conn->prepare($query_foto);
        $stmt_foto->bind_param("ii", $id, $user_id);
        $stmt_foto->execute();
        $result = $stmt_foto->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if (!empty($row['foto']) && file_exists($row['foto'])) {
                unlink($row['foto']);
            }
        }
        
        $_SESSION['success'] = "Data absensi berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Data absensi tidak ditemukan atau Anda tidak memiliki akses!";
    }
} else {
    $_SESSION['error'] = "Gagal menghapus data absensi!";
}

// Tutup statement dan koneksi
$stmt->close();
if (isset($stmt_foto)) $stmt_foto->close();
$conn->close();

// Redirect kembali ke halaman data absensi
header("Location: kehadiran_lengkap.php");
exit();
?>