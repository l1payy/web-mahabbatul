<?php
require_once 'includes/auth.php';
require_once 'config/db.php';

$page_title = 'Profil Pengguna';
$current_page = 'profil.php';

$success = '';
$error = '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $password_lama = $_POST['password_lama'] ?? '';
    $password_baru = $_POST['password_baru'] ?? '';
    $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';

    // Validasi nama dan email
    if (!$nama || !$email) {
        $error = 'Nama dan email wajib diisi!';
    } else {
        // Cek apakah email sudah dipakai oleh user lain
        $stmtCheckEmail = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmtCheckEmail->execute([$email, $_SESSION['user_id']]);
        if ($stmtCheckEmail->fetch()) {
            $error = 'Email sudah dipakai oleh akun lain!';
        } else {
            // Update nama dan email
            $stmtUpdate = $pdo->prepare("UPDATE users SET nama = ?, email = ? WHERE id = ?");
            $stmtUpdate->execute([$nama, $email, $_SESSION['user_id']]);
            
            // Update session nama
            $_SESSION['nama'] = $nama;

            // Jika ingin ganti password
            if ($password_lama || $password_baru || $konfirmasi_password) {
                if (!password_verify($password_lama, $user['password'])) {
                    $error = 'Password lama salah!';
                } elseif ($password_baru !== $konfirmasi_password) {
                    $error = 'Konfirmasi password tidak sesuai!';
                } elseif (strlen($password_baru) < 6) {
                    $error = 'Password baru minimal 6 karakter!';
                } else {
                    $hashedPassword = password_hash($password_baru, PASSWORD_DEFAULT);
                    $stmtUpdatePassword = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmtUpdatePassword->execute([$hashedPassword, $_SESSION['user_id']]);
                    $success = 'Profil dan password berhasil diperbarui!';
                }
            } else {
                $success = 'Profil berhasil diperbarui!';
            }
            
            // Refresh data user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
        }
    }
}

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<header class="page-header">
    <div class="header-title">
        <h2>Profil Saya</h2>
        <p>Ubah informasi akun Anda.</p>
    </div>
</header>

<?php if ($success): ?>
    <div style="background: var(--success-bg); color: var(--success-text); padding: 12px 24px; border-radius: 8px; margin-bottom: 24px; font-weight: 600;">
        <?php echo $success; ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div style="background: var(--error-bg); color: var(--error-text); padding: 12px 24px; border-radius: 8px; margin-bottom: 24px; font-weight: 600;">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="data-card" style="max-width: 700px; padding: 32px;">
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

    <form action="" method="POST">
        <div class="form-group">
            <label for="nama">Nama</label>
            <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        
        <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 32px 0;">
        
        <h4 style="margin-bottom: 24px;">Ganti Password (Opsional)</h4>
        
        <div class="form-group">
            <label for="password_lama">Password Lama</label>
            <input type="password" id="password_lama" name="password_lama" placeholder="Ketik password lama Anda">
        </div>
        
        <div class="form-group">
            <label for="password_baru">Password Baru</label>
            <input type="password" id="password_baru" name="password_baru" placeholder="Password baru (min 6 karakter)">
        </div>
        
        <div class="form-group">
            <label for="konfirmasi_password">Konfirmasi Password Baru</label>
            <input type="password" id="konfirmasi_password" name="konfirmasi_password" placeholder="Ketik ulang password baru">
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 24px;">
            <i data-lucide="save"></i>
            <span>Simpan Perubahan</span>
        </button>
    </form>
</div>

<?php 
require_once 'includes/footer.php';
?>
