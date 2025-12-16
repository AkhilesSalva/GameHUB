<?php
include '../config.php';
include 'cek_login.php'; 

header("Cache-Control: no-cache, no-store, must-revalidate"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 

$nama_admin = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Administrator';

// Statistik
$total_games_q = mysqli_query($koneksi, "SELECT COUNT(id) as total FROM games");
$total_games = mysqli_fetch_assoc($total_games_q)['total'];

$total_downloads_q = mysqli_query($koneksi, "SELECT SUM(download_count) as total FROM games");
$total_downloads = mysqli_fetch_assoc($total_downloads_q)['total'] ?? 0;

$total_visits_q = mysqli_query($koneksi, "SELECT SUM(view_count) as total FROM games");
$total_visits = mysqli_fetch_assoc($total_visits_q)['total'] ?? 0;

$total_comments_q = mysqli_query($koneksi, "SELECT COUNT(id) as total FROM komentar");
$total_comments = mysqli_fetch_assoc($total_comments_q)['total'] ?? 0;

// Aktivitas Terbaru
$recent_games_q = mysqli_query($koneksi, "SELECT nama, created_at FROM games ORDER BY id DESC LIMIT 5");

// Top Games
$top_games_q = mysqli_query($koneksi, "SELECT nama, download_count FROM games ORDER BY download_count DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Game Hub Console</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin_style.css">
</head>
<body>
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loader"></div>
    </div>

    <div class="admin-wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>GAME HUB</h2>
            <nav>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/admin/index.php" class="active">📊 Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/daftar_game.php">🎮 Daftar Game</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/tambah.php">➕ Tambah Game</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/komentar.php">💬 Komentar</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/genre.php">🏷️ Genre</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/users.php">👤 Users</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/requests.php">🎯 Requests</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/reports.php">📋 Reports</a></li>
                </ul>
            </nav>
            <div class="logout-link">
                <p>👋 <?php echo htmlspecialchars($nama_admin); ?></p>
                <a href="<?php echo BASE_URL; ?>/logout.php" class="btn btn-danger btn-full">🚪 Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h2>DASHBOARD</h2>
            
            <div class="welcome-message">
                <div>
                    <p style="font-size: 1.2em; color: var(--primary); font-weight: 600;">
                        Selamat datang, <?php echo htmlspecialchars($nama_admin); ?>! 👋
                    </p>
                    <p>Kelola koleksi game dari panel admin ini.</p>
                </div>
                <a href="<?php echo BASE_URL; ?>" class="btn btn-secondary" target="_blank">🌐 Lihat Website</a>
            </div>

            <div class="dashboard-grid">
                <div class="main-column">
                    <h3>📈 Statistik Utama</h3>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <h3>🎮 Total Game</h3>
                            <div class="value"><?php echo number_format($total_games); ?></div>
                        </div>
                        <div class="stat-card">
                            <h3>📥 Total Download</h3>
                            <div class="value"><?php echo number_format($total_downloads); ?></div>
                        </div>
                        <div class="stat-card">
                            <h3>👁️ Total Views</h3>
                            <div class="value"><?php echo number_format($total_visits); ?></div>
                        </div>
                        <div class="stat-card stat-card-comment">
                            <h3>💬 Total Komentar</h3>
                            <div class="value"><?php echo number_format($total_comments); ?></div>
                        </div>
                    </div>
                    
                    <!-- Top Games -->
                    <div class="recent-activity-card">
                        <h3>🔥 Game Terpopuler</h3>
                        <ul class="activity-list">
                            <?php 
                            if(mysqli_num_rows($top_games_q) > 0) {
                                while($top = mysqli_fetch_assoc($top_games_q)) {
                                    echo '<li class="activity-item">
                                            <span>' . htmlspecialchars($top['nama']) . '</span>
                                            <span class="date" style="color: var(--primary);">📥 ' . number_format($top['download_count']) . '</span>
                                          </li>';
                                }
                            } else {
                                echo '<li class="activity-item"><span>Belum ada data.</span></li>';
                            }
                            ?>
                        </ul>
                    </div>
                </div>
                
                <div class="side-column">
                    <div class="recent-activity-card">
                        <h3>🕐 Game Terbaru</h3>
                        <ul class="activity-list">
                            <?php 
                            if(mysqli_num_rows($recent_games_q) > 0) {
                                while($recent = mysqli_fetch_assoc($recent_games_q)) {
                                    $date = date_create($recent['created_at']);
                                    $formatted_date = date_format($date, 'd M Y');
                                    echo '<li class="activity-item">
                                            <span>' . htmlspecialchars($recent['nama']) . '</span>
                                            <span class="date">' . $formatted_date . '</span>
                                          </li>';
                                }
                            } else {
                                echo '<li class="activity-item"><span>Belum ada aktivitas.</span></li>';
                            }
                            ?>
                        </ul>
                    </div>
                    
                    <div class="recent-activity-card">
                        <h3>💬 Kelola Komentar</h3>
                        <p style="margin-bottom: var(--space-md); color: var(--text-secondary);">
                            Ada <strong style="color: var(--accent);"><?php echo number_format($total_comments); ?></strong> komentar dari user.
                        </p>
                        <a href="<?php echo BASE_URL; ?>/admin/komentar.php" class="btn btn-primary btn-full">Lihat Semua Komentar</a>
                    </div>
                    
                    <div class="recent-activity-card">
                        <h3>⚡ Quick Actions</h3>
                        <div style="display: flex; flex-direction: column; gap: var(--space-sm);">
                            <a href="<?php echo BASE_URL; ?>/admin/tambah.php" class="btn btn-primary btn-full">➕ Tambah Game Baru</a>
                            <a href="<?php echo BASE_URL; ?>/admin/genre.php" class="btn btn-secondary btn-full">🏷️ Kelola Genre</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        window.addEventListener('load', function() {
            const loader = document.getElementById('loadingOverlay');
            if (loader) {
                loader.style.opacity = '0';
                setTimeout(() => loader.style.display = 'none', 500);
            }
        });
    </script>
</body>
</html>