<?php
require "koneksi.php";
session_start();

// Cek apakah ada data yang dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $namaLengkap = mysqli_real_escape_string($conn, $_POST['namaLengkap']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Query insert
    $sql = "INSERT INTO users (username, password, namaLengkap, status) 
            VALUES ('$username', '$hashed_password', '$namaLengkap', '$status')";
    
    // Eksekusi query
    if ($conn->query($sql) === TRUE) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil disimpan'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error: ' . $conn->error
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Method tidak diizinkan'
    ]);
}

$conn->close();
?>