<?php
include '../config.php';
include 'cek_login.php'; 

header("Cache-Control: no-cache, no-store, must-revalidate"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 

$nama_admin = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Administrator';

// --- LOGIKA STATISTIK ---
$total_games_q = mysqli_query($koneksi, "SELECT COUNT(id) as total FROM games");
$total_games = mysqli_fetch_assoc($total_games_q)['total'];
$total_downloads_q = mysqli_query($koneksi, "SELECT SUM(download_count) as total FROM games");
$total_downloads = mysqli_fetch_assoc($total_downloads_q)['total'] ?? 0;
$total_visits_q = mysqli_query($koneksi, "SELECT SUM(view_count) as total FROM games");
$total_visits = mysqli_fetch_assoc($total_visits_q)['total'] ?? 0;
$total_comments_q = mysqli_query($koneksi, "SELECT COUNT(id) as total FROM komentar");
$total_comments = mysqli_fetch_assoc($total_comments_q)['total'] ?? 0;

// --- LOGIKA AKTIVITAS TERBARU ---
$recent_games_q = mysqli_query($koneksi, "SELECT nama, created_at FROM games ORDER BY id DESC LIMIT 5");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Game Hub Console</title>
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>/assets/css/admin_style.css"> 
</head>
<body onload="hideLoader()">
    <div id="loadingOverlay" class="loading-overlay"><div class="loader"></div></div>

    <div class="admin-wrapper">
        <div class="sidebar">
            <h2>GAME HUB</h2>
            <nav>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/admin/index.php" class="active">Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/daftar_game.php">Daftar Game</a></li> 
                    <li><a href="<?php echo BASE_URL; ?>/admin/tambah.php">Tambah Game</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/komentar.php">Kelola Komentar</a></li> 
                    <li><a href="<?php echo BASE_URL; ?>/admin/genre.php">Kelola Genre</a></li> 
                    <li><a href="<?php echo BASE_URL; ?>/admin/users.php">Kelola User</a></li>
                </ul>
            </nav>
            <div class="logout-link">
                <p>Welcome, <?php echo htmlspecialchars($nama_admin); ?></p>
                <a href="<?php echo BASE_URL; ?>/logout.php" class="btn btn-danger btn-full">Logout</a> 
            </div>
        </div>

        <div class="main-content">
            <div class="welcome-message" style="margin-bottom: 30px;">
                <p style="font-size: 1.2em; font-weight: 500; color: #00bcd4;">
                    Selamat Datang, **<?php echo htmlspecialchars($nama_admin); ?>**.
                </p>
                <p>Kelola koleksi game Anda di sini.</p>
            </div>

            <h2>CONSOLE MANAGEMENT</h2>
            
            <div class="dashboard-grid">
                
                <div class="main-column">
                    <h3>Statistik Utama</h3>
                    <div class="stats-grid">
                        <div class="stat-card"><h3>Total Game</h3><div class="value"><?php echo number_format($total_games); ?></div></div>
                        <div class="stat-card"><h3>Total Download</h3><div class="value"><?php echo number_format($total_downloads); ?></div></div>
                        <div class="stat-card"><h3>Total Kunjungan</h3><div class="value"><?php echo number_format($total_visits); ?></div></div>
                        
                        <div class="stat-card stat-card-comment"> 
                            <h3 style="color: #0f0f1a;">Total Komentar</h3> 
                            <div class="value" style="color: #0f0f1a;"><?php echo number_format($total_comments); ?></div>
                        </div>
                    </div>
                </div> <div class="side-column">
                    <div class="recent-activity-card">
                        <h3>Aktivitas Terbaru</h3>
                        <ul class="activity-list">
                            <?php 
                            if(mysqli_num_rows($recent_games_q) > 0) {
                                while($recent = mysqli_fetch_assoc($recent_games_q)) {
                                    $date = date_create($recent['created_at']);
                                    $formatted_date = date_format($date, 'd M Y');
                                    echo '<li class="activity-item"><span>' . htmlspecialchars($recent['nama']) . '</span><span class="date">' . $formatted_date . '</span></li>';
                                }
                            } else {
                                echo '<li class="activity-item"><span>Belum ada aktivitas.</span></li>';
                            }
                            ?>
                        </ul>
                    </div>
                    
                    <div class="recent-activity-card card-kelola-komentar">
                        <h3>Kelola Komentar</h3>
                        <p style="margin-bottom: 10px; color: #b0b0b0;">Ada **<?php echo number_format($total_comments); ?>** komentar dari user yang menunggu balasan.</p>
                        <a href="<?php echo BASE_URL; ?>/admin/komentar.php" class="btn btn-primary btn-full" style="background-color: #4CAF50;">Lihat Semua Komentar</a>
                    </div>
                </div> </div> </div> </div> <script>
        function hideLoader() {
            const loader = document.getElementById('loadingOverlay');
            if (loader) {
                loader.style.opacity = '0';
                setTimeout(() => { loader.style.display = 'none'; }, 500); 
            }
        }
    </script>
</body>
</html>