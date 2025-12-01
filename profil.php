<?php
session_start();
require 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Get user info
$stmt = $koneksi->prepare("SELECT * FROM Pengguna WHERE id_pengguna = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle update profil
if (isset($_POST['update_profil'])) {
    $nama = trim($_POST['nama_pengguna']);
    $email = trim($_POST['email']);
    $telepon = trim($_POST['telepon']);
    
    // Validasi
    if (empty($nama) || empty($email) || empty($telepon)) {
        $_SESSION['error'] = 'Semua field harus diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Format email tidak valid!';
    } else {
        // Cek apakah email sudah digunakan user lain
        $stmt = $koneksi->prepare("SELECT id_pengguna FROM Pengguna WHERE email = ? AND id_pengguna != ?");
        $stmt->bind_param("si", $email, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $_SESSION['error'] = 'Email sudah digunakan oleh user lain!';
        } else {
            // Update profil
            $stmt = $koneksi->prepare("UPDATE Pengguna SET nama_pengguna = ?, email = ?, telepon = ? WHERE id_pengguna = ?");
            $stmt->bind_param("sssi", $nama, $email, $telepon, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Profil berhasil diperbarui!';
                // Refresh data user
                $user['nama_pengguna'] = $nama;
                $user['email'] = $email;
                $user['telepon'] = $telepon;
            } else {
                $_SESSION['error'] = 'Gagal memperbarui profil!';
            }
        }
        $stmt->close();
    }
}

// Handle ganti password
if (isset($_POST['ganti_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    
    // Validasi
    if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
        $_SESSION['error'] = 'Semua field password harus diisi!';
    } elseif (strlen($password_baru) < 6) {
        $_SESSION['error'] = 'Password baru minimal 6 karakter!';
    } elseif ($password_baru !== $konfirmasi_password) {
        $_SESSION['error'] = 'Password baru dan konfirmasi tidak cocok!';
    } else {
        // Cek password lama
        if (password_verify($password_lama, $user['kata_sandi'])) {
            // Hash password baru
            $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
            
            // Update password
            $stmt = $koneksi->prepare("UPDATE Pengguna SET kata_sandi = ? WHERE id_pengguna = ?");
            $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Password berhasil diubah!';
            } else {
                $_SESSION['error'] = 'Gagal mengubah password!';
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = 'Password lama salah!';
        }
    }
}

// Get riwayat pesanan (5 terakhir)
$stmt = $koneksi->prepare("SELECT * FROM Pesanan WHERE id_pelanggan = ? ORDER BY tanggal_pesanan DESC LIMIT 5");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$pesanan_list = [];
while($row = $result->fetch_assoc()) {
    $pesanan_list[] = $row;
}
$stmt->close();

// Hitung jumlah item di cart
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

$koneksi->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Katering Rumahan</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-blue: #4A90E2;
            --secondary-blue: #5BA3F5;
            --orange: #FF6B35;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--secondary-blue) 0%, var(--primary-blue) 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            color: white !important;
            font-size: 1.3rem;
        }
        
       .nav-link {
            color: rgba(255,255,255,0.8) !important;
            font-weight: 500;
            transition: all 0.3s;
            padding: 8px 15px !important;
            border-radius: 5px;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.2);
            color: white !important;
        }
        
        .nav-link.active {
            background: rgba(255,255,255,0.25) !important;
            color: white !important;
            font-weight: 600;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--secondary-blue) 0%, var(--primary-blue) 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        
        .profile-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--secondary-blue) 0%, var(--primary-blue) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .profile-avatar i {
            font-size: 50px;
            color: white;
        }
        
        .btn-update {
            background: var(--primary-blue);
            border: none;
            color: white;
            padding: 10px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-update:hover {
            background: #3a7bc8;
            transform: translateY(-2px);
        }
        
        .order-item {
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }
        
        .order-item:hover {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-diproses { background: #cfe2ff; color: #084298; }
        .status-dikirim { background: #d1e7dd; color: #0a3622; }
        .status-selesai { background: #d1e7dd; color: #0f5132; }
        .status-dibatalkan { background: #f8d7da; color: #842029; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-egg-fried me-2"></i>Katering Rumahan
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-house-door me-1"></i>Menu
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            <i class="bi bi-cart me-1"></i>Pesanan
                            <?php if ($cart_count > 0): ?>
                                <span class="badge bg-danger"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="profil.php">
                            <i class="bi bi-person me-1"></i>Profil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i>Keluar
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 class="mb-0"><i class="bi bi-person-circle me-2"></i>Profil Saya</h1>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mb-5">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-4">
                <!-- Profile Info Card -->
                <div class="profile-card text-center">
                    <div class="profile-avatar">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <h4 class="mb-1"><?php echo htmlspecialchars($user['nama_pengguna']); ?></h4>
                    <p class="text-muted mb-2"><?php echo htmlspecialchars($user['email']); ?></p>
                    <p class="text-muted mb-3"><i class="bi bi-telephone me-2"></i><?php echo htmlspecialchars($user['telepon']); ?></p>
                    <span class="badge bg-primary">
                        <i class="bi bi-shield-check me-1"></i><?php echo ucfirst($user['peran']); ?>
                    </span>
                    <hr class="my-3">
                    <small class="text-muted">
                        <i class="bi bi-calendar me-1"></i>Bergabung: <?php echo date('d M Y', strtotime($user['dibuat_pada'])); ?>
                    </small>
                </div>
            </div>

            <div class="col-lg-8">
                <!-- Edit Profil -->
                <div class="profile-card">
                    <h4 class="mb-4"><i class="bi bi-pencil-square me-2"></i>Edit Profil</h4>
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama_pengguna" 
                                       value="<?php echo htmlspecialchars($user['nama_pengguna']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Nomor Telepon</label>
                                <input type="tel" class="form-control" name="telepon" 
                                       value="<?php echo htmlspecialchars($user['telepon']); ?>" required>
                            </div>
                        </div>
                        <button type="submit" name="update_profil" class="btn btn-update">
                            <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
                        </button>
                    </form>
                </div>

                <!-- Ganti Password -->
                <div class="profile-card">
                    <h4 class="mb-4"><i class="bi bi-key me-2"></i>Ganti Password</h4>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password Lama</label>
                            <input type="password" class="form-control" name="password_lama" 
                                   placeholder="Masukkan password lama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password Baru</label>
                            <input type="password" class="form-control" name="password_baru" 
                                   placeholder="Minimal 6 karakter" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" name="konfirmasi_password" 
                                   placeholder="Ulangi password baru" required>
                        </div>
                        <button type="submit" name="ganti_password" class="btn btn-update">
                            <i class="bi bi-shield-lock me-2"></i>Ganti Password
                        </button>
                    </form>
                </div>

                <!-- Riwayat Pesanan -->
                <div class="profile-card">
                    <h4 class="mb-4"><i class="bi bi-clock-history me-2"></i>Riwayat Pesanan Terakhir</h4>
                    
                    <?php if (count($pesanan_list) > 0): ?>
                        <?php foreach ($pesanan_list as $pesanan): ?>
                            <div class="order-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>#<?php echo str_pad($pesanan['id_pesanan'], 5, '0', STR_PAD_LEFT); ?></strong>
                                        <span class="text-muted ms-2"><?php echo date('d M Y', strtotime($pesanan['tanggal_pesanan'])); ?></span>
                                        <?php if ($pesanan['status'] == 'selesai'): ?>
                                            <a href="umpan_balik.php?id=<?php echo $pesanan['id_pesanan']; ?>" 
                                               class="btn btn-sm btn-outline-warning ms-2">
                                                <i class="bi bi-star me-1"></i>Beri Rating
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-end">
                                        <div><strong>Rp <?php echo number_format($pesanan['total_jumlah'], 0, ',', '.'); ?></strong></div>
                                        <span class="status-badge status-<?php echo $pesanan['status']; ?>">
                                            <?php echo ucfirst($pesanan['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <p class="mt-3">Belum ada riwayat pesanan</p>
                            <a href="dashboard.php" class="btn btn-sm btn-primary">Mulai Belanja</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>