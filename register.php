<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katering Rumahan - Daftar</title>
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
            padding: 20px 0;
        }
        
        .register-container {
            max-width: 500px;
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
        
        .btn-register {
            background: linear-gradient(135deg, #ff6b00 0%, #ff8c00 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: transform 0.2s;
        }
        
        .btn-register:hover {
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
        
        .login-text {
            color: #6c757d;
        }
        
        .login-text a {
            color: #ff6b00;
            font-weight: 600;
            text-decoration: none;
        }
        
        .login-text a:hover {
            text-decoration: underline;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            animation: pulse 2s infinite;
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #218838 0%, #1a9d7c 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(40, 167, 69, 0.4);
        }
        
        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
            }
            50% {
                box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
            }
        }
    </style>
</head>
<body>
    <?php
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
            
            $nama = $_POST['nama_pengguna'];
            $email = $_POST['email'];
            $telepon = $_POST['telepon'];
            $pass = $_POST['password'];
            $confirm_pass = $_POST['confirm_password'];
            
            // Validasi input
            if (empty($nama) || empty($email) || empty($telepon) || empty($pass) || empty($confirm_pass)) {
                $message = 'Semua field harus diisi!';
                $message_type = 'danger';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = 'Format email tidak valid!';
                $message_type = 'danger';
            } elseif (strlen($pass) < 6) {
                $message = 'Password minimal 6 karakter!';
                $message_type = 'danger';
            } elseif ($pass !== $confirm_pass) {
                $message = 'Password dan konfirmasi password tidak cocok!';
                $message_type = 'danger';
            } else {
                // Cek apakah email sudah terdaftar
                $stmt = $conn->prepare("SELECT id_pengguna FROM Pengguna WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $message = 'Email sudah terdaftar!';
                    $message_type = 'danger';
                } else {
                    // Hash password
                    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
                    
                    // Insert user baru ke tabel Pengguna dengan peran 'pelanggan'
                    $stmt = $conn->prepare("INSERT INTO Pengguna (nama_pengguna, email, telepon, kata_sandi, peran) 
                                          VALUES (:nama, :email, :telepon, :kata_sandi, 'pelanggan')");
                    $stmt->bindParam(':nama', $nama);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':telepon', $telepon);
                    $stmt->bindParam(':kata_sandi', $hashed_password);
                    
                    if ($stmt->execute()) {
                        $message = 'Pendaftaran berhasil! Silakan login.';
                        $message_type = 'success';
                    } else {
                        $message = 'Pendaftaran gagal!';
                        $message_type = 'danger';
                    }
                }
            }
        } catch(PDOException $e) {
            $message = 'Koneksi database gagal: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
    ?>
    
    <div class="register-container">
        <div class="card">
            <div class="card-body">
                <div class="logo-circle">
                    <i class="bi bi-egg-fried"></i>
                </div>
                
                <h2 class="text-center fw-bold mb-2">Daftar Akun Baru</h2>
                <p class="text-center text-muted mb-4">Buat akun untuk mulai memesan makanan lezat</p>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <i class="bi bi-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    
                    <?php if ($message_type == 'success'): ?>
                        <div class="text-center mb-4">
                            <a href="index.php" class="btn btn-success btn-lg w-100">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login Sekarang
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <form method="POST" action="" id="registerForm">
                    <div class="mb-3">
                        <label for="nama_pengguna" class="form-label fw-semibold">Nama Lengkap</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="nama_pengguna" name="nama_pengguna" 
                                   placeholder="Masukkan nama lengkap" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="nama@email.com" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="telepon" class="form-label fw-semibold">Nomor Telepon</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                            <input type="tel" class="form-control" id="telepon" name="telepon" 
                                   placeholder="08123456789" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Minimal 6 karakter" required>
                        </div>
                        <small class="text-muted">Minimal 6 karakter</small>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label fw-semibold">Konfirmasi Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   placeholder="Ulangi password" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-register w-100 mb-3">
                        <i class="bi bi-person-plus me-2"></i>Daftar
                    </button>
                </form>
                
                <div class="text-center login-text">
                    Sudah punya akun? <a href="index.php">Masuk sekarang</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Validasi password match secara real-time
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Password tidak cocok');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });
        
        // Validasi nomor telepon (hanya angka)
        document.getElementById('telepon').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>