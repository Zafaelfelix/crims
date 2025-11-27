<?php
session_start();
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /crims/login/');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    $_SESSION['login_error'] = 'Username dan password wajib diisi.';
    header('Location: /crims/login/');
    exit;
}

// Query untuk mengambil id, username, password, role, dan full_name
$stmt = $mysqli->prepare('SELECT id, username, password, role, full_name FROM users WHERE username = ? LIMIT 1');
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    // Simpan data user ke session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'] ?? 'admin'; // Default ke admin jika role tidak ada
    $_SESSION['full_name'] = $user['full_name'] ?? $user['username'];
    
    // Redirect sesuai role
    $role = strtolower($user['role'] ?? 'admin');
    
    switch ($role) {
        case 'admin':
            header('Location: /crims/admin/dashboard.php');
            break;
        case 'dosen':
            header('Location: /crims/dosen/dashboard.php');
            break;
        case 'mahasiswa':
            header('Location: /crims/mahasiswa/dashboard.php');
            break;
        default:
            // Jika role tidak dikenal, redirect ke admin (fallback)
            header('Location: /crims/admin/dashboard.php');
            break;
    }
    exit;
}

$_SESSION['login_error'] = 'Username atau password salah.';
header('Location: /crims/login/');
exit;

