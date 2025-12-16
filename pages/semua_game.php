<?php
session_start();
include '../config.php';

// Fungsi untuk mendapatkan genre game
function get_game_genres_str($koneksi, $game_id) {
    $genre_q = mysqli_query($koneksi, "SELECT g.nama_genre FROM genre g JOIN game_genre gg ON g.id = gg.genre_id WHERE gg.game_id = $game_id LIMIT 2");
    $genres = [];
    while ($row = mysqli_fetch_assoc($genre_q)) {
        $genres[] = $row['nama_genre'];
    }
    return implode(', ', $genres);
}

// Ambil semua genre untuk filter
$all_genres_query = mysqli_query($koneksi, "SELECT * FROM genre ORDER BY nama_genre ASC");
$all_genres = [];
while ($row = mysqli_fetch_assoc($all_genres_query)) {
    $all_genres[] = $row;
}

// Handle Search & Filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, trim($_GET['search'])) : '';
$selected_genres = isset($_GET['genre']) ? $_GET['genre'] : [];
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build query
$where_conditions = [];
$join_clause = "";

if (!empty($search)) {
    $where_conditions[] = "g.nama LIKE '%$search%'";
}

if (!empty($selected_genres)) {
    $genre_ids = array_map('intval', $selected_genres);
    $genre_ids_str = implode(',', $genre_ids);
    $join_clause = "INNER JOIN game_genre gg ON g.id = gg.game_id";
    $where_conditions[] = "gg.genre_id IN ($genre_ids_str)";
}

$where_sql = !empty($where_conditions) ? "WHERE " . implode(' AND ', $where_conditions) : "";

// Sort
switch ($sort) {
    case 'popular':
        $order_sql = "ORDER BY g.download_count DESC";
        break;
    case 'views':
        $order_sql = "ORDER BY g.view_count DESC";
        break;
    case 'name':
        $order_sql = "ORDER BY g.nama ASC";
        break;
    case 'newest':
    default:
        $order_sql = "ORDER BY g.created_at DESC";
        break;
}

$all_games_query = "SELECT DISTINCT g.id, g.nama, g.gambar_path, g.download_count, g.view_count, g.created_at,
                    (SELECT COUNT(*) FROM komentar k WHERE k.game_id = g.id) as komentar_count
                    FROM games g 
                    $join_clause
                    $where_sql
                    $order_sql";
$all_games_result = mysqli_query($koneksi, $all_games_query);

