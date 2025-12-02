<?php
/**
 * AUTH CHECK SIMPLE - Proteksi Halaman Admin
 * Include di semua halaman admin: require 'auth_check.php';
 */

// Start session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah sudah login sebagai admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Belum login, redirect ke login admin
    header("Location: login.php");
    exit();
}

// Session timeout (30 menit tidak aktif = logout otomatis)
$timeout_duration = 1800; // 30 menit
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit();
}

// Update waktu aktivitas terakhir
$_SESSION['last_activity'] = time();

/**
 * Fungsi helper
 */
function get_admin_name() {
    return isset($_SESSION['admin_nama']) ? $_SESSION['admin_nama'] : 'Admin';
}

function get_admin_id() {
    return isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 0;
}
?>