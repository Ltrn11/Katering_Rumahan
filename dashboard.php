<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Konfigurasi database
$host = 'localhost';
$dbname = 'katering_rumahan';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get kategori untuk filter
    $stmt_kategori = $conn->query("SELECT * FROM Kategori ORDER BY nama_kategori");
    $kategoris = $stmt_kategori->fetchAll(PDO::FETCH_ASSOC);
    
    // Filter produk berdasarkan kategori atau search
    $where = "WHERE p.tersedia = 1";
    
    if (isset($_GET['kategori']) && !empty($_GET['kategori'])) {
        $kategori_id = $_GET['kategori'];
        $where .= " AND p.id_kategori = :kategori_id";
    }
    
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = '%' . $_GET['search'] . '%';
        $where .= " AND p.nama_produk LIKE :search";
    }
    
    // Get produk
    $query = "SELECT p.*, k.nama_kategori 
              FROM Produk p 
              JOIN Kategori k ON p.id_kategori = k.id_kategori 
              $where 
              ORDER BY p.id_produk DESC";
    
    $stmt_produk = $conn->prepare($query);
    
    if (isset($kategori_id)) {
        $stmt_produk->bindParam(':kategori_id', $kategori_id);
    }
    if (isset($search)) {
        $stmt_produk->bindParam(':search', $search);
    }
    
    $stmt_produk->execute();
    $produks = $stmt_produk->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
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
            color: white !important;
            font-weight: 500;
            transition: all 0.3s;
            padding: 8px 15px !important;
            border-radius: 5px;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.2);
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
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-house-door me-1"></i>Menu
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pesanan.php">
                            <i class="bi bi-cart me-1"></i>Pesanan
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
            <?php foreach ($kategoris as $kat): ?>
                <a href="?kategori=<?php echo $kat['id_kategori']; ?>" 
                   class="filter-btn <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == $kat['id_kategori']) ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($kat['nama_kategori']); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Products Grid -->
        <?php if (count($produks) > 0): ?>
            <div class="row">
                <?php foreach ($produks as $produk): ?>
                    <div class="col-md-4 col-lg-4">
                        <div class="card product-card">
                            <div class="position-relative">
                                <img src="https://source.unsplash.com/400x300/?food,<?php echo urlencode($produk['nama_produk']); ?>" 
                                     class="product-img" alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>">
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
                <?php endforeach; ?>
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