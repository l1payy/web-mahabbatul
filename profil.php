<?php
require_once 'includes/auth.php';
require_once 'config/db.php';

$page_title = 'Profil Pengguna';
$current_page = 'profil.php';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($current_password && $new_password && $confirm_password) {
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->execute([$hashed_password, $_SESSION['user_id']]);
                $message = "Kata sandi berhasil diubah!";
            } else {
                $error = "Konfirmasi kata sandi tidak cocok!";
            }
        } else {
            $error = "Kata sandi saat ini salah!";
        }
    } else {
        $error = "Semua field wajib diisi!";
    }
}

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<header class="page-header">
    <div class="header-title">
        <h2>Profil Saya</h2>
        <p>Informasi detail akun Anda.</p>
    </div>
</header>

<?php if ($message): ?>
    <div style="background: var(--success-bg); color: var(--success-text); padding: 12px 24px; border-radius: 8px; margin-bottom: 24px; font-weight: 600;">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div style="background: var(--error-bg); color: var(--error-text); padding: 12px 24px; border-radius: 8px; margin-bottom: 24px; font-weight: 600;">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start;">
    <div class="data-card" style="padding: 32px;">
        <h3 style="margin-bottom: 24px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">Informasi Akun</h3>
        <div style="display: flex; align-items: center; gap: 24px; margin-bottom: 32px;">
            <div class="avatar" style="width: 80px; height: 80px; font-size: 2rem;">
                <?php 
                    $names = explode(' ', $user['nama']);
                    echo strtoupper(substr($names[0], 0, 1) . (isset($names[1]) ? substr($names[1], 0, 1) : ''));
                ?>
            </div>
            <div>
                <h3 style="font-size: 1.5rem; margin-bottom: 4px;"><?php echo htmlspecialchars($user['nama']); ?></h3>
                <span class="badge badge-info"><?php echo str_replace('_', ' ', strtoupper($user['role'])); ?></span>
            </div>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="text" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="background: var(--bg-color);">
        </div>
        
        <div class="form-group">
            <label>Role</label>
            <input type="text" value="<?php echo htmlspecialchars($user['role']); ?>" disabled style="background: var(--bg-color);">
        </div>

        <div class="form-group">
            <label>Dibuat Pada</label>
            <input type="text" value="<?php echo date('d F Y, H:i', strtotime($user['created_at'])); ?>" disabled style="background: var(--bg-color);">
        </div>
    </div>

    <div class="data-card" style="padding: 32px;">
        <h3 style="margin-bottom: 24px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">Ubah Kata Sandi</h3>
        <form action="" method="POST">
            <input type="hidden" name="change_password" value="1">
            <div class="form-group">
                <label for="current_password">Kata Sandi Saat Ini</label>
                <input type="password" id="current_password" name="current_password" required placeholder="••••••••">
            </div>
            <div class="form-group">
                <label for="new_password">Kata Sandi Baru</label>
                <input type="password" id="new_password" name="new_password" required placeholder="••••••••">
            </div>
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Kata Sandi Baru</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary btn-block" style="margin-top: 24px;">
                <i data-lucide="lock"></i>
                <span>Simpan Kata Sandi</span>
            </button>
        </form>
    </div>
</div>

<?php 
require_once 'includes/footer.php';
?>