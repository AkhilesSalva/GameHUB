<?php
session_start();
include 'config.php';

// Fungsi untuk mendapatkan genre game (untuk card)
function get_game_genres_str($koneksi, $game_id) {
    $genre_q = mysqli_query($koneksi, "SELECT g.nama_genre FROM genre g JOIN game_genre gg ON g.id = gg.genre_id WHERE gg.game_id = $game_id LIMIT 2");
    $genres = [];
    while ($row = mysqli_fetch_assoc($genre_q)) {
        $genres[] = $row['nama_genre'];
    }
    return implode(', ', $genres);
}

// Ambil 5 game untuk hero section (untuk carousel)
$hero_games_query = "SELECT id, nama, deskripsi, link_type, link_single, gambar_path, hero_image_path FROM games WHERE hero_image_path IS NOT NULL AND hero_image_path != '' ORDER BY view_count DESC, created_at DESC LIMIT 5";
$hero_games_result = mysqli_query($koneksi, $hero_games_query);
$all_hero_games = [];
if ($hero_games_result) {
    while ($row = mysqli_fetch_assoc($hero_games_result)) {
        $row['download_link'] = $row['link_single']; 
        $all_hero_games[] = $row;
    }
}

// Ambil 6 game untuk "Discover" section
$discover_query = "SELECT g.id, g.nama, g.gambar_path, g.download_count, COUNT(k.id) as komentar_count 
                   FROM games g 
                   LEFT JOIN komentar k ON g.id = k.game_id 
                   GROUP BY g.id 
                   ORDER BY g.created_at DESC LIMIT 6";
$discover_result = mysqli_query($koneksi, $discover_query);

