<?php
include '../config.php';
include 'cek_login.php'; 

// Cek Role Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$nama_admin = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Administrator';

// Ambil semua genre dari database
$genres_query = mysqli_query($koneksi, "SELECT * FROM genre ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Kelola Genre</title>
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
                    <li><a href="<?php echo BASE_URL; ?>/admin/daftar_game.php">Daftar Game</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/tambah.php">Tambah Game</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/komentar.php">Kelola Komentar</a></li> 
                    <li><a href="<?php echo BASE_URL; ?>/admin/genre.php" class="active">Kelola Genre</a></li> 
                    <li><a href="<?php echo BASE_URL; ?>/admin/users.php">Kelola User</a></li>
                </ul>
            </nav>
            <div class="logout-link">
                <p>Welcome, <?php echo htmlspecialchars($nama_admin); ?></p>
                <a href="<?php echo BASE_URL; ?>/logout.php" class="btn btn-danger btn-full">Logout</a>
            </div>
        </div>

        <div class="main-content">
            <h2>KELOLA KATEGORI (GENRE)</h2>

            <?php if (isset($_GET['pesan'])): ?>
                <?php if ($_GET['pesan'] == 'sukses_tambah'): ?>
                    <div class="alert alert-success">Genre berhasil ditambahkan!</div>
                <?php elseif ($_GET['pesan'] == 'sukses_edit'): ?>
                    <div class="alert alert-success">Genre berhasil diupdate!</div>
                <?php elseif ($_GET['pesan'] == 'sukses_hapus'): ?>
                    <div class="alert alert-success">Genre berhasil dihapus!</div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="dashboard-grid" style="grid-template-columns: 1fr 2fr; gap: 30px;">
                
                <div class="main-column" style="gap: 0;">
                    <h3>Tambah/Edit Genre</h3>
                    <div class="form-container">
                        <form action="aksi_genre.php" method="POST">
                            <?php 
                                $edit_genre = null;
                                if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
                                    $id = (int)$_GET['id'];
                                    $edit_q = mysqli_query($koneksi, "SELECT * FROM genre WHERE id=$id");
                                    $edit_genre = mysqli_fetch_assoc($edit_q);
                                }
                            ?>
                            <input type="hidden" name="id" value="<?php echo $edit_genre['id'] ?? ''; ?>">
                            <div class="input-group">
                                <label>Nama Genre:</label>
                                <input type="text" name="nama_genre" value="<?php echo $edit_genre['nama_genre'] ?? ''; ?>" required>
                            </div>
                            <button type="submit" name="<?php echo $edit_genre ? 'edit_genre' : 'tambah_genre'; ?>" 
                                    class="btn btn-primary btn-full">
                                <?php echo $edit_genre ? 'UPDATE GENRE' : 'SIMPAN GENRE'; ?>
                            </button>
                            <?php if ($edit_genre): ?>
                                <a href="genre.php" class="btn btn-secondary btn-full" style="margin-top: 10px;">Batal Edit</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <div class="side-column" style="gap: 0;">
                    <h3>Daftar Genre Aktif</h3>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr><th>ID</th><th>Nama Genre</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($genres_query) > 0): ?>
                                    <?php while ($genre = mysqli_fetch_assoc($genres_query)): ?>
                                    <tr>
                                        <td><?php echo $genre['id']; ?></td>
                                        <td><?php echo htmlspecialchars($genre['nama_genre']); ?></td>
                                        <td class="aksi-group">
                                            <a href="genre.php?action=edit&id=<?php echo $genre['id']; ?>" class="btn btn-warning">Edit</a> 
                                            <a href="aksi_genre.php?aksi=hapus&id=<?php echo $genre['id']; ?>" onclick="return confirm('Menghapus genre akan menghapus relasi di semua game. Yakin?');" class="btn btn-danger">Hapus</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" style="text-align:center;">Belum ada genre yang ditambahkan.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

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