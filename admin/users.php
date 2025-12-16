<?php
include '../config.php';
include 'cek_login.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$nama_admin = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Administrator';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin_style.css">
    <style>
        .role-badge {
            padding: 4px 10px;
            border-radius: var(--radius-full);
            font-size: 0.8em;
            font-weight: 600;
            text-transform: uppercase;
        }
        .role-badge.admin {
            background: var(--primary);
            color: var(--bg-darkest);
        }
        .role-badge.user {
            background: var(--bg-elevated);
            color: var(--text-muted);
            border: 1px solid var(--border-dark);
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
                    <li><a href="<?php echo BASE_URL; ?>/admin/genre.php">🏷️ Genre</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/users.php" class="active">👥 Users</a></li>
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
            <h2>KELOLA USER</h2>
            
            <div class="welcome-message">
                <p>Kelola semua akun pengguna sistem (Admin & User).</p>
                <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-primary" target="_blank">➕ Tambah Admin Baru</a>
            </div>

            <?php if (isset($_GET['pesan'])): ?>
                <div class="alert alert-success">
                    <?php 
                    if ($_GET['pesan'] == 'user_edit') echo "✓ Data user berhasil diupdate!";
                    if ($_GET['pesan'] == 'user_hapus') echo "✓ Akun berhasil dihapus!";
                    ?>
                </div>
            <?php endif; ?>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT id, username, nama_lengkap, role FROM users ORDER BY id ASC";
                        $result = mysqli_query($koneksi, $query);
                        if (mysqli_num_rows($result) > 0) {
                            while ($data = mysqli_fetch_assoc($result)) { 
                                $is_current_user = ($data['username'] === $_SESSION['username']);
                                $role_class = $data['role'] === 'admin' ? 'admin' : 'user';
                        ?>
                        <tr>
                            <td><strong>#<?php echo $data['id']; ?></strong></td>
                            <td style="color: var(--text-primary);"><?php echo htmlspecialchars($data['username']); ?></td>
                            <td><?php echo htmlspecialchars($data['nama_lengkap']); ?></td>
                            <td><span class="role-badge <?php echo $role_class; ?>"><?php echo $data['role']; ?></span></td>
                            <td class="aksi-group">
                                <a href="<?php echo BASE_URL; ?>/admin/edit_user.php?id=<?php echo $data['id']; ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                                <?php if (!$is_current_user): ?>
                                    <a href="<?php echo BASE_URL; ?>/admin/aksi_crud.php?aksi=hapus_user&id=<?php echo $data['id']; ?>" onclick="return confirm('Yakin ingin menghapus akun ini?');" class="btn btn-danger btn-sm">🗑️ Hapus</a>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-sm" disabled style="opacity: 0.5;">Anda</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php }
                        } else { ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: var(--space-xl); color: var(--text-muted);">
                                👤 Tidak ada akun terdaftar.
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