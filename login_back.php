<?php
session_start();
include 'koneksi.php';

// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Debug login
    error_log("Attempting login for user: " . $username);
    
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        if ($password === $row['password']) {
            // Bersihkan session lama
            session_unset();
            session_destroy();
            
            // Mulai session baru
            session_start();
            
            // Set session baru
            $_SESSION = array(
                'user_id' => (int)$row['id'],
                'logged_in' => true,
                'login_time' => time()
            );
            
            session_write_close();

            // Set token dan cookies
            $token = md5(uniqid($username . time(), true));
            setcookie('authToken', $token, [
                'expires' => time() + (86400 * 30),
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            
            // Debug logs
            error_log("Cookies set = authToken: " . $token);
            error_log("New session created - ID: " . session_id());
            error_log("Session contents: " . print_r($_SESSION, true));
            
            // Redirect berdasarkan username
            if ($username === 'fauzinugroho') {
                header("Location: beranda_superUser.php");
                exit();
            } else {
                header("Location: beranda.php");
                exit();
            }
        } else {
            $_SESSION["error"] = "password_wrong";
            $_SESSION["alertLupaPassJudul"] = "Password Salah";
            $_SESSION["alertLupaPassDesc"] = "Password yang Anda masukkan salah, cek kembali.";
            header("Location: index.php");
            exit();
        }
    } else {
        $_SESSION["error"] = "username_wrong";
        $_SESSION["alertUsernameJudul"] = "Username Salah";
        $_SESSION["alertUsernameDesc"] = "Kami lihat username Anda tidak benar, cek kembali.";
        header("Location: index.php");
        exit();
        }
}

// Jika bukan POST request, redirect ke login
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: login.php");
    exit();
}
?>