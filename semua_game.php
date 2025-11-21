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

// Ambil semua game
$all_games_query = "SELECT g.id, g.nama, g.gambar_path, g.download_count, COUNT(k.id) as komentar_count 
                    FROM games g 
                    LEFT JOIN komentar k ON g.id = k.game_id
                    GROUP BY g.id 
                    ORDER BY g.nama ASC"; 
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* ========================================================================= */
        /* 1. RESET & UTILITIES */
        /* ========================================================================= */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { line-height: 1.6; background-color: #0f0f1a; color: #e4e6eb; }
        a { text-decoration: none; color: #00bcd4; transition: color 0.3s; }
        a:hover { color: #4CAF50; }
        .store-container { max-width: 1600px; margin: 40px auto; padding: 0 40px; }
        
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
        
        /* Dropdown Admin/User */
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
        /* 3. PAGE CONTENT & CARDS */
        /* ========================================================================= */
        .page-header { margin-bottom: 25px; }
        .page-header h1 { color: white; font-size: 2.2em; }
        
        .game-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 25px; }
        
        /* Efek Hover Card Penuh */
        .game-card { background-color: transparent; border-radius: 8px; overflow: hidden; transition: transform 0.3s, box-shadow 0.3s; }
        .game-card:hover { transform: translateY(-8px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5); }
        
        /* Efek Zoom Gambar */
        .game-card-image-wrapper { border-radius: 8px; overflow: hidden; margin-bottom: 15px; box-shadow: 0 8px 15px rgba(0, 0, 0, 0.5); }
        .game-card img { width: 100%; height: 300px; object-fit: cover; display: block; transition: transform 0.3s; }
        .game-card:hover img { transform: scale(1.05); }
        
        /* Card Content Styling */
        .card-content { padding: 0 5px; }
        .card-content h3 { color: #e4e6eb; margin-bottom: 5px; font-size: 1.1em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: 500; }
        .card-content .genre { font-size: 0.8em; color: #00bcd4; margin-bottom: 5px; font-weight: 500; }
        .card-content .stats {
             font-size: 0.9em; color: #8c98a3; display: flex; justify-content: space-between; margin-top: 5px;
        }
        .empty-state { color: #8c98a3; grid-column: 1 / -1; text-align: center; padding: 40px; font-size: 1.2em; }
        
        /* FOOTER */
        .main-footer { background-color: #11111d; padding: 30px 40px; margin-top: 50px; box-shadow: 0 -4px 15px rgba(0,0,0,0.5); text-align: center; }
        .footer-content { max-width: 1600px; margin: 0 auto; color: #8c98a3; font-size: 0.9em; }
    </style>
</head>
<body onload="hideLoader()">
    <nav class="public-navbar">
        <div class="navbar-content">
            <a href="<?php echo BASE_URL; ?>" class="navbar-brand">GAMEHUB</a>
            <div class="nav-right">
                <div class="nav-links">
                    <a href="<?php echo BASE_URL; ?>/index.php">Beranda</a>
                    <a href="<?php echo BASE_URL; ?>/semua_game.php" class="active-link">Semua Game</a>
                    
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
        <div class="page-header">
            <h1>Semua Koleksi Game</h1>
        </div>

        <div class="game-grid">
            <?php if ($all_games_result && mysqli_num_rows($all_games_result) > 0): ?>
                <?php while ($game = mysqli_fetch_assoc($all_games_result)): ?>
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
            <?php else: ?>
                <p class="empty-state">Belum ada game untuk ditampilkan.</p>
            <?php endif; ?>
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
            // Fungsi ini disisipkan jika ada loading overlay di halaman ini.
        }
    </script>
</body>
</html>