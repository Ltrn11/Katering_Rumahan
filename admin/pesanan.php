<?php
session_start();
require '../koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Filter pesanan berdasarkan status
$filter = isset($_GET['status']) ? $_GET['status'] : '';
$where = $filter ? "WHERE p.status = '$filter'" : "";

$query = "SELECT p.*, u.nama_pengguna, u.telepon, u.email 
          FROM Pesanan p 
          JOIN Pengguna u ON p.id_pelanggan = u.id_pengguna 
          $where
          ORDER BY p.tanggal_pesanan DESC";
$result_pesanan = $koneksi->query($query);

// Hitung jumlah per status
$status_count = [];
$statuses = ['pending', 'diproses', 'dikirim', 'selesai', 'dibatalkan'];
foreach ($statuses as $status) {
    $result = $koneksi->query("SELECT COUNT(*) as total FROM Pesanan WHERE status = '$status'");
    $status_count[$status] = $result->fetch_assoc()['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pesanan - Admin</title>
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
        
        .filter-btn {
            margin: 5px;
            border-radius: 20px;
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
                <a class="nav-link active" href="pesanan.php">
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
            <h2 class="mb-4"><i class="bi bi-cart-check me-2"></i>Manajemen Pesanan</h2>

            <!-- Alert Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Filter Status -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex flex-wrap">
                        <a href="pesanan.php" class="btn filter-btn <?php echo $filter == '' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            Semua (<?php echo array_sum($status_count); ?>)
                        </a>
                        <a href="?status=pending" class="btn filter-btn <?php echo $filter == 'pending' ? 'btn-warning' : 'btn-outline-warning'; ?>">
                            Pending (<?php echo $status_count['pending']; ?>)
                        </a>
                        <a href="?status=diproses" class="btn filter-btn <?php echo $filter == 'diproses' ? 'btn-info' : 'btn-outline-info'; ?>">
                            Diproses (<?php echo $status_count['diproses']; ?>)
                        </a>
                        <a href="?status=dikirim" class="btn filter-btn <?php echo $filter == 'dikirim' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            Dikirim (<?php echo $status_count['dikirim']; ?>)
                        </a>
                        <a href="?status=selesai" class="btn filter-btn <?php echo $filter == 'selesai' ? 'btn-success' : 'btn-outline-success'; ?>">
                            Selesai (<?php echo $status_count['selesai']; ?>)
                        </a>
                        <a href="?status=dibatalkan" class="btn filter-btn <?php echo $filter == 'dibatalkan' ? 'btn-danger' : 'btn-outline-danger'; ?>">
                            Dibatalkan (<?php echo $status_count['dibatalkan']; ?>)
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tabel Pesanan -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Pelanggan</th>
                                    <th>Total</th>
                                    <th>Alamat</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result_pesanan->num_rows > 0): ?>
                                    <?php while($pesanan = $result_pesanan->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong>#<?php echo $pesanan['id_pesanan']; ?></strong></td>
                                        <td>
                                            <strong><?php echo $pesanan['nama_pengguna']; ?></strong><br>
                                            <small class="text-muted"><?php echo $pesanan['telepon']; ?></small>
                                        </td>
                                        <td><strong>Rp <?php echo number_format($pesanan['total_jumlah'], 0, ',', '.'); ?></strong></td>
                                        <td><?php echo substr($pesanan['alamat_pengiriman'], 0, 30); ?>...</td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($pesanan['tanggal_pesanan'])); ?></td>
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
                                            <span class="badge <?php echo $badge_class[$pesanan['status']]; ?>">
                                                <?php echo ucfirst($pesanan['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="lihatDetail(<?php echo $pesanan['id_pesanan']; ?>)">
                                                <i class="bi bi-eye"></i> Detail
                                            </button>
                                            <?php if ($pesanan['status'] != 'selesai' && $pesanan['status'] != 'dibatalkan'): ?>
                                            <button class="btn btn-sm btn-success" onclick="updateStatus(<?php echo $pesanan['id_pesanan']; ?>, '<?php echo $pesanan['status']; ?>')">
                                                <i class="bi bi-arrow-right-circle"></i>
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                            <p class="mt-2">Tidak ada pesanan</p>
                                        </td>
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

<!-- Modal Update Status -->
<div class="modal fade" id="modalStatus" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Status Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="update_status_pesanan.php" method="POST">
                <input type="hidden" name="id_pesanan" id="status_id_pesanan">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Status Baru</label>
                        <select name="status" id="status_baru" class="form-select" required>
                            <option value="pending">Pending</option>
                            <option value="diproses">Diproses</option>
                            <option value="dikirim">Dikirim</option>
                            <option value="selesai">Selesai</option>
                            <option value="dibatalkan">Dibatalkan</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function updateStatus(id, currentStatus) {
    document.getElementById('status_id_pesanan').value = id;
    
    // Set status selanjutnya
    const nextStatus = {
        'pending': 'diproses',
        'diproses': 'dikirim',
        'dikirim': 'selesai'
    };
    
    document.getElementById('status_baru').value = nextStatus[currentStatus] || currentStatus;
    
    new bootstrap.Modal(document.getElementById('modalStatus')).show();
}

function lihatDetail(id) {
    window.location.href = 'detail_pesanan.php?id=' + id;
}
</script>
</body>
</html>
<?php $koneksi->close(); ?>