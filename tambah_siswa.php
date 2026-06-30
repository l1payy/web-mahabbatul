<?php
require_once 'includes/auth.php';
require_once 'config/db.php';

// Only admin_guru can add students
checkRole(['admin_guru']);

$page_title = 'Tambah Siswa';
$current_page = 'beranda.php';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $nis = $_POST['nis'] ?? '';
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $kelas = $_POST['kelas'] ?? '';

    if ($nama && $nis && $jenis_kelamin && $kelas) {
        try {
            $stmt = $pdo->prepare("INSERT INTO siswa (nama, nis, jenis_kelamin, kelas) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nama, $nis, $jenis_kelamin, $kelas]);
            header("Location: beranda.php?success=Siswa berhasil ditambahkan");
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "NIS sudah terdaftar!";
            } else {
                $error = "Terjadi kesalahan: " . $e->getMessage();
            }
        }
    } else {
        $error = "Semua field wajib diisi!";
    }
}

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<header class="page-header">
    <div class="header-title">
        <h2>Tambah Siswa Baru</h2>
        <p>Lengkapi formulir di bawah ini untuk menambahkan data siswa.</p>
    </div>
    <div class="header-actions">
        <a href="beranda.php" class="btn btn-outline">
            <i data-lucide="arrow-left"></i>
            <span>Kembali</span>
        </a>
    </div>
</header>

<?php if ($error): ?>
    <div style="background: var(--error-bg); color: var(--error-text); padding: 12px 24px; border-radius: 8px; margin-bottom: 24px; font-weight: 600;">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="data-card" style="max-width: 600px; padding: 32px;">
    <form action="" method="POST">
        <div class="form-group">
            <label for="nama">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" placeholder="Masukkan nama siswa" required>
        </div>
        <div class="form-group">
            <label for="nis">NIS (Nomor Induk Siswa)</label>
            <input type="text" id="nis" name="nis" placeholder="Contoh: 2023001" required>
        </div>
        <div class="form-group">
            <label for="jenis_kelamin">Jenis Kelamin</label>
            <select name="jenis_kelamin" id="jenis_kelamin" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px;">
                <option value="">Pilih Jenis Kelamin</option>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
            </select>
        </div>
        <div class="form-group">
            <label for="kelas">Kelas</label>
            <input type="text" id="kelas" name="kelas" placeholder="Contoh: 7-A atau 9A - Tahfidz" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 24px;">
            <i data-lucide="save"></i>
            <span>Simpan Data Siswa</span>
        </button>
    </form>
</div>

<?php 
require_once 'includes/footer.php';
?>