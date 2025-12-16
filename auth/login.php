<?php
include '../config.php'; 

// Cek jika sudah login sebagai admin
if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin') {
    header("location:" . BASE_URL . "/admin/index.php");
    exit();
}

// Proses login
$pesan = '';
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password_input = $_POST['password']; 

    $query = "SELECT id, username, password, nama_lengkap, role FROM users WHERE username='$username' AND role='admin'";
    $result = mysqli_query($koneksi, $query);
    $data_user = mysqli_fetch_assoc($result);

    if ($data_user && password_verify($password_input, $data_user['password'])) {
        $_SESSION['user_id'] = $data_user['id'];
        $_SESSION['username'] = $data_user['username'];
        $_SESSION['nama'] = $data_user['nama_lengkap'];
        $_SESSION['role'] = $data_user['role']; 
        
        unset($_SESSION['status']);
        header("location:" . BASE_URL . "/admin/index.php");
        exit();
    } else {
        $pesan = 'gagal';
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
    <title>Login Admin - Game Hub</title>
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
        
        .login-container {
            display: flex;
            width: 1000px;
            max-width: 100%;
            min-height: 550px;
            background: var(--bg-card);
            border: 1px solid var(--border-dark);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }
        
        .login-visual {
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
            font-size: 2.5em;
            color: var(--accent);
            text-shadow: 0 0 30px var(--accent-glow);
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
        
        .login-form-area {
            flex: 1;
            padding: var(--space-2xl);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-form-area h3 {
            font-size: 1.8em;
            color: var(--primary);
            text-align: center;
            margin-bottom: var(--space-xl);
        }
        
        .login-form-area .input-group {
            margin-bottom: var(--space-lg);
        }
        
        .login-form-area .input-group input {
            padding: 16px var(--space-md);
        }
        
        .form-footer {
            display: flex;
            justify-content: space-between;
            margin-top: var(--space-xl);
            font-size: 0.9em;
        }
        
        .form-footer a {
            color: var(--text-muted);
            transition: color var(--transition-fast);
        }
        
        .form-footer a:hover {
            color: var(--accent);
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }
            
            .login-visual {
                min-height: 200px;
                order: 2;
            }
            
            .login-form-area {
                order: 1;
            }
            
            .visual-overlay h2 {
                font-size: 1.8em;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-visual">
            <div class="bg-carousel" id="bgCarousel">
                <?php foreach ($cover_images as $index => $img): ?>
                    <div class="bg-slide <?php echo $index === 0 ? 'active' : ''; ?>" style="background-image: url('<?php echo htmlspecialchars($img); ?>')"></div>
                <?php endforeach; ?>
                <?php if (empty($cover_images)): ?>
                    <div class="bg-slide active" style="background: linear-gradient(135deg, var(--bg-dark), var(--bg-elevated));"></div>
                <?php endif; ?>
            </div>
            <div class="visual-overlay">
                <h2>🎮 Game Hub Console</h2>
                <p>Portal administrasi untuk mengelola koleksi game offline terlengkap.</p>
                <span class="visual-badge">🔐 Area Khusus Administrator</span>
            </div>
        </div>
        
        <div class="login-form-area">
            <h3>LOGIN ADMIN</h3>
            
            <?php 
            $pesan_param = isset($_GET['pesan']) ? $_GET['pesan'] : $pesan;
            if ($pesan_param == 'gagal'): ?>
                <div class="alert alert-danger">❌ Login Gagal! Username atau Password salah.</div>
            <?php elseif ($pesan_param == 'logout'): ?>
                <div class="alert alert-success">✓ Anda berhasil logout.</div>
            <?php elseif ($pesan_param == 'belum_login'): ?>
                <div class="alert alert-danger">⚠️ Anda harus login sebagai Admin.</div>
            <?php elseif ($pesan_param == 'sukses_register_admin'): ?>
                <div class="alert alert-success">✓ Pendaftaran Admin berhasil! Silakan login.</div>
            <?php endif; ?>
            
            <form action="" method="POST">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required autofocus>
                </div>
                
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                </div>
                
                <button type="submit" name="login" class="btn btn-primary btn-full btn-lg">🚀 LOGIN</button>
            </form>
            
            <div class="form-footer">
                <a href="<?php echo BASE_URL; ?>">← Kembali ke Beranda</a>
                <a href="<?php echo BASE_URL; ?>/register.php">Daftar Admin Baru →</a>
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