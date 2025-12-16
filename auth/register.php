<?php
session_start();
include '../config.php'; 

$pesan = '';

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password_input = $_POST['password']; 
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    
    $password_hash = password_hash($password_input, PASSWORD_BCRYPT);
    
    $check_query = "SELECT username FROM users WHERE username='$username'";
    $check_result = mysqli_query($koneksi, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $pesan = 'exists';
    } else {
        $insert_query = "INSERT INTO users (username, password, nama_lengkap, role) 
                         VALUES ('$username', '$password_hash', '$nama_lengkap', 'admin')";
                         
        if (mysqli_query($koneksi, $insert_query)) {
            header("Location: login.php?pesan=sukses_register_admin");
            exit();
        } else {
            $pesan = 'error';
        }
    }
}

// Ambil gambar untuk background carousel
$cover_query = mysqli_query($koneksi, "SELECT hero_image_path FROM games WHERE hero_image_path IS NOT NULL AND hero_image_path != '' ORDER BY view_count DESC LIMIT 4");
$cover_images = [];
while ($row = mysqli_fetch_assoc($cover_query)) {
    $cover_images[] = BASE_URL . '/' . $row['hero_image_path'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Admin - Game Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-xl);
        }
        
        .register-container {
            display: flex;
            width: 1000px;
            max-width: 100%;
            min-height: 580px;
            background: var(--bg-card);
            border: 1px solid var(--border-dark);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }
        
        .register-visual {
            flex: 1.2;
            position: relative;
            background: var(--bg-dark);
            overflow: hidden;
        }
        
        .bg-carousel {
            position: absolute;
            inset: 0;
        }
        
        .bg-slide {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 1.5s ease-in-out;
        }
        
        .bg-slide.active {
            opacity: 1;
        }
        
        .visual-overlay {
            position: absolute;
            inset: 0;
            background: rgba(5, 5, 8, 0.85);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: var(--space-2xl);
        }
        
        .visual-overlay h2 {
            font-family: var(--font-display);
            font-size: 2.2em;
            color: var(--primary);
            text-shadow: 0 0 30px var(--primary-glow);
            margin-bottom: var(--space-md);
        }
        
        .visual-overlay p {
            color: var(--text-secondary);
            font-size: 1em;
            max-width: 80%;
        }
        
        .visual-badge {
            margin-top: var(--space-xl);
            padding: 8px 20px;
            background: var(--bg-elevated);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-full);
            color: var(--text-muted);
            font-size: 0.85em;
        }
        
        .register-form-area {
            flex: 1;
            padding: var(--space-2xl);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .register-form-area h3 {
            font-size: 1.8em;
            color: var(--accent);
            text-align: center;
            margin-bottom: var(--space-xl);
        }
        
        .register-form-area .input-group {
            margin-bottom: var(--space-md);
        }
        
        .register-form-area .input-group input {
            padding: 14px var(--space-md);
        }
        
        .form-footer {
            display: flex;
            justify-content: center;
            margin-top: var(--space-lg);
            font-size: 0.9em;
        }
        
        .form-footer a {
            color: var(--text-muted);
            transition: color var(--transition-fast);
        }
        
        .form-footer a:hover {
            color: var(--primary);
        }
        
        .password-hint {
            font-size: 0.8em;
            color: var(--text-muted);
            margin-top: -10px;
            margin-bottom: var(--space-md);
        }
        
        @media (max-width: 768px) {
            .register-container {
                flex-direction: column;
            }
            
            .register-visual {
                min-height: 180px;
                order: 2;
            }
            
            .register-form-area {
                order: 1;
            }
            
            .visual-overlay h2 {
                font-size: 1.6em;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-visual">
            <div class="bg-carousel" id="bgCarousel">
                <?php foreach ($cover_images as $index => $img): ?>
                    <div class="bg-slide <?php echo $index === 0 ? 'active' : ''; ?>" style="background-image: url('<?php echo htmlspecialchars($img); ?>')"></div>
                <?php endforeach; ?>
                <?php if (empty($cover_images)): ?>
                    <div class="bg-slide active" style="background: linear-gradient(135deg, var(--bg-dark), var(--bg-elevated));"></div>
                <?php endif; ?>
            </div>
            <div class="visual-overlay">
                <h2>✨ Bergabung Sekarang</h2>
                <p>Daftarkan akun administrator untuk mengelola Game Hub Console.</p>
                <span class="visual-badge">🛡️ Akses Administrator Penuh</span>
            </div>
        </div>
        
        <div class="register-form-area">
            <h3>DAFTAR ADMIN BARU</h3>
            
            <?php if ($pesan == 'exists'): ?>
                <div class="alert alert-danger">⚠️ Username sudah digunakan. Pilih username lain.</div>
            <?php elseif ($pesan == 'error'): ?>
                <div class="alert alert-danger">❌ Pendaftaran gagal. Silakan coba lagi.</div>
            <?php endif; ?>
            
            <form action="" method="POST">
                <div class="input-group">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan nama lengkap" required>
                </div>
                
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Buat username unik" required>
                </div>
                
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Buat password yang kuat" required minlength="6">
                </div>
                <p class="password-hint">Minimal 6 karakter</p>
                
                <button type="submit" name="register" class="btn btn-accent btn-full btn-lg">🚀 DAFTAR SEKARANG</button>
            </form>
            
            <div class="form-footer">
                <a href="<?php echo BASE_URL; ?>/auth/login.php">← Sudah punya akun? Login di sini</a>
            </div>
        </div>
    </div>
    
    <script>
        // Background Carousel
        const slides = document.querySelectorAll('.bg-slide');
        let currentSlide = 0;
        
        if (slides.length > 1) {
            setInterval(() => {
                slides[currentSlide].classList.remove('active');
                currentSlide = (currentSlide + 1) % slides.length;
                slides[currentSlide].classList.add('active');
            }, 5000);
        }
    </script>
</body>
</html>