<?php
include '../config.php';
include 'cek_login.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

function get_comment_count($koneksi, $game_id) {
    $count_q = mysqli_query($koneksi, "SELECT COUNT(id) as total_komentar FROM komentar WHERE game_id = $game_id");
    return mysqli_fetch_assoc($count_q)['total_komentar'];
}

function get_game_genres($koneksi, $game_id) {
    $genre_q = mysqli_query($koneksi, "SELECT g.nama_genre FROM genre g JOIN game_genre gg ON g.id = gg.genre_id WHERE gg.game_id = $game_id LIMIT 3");
    $genres = [];
    while ($row = mysqli_fetch_assoc($genre_q)) {
        $genres[] = $row['nama_genre'];
    }
    return implode(', ', $genres);
}

$nama_admin = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Administrator';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Game - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin_style.css">
</head>
<body>
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loader"></div>
    </div>

    <div class="admin-wrapper">
        <div class="sidebar">
            <h2>GAME HUB</h2>
            <nav>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/admin/index.php">📊 Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/daftar_game.php" class="active">🎮 Daftar Game</a></li>
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

        <div class="main-content">
            <h2>DAFTAR GAME</h2>
            
            <div class="welcome-message">
                <p>Kelola semua game yang ada di sistem.</p>
                <a href="<?php echo BASE_URL; ?>/admin/tambah.php" class="btn btn-primary">➕ Tambah Game Baru</a>
            </div>

            <?php if (isset($_GET['pesan'])): ?>
                <?php if ($_GET['pesan'] == 'sukses_tambah'): ?>
                    <div class="alert alert-success">✓ Game berhasil ditambahkan!</div>
                <?php elseif ($_GET['pesan'] == 'sukses_edit'): ?>
                    <div class="alert alert-success">✓ Game berhasil diupdate!</div>
                <?php elseif ($_GET['pesan'] == 'sukses_hapus'): ?>
                    <div class="alert alert-success">✓ Game berhasil dihapus!</div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Game</th>
                            <th>Genre</th>
                            <th>Downloads</th>
                            <th>Komentar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT id, nama, link_type, download_count FROM games ORDER BY id DESC";
                        $result = mysqli_query($koneksi, $query);
                        if (mysqli_num_rows($result) > 0) {
                            while ($data = mysqli_fetch_assoc($result)) { 
                                $komentar_count = get_comment_count($koneksi, $data['id']);
                                $genres_list = get_game_genres($koneksi, $data['id']);
                        ?>
                        <tr>
                            <td><strong>#<?php echo $data['id']; ?></strong></td>
                            <td style="color: var(--text-primary);"><?php echo htmlspecialchars($data['nama']); ?></td>
                            <td><?php echo empty($genres_list) ? '<span style="color: var(--text-muted);">-</span>' : htmlspecialchars($genres_list); ?></td>
                            <td><span style="color: var(--primary); font-weight: 600;">📥 <?php echo number_format($data['download_count']); ?></span></td>
                            <td><a href="<?php echo BASE_URL; ?>/admin/komentar.php" style="color: var(--accent); font-weight: 600;">💬 <?php echo number_format($komentar_count); ?></a></td>
                            <td class="aksi-group">
                                <a href="edit.php?id=<?php echo $data['id']; ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                                <a href="aksi_crud.php?aksi=hapus&id=<?php echo $data['id']; ?>" onclick="return confirm('Yakin ingin menghapus game ini?');" class="btn btn-danger btn-sm">🗑️ Hapus</a>
                            </td>
                        </tr>
                        <?php }
                        } else { ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: var(--space-xl); color: var(--text-muted);">
                                🎮 Belum ada data game. <a href="<?php echo BASE_URL; ?>/admin/tambah.php">Tambah game pertama!</a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
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