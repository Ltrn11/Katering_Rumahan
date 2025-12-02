<?php
session_start();
require '../koneksi.php';

// Redirect jika sudah login
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Cek di tabel admin
    $stmt = $koneksi->prepare("SELECT id, nama_admin, email, password FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data) {
        if (password_verify($password, $data['password'])) {
            // Set session admin (TANPA role, semua admin sama)
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $data['id'];
            $_SESSION['admin_nama'] = $data['nama_admin'];
            $_SESSION['admin_email'] = $data['email'];

            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak terdaftar sebagai admin!";
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
    <title>Login Admin - Katering Rumahan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            color: #667eea !important;
            font-size: 1.3rem;
        }
        
        .nav-link {
            color: #333 !important;
            font-weight: 500;
            padding: 8px 15px !important;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .nav-link:hover {
            background: #667eea;
            color: white !important;
        }
        
        .login-card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .security-notice {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 12px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" href="../dashboard.php">
            <i class="bi bi-egg-fried me-2"></i>Katering Rumahan
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../dashboard.php">
                        <i class="bi bi-house-door me-1"></i>Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../index.php">
                        <i class="bi bi-person me-1"></i>Login Pelanggan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="login.php">
                        <i class="bi bi-shield-lock me-1"></i>Login Admin
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container d-flex justify-content-center align-items-center" style="min-height:calc(100vh - 76px);">
    <div class="card login-card p-4 shadow" style="width:400px;">
        <div class="text-center mb-4">
            <i class="bi bi-shield-lock-fill text-primary" style="font-size: 3rem;"></i>
            <h3 class="mt-3">Login Admin</h3>
            <p class="text-muted">Katering Rumahan</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label">Email Admin</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" required placeholder="admin@katering.com">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" required placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="btn btn-login btn-primary w-100 py-2">
                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk ke Dashboard
            </button>
        </form>

        <div class="security-notice">
        <i class="bi bi-info-circle me-1"></i>
        <strong>Akun admin terbatas.</strong><br>
        <small>Hubungi pemilik sistem untuk mendapatkan akses:<br>
        <i class="bi bi-whatsapp me-1"></i> <a href="https://wa.me/6282249558367" target="_blank">+62 822-4955-8367</a><br>
        <i class="bi bi-envelope me-1"></i> <a href="mailto:samuel11lumbantoruan@gmail.com">samuel11lumbantoruan@gmail.com</a>
        </small>
        </div>

        
        <hr class="my-3">
        
        <div class="text-center">
            <small class="text-muted">Bukan admin?</small><br>
            <a href="../index.php" class="btn btn-sm btn-outline-primary mt-2">
                <i class="bi bi-person me-1"></i>Login sebagai Pelanggan
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>