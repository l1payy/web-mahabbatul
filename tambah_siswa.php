<?php
require_once 'includes/auth.php';
require_once 'config/db.php';

// Only admin_guru can add students
checkRole(['admin_guru']);

$page_title = 'Tambah Siswa';
$current_page = 'index.php';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $nisn = $_POST['nisn'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $nama_orang_tua = $_POST['nama_orang_tua'] ?? '';
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $foto_path = 'assets/orang.png'; // Default

    if ($nama && $nisn && $tanggal_lahir && $alamat && $nama_orang_tua && $jenis_kelamin) {
        try {
            // Handle File Upload
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'assets/uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $file_name = time() . '_' . $nisn . '.' . $file_extension;
                $target_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_path)) {
                    $foto_path = $target_path;
                }
            }

            $stmt = $pdo->prepare("INSERT INTO siswa (nama, nisn, tanggal_lahir, alamat, nama_orang_tua, jenis_kelamin, foto) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nama, $nisn, $tanggal_lahir, $alamat, $nama_orang_tua, $jenis_kelamin, $foto_path]);
            header("Location: index.php?success=Siswa berhasil ditambahkan");
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "NISN sudah terdaftar!";
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
        <a href="index.php" class="btn btn-outline">
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
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="nama">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" placeholder="Masukkan nama siswa" required>
        </div>
        <div class="form-group">
            <label for="nisn">NISN</label>
            <input type="text" id="nisn" name="nisn" placeholder="Contoh: 0123456789" required>
        </div>
        <div class="form-group">
            <label for="tanggal_lahir">Tanggal Lahir</label>
            <input type="date" id="tanggal_lahir" name="tanggal_lahir" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px;">
        </div>
        <div class="form-group">
            <label for="alamat">Alamat</label>
            <textarea id="alamat" name="alamat" placeholder="Masukkan alamat lengkap" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; min-height: 100px;"></textarea>
        </div>
        <div class="form-group">
            <label for="nama_orang_tua">Nama Orang Tua/Wali</label>
            <input type="text" id="nama_orang_tua" name="nama_orang_tua" placeholder="Masukkan nama orang tua atau wali" required>
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
            <label for="foto">Foto Siswa</label>
            <input type="file" id="foto" name="foto" accept="image/*" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px;">
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">Opsional. Jika kosong akan menggunakan foto default.</p>
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