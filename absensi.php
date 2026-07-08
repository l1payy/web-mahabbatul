<?php
require_once 'includes/auth.php';
require_once 'config/db.php';

$page_title = 'Absensi Siswa';
$current_page = 'absensi.php';

// Stats
$totalSiswa = $pdo->query("SELECT COUNT(*) FROM siswa")->fetchColumn();
$tuntasHafalan = $pdo->query("SELECT COUNT(*) FROM hafalan WHERE status = 'Sudah Lancar'")->fetchColumn();

// Cek role (accept both for backward compatibility)
$is_guru = ($_SESSION['role'] === 'guru_wali_kelas' || $_SESSION['role'] === 'admin_guru');

// Filters
$search = $_GET['search'] ?? '';
$tanggal_filter = $_GET['tanggal'] ?? date('Y-m-d');

// Handle Save Absensi
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_guru) {
    $tanggal_simpan = $_POST['tanggal_absensi'] ?? date('Y-m-d');
    foreach ($_POST['kehadiran'] as $siswa_id => $status) {
        // Check if already exists for the selected date
        $stmt = $pdo->prepare("SELECT id FROM absensi WHERE siswa_id = ? AND tanggal = ?");
        $stmt->execute([$siswa_id, $tanggal_simpan]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $pdo->prepare("UPDATE absensi SET kehadiran = ?, created_by = ? WHERE id = ?");
            $stmt->execute([$status, $_SESSION['user_id'], $existing['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO absensi (siswa_id, tanggal, kehadiran, created_by) VALUES (?, ?, ?, ?)");
            $stmt->execute([$siswa_id, $tanggal_simpan, $status, $_SESSION['user_id']]);
        }
    }
    $message = 'Absensi berhasil disimpan!';
}

// Fetch Students with Selected Date's Attendance
$query = "
    SELECT s.*, a.kehadiran
    FROM siswa s
    LEFT JOIN absensi a ON s.id = a.siswa_id AND a.tanggal = ?
    WHERE 1=1
";
$params = [$tanggal_filter];

if ($search) {
    $query .= " AND s.nama LIKE ?";
    $params[] = "%$search%";
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
        <p>Kelola kehadiran siswa/siswi.</p>
    </div>
    <div class="header-actions">
        <form action="" method="GET" style="display: flex; gap: 12px; align-items: center;">
            <div class="input-group" style="margin: 0;">
                <input type="date" name="tanggal" value="<?php echo $tanggal_filter; ?>" onchange="this.form.submit()" style="padding: 8px 12px 8px 36px; min-width: 180px; border: 1px solid var(--border-color); border-radius: 8px;">
            </div>
            <?php if($search): ?><input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>"><?php endif; ?>
        </form>

        <?php if ($is_guru): ?>
            <button type="submit" form="absensiForm" class="btn btn-primary" style="margin-left: 8px;">
                <i data-lucide="save"></i>
                <span>Simpan</span>
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
        </form>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            Menampilkan <?php echo count($siswaList); ?> dari <?php echo $totalSiswa; ?> Siswa
        </p>
    </div>

    <form id="absensiForm" action="" method="POST">
        <input type="hidden" name="tanggal_absensi" value="<?php echo $tanggal_filter; ?>">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama Siswa</th>
                    <th>No. Induk</th>
                    <th>Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($siswaList as $siswa): ?>
                <tr>
                    <td>
                        <div class="student-info">
                            <div class="avatar" style="background: transparent;">
                                <img src="<?php echo htmlspecialchars($siswa['foto'] ?? 'assets/orang.png'); ?>" alt="Foto" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                            </div>
                            <span><?php echo htmlspecialchars($siswa['nama']); ?></span>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($siswa['no_induk']); ?></td>
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
