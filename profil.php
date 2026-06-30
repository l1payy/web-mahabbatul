<?php
require_once 'includes/auth.php';
require_once 'config/db.php';

$page_title = 'Profil Pengguna';
$current_page = 'profil.php';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<header class="page-header">
    <div class="header-title">
        <h2>Profil Saya</h2>
        <p>Informasi detail akun Anda.</p>
    </div>
</header>

<div class="data-card" style="max-width: 600px; padding: 32px;">
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

<?php 
require_once 'includes/footer.php';
?>