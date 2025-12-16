<?php
session_start();
include '../config.php';

// Coming Soon Wishlist - berbasis localStorage
// Data disimpan di browser user via followedGames key

$is_logged_in = isset($_SESSION['user_id']);
$is_admin = $is_logged_in && ($_SESSION['role'] === 'admin');

// Admin diarahkan ke admin panel
if ($is_admin) {
    header("Location: " . BASE_URL . "/admin/index.php");
    exit();
}

$username_display = $is_logged_in ? ($_SESSION['username'] ?? 'User') : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coming Soon - Game Hub</title>
    <meta name="description" content="Game Coming Soon yang kamu tunggu">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <style>
        .wishlist-header {
            background: linear-gradient(135deg, var(--bg-dark), var(--bg-elevated));
            padding: var(--space-2xl) 0;
            text-align: center;
            border-bottom: 1px solid var(--border-dark);
        }
        .wishlist-header h1 {
            font-size: 2.5rem;
            margin-bottom: var(--space-sm);
            background: linear-gradient(135deg, var(--accent), var(--warning));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .wishlist-header .bell { animation: bellRing 2s ease-in-out infinite; display: inline-block; }
        @keyframes bellRing {
            0%, 100% { transform: rotate(0); }
            10%, 30% { transform: rotate(-10deg); }
            20%, 40% { transform: rotate(10deg); }
            50% { transform: rotate(0); }
        }
        .wishlist-header p { color: var(--text-secondary); }
        .wishlist-count { font-size: 1.5rem; font-weight: 700; color: var(--accent); margin-top: var(--space-sm); }
        .wishlist-content { padding: var(--space-2xl); min-height: 60vh; }
        .empty-wishlist {
            text-align: center;
            padding: var(--space-2xl);
            background: var(--bg-elevated);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-dark);
        }
        .empty-wishlist h3 { margin-bottom: var(--space-md); color: var(--text-muted); }
        .wishlist-card { position: relative; }
        .remove-wishlist-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 68, 102, 0.9);
            border: none;
            cursor: pointer;
            font-size: 1.2em;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-fast);
            z-index: 2;
            color: white;
        }
        .remove-wishlist-btn:hover { background: var(--danger); transform: scale(1.1); }
        .loading-games { text-align: center; padding: var(--space-2xl); color: var(--text-muted); }
        .coming-soon-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: linear-gradient(135deg, var(--accent), var(--warning));
            color: var(--bg-darkest);
            padding: 4px 10px;
            font-size: 0.75em;
            font-weight: 600;
            border-radius: var(--radius-sm);
            z-index: 2;
        }
        .released-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: var(--success);
            color: white;
            padding: 4px 10px;
            font-size: 0.75em;
            font-weight: 600;
            border-radius: var(--radius-sm);
            z-index: 2;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .notification-banner {
            background: linear-gradient(135deg, var(--success), #10b981);
            padding: var(--space-lg);
            border-radius: var(--radius-lg);
            margin-bottom: var(--space-xl);
            text-align: center;
            color: white;
            display: none;
        }
        .notification-banner h3 { margin-bottom: var(--space-sm); }
        .game-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: var(--space-lg);
            max-width: 1200px;
            margin: 0 auto;
        }
        .game-card { position: relative; }
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
                    <a href="<?php echo BASE_URL; ?>/pages/koleksi.php" class="active">&#128276; Coming Soon</a>
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
                        <a href="<?php echo BASE_URL; ?>/pages/koleksi.php">&#128276; Coming Soon</a>
                        <a href="<?php echo BASE_URL; ?>/pages/download_history.php">&#128229; Downloads</a>
                        <a href="<?php echo BASE_URL; ?>/pages/request_game.php">&#127919; Request</a>
                        <div class="menu-separator"></div>
                        <a href="<?php echo BASE_URL; ?>/auth/logout.php">&#128682; Logout</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Wishlist Header -->
    <div class="wishlist-header">
        <div class="container">
            <h1><span class="bell">&#128276;</span> Coming Soon</h1>
            <p>Game yang kamu tunggu rilisnya</p>
            <div class="wishlist-count" id="wishlistCount">0 game</div>
        </div>
    </div>

    <!-- Released Notification Banner -->
    <div class="wishlist-content">
        <div class="notification-banner" id="releasedBanner">
            <h3>&#127881; Game Baru Rilis!</h3>
            <p id="releasedMessage">Beberapa game yang kamu tunggu sudah dirilis!</p>
        </div>

        <!-- Loading -->
        <div class="loading-games" id="loadingGames">
            <div class="loader" style="margin: 0 auto;"></div>
            <p style="margin-top: var(--space-md);">Memuat game...</p>
        </div>

        <!-- Empty State -->
        <div class="empty-wishlist" id="emptyWishlist" style="display: none;">
            <div style="font-size: 4rem; margin-bottom: var(--space-md);">&#128276;</div>
            <h3>Belum Ada Game</h3>
            <p style="margin-bottom: var(--space-lg); color: var(--text-muted);">
                Kamu belum follow game Coming Soon apapun.<br>
                Klik "Notify Me" pada halaman game Coming Soon untuk mendapat notifikasi!
            </p>
            <a href="<?php echo BASE_URL; ?>/pages/semua_game.php" class="btn btn-primary">Jelajahi Game</a>
        </div>

        <!-- Games Grid -->
        <div class="game-grid" id="wishlistGrid" style="display: none;"></div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <span class="footer-brand">&#127918; GAMEHUB</span>
            <div class="footer-links">
                <a href="<?php echo BASE_URL; ?>">Beranda</a>
                <a href="<?php echo BASE_URL; ?>/pages/semua_game.php">Semua Game</a>
            </div>
            <p class="footer-text">&copy; <?php echo date('Y'); ?> Game Hub</p>
        </div>
    </footer>

    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        
        // Get followed games from localStorage
        function getFollowedGames() {
            try { return JSON.parse(localStorage.getItem('followedGames') || '[]'); }
            catch (e) { return []; }
        }
        
        // Remove from followed
        function removeFromFollowed(gameId) {
            let followed = getFollowedGames();
            followed = followed.filter(id => id !== gameId);
            localStorage.setItem('followedGames', JSON.stringify(followed));
            loadWishlist();
        }
        
        // Load wishlist
        function loadWishlist() {
            const followed = getFollowedGames();
            const countEl = document.getElementById('wishlistCount');
            const loadingEl = document.getElementById('loadingGames');
            const emptyEl = document.getElementById('emptyWishlist');
            const gridEl = document.getElementById('wishlistGrid');
            const bannerEl = document.getElementById('releasedBanner');
            
            countEl.textContent = `${followed.length} game`;
            
            if (followed.length === 0) {
                loadingEl.style.display = 'none';
                emptyEl.style.display = 'block';
                gridEl.style.display = 'none';
                bannerEl.style.display = 'none';
                return;
            }
            
            // Fetch game data
            fetch(BASE_URL + '/api/get_games_by_ids.php?ids=' + followed.join(','))
                .then(r => r.json())
                .then(data => {
                    loadingEl.style.display = 'none';
                    
                    if (data.success && data.games.length > 0) {
                        gridEl.style.display = 'grid';
                        
                        // Check for released games
                        const releasedGames = data.games.filter(g => !g.coming_soon || g.coming_soon == 0);
                        if (releasedGames.length > 0) {
                            const names = releasedGames.map(g => g.nama).join(', ');
                            document.getElementById('releasedMessage').textContent = `${names} sudah bisa didownload!`;
                            bannerEl.style.display = 'block';
                        }
                        
                        gridEl.innerHTML = data.games.map(game => `
                            <div class="game-card wishlist-card">
                                <button class="remove-wishlist-btn" onclick="event.preventDefault(); removeFromFollowed(${game.id})" title="Hapus dari list">
                                    &#10006;
                                </button>
                                ${game.coming_soon == 1 
                                    ? '<span class="coming-soon-badge">&#128284; Coming Soon</span>' 
                                    : '<span class="released-badge">&#127881; RILIS!</span>'
                                }
                                <a href="${BASE_URL}/pages/detail.php?id=${game.id}">
                                    <img src="${BASE_URL}/${game.gambar_path}" alt="${game.nama}" class="game-card-image">
                                    <div class="game-card-content">
                                        <h3 class="game-card-title">${game.nama}</h3>
                                        <div class="game-card-meta">
                                            <span>&#128065; ${game.view_count || 0}</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        `).join('');
                    } else {
                        emptyEl.style.display = 'block';
                    }
                })
                .catch(err => {
                    loadingEl.style.display = 'none';
                    console.error('Error loading games:', err);
                    emptyEl.innerHTML = `
                        <div style="font-size: 4rem; margin-bottom: var(--space-md);">&#128532;</div>
                        <h3>Terjadi Kesalahan</h3>
                        <p style="margin-bottom: var(--space-lg); color: var(--text-muted);">Gagal memuat data game.</p>
                        <button onclick="loadWishlist()" class="btn btn-primary">Coba Lagi</button>
                    `;
                    emptyEl.style.display = 'block';
                });
        }
        
        // On load
        window.addEventListener('load', function() {
            // Hide loader
            const loader = document.getElementById('loadingOverlay');
            if (loader) { loader.style.opacity = '0'; setTimeout(() => loader.style.display = 'none', 500); }
            
            // Load wishlist
            loadWishlist();
        });
        
        // Navbar scroll
        window.addEventListener('scroll', function() {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 50);
        });
    </script>
</body>
</html>