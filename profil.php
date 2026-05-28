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

// Handle Update Email and Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {
    $new_email = $_POST['email'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Check if current password is provided for security
    if (!$current_password) {
        $error = "Masukkan kata sandi saat ini untuk konfirmasi!";
    } elseif (!password_verify($current_password, $user['password'])) {
        $error = "Kata sandi saat ini salah!";
    } else {
        try {
            // Update email if changed
            if ($new_email && $new_email !== $user['email']) {
                $check_email = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $check_email->execute([$new_email, $_SESSION['user_id']]);
                if ($check_email->fetch()) {
                    $error = "Email sudah digunakan oleh akun lain!";
                } else {
                    $update_stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                    $update_stmt->execute([$new_email, $_SESSION['user_id']]);
                    $message = "Email berhasil diperbarui! ";
                }
            }

            // Update password if provided
            if (!$error && $new_password) {
                if ($new_password === $confirm_password) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $update_stmt->execute([$hashed_password, $_SESSION['user_id']]);
                    $message .= "Kata sandi berhasil diubah!";
                } else {
                    $error = "Konfirmasi kata sandi tidak cocok!";
                }
            }

            // If only email was changed
            if (!$error && !$new_password && $message) {
                // Message already set
            } elseif (!$error && !$message) {
                $error = "Tidak ada perubahan yang dilakukan!";
            }

            // Refresh user data
            if ($message) {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Get display role
$display_role = $user['role'];
if ($display_role === 'admin_guru') {
    $display_role = 'Guru';
} elseif ($display_role === 'kepala_sekolah') {
    $display_role = 'Kepala Sekolah';
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
                <span class="badge badge-info"><?php echo htmlspecialchars($display_role); ?></span>
            </div>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="text" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="background: var(--bg-color);">
        </div>

        <div class="form-group">
            <label>Dibuat Pada</label>
            <input type="text" value="<?php echo date('d F Y, H:i', strtotime($user['created_at'])); ?>" disabled style="background: var(--bg-color);">
        </div>
    </div>

    <div class="data-card" style="padding: 32px;">
        <h3 style="margin-bottom: 24px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">Ubah Email & Kata Sandi</h3>
        <form action="" method="POST">
            <input type="hidden" name="update_account" value="1">

            <div class="form-group">
                <label for="email">Email Baru</label>
                <input type="email" id="email" name="email" value="" placeholder="Masukan Email Baru">
            </div>

            <div class="form-group">
                <label for="current_password">Kata Sandi Saat Ini <span style="color: var(--error-text);">*</span></label>
                <input type="password" id="current_password" name="current_password" required placeholder="Masukkan kata sandi saat ini untuk konfirmasi">
            </div>

            <div class="form-group">
                <label for="new_password">Kata Sandi Baru</label>
                <input type="password" id="new_password" name="new_password" placeholder="Masukan Kata Sandi Baru">
            </div>

            <div class="form-group">
                <label for="confirm_password">Konfirmasi Kata Sandi Baru</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Konfirmasi kata sandi baru">
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="margin-top: 24px;">
                <i data-lucide="save"></i>
                <span>Simpan Perubahan</span>
            </button>
        </form>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
