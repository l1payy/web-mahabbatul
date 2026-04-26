<?php
require_once 'includes/auth.php';
require_once 'config/db.php';

$page_title = 'Absensi Siswa';
$current_page = 'absensi.php';

// Stats
$totalSiswa = $pdo->query("SELECT COUNT(*) FROM siswa")->fetchColumn();
$totalGuru = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin_guru'")->fetchColumn();
$tuntasHafalan = $pdo->query("SELECT COUNT(*) FROM hafalan WHERE status = 'Sudah Lancar'")->fetchColumn();

// Filters
$search = $_GET['search'] ?? '';
$kelas_filter = $_GET['kelas'] ?? '';

// Fetch distinct classes for dropdown
$classes = $pdo->query("SELECT DISTINCT kelas FROM siswa ORDER BY kelas ASC")->fetchAll(PDO::FETCH_COLUMN);

// Handle Save Absensi
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'admin_guru') {
    $tanggal = date('Y-m-d');
    foreach ($_POST['kehadiran'] as $siswa_id => $status) {
        // Check if already exists for today
        $stmt = $pdo->prepare("SELECT id FROM absensi WHERE siswa_id = ? AND tanggal = ?");
        $stmt->execute([$siswa_id, $tanggal]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $pdo->prepare("UPDATE absensi SET kehadiran = ?, created_by = ? WHERE id = ?");
            $stmt->execute([$status, $_SESSION['user_id'], $existing['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO absensi (siswa_id, tanggal, kehadiran, created_by) VALUES (?, ?, ?, ?)");
            $stmt->execute([$siswa_id, $tanggal, $status, $_SESSION['user_id']]);
        }
    }
    $message = 'Absensi berhasil disimpan!';
}

// Fetch Students with Today's Attendance
$query = "
    SELECT s.*, a.kehadiran 
    FROM siswa s 
    LEFT JOIN absensi a ON s.id = a.siswa_id AND a.tanggal = CURDATE()
    WHERE 1=1
";
$params = [];

if ($search) {
    $query .= " AND s.nama LIKE ?";
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
        <h2>Absensi Siswa</h2>
        <p>Kelola kehadiran harian santri secara tertib dan amanah.</p>
    </div>
    <div class="header-actions">
        <div class="btn btn-outline">
            <i data-lucide="calendar"></i>
            <span><?php echo date('l, d F Y'); ?></span>
        </div>
        <?php if ($_SESSION['role'] === 'admin_guru'): ?>
            <button type="submit" form="absensiForm" class="btn btn-primary">
                <i data-lucide="save"></i>
                <span>Simpan Absensi</span>
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
            <i data-lucide="user-check" size="28"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <p>Tuntas Hafalan</p>
            <h3><?php echo number_format($tuntasHafalan, 0, ',', '.'); ?></h3>
        </div>
        <div class="stat-icon">
            <i data-lucide="book-open" size="28"></i>
        </div>
    </div>
</div>

<?php if ($message): ?>
    <div class="message-alert" style="background: var(--success-bg); color: var(--success-text); padding: 12px 24px; border-radius: 8px; margin-bottom: 24px; font-weight: 600; transition: opacity 0.5s;">
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
                <select name="kelas" onchange="this.form.submit()">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?php echo $c; ?>" <?php echo ($kelas_filter == $c) ? 'selected' : ''; ?>>
                            <?php echo $c; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            Menampilkan <?php echo count($siswaList); ?> dari <?php echo $totalSiswa; ?> Siswa
        </p>
    </div>

    <form id="absensiForm" action="" method="POST">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($siswaList as $siswa): ?>
                <tr>
                    <td>
                        <div class="student-info">
                            <div class="avatar">
                                <?php 
                                    $names = explode(' ', $siswa['nama']);
                                    $initials = strtoupper(substr($names[0], 0, 1) . (isset($names[1]) ? substr($names[1], 0, 1) : ''));
                                    echo $initials;
                                ?>
                            </div>
                            <span><?php echo htmlspecialchars($siswa['nama']); ?></span>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($siswa['kelas']); ?></td>
                    <td>
                        <div class="input-group">
                            <select name="kehadiran[<?php echo $siswa['id']; ?>]" 
                                    class="kehadiran-select" 
                                    <?php echo ($_SESSION['role'] === 'kepala_sekolah') ? 'disabled' : ''; ?>
                                    style="min-width: 120px; padding: 6px 12px;">
                                <option value="Hadir" <?php echo ($siswa['kehadiran'] == 'Hadir') ? 'selected' : ''; ?>>Hadir</option>
                                <option value="Sakit" <?php echo ($siswa['kehadiran'] == 'Sakit') ? 'selected' : ''; ?>>Sakit</option>
                                <option value="Izin" <?php echo ($siswa['kehadiran'] == 'Izin') ? 'selected' : ''; ?>>Izin</option>
                                <option value="Alpa" <?php echo ($siswa['kehadiran'] == 'Alpa') ? 'selected' : ''; ?>>Alpa</option>
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