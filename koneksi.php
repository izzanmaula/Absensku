<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['SERVER_NAME'] == "localhost") {  
    // database lokal
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "absensi";
} else {
    // database online
    $servername = "localhost";
    $username = "smpp3485_admin";
    $password = "kemambuan";
    $dbname = "smpp3485_absensi";
}


// koneksi ke database
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("koneksi gagal").$conn->connect_error;
}

// Set charset
$conn->set_charset("utf8mb4");
?>