$is_logged_in = isset($_SESSION['user_id']);
$is_admin = $is_logged_in && ($_SESSION['role'] === 'admin');
$username_display = $is_logged_in ? ($_SESSION['username'] ?? 'User') : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Game - Game Hub</title>
    <meta name="description" content="Jelajahi koleksi game offline terlengkap. Filter berdasarkan genre dan urutkan sesuai preferensi.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <style>
        .page-header {
            padding: var(--space-xl) 0;
            border-bottom: 1px solid var(--border-dark);
            margin-bottom: var(--space-xl);
        }
        .page-header h1 {
            font-size: 2.5em;
            margin-bottom: var(--space-sm);
        }
        .results-info { color: var(--text-muted); }
        .sort-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-xl);
            flex-wrap: wrap;
            gap: var(--space-md);
        }
        .sort-options { display: flex; gap: var(--space-sm); }
        .sort-btn {
            padding: 8px 16px;
            background: var(--bg-elevated);
            border: 1px solid var(--border-dark);
            border-radius: var(--radius-full);
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9em;
            transition: all var(--transition-normal);
        }
        .sort-btn:hover, .sort-btn.active {
            background: var(--accent);
            color: var(--bg-darkest);
            border-color: var(--accent);
        }
        .filter-sidebar { position: sticky; top: 100px; }
        .clear-filters {
            display: block;
            text-align: center;
            padding: var(--space-sm);
            color: var(--danger);
            font-size: 0.9em;
            margin-top: var(--space-md);
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
                    <a href="<?php echo BASE_URL; ?>/pages/semua_game.php" class="active">Semua Game</a>
                </div>
            </div>
            <div class="nav-right">
                <form class="search-box" action="<?php echo BASE_URL; ?>/pages/semua_game.php" method="GET">
                    <input type="text" name="search" placeholder="Cari game..." value="<?php echo htmlspecialchars($search); ?>" autocomplete="off">
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
    
    <div class="container">
        <div class="page-header">
            <h1>&#127918; Semua Game</h1>
            <p class="results-info">
                <?php 
                $count = mysqli_num_rows($all_games_result);
                echo "Menampilkan $count game";
                if (!empty($search)) echo " untuk \"" . htmlspecialchars($search) . "\"";
                ?>
            </p>
        </div>
    </div>
    
    <div class="page-with-sidebar">
        <!-- Filter Sidebar -->
        <aside class="filter-sidebar">
            <form method="GET" action="semua_game.php" id="filterForm">
                <?php if (!empty($search)): ?>
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                <?php endif; ?>
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>" id="sortInput">
                
                <div class="filter-card">
                    <h4>&#127991;&#65039; Genre</h4>
                    <div class="filter-options">
                        <?php foreach ($all_genres as $genre): ?>
                            <label class="filter-option">
                                <input type="checkbox" name="genre[]" value="<?php echo $genre['id']; ?>" 
                                    <?php echo in_array($genre['id'], $selected_genres) ? 'checked' : ''; ?>
                                    onchange="document.getElementById('filterForm').submit();">
                                <span><?php echo htmlspecialchars($genre['nama_genre']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (!empty($selected_genres) || !empty($search)): ?>
                        <a href="semua_game.php" class="clear-filters">&#10005; Reset Filter</a>
                    <?php endif; ?>
                </div>
            </form>
        </aside>
        
        <!-- Main Content -->
        <main>
            <!-- Sort Bar -->
            <div class="sort-bar">
                <div class="sort-options">
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'newest'])); ?>" 
                       class="sort-btn <?php echo $sort == 'newest' ? 'active' : ''; ?>">&#128336; Terbaru</a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'popular'])); ?>" 
                       class="sort-btn <?php echo $sort == 'popular' ? 'active' : ''; ?>">&#128293; Populer</a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'views'])); ?>" 
                       class="sort-btn <?php echo $sort == 'views' ? 'active' : ''; ?>">&#128065;&#65039; Terbanyak Dilihat</a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['sort' => 'name'])); ?>" 
                       class="sort-btn <?php echo $sort == 'name' ? 'active' : ''; ?>">&#128292; Nama</a>
                </div>
            </div>
            
            <!-- Games Grid -->
            <div class="games-grid">
                <?php if ($all_games_result && mysqli_num_rows($all_games_result) > 0): ?>
                    <?php while ($game = mysqli_fetch_assoc($all_games_result)): ?>
                    <a href="<?php echo BASE_URL; ?>/pages/detail.php?id=<?php echo $game['id']; ?>" class="game-card">
                        <div class="game-card-image">
                            <img src="<?php echo BASE_URL . '/' . htmlspecialchars($game['gambar_path']); ?>" alt="<?php echo htmlspecialchars($game['nama']); ?>">
                        </div>
                        <div class="game-card-content">
                            <span class="game-card-genre"><?php echo get_game_genres_str($koneksi, $game['id']); ?></span>
                            <h3 class="game-card-title"><?php echo htmlspecialchars($game['nama']); ?></h3>
                            <div class="game-card-stats">
                                <span>&#128229; <?php echo number_format($game['download_count']); ?></span>
                                <span>&#128172; <?php echo number_format($game['komentar_count']); ?></span>
                            </div>
                        </div>
                    </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <h3 style="margin-bottom: var(--space-md);">&#127918; Tidak ada game ditemukan</h3>
                        <p>Coba ubah filter atau kata kunci pencarian.</p>
                        <a href="semua_game.php" class="btn btn-secondary" style="margin-top: var(--space-lg);">Lihat Semua Game</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <span class="footer-brand" id="secretLogin" style="cursor: default;">&#127918; GAMEHUB</span>
            <div class="footer-links">
                <a href="<?php echo BASE_URL; ?>">Beranda</a>
                <a href="<?php echo BASE_URL; ?>/pages/semua_game.php">Semua Game</a>
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
            navbar.classList.toggle('scrolled', window.scrollY > 50);
        });
        
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