<?php
require_once 'koneksi.php';
require 'config.php';

// Cek session
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

class AttendanceRepository {
    private $db;
    
    public function __construct($connection) {
        if (!$connection || !($connection instanceof mysqli)) {
            throw new Exception('Invalid database connection');
        }
        $this->db = $connection;
    }
    
    public function getAttendanceStatus($userId, $date) {
        try {
            $stmt = $this->db->prepare("SELECT status FROM datang 
                     WHERE user_id = ? AND DATE(tanggal) = ?");
            
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return null;
            }
            
            $stmt->bind_param('ss', $userId, $date);
            
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return null;
            }
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            return $row ? strtolower(trim($row['status'])) : null;
        } catch (Exception $e) {
            error_log("Database error: " . $e->getMessage());
            return null;
        }
    }
}

class CalendarHelper {
    private $month;
    private $year;
    
    public function __construct($month = null, $year = null) {
        $this->month = $month ?: date('n');
        $this->year = $year ?: date('Y');
    }
    
    public function getDaysInMonth() {
        return cal_days_in_month(CAL_GREGORIAN, $this->month, $this->year);
    }
    
    public function getMonthName() {
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return $monthNames[$this->month];
    }
    
    public function getFirstDayOfMonth() {
        return date('w', strtotime("{$this->year}-{$this->month}-01"));
    }
    
    public function isValidDate($day) {
        return checkdate($this->month, $day, $this->year);
    }
}

// Handle AJAX request
if (isset($_GET['ajax'])) {
    $month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
    $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

    try {
        $calendar = new CalendarHelper($month, $year);
        $attendanceRepo = new AttendanceRepository($conn);
        
        // Generate calendar data
        $data = [
            'month' => $calendar->getMonthName(),
            'year' => $year,
            'firstDay' => $calendar->getFirstDayOfMonth(),
            'daysInMonth' => $calendar->getDaysInMonth(),
            'attendance' => []
        ];
        
        // Get attendance data for each day
        for ($day = 1; $day <= $data['daysInMonth']; $day++) {
            if ($calendar->isValidDate($day)) {
                $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
                $data['attendance'][$day] = $attendanceRepo->getAttendanceStatus($user_id, $date);
            }
        }
        
        echo json_encode($data);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Regular initialization
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

try {
    $calendar = new CalendarHelper($month, $year);
    $attendanceRepo = new AttendanceRepository($conn);
} catch (Exception $e) {
    die("Error initializing objects: " . $e->getMessage());
}
?>