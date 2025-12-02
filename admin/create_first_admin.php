<?php
/**
 * FILE INI HANYA DIJALANKAN SEKALI!
 * Untuk membuat Super Admin pertama
 * Setelah berhasil, HAPUS file ini dari server!
 */

// Konfigurasi database
$host = 'localhost';
$dbname = 'katering_rumahan';
$username = 'root';
$password = '';

$message = '';
$created = false;

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // CEK TOTAL ADMIN (SUPERADMIN + ADMIN BIASA)
    $checkTotal = $conn->prepare("SELECT COUNT(*) as total FROM admin");
    $checkTotal->execute();
    $totalAdmin = $checkTotal->fetch(PDO::FETCH_ASSOC)['total'];

    if ($totalAdmin >= 3) {
        $message = "⚠️ Jumlah admin sudah mencapai batas maksimal (3 orang)! Tidak bisa menambah admin lagi.";
    } else {
        // Cek apakah sudah ada super admin
        $check = $conn->prepare("SELECT id FROM admin WHERE role = 'superadmin'");
        $check->execute();

        if ($check->rowCount() > 0) {
            $message = "⚠️ Super Admin sudah ada! Tidak perlu membuat lagi.";
        } else {
            // Data super admin pertama (GANTI SESUAI KEBUTUHAN!)
            $nama = "Super Admin";
            $email = "admin@kateringrumahan.com";
            $telepon = "081234567890";
            $plainPassword = "admin123"; // GANTI PASSWORD INI!
            $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
            $role = "superadmin";

            // Insert super admin
            $insert = $conn->prepare("INSERT INTO admin 
                (nama_admin, email, telepon, password, role) 
                VALUES (:nama, :email, :telepon, :pass, :role)");
            
            $insert->bindParam(":nama", $nama);
            $insert->bindParam(":email", $email);
            $insert->bindParam(":telepon", $telepon);
            $insert->bindParam(":pass", $hashedPassword);
            $insert->bindParam(":role", $role);

            if ($insert->execute()) {
                $created = true;
                $message = "✅ Super Admin berhasil dibuat!";
            } else {
                $message = "❌ Gagal membuat Super Admin!";
            }
        }
    }

} catch (PDOException $e) {
    $message = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Setup Super Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
        }
        .setup-box {
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 600px;
        }
        .success-box {
            background-color: #d4edda;
            border: 2px solid #28a745;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .warning-box {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .danger-box {
            background-color: #f8d7da;
            border: 2px solid #dc3545;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="setup-box">
    <h2 class="text-center mb-4">🔐 Setup Super Admin</h2>
    
    <?php if ($created): ?>
        <div class="success-box">
            <h4>✅ <?php echo $message; ?></h4>
            <hr>
            <p><strong>Detail Login:</strong></p>
            <ul>
                <li><strong>Email:</strong> <?php echo $email; ?></li>
                <li><strong>Password:</strong> <?php echo $plainPassword; ?></li>
            </ul>
            <p class="text-danger"><strong>⚠️ PENTING: Catat kredensial di atas, lalu:</strong></p>
            <ol>
                <li>Login ke admin panel</li>
                <li><strong>HAPUS file ini (create_first_admin.php) dari server!</strong></li>
                <li>Ganti password setelah login pertama</li>
            </ol>
            <a href="index.php" class="btn btn-primary w-100 mt-3">Login Sekarang</a>
        </div>
    <?php elseif (strpos($message, 'sudah ada') !== false || strpos($message, 'maksimal') !== false): ?>
        <div class="warning-box">
            <h4><?php echo $message; ?></h4>
            <p>Anda sudah bisa login dengan akun yang ada.</p>
            <p class="text-danger"><strong>HAPUS file ini sekarang untuk keamanan!</strong></p>
            <a href="index.php" class="btn btn-warning w-100 mt-3">Ke Halaman Login</a>
        </div>
    <?php else: ?>
        <div class="danger-box">
            <h4>❌ Error</h4>
            <p><?php echo $message; ?></p>
        </div>
    <?php endif; ?>

    <div class="alert alert-info mt-4">
        <strong>📝 Catatan:</strong><br>
        - File ini membuat 1 Super Admin pertama<br>
        - Super Admin bisa menambah admin lain lewat dashboard<br>
        - <strong>Maksimal total admin: 3 orang (termasuk superadmin)</strong><br>
        - Setelah selesai, WAJIB hapus file ini!
    </div>
</div>

</body>
</html>