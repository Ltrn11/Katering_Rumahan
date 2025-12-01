<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Handle update quantity
if (isset($_POST['update_cart'])) {
    $id_produk = (int)$_POST['id_produk'];
    $jumlah = (int)$_POST['jumlah'];
    
    if ($jumlah > 0 && isset($_SESSION['cart'][$id_produk])) {
        $_SESSION['cart'][$id_produk]['jumlah'] = $jumlah;
        $_SESSION['success'] = 'Keranjang berhasil diupdate!';
    }
}

// Handle hapus item
if (isset($_GET['remove'])) {
    $id_produk = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$id_produk])) {
        unset($_SESSION['cart'][$id_produk]);
        $_SESSION['success'] = 'Produk berhasil dihapus dari keranjang!';
    }
}

// Hitung total
$total = 0;
if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['harga'] * $item['jumlah'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - Katering Rumahan</title>
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
        
        .cart-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .cart-summary {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
        }
        
        .btn-checkout {
            background: var(--orange);
            border: none;
            color: white;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn-checkout:hover {
            background: #e55a25;
            transform: scale(1.02);
        }
        
        .btn-continue {
            background: var(--primary-blue);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-continue:hover {
            background: #3a7bc8;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
        }
        
        .quantity-btn {
            width: 30px;
            height: 30px;
            border: 2px solid #ddd;
            background: white;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .quantity-input {
            width: 50px;
            text-align: center;
            border: 2px solid #ddd;
            border-radius: 5px;
            margin: 0 10px;
            font-weight: 600;
        }
        
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-cart i {
            font-size: 5rem;
            margin-bottom: 20px;
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
                        <a class="nav-link active" href="cart.php">
                            <i class="bi bi-cart me-1"></i>Pesanan
                            <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                                <span class="badge bg-danger"><?php echo count($_SESSION['cart']); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profil.php">
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
            <h1 class="mb-0"><i class="bi bi-cart3 me-2"></i>Keranjang Belanja</h1>
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

        <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="mb-3">
                        <a href="dashboard.php" class="btn btn-continue">
                            <i class="bi bi-arrow-left me-2"></i>Lanjut Belanja
                        </a>
                    </div>
                    
                    <?php foreach ($_SESSION['cart'] as $id_produk => $item): ?>
                        <div class="cart-item">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($item['nama_produk']); ?></h5>
                                    <p class="text-muted mb-0">Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?> / item</p>
                                </div>
                                <div class="col-md-3">
                                    <form method="POST" action="" class="d-inline">
                                        <input type="hidden" name="id_produk" value="<?php echo $id_produk; ?>">
                                        <div class="quantity-control">
                                            <button type="button" class="quantity-btn" onclick="updateQty(<?php echo $id_produk; ?>, -1)">-</button>
                                            <input type="number" class="quantity-input" id="qty_<?php echo $id_produk; ?>" 
                                                   name="jumlah" value="<?php echo $item['jumlah']; ?>" min="1" max="99" readonly>
                                            <button type="button" class="quantity-btn" onclick="updateQty(<?php echo $id_produk; ?>, 1)">+</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-md-3 text-end">
                                    <h5 class="text-primary mb-0">
                                        Rp <?php echo number_format($item['harga'] * $item['jumlah'], 0, ',', '.'); ?>
                                    </h5>
                                </div>
                                <div class="col-md-1 text-end">
                                    <a href="?remove=<?php echo $id_produk; ?>" class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Hapus produk ini dari keranjang?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h4 class="mb-4">Ringkasan Pesanan</h4>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span>Subtotal (<?php echo count($_SESSION['cart']); ?> item)</span>
                            <strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span>Biaya Pengiriman</span>
                            <strong>Gratis</strong>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-4">
                            <h5>Total</h5>
                            <h5 class="text-primary">Rp <?php echo number_format($total, 0, ',', '.'); ?></h5>
                        </div>
                        
                        <a href="checkout.php" class="btn btn-checkout">
                            <i class="bi bi-credit-card me-2"></i>Lanjut ke Pembayaran
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <i class="bi bi-cart-x"></i>
                <h3>Keranjang Belanja Kosong</h3>
                <p class="mb-4">Belum ada produk di keranjang Anda</p>
                <a href="dashboard.php" class="btn btn-continue">
                    <i class="bi bi-arrow-left me-2"></i>Mulai Belanja
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function updateQty(id_produk, change) {
            const input = document.getElementById('qty_' + id_produk);
            let currentQty = parseInt(input.value);
            let newQty = currentQty + change;
            
            if (newQty < 1) newQty = 1;
            if (newQty > 99) newQty = 99;
            
            if (newQty !== currentQty) {
                input.value = newQty;
                
                // Submit form untuk update
                const form = input.closest('form');
                const formData = new FormData();
                formData.append('update_cart', '1');
                formData.append('id_produk', id_produk);
                formData.append('jumlah', newQty);
                
                fetch('cart.php', {
                    method: 'POST',
                    body: formData
                }).then(() => {
                    location.reload();
                });
            }
        }
    </script>
</body>
</html>