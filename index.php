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
            
            // Cek apakah ada produk yang pending (mau ditambah sebelum login)
            if (isset($_SESSION['pending_cart'])) {
                $pending = $_SESSION['pending_cart'];
                
                // Get info produk
                $stmt2 = $koneksi->prepare("SELECT * FROM Produk WHERE id_produk = ?");
                $stmt2->bind_param("i", $pending['id_produk']);
                $stmt2->execute();
                $result = $stmt2->get_result();
                $produk = $result->fetch_assoc();
                
                if ($produk) {
                    // Inisialisasi cart
                    if (!isset($_SESSION['cart'])) {
                        $_SESSION['cart'] = array();
                    }
                    
                    // Tambah ke cart
                    $_SESSION['cart'][$pending['id_produk']] = array(
                        'id_produk' => $produk['id_produk'],
                        'nama_produk' => $produk['nama_produk'],
                        'harga' => $produk['harga'],
                        'jumlah' => $pending['jumlah']
                    );
                    
                    $_SESSION['success'] = 'Login berhasil! Produk berhasil ditambahkan ke keranjang.';
                }
                
                // Hapus pending cart
                unset($_SESSION['pending_cart']);
                $stmt2->close();
            }
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email belum terdaftar!";
    }

    $stmt->close();
}

$koneksi->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katering Rumahan - Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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
            backdrop-filter: blur(10px);
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
        .btn-warning:hover {
            background: #e0a800;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="text-center mb-3">
        <i class="bi bi-egg-fried" style="font-size: 3rem; color: #ffc107;"></i>
    </div>
    <h2 class="text-center mb-4">Login Pengguna</h2>

    <?php if (!empty($error)) : ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-circle me-2"></i><?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])) : ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="bi bi-info-circle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <div class="input-group">
                <span class="input-group-text bg-dark text-white border-secondary">
                    <i class="bi bi-envelope"></i>
                </span>
                <input type="email" name="email" class="form-control" required placeholder="Masukkan email">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-dark text-white border-secondary">
                    <i class="bi bi-lock"></i>
                </span>
                <input type="password" name="password" class="form-control" required placeholder="Masukkan password">
            </div>
        </div>

        <button type="submit" name="login" class="btn btn-warning w-100 fw-bold">
            <i class="bi bi-box-arrow-in-right me-2"></i>Login
        </button>
    </form>

    <div class="text-center register-text mt-3">
        Belum punya akun? <a href="register.php" class="fw-bold text-warning">Daftar sekarang</a>
    </div>

    <hr class="text-white my-3">

    <div class="text-center">
        <a href="dashboard.php" class="text-white-50" style="text-decoration:none;">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
        </a>
    </div>

    <!-- 🔥 Tambahan: Tombol Login Admin -->
    <div class="text-center mt-3">
        <a href="admin/login.php" class="text-white-50" style="text-decoration:none;">
            <i class="bi bi-shield-lock"></i> Login Admin
        </a>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>