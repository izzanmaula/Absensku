<?php
session_start();
require"koneksi.php";
class TeacherScheduleManager {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    // Mengambil semua data guru beserta jadwalnya
    public function getAllTeachersSchedule() {
        $query = "SELECT id, namaLengkap, jam_datang, jam_pulang FROM users ORDER BY namaLengkap";
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Update jadwal guru
    public function updateTeacherSchedule($id, $jam_datang, $jam_pulang) {
        $query = "UPDATE users SET jam_datang = ?, jam_pulang = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssi", $jam_datang, $jam_pulang, $id);
        return $stmt->execute();
    }
}

// File: schedule_interface.php
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manajemen Jadwal Guru</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .form-group { margin: 10px 0; }
    </style>
</head>
<body>
    <h2>Manajemen Jadwal Guru</h2>
    
    <!-- Form Tambah/Update Jadwal -->
    <form action="process_schedule.php" method="POST">
        <div class="form-group">
            <label>Pilih Guru:</label>
            <select name="teacher_id" required>
                <?php
                $teachers = $scheduleManager->getAllTeachersSchedule();
                foreach($teachers as $teacher) {
                    echo "<option value='{$teacher['id']}'>{$teacher['namaLengkap']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label>Jam Datang:</label>
            <input type="time" name="jam_datang" required>
        </div>
        <div class="form-group">
            <label>Jam Pulang:</label>
            <input type="time" name="jam_pulang" required>
        </div>
        <button type="submit">Simpan Jadwal</button>
    </form>

    <!-- Tabel Jadwal Guru -->
    <h3>Daftar Jadwal Guru</h3>
    <table>
        <tr>
            <th>Nama Guru</th>
            <th>Jam Datang</th>
            <th>Batas Tepat Waktu</th>
            <th>Jam Pulang</th>
            <th>Aksi</th>
        </tr>
        <?php foreach($teachers as $teacher): ?>
            <tr>
                <td><?= htmlspecialchars($teacher['namaLengkap']) ?></td>
                <td><?= $teacher['jam_datang'] ?></td>
                <td><?= date('H:i:s', strtotime('-1 hour', strtotime($teacher['jam_datang']))) ?></td>
                <td><?= $teacher['jam_pulang'] ?></td>
                <td>
                    <button onclick="editSchedule(<?= $teacher['id'] ?>)">Edit</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>

<?php
// File: process_schedule.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_POST['teacher_id'];
    $jam_datang = $_POST['jam_datang'];
    $jam_pulang = $_POST['jam_pulang'];
    
    $db = new mysqli('localhost', 'smpp3485_admin', 'kemambuan', 'smpp3485_absensi');
    $scheduleManager = new TeacherScheduleManager($db);
    
    if ($scheduleManager->updateTeacherSchedule($teacher_id, $jam_datang, $jam_pulang)) {
        header('Location: schedule_interface.php?success=1');
    } else {
        header('Location: schedule_interface.php?error=1');
    }
}
?>

<?php
// File: check_attendance.php
class AttendanceChecker {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    public function checkStatus($user_id, $current_time = null) {
        if ($current_time === null) {
            $current_time = date('H:i:s');
        }
        
        // Ambil jadwal guru
        $query = "SELECT jam_datang, jam_pulang FROM users WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $schedule = $stmt->get_result()->fetch_assoc();
        
        // Hitung batas waktu tepat waktu (1 jam sebelum jam datang)
        $tolerance_start = date('H:i:s', strtotime('-1 hour', strtotime($schedule['jam_datang'])));
        
        // Tentukan status
        if ($current_time >= $tolerance_start && $current_time <= $schedule['jam_datang']) {
            return 'tepat_waktu';
        } elseif ($current_time > $schedule['jam_datang'] && $current_time < $schedule['jam_pulang']) {
            return 'terlambat';
        } else {
            return 'pulang';
        }
    }
    
    public function recordAttendance($user_id) {
        $current_time = date('H:i:s');
        $status = $this->checkStatus($user_id, $current_time);
        
        $query = "INSERT INTO datang (user_id, waktu, status) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iss", $user_id, $current_time, $status);
        
        return $stmt->execute();
    }
}
?>