<?php

$host = 'localhost';
$dbname = 'katering_rumahan';
$username = 'root';
$password = '';

$message = '';
$admins_created = [];

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Data 3 admin (GANTI SESUAI KEBUTUHAN!)
    $admin_data = [
        [
            'nama' => 'Admin Pertama',
            'email' => 'admin1@katering.com',
            'telepon' => '081234567890',
            'password' => 'admin123'
        ],
        [
            'nama' => 'Admin Kedua',
            'email' => 'admin2@katering.com',
            'telepon' => '081234567891',
            'password' => 'admin123'
        ],
        [
            'nama' => 'Admin Ketiga',
            'email' => 'admin3@katering.com',
            'telepon' => '081234567892',
            'password' => 'admin123'
        ]
    ];

    // Cek apakah sudah ada admin
    $check = $conn->query("SELECT COUNT(*) FROM admin");
    $count = $check->fetchColumn();

    if ($count > 0) {
        $message = "⚠️ Admin sudah ada di database. Tidak perlu setup lagi.";
    } else {
        // Insert 3 admin sekaligus
        $stmt = $conn->prepare("INSERT INTO admin (nama_admin, email, telepon, password) 
                                VALUES (:nama, :email, :telepon, :password)");

        foreach ($admin_data as $admin) {
            $hashed = password_hash($admin['password'], PASSWORD_DEFAULT);
            
            $stmt->bindParam(':nama', $admin['nama']);
            $stmt->bindParam(':email', $admin['email']);
            $stmt->bindParam(':telepon', $admin['telepon']);
            $stmt->bindParam(':password', $hashed);
            
            if ($stmt->execute()) {
                $admins_created[] = [
                    'nama' => $admin['nama'],
                    'email' => $admin['email'],
                    'password' => $admin['password']
                ];
            }
        }

        if (count($admins_created) === 3) {
            $message = "✅ Berhasil membuat 3 admin!";
        } else {
            $message = "⚠️ Ada masalah saat membuat admin.";
        }
    }

} catch (PDOException $e) {
    $message = "❌ Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Setup 3 Admin - Katering Rumahan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial;
            padding: 20px;
        }
        .setup-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 700px;
            width: 100%;
        }
        .admin-card {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="setup-box">
    <h2 class="text-center mb-4">🔐 Setup 3 Admin</h2>
    
    <div class="alert alert-info">
        <strong>ℹ️ Informasi:</strong><br>
        File ini akan membuat 3 akun admin secara otomatis.<br>
        Setelah berhasil, <strong>HAPUS file ini dari server!</strong>
    </div>

    <?php if (!empty($message)): ?>
        <?php if (count($admins_created) > 0): ?>
            <div class="alert alert-success">
                <h5><?php echo $message; ?></h5>
            </div>

            <h5 class="mb-3">📋 Kredensial Login (CATAT!):</h5>
            <?php foreach ($admins_created as $index => $admin): ?>
                <div class="admin-card">
                    <strong><i class="bi bi-person-badge me-2"></i><?php echo $admin['nama']; ?></strong>
                    <br>
                    <small class="text-muted">
                        📧 <strong>Email:</strong> <?php echo $admin['email']; ?><br>
                        🔑 <strong>Password:</strong> <?php echo $admin['password']; ?>
                    </small>
                </div>
            <?php endforeach; ?>

            <div class="alert alert-danger mt-4">
                <strong>⚠️ PENTING - LAKUKAN SEKARANG:</strong>
                <ol class="mb-0 mt-2">
                    <li><strong>SCREENSHOT atau CATAT semua kredensial di atas</strong></li>
                    <li><strong>HAPUS file setup_3_admins.php dari server</strong></li>
                    <li>Login dan <strong>GANTI PASSWORD</strong> masing-masing admin</li>
                </ol>
            </div>

            <a href="admin/login.php" class="btn btn-primary w-100 mt-3">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login Sekarang
            </a>

        <?php else: ?>
            <div class="alert alert-warning">
                <h5><?php echo $message; ?></h5>
                <p class="mb-0">Anda sudah bisa login dengan akun yang ada.</p>
            </div>
            <a href="admin/login.php" class="btn btn-primary w-100 mt-3">
                Ke Halaman Login
            </a>
        <?php endif; ?>
    <?php endif; ?>

    <div class="alert alert-secondary mt-3">
        <strong>📝 Catatan:</strong><br>
        - Semua admin punya akses yang sama (setara)<br>
        - Tidak ada tingkatan/role (semua admin biasa)<br>
        - Password default: <code>admin123</code> (segera ganti!)<br>
        - Untuk ubah/tambah admin: edit database manual via phpMyAdmin
    </div>
</div>

</body>
</html>