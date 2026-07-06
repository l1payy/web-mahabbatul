<?php
require_once 'includes/auth.php';
require_once 'config/db.php';

$page_title = 'Beranda';
$current_page = 'index.php';

// Pagination setup
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Stats
$totalSiswa = $pdo->query("SELECT COUNT(*) FROM siswa")->fetchColumn();
$tuntasHafalan = $pdo->query("SELECT COUNT(*) FROM hafalan WHERE status = 'Sudah Lancar'")->fetchColumn();

// Fetch Data Siswa with Hafalan Status
$stmt = $pdo->prepare("
    SELECT s.*, h.status as status_hafalan
    FROM siswa s
    LEFT JOIN hafalan h ON s.id = h.siswa_id
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$siswaList = $stmt->fetchAll();

// Total pages for pagination
$totalPages = ceil($totalSiswa / $limit);

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<header class="page-header">
    <div class="header-title">
        <h2>Selamat datang di Sistem Pelaporan Data Siswa Mahabbatul Ummi</h2>
        <p>Ringkasan informasi data sekolah Mahabbatul Ummi.</p>
    </div>
</header>

<?php if (isset($_GET['success'])): ?>
    <div class="message-alert" style="background: var(--success-bg); color: var(--success-text); padding: 12px 24px; border-radius: 8px; margin-bottom: 24px; font-weight: 600;">
        <?php echo htmlspecialchars($_GET['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="message-alert" style="background: var(--error-bg); color: var(--error-text); padding: 12px 24px; border-radius: 8px; margin-bottom: 24px; font-weight: 600;">
        <?php echo htmlspecialchars($_GET['error']); ?>
    </div>
<?php endif; ?>

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
            <p>Total Siswa Lulus Hafalan</p>
            <h3><?php echo number_format($tuntasHafalan, 0, ',', '.'); ?></h3>
        </div>
        <div class="stat-icon">
            <i data-lucide="book-open" size="28"></i>
        </div>
    </div>
</div>

<div class="data-card">
    <div class="card-header">
        <h3>Data Siswa</h3>
        <?php if ($_SESSION['role'] === 'guru_wali_kelas'): ?>
            <a href="tambah_siswa.php" class="btn btn-primary">
                <i data-lucide="plus"></i>
                <span>Tambah Siswa</span>
            </a>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No. Induk</th>
                    <th>Nama Lengkap</th>
                    <th>Tempat Tanggal Lahir</th>
                    <th>Anak Ke</th>
                    <th>Jenis Kelamin</th>
                    <th>Orang Tua/Wali</th>
                    <th>Pendidikan</th>
                    <th>Pekerjaan</th>
                    <th>Alamat Orang Tua</th>
                    <?php if ($_SESSION['role'] === 'guru_wali_kelas'): ?>
                    <th style="text-align: center; width: 120px;">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($siswaList as $siswa): ?>
                <tr>
                    <td><?php echo htmlspecialchars($siswa['no_induk']); ?></td>
                    <td>
                        <div class="student-info">
                            <div class="avatar" style="background: transparent;">
                                <img src="<?php echo htmlspecialchars($siswa['foto'] ?? 'assets/orang.png'); ?>" alt="Foto" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                            </div>
                            <span><?php echo htmlspecialchars($siswa['nama']); ?></span>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($siswa['tempat_lahir'] . ', ' . date('d/m/Y', strtotime($siswa['tanggal_lahir']))); ?></td>
                    <td><?php echo htmlspecialchars($siswa['anak_ke']); ?></td>
                    <td><?php echo htmlspecialchars($siswa['jenis_kelamin']); ?></td>
                    <td><?php echo htmlspecialchars($siswa['nama_orang_tua']); ?></td>
                    <td><?php echo htmlspecialchars($siswa['pendidikan_ortu']); ?></td>
                    <td><?php echo htmlspecialchars($siswa['pekerjaan_ortu']); ?></td>
                    <td><?php echo htmlspecialchars($siswa['alamat_ortu']); ?></td>
                    <?php if ($_SESSION['role'] === 'guru_wali_kelas'): ?>
                    <td>
                        <div style="display: flex; gap: 8px; justify-content: center;">
                            <a href="edit_siswa.php?id=<?php echo $siswa['id']; ?>" class="btn-icon" title="Edit" style="color: var(--primary-color); padding: 6px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; background: var(--primary-bg); transition: all 0.2s;">
                                <i data-lucide="edit-2" size="16"></i>
                            </a>
                            <a href="hapus_siswa.php?id=<?php echo $siswa['id']; ?>" class="btn-icon" title="Hapus" onclick="return confirm('Yakin ingin menghapus data siswa ini?')" style="color: var(--error-text); padding: 6px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; background: var(--error-bg); transition: all 0.2s;">
                                <i data-lucide="trash-2" size="16"></i>
                            </a>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-container" style="padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color);">
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            Menampilkan <?php echo count($siswaList); ?> dari <?php echo $totalSiswa; ?> siswa
        </p>
        <div class="pagination">
            <a href="?page=<?php echo max(1, $page-1); ?>" class="page-btn <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                <i data-lucide="chevron-left" size="16"></i>
            </a>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="page-btn <?php echo ($page == $i) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            <a href="?page=<?php echo min($totalPages, $page+1); ?>" class="page-btn <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                <i data-lucide="chevron-right" size="16"></i>
            </a>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php'; // I'll create this next
?>
