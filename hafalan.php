<?php
require_once 'includes/auth.php';
require_once 'config/db.php';

// Handle AJAX request for stats update
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_stats') {
    header('Content-Type: application/json');
    try {
        $totalSiswa = $pdo->query("SELECT COUNT(*) FROM siswa")->fetchColumn();
        $tuntasHafalan = $pdo->query("SELECT COUNT(*) FROM hafalan WHERE status = 'Sudah Lancar'")->fetchColumn();
        echo json_encode([
            'success' => true,
            'totalSiswa' => $totalSiswa,
            'tuntasHafalan' => $tuntasHafalan
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit();
}

$page_title = 'Hafalan Siswa';
$current_page = 'hafalan.php';

// Stats
$totalSiswa = $pdo->query("SELECT COUNT(*) FROM siswa")->fetchColumn();
$tuntasHafalan = $pdo->query("SELECT COUNT(*) FROM hafalan WHERE status = 'Sudah Lancar'")->fetchColumn();

// Filters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Handle Save Hafalan
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'guru_wali_kelas') {
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
    $query .= " AND (s.nama LIKE ? OR s.no_induk LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($status_filter) {
    if ($status_filter == 'Belum Hafal') {
        $query .= " AND (h.status = ? OR h.status IS NULL)";
    } else {
        $query .= " AND h.status = ?";
    }
    $params[] = $status_filter;
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
        <p>Kelola dan pantau progres hafalan Al-Qur'an para Siswa/Siswi.</p>
    </div>
    <div class="header-actions">
        <div class="btn btn-outline">
            <i data-lucide="calendar"></i>
            <span><?php echo date('l, d F Y'); ?></span>
        </div>
        <?php if ($_SESSION['role'] === 'guru_wali_kelas'): ?>
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
        <div class="card-header">
            <h3>Data Hafalan Siswa</h3>
        </div>
        <div style="padding: 20px 24px;">
            <form action="" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <div class="input-group" style="flex: 1; min-width: 250px;">
                    <i data-lucide="search"></i>
                    <input type="text" name="search" placeholder="Cari Nama/No. Induk..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <select name="status" style="min-width: 200px; padding: 12px 16px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--card-bg); color: var(--text-primary); font-size: 0.95rem; height: 44px;">
                    <option value="">Semua Status</option>
                    <option value="Belum Hafal" <?php echo ($status_filter == 'Belum Hafal') ? 'selected' : ''; ?>>Belum Hafal</option>
                    <option value="Masih Menghafal" <?php echo ($status_filter == 'Masih Menghafal') ? 'selected' : ''; ?>>Masih Menghafal</option>
                    <option value="Sudah Lancar" <?php echo ($status_filter == 'Sudah Lancar') ? 'selected' : ''; ?>>Sudah Lancar</option>
                </select>
                <button type="submit" class="btn btn-primary" style="height: 44px; padding: 0 20px;">
                    <i data-lucide="filter"></i>
                    <span>Filter</span>
                </button>
            </form>
        </div>

    <form id="hafalanForm" action="" method="POST">
        <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Nama Siswa</th>
                    <th>No. Induk</th>
                    <th>Status Hafalan</th>
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
        </div>
    </form>
</div>

<script>
// Auto-refresh stats every 5 seconds
function updateStats() {
    fetch('hafalan.php?ajax=get_stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update Total Siswa
                document.querySelector('.stats-container .stat-card:nth-child(1) h3').textContent =
                    new Intl.NumberFormat('id-ID').format(data.totalSiswa);

                // Update Tuntas Hafalan
                document.querySelector('.stats-container .stat-card:nth-child(2) h3').textContent =
                    new Intl.NumberFormat('id-ID').format(data.tuntasHafalan);
            }
        })
        .catch(error => console.log('Error updating stats:', error));
}

// Update stats immediately when page loads
updateStats();

// Then update every 5 seconds
setInterval(updateStats, 5000);
</script>

<?php
require_once 'includes/footer.php';
?>
