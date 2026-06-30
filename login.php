<?php
require_once 'config/db.php';
session_start();

// If already logged in, redirect to beranda
if (isset($_SESSION['user_id'])) {
    header("Location: beranda.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['role'] = $user['role'];
            header("Location: beranda.php");
            exit();
        } else {
            $error = 'Email atau password salah!';
        }
    } else {
        $error = 'Semua field wajib diisi!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Al-Falah System</title>
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="login-container">
    <div class="login-box">
        <div class="login-header">
            <div class="logo-img" style="margin-bottom: 16px;">
                <img src="assets/logo.png" alt="Al-Falah Logo" style="height: 80px; width: auto;">
            </div>
            <h2>Al-Falah System</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 8px;">Silakan masuk ke akun Anda</p>
        </div>

        <?php if ($error): ?>
            <div style="background: var(--error-bg); color: var(--error-text); padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="admin@alfalah.com" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">
                <span>Masuk Sekarang</span>
                <i data-lucide="arrow-right" size="18"></i>
            </button>
        </form>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>