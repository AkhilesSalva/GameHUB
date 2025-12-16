<?php
session_start();
include 'config.php';

// Fungsi untuk mendapatkan genre game
function get_game_genres_str($koneksi, $game_id) {
    $genre_q = mysqli_query($koneksi, "SELECT g.nama_genre FROM genre g JOIN game_genre gg ON g.id = gg.genre_id WHERE gg.game_id = $game_id LIMIT 2");
    $genres = [];
    while ($row = mysqli_fetch_assoc($genre_q)) {
        $genres[] = $row['nama_genre'];
    }
    return implode(', ', $genres);
}

// Hero Games untuk carousel
$hero_games_query = "SELECT id, nama, deskripsi, link_type, link_single, gambar_path, hero_image_path FROM games WHERE hero_image_path IS NOT NULL AND hero_image_path != '' ORDER BY view_count DESC, created_at DESC LIMIT 5";
$hero_games_result = mysqli_query($koneksi, $hero_games_query);
$all_hero_games = [];
if ($hero_games_result) {
    while ($row = mysqli_fetch_assoc($hero_games_result)) {
        $row['download_link'] = $row['link_single']; 
        $all_hero_games[] = $row;
    }
}

// Discover Games (Terbaru - exclude coming soon)
$discover_query = "SELECT g.id, g.nama, g.gambar_path, g.download_count, g.view_count, COUNT(k.id) as komentar_count 
                   FROM games g 
                   LEFT JOIN komentar k ON g.id = k.game_id 
                   WHERE g.coming_soon = 0
                   GROUP BY g.id 
                   ORDER BY g.created_at DESC LIMIT 8";
$discover_result = mysqli_query($koneksi, $discover_query);

// Popular Games (exclude coming soon)
$popular_query = "SELECT g.id, g.nama, g.gambar_path, g.download_count, g.view_count, COUNT(k.id) as komentar_count 
                  FROM games g 
                  LEFT JOIN komentar k ON g.id = k.game_id 
                  WHERE g.coming_soon = 0
                  GROUP BY g.id 
                  ORDER BY g.download_count DESC LIMIT 4";
$popular_result = mysqli_query($koneksi, $popular_query);

// Coming Soon Games
$coming_soon_query = "SELECT id, nama, gambar_path, deskripsi FROM games WHERE coming_soon = 1 ORDER BY created_at DESC LIMIT 4";
$coming_soon_result = mysqli_query($koneksi, $coming_soon_query);

