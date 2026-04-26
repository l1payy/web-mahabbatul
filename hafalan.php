<?php
require_once 'includes/auth.php';
require_once 'config/db.php';

$page_title = 'Hafalan Siswa';
$current_page = 'hafalan.php';

// Stats
$totalSiswa = $pdo->query("SELECT COUNT(*) FROM siswa")->fetchColumn();
$totalGuru = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin_guru'")->fetchColumn();
$tuntasHafalan = $pdo->query("SELECT COUNT(*) FROM hafalan WHERE status = 'Sudah Lancar'")->fetchColumn();

// Filters
$search = $_GET['search'] ?? '';
$kelas_filter = $_GET['kelas'] ?? '';

// Fetch distinct classes
$classes = $pdo->query("SELECT DISTINCT kelas FROM siswa ORDER BY kelas ASC")->fetchAll(PDO::FETCH_COLUMN);

// Handle Save Hafalan
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'admin_guru') {
    foreach ($_POST['status_hafalan'] as $siswa_id => $status) {
        // Check if exists
        $stmt = $pdo->prepare("SELECT id FROM hafalan WHERE siswa_id = ?");
        $stmt->execute([$siswa_id]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $pdo->prepare("UPDATE hafalan SET status = ?, updated_by = ? WHERE id = ?");
            $stmt->execute([$status, $_SESSION['user_id'], $existing['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO hafalan (siswa_id, status, updated_by) VALUES (?, ?, ?)");
            $stmt->execute([$siswa_id, $status, $_SESSION['user_id']]);
        }
    }
    $message = 'Data hafalan berhasil diperbarui!';
}

// Fetch Students with Hafalan Status
$query = "
    SELECT s.*, h.status as status_hafalan 
    FROM siswa s 
    LEFT JOIN hafalan h ON s.id = h.siswa_id 
    WHERE 1=1
";
$params = [];

if ($search) {
    $query .= " AND (s.nama LIKE ? OR s.nis LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($kelas_filter) {
    $query .= " AND s.kelas = ?";
    $params[] = $kelas_filter;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$siswaList = $stmt->fetchAll();

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<header class="page-header">
    <div class="header-title">
        <h2>Hafalan Siswa</h2>
        <p>Kelola dan pantau progres hafalan Al-Qur'an para santri.</p>
    </div>
    <div class="header-actions">
        <div class="btn btn-outline">
            <i data-lucide="calendar"></i>
            <span><?php echo date('l, d F Y'); ?></span>
        </div>
        <?php if ($_SESSION['role'] === 'admin_guru'): ?>
            <button type="submit" form="hafalanForm" class="btn btn-primary">
                <i data-lucide="save"></i>
                <span>Simpan Hafalan</span>
            </button>
        <?php endif; ?>
    </div>
</header>

<div class="stats-container">
    <div class="stat-card">
        <div class="stat-info">
            <p>Total Siswa</p>
            <h3><?php echo number_format($totalSiswa, 0, ',', '.'); ?></h3>
        </div>
        <div class="stat-icon">
            <i data-lucide="users" size="28"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <p>Total Guru</p>
            <h3><?php echo number_format($totalGuru, 0, ',', '.'); ?></h3>
        </div>
        <div class="stat-icon">
            <i data-lucide="graduation-cap" size="28"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <p>Tuntas Hafalan</p>
            <h3><?php echo number_format($tuntasHafalan, 0, ',', '.'); ?></h3>
        </div>
        <div class="stat-icon">
            <i data-lucide="check-circle" size="28"></i>
        </div>
    </div>
</div>

<?php if ($message): ?>
    <div class="message-alert" style="background: var(--success-bg); color: var(--success-text); padding: 12px 24px; border-radius: 8px; margin-bottom: 24px; font-weight: 600;">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="data-card">
    <div class="table-controls">
        <form action="" method="GET" class="search-filter">
            <div class="input-group">
                <i data-lucide="search"></i>
                <input type="text" name="search" placeholder="Cari Nama Siswa..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="input-group">
                <select name="kelas">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?php echo $c; ?>" <?php echo ($kelas_filter == $c) ? 'selected' : ''; ?>>
                            <?php echo $c; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="filter"></i>
                <span>Filter</span>
            </button>
        </form>
    </div>

    <form id="hafalanForm" action="" method="POST">
        <table>
            <thead>
                <tr>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Status Hafalan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($siswaList as $siswa): ?>
                <tr>
                    <td>
                        <div style="display: flex; flex-direction: column;">
                            <span style="font-weight: 600;"><?php echo htmlspecialchars($siswa['nama']); ?></span>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">NIS: <?php echo htmlspecialchars($siswa['nis']); ?></span>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($siswa['kelas']); ?></td>
                    <td>
                        <div class="input-group">
                            <select name="status_hafalan[<?php echo $siswa['id']; ?>]" 
                                    class="status-select"
                                    <?php echo ($_SESSION['role'] === 'kepala_sekolah') ? 'disabled' : ''; ?>
                                    style="padding: 6px 12px; border-radius: 4px; border: 1px solid var(--border-color); width: 100%;">
                                <option value="Belum Hafal" <?php echo ($siswa['status_hafalan'] == 'Belum Hafal') ? 'selected' : ''; ?>>Belum Hafal</option>
                                <option value="Masih Menghafal" <?php echo ($siswa['status_hafalan'] == 'Masih Menghafal') ? 'selected' : ''; ?>>Masih Menghafal</option>
                                <option value="Sudah Lancar" <?php echo ($siswa['status_hafalan'] == 'Sudah Lancar') ? 'selected' : ''; ?>>Sudah Lancar</option>
                            </select>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </form>
</div>

<?php 
require_once 'includes/footer.php';
?>