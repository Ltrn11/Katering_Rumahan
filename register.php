<?php
// Konfigurasi database
$host = 'localhost';
$dbname = 'katering_rumahan';
$username = 'root';
$password = '';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $nama = $_POST['nama_pengguna'];
        $email = $_POST['email'];
        $telepon = $_POST['telepon'];
        $pass = $_POST['password'];
        $confirm_pass = $_POST['confirm_password'];
        $peran = $_POST['peran'];

        // Validasi password
        if ($pass !== $confirm_pass) {
            $message = "Password tidak cocok!";
            $message_type = "danger";
        } else {
            // Cek email di kedua tabel
            $check_pengguna = $conn->prepare("SELECT id_pengguna FROM Pengguna WHERE email = :email");
            $check_pengguna->bindParam(":email", $email);
            $check_pengguna->execute();

            $check_admin = $conn->prepare("SELECT id FROM admin WHERE email = :email");
            $check_admin->bindParam(":email", $email);
            $check_admin->execute();

            if ($check_pengguna->rowCount() > 0 || $check_admin->rowCount() > 0) {
                $message = "Email sudah terdaftar!";
                $message_type = "danger";
            } else {
                $hashed = password_hash($pass, PASSWORD_DEFAULT);

                // Insert berdasarkan peran yang dipilih
                if ($peran === 'admin') {
                    // Insert ke tabel admin
                    $insert = $conn->prepare("INSERT INTO admin
                        (nama_admin, email, telepon, password)
                        VALUES (:nama, :email, :telepon, :pass)");
                } else {
                    // Insert ke tabel Pengguna
                    $insert = $conn->prepare("INSERT INTO Pengguna
                        (nama_pengguna, email, telepon, kata_sandi, peran)
                        VALUES (:nama, :email, :telepon, :pass, :peran)");
                    $insert->bindParam(":peran", $peran);
                }

                $insert->bindParam(":nama", $nama);
                $insert->bindParam(":email", $email);
                $insert->bindParam(":telepon", $telepon);
                $insert->bindParam(":pass", $hashed);

                if ($insert->execute()) {
                    $message = "Pendaftaran berhasil! Silakan login.";
                    $message_type = "success";
                } else {
                    $message = "Pendaftaran gagal!";
                    $message_type = "danger";
                }
            }
        }
    } catch (PDOException $e) {
        $message = "Database error: " . $e->getMessage();
        $message_type = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register - Katering Rumahan</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>
        body {
            background: url('https://images.unsplash.com/photo-1504674900247-0877df9cc836') no-repeat center center/cover;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .register-box {
            background-color: rgba(0,0,0,0.75);
            padding: 30px;
            border-radius: 15px;
            width: 420px;
            color: white;
        }
        .form-label {
            color: #ddd;
        }
        .btn-warning {
            font-weight: 600;
        }
        .back-login {
            color: #aaa;
        }
        .back-login a {
            color: #f6c144;
            font-weight: bold;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="register-box">
    <h2 class="text-center mb-3">Daftar Akun</h2>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <label class="form-label">Nama Lengkap</label>
        <div class="input-group mb-3">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control" name="nama_pengguna" required>
        </div>

        <label class="form-label">Email</label>
        <div class="input-group mb-3">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control" name="email" required>
        </div>

        <label class="form-label">Telepon</label>
        <div class="input-group mb-3">
            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
            <input type="text" class="form-control" name="telepon" required>
        </div>

        <label class="form-label">Daftar Sebagai</label>
        <div class="input-group mb-3">
            <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
            <select class="form-control" name="peran" required>
                <option value="pelanggan">Pelanggan</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <label class="form-label">Password</label>
        <div class="input-group mb-3">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" name="password" required>
        </div>

        <label class="form-label">Konfirmasi Password</label>
        <div class="input-group mb-4">
            <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
            <input type="password" class="form-control" name="confirm_password" required>
        </div>

        <button class="btn btn-warning w-100">Daftar</button>
    </form>

    <p class="text-center mt-3 back-login">
        Sudah punya akun? <a href="index.php">Login</a>
    </p>

</div>

</body>
</html>