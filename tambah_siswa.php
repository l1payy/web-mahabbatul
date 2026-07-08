<?php
require_once 'includes/auth.php';
require_once 'config/db.php';

// Only guru_wali_kelas can add students
checkRole(['guru_wali_kelas']);

$page_title = 'Tambah Siswa';
$current_page = 'index.php';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $no_induk = $_POST['no_induk'] ?? '';
    $tempat_lahir = $_POST['tempat_lahir'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $anak_ke = $_POST['anak_ke'] ?? '';
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $nama_orang_tua = $_POST['nama_orang_tua'] ?? '';
    $pendidikan_ortu = $_POST['pendidikan_ortu'] ?? '';
    $pekerjaan_ortu = $_POST['pekerjaan_ortu'] ?? '';
    $alamat_ortu = $_POST['alamat_ortu'] ?? '';
    $foto_path = 'assets/orang.png'; // Default photo

    if ($nama && $no_induk && $tempat_lahir && $tanggal_lahir && $anak_ke && $jenis_kelamin && $nama_orang_tua && $pendidikan_ortu && $pekerjaan_ortu && $alamat_ortu) {
        try {
            // Handle File Upload
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'assets/uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $file_name = time() . '_' . preg_replace('/[^A-Za-z0-9]/', '', $no_induk) . '.' . $file_extension;
                $target_path = $upload_dir . $file_name;

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_path)) {
                    $foto_path = $target_path;
                }
            }

            $stmt = $pdo->prepare("INSERT INTO siswa (nama, no_induk, tempat_lahir, tanggal_lahir, anak_ke, jenis_kelamin, nama_orang_tua, pendidikan_ortu, pekerjaan_ortu, alamat_ortu, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nama, $no_induk, $tempat_lahir, $tanggal_lahir, $anak_ke, $jenis_kelamin, $nama_orang_tua, $pendidikan_ortu, $pekerjaan_ortu, $alamat_ortu, $foto_path]);
            header("Location: index.php?success=Siswa berhasil ditambahkan");
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "No. Induk sudah terdaftar!";
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

<div class="data-card" style="max-width: 800px; padding: 32px; margin: 0 auto;">
    <form action="" method="POST" enctype="multipart/form-data">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <div class="form-group">
                <label for="no_induk">No. Induk</label>
                <input type="text" id="no_induk" name="no_induk" placeholder="Contoh: 476.07.25" required>
            </div>
            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap siswa" required>
            </div>
            <div class="form-group">
                <label for="tempat_lahir">Tempat Lahir</label>
                <input type="text" id="tempat_lahir" name="tempat_lahir" placeholder="Contoh: Medan" required>
            </div>
            <div class="form-group">
                <label for="tanggal_lahir">Tanggal Lahir</label>
                <input type="date" id="tanggal_lahir" name="tanggal_lahir" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px;">
            </div>
            <div class="form-group">
                <label for="anak_ke">Anak Ke</label>
                <input type="text" id="anak_ke" name="anak_ke" placeholder="Contoh: 2 (Dua)" required>
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
                <label for="nama_orang_tua">Orang Tua / Wali</label>
                <input type="text" id="nama_orang_tua" name="nama_orang_tua" placeholder="Nama ayah/ibu/wali" required>
            </div>
            <div class="form-group">
                <label for="pendidikan_ortu">Pendidikan Orang Tua</label>
                <input type="text" id="pendidikan_ortu" name="pendidikan_ortu" placeholder="Contoh: SLTP / Sederajat" required>
            </div>
            <div class="form-group">
                <label for="pekerjaan_ortu">Pekerjaan Orang Tua</label>
                <input type="text" id="pekerjaan_ortu" name="pekerjaan_ortu" placeholder="Contoh: Wiraswasta" required>
            </div>
            <div class="form-group">
                <label for="foto">Foto Siswa</label>
                <input type="file" id="foto" name="foto" accept="image/*" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px;">
                <small style="color: var(--text-muted); font-size: 0.85rem; margin-top: 4px; display: block;">Opsional, kosongkan jika tidak ada foto</small>
            </div>
        </div>
        <div class="form-group" style="margin-top: 24px;">
            <label for="alamat_ortu">Alamat Orang Tua</label>
            <textarea id="alamat_ortu" name="alamat_ortu" placeholder="Masukkan alamat lengkap orang tua" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; min-height: 100px;"></textarea>
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
