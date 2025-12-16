<?php
include '../config.php';
include 'cek_login.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$nama_admin = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Administrator';

$genres_query = mysqli_query($koneksi, "SELECT * FROM genre ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Genre - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin_style.css">
    <style>
        .genre-grid-layout {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: var(--space-xl);
        }
        @media (max-width: 900px) {
            .genre-grid-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
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
                    <li><a href="<?php echo BASE_URL; ?>/admin/daftar_game.php">🎮 Daftar Game</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/tambah.php">➕ Tambah Game</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/komentar.php">💬 Komentar</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/genre.php" class="active">🏷️ Genre</a></li>
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
            <h2>KELOLA GENRE</h2>

            <?php if (isset($_GET['pesan'])): ?>
                <?php if ($_GET['pesan'] == 'sukses_tambah'): ?>
                    <div class="alert alert-success">✓ Genre berhasil ditambahkan!</div>
                <?php elseif ($_GET['pesan'] == 'sukses_edit'): ?>
                    <div class="alert alert-success">✓ Genre berhasil diupdate!</div>
                <?php elseif ($_GET['pesan'] == 'sukses_hapus'): ?>
                    <div class="alert alert-success">✓ Genre berhasil dihapus!</div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="genre-grid-layout">
                <!-- Form Section -->
                <div>
                    <?php 
                        $edit_genre = null;
                        if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
                            $id = (int)$_GET['id'];
                            $edit_q = mysqli_query($koneksi, "SELECT * FROM genre WHERE id=$id");
                            $edit_genre = mysqli_fetch_assoc($edit_q);
                        }
                    ?>
                    <div class="form-container">
                        <h3 style="margin-bottom: var(--space-lg); color: var(--accent);">
                            <?php echo $edit_genre ? '✏️ Edit Genre' : '➕ Tambah Genre Baru'; ?>
                        </h3>
                        <form action="aksi_genre.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo $edit_genre['id'] ?? ''; ?>">
                            <div class="input-group">
                                <label>Nama Genre</label>
                                <input type="text" name="nama_genre" value="<?php echo $edit_genre['nama_genre'] ?? ''; ?>" placeholder="Contoh: Action, RPG, Adventure..." required>
                            </div>
                            <button type="submit" name="<?php echo $edit_genre ? 'edit_genre' : 'tambah_genre'; ?>" class="btn btn-primary btn-full">
                                <?php echo $edit_genre ? '💾 UPDATE GENRE' : '➕ SIMPAN GENRE'; ?>
                            </button>
                            <?php if ($edit_genre): ?>
                                <a href="genre.php" class="btn btn-secondary btn-full" style="margin-top: var(--space-sm);">✕ Batal Edit</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Table Section -->
                <div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Genre</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($genres_query) > 0): ?>
                                    <?php while ($genre = mysqli_fetch_assoc($genres_query)): ?>
                                    <tr>
                                        <td><strong>#<?php echo $genre['id']; ?></strong></td>
                                        <td style="color: var(--text-primary);"><?php echo htmlspecialchars($genre['nama_genre']); ?></td>
                                        <td class="aksi-group">
                                            <a href="genre.php?action=edit&id=<?php echo $genre['id']; ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                                            <a href="aksi_genre.php?aksi=hapus&id=<?php echo $genre['id']; ?>" onclick="return confirm('Menghapus genre akan menghapus relasi di semua game. Yakin?');" class="btn btn-danger btn-sm">🗑️ Hapus</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; padding: var(--space-xl); color: var(--text-muted);">
                                            🏷️ Belum ada genre. Tambah genre pertama!
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
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