<?php 
include 'koneksi.php';

// Set header untuk menerima JSON
header('Content-Type: application/json');

// Debug: Cek raw data yang diterima
$raw_data = file_get_contents('php://input');
error_log("Received data: " . $raw_data); // Untuk debugging

// Decode JSON
$data = json_decode($raw_data, true);

// Debug: Cek hasil decode
error_log("Decoded data: " . print_r($data, true)); // Untuk debugging

// Validasi data
if (!$data) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON data: ' . json_last_error_msg()
    ]);
    exit;
}

// Ambil data dengan pengecekan null
$user_id = $data['user_id'] ?? null;
$perizinan = $data['perizinan'] ?? null;
$tanggal_mulai = $data['tanggal_mulai'] ?? null;
$tanggal_selesai = $data['tanggal_selesai'] ?? null;
$keterangan = $data['keterangan'] ?? null;

// Validasi data yang diperlukan
if (!$user_id || !$perizinan || !$tanggal_mulai || !$tanggal_selesai || !$keterangan) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Semua field harus diisi'
    ]);
    exit;
}

try {
    // Query untuk insert data
    $query = "INSERT INTO ijin (user_id, perizinan, tanggal_mulai, tanggal_selesai, keterangan) 
              VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query); // Menggunakan $conn bukan $mysqli
    
    if ($stmt) {
        $stmt->bind_param("issss", $user_id, $perizinan, $tanggal_mulai, $tanggal_selesai, $keterangan);
        
        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Perizinan berhasil diajukan'
            ]);
        } else {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
    } else {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();