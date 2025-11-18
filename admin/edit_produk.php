<?php
session_start();
require '../koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produk = (int)$_POST['id_produk'];
    $nama_produk = trim($_POST['nama_produk']);
    $id_kategori = (int)$_POST['id_kategori'];
    $deskripsi = trim($_POST['deskripsi']);
    $harga = (float)$_POST['harga'];
    $tersedia = (int)$_POST['tersedia'];

    // Validasi input
    if (empty($nama_produk) || empty($deskripsi) || $harga <= 0) {
        $_SESSION['error'] = "Semua field harus diisi dengan benar!";
        header("Location: produk.php");
        exit;
    }

    $stmt = $koneksi->prepare("UPDATE Produk SET id_kategori=?, nama_produk=?, deskripsi=?, harga=?, tersedia=? WHERE id_produk=?");
    $stmt->bind_param("issdii", $id_kategori, $nama_produk, $deskripsi, $harga, $tersedia, $id_produk);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['success'] = "Produk '$nama_produk' berhasil diupdate!";
        } else {
            $_SESSION['error'] = "Tidak ada perubahan data atau produk tidak ditemukan!";
        }
    } else {
        $_SESSION['error'] = "Gagal mengupdate produk: " . $koneksi->error;
    }

    $stmt->close();
    $koneksi->close();
    
    header("Location: produk.php");
    exit;
} else {
    // Jika diakses langsung tanpa POST
    header("Location: produk.php");
    exit;
}
?>