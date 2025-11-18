<?php
session_start();
require 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Cek apakah ada ID pesanan
if (!isset($_GET['id'])) {
    header('Location: profil.php');
    exit();
}

$id_pesanan = (int)$_GET['id'];

// Get detail pesanan
$stmt = $koneksi->prepare("SELECT p.*, u.nama_pengguna 
                          FROM Pesanan p
                          JOIN Pengguna u ON p.id_pelanggan = u.id_pengguna
                          WHERE p.id_pesanan = ? AND p.id_pelanggan = ?");
$stmt->bind_param("ii", $id_pesanan, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$pesanan = $result->fetch_assoc();
$stmt->close();

if (!$pesanan) {
    $_SESSION['error'] = 'Pesanan tidak ditemukan!';
    header('Location: profil.php');
    exit();
}

// Cek apakah pesanan sudah selesai
if ($pesanan['status'] != 'selesai') {
    $_SESSION['error'] = 'Umpan balik hanya bisa diberikan untuk pesanan yang sudah selesai!';
    header('Location: profil.php');
    exit();
}

// Cek apakah sudah pernah kasih umpan balik
$stmt = $koneksi->prepare("SELECT * FROM Umpan_Balik_Pelanggan WHERE id_pesanan = ?");
$stmt->bind_param("i", $id_pesanan);
$stmt->execute();
$result = $stmt->get_result();
$existing_feedback = $result->fetch_assoc();
$stmt->close();

// Handle submit umpan balik
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pemberian = (int)$_POST['pemberian'];
    $komentar = trim($_POST['komentar']);
    
    // Validasi
    if ($pemberian < 1 || $pemberian > 5) {
        $_SESSION['error'] = 'Rating harus antara 1-5 bintang!';
    } else {
        if ($existing_feedback) {
            // Update umpan balik yang sudah ada
            $stmt = $koneksi->prepare("UPDATE Umpan_Balik_Pelanggan 
                                      SET pemberian = ?, komentar = ?, tanggal_umpan_balik = NOW() 
                                      WHERE id_pesanan = ?");
            $stmt->bind_param("isi", $pemberian, $komentar, $id_pesanan);
            $pesan = 'Umpan balik berhasil diperbarui!';
        } else {
            // Insert umpan balik baru
            $stmt = $koneksi->prepare("INSERT INTO Umpan_Balik_Pelanggan (id_pesanan, pemberian, komentar) 
                                      VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $id_pesanan, $pemberian, $komentar);
            $pesan = 'Terima kasih atas umpan balik Anda!';
        }
        
        if ($stmt->execute()) {
            $_SESSION['success'] = $pesan;
            header('Location: profil.php');
            exit();
        } else {
            $_SESSION['error'] = 'Gagal menyimpan umpan balik!';
        }
        $stmt->close();
    }
}

// Get item pesanan
$stmt = $koneksi->prepare("SELECT ip.*, p.nama_produk 
                          FROM Item_Pesanan ip
                          JOIN Produk p ON ip.id_produk = p.id_produk
                          WHERE ip.id_pesanan = ?");
$stmt->bind_param("i", $id_pesanan);
$stmt->execute();
$result = $stmt->get_result();
$items = [];
while($row = $result->fetch_assoc()) {
    $items[] = $row;
}
$stmt->close();

$koneksi->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berikan Umpan Balik - Katering Rumahan</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-blue: #4A90E2;
            --secondary-blue: #5BA3F5;
            --orange: #FF6B35;
            --star-yellow: #FFD700;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--secondary-blue) 0%, var(--primary-blue) 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            color: white !important;
            font-size: 1.3rem;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--secondary-blue) 0%, var(--primary-blue) 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        
        .feedback-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            max-width: 700px;
            margin: 0 auto;
        }
        
        .rating-container {
            text-align: center;
            margin: 30px 0;
        }
        
        .star-rating {
            font-size: 3rem;
            cursor: pointer;
            display: inline-block;
        }
        
        .star {
            color: #ddd;
            transition: all 0.2s;
            display: inline-block;
            margin: 0 5px;
        }
        
        .star:hover,
        .star.active {
            color: var(--star-yellow);
            transform: scale(1.2);
        }
        
        .order-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .btn-submit {
            background: var(--orange);
            border: none;
            color: white;
            padding: 12px 40px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            background: #e55a25;
            transform: translateY(-2px);
        }
        
        .rating-text {
            font-size: 1.2rem;
            color: #666;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-egg-fried me-2"></i>Katering Rumahan
            </a>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 class="mb-0"><i class="bi bi-star me-2"></i>Berikan Umpan Balik</h1>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mb-5">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="feedback-card">
            <!-- Order Info -->
            <div class="order-info">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-1">Pesanan #<?php echo str_pad($pesanan['id_pesanan'], 5, '0', STR_PAD_LEFT); ?></h5>
                        <small class="text-muted"><?php echo date('d M Y, H:i', strtotime($pesanan['tanggal_pesanan'])); ?></small>
                    </div>
                    <div class="text-end">
                        <div class="text-muted small">Total</div>
                        <strong>Rp <?php echo number_format($pesanan['total_jumlah'], 0, ',', '.'); ?></strong>
                    </div>
                </div>
                
                <div class="mt-3">
                    <strong class="d-block mb-2">Item Pesanan:</strong>
                    <?php foreach ($items as $item): ?>
                        <div class="text-muted">
                            • <?php echo htmlspecialchars($item['nama_produk']); ?> 
                            <span class="text-dark">(<?php echo $item['jumlah']; ?>x)</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <h4 class="text-center mb-4">
                <?php echo $existing_feedback ? 'Perbarui' : 'Berikan'; ?> penilaian Anda
            </h4>

            <form method="POST" action="" id="feedbackForm">
                <!-- Rating Stars -->
                <div class="rating-container">
                    <div class="star-rating" id="starRating">
                        <span class="star" data-rating="1"><i class="bi bi-star-fill"></i></span>
                        <span class="star" data-rating="2"><i class="bi bi-star-fill"></i></span>
                        <span class="star" data-rating="3"><i class="bi bi-star-fill"></i></span>
                        <span class="star" data-rating="4"><i class="bi bi-star-fill"></i></span>
                        <span class="star" data-rating="5"><i class="bi bi-star-fill"></i></span>
                    </div>
                    <input type="hidden" name="pemberian" id="ratingValue" value="<?php echo $existing_feedback ? $existing_feedback['pemberian'] : '0'; ?>">
                    <div class="rating-text" id="ratingText">
                        <?php 
                        if ($existing_feedback) {
                            $rating_labels = ['', 'Sangat Buruk', 'Buruk', 'Cukup', 'Baik', 'Sangat Baik'];
                            echo $rating_labels[$existing_feedback['pemberian']];
                        } else {
                            echo 'Pilih rating Anda';
                        }
                        ?>
                    </div>
                </div>

                <!-- Komentar -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Komentar (Opsional)</label>
                    <textarea class="form-control" name="komentar" rows="4" 
                              placeholder="Bagikan pengalaman Anda tentang pesanan ini..."><?php echo $existing_feedback ? htmlspecialchars($existing_feedback['komentar']) : ''; ?></textarea>
                </div>

                <!-- Submit Button -->
                <div class="text-center">
                    <button type="submit" class="btn btn-submit" id="submitBtn" disabled>
                        <i class="bi bi-send me-2"></i><?php echo $existing_feedback ? 'Perbarui' : 'Kirim'; ?> Umpan Balik
                    </button>
                    <a href="profil.php" class="btn btn-outline-secondary ms-2">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const stars = document.querySelectorAll('.star');
        const ratingValue = document.getElementById('ratingValue');
        const ratingText = document.getElementById('ratingText');
        const submitBtn = document.getElementById('submitBtn');
        
        const ratingLabels = {
            0: 'Pilih rating Anda',
            1: 'Sangat Buruk 😞',
            2: 'Buruk 😕',
            3: 'Cukup 😐',
            4: 'Baik 😊',
            5: 'Sangat Baik 😍'
        };
        
        // Set initial rating jika sudah ada
        const initialRating = parseInt(ratingValue.value);
        if (initialRating > 0) {
            updateStars(initialRating);
            submitBtn.disabled = false;
        }
        
        // Star click handler
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = parseInt(this.dataset.rating);
                ratingValue.value = rating;
                updateStars(rating);
                ratingText.textContent = ratingLabels[rating];
                submitBtn.disabled = false;
            });
            
            // Hover effect
            star.addEventListener('mouseenter', function() {
                const rating = parseInt(this.dataset.rating);
                highlightStars(rating);
            });
        });
        
        // Reset on mouse leave
        document.getElementById('starRating').addEventListener('mouseleave', function() {
            const currentRating = parseInt(ratingValue.value);
            if (currentRating > 0) {
                updateStars(currentRating);
            } else {
                stars.forEach(star => star.classList.remove('active'));
            }
        });
        
        function updateStars(rating) {
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        }
        
        function highlightStars(rating) {
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        }
        
        // Form validation
        document.getElementById('feedbackForm').addEventListener('submit', function(e) {
            const rating = parseInt(ratingValue.value);
            if (rating < 1 || rating > 5) {
                e.preventDefault();
                alert('Silakan pilih rating terlebih dahulu!');
            }
        });
    </script>
</body>
</html>