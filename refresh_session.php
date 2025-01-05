<?php
// refresh_session.php
session_start();
require_once 'koneksi.php';

header('Content-Type: application/json');

// Terima data dari request
$input = json_decode(file_get_contents('php://input'), true);
$token = $input['token'] ?? null;

if (!$token) {
    echo json_encode(['success' => false, 'message' => 'Token tidak valid']);
    exit;
}

// Validasi token dengan database
$stmt = $conn->prepare("SELECT id, username FROM users WHERE auth_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Session tidak valid']);
    exit;
}

$user = $result->fetch_assoc();

// Generate token baru
$newToken = md5(uniqid($user['username'] . time(), true));
$expireTime = time() + (86400 * 30); // 30 hari

// Update token di database
$updateStmt = $conn->prepare("UPDATE users SET auth_token = ? WHERE id = ?");
$updateStmt->bind_param("si", $newToken, $user['id']);
$updateStmt->execute();

// Perbarui session
$_SESSION['user_id'] = $user['id'];
$_SESSION['logged_in'] = true;
$_SESSION['auth_token'] = $newToken;
$_SESSION['expire_time'] = $expireTime;

// Siapkan data untuk response
$userData = [
    'user_id' => $user['id'],
    'username' => $user['username'],
    'token' => $newToken,
    'expires' => $expireTime * 1000 // Konversi ke milliseconds untuk JavaScript
];

echo json_encode([
    'success' => true,
    'userData' => $userData
]);