<?php
session_start();
require '../koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil data inventori
$query = "SELECT i.*, p.nama_produk, k.nama_kategori 
          FROM Inventori i 
          JOIN Produk p ON i.id_produk = p.id_produk 
          JOIN Kategori k ON p.id_kategori = k.id_kategori 
          ORDER BY i.jumlah_stok ASC";
$result_inventori = $koneksi->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Inventori - Admin</title>
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
        
        .progress {
            height: 25px;
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
                <a class="nav-link active" href="inventori.php">
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
            <h2 class="mb-4"><i class="bi bi-boxes me-2"></i>Manajemen Inventori</h2>

            <!-- Alert Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Tabel Inventori -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Kategori</th>
                                    <th>Stok Saat Ini</th>
                                    <th>Stok Minimum</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($item = $result_inventori->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo $item['nama_produk']; ?></strong></td>
                                    <td><?php echo $item['nama_kategori']; ?></td>
                                    <td>
                                        <strong><?php echo $item['jumlah_stok']; ?></strong>
                                    </td>
                                    <td><?php echo $item['stok_minimum']; ?></td>
                                    <td style="width: 300px;">
                                        <?php
                                        $persentase = ($item['jumlah_stok'] / max($item['stok_minimum'] * 2, 1)) * 100;
                                        $persentase = min($persentase, 100);
                                        
                                        if ($item['jumlah_stok'] <= $item['stok_minimum']) {
                                            $class = 'bg-danger';
                                            $status = 'Stok Rendah!';
                                        } elseif ($item['jumlah_stok'] <= $item['stok_minimum'] * 1.5) {
                                            $class = 'bg-warning';
                                            $status = 'Stok Terbatas';
                                        } else {
                                            $class = 'bg-success';
                                            $status = 'Stok Aman';
                                        }
                                        ?>
                                        <div class="progress">
                                            <div class="progress-bar <?php echo $class; ?>" style="width: <?php echo $persentase; ?>%">
                                                <?php echo $status; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="updateStok(<?php echo $item['id_inventori']; ?>, '<?php echo $item['nama_produk']; ?>', <?php echo $item['jumlah_stok']; ?>)">
                                            <i class="bi bi-pencil"></i> Update
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Update Stok -->
<div class="modal fade" id="modalStok" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Stok</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="update_stok.php" method="POST">
                <input type="hidden" name="id_inventori" id="stok_id_inventori">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Produk</label>
                        <input type="text" id="stok_nama_produk" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stok Saat Ini</label>
                        <input type="number" id="stok_current" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tambah/Kurangi Stok</label>
                        <input type="number" name="perubahan_stok" class="form-control" placeholder="Contoh: 10 atau -5" required>
                        <small class="text-muted">Gunakan angka negatif untuk mengurangi stok</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update Stok</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function updateStok(id, nama, stok) {
    document.getElementById('stok_id_inventori').value = id;
    document.getElementById('stok_nama_produk').value = nama;
    document.getElementById('stok_current').value = stok;
    
    new bootstrap.Modal(document.getElementById('modalStok')).show();
}
</script>
</body>
</html>
<?php $koneksi->close(); ?>