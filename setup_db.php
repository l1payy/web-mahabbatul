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
            nis VARCHAR(20) UNIQUE, 
            jenis_kelamin ENUM('Laki-laki', 'Perempuan'), 
            kelas VARCHAR(20), 
            status ENUM('aktif', 'tidak_aktif') DEFAULT 'aktif', 
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
        ); 

        CREATE TABLE IF NOT EXISTS absensi ( 
            id INT AUTO_INCREMENT PRIMARY KEY, 
            siswa_id INT, 
            tanggal DATE, 
            kehadiran ENUM('Hadir', 'Sakit', 'Izin', 'Alpa'), 
            created_by INT, 
            FOREIGN KEY (siswa_id) REFERENCES siswa(id), 
            FOREIGN KEY (created_by) REFERENCES users(id) 
        ); 

        CREATE TABLE IF NOT EXISTS hafalan ( 
            id INT AUTO_INCREMENT PRIMARY KEY, 
            siswa_id INT, 
            status ENUM('Belum Hafal', 'Masih Menghafal', 'Sudah Lancar'), 
            updated_by INT, 
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
            FOREIGN KEY (siswa_id) REFERENCES siswa(id), 
            FOREIGN KEY (updated_by) REFERENCES users(id) 
        ); 
    ");

    // 2. Seed Users
    $password = password_hash('password123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Admin Guru', 'guru@alfalah.com', $password, 'admin_guru']);
    $stmt->execute(['Kepala Sekolah', 'kepala@alfalah.com', $password, 'kepala_sekolah']);

    // 3. Seed Siswa
    $siswaData = [
        ['Ahmad Zulkarnain', '20231001', 'Laki-laki', '9A - Tahfidz'],
        ['Fatimah Az-Zahra', '20231002', 'Perempuan', '8C - Ikhwan'],
        ['Muhammad Rizky', '20231003', 'Laki-laki', '7B - Reguler'],
        ['Siti Aminah', '20231004', 'Perempuan', '9A - Tahfidz'],
        ['Umar bin Khattab', '20231005', 'Laki-laki', '8A - Tahfidz'],
        ['Ahmad Al-Ghifari', '20231006', 'Laki-laki', '7-A'],
        ['Fatimah Nurul Huda', '20231007', 'Perempuan', '7-A'],
        ['Muhammad Zulkifli', '20231008', 'Laki-laki', '7-A'],
        ['Siti Khadijah', '20231009', 'Perempuan', '7-A'],
        ['Yahya Al-Fatih', '20231010', 'Laki-laki', '7-A'],
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO siswa (nama, nis, jenis_kelamin, kelas) VALUES (?, ?, ?, ?)");
    foreach ($siswaData as $s) {
        $stmt->execute($s);
    }

    echo "Database initialized and seeded successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>