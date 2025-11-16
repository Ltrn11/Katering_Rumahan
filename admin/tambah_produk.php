<?php
session_start();
require '../koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = $_POST['nama_produk'];
    $id_kategori = $_POST['id_kategori'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $tersedia = $_POST['tersedia'];

    $stmt = $koneksi->prepare("INSERT INTO Produk (id_kategori, nama_produk, deskripsi, harga, tersedia) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issdi", $id_kategori, $nama_produk, $deskripsi, $harga, $tersedia);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Produk berhasil ditambahkan!";
    } else {
        $_SESSION['error'] = "Gagal menambahkan produk: " . $koneksi->error;
    }

    $stmt->close();
    $koneksi->close();
    
    header("Location: produk.php");
    exit;
}
?>