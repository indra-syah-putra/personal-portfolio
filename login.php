<?php
require_once 'includes/functions.php';
session_start();

// Jika sudah login, otomatis lempar ke Dashboard Admin
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Myportofolio</title>
    <style>
        /* CSS Khusus untuk Halaman Login ini saja */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6; /* Abu-abu muda */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-card {
            background: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 350px;
            text-align: center;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .alert {
            background: #fee2e2;
            color: #991b1b;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 14px;
            color: #555;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 14px;
        }

        input:focus {
            border-color: #2563eb;
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #2563eb;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background-color: #1d4ed8;
        }

        .hint {
            margin-top: 20px;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <h2>Admin Login</h2>

        <!-- Tampilkan pesan error jika ada -->
        <?php if(isset($_GET['error'])): ?>
            <div class="alert">
                Username atau Password Salah!
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['timeout'])): ?>
            <div class="alert" style="background:#fef3c7;color:#92400e;">
                Sesi berakhir. Silakan login kembali.
            </div>
        <?php endif; ?>

        <!-- Form Login -->
        <form action="process/login.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf() ?>">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan username" required autofocus>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>

            <button type="submit">MASUK DASHBOARD</button>
        </form>

        <p class="hint">Masukkan Username dan Password dengan sesuai</p>
        
        <!-- Kembali ke Beranda -->
        <div style="margin-top: 15px;">
            <a href="index.php" style="font-size: 13px; color: #2563eb; text-decoration: none;">&larr; Kembali ke Beranda</a>
        </div>
    </div>

</body>
</html>