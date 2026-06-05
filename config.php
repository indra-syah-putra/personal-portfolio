<?php
// Konfigurasi Login Admin
define('ADMIN_USER', 'admin');
define('ADMIN_PASS_HASH', '$2y$10$.sh5i/5jdAw6zIX.N3waI.s8HwT0CN7mhiJa4vN2Y7aj6ecf6uWb.');

// Path data JSON
define('DATA_PATH', __DIR__ . '/data/data.json');
define('MESSAGES_PATH', __DIR__ . '/data/messages.json');

// Session timeout (detik) — 30 menit
define('SESSION_TIMEOUT', 1800);
define('SESSION_WARNING', 120); // peringatan 2 menit sebelum habis
