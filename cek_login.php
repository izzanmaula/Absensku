<?php
session_start();

// Fungsi untuk mengecek status login
function checkLogin() {
    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        header("Location: login.php");
        exit();
    }
}

// Simpan di file terpisah (misalnya session_check.php) dan include di setiap halaman yang perlu login
?>