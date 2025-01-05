<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
require_once 'koneksi.php';

try {
    // Get parameters
    $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
    $month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
    
    // Validasi input
    if (!is_numeric($year) || !is_numeric($month) || 
        $year < 2020 || $year > 2030 || 
        $month < 1 || $month > 12) {
        throw new Exception('Invalid date parameters');
    }
    
    // Format month with leading zero
    $month = str_pad($month, 2, '0', STR_PAD_LEFT);
    
    // Set date range for the month
    $start_date = "$year-$month-01";
    $end_date = date('Y-m-t', strtotime($start_date));

    // Query untuk mendapatkan data per 15 menit
    $query = "SELECT 
                CONCAT(
                    LPAD(HOUR(waktu_absen), 2, '0'),
                    ':',
                    LPAD(FLOOR(MINUTE(waktu_absen) / 15) * 15, 2, '0')
                ) as waktu,
                COUNT(*) as jumlah
              FROM datang 
              WHERE tanggal BETWEEN ? AND ?
                AND waktu_absen BETWEEN '06:00:00' AND '08:00:00'
                AND status != 'pulang'
              GROUP BY 
                HOUR(waktu_absen),
                FLOOR(MINUTE(waktu_absen) / 15)
              ORDER BY waktu";
              
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param('ss', $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $labels = [];
        $data = [];
        
        // Inisialisasi data untuk setiap interval 15 menit
        $current = strtotime('06:00');
        $end = strtotime('08:15');
        
        while ($current < $end) {
            $timeKey = date('H:i', $current);
            $labels[] = $timeKey;
            $data[$timeKey] = 0;
            $current += 15 * 60;
        }
        
        // Isi dengan data aktual
        while ($row = $result->fetch_assoc()) {
            if (isset($data[$row['waktu']])) {
                $data[$row['waktu']] = (int)$row['jumlah'];
            }
        }
        
        echo json_encode([
            'success' => true,
            'labels' => $labels,
            'data' => array_values($data),
            'dateRange' => [
                'start' => $start_date,
                'end' => $end_date,
                'month' => $month,
                'year' => $year
            ]
        ]);
    } else {
        throw new Exception("Failed to prepare statement");
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'errorDetails' => [
            'year' => $year,
            'month' => $month
        ]
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>