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
    
    // Backward compatibility: treat 'admin_guru' as 'guru_wali_kelas'
    $effective_role = ($_SESSION['role'] === 'admin_guru') ? 'guru_wali_kelas' : $_SESSION['role'];
    
    if (!in_array($effective_role, $allowed_roles)) {
        header("Location: index.php?error=unauthorized");
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