<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$user_id = $_SESSION['user_id'];

try {
    // Mulai transaction untuk memastikan kedua operasi berhasil
    $conn->begin_transaction();

    // Hapus pencapaian user
    $query_del_pencapaian = "DELETE FROM pencapaian_user WHERE user_id = ?";
    $stmt = $conn->prepare($query_del_pencapaian);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Hapus poin user
    $query_del_poin = "DELETE FROM poin_user WHERE user_id = ?";
    $stmt = $conn->prepare($query_del_poin);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Commit transaction
    $conn->commit();
    
    echo "Poin berhasil direset!";

} catch (Exception $e) {
    // Rollback jika terjadi error
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>