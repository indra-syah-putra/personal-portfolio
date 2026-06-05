<?php
require_once __DIR__ . '/../config.php';

function baca_json() {
    if (!file_exists(DATA_PATH)) {
        $default = [
            'site_settings' => [
                'hero_title' => 'Mahasiswa SI & Tech Enthusiast',
                'hero_desc' => 'Selamat datang di blog pribadiku.',
                'hero_img' => 'https://picsum.photos/seed/indratech/400/400',
                'about_bio' => 'Halo! Saya Indra Syah Putra.',
                'skills' => [],
                'contact_title' => 'Hubungi Saya',
                'contact_desc' => '',
                'contact_email' => '',
                'contact_insta' => '',
                'contact_github' => '',
            'categories' => ['Komputer', 'Tutorial', 'Tips'],
            'gallery' => [],
            'tech_skills' => []
        ],
            'projects' => [],
            'articles' => [],
            'messages' => []
        ];
        tulis_json($default);
        return $default;
    }

    $max_attempts = 5;
    $attempt = 0;
    $file = null;

    while ($attempt < $max_attempts) {
        $file = @fopen(DATA_PATH, 'r');
        if (!$file) return false;

        if (flock($file, LOCK_SH | LOCK_NB)) {
            break;
        }
        fclose($file);
        $file = null;
        $attempt++;
        usleep(200000);
    }
    if (!$file) return false;

    $content = filesize(DATA_PATH) > 0 ? fread($file, filesize(DATA_PATH)) : '{}';
    flock($file, LOCK_UN);
    fclose($file);

    $data = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE) return false;

    return $data;
}

function tulis_json($data) {
    $backup_dir = dirname(DATA_PATH) . '/backups';
    if (!is_dir($backup_dir)) {
        @mkdir($backup_dir, 0755, true);
    }
    if (file_exists(DATA_PATH)) {
        $backup_file = $backup_dir . '/data_' . date('Y-m-d_His') . '.json';
        @copy(DATA_PATH, $backup_file);
        $backups = glob($backup_dir . '/data_*.json');
        if (count($backups) > 5) {
            usort($backups, function($a, $b) { return filemtime($a) - filemtime($b); });
            foreach (array_slice($backups, 0, count($backups) - 5) as $old) {
                @unlink($old);
            }
        }
    }

    $max_attempts = 5;
    $attempt = 0;
    $file = null;

    while ($attempt < $max_attempts) {
        $file = @fopen(DATA_PATH, 'w');
        if (!$file) return false;

        if (flock($file, LOCK_EX | LOCK_NB)) {
            break;
        }
        fclose($file);
        $file = null;
        $attempt++;
        usleep(200000);
    }
    if (!$file) return false;

    fwrite($file, json_encode($data, JSON_PRETTY_PRINT));
    fflush($file);
    flock($file, LOCK_UN);
    fclose($file);

    return true;
}

function cek_admin() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
    // Session timeout check
    $now = time();
    $last_activity = $_SESSION['last_activity'] ?? $now;
    if (($now - $last_activity) > SESSION_TIMEOUT) {
        session_destroy();
        header('Location: login.php?timeout=1');
        exit;
    }
    $_SESSION['last_activity'] = $now;
}

function set_flash($type, $message) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function generate_csrf() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function validate_url($url) {
    if (empty($url)) return true;
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function baca_messages() {
    if (!file_exists(MESSAGES_PATH)) {
        tulis_messages([]);
        return [];
    }
    $file = @fopen(MESSAGES_PATH, 'r');
    if (!$file) return [];
    flock($file, LOCK_SH);
    $content = filesize(MESSAGES_PATH) > 0 ? fread($file, filesize(MESSAGES_PATH)) : '[]';
    flock($file, LOCK_UN);
    fclose($file);
    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

function tulis_messages($messages) {
    $file = @fopen(MESSAGES_PATH, 'w');
    if (!$file) return false;
    flock($file, LOCK_EX);
    fwrite($file, json_encode($messages, JSON_PRETTY_PRINT));
    fflush($file);
    flock($file, LOCK_UN);
    fclose($file);
    return true;
}

function upload_gambar($file, $folder = 'uploads') {
    $target_dir = __DIR__ . '/../' . $folder . '/';
    if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    if (!in_array($ext, $allowed)) return false;

    $filename = uniqid('img_') . '.' . $ext;
    $dest = $target_dir . $filename;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        compress_gambar($dest, $ext);
        return $folder . '/' . $filename;
    }
    return false;
}

function compress_gambar($path, $ext) {
    if (!function_exists('imagecreatefromjpeg')) return;
    $max_w = 1200;
    $max_h = 1200;
    $quality = 80;

    if ($ext === 'gif' || $ext === 'svg' || $ext === 'webp') return;

    $src = $ext === 'png' ? @imagecreatefrompng($path) : @imagecreatefromjpeg($path);
    if (!$src) return;

    $ow = imagesx($src);
    $oh = imagesy($src);
    $w = $ow;
    $h = $oh;
    if ($w > $max_w || $h > $max_h) {
        $ratio = min($max_w / $w, $max_h / $h);
        $w = (int)($w * $ratio);
        $h = (int)($h * $ratio);
    }
    if ($w === $ow && $h === $oh && $ext === 'jpg') {
        $ext = 'jpeg';
    }
    $dst = imagecreatetruecolor($w, $h);
    if ($ext === 'png') {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, $ow, $oh);

    $ext === 'png' ? imagepng($dst, $path, 6) : imagejpeg($dst, $path, $quality);
    imagedestroy($src);
    imagedestroy($dst);
}

function paginate($items, $per_page = 10, $param = 'pg') {
    $page = max(1, (int)($_GET[$param] ?? 1));
    $total = count($items);
    $total_pages = max(1, ceil($total / $per_page));
    $offset = ($page - 1) * $per_page;
    return [
        'items' => array_slice($items, $offset, $per_page),
        'page' => $page,
        'total_pages' => $total_pages,
        'total' => $total,
        'offset' => $offset
    ];
}

function render_pagination($total_pages, $page, $base_url, $param = 'pg') {
    if ($total_pages <= 1) return;
    $sep = strpos($base_url, '?') === false ? '?' : '&';
    echo '<div style="padding:0.75rem 1rem;display:flex;justify-content:center;align-items:center;gap:0.35rem;border-top:1px solid #e2e8f0;">';
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = $i === $page;
        echo '<a href="' . $base_url . $sep . $param . '=' . $i . '" style="';
        echo 'display:inline-block;padding:4px 10px;border-radius:6px;text-decoration:none;font-size:0.8rem;font-weight:600;';
        echo $active ? 'background:#2563eb;color:#fff;"' : 'background:#f1f5f9;color:#475569;"';
        echo '>' . $i . '</a>';
    }
    echo '</div>';
}
