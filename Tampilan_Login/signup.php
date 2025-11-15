<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Katering Rumahan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow p-4" style="width: 25rem;">
        <h3 class="text-center mb-4">Buat Akun Baru</h3>

        <?php
        if (isset($_SESSION['error'])) {
            echo "<div class='alert alert-danger'>".$_SESSION['error']."</div>";
            unset($_SESSION['error']);
        }
        ?>

        <form action="signup_proses.php" method="POST">
            <div class="mb-3">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Sign Up</button>
        </form>

        <div class="text-center mt-3">
            Sudah punya akun? <a href="index.php">Login di sini</a>
        </div>
    </div>
</body>
</html>
