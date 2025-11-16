<?php
session_start();
require 'koneksi.php';

$error = "";

// Proses ketika form dikirim
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Cek user berdasarkan email
    $stmt = $koneksi->prepare("SELECT id_pengguna, kata_sandi FROM Pengguna WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email belum terdaftar!";
    }

    $stmt->close();
    $koneksi->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katering Rumahan - Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: url('https://images.unsplash.com/photo-1504674900247-0877df9cc836') no-repeat center center/cover;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-container {
            background-color: rgba(0,0,0,0.7);
            padding: 30px;
            border-radius: 15px;
            width: 350px;
        }
        .login-container h2 {
            color: white;
        }
        .form-label {
            color: #ddd;
        }
        .register-text {
            color: #aaa;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2 class="text-center mb-4">Login Pengguna</h2>

    <?php if (!empty($error)) : ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required placeholder="Masukkan email">
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required placeholder="Masukkan password">
        </div>

        <button type="submit" name="login" class="btn btn-warning w-100">Login</button>
    </form>

    <div class="text-center register-text mt-3">
        Belum punya akun? <a href="register.php" class="fw-bold text-warning">Daftar sekarang</a>
    </div>

    <!-- 🔥 Tambahan: Tombol Login Admin -->
    <div class="text-center mt-3">
        <a href="admin/login.php" class="text-white-50" style="text-decoration:none;">
            <i class="bi bi-shield-lock"></i> Login Admin
        </a>
    </div>

</div>

</body>
</html>
