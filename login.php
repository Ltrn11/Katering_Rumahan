<?php
session_start();
include 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // ubah nama tabel dan kolom agar sesuai database
    $query = mysqli_query($conn, "SELECT * FROM pengguna WHERE email='$email'");
    $user = mysqli_fetch_assoc($query);

    // kolom di database: kata_sandi
    if ($user && password_verify($password, $user['kata_sandi'])) {
        $_SESSION['user'] = $user['nama_pengguna']; // sesuaikan dengan nama kolom
        header("Location: home.php");
        exit;
    } else {
        $_SESSION['error'] = "Email atau password salah!";
        header("Location: index.php");
        exit;
    }
}
?>
