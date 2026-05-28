<?php
require_once 'includes/auth.php';
require_once 'config/db.php';

// Only admin_guru can delete students
checkRole(['admin_guru']);

$siswa_id = $_GET['id'] ?? null;

if ($siswa_id) {
    try {
        // Get siswa data to delete photo
        $stmt = $pdo->prepare("SELECT foto FROM siswa WHERE id = ?");
        $stmt->execute([$siswa_id]);
        $siswa = $stmt->fetch();

        if ($siswa) {
            // Delete from hafalan table first (foreign key constraint)
            $stmt = $pdo->prepare("DELETE FROM hafalan WHERE siswa_id = ?");
            $stmt->execute([$siswa_id]);

            // Delete from siswa table
            $stmt = $pdo->prepare("DELETE FROM siswa WHERE id = ?");
            $stmt->execute([$siswa_id]);

            // Delete photo if not default
            if ($siswa['foto'] && $siswa['foto'] !== 'assets/orang.png' && file_exists($siswa['foto'])) {
                unlink($siswa['foto']);
            }

            header("Location: index.php?success=Data siswa berhasil dihapus");
        } else {
            header("Location: index.php?error=Siswa tidak ditemukan");
        }
    } catch (PDOException $e) {
        header("Location: index.php?error=Gagal menghapus data: " . $e->getMessage());
    }
} else {
    header("Location: index.php?error=ID siswa tidak valid");
}
exit();
?>
