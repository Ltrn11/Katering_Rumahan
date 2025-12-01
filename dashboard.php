<?php
session_start();
require 'koneksi.php';

// TIDAK ADA pengecekan login - semua orang bisa akses dashboard

// Get kategori untuk filter
$query_kategori = "SELECT * FROM Kategori ORDER BY nama_kategori";
$result_kategori = $koneksi->query($query_kategori);

// Filter produk berdasarkan kategori atau search
$where = "WHERE p.tersedia = 1";
$params = [];

if (isset($_GET['kategori']) && !empty($_GET['kategori'])) {
    $kategori_id = (int)$_GET['kategori'];
    $where .= " AND p.id_kategori = $kategori_id";
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $koneksi->real_escape_string($_GET['search']);
    $where .= " AND p.nama_produk LIKE '%$search%'";
}

// Get produk
$query_produk = "SELECT p.*, k.nama_kategori 
                FROM Produk p 
                JOIN Kategori k ON p.id_kategori = k.id_kategori 
                $where 
                ORDER BY p.id_produk DESC";
$result_produk = $koneksi->query($query_produk);

// Hitung jumlah item di cart (kalau user sudah login)
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    $cart_count = count($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Katering Rumahan</title>
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
        
        .hero-section {
            background: linear-gradient(135deg, var(--secondary-blue) 0%, var(--primary-blue) 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        
        .hero-section h1 {
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .search-bar {
            max-width: 600px;
            margin: 20px auto 0;
        }
        
        .filter-section {
            margin-bottom: 30px;
        }
        
        .filter-btn {
            padding: 8px 20px;
            border: 2px solid var(--primary-blue);
            background: white;
            color: var(--primary-blue);
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s;
            margin: 5px;
            text-decoration: none;
            display: inline-block;
        }
        
        .filter-btn:hover, .filter-btn.active {
            background: var(--primary-blue);
            color: white;
        }
        
        .product-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 25px;
            height: 100%;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .product-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .product-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--orange);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .product-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 8px;
        }
        
        .product-desc {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .product-price {
            color: var(--primary-blue);
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 15px;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .quantity-btn {
            width: 35px;
            height: 35px;
            border: 2px solid #ddd;
            background: white;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .quantity-btn:hover {
            border-color: var(--primary-blue);
            color: var(--primary-blue);
        }
        
        .quantity-input {
            width: 50px;
            text-align: center;
            border: 2px solid #ddd;
            border-radius: 5px;
            margin: 0 10px;
            font-weight: 600;
        }
        
        .btn-add-cart {
            background: var(--orange);
            border: none;
            color: white;
            padding: 10px;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn-add-cart:hover {
            background: #e55a25;
            transform: scale(1.02);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-state i {
            font-size: 4rem;
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
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-house-door me-1"></i>Menu
                        </a>
                    </li>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Menu untuk user yang SUDAH login -->
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php">
                                <i class="bi bi-cart me-1"></i>Pesanan
                                <?php if ($cart_count > 0): ?>
                                    <span class="badge bg-danger"><?php echo $cart_count; ?></span>
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
                    <?php else: ?>
                        <!-- Menu untuk user yang BELUM login -->
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="bi bi-person-plus me-1"></i>Daftar
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <h1 class="text-center">Selamat Datang di Katering Rumahan</h1>
            <p class="text-center mb-0">Makanan rumahan berkualitas, siap diantar ke rumah Anda</p>
            
            <!-- Search Bar -->
            <div class="search-bar">
                <form method="GET" action="">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Cari menu..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button class="btn btn-light" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mb-5">
        <!-- Alert Messages -->
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
        
        <!-- Filter Section -->
        <div class="filter-section text-center">
            <a href="dashboard.php" class="filter-btn <?php echo !isset($_GET['kategori']) ? 'active' : ''; ?>">
                Semua
            </a>
            <?php while($kat = $result_kategori->fetch_assoc()): ?>
                <a href="?kategori=<?php echo $kat['id_kategori']; ?>" 
                   class="filter-btn <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == $kat['id_kategori']) ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($kat['nama_kategori']); ?>
                </a>
            <?php endwhile; ?>
        </div>

        <!-- Products Grid -->
        <?php if ($result_produk->num_rows > 0): ?>
            <div class="row">
                <?php while($produk = $result_produk->fetch_assoc()): ?>
                    <div class="col-md-4 col-lg-4">
                        <div class="card product-card">
                            <div class="position-relative">
                                <?php
                                // Tentukan gambar berdasarkan nama produk
                                $foto_id = 'photo-1546069901-ba9599a7e63c'; // default: salad
                                $nama_lower = strtolower($produk['nama_produk']);
                                
                                if (strpos($nama_lower, 'nasi') !== false) {
                                    $foto_id = 'photo-1603133872878-684f208fb84b'; // nasi
                                } elseif (strpos($nama_lower, 'ayam') !== false || strpos($nama_lower, 'chicken') !== false) {
                                    $foto_id = 'photo-1598103442097-8b74394b95c6'; // ayam
                                } elseif (strpos($nama_lower, 'ikan') !== false || strpos($nama_lower, 'fish') !== false) {
                                    $foto_id = 'photo-1559847844-5315695dadae'; // ikan
                                } elseif (strpos($nama_lower, 'soto') !== false || strpos($nama_lower, 'soup') !== false) {
                                    $foto_id = 'photo-1547592166-23ac45744acd'; // soup
                                } elseif (strpos($nama_lower, 'mie') !== false || strpos($nama_lower, 'noodle') !== false) {
                                    $foto_id = 'photo-1569718212165-3a8278d5f624'; // mie
                                } elseif (strpos($nama_lower, 'sate') !== false || strpos($nama_lower, 'satay') !== false) {
                                    $foto_id = 'photo-1529006557810-274b9b2fc783'; // sate
                                } elseif (strpos($nama_lower, 'bakso') !== false) {
                                    $foto_id = 'photo-1607330289024-1aa69373b863'; // bakso
                                } elseif (strpos($nama_lower, 'gado') !== false || strpos($nama_lower, 'salad') !== false) {
                                    $foto_id = 'photo-1546069901-ba9599a7e63c'; // salad
                                } elseif (strpos($nama_lower, 'rendang') !== false) {
                                    $foto_id = 'photo-1625943553852-781c6dd46faa'; // rendang
                                } elseif (strpos($nama_lower, 'bebek') !== false || strpos($nama_lower, 'duck') !== false) {
                                    $foto_id = 'photo-1626082927389-6cd097cdc6ec'; // bebek
                                } elseif (strpos($nama_lower, 'jus') !== false || strpos($nama_lower, 'juice') !== false) {
                                    $foto_id = 'photo-1600271886742-f049cd451bba'; // jus
                                } elseif (strpos($nama_lower, 'es') !== false || strpos($nama_lower, 'ice') !== false) {
                                    $foto_id = 'photo-1563805042-7684c019e1cb'; // es/minuman
                                } elseif (strpos($nama_lower, 'kue') !== false || strpos($nama_lower, 'cake') !== false) {
                                    $foto_id = 'photo-1578985545062-69928b1d9587'; // kue
                                } elseif (strpos($nama_lower, 'tahu') !== false || strpos($nama_lower, 'tempe') !== false) {
                                    $foto_id = 'photo-1546833999-b9f581a1996d'; // tahu/tempe
                                } elseif (strpos($nama_lower, 'udang') !== false || strpos($nama_lower, 'shrimp') !== false) {
                                    $foto_id = 'photo-1565680018434-b513d5e5fd47'; // udang
                                }
                                ?>
                                <img src="https://images.unsplash.com/<?php echo $foto_id; ?>?w=400&h=300&fit=crop" 
                                     class="product-img" alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>"
                                     onerror="this.src='https://via.placeholder.com/400x300/4A90E2/FFFFFF?text=Makanan'">
                                <span class="product-badge">Tersedia</span>
                            </div>
                            <div class="card-body">
                                <h5 class="product-title"><?php echo htmlspecialchars($produk['nama_produk']); ?></h5>
                                <p class="product-desc"><?php echo htmlspecialchars($produk['deskripsi']); ?></p>
                                <div class="product-price">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></div>
                                
                                <form method="POST" action="add_to_cart.php">
                                    <input type="hidden" name="id_produk" value="<?php echo $produk['id_produk']; ?>">
                                    <div class="quantity-control">
                                        <button type="button" class="quantity-btn" onclick="decreaseQty(this)">-</button>
                                        <input type="number" class="quantity-input" name="jumlah" value="1" min="1" max="99" readonly>
                                        <button type="button" class="quantity-btn" onclick="increaseQty(this)">+</button>
                                    </div>
                                    <button type="submit" class="btn btn-add-cart">
                                        <i class="bi bi-cart-plus me-2"></i>Tambah
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-search"></i>
                <h4>Tidak ada produk ditemukan</h4>
                <p>Coba cari dengan kata kunci lain atau pilih kategori berbeda</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function increaseQty(btn) {
            const input = btn.parentElement.querySelector('.quantity-input');
            let value = parseInt(input.value);
            if (value < 99) {
                input.value = value + 1;
            }
        }
        
        function decreaseQty(btn) {
            const input = btn.parentElement.querySelector('.quantity-input');
            let value = parseInt(input.value);
            if (value > 1) {
                input.value = value - 1;
            }
        }
    </script>
</body>
</html>
<?php
$koneksi->close();
?>