<?php
header('Content-Type: application/json');
require_once 'koneksi.php';

$debug_log = [];  // Array untuk menyimpan log debug

function addDebugLog($step, $info) {
    global $debug_log;
    $debug_log[] = ['step' => $step, 'info' => $info];
}

try {
    addDebugLog('Start', 'Memulai proses');
    
    // Set timezone
    date_default_timezone_set('Asia/Jakarta');
    addDebugLog('Timezone', 'Set ke Asia/Jakarta');
    
    // Cek koneksi database
    if ($conn->connect_error) {
        addDebugLog('Database', 'Koneksi gagal: ' . $conn->connect_error);
        throw new Exception('Koneksi database gagal');
    }
    addDebugLog('Database', 'Koneksi berhasil');
    
    // Query sederhana untuk melihat data
    $query = "SELECT tanggal, waktu_absen, status FROM datang ORDER BY tanggal DESC, waktu_absen DESC LIMIT 10";
    addDebugLog('Query', $query);
    
    $result = $conn->query($query);
    if (!$result) {
        addDebugLog('Query Execution', 'Query gagal: ' . $conn->error);
        throw new Exception('Query gagal');
    }
    
    $sample_data = [];
    while ($row = $result->fetch_assoc()) {
        $sample_data[] = $row;
    }
    addDebugLog('Sample Data', $sample_data);
    
    // Cek jumlah total data
    $count_query = "SELECT COUNT(*) as total FROM datang";
    $count_result = $conn->query($count_query);
    $total_records = $count_result->fetch_assoc()['total'];
    addDebugLog('Total Records', $total_records);
    
    // Cek distinct tanggal
    $dates_query = "SELECT DISTINCT tanggal FROM datang ORDER BY tanggal DESC LIMIT 5";
    $dates_result = $conn->query($dates_query);
    $distinct_dates = [];
    while ($row = $dates_result->fetch_assoc()) {
        $distinct_dates[] = $row['tanggal'];
    }
    addDebugLog('Recent Dates', $distinct_dates);
    
    // Cek format waktu yang tersimpan
    $time_query = "SELECT DISTINCT waktu_absen FROM datang ORDER BY waktu_absen LIMIT 5";
    $time_result = $conn->query($time_query);
    $time_samples = [];
    while ($row = $time_result->fetch_assoc()) {
        $time_samples[] = $row['waktu_absen'];
    }
    addDebugLog('Time Samples', $time_samples);
    
    // Cek status yang ada
    $status_query = "SELECT DISTINCT status FROM datang";
    $status_result = $conn->query($status_query);
    $status_types = [];
    while ($row = $status_result->fetch_assoc()) {
        $status_types[] = $row['status'];
    }
    addDebugLog('Status Types', $status_types);
    
    // Coba query dengan rentang waktu spesifik (jam 6-12)
    $time_range_query = "SELECT 
        waktu_absen,
        COUNT(*) as count
    FROM datang
    WHERE waktu_absen >= '06:00' 
    AND waktu_absen <= '12:00'
    AND status != 'pulang'
    GROUP BY waktu_absen
    ORDER BY waktu_absen";
    
    $time_range_result = $conn->query($time_range_query);
    $time_range_data = [];
    if ($time_range_result) {
        while ($row = $time_range_result->fetch_assoc()) {
            $time_range_data[] = $row;
        }
    }
    addDebugLog('Time Range Data', [
        'query' => $time_range_query,
        'data' => $time_range_data
    ]);
    
    // Return semua informasi debug
    echo json_encode([
        'success' => true,
        'debug_log' => $debug_log,
        'connection_info' => [
            'server_info' => $conn->server_info,
            'host_info' => $conn->host_info,
            'protocol_version' => $conn->protocol_version
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    addDebugLog('Error', $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug_log' => $debug_log
    ], JSON_PRETTY_PRINT);
} finally {
    if (isset($conn)) {
        $conn->close();
        addDebugLog('Cleanup', 'Database connection closed');
    }
}
?>