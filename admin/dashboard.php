<?php
session_start();
require '../koneksi.php';

// Cek login admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Statistik Dashboard
$stats = [];

// Total Produk
$result = $koneksi->query("SELECT COUNT(*) as total FROM Produk");
$stats['total_produk'] = $result->fetch_assoc()['total'];

// Total Pesanan
$result = $koneksi->query("SELECT COUNT(*) as total FROM Pesanan");
$stats['total_pesanan'] = $result->fetch_assoc()['total'];

// Total Pendapatan
$result = $koneksi->query("SELECT SUM(total_jumlah) as total FROM Pesanan WHERE status != 'dibatalkan'");
$stats['total_pendapatan'] = $result->fetch_assoc()['total'] ?? 0;

// Total Pelanggan
$result = $koneksi->query("SELECT COUNT(*) as total FROM Pengguna WHERE peran = 'pelanggan'");
$stats['total_pelanggan'] = $result->fetch_assoc()['total'];

// Pesanan Pending
$result = $koneksi->query("SELECT COUNT(*) as total FROM Pesanan WHERE status = 'pending'");
$stats['pesanan_pending'] = $result->fetch_assoc()['total'];

// Pesanan Terbaru
$query_pesanan = "SELECT p.*, u.nama_pengguna, u.telepon 
                  FROM Pesanan p 
                  JOIN Pengguna u ON p.id_pelanggan = u.id_pengguna 
                  ORDER BY p.tanggal_pesanan DESC 
                  LIMIT 10";
$result_pesanan = $koneksi->query($query_pesanan);

// Produk Stok Rendah
$query_stok = "SELECT pr.nama_produk, i.jumlah_stok, i.stok_minimum 
               FROM Inventori i 
               JOIN Produk pr ON i.id_produk = pr.id_produk 
               WHERE i.jumlah_stok <= i.stok_minimum 
               ORDER BY i.jumlah_stok ASC 
               LIMIT 5";
$result_stok = $koneksi->query($query_stok);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Katering Rumahan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 15px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        
        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .badge-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 p-0 sidebar">
            <div class="text-center py-4 text-white">
                <i class="bi bi-egg-fried" style="font-size: 2.5rem;"></i>
                <h5 class="mt-2">Admin Panel</h5>
                <small><?php echo $_SESSION['admin_nama']; ?></small>
            </div>
            
            <nav class="nav flex-column">
                <a class="nav-link active" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
                <a class="nav-link" href="produk.php">
                    <i class="bi bi-box-seam me-2"></i>Produk
                </a>
                <a class="nav-link" href="pesanan.php">
                    <i class="bi bi-cart-check me-2"></i>Pesanan
                </a>
                <a class="nav-link" href="inventori.php">
                    <i class="bi bi-boxes me-2"></i>Inventori
                </a>
                <a class="nav-link" href="laporan.php">
                    <i class="bi bi-graph-up me-2"></i>Laporan
                </a>
                <hr class="text-white mx-3">
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-left me-2"></i>Keluar
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Dashboard</h2>
                <div class="text-muted">
                    <i class="bi bi-calendar3"></i> <?php echo date('d F Y'); ?>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Total Produk</p>
                                    <h3><?php echo $stats['total_produk']; ?></h3>
                                </div>
                                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Total Pesanan</p>
                                    <h3><?php echo $stats['total_pesanan']; ?></h3>
                                </div>
                                <div class="stat-icon bg-success bg-opacity-10 text-success">
                                    <i class="bi bi-cart-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Pendapatan</p>
                                    <h3>Rp <?php echo number_format($stats['total_pendapatan'], 0, ',', '.'); ?></h3>
                                </div>
                                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="bi bi-cash-stack"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-muted mb-1">Pelanggan</p>
                                    <h3><?php echo $stats['total_pelanggan']; ?></h3>
                                </div>
                                <div class="stat-icon bg-info bg-opacity-10 text-info">
                                    <i class="bi bi-people"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert Pesanan Pending -->
            <?php if ($stats['pesanan_pending'] > 0): ?>
            <div class="alert alert-warning alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Ada <strong><?php echo $stats['pesanan_pending']; ?></strong> pesanan menunggu diproses!
                <a href="pesanan.php" class="alert-link ms-2">Lihat Pesanan</a>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="row">
                <!-- Pesanan Terbaru -->
                <div class="col-md-8">
                    <div class="card stat-card">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Pesanan Terbaru</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Pelanggan</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($pesanan = $result_pesanan->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo $pesanan['id_pesanan']; ?></td>
                                            <td>
                                                <strong><?php echo $pesanan['nama_pengguna']; ?></strong><br>
                                                <small class="text-muted"><?php echo $pesanan['telepon']; ?></small>
                                            </td>
                                            <td>Rp <?php echo number_format($pesanan['total_jumlah'], 0, ',', '.'); ?></td>
                                            <td>
                                                <?php
                                                $badge_class = [
                                                    'pending' => 'bg-warning',
                                                    'diproses' => 'bg-info',
                                                    'dikirim' => 'bg-primary',
                                                    'selesai' => 'bg-success',
                                                    'dibatalkan' => 'bg-danger'
                                                ];
                                                ?>
                                                <span class="badge badge-status <?php echo $badge_class[$pesanan['status']]; ?>">
                                                    <?php echo ucfirst($pesanan['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($pesanan['tanggal_pesanan'])); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stok Rendah -->
                <div class="col-md-4">
                    <div class="card stat-card">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Stok Rendah</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($result_stok->num_rows > 0): ?>
                                <div class="list-group list-group-flush">
                                    <?php while($stok = $result_stok->fetch_assoc()): ?>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex justify-content-between">
                                            <strong><?php echo $stok['nama_produk']; ?></strong>
                                            <span class="badge bg-danger"><?php echo $stok['jumlah_stok']; ?></span>
                                        </div>
                                        <small class="text-muted">Min: <?php echo $stok['stok_minimum']; ?></small>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                                <a href="inventori.php" class="btn btn-sm btn-outline-primary w-100 mt-3">
                                    Kelola Inventori
                                </a>
                            <?php else: ?>
                                <p class="text-muted text-center">Semua stok aman!</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $koneksi->close(); ?>