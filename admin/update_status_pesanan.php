<?php
session_start();
require '../koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = 'Silakan login terlebih dahulu!';
    header('Location: login.php');
    exit();
}

// Cek method dan data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_pesanan']) && isset($_POST['status'])) {
    $id_pesanan = (int)$_POST['id_pesanan'];
    $new_status = $_POST['status'];
    
    // Validasi status
    $valid_statuses = ['pending', 'diproses', 'dikirim', 'selesai', 'dibatalkan'];
    if (!in_array($new_status, $valid_statuses)) {
        $_SESSION['error'] = 'Status tidak valid!';
        header('Location: pesanan.php');
        exit();
    }
    
    // Update status pesanan
    $stmt = $koneksi->prepare("UPDATE Pesanan SET status = ? WHERE id_pesanan = ?");
    $stmt->bind_param("si", $new_status, $id_pesanan);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Status pesanan berhasil diperbarui!';
    } else {
        $_SESSION['error'] = 'Gagal memperbarui status pesanan!';
    }
    
    $stmt->close();
    $koneksi->close();
    
    // Redirect kembali ke halaman pesanan
    header('Location: pesanan.php');
    exit();
} else {
    $_SESSION['error'] = 'Data tidak valid!';
    header('Location: pesanan.php');
    exit();
}
?>