$is_logged_in = isset($_SESSION['user_id']);
$is_admin = $is_logged_in && ($_SESSION['role'] === 'admin');
$username_display = $is_logged_in ? ($_SESSION['username'] ?? 'User') : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Hub - The Ultimate Game Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* ========================================================================= */
        /* 1. RESET, FONT, LOADER & UTILITIES */
        /* ========================================================================= */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { line-height: 1.6; background-color: #0f0f1a; color: #e4e6eb; }
        a { text-decoration: none; color: #00bcd4; transition: color 0.3s; }
        a:hover { color: #4CAF50; }
        .store-container { max-width: 1600px; margin: 40px auto; padding: 0 40px; }
        .loading-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(15, 19, 37, 0.95); display: flex; justify-content: center; align-items: center; z-index: 9999; transition: opacity 0.5s; }
        .loader { width: 80px; height: 80px; border: 5px solid #2a3c58; border-radius: 50%; border-top-color: #00bcd4; animation: spin 1s infinite linear; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ========================================================================= */
        /* 2. NAVBAR & DROPDOWN */
        /* ========================================================================= */
        .public-navbar { width: 100%; background-color: #11111d; padding: 10px 0; box-shadow: 0 4px 15px rgba(0,0,0,0.5); position: sticky; top: 0; z-index: 1000; }
        .navbar-content { max-width: 1600px; margin: 0 auto; padding: 0 40px; display: flex; justify-content: space-between; align-items: center; }
        .navbar-brand { color: #4CAF50; font-size: 1.8em; font-weight: 700; text-shadow: 0 0 5px #4CAF50; }
        .nav-right { display: flex; align-items: center; gap: 30px; }
        .nav-links { display: flex; gap: 30px; }
        .nav-links a { color: #b0b0b0; font-weight: 500; padding: 5px 0; transition: color 0.2s, border-bottom 0.2s; border-bottom: 2px solid transparent; }
        .nav-links a:hover, .nav-links a.active-link { color: white; border-bottom-color: #4CAF50; }
        
        .user-menu-container { position: relative; cursor: pointer; z-index: 1001; padding: 5px 0; }
        .profile-btn { background-color: #4CAF50; color: white; padding: 8px 15px; border-radius: 5px; font-weight: 500; display: flex; align-items: center; gap: 8px; }
        .profile-btn::after { content: '▼'; font-size: 0.7em; transition: transform 0.3s; }
        .user-menu { position: absolute; top: 100%; right: 0; background-color: #1a1a2e; border: 1px solid #2a3c58; border-radius: 8px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5); min-width: 180px; padding: 10px 0; display: none; }
        .user-menu a { display: block; padding: 10px 15px; color: #e4e6eb; font-weight: 400; border-bottom: none; }
        .user-menu a:hover { background-color: #2a3c58; color: white; }
        .user-menu-container:hover .user-menu { display: block; }
        .menu-separator { border-top: 1px solid #2a3c58; margin: 5px 0; }
        .btn-action { background-color: #4CAF50; color: white; padding: 8px 15px; border-radius: 5px; font-weight: 500; transition: background-color 0.3s; }
        .btn-action:hover { background-color: #45a049; color: white; }

        /* ========================================================================= */
        /* 3. HERO SECTION */
        /* ========================================================================= */
        .epic-hero-section { display: grid; grid-template-columns: 2.5fr 1fr; gap: 20px; height: 450px; margin-bottom: 50px; }
        .hero-main-display { position: relative; border-radius: 12px; overflow: hidden; background-color: #1e293b; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .hero-main-image { width: 100%; height: 100%; background-size: cover; background-position: center; transition: transform 0.4s ease-out, opacity 0.5s; }
        .hero-main-display:hover .hero-main-image { transform: scale(1.05); }
        .hero-main-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(90deg, rgba(15, 19, 37, 0.9) 0%, rgba(15, 19, 37, 0.2) 60%, transparent 100%); display: flex; flex-direction: column; justify-content: flex-end; padding: 40px; opacity: 1; transition: opacity 0.5s; color: white; }
        .hero-main-overlay.fade-out { opacity: 0; }
        .hero-main-overlay h1 { font-size: 2.5em; font-weight: 700; text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.8); margin-bottom: 10px; max-width: 80%; }
        .hero-main-overlay p { font-size: 1em; color: #b0b0b0; margin-bottom: 20px; max-width: 70%; }
        .btn-buy { background-color: white; color: #0f0f1a; padding: 12px 25px; font-size: 1em; width: fit-content; border-radius: 5px; font-weight: bold; }
        
        /* Hero Sidebar List */
        .hero-sidebar-list { display: flex; flex-direction: column; gap: 10px; }
        .sidebar-item { background-color: #1e293b; border-radius: 8px; display: flex; align-items: center; padding: 10px; gap: 15px; transition: background-color 0.3s; border-left: 3px solid transparent; cursor: pointer; position: relative; overflow: hidden; }
        .sidebar-item:hover { background-color: #2a3c58; }
        .sidebar-item.active { background-color: #2a3c58; border-left-color: #4CAF50; }
        .sidebar-item img { width: 50px; height: 65px; object-fit: cover; border-radius: 4px; }
        .sidebar-item h4 { color: #e4e6eb; font-size: 1em; font-weight: 500; }
        .sidebar-progress-bar { position: absolute; bottom: 0; left: 0; height: 3px; width: 0%; background-color: #00bcd4; }
        .sidebar-item.active .sidebar-progress-bar { width: 100%; transition: width 5s linear; }

        /* ========================================================================= */
        /* 4. CONTENT ENRICHMENT & DISCOVER SECTION */
        /* ========================================================================= */
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .section-header h3 { color: white; font-size: 1.8em; }
        
        /* Genre List Styling */
        .trending-genres { margin-bottom: 50px; }
        .genre-list a {
            background-color: #1e293b !important; 
            padding: 10px 20px; 
            border-radius: 6px; 
            color: #00bcd4 !important; 
            font-weight: 500; 
            transition: all 0.2s ease-in-out; 
            border: 1px solid transparent;
        }
        .genre-list a:hover {
            background-color: #00bcd4 !important; 
            color: #0f0f1a !important; 
            transform: translateY(-2px); 
            box-shadow: 0 5px 10px rgba(0, 188, 212, 0.3); 
            border-color: #4CAF50; 
        }
        
        /* Discover Grid */
        .discover-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 25px; }
        .game-card { background-color: transparent; border-radius: 8px; overflow: hidden; transition: transform 0.3s; }
        .game-card:hover { transform: translateY(-8px); }
        .game-card-image-wrapper { border-radius: 8px; overflow: hidden; margin-bottom: 15px; box-shadow: 0 8px 15px rgba(0, 0, 0, 0.5); }
        .game-card img { width: 100%; height: 300px; object-fit: cover; display: block; transition: transform 0.3s; }
        .game-card:hover img { transform: scale(1.05); }
        .card-content { padding: 0 5px; }
        .card-content h3 { color: #e4e6eb; margin-bottom: 5px; font-size: 1.1em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: 500; }
        .card-content .genre { font-size: 0.8em; color: #00bcd4; margin-bottom: 5px; font-weight: 500; }
        .card-content .stats {
             font-size: 0.9em; color: #8c98a3; display: flex; justify-content: space-between; margin-top: 5px;
        }
        .empty-state { color: #8c98a3; grid-column: 1 / -1; text-align: center; padding: 40px; }
        .hidden-admin-link { display: none; } 

        /* FOOTER */
        .main-footer {
            background-color: #11111d;
            padding: 30px 40px;
            margin-top: 50px;
            box-shadow: 0 -4px 15px rgba(0,0,0,0.5);
            text-align: center;
        }
        .footer-content {
            max-width: 1600px;
            margin: 0 auto;
            color: #8c98a3;
            font-size: 0.9em;
        }
    </style>
</head>
<body onload="hideLoader()">
    <div id="loadingOverlay" class="loading-overlay"><div class="loader"></div></div>
    
    <nav class="public-navbar">
        <div class="navbar-content">
            <a href="<?php echo BASE_URL; ?>" class="navbar-brand">GAMEHUB</a>
            <div class="nav-right">
                <div class="nav-links">
                    <a href="<?php echo BASE_URL; ?>/index.php" class="active-link">Beranda</a>
                    <a href="<?php echo BASE_URL; ?>/semua_game.php">Semua Game</a>
                    
                    <?php if ($is_logged_in && !$is_admin): ?>
                        <a href="<?php echo BASE_URL; ?>/koleksiku.php">Koleksiku</a>
                    <?php endif; ?>
                </div>
                
                <?php if ($is_logged_in): ?>
                    <div class="user-menu-container">
                        <div class="profile-btn">
                            <?php echo htmlspecialchars($username_display); ?>
                        </div>
                        <div class="user-menu">
                            <?php if ($is_admin): ?>
                                <a href="<?php echo BASE_URL; ?>/admin/index.php">Admin Console</a>
                                <div class="menu-separator"></div>
                            <?php endif; ?>
                            <a href="<?php echo BASE_URL; ?>/logout.php">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/login.php" class="btn-action">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="store-container">
        <?php if (!empty($all_hero_games)): ?>
        <div class="epic-hero-section">
            <?php $first_game = $all_hero_games[0]; ?>
            <a href="detail.php?id=<?php echo $first_game['id']; ?>" class="hero-main-display" id="main-hero-link">
                <div id="main-hero-image" class="hero-main-image" style="background-image: url('<?php echo BASE_URL . '/' . htmlspecialchars($first_game['hero_image_path']); ?>');"></div>
                <div id="main-hero-overlay" class="hero-main-overlay">
                    <div>
                        <h1 id="main-hero-title"><?php echo htmlspecialchars($first_game['nama']); ?></h1>
                        <p id="main-hero-desc"><?php echo htmlspecialchars(substr($first_game['deskripsi'] ?? '', 0, 100)) . '...'; ?></p>
                        <div class="btn btn-buy">Lihat Detail</div>
                    </div>
                </div>
            </a>
            
            <div class="hero-sidebar-list">
                <?php foreach ($all_hero_games as $index => $side_game): ?>
                <div 
                    class="sidebar-item <?php echo ($index == 0) ? 'active' : ''; ?>" 
                    data-index="<?php echo $index; ?>"
                    data-id="<?php echo $side_game['id']; ?>"
                    data-title="<?php echo htmlspecialchars($side_game['nama']); ?>"
                    data-desc="<?php echo htmlspecialchars(substr($side_game['deskripsi'] ?? '', 0, 100)) . '...'; ?>"
                    data-hero-image="<?php echo BASE_URL . '/' . htmlspecialchars($side_game['hero_image_path']); ?>"
                >
                    <img src="<?php echo BASE_URL . '/' . htmlspecialchars($side_game['gambar_path']); ?>" alt="<?php echo htmlspecialchars($side_game['nama']); ?>">
                    <h4><?php echo htmlspecialchars($side_game['nama']); ?></h4>
                    <div class="sidebar-progress-bar"></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
            <div class="epic-hero-section" style="height: 150px; align-items: center; justify-content: center; background-color: #1e293b;">
                <h2 style="grid-column: 1 / -1; color: #b0b0b0;">Silakan tambahkan game dengan Hero Image di Admin Panel!</h2>
            </div>
        <?php endif; ?>
        <div class="trending-genres">
            <div class="section-header">
                <h3>Kategori Populer</h3>
            </div>
            <div class="genre-list" style="display: flex; flex-wrap: wrap; gap: 15px;">
                <?php 
                    $popular_genres_q = mysqli_query($koneksi, "SELECT nama_genre FROM genre LIMIT 5");
                    while($genre = mysqli_fetch_assoc($popular_genres_q)):
                ?>
                    <a href="genre.php?nama=<?php echo urlencode($genre['nama_genre']); ?>">
                        <?php echo htmlspecialchars($genre['nama_genre']); ?>
                    </a>
                <?php endwhile; ?>
                <?php if (mysqli_num_rows($popular_genres_q) == 0): ?>
                    <p style="color: #8c98a3;">Belum ada genre yang ditambahkan.</p>
                <?php endif; ?>
            </div>
        </div>


        <div class="discover-section">
            <div class="section-header">
                <h3>Discover Something New</h3>
            </div>
            <div class="discover-grid">
                <?php if ($discover_result && mysqli_num_rows($discover_result) > 0): ?>
                    <?php while ($game = mysqli_fetch_assoc($discover_result)): ?>
                    <a href="detail.php?id=<?php echo $game['id']; ?>" class="game-card">
                        <div class="game-card-image-wrapper">
                            <img src="<?php echo BASE_URL . '/' . htmlspecialchars($game['gambar_path']); ?>" alt="<?php echo htmlspecialchars($game['nama']); ?>">
                        </div>
                        <div class="card-content">
                            <p class="genre"><?php echo get_game_genres_str($koneksi, $game['id']); ?></p> 
                            <h3><?php echo htmlspecialchars($game['nama']); ?></h3>
                            <div class="stats">
                                <p>Downloads: <?php echo number_format($game['download_count']); ?></p>
                                <p style="color:#00bcd4;">Komen: <?php echo number_format($game['komentar_count']); ?></p>
                            </div>
                        </div>
                    </a>
                    <?php endwhile; ?>
                <?php else: echo '<p class="empty-state">Belum ada game untuk ditampilkan.</p>'; ?>
                    <?php for ($i = 0; $i < 5; $i++): ?>
                    <div class="game-card" style="opacity: 0.5;">
                        <div class="game-card-image-wrapper" style="background-color: #2a3c58;">
                            <img src="#" alt="Placeholder" style="height: 300px; object-fit: cover;">
                        </div>
                        <div class="card-content">
                            <p class="genre">Action, RPG</p>
                            <h3>Coming Soon #<?php echo $i + 1; ?></h3>
                            <div class="stats"><p>Downloads: 0</p><p>Komen: 0</p></div>
                        </div>
                    </div>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <footer class="main-footer">
        <div class="footer-content">
            <p>&copy; <?php echo date('Y'); ?> Game Hub - The Ultimate Game Store.</p>
            <p>Portal Game Offline - Akhiles Salva</p>
        </div>
    </footer>

    <script>
        function hideLoader() {
            const loader = document.getElementById('loadingOverlay');
            if(loader) {
                loader.style.opacity = '0';
                setTimeout(() => { loader.style.display = 'none'; }, 500);
            }
        }

        <?php if (!empty($all_hero_games)): ?>
        // Logika Carousel (diperbaiki agar data-attributes terisi)
        const heroGamesData = <?php echo json_encode($all_hero_games); ?>;
        const mainHeroImage = document.getElementById('main-hero-image');
        const mainHeroOverlay = document.getElementById('main-hero-overlay');
        const mainHeroTitle = document.getElementById('main-hero-title');
        const mainHeroDesc = document.getElementById('main-hero-desc');
        const mainHeroLink = document.getElementById('main-hero-link');
        const sidebarItems = document.querySelectorAll('.sidebar-item');
        let currentIndex = 0;
        let carouselInterval;
        const CAROUSEL_DURATION = 5000;
        function updateHeroDisplay(index) {
            const game = heroGamesData[index];
            const heroImageURL = `<?php echo BASE_URL . '/'; ?>${game.hero_image_path.replace(/\\/g, '/')}`;
            const description = game.deskripsi && game.deskripsi.length > 100 ? game.deskripsi.substring(0, 100) + '...' : game.deskripsi;
            mainHeroImage.style.opacity = '0';
            mainHeroOverlay.classList.add('fade-out');
            setTimeout(() => {
                mainHeroImage.style.backgroundImage = `url('${heroImageURL}')`;
                mainHeroTitle.innerText = game.nama;
                mainHeroDesc.innerText = description;
                mainHeroLink.href = `detail.php?id=${game.id}`;
                mainHeroImage.style.opacity = '1';
                mainHeroOverlay.classList.remove('fade-out');
                updateSidebarNav(index);
            }, 500);
        }
        function updateSidebarNav(activeIndex) {
            sidebarItems.forEach((item, index) => {
                const progressBar = item.querySelector('.sidebar-progress-bar');
                item.classList.remove('active');
                progressBar.style.transition = 'none';
                progressBar.style.width = '0%';
                if(index === activeIndex) {
                    item.classList.add('active');
                    void progressBar.offsetWidth; 
                    progressBar.style.transition = `width ${CAROUSEL_DURATION / 1000}s linear`;
                    progressBar.style.width = '100%';
                }
            });
        }
        function startCarousel() {
            clearInterval(carouselInterval);
            carouselInterval = setInterval(() => {
                currentIndex = (currentIndex + 1) % heroGamesData.length;
                updateHeroDisplay(currentIndex);
            }, CAROUSEL_DURATION);
        }
        sidebarItems.forEach(item => {
            item.addEventListener('click', () => {
                const newIndex = parseInt(item.dataset.index, 10);
                if (newIndex !== currentIndex) {
                    currentIndex = newIndex;
                    updateHeroDisplay(currentIndex);
                    startCarousel();
                }
            });
        });
        window.addEventListener('load', () => {
            if(heroGamesData.length > 1) {
                 updateSidebarNav(0);
                 startCarousel();
            } else if (heroGamesData.length === 1) {
                 updateSidebarNav(0);
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>