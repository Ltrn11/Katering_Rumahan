<?php
session_start();
require '../koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_inventori = (int)$_POST['id_inventori'];
    $perubahan_stok = (int)$_POST['perubahan_stok'];

    // Cek stok saat ini
    $check = $koneksi->query("SELECT jumlah_stok FROM Inventori WHERE id_inventori = $id_inventori");
    
    if ($check && $check->num_rows > 0) {
        $current = $check->fetch_assoc();
        $stok_baru = $current['jumlah_stok'] + $perubahan_stok;
        
        // Pastikan stok tidak negatif
        if ($stok_baru < 0) {
            $_SESSION['error'] = "Stok tidak boleh negatif! Stok saat ini: " . $current['jumlah_stok'];
        } else {
            $stmt = $koneksi->prepare("UPDATE Inventori SET jumlah_stok = jumlah_stok + ? WHERE id_inventori = ?");
            $stmt->bind_param("ii", $perubahan_stok, $id_inventori);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Stok berhasil diupdate! Stok baru: " . $stok_baru;
            } else {
                $_SESSION['error'] = "Gagal mengupdate stok: " . $koneksi->error;
            }

            $stmt->close();
        }
    } else {
        $_SESSION['error'] = "Data inventori tidak ditemukan!";
    }

    $koneksi->close();
}

header("Location: inventori.php");
exit;
?>