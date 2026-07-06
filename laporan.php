<?php
require_once 'includes/auth.php';
require_once 'config/db.php';

$page_title = 'Laporan Absensi';
$current_page = 'laporan.php';

// Filters
$recap_month = $_GET['recap_month'] ?? date('m');
$recap_year = $_GET['recap_year'] ?? date('Y');

// Fetch Recap Data (Monthly)
$recapQuery = "
    SELECT s.id, s.nama, s.no_induk,
           SUM(CASE WHEN a.kehadiran = 'Hadir' THEN 1 ELSE 0 END) as total_hadir,
           SUM(CASE WHEN a.kehadiran = 'Sakit' THEN 1 ELSE 0 END) as total_sakit,
           SUM(CASE WHEN a.kehadiran = 'Izin' THEN 1 ELSE 0 END) as total_izin,
           SUM(CASE WHEN a.kehadiran = 'Alpa' THEN 1 ELSE 0 END) as total_alpa,
           h.status as status_hafalan
    FROM siswa s
    LEFT JOIN absensi a ON s.id = a.siswa_id AND MONTH(a.tanggal) = ? AND YEAR(a.tanggal) = ?
    LEFT JOIN hafalan h ON s.id = h.siswa_id
    GROUP BY s.id, s.nama, s.no_induk, h.status
";
$stmtRecap = $pdo->prepare($recapQuery);
$stmtRecap->execute([$recap_month, $recap_year]);
$recapList = $stmtRecap->fetchAll();

require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Get month name in Indonesian
$monthName = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

// Get current date for print
$tgl_cetak = date('d F Y');
?>

<header class="page-header">
    <div class="header-title">
        <h2>Laporan Absensi</h2>
        <p>Rekap data kehadiran siswa/siswi.</p>
    </div>
    <div class="header-actions">
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
                <span>Cetak</span>
            </button>
        </form>
    </div>
</header>

<div class="data-card" id="laporanSection">
    <div class="card-header" style="text-align: center; display: block;">
        <h2 style="margin-bottom: 8px;">LAPORAN REKAPITULASI ABSENSI SISWA</h2>
        <h3>Mahabbatul Ummi</h3>
        <p style="margin-top: 12px; font-weight: 600;">Periode: <?php echo $monthName[$recap_month] . " " . $recap_year; ?></p>
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
                    <th style="border: 1px solid #000; padding: 12px; text-align: center;">Status Hafalan</th>
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
                    <td style="border: 1px solid #000; padding: 12px; text-align: center;"><?php echo htmlspecialchars($recap['status_hafalan'] ?? 'Belum Hafal'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 48px; display: flex; justify-content: space-between; flex-wrap: wrap;">
        <div style="text-align: center; width: 200px;">
            <p style="font-weight: 600;">Wali Kelas</p>
            <div style="margin-top: 80px; border-top: 1px solid #000; padding-top: 4px;">
                <strong>( ................................ )</strong>
            </div>
        </div>
        
        <div style="text-align: center; width: 200px;">
            <p>Medan, <?php echo $tgl_cetak; ?></p>
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

    /* Tampilkan hanya bagian laporan */
    #laporanSection, #laporanSection * {
        visibility: visible;
    }

    #laporanSection {
        display: block !important;
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 20px;
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
    .sidebar, .page-header, .stats-container, .btn, .message-alert, form {
        display: none !important;
    }
}
</style>

<?php
require_once 'includes/footer.php';
?>
