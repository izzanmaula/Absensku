<?php
ob_start();
session_start();
include 'koneksi.php';

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Get period from request, default to 7 days
$period = isset($_GET['period']) ? intval($_GET['period']) : 7;

// Validate period
if (!in_array($period, [7, 14, 30])) {
    $period = 7;
}

// Get total users (excluding admin)
$total_query = "SELECT COUNT(*) as total FROM users WHERE id != '1'";
$total_result = $conn->query($total_query);
$total_users = $total_result->fetch_assoc()['total'];

// Get dates for the selected period
$dates = array();
$labels = array();
for ($i = ($period - 1); $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dates[] = $date;
    $labels[] = date('D, d M', strtotime("-$i days"));
}

$data = array();
foreach ($dates as $date) {
    // Query untuk tepat waktu
    $ontime_query = "SELECT COUNT(*) as count FROM datang 
                    WHERE tanggal = '$date' 
                    AND status = 'tepat waktu'";
    
    // Query untuk terlambat
    $late_query = "SELECT COUNT(*) as count FROM datang 
                  WHERE tanggal = '$date' 
                  AND status = 'terlambat'";
    
    $ontime_result = $conn->query($ontime_query);
    $late_result = $conn->query($late_query);
    
    if ($ontime_result && $late_result) {
        $ontime_count = (int)$ontime_result->fetch_assoc()['count'];
        $late_count = (int)$late_result->fetch_assoc()['count'];
        
        // Hitung yang tidak hadir
        $absent_count = $total_users - ($ontime_count + $late_count);
        
        $data[] = array(
            'date' => $labels[array_search($date, $dates)],
            'tepatWaktu' => $ontime_count,
            'terlambat' => $late_count,
            'tidakHadir' => $absent_count
        );
    }
}

// Clear any output buffers
ob_clean();

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'data' => $data,
    'period' => $period
]);

// Close connection
$conn->close();
exit();
?>