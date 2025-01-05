<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
require_once 'koneksi.php';

try {
    // Get parameters
    $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
    $month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
    
    // Format month with leading zero
    $month = str_pad($month, 2, '0', STR_PAD_LEFT);
    
    // Get first and last day of month
    $start_date = "$year-$month-01";
    $end_date = date('Y-m-t', strtotime($start_date));
    
    // Get weeks in month
    $weeks = [];
    $current = strtotime($start_date);
    $end = strtotime($end_date);
    
    while ($current <= $end) {
        $week_start = date('Y-m-d', $current);
        $week_end = date('Y-m-d', strtotime('next saturday', $current));
        
        if (strtotime($week_end) > $end) {
            $week_end = $end_date;
        }
        
        $weeks[] = [
            'start' => $week_start,
            'end' => $week_end,
            'label' => 'Minggu ' . (count($weeks) + 1)
        ];
        
        $current = strtotime('next sunday', $current);
    }
    
    // Query untuk mendapatkan data per minggu
    $query = "SELECT 
                DATE(tanggal) as date,
                status,
                COUNT(*) as count
              FROM datang 
              WHERE tanggal BETWEEN ? AND ?
                AND status != 'pulang'
              GROUP BY DATE(tanggal), status
              ORDER BY date";
              
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param('ss', $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Initialize data arrays
        $weekly_data = [];
        foreach ($weeks as $week) {
            $weekly_data[$week['start']] = [
                'tepat_waktu' => 0,
                'terlambat' => 0
            ];
        }
        
        // Process data
        while ($row = $result->fetch_assoc()) {
            foreach ($weeks as $week) {
                if ($row['date'] >= $week['start'] && $row['date'] <= $week['end']) {
                    if ($row['status'] == 'tepat waktu') {
                        $weekly_data[$week['start']]['tepat_waktu'] += (int)$row['count'];
                    } else if ($row['status'] == 'terlambat') {
                        $weekly_data[$week['start']]['terlambat'] += (int)$row['count'];
                    }
                    break;
                }
            }
        }
        
        // Format data for chart
        $chart_data = [
            'weeks' => array_column($weeks, 'label'),
            'onTime' => array_column($weekly_data, 'tepat_waktu'),
            'late' => array_column($weekly_data, 'terlambat')
        ];
        
        echo json_encode([
            'success' => true,
            'weeks' => array_column($weeks, 'label'),
            'onTime' => array_column($weekly_data, 'tepat_waktu'),
            'late' => array_column($weekly_data, 'terlambat'),
            'dateRange' => [
                'start' => $start_date,
                'end' => $end_date
            ]
        ]);
    } else {
        throw new Exception("Failed to prepare statement");
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>