<?php
require_once 'includes/auth.php';
require_once 'config/db.php';

$page_title = 'Absensi Siswa';
$current_page = 'absensi.php';

// Stats
$totalSiswa = $pdo->query("SELECT COUNT(*) FROM siswa")->fetchColumn();
$tuntasHafalan = $pdo->query("SELECT COUNT(*) FROM hafalan WHERE status = 'Sudah Lancar'")->fetchColumn();

// Filters
$search = $_GET['search'] ?? '';
$tanggal_filter = $_GET['tanggal'] ?? date('Y-m-d');
$recap_month = $_GET['recap_month'] ?? date('m');
$recap_year = $_GET['recap_year'] ?? date('Y');

// Handle Save Absensi
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'admin_guru') {
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

// Fetch Recap Data (Monthly)
$recapQuery = "
    SELECT s.id, s.nama, s.no_induk,
           SUM(CASE WHEN a.kehadiran = 'Hadir' THEN 1 ELSE 0 END) as total_hadir,
           SUM(CASE WHEN a.kehadiran = 'Sakit' THEN 1 ELSE 0 END) as total_sakit,
           SUM(CASE WHEN a.kehadiran = 'Izin' THEN 1 ELSE 0 END) as total_izin,
           SUM(CASE WHEN a.kehadiran = 'Alpa' THEN 1 ELSE 0 END) as total_alpa
    FROM siswa s
    LEFT JOIN absensi a ON s.id = a.siswa_id AND MONTH(a.tanggal) = ? AND YEAR(a.tanggal) = ?
    GROUP BY s.id, s.nama, s.no_induk
";
$stmtRecap = $pdo->prepare($recapQuery);
$stmtRecap->execute([$recap_month, $recap_year]);
$recapList = $stmtRecap->fetchAll();

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

        <?php if ($_SESSION['role'] === 'admin_guru'): ?>
            <button type="submit" form="absensiForm" class="btn btn-primary" style="margin-left: 8px;">
                <i data-lucide="save"></i>
                <span>Simpan</span>
            </button>
        <?php endif; ?>

        <div style="height: 32px; width: 1px; background: var(--border-color); margin: 0 8px;"></div>

        <form action="" method="GET" style="display: flex; gap: 8px; align-items: center;">
            <select name="recap_month" style="padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--card-bg); color: var(--text-color);">
                <?php
                $months = [
                    '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
                    '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agust',
                    '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'
                ];
                foreach ($months as $m => $name) {
                    $selected = ($m == $recap_month) ? 'selected' : '';
                    echo "<option value=\"$m\" $selected>$name</option>";
                }
                ?>
            </select>
            <select name="recap_year" style="padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--card-bg); color: var(--text-color);">
                <?php
                for ($y = 2023; $y <= 2030; $y++) {
                    $selected = ($y == $recap_year) ? 'selected' : '';
                    echo "<option value=\"$y\" $selected>$y</option>";
                }
                ?>
            </select>
            <button type="submit" class="btn btn-outline" style="padding: 8px 12px;">
                <i data-lucide="filter" size="16"></i>
            </button>
            <button type="button" onclick="window.print()" class="btn btn-primary">
                <i data-lucide="printer"></i>
                <span>Ekspor</span>
            </button>
        </form>


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
            <h3>3</h3>
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

<div class="data-card" style="margin-top: 32px; display: none;" id="recapSection">
    <div class="card-header" style="text-align: center; display: block;">
        <h2 style="margin-bottom: 8px;">LAPORAN REKAPITULASI ABSENSI SISWA</h2>
        <h3>Mahabbatul Ummi</h3>
        <p style="margin-top: 12px; font-weight: 600;">Periode: <?php
            $monthName = [
                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
            ];
            echo $monthName[$recap_month] . " " . $recap_year;
        ?></p>
    </div>

    <div class="table-responsive" style="margin-top: 24px;">
        <table class="table" id="recapTable" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="border: 1px solid #000; padding: 12px; text-align: left;">Nama Siswa</th>
                    <th style="border: 1px solid #000; padding: 12px; text-align: center;">No. Induk</th>
                    <th style="border: 1px solid #000; padding: 12px; text-align: center;">Hadir</th>
                    <th style="border: 1px solid #000; padding: 12px; text-align: center;">Sakit</th>
                    <th style="border: 1px solid #000; padding: 12px; text-align: center;">Izin</th>
                    <th style="border: 1px solid #000; padding: 12px; text-align: center;">Alpa</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recapList as $recap): ?>
                <tr>
                    <td style="border: 1px solid #000; padding: 12px;"><?php echo htmlspecialchars($recap['nama']); ?></td>
                    <td style="border: 1px solid #000; padding: 12px; text-align: center;"><?php echo htmlspecialchars($recap['no_induk']); ?></td>
                    <td style="border: 1px solid #000; padding: 12px; text-align: center;"><?php echo $recap['total_hadir']; ?></td>
                    <td style="border: 1px solid #000; padding: 12px; text-align: center;"><?php echo $recap['total_sakit']; ?></td>
                    <td style="border: 1px solid #000; padding: 12px; text-align: center;"><?php echo $recap['total_izin']; ?></td>
                    <td style="border: 1px solid #000; padding: 12px; text-align: center;"><?php echo $recap['total_alpa']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 48px; display: flex; justify-content: flex-end;">
        <div style="text-align: center; width: 200px;">
            <p>Medan, <?php echo date('d F Y'); ?></p>
            <p style="margin-top: 8px;">Kepala Sekolah</p>
            <div style="margin-top: 80px; border-top: 1px solid #000; padding-top: 4px;">
                <strong>( ................................ )</strong>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    /* Sembunyikan semua elemen UI */
    body * {
        visibility: hidden;
    }

    /* Tampilkan hanya bagian rekap */
    #recapSection, #recapSection * {
        visibility: visible;
    }

    #recapSection {
        display: block !important;
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 0;
        box-shadow: none;
        background: white;
    }

    /* Pastikan tabel tetap dalam format tabel */
    #recapTable {
        display: table !important;
        width: 100% !important;
        border-collapse: collapse !important;
    }

    #recapTable thead {
        display: table-header-group !important;
    }

    #recapTable tbody {
        display: table-row-group !important;
    }

    #recapTable tr {
        display: table-row !important;
    }

    #recapTable th, #recapTable td {
        display: table-cell !important;
        border: 1px solid black !important;
        padding: 8px !important;
    }

    /* Sembunyikan elemen yang tidak perlu saat print */
    .sidebar, .page-header, .stats-container, .data-card, .btn, .message-alert, form {
        display: none !important;
    }
}
</style>

<?php
require_once 'includes/footer.php';
?>
