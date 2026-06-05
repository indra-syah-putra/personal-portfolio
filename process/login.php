<?php
require_once __DIR__ . '/../includes/functions.php';

session_start();

// Validasi CSRF
$token = $_POST['csrf_token'] ?? '';
if (!validate_csrf($token)) {
    die('CSRF validation failed.');
}

// Rate limiting: max 5 attempts per 15 menit per IP
$rate_file = __DIR__ . '/../data/login_attempts.json';
$max_attempts = 5;
$lockout_time = 900;
$ip = $_SERVER['REMOTE_ADDR'];
$now = time();

$attempts = [];
if (file_exists($rate_file)) {
    $content = @file_get_contents($rate_file);
    $attempts = $content ? json_decode($content, true) : [];
    if (!is_array($attempts)) $attempts = [];
}

// Hapus entry yang expired
$attempts = array_values(array_filter($attempts, function($a) use ($now, $lockout_time) {
    return ($now - $a['time']) < $lockout_time;
}));

// Hitung percobaan dari IP ini dalam window
$ip_attempts = array_filter($attempts, function($a) use ($ip) {
    return $a['ip'] === $ip;
});

if (count($ip_attempts) >= $max_attempts) {
    $wait = $lockout_time - ($now - $ip_attempts[0]['time']);
    $menit = ceil($wait / 60);
    die("Terlalu banyak percobaan login. Coba lagi dalam $menit menit.");
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if ($username === ADMIN_USER && password_verify($password, ADMIN_PASS_HASH)) {
    // Reset attempts pada sukses login
    $attempts = array_values(array_filter($attempts, function($a) use ($ip) {
        return $a['ip'] !== $ip;
    }));
    file_put_contents($rate_file, json_encode($attempts), LOCK_EX);

    session_regenerate_id(true);
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['last_activity'] = time();
    header('Location: ../admin.php');
    exit;
} else {
    $attempts[] = ['ip' => $ip, 'time' => $now];
    file_put_contents($rate_file, json_encode($attempts), LOCK_EX);
    header('Location: ../login.php?error=true');
    exit;
}
