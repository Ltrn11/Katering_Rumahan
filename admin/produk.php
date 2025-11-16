<?php
session_start();
require '../koneksi.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil semua produk
$query = "SELECT p.*, k.nama_kategori 
          FROM Produk p 
          JOIN Kategori k ON p.id_kategori = k.id_kategori 
          ORDER BY p.id_produk DESC";
$result_produk = $koneksi->query($query);

// Ambil kategori untuk dropdown
$result_kategori = $koneksi->query("SELECT * FROM Kategori ORDER BY nama_kategori");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Produk - Admin</title>
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
        
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
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
                <a class="nav-link active" href="produk.php">
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
                <h2><i class="bi bi-box-seam me-2"></i>Manajemen Produk</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Produk
                </button>
            </div>

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

            <!-- Tabel Produk -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Gambar</th>
                                    <th>Nama Produk</th>
                                    <th>Kategori</th>
                                    <th>Harga</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($produk = $result_produk->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $produk['id_produk']; ?></td>
                                    <td>
                                        <?php
                                        $foto_id = 'photo-1546069901-ba9599a7e63c';
                                        $nama_lower = strtolower($produk['nama_produk']);
                                        
                                        if (strpos($nama_lower, 'nasi') !== false) {
                                            $foto_id = 'photo-1603133872878-684f208fb84b';
                                        } elseif (strpos($nama_lower, 'ayam') !== false) {
                                            $foto_id = 'photo-1598103442097-8b74394b95c6';
                                        } elseif (strpos($nama_lower, 'sate') !== false) {
                                            $foto_id = 'photo-1529006557810-274b9b2fc783';
                                        }
                                        ?>
                                        <img src="https://images.unsplash.com/<?php echo $foto_id; ?>?w=60&h=60&fit=crop" class="product-img" alt="">
                                    </td>
                                    <td>
                                        <strong><?php echo $produk['nama_produk']; ?></strong><br>
                                        <small class="text-muted"><?php echo substr($produk['deskripsi'], 0, 50); ?>...</small>
                                    </td>
                                    <td><?php echo $produk['nama_kategori']; ?></td>
                                    <td><strong>Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></strong></td>
                                    <td>
                                        <?php if ($produk['tersedia']): ?>
                                            <span class="badge bg-success">Tersedia</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Tidak Tersedia</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editProduk(<?php echo htmlspecialchars(json_encode($produk)); ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="hapus_produk.php?id=<?php echo $produk['id_produk']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Yakin hapus produk ini?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
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

<!-- Modal Tambah Produk -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Produk Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="tambah_produk.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Produk</label>
                        <input type="text" name="nama_produk" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="id_kategori" class="form-select" required>
                            <option value="">Pilih Kategori</option>
                            <?php
                            $result_kategori->data_seek(0);
                            while($kat = $result_kategori->fetch_assoc()):
                            ?>
                                <option value="<?php echo $kat['id_kategori']; ?>">
                                    <?php echo $kat['nama_kategori']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga</label>
                        <input type="number" name="harga" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="tersedia" class="form-select" required>
                            <option value="1">Tersedia</option>
                            <option value="0">Tidak Tersedia</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Produk -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="edit_produk.php" method="POST">
                <input type="hidden" name="id_produk" id="edit_id_produk">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Produk</label>
                        <input type="text" name="nama_produk" id="edit_nama_produk" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="id_kategori" id="edit_id_kategori" class="form-select" required>
                            <?php
                            $result_kategori->data_seek(0);
                            while($kat = $result_kategori->fetch_assoc()):
                            ?>
                                <option value="<?php echo $kat['id_kategori']; ?>">
                                    <?php echo $kat['nama_kategori']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga</label>
                        <input type="number" name="harga" id="edit_harga" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="tersedia" id="edit_tersedia" class="form-select" required>
                            <option value="1">Tersedia</option>
                            <option value="0">Tidak Tersedia</option>
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
function editProduk(data) {
    document.getElementById('edit_id_produk').value = data.id_produk;
    document.getElementById('edit_nama_produk').value = data.nama_produk;
    document.getElementById('edit_id_kategori').value = data.id_kategori;
    document.getElementById('edit_deskripsi').value = data.deskripsi;
    document.getElementById('edit_harga').value = data.harga;
    document.getElementById('edit_tersedia').value = data.tersedia;
    
    new bootstrap.Modal(document.getElementById('modalEdit')).show();
}
</script>
</body>
</html>
<?php $koneksi->close(); ?>