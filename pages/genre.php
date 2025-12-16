<?php
session_start();
include '../config.php';

// Ambil nama genre dari URL
if (!isset($_GET['nama']) || empty($_GET['nama'])) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}
$genre_nama = mysqli_real_escape_string($koneksi, $_GET['nama']);

// Fungsi untuk mendapatkan genre game
function get_game_genres_str($koneksi, $game_id) {
    $genre_q = mysqli_query($koneksi, "SELECT g.nama_genre FROM genre g JOIN game_genre gg ON g.id = gg.genre_id WHERE gg.game_id = $game_id LIMIT 2");
    $genres = [];
    while ($row = mysqli_fetch_assoc($genre_q)) {
        $genres[] = $row['nama_genre'];
    }
    return implode(', ', $genres);
}

// Query untuk mengambil semua game berdasarkan Genre
$games_query = "SELECT g.id, g.nama, g.gambar_path, g.download_count, COUNT(k.id) as komentar_count 
                FROM games g 
                JOIN game_genre gg ON g.id = gg.game_id
                JOIN genre gn ON gg.genre_id = gn.id
                LEFT JOIN komentar k ON g.id = k.game_id
                WHERE gn.nama_genre = '$genre_nama'
                GROUP BY g.id
                ORDER BY g.nama ASC"; 
$games_result = mysqli_query($koneksi, $games_query);

$is_logged_in = isset($_SESSION['user_id']);
$is_admin = $is_logged_in && ($_SESSION['role'] === 'admin');
$username_display = $is_logged_in ? ($_SESSION['username'] ?? 'User') : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genre: <?php echo htmlspecialchars($genre_nama); ?> - Game Hub</title>
    <meta name="description" content="Jelajahi game <?php echo htmlspecialchars($genre_nama); ?> terbaik di Game Hub.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <style>
        .genre-hero {
            background: linear-gradient(135deg, var(--bg-dark), var(--bg-elevated));
            padding: var(--space-2xl) 0;
            text-align: center;
            border-bottom: 1px solid var(--border-dark);
            margin-bottom: var(--space-xl);
        }
        .genre-hero h1 {
            font-size: 2.5em;
            margin-bottom: var(--space-sm);
        }
        .genre-hero h1 span {
            color: var(--primary);
        }
        .genre-hero p {
            color: var(--text-muted);
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: var(--space-sm);
            color: var(--text-secondary);
            margin-bottom: var(--space-lg);
        }
        .back-link:hover {
            color: var(--primary);
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
    
    <!-- Genre Hero -->
    <div class="genre-hero">
        <div class="container">
            <a href="<?php echo BASE_URL; ?>/pages/semua_game.php" class="back-link">← Kembali ke Semua Game</a>
            <h1>🏷️ Genre: <span><?php echo htmlspecialchars($genre_nama); ?></span></h1>
            <p>Menampilkan <?php echo mysqli_num_rows($games_result); ?> game dalam kategori ini</p>
        </div>
    </div>
    
    <div class="container">
        <div class="games-grid">
            <?php if ($games_result && mysqli_num_rows($games_result) > 0): ?>
                <?php while ($game = mysqli_fetch_assoc($games_result)): ?>
                <a href="detail.php?id=<?php echo $game['id']; ?>" class="game-card">
                    <div class="game-card-image">
                        <img src="<?php echo BASE_URL . '/' . htmlspecialchars($game['gambar_path']); ?>" alt="<?php echo htmlspecialchars($game['nama']); ?>">
                    </div>
                    <div class="game-card-content">
                        <span class="game-card-genre"><?php echo get_game_genres_str($koneksi, $game['id']); ?></span>
                        <h3 class="game-card-title"><?php echo htmlspecialchars($game['nama']); ?></h3>
                        <div class="game-card-stats">
                            <span>📥 <?php echo number_format($game['download_count']); ?></span>
                            <span>💬 <?php echo number_format($game['komentar_count']); ?></span>
                        </div>
                    </div>
                </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <h3 style="margin-bottom: var(--space-md);">🎮 Tidak ada game ditemukan</h3>
                    <p>Belum ada game dalam genre <strong><?php echo htmlspecialchars($genre_nama); ?></strong>.</p>
                    <a href="<?php echo BASE_URL; ?>/pages/semua_game.php" class="btn btn-secondary" style="margin-top: var(--space-lg);">Lihat Semua Game</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <span class="footer-brand" id="secretLogin" style="cursor: default;">🎮 GAMEHUB</span>
            <div class="footer-links">
                <a href="<?php echo BASE_URL; ?>">Beranda</a>
                <a href="<?php echo BASE_URL; ?>/pages/semua_game.php">Semua Game</a>
            </div>
            <p class="footer-text">&copy; <?php echo date('Y'); ?> Game Hub - Akhiles Salva</p>
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