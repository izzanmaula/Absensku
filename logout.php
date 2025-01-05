<?php
session_start();
require_once 'koneksi.php';

// 1. Hapus token dari database
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("UPDATE users SET auth_token = NULL WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
}

// 2. Hapus cookie
setcookie('auth_token', '', time() - 3600, '/');
setcookie('auth_token', '', time() - 3600, '/', '', true, true);

// 3. Hapus session
session_unset();
session_destroy();

// 4. Redirect langsung ke index tanpa membuat session baru
header("Location: index.php?action=logout");
exit();
?>