$is_logged_in = isset($_SESSION['user_id']);
$is_admin = $is_logged_in && ($_SESSION['role'] === 'admin');
$username_display = $is_logged_in ? ($_SESSION['username'] ?? 'User') : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Hub - Portal Game Offline Terlengkap</title>
    <meta name="description" content="Download game offline gratis terlengkap dan terbaru.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <style>
        .hero-section {
            position: relative;
            height: 70vh;
            min-height: 500px;
            margin-bottom: var(--space-2xl);
            display: flex;
            overflow: hidden;
        }
        .hero-main {
            flex: 1;
            position: relative;
            overflow: hidden;
        }
        .hero-image {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            transition: opacity 0.8s ease-in-out, transform 8s ease-out;
            transform: scale(1);
        }
        .hero-image.transitioning {
            opacity: 0;
            transform: scale(1.05);
        }
        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(10,10,15,0.95) 0%, rgba(10,10,15,0.4) 50%, rgba(10,10,15,0.2) 100%);
        }
        .hero-content {
            position: absolute;
            bottom: 60px;
            left: 40px;
            right: 40px;
            z-index: 10;
        }
        .hero-title {
            font-size: 3em;
            font-weight: 700;
            margin-bottom: var(--space-sm);
            text-shadow: 2px 2px 8px rgba(0,0,0,0.5);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }
        .hero-title.fade-out { opacity: 0; transform: translateY(20px); }
        .hero-desc {
            color: var(--text-secondary);
            font-size: 1.1em;
            margin-bottom: var(--space-lg);
            max-width: 600px;
            transition: opacity 0.5s ease 0.1s, transform 0.5s ease 0.1s;
        }
        .hero-desc.fade-out { opacity: 0; transform: translateY(20px); }
        .hero-btn {
            display: inline-flex;
            align-items: center;
            gap: var(--space-sm);
            padding: 16px 32px;
            background: var(--accent);
            color: var(--bg-darkest);
            font-weight: 600;
            border-radius: var(--radius-md);
            transition: all 0.3s ease;
        }
        .hero-btn:hover {
            background: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0,255,136,0.3);
        }
        .hero-sidebar {
            width: 300px;
            background: rgba(15,15,20,0.95);
            backdrop-filter: blur(10px);
            padding: var(--space-md);
            display: flex;
            flex-direction: column;
            gap: var(--space-sm);
            overflow-y: auto;
        }
        .sidebar-item {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            padding: var(--space-sm);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            border: 1px solid transparent;
        }
        .sidebar-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--accent);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }
        .sidebar-item:hover { background: rgba(255,255,255,0.05); }
        .sidebar-item.active { background: rgba(0,255,136,0.1); border-color: rgba(0,255,136,0.3); }
        .sidebar-item.active::before { transform: scaleY(1); }
        .sidebar-item img { width: 50px; height: 65px; object-fit: cover; border-radius: var(--radius-sm); transition: transform 0.3s ease; }
        .sidebar-item:hover img { transform: scale(1.05); }
        .sidebar-item-title { font-size: 0.9em; font-weight: 500; color: var(--text-primary); transition: color 0.3s ease; }
        .sidebar-item.active .sidebar-item-title { color: var(--accent); }
        .sidebar-progress { position: absolute; bottom: 0; left: 0; height: 2px; background: var(--accent); width: 0%; transition: none; }
        .sidebar-item.active .sidebar-progress { transition: width linear; }
        .follow-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: var(--radius-full);
            color: var(--text-primary);
            font-size: 0.85em;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .follow-btn:hover, .follow-btn.following { background: var(--accent); color: var(--bg-darkest); border-color: var(--accent); }
        .toast-notification {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: var(--bg-elevated);
            color: var(--text-primary);
            padding: 16px 24px;
            border-radius: var(--radius-md);
            border: 1px solid var(--accent);
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
            z-index: 9999;
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .toast-notification.show { transform: translateX(-50%) translateY(0); opacity: 1; }
        @media (max-width: 900px) {
            .hero-section { flex-direction: column; height: auto; }
            .hero-sidebar { width: 100%; flex-direction: row; overflow-x: auto; }
            .hero-title { font-size: 2em; }
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
                    <a href="<?php echo BASE_URL; ?>/index.php" class="active">Beranda</a>
                    <a href="<?php echo BASE_URL; ?>/pages/semua_game.php">Semua Game</a>
                    <a href="<?php echo BASE_URL; ?>/pages/koleksi.php">&#128276; Coming Soon</a>
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
                                <a href="<?php echo BASE_URL; ?>/pages/koleksi.php">&#128276; Coming Soon</a>
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
    
    <?php if (!empty($all_hero_games)): ?>
    <!-- Hero Section with Smooth Carousel -->
    <section class="hero-section">
        <div class="hero-main">
            <div class="hero-image" id="hero-image" style="background-image: url('<?php echo BASE_URL . '/' . str_replace('\\', '/', $all_hero_games[0]['hero_image_path']); ?>');"></div>
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <h1 class="hero-title" id="hero-title"><?php echo htmlspecialchars($all_hero_games[0]['nama']); ?></h1>
                <p class="hero-desc" id="hero-desc"><?php echo htmlspecialchars(substr($all_hero_games[0]['deskripsi'] ?? '', 0, 120)); ?>...</p>
                <a href="pages/detail.php?id=<?php echo $all_hero_games[0]['id']; ?>" class="hero-btn" id="main-hero-link">
                    Lihat Detail &#8594;
                </a>
            </div>
        </div>
        <div class="hero-sidebar">
            <?php foreach ($all_hero_games as $index => $hero_game): ?>
            <div class="sidebar-item <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                <img src="<?php echo BASE_URL . '/' . htmlspecialchars($hero_game['gambar_path']); ?>" alt="<?php echo htmlspecialchars($hero_game['nama']); ?>">
                <div>
                    <div class="sidebar-item-title"><?php echo htmlspecialchars($hero_game['nama']); ?></div>
                </div>
                <div class="sidebar-progress"></div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Terbaru Section -->
    <section class="section">
        <div class="section-header">
            <h2 class="section-title">Terbaru</h2>
            <a href="<?php echo BASE_URL; ?>/pages/semua_game.php?sort=newest" class="section-link">Lihat Semua &#8594;</a>
        </div>
        <div class="games-grid">
            <?php if ($discover_result && mysqli_num_rows($discover_result) > 0): ?>
                <?php while ($game = mysqli_fetch_assoc($discover_result)): ?>
                <a href="pages/detail.php?id=<?php echo $game['id']; ?>" class="game-card">
                    <div class="game-card-image">
                        <img src="<?php echo BASE_URL . '/' . htmlspecialchars($game['gambar_path']); ?>" alt="<?php echo htmlspecialchars($game['nama']); ?>">
                    </div>
                    <div class="game-card-content">
                        <span class="game-card-genre"><?php echo get_game_genres_str($koneksi, $game['id']); ?></span>
                        <h3 class="game-card-title"><?php echo htmlspecialchars($game['nama']); ?></h3>
                        <div class="game-card-stats">
                            <span>&#128229; <?php echo number_format($game['download_count']); ?></span>
                            <span>&#128065; <?php echo number_format($game['view_count']); ?></span>
                        </div>
                    </div>
                </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="empty-state">Belum ada game.</p>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Populer Section -->
    <section class="section">
        <div class="section-header">
            <h2 class="section-title">Paling Populer</h2>
            <a href="<?php echo BASE_URL; ?>/pages/semua_game.php?sort=popular" class="section-link">Lihat Semua &#8594;</a>
        </div>
        <div class="games-grid">
            <?php if ($popular_result && mysqli_num_rows($popular_result) > 0): ?>
                <?php while ($game = mysqli_fetch_assoc($popular_result)): ?>
                <a href="pages/detail.php?id=<?php echo $game['id']; ?>" class="game-card">
                    <div class="game-card-image">
                        <img src="<?php echo BASE_URL . '/' . htmlspecialchars($game['gambar_path']); ?>" alt="<?php echo htmlspecialchars($game['nama']); ?>">
                        <span class="game-card-badge" style="background: var(--warning);">&#128293; Hot</span>
                    </div>
                    <div class="game-card-content">
                        <span class="game-card-genre"><?php echo get_game_genres_str($koneksi, $game['id']); ?></span>
                        <h3 class="game-card-title"><?php echo htmlspecialchars($game['nama']); ?></h3>
                        <div class="game-card-stats">
                            <span>&#128293; <?php echo number_format($game['download_count']); ?></span>
                            <span>&#128065; <?php echo number_format($game['view_count']); ?></span>
                        </div>
                    </div>
                </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="empty-state">Belum ada game populer.</p>
            <?php endif; ?>
        </div>
    </section>
    
    <?php if ($coming_soon_result && mysqli_num_rows($coming_soon_result) > 0): ?>
    <!-- Coming Soon Section -->
    <section class="section">
        <div class="section-header">
            <h2 class="section-title">&#128284; Coming Soon</h2>
            <span class="section-link" style="color: var(--text-muted);">Akan Segera Hadir</span>
        </div>
        <div class="games-grid">
            <?php while ($game = mysqli_fetch_assoc($coming_soon_result)): ?>
            <a href="pages/detail.php?id=<?php echo $game['id']; ?>" class="game-card coming-soon-card">
                <div class="game-card-image">
                    <img src="<?php echo BASE_URL . '/' . htmlspecialchars($game['gambar_path']); ?>" alt="<?php echo htmlspecialchars($game['nama']); ?>">
                    <span class="game-card-badge" style="background: var(--accent);">&#128284; Coming Soon</span>
                </div>
                <div class="game-card-content">
                    <h3 class="game-card-title"><?php echo htmlspecialchars($game['nama']); ?></h3>
                    <?php if (!$is_admin): ?>
                    <button class="follow-btn" onclick="event.preventDefault(); toggleFollow(<?php echo $game['id']; ?>, '<?php echo htmlspecialchars(addslashes($game['nama'])); ?>')">
                        <span class="follow-icon">&#128276;</span>
                        <span class="follow-text">Notify Me</span>
                    </button>
                    <?php endif; ?>
                </div>
            </a>
            <?php endwhile; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div>
                <span class="footer-brand" id="secretLogin" style="cursor: default;">&#127918; GAMEHUB</span>
                <p class="footer-text" style="margin-top: 8px;">Portal Game Offline Terlengkap</p>
            </div>
            <div class="footer-links">
                <a href="<?php echo BASE_URL; ?>">Beranda</a>
                <a href="<?php echo BASE_URL; ?>/pages/semua_game.php">Semua Game</a>
                <a href="<?php echo BASE_URL; ?>/pages/koleksi.php">Coming Soon</a>
            </div>
            <p class="footer-text">&copy; <?php echo date('Y'); ?> Game Hub</p>
        </div>
    </footer>

    <!-- Toast Notification -->
    <div class="toast-notification" id="toast"></div>

    <script>
        // Hide loader when page is ready
        window.addEventListener('load', function() {
            const loader = document.getElementById('loadingOverlay');
            if (loader) {
                loader.style.opacity = '0';
                setTimeout(() => loader.style.display = 'none', 500);
            }
        });
        
        // Secret login
        let clickCount = 0, clickTimer = null;
        const secretLogin = document.getElementById('secretLogin');
        if (secretLogin) {
            secretLogin.addEventListener('click', function() {
                clickCount++;
                clearTimeout(clickTimer);
                clickTimer = setTimeout(() => { clickCount = 0; }, 800);
                if (clickCount >= 3) window.location.href = '<?php echo BASE_URL; ?>/auth/login.php';
            });
        }
        
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            navbar.classList.toggle('scrolled', window.scrollY > 50);
        });
        
        // Toast notification
        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 2500);
        }
        
        // Get followed games from localStorage
        function getFollowedGames() {
            try { return JSON.parse(localStorage.getItem('followedGames') || '[]'); }
            catch (e) { return []; }
        }
        
        // Toggle follow for Coming Soon games
        function toggleFollow(gameId, gameName) {
            let followed = getFollowedGames();
            const btn = event.currentTarget;
            const index = followed.indexOf(gameId);
            
            if (index === -1) {
                followed.push(gameId);
                showToast(`Kamu akan dinotifikasi saat "${gameName}" dirilis!`);
                btn.classList.add('following');
                btn.innerHTML = '<span class="follow-icon">&#10004;</span><span class="follow-text">Following</span>';
            } else {
                followed.splice(index, 1);
                showToast(`Notifikasi untuk "${gameName}" dinonaktifkan.`);
                btn.classList.remove('following');
                btn.innerHTML = '<span class="follow-icon">&#128276;</span><span class="follow-text">Notify Me</span>';
            }
            
            localStorage.setItem('followedGames', JSON.stringify(followed));
        }
        
        // Update follow button states on load
        document.addEventListener('DOMContentLoaded', function() {
            const followed = getFollowedGames();
            document.querySelectorAll('.follow-btn').forEach(btn => {
                const onclickStr = btn.getAttribute('onclick');
                const match = onclickStr.match(/toggleFollow\((\d+)/);
                if (match && followed.includes(parseInt(match[1]))) {
                    btn.classList.add('following');
                    btn.innerHTML = '<span class="follow-icon">&#10004;</span><span class="follow-text">Following</span>';
                }
            });
        });
        
        <?php if (!empty($all_hero_games)): ?>
        // Smooth Hero Carousel
        const heroGamesData = <?php echo json_encode($all_hero_games); ?>;
        const heroImage = document.getElementById('hero-image');
        const heroTitle = document.getElementById('hero-title');
        const heroDesc = document.getElementById('hero-desc');
        const heroLink = document.getElementById('main-hero-link');
        const sidebarItems = document.querySelectorAll('.sidebar-item');
        let currentIndex = 0, carouselInterval;
        const DURATION = 6000;
        
        function updateHero(index) {
            const game = heroGamesData[index];
            const imgUrl = `<?php echo BASE_URL . '/'; ?>${game.hero_image_path.replace(/\\/g, '/')}`;
            
            heroImage.classList.add('transitioning');
            heroTitle.classList.add('fade-out');
            heroDesc.classList.add('fade-out');
            
            setTimeout(() => {
                heroImage.style.backgroundImage = `url('${imgUrl}')`;
                heroTitle.textContent = game.nama;
                heroDesc.textContent = game.deskripsi ? game.deskripsi.substring(0, 120) + '...' : '';
                heroLink.href = `pages/detail.php?id=${game.id}`;
                
                requestAnimationFrame(() => {
                    heroImage.classList.remove('transitioning');
                    heroTitle.classList.remove('fade-out');
                    heroDesc.classList.remove('fade-out');
                });
                
                updateSidebar(index);
            }, 400);
        }
        
        function updateSidebar(activeIndex) {
            sidebarItems.forEach((item, index) => {
                const progress = item.querySelector('.sidebar-progress');
                item.classList.remove('active');
                progress.style.transition = 'none';
                progress.style.width = '0%';
                
                if (index === activeIndex) {
                    item.classList.add('active');
                    setTimeout(() => {
                        progress.style.transition = `width ${DURATION}ms linear`;
                        progress.style.width = '100%';
                    }, 50);
                }
            });
        }
        
        function nextHero() {
            currentIndex = (currentIndex + 1) % heroGamesData.length;
            updateHero(currentIndex);
        }
        
        function startCarousel() {
            updateSidebar(0);
            carouselInterval = setInterval(nextHero, DURATION);
        }
        
        sidebarItems.forEach(item => {
            item.addEventListener('click', function() {
                clearInterval(carouselInterval);
                const index = parseInt(this.dataset.index);
                if (index !== currentIndex) {
                    currentIndex = index;
                    updateHero(index);
                }
                carouselInterval = setInterval(nextHero, DURATION);
            });
        });
        
        startCarousel();
        <?php endif; ?>
    </script>
</body>
</html>