<?php
include '../config.php';
// Cek Login sudah termasuk cek session, tapi kita tambahkan cek role admin
include 'cek_login.php'; 

// PENTING: Cek Role Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$nama_admin = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Administrator';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Kelola User</title>
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
                    <li><a href="<?php echo BASE_URL; ?>/admin/genre.php">Kelola Genre</a></li> 
                    <li><a href="<?php echo BASE_URL; ?>/admin/users.php" class="active">Kelola User</a></li>
                </ul>
            </nav>
            <div class="logout-link">
                <p>Welcome, <?php echo htmlspecialchars($nama_admin); ?></p>
                <a href="<?php echo BASE_URL; ?>/logout.php" class="btn btn-danger btn-full">Logout</a>
            </div>
        </div>

        <div class="main-content">
            <h2>KELOLA AKUN SISTEM</h2>
            <div class="welcome-message">
                <p>Di sini Anda dapat melihat, mengedit, atau menghapus **semua** akun yang terdaftar (Admin & User Biasa).</p>
                <a href="<?php echo BASE_URL; ?>/register.php" class="btn btn-primary" target="_blank">➕ Tambah Akun Baru</a>
            </div>

            <?php if (isset($_GET['pesan'])): ?>
                <div class="alert alert-success">
                    <?php 
                    if ($_GET['pesan'] == 'user_edit') echo "Data user berhasil diupdate!";
                    if ($_GET['pesan'] == 'user_hapus') echo "Akun berhasil dihapus!";
                    ?>
                </div>
            <?php endif; ?>

            <h3>Daftar Pengguna Sistem</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr><th>ID</th><th>Username</th><th>Nama Lengkap</th><th>Role</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT id, username, nama_lengkap, role FROM users ORDER BY id ASC";
                        $result = mysqli_query($koneksi, $query);
                        if (mysqli_num_rows($result) > 0) {
                            while ($data = mysqli_fetch_assoc($result)) { 
                                $is_current_user = ($data['username'] === $_SESSION['username']);
                            ?>
                            <tr>
                                <td><?php echo $data['id']; ?></td>
                                <td><?php echo htmlspecialchars($data['username']); ?></td>
                                <td><?php echo htmlspecialchars($data['nama_lengkap']); ?></td>
                                <td><span style="font-weight: bold; color: <?php echo ($data['role'] === 'admin') ? '#4CAF50' : '#8c98a3'; ?>;"><?php echo strtoupper($data['role']); ?></span></td>
                                <td class="aksi-group">
                                    <a href="<?php echo BASE_URL; ?>/admin/edit_user.php?id=<?php echo $data['id']; ?>" class="btn btn-warning">Edit</a>
                                    <?php if (!$is_current_user): ?>
                                        <a href="<?php echo BASE_URL; ?>/admin/aksi_crud.php?aksi=hapus_user&id=<?php echo $data['id']; ?>" onclick="return confirm('Yakin ingin menghapus akun ini?');" class="btn btn-danger">Hapus</a>
                                    <?php else: ?>
                                        <button class="btn btn-secondary" disabled>Akun Anda</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php }
                        } else { echo '<tr><td colspan="5" style="text-align:center;">Tidak ada akun terdaftar.</td></tr>'; }
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