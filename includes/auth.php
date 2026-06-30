<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function checkRole($allowed_roles) {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: beranda.php?error=unauthorized");
        exit();
    }
}

// Global check for all pages except login
$current_page = basename($_SERVER['PHP_SELF']);
if ($current_page !== 'login.php' && !isLoggedIn()) {
    header("Location: login.php");
    exit();
}
?>