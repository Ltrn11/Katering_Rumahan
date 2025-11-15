<?php
session_start();
include 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $cek = mysqli_query($conn, "SELECT * FROM pengguna WHERE email='$email'");
    if (mysqli_num_rows($cek) > 0) {
        $_SESSION['error'] = "Email sudah terdaftar!";
        header("Location: signup.php");
        exit;
    }

    $query = "INSERT INTO pengguna (nama_pengguna, email, kata_sandi, peran) 
              VALUES ('$nama', '$email', '$password', 'pelanggan')";
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Pendaftaran berhasil, silakan login!";
        header("Location: index.php");
        exit;
    } else {
        $_SESSION['error'] = "Terjadi kesalahan, coba lagi!";
        header("Location: signup.php");
        exit;
    }
}
?>
