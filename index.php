<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katering Rumahan - Login</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 15px;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        .card-body {
            padding: 40px;
        }
        
        .logo-circle {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #ff6b00 0%, #ff8c00 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            box-shadow: 0 5px 20px rgba(255,107,0,0.3);
        }
        
        .logo-circle i {
            font-size: 50px;
            color: white;
        }
        
        .form-control:focus {
            border-color: #ff6b00;
            box-shadow: 0 0 0 0.25rem rgba(255,107,0,0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #ff6b00 0%, #ff8c00 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: transform 0.2s;
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, #e55f00 0%, #e57c00 100%);
            transform: translateY(-2px);
        }
        
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
        }
        
        .form-control {
            border-left: none;
        }
        
        .register-text {
            color: #6c757d;
        }
        
        .register-text a {
            color: #ff6b00;
            font-weight: 600;
            text-decoration: none;
        }
        
        .register-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php
    session_start();
    
    // Konfigurasi database
    $host = 'localhost';
    $dbname = 'katering_rumahan';
    $username = 'root';
    $password = '';
    
    // Pesan error/success
    $message = '';
    $message_type = '';
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            // Koneksi ke database
            $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $email = $_POST['email'];
            $pass = $_POST['password'];
            
            // Validasi input
            if (empty($email) || empty($pass)) {
                $message = 'Email dan password harus diisi!';
                $message_type = 'danger';
            } else {
                // Query untuk cek user di tabel Pengguna
                $stmt = $conn->prepare("SELECT * FROM Pengguna WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($pass, $user['kata_sandi'])) {
                    // Login berhasil
                    $_SESSION['user_id'] = $user['id_pengguna'];
                    $_SESSION['nama_pengguna'] = $user['nama_pengguna'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['peran'] = $user['peran'];
                    
                    // Redirect berdasarkan peran
                    if ($user['peran'] == 'admin') {
                        header('Location: admin_dashboard.php');
                    } else {
                        header('Location: dashboard.php');
                    }
                    exit();
                } else {
                    $message = 'Email atau password salah!';
                    $message_type = 'danger';
                }
            }
        } catch(PDOException $e) {
            $message = 'Koneksi database gagal: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
    ?>
    
    <div class="login-container">
        <div class="card">
            <div class="card-body">
                <div class="logo-circle">
                    <i class="bi bi-egg-fried"></i>
                </div>
                
                <h2 class="text-center fw-bold mb-2">Katering Rumahan</h2>
                <p class="text-center text-muted mb-4">Masuk ke akun Anda untuk memesan makanan lezat</p>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i><?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="nama@email.com" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="••••••••" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-login w-100 mb-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                    </button>
                </form>
                
                <div class="text-center register-text">
                    Belum punya akun? <a href="register.php" class="fw-bold">Daftar sekarang</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>