<?php
require_once __DIR__ . '/../includes/functions.php';
cek_admin();
$_SESSION['last_activity'] = time();
session_write_close();
echo 'ok';