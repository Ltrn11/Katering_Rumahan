<?php
session_start();
require_once '../koneksi.php';

// Cek login dan role superadmin
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] != 'superadmin') {
    header('Location: index.php');
    exit;
}

$message = '';
$error = '';

// Cek total admin
$checkTotal = $conn->prepare("SELECT COUNT(*) as total FROM admin");
$checkTotal->execute();
$totalAdmin = $checkTotal->fetch(PDO::FETCH_ASSOC)['total'];
$sisaSlot = 3 - $totalAdmin;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // CEK ULANG total admin SAAT menekan submit
    $checkTotal = $conn->prepare("SELECT COUNT(*) as total FROM admin");
    $checkTotal->execute();
    $totalAdmin = $checkTotal->fetch(PDO::FETCH_ASSOC)['total'];

    if ($totalAdmin >= 3) {
        $error = "Tidak bisa menambah admin! Jumlah maksimal (3 orang) sudah tercapai.";
    } else {
        $nama = trim($_POST['nama_admin']);
        $email = trim($_POST['email']);
        $telepon = trim($_POST['telepon']);
        $password = $_POST['password'];
        $role = $_POST['role']; 
        
        // Validasi email duplikat
        $checkEmail = $conn->prepare("SELECT id FROM admin WHERE email = :email");
        $checkEmail->bindParam(':email', $email);
        $checkEmail->execute();
        
        if ($checkEmail->rowCount() > 0) {
            $error = "Email sudah terdaftar!";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $insert = $conn->prepare("INSERT INTO admin 
                (nama_admin, email, telepon, password, role) 
                VALUES (:nama, :email, :telepon, :pass, :role)");
            
            $insert->bindParam(':nama', $nama);
            $insert->bindParam(':email', $email);
            $insert->bindParam(':telepon', $telepon);
            $insert->bindParam(':pass', $hashedPassword);
            $insert->bindParam(':role', $role);
            
            if ($insert->execute()) {
                $message = "Admin berhasil ditambahkan!";
                // Refresh tampilan jumlah admin
                $checkTotal->execute();
                $totalAdmin = $checkTotal->fetch(PDO::FETCH_ASSOC)['total'];
                $sisaSlot = 3 - $totalAdmin;
            } else {
                $error = "Gagal menambahkan admin!";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Tambah Admin Baru</h4>
                    <p class="mb-0">
                        Jumlah Admin: <strong><?php echo $totalAdmin; ?>/3</strong> | 
                        Sisa Slot: <strong class="<?php echo $sisaSlot > 0 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo $sisaSlot; ?>
                        </strong>
                    </p>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-success"><?php echo $message; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($sisaSlot > 0): ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nama Admin</label>
                            <input type="text" name="nama_admin" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Telepon</label>
                            <input type="text" name="telepon" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-control" required>
                                <option value="admin">Admin</option>
                                <option value="superadmin">Super Admin</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Tambah Admin</button>
                    </form>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <strong>Jumlah admin sudah maksimal!</strong><br>
                            Hapus admin yang ada terlebih dahulu sebelum menambah yang baru.
                        </div>
                    <?php endif; ?>
                    
                    <a href="dashboard.php" class="btn btn-secondary w-100 mt-3">Kembali ke Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>