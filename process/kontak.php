<?php
require_once __DIR__ . '/../includes/functions.php';
session_start();
session_write_close();

if (!empty($_POST['website'])) {
    header('Location: ../index.php');
    exit;
}

$nama = htmlspecialchars($_POST['nama'] ?? '');
$email = htmlspecialchars($_POST['email'] ?? '');
$pesan = htmlspecialchars($_POST['pesan'] ?? '');

if ($nama && $email && $pesan) {
    $messages = baca_messages();

    $new_message = [
        'id' => uniqid(),
        'nama' => $nama,
        'email' => $email,
        'pesan' => $pesan,
        'tanggal' => date('Y-m-d H:i:s'),
        'is_read' => false
    ];

    $messages[] = $new_message;

    session_start();
    if (tulis_messages($messages)) {
        $_SESSION['contact_status'] = 'success';
    } else {
        $_SESSION['contact_status'] = 'error';
    }
    session_write_close();
} else {
    session_start();
    $_SESSION['contact_status'] = 'error';
    session_write_close();
}

header('Location: ../kontak.php');
exit;
