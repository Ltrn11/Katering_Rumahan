<?php
session_start();
require 'koneksi.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Cek apakah ada ID pesanan
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID pesanan tidak valid!";
    header("Location: profil.php");
    exit;
}

$id_pesanan = (int)$_GET['id'];
$id_pelanggan = $_SESSION['user_id'];

// Cek apakah pesanan milik user ini dan statusnya selesai
$query = "SELECT * FROM Pesanan WHERE id_pesanan = ? AND id_pelanggan = ? AND status = 'selesai'";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("ii", $id_pesanan, $id_pelanggan);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = "Pesanan tidak ditemukan atau belum selesai!";
    header("Location: profil.php");
    exit;
}

$pesanan = $result->fetch_assoc();

// Cek apakah sudah pernah kasih feedback
$check = $koneksi->query("SELECT * FROM Umpan_Balik_Pelanggan WHERE id_pesanan = $id_pesanan");
if ($check->num_rows > 0) {
    $_SESSION['error'] = "Anda sudah memberikan umpan balik untuk pesanan ini!";
    header("Location: profil.php");
    exit;
}

// Proses submit feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)$_POST['rating'];
    $komentar = trim($_POST['komentar']);
    
    if ($rating < 1 || $rating > 5) {
        $_SESSION['error'] = "Rating harus antara 1-5!";
    } else {
        $stmt = $koneksi->prepare("INSERT INTO Umpan_Balik_Pelanggan (id_pesanan, pemberian, komentar) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id_pesanan, $rating, $komentar);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Terima kasih atas umpan balik Anda!";
            header("Location: profil.php");
            exit;
        } else {
            $_SESSION['error'] = "Gagal menyimpan umpan balik!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beri Umpan Balik - Katering Rumahan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-blue: #4A90E2;
            --secondary-blue: #5BA3F5;
            --orange: #FF6B35;
        }
        
        body {
            background-color: #f8f9fa;
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
        
        .nav-link {
            color: white !important;
            font-weight: 500;
            transition: all 0.3s;
            padding: 8px 15px !important;
            border-radius: 5px;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .feedback-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        
        .star-rating {
            font-size: 3rem;
            cursor: pointer;
        }
        
        .star-rating i {
            color: #ddd;
            transition: all 0.2s;
        }
        
        .star-rating i.active,
        .star-rating i:hover {
            color: #ffc107;
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
            transform: scale(1.02);
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
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="bi bi-house-door me-1"></i>Menu
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        <i class="bi bi-cart me-1"></i>Pesanan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="profil.php">
                        <i class="bi bi-person me-1"></i>Profil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="bi bi-box-arrow-right me-1"></i>Keluar
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card feedback-card p-4">
                <div class="text-center mb-4">
                    <i class="bi bi-star-fill text-warning" style="font-size: 3rem;"></i>
                    <h2 class="mt-3">Beri Umpan Balik</h2>
                    <p class="text-muted">Bagaimana pengalaman Anda dengan pesanan #<?php echo $id_pesanan; ?>?</p>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="feedbackForm">
                    <!-- Rating Bintang -->
                    <div class="text-center mb-4">
                        <label class="form-label d-block mb-3">
                            <strong>Berikan Rating:</strong>
                        </label>
                        <div class="star-rating" id="starRating">
                            <i class="bi bi-star-fill" data-rating="1"></i>
                            <i class="bi bi-star-fill" data-rating="2"></i>
                            <i class="bi bi-star-fill" data-rating="3"></i>
                            <i class="bi bi-star-fill" data-rating="4"></i>
                            <i class="bi bi-star-fill" data-rating="5"></i>
                        </div>
                        <input type="hidden" name="rating" id="ratingInput" required>
                        <div id="ratingText" class="mt-2 text-muted"></div>
                    </div>

                    <!-- Komentar -->
                    <div class="mb-4">
                        <label class="form-label"><strong>Komentar (Opsional):</strong></label>
                        <textarea name="komentar" class="form-control" rows="5" 
                                  placeholder="Ceritakan pengalaman Anda..."></textarea>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-submit flex-grow-1">
                            <i class="bi bi-send me-2"></i>Kirim Umpan Balik
                        </button>
                        <a href="profil.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-2"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Rating Star System
const stars = document.querySelectorAll('#starRating i');
const ratingInput = document.getElementById('ratingInput');
const ratingText = document.getElementById('ratingText');

const ratingTexts = {
    1: 'Sangat Buruk',
    2: 'Buruk',
    3: 'Cukup',
    4: 'Baik',
    5: 'Sangat Baik'
};

stars.forEach(star => {
    star.addEventListener('click', function() {
        const rating = parseInt(this.getAttribute('data-rating'));
        ratingInput.value = rating;
        
        // Update star visual
        stars.forEach(s => {
            const sRating = parseInt(s.getAttribute('data-rating'));
            if (sRating <= rating) {
                s.classList.add('active');
            } else {
                s.classList.remove('active');
            }
        });
        
        // Update text
        ratingText.textContent = ratingTexts[rating];
        ratingText.style.color = '#ffc107';
        ratingText.style.fontWeight = 'bold';
    });
    
    // Hover effect
    star.addEventListener('mouseenter', function() {
        const rating = parseInt(this.getAttribute('data-rating'));
        stars.forEach(s => {
            const sRating = parseInt(s.getAttribute('data-rating'));
            if (sRating <= rating) {
                s.style.color = '#ffc107';
            } else {
                s.style.color = '#ddd';
            }
        });
    });
});

document.getElementById('starRating').addEventListener('mouseleave', function() {
    const currentRating = parseInt(ratingInput.value) || 0;
    stars.forEach(s => {
        const sRating = parseInt(s.getAttribute('data-rating'));
        if (sRating <= currentRating) {
            s.style.color = '#ffc107';
        } else {
            s.style.color = '#ddd';
        }
    });
});

// Form validation
document.getElementById('feedbackForm').addEventListener('submit', function(e) {
    if (!ratingInput.value) {
        e.preventDefault();
        alert('Mohon pilih rating terlebih dahulu!');
    }
});
</script>
</body>
</html>
<?php $koneksi->close(); ?>