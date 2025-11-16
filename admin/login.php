<<?php
session_start();
require '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM Pengguna WHERE email = ? AND peran = 'admin'");
    $stmt->execute([$email]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        if (password_verify($password, $data['kata_sandi'])) {

            $_SESSION['admin_id'] = $data['id_pengguna'];
            $_SESSION['admin_nama'] = $data['nama_pengguna'];

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak terdaftar sebagai admin!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center" style="height:100vh;">
    <div class="card p-4 shadow" style="width:350px;">
        <h3 class="text-center mb-3">Login Admin</h3>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required placeholder="Email admin">
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required placeholder="Password admin">
            </div>

            <button type="submit" name="login" class="btn btn-primary w-100">Masuk</button>
        </form>

    </div>
</div>

</body>
</html>
