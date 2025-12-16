<?php
session_start();
include '../config.php';

$is_logged_in = isset($_SESSION['user_id']);
$is_admin = $is_logged_in && ($_SESSION['role'] === 'admin');
$username_display = $is_logged_in ? ($_SESSION['username'] ?? 'User') : '';

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    $game_name = mysqli_real_escape_string($koneksi, trim($_POST['game_name']));
    $description = mysqli_real_escape_string($koneksi, trim($_POST['description'] ?? ''));
    $platform = mysqli_real_escape_string($koneksi, trim($_POST['platform'] ?? ''));
    $user_id = $is_logged_in ? (int)$_SESSION['user_id'] : 'NULL';
    
    if (empty($game_name)) {
        $error_message = 'Nama game harus diisi!';
    } else {
        $insert = mysqli_query($koneksi, "INSERT INTO game_requests (user_id, game_name, description, platform, status) VALUES ($user_id, '$game_name', '$description', '$platform', 'pending')");
        if ($insert) {
            $success_message = 'Request game berhasil dikirim! Admin akan meninjau permintaan kamu.';
        } else {
            $error_message = 'Gagal mengirim request. Silakan coba lagi.';
        }
    }
}

// Get user's requests if logged in
$my_requests = [];
if ($is_logged_in) {
    $requests_query = mysqli_query($koneksi, "SELECT * FROM game_requests WHERE user_id = " . (int)$_SESSION['user_id'] . " ORDER BY created_at DESC LIMIT 10");
    while ($row = mysqli_fetch_assoc($requests_query)) {
        $my_requests[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Game - Game Hub</title>
    <meta name="description" content="Request game yang kamu inginkan di Game Hub">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <style>
        .request-page {
            padding: var(--space-2xl) 0;
        }
        .request-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-xl);
        }
        @media (max-width: 900px) {
            .request-grid {
                grid-template-columns: 1fr;
            }
        }
        .request-form-card {
            background: var(--bg-card);
            border: 1px solid var(--border-dark);
            border-radius: var(--radius-lg);
            padding: var(--space-xl);
        }
        .request-form-card h2 {
            color: var(--primary);
            margin-bottom: var(--space-lg);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }
        .form-group {
            margin-bottom: var(--space-lg);
        }
        .form-group label {
            display: block;
            color: var(--accent);
            font-weight: 500;
            margin-bottom: var(--space-sm);
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 14px var(--space-md);
            background: var(--bg-elevated);
            border: 1px solid var(--border-dark);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-size: 1em;
            font-family: inherit;
            transition: all var(--transition-normal);
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 15px var(--primary-glow);
        }
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        .my-requests-card {
            background: var(--bg-card);
            border: 1px solid var(--border-dark);
            border-radius: var(--radius-lg);
            padding: var(--space-xl);
        }
        .my-requests-card h2 {
            color: var(--accent);
            margin-bottom: var(--space-lg);
        }
        .request-item {
            padding: var(--space-md);
            border-bottom: 1px solid var(--border-dark);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .request-item:last-child {
            border-bottom: none;
        }
        .request-name {
            font-weight: 600;
            color: var(--text-primary);
        }
        .request-date {
            font-size: 0.85em;
            color: var(--text-muted);
        }
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75em;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending { background: var(--warning); color: var(--bg-darkest); }
        .status-approved { background: var(--accent); color: var(--bg-darkest); }
        .status-completed { background: var(--success); color: var(--bg-darkest); }
        .status-rejected { background: var(--danger); color: white; }
        .alert {
            padding: var(--space-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-lg);
        }
        .alert-success {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid var(--success);
            color: var(--success);
        }
        .alert-error {
            background: rgba(255, 68, 102, 0.1);
            border: 1px solid var(--danger);
            color: var(--danger);
        }
    </style>
</head>
<body>
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loader"></div>
    </div>

    <!-- Navbar -->
    <nav class="navbar" id="navbar">
        <div class="navbar-content">
            <a href="<?php echo BASE_URL; ?>" class="navbar-brand">GAMEHUB</a>
            <div class="nav-center">
                <div class="nav-links">
                    <a href="<?php echo BASE_URL; ?>/index.php">Beranda</a>
                    <a href="<?php echo BASE_URL; ?>/pages/semua_game.php">Semua Game</a>
                </div>
            </div>
            <div class="nav-right">
                <form class="search-box" action="<?php echo BASE_URL; ?>/pages/semua_game.php" method="GET">
                    <input type="text" name="search" placeholder="Cari game..." autocomplete="off">
                    <button type="submit">&#128269;</button>
                </form>
                <?php if ($is_logged_in): ?>
                    <div class="user-menu-container">
                        <button class="profile-btn"><?php echo htmlspecialchars($username_display); ?></button>
                        <div class="user-menu">
                            <?php if ($is_admin): ?>
                                <a href="<?php echo BASE_URL; ?>/admin/index.php">&#127918; Admin Console</a>
                                <div class="menu-separator"></div>
                                <a href="<?php echo BASE_URL; ?>/auth/logout.php">&#128682; Logout</a>
                            <?php else: ?>
                                <a href="<?php echo BASE_URL; ?>/pages/koleksi.php">&#10084;&#65039; Wishlist</a>
                                <a href="<?php echo BASE_URL; ?>/pages/download_history.php">&#128229; Downloads</a>
                                <a href="<?php echo BASE_URL; ?>/pages/request_game.php">&#127919; Request</a>
                                <div class="menu-separator"></div>
                                <a href="<?php echo BASE_URL; ?>/auth/logout.php">&#128682; Logout</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container request-page">
        <div class="section-header" style="margin-bottom: var(--space-xl);">
            <h1>🎮 Request Game</h1>
            <p style="color: var(--text-secondary);">Tidak menemukan game yang kamu cari? Request di sini!</p>
        </div>

        <div class="request-grid">
            <div class="request-form-card">
                <h2>📝 Form Request</h2>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success">✓ <?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-error">✕ <?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Nama Game *</label>
                        <input type="text" name="game_name" placeholder="Contoh: Grand Theft Auto V" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Platform</label>
                        <select name="platform">
                            <option value="">-- Pilih Platform --</option>
                            <option value="PC">PC / Windows</option>
                            <option value="Android">Android</option>
                            <option value="PS4">PlayStation 4</option>
                            <option value="PS5">PlayStation 5</option>
                            <option value="Xbox">Xbox</option>
                            <option value="Nintendo Switch">Nintendo Switch</option>
                            <option value="Other">Lainnya</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Keterangan Tambahan</label>
                        <textarea name="description" placeholder="Contoh: Versi terbaru, include DLC, dll..."></textarea>
                    </div>
                    
                    <button type="submit" name="submit_request" class="btn btn-primary btn-full">
                        🚀 Kirim Request
                    </button>
                </form>
            </div>
            
            <div class="my-requests-card">
                <h2>📋 Request Saya</h2>
                
                <?php if (!$is_logged_in): ?>
                    <p style="color: var(--text-muted); text-align: center; padding: var(--space-xl);">
                        <a href="<?php echo BASE_URL; ?>/auth/login.php">Login</a> untuk melihat history request kamu.
                    </p>
                <?php elseif (empty($my_requests)): ?>
                    <p style="color: var(--text-muted); text-align: center; padding: var(--space-xl);">
                        Belum ada request. Mulai request game favorit kamu!
                    </p>
                <?php else: ?>
                    <?php foreach ($my_requests as $request): ?>
                        <div class="request-item">
                            <div>
                                <div class="request-name"><?php echo htmlspecialchars($request['game_name']); ?></div>
                                <div class="request-date"><?php echo date('d M Y', strtotime($request['created_at'])); ?></div>
                            </div>
                            <span class="status-badge status-<?php echo $request['status']; ?>">
                                <?php echo ucfirst($request['status']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <span class="footer-brand" id="secretLogin" style="cursor: default;">🎮 GAMEHUB</span>
            <div class="footer-links">
                <a href="<?php echo BASE_URL; ?>">Beranda</a>
                <a href="<?php echo BASE_URL; ?>/pages/semua_game.php">Semua Game</a>
                <a href="<?php echo BASE_URL; ?>/request_game.php">Request Game</a>
            </div>
            <p class="footer-text">&copy; <?php echo date('Y'); ?> Game Hub</p>
        </div>
    </footer>

    <script>
        window.addEventListener('load', function() {
            const loader = document.getElementById('loadingOverlay');
            if (loader) {
                loader.style.opacity = '0';
                setTimeout(() => loader.style.display = 'none', 500);
            }
        });
        
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
            // Secret login
        let clickCount = 0; let clickTimer = null;
        const secretLogin = document.getElementById('secretLogin');
        if (secretLogin) {
            secretLogin.addEventListener('click', function() {
                clickCount++;
                clearTimeout(clickTimer);
                clickTimer = setTimeout(() => { clickCount = 0; }, 800);
                if (clickCount >= 3) { window.location.href = '<?php echo BASE_URL; ?>/auth/login.php'; }
            });
        }
    </script>
</body>
</html>
