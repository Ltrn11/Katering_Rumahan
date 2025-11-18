<?php
session_start();
require '../koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$id_pesanan = (int)$_GET['id'];

// Get detail pesanan
$stmt = $koneksi->prepare("SELECT p.*, u.nama_pengguna, u.email, u.telepon, pm.metode_pembayaran, pm.status as status_bayar
                          FROM Pesanan p
                          JOIN Pengguna u ON p.id_pelanggan = u.id_pengguna
                          LEFT JOIN Pembayaran pm ON p.id_pesanan = pm.id_pesanan
                          WHERE p.id_pesanan = ?");
$stmt->bind_param("i", $id_pesanan);
$stmt->execute();
$result = $stmt->get_result();
$pesanan = $result->fetch_assoc();
$stmt->close();

if (!$pesanan) {
    header('Location: pesanan.php');
    exit();
}

// Get item pesanan
$stmt = $koneksi->prepare("SELECT ip.*, p.nama_produk 
                          FROM Item_Pesanan ip
                          JOIN Produk p ON ip.id_produk = p.id_produk
                          WHERE ip.id_pesanan = ?");
$stmt->bind_param("i", $id_pesanan);
$stmt->execute();
$result = $stmt->get_result();
$items = [];
while($row = $result->fetch_assoc()) {
    $items[] = $row;
}
$stmt->close();

// Get info pengiriman
$stmt = $koneksi->prepare("SELECT * FROM Pengiriman WHERE id_pesanan = ?");
$stmt->bind_param("i", $id_pesanan);
$stmt->execute();
$result = $stmt->get_result();
$pengiriman = $result->fetch_assoc();
$stmt->close();

// Get umpan balik (jika ada)
$stmt = $koneksi->prepare("SELECT * FROM Umpan_Balik_Pelanggan WHERE id_pesanan = ?");
$stmt->bind_param("i", $id_pesanan);
$stmt->execute();
$result = $stmt->get_result();
$feedback = $result->fetch_assoc();
$stmt->close();

// Handle update status pesanan
if (isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $stmt = $koneksi->prepare("UPDATE Pesanan SET status = ? WHERE id_pesanan = ?");
    $stmt->bind_param("si", $new_status, $id_pesanan);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Status pesanan berhasil diperbarui!';
        $pesanan['status'] = $new_status;
    }
    $stmt->close();
}

// Handle update status pengiriman
if (isset($_POST['update_pengiriman'])) {
    $status_kirim = $_POST['status_pengiriman'];
    $stmt = $koneksi->prepare("UPDATE Pengiriman SET status = ? WHERE id_pesanan = ?");
    $stmt->bind_param("si", $status_kirim, $id_pesanan);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Status pengiriman berhasil diperbarui!';
        $pengiriman['status'] = $status_kirim;
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
    <title>Detail Pesanan #<?php echo $id_pesanan; ?> - Admin</title>
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
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            color: white !important;
            font-size: 1.3rem;
        }
        
        .nav-link {
            color: white !important;
            font-weight: 500;
        }
        
        .page-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        
        .detail-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .info-row {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .status-badge {
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-diproses { background: #cfe2ff; color: #084298; }
        .status-dikirim { background: #d1e7dd; color: #0a3622; }
        .status-selesai { background: #d1e7dd; color: #0f5132; }
        .status-dibatalkan { background: #f8d7da; color: #842029; }
        
        .btn-update {
            background: var(--primary-blue);
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 6px;
            font-weight: 600;
        }
        
        .btn-update:hover {
            background: #3a7bc8;
        }
        
        .star-rating {
            color: #FFD700;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-shield-check me-2"></i>Admin Panel
            </a>
            <div class="ms-auto">
                <a href="pesanan.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 class="mb-0">
                <i class="bi bi-receipt me-2"></i>Detail Pesanan #<?php echo str_pad($pesanan['id_pesanan'], 5, '0', STR_PAD_LEFT); ?>
            </h1>
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

        <div class="row">
            <!-- Info Pesanan -->
            <div class="col-lg-8">
                <!-- Status & Aksi -->
                <div class="detail-card">
                    <h5 class="mb-3"><i class="bi bi-gear me-2"></i>Status & Aksi</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Status Pesanan</label>
                            <form method="POST" action="">
                                <div class="input-group">
                                    <select class="form-select" name="status" required>
                                        <option value="pending" <?php echo $pesanan['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="diproses" <?php echo $pesanan['status'] == 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                                        <option value="dikirim" <?php echo $pesanan['status'] == 'dikirim' ? 'selected' : ''; ?>>Dikirim</option>
                                        <option value="selesai" <?php echo $pesanan['status'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                        <option value="dibatalkan" <?php echo $pesanan['status'] == 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-update">Update</button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Status Pengiriman</label>
                            <form method="POST" action="">
                                <div class="input-group">
                                    <select class="form-select" name="status_pengiriman" required>
                                        <option value="pending" <?php echo $pengiriman['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="dijemput" <?php echo $pengiriman['status'] == 'dijemput' ? 'selected' : ''; ?>>Dijemput</option>
                                        <option value="dalam_perjalanan" <?php echo $pengiriman['status'] == 'dalam_perjalanan' ? 'selected' : ''; ?>>Dalam Perjalanan</option>
                                        <option value="selesai" <?php echo $pengiriman['status'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                    </select>
                                    <button type="submit" name="update_pengiriman" class="btn btn-update">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Detail Item -->
                <div class="detail-card">
                    <h5 class="mb-3"><i class="bi bi-basket me-2"></i>Detail Pesanan</h5>
                    <?php foreach ($items as $item): ?>
                        <div class="info-row">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo htmlspecialchars($item['nama_produk']); ?></strong>
                                    <span class="text-muted ms-2">x<?php echo $item['jumlah']; ?></span>
                                </div>
                                <div class="text-end">
                                    <div class="text-muted small">Rp <?php echo number_format($item['harga_satuan'], 0, ',', '.'); ?> / item</div>
                                    <strong>Rp <?php echo number_format($item['harga_satuan'] * $item['jumlah'], 0, ',', '.'); ?></strong>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="info-row mt-3 pt-3">
                        <div class="d-flex justify-content-between">
                            <h5>Total Pembayaran</h5>
                            <h5 class="text-primary">Rp <?php echo number_format($pesanan['total_jumlah'], 0, ',', '.'); ?></h5>
                        </div>
                    </div>
                </div>

                <!-- Alamat Pengiriman -->
                <div class="detail-card">
                    <h5 class="mb-3"><i class="bi bi-geo-alt me-2"></i>Alamat Pengiriman</h5>
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($pesanan['alamat_pengiriman'])); ?></p>
                    <?php if (!empty($pesanan['catatan'])): ?>
                        <div class="mt-3 p-3 bg-light rounded">
                            <strong>Catatan:</strong><br>
                            <?php echo nl2br(htmlspecialchars($pesanan['catatan'])); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Umpan Balik -->
                <?php if ($feedback): ?>
                    <div class="detail-card">
                        <h5 class="mb-3"><i class="bi bi-star me-2"></i>Umpan Balik Pelanggan</h5>
                        <div class="star-rating mb-2">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star<?php echo $i <= $feedback['pemberian'] ? '-fill' : ''; ?>"></i>
                            <?php endfor; ?>
                            <span class="text-muted ms-2">(<?php echo $feedback['pemberian']; ?>/5)</span>
                        </div>
                        <?php if (!empty($feedback['komentar'])): ?>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($feedback['komentar'])); ?></p>
                        <?php endif; ?>
                        <small class="text-muted">
                            <i class="bi bi-clock me-1"></i><?php echo date('d M Y, H:i', strtotime($feedback['tanggal_umpan_balik'])); ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Info Pelanggan & Pembayaran -->
            <div class="col-lg-4">
                <!-- Info Pelanggan -->
                <div class="detail-card">
                    <h5 class="mb-3"><i class="bi bi-person me-2"></i>Info Pelanggan</h5>
                    <div class="info-row">
                        <small class="text-muted">Nama</small>
                        <div><?php echo htmlspecialchars($pesanan['nama_pengguna']); ?></div>
                    </div>
                    <div class="info-row">
                        <small class="text-muted">Email</small>
                        <div><?php echo htmlspecialchars($pesanan['email']); ?></div>
                    </div>
                    <div class="info-row">
                        <small class="text-muted">Telepon</small>
                        <div><?php echo htmlspecialchars($pesanan['telepon']); ?></div>
                    </div>
                </div>

                <!-- Info Pembayaran -->
                <div class="detail-card">
                    <h5 class="mb-3"><i class="bi bi-wallet2 me-2"></i>Pembayaran</h5>
                    <div class="info-row">
                        <small class="text-muted">Metode</small>
                        <div class="fw-bold">
                            <?php 
                            $metode = [
                                'cash' => 'Bayar di Tempat (COD)',
                                'transfer' => 'Transfer Bank',
                                'ewallet' => 'E-Wallet',
                                'kartu' => 'Kartu Kredit/Debit'
                            ];
                            echo $metode[$pesanan['metode_pembayaran']] ?? $pesanan['metode_pembayaran'];
                            ?>
                        </div>
                    </div>
                    <div class="info-row">
                        <small class="text-muted">Status Bayar</small>
                        <div>
                            <span class="badge <?php echo $pesanan['status_bayar'] == 'berhasil' ? 'bg-success' : 'bg-warning'; ?>">
                                <?php echo ucfirst($pesanan['status_bayar']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="info-row">
                        <small class="text-muted">Total</small>
                        <div class="fw-bold text-primary">Rp <?php echo number_format($pesanan['total_jumlah'], 0, ',', '.'); ?></div>
                    </div>
                </div>

                <!-- Info Waktu -->
                <div class="detail-card">
                    <h5 class="mb-3"><i class="bi bi-clock me-2"></i>Informasi Waktu</h5>
                    <div class="info-row">
                        <small class="text-muted">Tanggal Pesanan</small>
                        <div><?php echo date('d M Y, H:i', strtotime($pesanan['tanggal_pesanan'])); ?></div>
                    </div>
                    <?php if ($pengiriman['waktu_pengiriman']): ?>
                        <div class="info-row">
                            <small class="text-muted">Waktu Pengiriman</small>
                            <div><?php echo date('d M Y, H:i', strtotime($pengiriman['waktu_pengiriman'])); ?></div>
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