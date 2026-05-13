<?php
require_once 'config/db.php';

try {
    // 1. Create Tables
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users ( 
            id INT AUTO_INCREMENT PRIMARY KEY, 
            nama VARCHAR(100), 
            email VARCHAR(100) UNIQUE, 
            password VARCHAR(255), 
            role ENUM('admin_guru', 'kepala_sekolah'), 
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
        ); 

        CREATE TABLE IF NOT EXISTS siswa ( 
            id INT AUTO_INCREMENT PRIMARY KEY, 
            nama VARCHAR(100), 
            nisn VARCHAR(20) UNIQUE, 
            tanggal_lahir DATE,
            alamat TEXT,
            nama_orang_tua VARCHAR(100),
            jenis_kelamin ENUM('Laki-laki', 'Perempuan'), 
            foto VARCHAR(255) DEFAULT 'assets/orang.png',
            status ENUM('aktif', 'tidak_aktif') DEFAULT 'aktif', 
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
        ); 

        CREATE TABLE IF NOT EXISTS absensi ( 
            id INT AUTO_INCREMENT PRIMARY KEY, 
            siswa_id INT, 
            tanggal DATE, 
            kehadiran ENUM('Hadir', 'Sakit', 'Izin', 'Alpa'), 
            created_by INT, 
            FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE, 
            FOREIGN KEY (created_by) REFERENCES users(id) 
        ); 

        CREATE TABLE IF NOT EXISTS hafalan ( 
            id INT AUTO_INCREMENT PRIMARY KEY, 
            siswa_id INT, 
            status ENUM('Belum Hafal', 'Masih Menghafal', 'Sudah Lancar'), 
            updated_by INT, 
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
            FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE, 
            FOREIGN KEY (updated_by) REFERENCES users(id) 
        ); 
    ");

    // 2. Seed Users
    $password = password_hash('password123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Admin Guru', 'guru@mahabbatulummi.com', $password, 'admin_guru']);
    $stmt->execute(['Kepala Sekolah', 'kepala@mahabbatulummi.com', $password, 'kepala_sekolah']);

    // 3. Seed Siswa
    $siswaData = [
        ['Ahmad Zulkarnain', '0123456789', '2018-05-15', 'Jl. Merdeka No. 1', 'Budi Santoso', 'Laki-laki'],
        ['Fatimah Az-Zahra', '0123456790', '2018-08-20', 'Jl. Mawar No. 12', 'Siti Aminah', 'Perempuan'],
        ['Muhammad Rizky', '0123456791', '2019-01-10', 'Jl. Melati No. 5', 'Agus Prayogo', 'Laki-laki'],
        ['Siti Aminah', '0123456792', '2018-12-05', 'Jl. Kenanga No. 3', 'Hasan Basri', 'Perempuan'],
        ['Umar bin Khattab', '0123456793', '2019-03-25', 'Jl. Anggrek No. 8', 'Ali Imran', 'Laki-laki'],
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO siswa (nama, nisn, tanggal_lahir, alamat, nama_orang_tua, jenis_kelamin) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($siswaData as $s) {
        $stmt->execute($s);
    }

    echo "Database initialized and seeded successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>