<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Cek apakah request dari form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Konfigurasi database
    $host = 'localhost';
    $dbname = 'katering_rumahan';
    $username = 'root';
    $password = '';
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
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
        $stmt = $conn->prepare("SELECT * FROM Produk WHERE id_produk = :id_produk AND tersedia = 1");
        $stmt->bindParam(':id_produk', $id_produk);
        $stmt->execute();
        $produk = $stmt->fetch(PDO::FETCH_ASSOC);
        
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
        
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
        header('Location: dashboard.php');
        exit();
    }
} else {
    // Kalau bukan POST, redirect ke dashboard
    header('Location: dashboard.php');
    exit();
}
?>