<?php
session_start();
require '../koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Filter tanggal
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Total Pendapatan
$query_pendapatan = "SELECT SUM(total_jumlah) as total 
                     FROM Pesanan 
                     WHERE status != 'dibatalkan' 
                     AND DATE(tanggal_pesanan) BETWEEN '$start_date' AND '$end_date'";
$result = $koneksi->query($query_pendapatan);
$total_pendapatan = $result->fetch_assoc()['total'] ?? 0;

// Total Pesanan
$query_pesanan = "SELECT COUNT(*) as total 
                  FROM Pesanan 
                  WHERE DATE(tanggal_pesanan) BETWEEN '$start_date' AND '$end_date'";
$result = $koneksi->query($query_pesanan);
$total_pesanan = $result->fetch_assoc()['total'];

// Produk Terlaris
$query_terlaris = "SELECT p.nama_produk, SUM(ip.jumlah) as total_terjual, SUM(ip.jumlah * ip.harga_satuan) as total_pendapatan
                   FROM Item_Pesanan ip
                   JOIN Produk p ON ip.id_produk = p.id_produk
                   JOIN Pesanan ps ON ip.id_pesanan = ps.id_pesanan
                   WHERE DATE(ps.tanggal_pesanan) BETWEEN '$start_date' AND '$end_date'
                   AND ps.status != 'dibatalkan'
                   GROUP BY ip.id_produk
                   ORDER BY total_terjual DESC
                   LIMIT 10";
$result_terlaris = $koneksi->query($query_terlaris);

// Pendapatan per Hari
$query_harian = "SELECT DATE(tanggal_pesanan) as tanggal, SUM(total_jumlah) as total
                 FROM Pesanan
                 WHERE status != 'dibatalkan'
                 AND DATE(tanggal_pesanan) BETWEEN '$start_date' AND '$end_date'
                 GROUP BY DATE(tanggal_pesanan)
                 ORDER BY tanggal DESC
                 LIMIT 7";
$result_harian = $koneksi->query($query_harian);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Admin</title>
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
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stat-card {
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
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
                <a class="nav-link" href="dashboard.php">
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
                <a class="nav-link active" href="laporan.php">
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
            <h2 class="mb-4"><i class="bi bi-graph-up me-2"></i>Laporan Penjualan</h2>

            <!-- Filter Tanggal -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel me-2"></i>Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card stat-card">
                        <div class="card-body">
                            <h6 class="text-muted">Total Pendapatan</h6>
                            <h2 class="text-success mb-0">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></h2>
                            <small class="text-muted">
                                Periode: <?php echo date('d/m/Y', strtotime($start_date)); ?> - <?php echo date('d/m/Y', strtotime($end_date)); ?>
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card stat-card">
                        <div class="card-body">
                            <h6 class="text-muted">Total Pesanan</h6>
                            <h2 class="text-primary mb-0"><?php echo $total_pesanan; ?> Pesanan</h2>
                            <small class="text-muted">
                                Rata-rata: Rp <?php echo $total_pesanan > 0 ? number_format($total_pendapatan / $total_pesanan, 0, ',', '.') : 0; ?> per pesanan
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Produk Terlaris -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-star-fill text-warning me-2"></i>Produk Terlaris</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Produk</th>
                                            <th>Terjual</th>
                                            <th>Pendapatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($result_terlaris->num_rows > 0): ?>
                                            <?php while($item = $result_terlaris->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong><?php echo $item['nama_produk']; ?></strong></td>
                                                <td><?php echo $item['total_terjual']; ?>x</td>
                                                <td>Rp <?php echo number_format($item['total_pendapatan'], 0, ',', '.'); ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">Tidak ada data</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pendapatan Harian -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Pendapatan Harian</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Total Pendapatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($result_harian->num_rows > 0): ?>
                                            <?php while($item = $result_harian->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo date('d F Y', strtotime($item['tanggal'])); ?></td>
                                                <td><strong>Rp <?php echo number_format($item['total'], 0, ',', '.'); ?></strong></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="2" class="text-center text-muted">Tidak ada data</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
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