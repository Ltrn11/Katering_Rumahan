<?php
session_start();
require 'koneksi.php';

// CEK LOGIN - Kalau belum login, redirect ke login
if (!isset($_SESSION['user_id'])) {
    // Simpan produk yang mau ditambah ke session temporary
    if (isset($_POST['id_produk']) && isset($_POST['jumlah'])) {
        $_SESSION['pending_cart'] = [
            'id_produk' => $_POST['id_produk'],
            'jumlah' => $_POST['jumlah']
        ];
    }
    
    $_SESSION['error'] = 'Silakan login terlebih dahulu untuk menambahkan produk ke keranjang!';
    header('Location: index.php');
    exit();
}

// Cek apakah request dari form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $id_produk = isset($_POST['id_produk']) ? (int)$_POST['id_produk'] : 0;
    $jumlah = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 1;
    
    // Validasi
    if ($id_produk <= 0 || $jumlah <= 0) {
        $_SESSION['error'] = 'Data tidak valid!';
        header('Location: dashboard.php');
        exit();
    }
    
    // Get info produk dari database
    $stmt = $koneksi->prepare("SELECT * FROM Produk WHERE id_produk = ? AND tersedia = 1");
    $stmt->bind_param("i", $id_produk);
    $stmt->execute();
    $result = $stmt->get_result();
    $produk = $result->fetch_assoc();
    
    if (!$produk) {
        $_SESSION['error'] = 'Produk tidak ditemukan atau tidak tersedia!';
        header('Location: dashboard.php');
        exit();
    }
    
    // Inisialisasi cart kalau belum ada
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    // Cek apakah produk sudah ada di cart
    if (isset($_SESSION['cart'][$id_produk])) {
        // Update quantity
        $_SESSION['cart'][$id_produk]['jumlah'] += $jumlah;
    } else {
        // Tambah produk baru ke cart
        $_SESSION['cart'][$id_produk] = array(
            'id_produk' => $produk['id_produk'],
            'nama_produk' => $produk['nama_produk'],
            'harga' => $produk['harga'],
            'jumlah' => $jumlah
        );
    }
    
    $_SESSION['success'] = 'Produk berhasil ditambahkan ke keranjang!';
    header('Location: dashboard.php');
    exit();
    
} else {
    // Kalau bukan POST, redirect ke dashboard
    header('Location: dashboard.php');
    exit();
}

$koneksi->close();
?>