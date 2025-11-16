<?php
session_start();
require 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Cek apakah cart kosong
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    header('Location: dashboard.php');
    exit();
}

// Get user info
$stmt = $koneksi->prepare("SELECT * FROM Pengguna WHERE id_pengguna = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Hitung total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['harga'] * $item['jumlah'];
}

// Process checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $alamat = trim($_POST['alamat']);
    $catatan = trim($_POST['catatan']);
    $metode_pembayaran = $_POST['metode_pembayaran'];
    
    // Validasi
    if (empty($alamat)) {
        $_SESSION['error'] = 'Alamat pengiriman harus diisi!';
    } else {
        // Start transaction
        $koneksi->begin_transaction();
        
        try {
            // Insert ke tabel Pesanan
            $stmt = $koneksi->prepare("INSERT INTO Pesanan (id_pelanggan, total_jumlah, status, alamat_pengiriman, catatan) 
                                      VALUES (?, ?, 'pending', ?, ?)");
            $stmt->bind_param("idss", $_SESSION['user_id'], $total, $alamat, $catatan);
            $stmt->execute();
            $id_pesanan = $koneksi->insert_id;
            $stmt->close();
            
            // Insert ke tabel Item_Pesanan
            $stmt_item = $koneksi->prepare("INSERT INTO Item_Pesanan (id_pesanan, id_produk, jumlah, harga_satuan) 
                                           VALUES (?, ?, ?, ?)");
            foreach ($_SESSION['cart'] as $item) {
                $stmt_item->bind_param("iiid", $id_pesanan, $item['id_produk'], $item['jumlah'], $item['harga']);
                $stmt_item->execute();
            }
            $stmt_item->close();
            
            // Insert ke tabel Pembayaran
            $stmt_bayar = $koneksi->prepare("INSERT INTO Pembayaran (id_pesanan, metode_pembayaran, jumlah, status) 
                                            VALUES (?, ?, ?, 'pending')");
            $stmt_bayar->bind_param("isd", $id_pesanan, $metode_pembayaran, $total);
            $stmt_bayar->execute();
            $stmt_bayar->close();
            
            // Insert ke tabel Pengiriman
            $stmt_kirim = $koneksi->prepare("INSERT INTO Pengiriman (id_pesanan, status) VALUES (?, 'pending')");
            $stmt_kirim->bind_param("i", $id_pesanan);
            $stmt_kirim->execute();
            $stmt_kirim->close();
            
            // Commit transaction
            $koneksi->commit();
            
            // Hapus cart
            unset($_SESSION['cart']);
            
            // Redirect ke halaman sukses
            $_SESSION['success'] = 'Pesanan berhasil dibuat! Nomor pesanan: #' . $id_pesanan;
            header('Location: order_success.php?id=' . $id_pesanan);
            exit();
            
        } catch (Exception $e) {
            $koneksi->rollback();
            $_SESSION['error'] = 'Gagal membuat pesanan: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Katering Rumahan</title>
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
            color: white !important;
            font-weight: 500;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--secondary-blue) 0%, var(--primary-blue) 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        
        .checkout-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .order-summary {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
        }
        
        .order-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .btn-order {
            background: var(--orange);
            border: none;
            color: white;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn-order:hover {
            background: #e55a25;
            transform: scale(1.02);
        }
        
        .payment-option {
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .payment-option:hover {
            border-color: var(--primary-blue);
        }
        
        .payment-option input[type="radio"] {
            margin-right: 10px;
        }
        
        .payment-option.selected {
            border-color: var(--primary-blue);
            background: rgba(74, 144, 226, 0.1);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-egg-fried me-2"></i>Katering Rumahan
            </a>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 class="mb-0"><i class="bi bi-credit-card me-2"></i>Checkout</h1>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mb-5">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="checkoutForm">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Info Pemesan -->
                    <div class="checkout-section">
                        <h4 class="mb-4"><i class="bi bi-person me-2"></i>Informasi Pemesan</h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Nama Lengkap</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['nama_pengguna']); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">No. Telepon</label>
                                <input type="tel" class="form-control" value="<?php echo htmlspecialchars($user['telepon']); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Alamat Pengiriman -->
                    <div class="checkout-section">
                        <h4 class="mb-4"><i class="bi bi-geo-alt me-2"></i>Alamat Pengiriman</h4>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="alamat" rows="3" placeholder="Masukkan alamat lengkap pengiriman" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Catatan (Opsional)</label>
                            <textarea class="form-control" name="catatan" rows="2" placeholder="Contoh: Tolong diantar sebelum jam 12 siang"></textarea>
                        </div>
                    </div>

                    <!-- Metode Pembayaran -->
                    <div class="checkout-section">
                        <h4 class="mb-4"><i class="bi bi-wallet2 me-2"></i>Metode Pembayaran</h4>
                        
                        <label class="payment-option">
                            <input type="radio" name="metode_pembayaran" value="cash" checked>
                            <i class="bi bi-cash-coin fs-4 me-2"></i>
                            <strong>Bayar di Tempat (COD)</strong>
                            <p class="text-muted mb-0 ms-4">Bayar langsung saat pesanan diterima</p>
                        </label>

                        <label class="payment-option">
                            <input type="radio" name="metode_pembayaran" value="transfer">
                            <i class="bi bi-bank fs-4 me-2"></i>
                            <strong>Transfer Bank</strong>
                            <p class="text-muted mb-0 ms-4">Transfer ke rekening yang akan diberikan</p>
                        </label>

                        <label class="payment-option">
                            <input type="radio" name="metode_pembayaran" value="ewallet">
                            <i class="bi bi-phone fs-4 me-2"></i>
                            <strong>E-Wallet</strong>
                            <p class="text-muted mb-0 ms-4">Gopay, OVO, Dana, ShopeePay</p>
                        </label>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Ringkasan Pesanan -->
                    <div class="order-summary">
                        <h4 class="mb-4">Ringkasan Pesanan</h4>
                        
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="order-item">
                                <div class="d-flex justify-content-between mb-1">
                                    <span><?php echo htmlspecialchars($item['nama_produk']); ?></span>
                                    <span class="text-muted">x<?php echo $item['jumlah']; ?></span>
                                </div>
                                <div class="text-end">
                                    <strong>Rp <?php echo number_format($item['harga'] * $item['jumlah'], 0, ',', '.'); ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <hr class="my-3">
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span>Biaya Pengiriman</span>
                            <strong class="text-success">Gratis</strong>
                        </div>
                        
                        <hr class="my-3">
                        
                        <div class="d-flex justify-content-between mb-4">
                            <h5>Total Bayar</h5>
                            <h5 class="text-primary">Rp <?php echo number_format($total, 0, ',', '.'); ?></h5>
                        </div>
                        
                        <button type="submit" class="btn btn-order">
                            <i class="bi bi-check-circle me-2"></i>Buat Pesanan
                        </button>
                        
                        <a href="cart.php" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="bi bi-arrow-left me-2"></i>Kembali ke Keranjang
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Highlight selected payment method
        document.querySelectorAll('.payment-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.payment-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                this.classList.add('selected');
                this.querySelector('input[type="radio"]').checked = true;
            });
        });
        
        // Set default selected
        document.querySelector('.payment-option input:checked').closest('.payment-option').classList.add('selected');
    </script>
</body>
</html>
<?php
$koneksi->close();
?>