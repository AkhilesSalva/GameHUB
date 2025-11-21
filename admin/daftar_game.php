<?php
include '../config.php';
include 'cek_login.php'; 

// Cek Role Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

// Fungsi untuk mendapatkan jumlah komentar per game
function get_comment_count($koneksi, $game_id) {
    $count_q = mysqli_query($koneksi, "SELECT COUNT(id) as total_komentar FROM komentar WHERE game_id = $game_id");
    return mysqli_fetch_assoc($count_q)['total_komentar'];
}

// Fungsi untuk mendapatkan genre game
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
<html>
<head>
    <title>Admin - Daftar Game</title>
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>/assets/css/admin_style.css"> 
</head>
<body onload="hideLoader()">
    <div id="loadingOverlay" class="loading-overlay"><div class="loader"></div></div>

    <div class="admin-wrapper">
        <div class="sidebar">
            <h2>GAME HUB</h2>
            <nav>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/admin/index.php">Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/daftar_game.php" class="active">Daftar Game</a></li>
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
            <h2>KELOLA DAFTAR GAME</h2>
            
            <div class="welcome-message">
                <p>Kelola semua game yang ada di sistem, dari mengedit detail hingga menghapus total.</p>
                <a href="<?php echo BASE_URL; ?>/admin/tambah.php" class="btn btn-primary">➕ Tambah Game Baru</a>
            </div>

            <?php if (isset($_GET['pesan'])): ?>
                <?php if ($_GET['pesan'] == 'sukses_tambah' || $_GET['pesan'] == 'sukses_edit' || $_GET['pesan'] == 'sukses_hapus'): ?>
                    <div class="alert alert-success">Operasi berhasil!</div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="table-container">
                <table>
                    <thead>
                        <tr><th>ID</th><th>Nama Game</th><th>Genre</th><th>Downloads</th><th>Komentar</th><th>Aksi</th></tr>
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
                                <td><?php echo $data['id']; ?></td>
                                <td><?php echo htmlspecialchars($data['nama']); ?></td>
                                <td><?php echo empty($genres_list) ? '-' : htmlspecialchars($genres_list); ?></td>
                                <td style="color:#4CAF50; font-weight:bold;"><?php echo number_format($data['download_count']); ?></td>
                                
                                <td><a href="<?php echo BASE_URL; ?>/admin/komentar.php" style="color:#00bcd4; font-weight:bold;"><?php echo number_format($komentar_count); ?></a></td>
                                
                                <td class="aksi-group">
                                    <a href="edit.php?id=<?php echo $data['id']; ?>" class="btn btn-warning">Edit</a> 
                                    <a href="aksi_crud.php?aksi=hapus&id=<?php echo $data['id']; ?>" onclick="return confirm('Yakin?');" class="btn btn-danger">Hapus</a>
                                </td>
                            </tr>
                            <?php }
                        } else { echo '<tr><td colspan="6" style="text-align:center;">Belum ada data game.</td></tr>'; }
                        ?>
                    </tbody>
                </table>
            </div>

        </div> 
    </div> 
    <script>
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