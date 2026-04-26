<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// First, connect without a specific database to create it if needed
$dsn = "mysql:host=$host;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     
     // Create database if it doesn't exist
     $pdo->exec("CREATE DATABASE IF NOT EXISTS al_falah");
     
     // Now switch to the database
     $pdo->exec("USE al_falah");
     
} catch (\PDOException $e) {
     die("Connection failed: " . $e->getMessage());
}
?>