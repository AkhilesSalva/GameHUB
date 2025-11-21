<?php
session_start();
include 'config.php'; 

$pesan = '';

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password_input = $_POST['password']; 
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    
    $password_hash = password_hash($password_input, PASSWORD_BCRYPT);
    
    $check_query = "SELECT username FROM users WHERE username='$username'";
    $check_result = mysqli_query($koneksi, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $pesan = '<div class="alert alert-danger">Username sudah terdaftar!</div>';
    } else {
        $insert_query = "INSERT INTO users (username, password, nama_lengkap, role) 
                         VALUES ('$username', '$password_hash', '$nama_lengkap', 'admin')";
                         
        if (mysqli_query($koneksi, $insert_query)) {
            header("Location: login.php?pesan=sukses_register_admin");
            exit();
        } else {
            $pesan = '<div class="alert alert-danger">Pendaftaran gagal: ' . mysqli_error($koneksi) . '</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Admin Baru</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
        /* ========================================================================= */
        /* BASE & UTILITIES */
        /* ========================================================================= */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { 
            background: #0f0f1a; color: #e4e6eb; display: flex; justify-content: center; 
            align-items: center; min-height: 100vh; line-height: 1.6; overflow: hidden; 
        }
        a { text-decoration: none; color: #00bcd4; transition: color 0.3s; }
        a:hover { color: #4CAF50; }

        /* ========================================================================= */
        /* ANIMASI BACKGROUND DAN COVER GAME */
        /* ========================================================================= */
        .animated-bg {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, #4CAF50, #00bcd4, #1a1a2e, #0f0f1a);
            background-size: 400% 400%; animation: gradientAnim 15s ease infinite reverse;
            opacity: 0.2; z-index: 1;
        }
        @keyframes gradientAnim {
            0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; }
        }
        .cover-carousel {
            position: relative; width: 100%; height: 100%; overflow: hidden;
            background-color: #11111d;
        }
        .cover-item {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background-size: cover; background-position: center;
            opacity: 0; transition: opacity 1.5s ease-in-out;
            transform: scale(1.1);
        }
        .cover-item.active {
            opacity: 1; transform: scale(1);
        }

        /* ========================================================================= */
        /* LOGIN BOX LAYOUT */
        /* ========================================================================= */
        .login-box {
            position: relative;
            display: flex;
            width: 1000px; 
            max-width: 95%;
            min-height: 580px; 
            background-color: #1a1a2e;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.7);
            z-index: 10;
        }
        
        .login-illustration {
            width: 60%;
            background: #11111d;
            padding: 0; display: flex; flex-direction: column; justify-content: flex-end;
            align-items: center; color: white; position: relative;
        }
        .illustration-overlay {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 15, 26, 0.85);
            display: flex; flex-direction: column; justify-content: center;
            padding: 40px; text-align: center;
        }
        .illustration-overlay h2 { font-size: 2.2em; margin-bottom: 5px; color: #00bcd4; }
        .illustration-overlay p { opacity: 0.9; margin-bottom: 20px; }


        .login-form-area {
            width: 40%;
            padding: 50px 30px;
            display: flex; flex-direction: column; justify-content: center;
        }
        .login-form-area h3 {
            font-size: 1.8em; color: #4CAF50; margin-bottom: 30px;
            border-bottom: 2px solid #2a3c58; padding-bottom: 10px; text-align: center;
        }

        /* ========================================================================= */
        /* FORM ELEMENTS & ALERTS */
        /* ========================================================================= */
        .alert-danger { padding: 12px 20px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9em; background-color: #f8d7da; color: #721c24; border-left: 5px solid #f5c6cb; }
        .alert-success { padding: 12px 20px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9em; background-color: #d4edda; color: #155724; border-left: 5px solid #c3e6cb; }
        
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #b0b0b0; }
        .input-group input {
            width: 100%; padding: 12px; margin-bottom: 20px; background-color: #2a3c58; 
            color: #e4e6eb; border: 1px solid #3e5072; border-radius: 5px; font-size: 1em;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.5);
        }
        .input-group input:focus { border-color: #4CAF50; box-shadow: 0 0 8px rgba(76, 175, 80, 0.6); outline: none; }

        .btn-primary { 
            background-color: #4CAF50; color: #0f0f1a; margin-top: 25px; 
            padding: 12px 20px; 
        }
        .btn-primary:hover { background-color: #5cc460; }

        /* Tata Letak Link Bawah */
        .link-group {
            display: flex;
            justify-content: center;
            margin-top: 15px;
            font-size: 0.9em;
            text-align: center;
        }
        .link-group a {
             color: #b0b0b0;
             text-decoration: underline;
        }
        .link-group a:hover {
            color: #00bcd4;
        }

        /* ========================================================================= */
        /* RESPONSIVE */
        /* ========================================================================= */
        @media (max-width: 850px) {
            .login-box { flex-direction: column; width: 95%; min-height: auto; }
            .login-illustration { width: 100%; min-height: 250px; order: 2; }
            .login-form-area { width: 100%; order: 1; padding: 30px; }
            .illustration-overlay { padding: 30px; }
            .login-illustration h2 { font-size: 1.8em; }
        }
    </style>
</head>
<body>
    <div class="animated-bg"></div>
    <div class="login-page">
        <div class="login-box">
            
            <div class="login-illustration">
                <div class="cover-carousel" id="cover-carousel">
                    </div>
                <div class="illustration-overlay">
                    <h2>Buat Akun Admin Baru</h2>
                    <p>Daftarkan diri Anda untuk mendapatkan akses ke Game Hub Console.</p>
                    <p style="font-size: 0.8em; margin-top: 10px;">Masuk hanya untuk Administrator</p>
                </div>
            </div>

            <div class="login-form-area">
                <h3>DAFTAR AKUN ADMIN</h3>
                
                <?php echo $pesan; ?>
                
                <form action="" method="POST">
                    <div class="input-group">
                        <label for="nama_lengkap">Nama Lengkap</label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Nama Lengkap Anda" required>
                    </div>
                    <div class="input-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Buat username" required>
                    </div>
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Buat password" required>
                    </div>
                    <button type="submit" name="register" class="btn btn-primary btn-full">DAFTAR</button>
                </form>
                
                <div class="link-group">
                    <a href="login.php">Sudah punya akun? Login!</a>
                </div>
            </div>
            
        </div>
    </div>
    
    <script>
        const coverImages = [
            '<?php echo BASE_URL; ?>/assets/img/cover_80fcf7.jpg',
            '<?php echo BASE_URL; ?>/assets/img/cover_60f6ac9bb036e.jpg',
            '<?php echo BASE_URL; ?>/assets/img/hero_default.jpg' 
        ];

        const carouselContainer = document.getElementById('cover-carousel');
        let currentCoverIndex = 0;

        function loadCovers() {
            coverImages.forEach((src, index) => {
                const div = document.createElement('div');
                div.classList.add('cover-item');
                div.style.backgroundImage = `url('${src}')`;
                if (index === 0) {
                    div.classList.add('active');
                }
                carouselContainer.appendChild(div);
            });
        }

        function nextCover() {
            const items = carouselContainer.querySelectorAll('.cover-item');
            items[currentCoverIndex].classList.remove('active');
            currentCoverIndex = (currentCoverIndex + 1) % items.length;
            items[currentCoverIndex].classList.add('active');
        }

        if (coverImages.length > 0) {
            loadCovers();
            if (coverImages.length > 1) {
                setInterval(nextCover, 5000); 
            }
        }
    </script>
</body>
</html>