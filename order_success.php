<?php
session_start();
require 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Cek apakah ada ID pesanan
if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$id_pesanan = (int)$_GET['id'];

// Get detail pesanan
$stmt = $koneksi->prepare("SELECT p.*, u.nama_pengguna, u.email, u.telepon, pm.metode_pembayaran 
                          FROM Pesanan p
                          JOIN Pengguna u ON p.id_pelanggan = u.id_pengguna
                          LEFT JOIN Pembayaran pm ON p.id_pesanan = pm.id_pesanan
                          WHERE p.id_pesanan = ? AND p.id_pelanggan = ?");
$stmt->bind_param("ii", $id_pesanan, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$pesanan = $result->fetch_assoc();
$stmt->close();

if (!$pesanan) {
    header('Location: dashboard.php');
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
$koneksi->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil - Katering Rumahan</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-blue: #4A90E2;
            --secondary-blue: #5BA3F5;
            --orange: #FF6B35;
            --success-green: #28a745;
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
        
        .success-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--success-green), #20c997);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease-out;
        }
        
        .success-icon i {
            font-size: 60px;
            color: white;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }
        
        .success-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 30px;
        }
        
        .order-details {
            background: white;
            border-radius: 10px;
            padding: 30px;
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
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .btn-primary-custom {
            background: var(--primary-blue);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary-custom:hover {
            background: #3a7bc8;
            transform: translateY(-2px);
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark no-print">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-egg-fried me-2"></i>Katering Rumahan
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-5">
        <!-- Success Message -->
        <div class="success-card">
            <div class="success-icon">
                <i class="bi bi-check-lg"></i>
            </div>
            <h2 class="fw-bold mb-3">Pesanan Berhasil Dibuat!</h2>
            <p class="text-muted mb-4">Terima kasih telah memesan di Katering Rumahan</p>
            <div class="mb-4">
                <h4>Nomor Pesanan</h4>
                <h2 class="text-primary fw-bold">#<?php echo str_pad($pesanan['id_pesanan'], 5, '0', STR_PAD_LEFT); ?></h2>
            </div>
            <span class="status-badge status-pending">
                <i class="bi bi-clock me-2"></i><?php echo ucfirst($pesanan['status']); ?>
            </span>
        </div>

        <div class="row">
            <!-- Detail Pesanan -->
            <div class="col-lg-8">
                <div class="order-details mb-4">
                    <h4 class="mb-4"><i class="bi bi-receipt me-2"></i>Detail Pesanan</h4>
                    
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

                <div class="order-details">
                    <h4 class="mb-4"><i class="bi bi-geo-alt me-2"></i>Alamat Pengiriman</h4>
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($pesanan['alamat_pengiriman'])); ?></p>
                    <?php if (!empty($pesanan['catatan'])): ?>
                        <div class="mt-3 p-3 bg-light rounded">
                            <strong>Catatan:</strong><br>
                            <?php echo nl2br(htmlspecialchars($pesanan['catatan'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Info Pembayaran -->
            <div class="col-lg-4">
                <div class="order-details mb-4">
                    <h4 class="mb-4"><i class="bi bi-wallet2 me-2"></i>Pembayaran</h4>
                    
                    <div class="info-row">
                        <small class="text-muted">Metode Pembayaran</small>
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
                    
                    <?php if ($pesanan['metode_pembayaran'] == 'transfer'): ?>
                        <div class="alert alert-info mt-3">
                            <strong>Rekening Transfer:</strong><br>
                            Bank BCA<br>
                            1234567890<br>
                            a.n Katering Rumahan
                        </div>
                    <?php elseif ($pesanan['metode_pembayaran'] == 'ewallet'): ?>
                        <div class="alert alert-info mt-3">
                            <strong>E-Wallet:</strong><br>
                            0812-3456-7890<br>
                            a.n Katering Rumahan
                        </div>
                    <?php endif; ?>
                </div>

                <div class="order-details mb-4">
                    <h4 class="mb-4"><i class="bi bi-person me-2"></i>Info Pemesan</h4>
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
                    <div class="info-row">
                        <small class="text-muted">Tanggal Pesanan</small>
                        <div><?php echo date('d M Y, H:i', strtotime($pesanan['tanggal_pesanan'])); ?></div>
                    </div>
                </div>

                <a href="dashboard.php" class="btn btn-primary-custom w-100 mb-2 no-print">
                    <i class="bi bi-house me-2"></i>Kembali ke Beranda
                </a>
                
                <button onclick="window.print()" class="btn btn-outline-primary w-100 no-print">
                    <i class="bi bi-printer me-2"></i>Cetak Pesanan
                </button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>