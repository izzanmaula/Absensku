<?php
include 'koneksi.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=database_export.csv');

// Membuka output untuk menulis
$output = fopen('php://output', 'w');

// Cek apakah output bisa dibuka
if ($output === false) {
    die('Gagal membuat file di server');
}

// Mendapatkan daftar semua tabel dalam database
$tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

// Loop melalui setiap tabel dan ekspor datanya
foreach ($tables as $table) {
    // Tulis header tabel ke CSV
    $columnsResult = $conn->query("SHOW COLUMNS FROM $table");
    $columns = [];
    while ($col = $columnsResult->fetch_array()) {
        $columns[] = $col[0]; // Kolom nama
    }
    fputcsv($output, $columns); // Tulis header kolom

    // Ambil data dari tabel
    $dataResult = $conn->query("SELECT * FROM $table");
    while ($dataRow = $dataResult->fetch_assoc()) {
        fputcsv($output, $dataRow); // Tulis data
    }
    fputcsv($output, []); // Tulis baris kosong antara tabel
}

// Tutup koneksi database
fclose($output);
$conn->close();
exit();
?>