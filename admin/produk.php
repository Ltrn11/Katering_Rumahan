<?php
session_start();
require "../koneksi.php";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $pass  = $_POST['password'];

    $query = $conn->prepare("SELECT * FROM pengguna WHERE email=? AND peran='admin'");
    $query->execute([$email]);
    $admin = $query->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($pass, $admin['kata_sandi'])) {
        $_SESSION['id'] = $admin['id_pengguna'];
        $_SESSION['nama'] = $admin['nama_pengguna'];
        $_SESSION['role'] = $admin['peran'];

        header("Location: dashboard.php");
        exit();
    }

    $error = "Email atau Password salah!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">

            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    Login Admin
                </div>

                <div class="card-body">
                    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button type="submit" name="login" class="btn btn-primary w-100">
                            Login
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
