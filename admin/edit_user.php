<?php
include '../config.php';
include 'cek_login.php'; 

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: " . BASE_URL . "/admin/users.php");
    exit();
}
$id_user = $_GET['id'];
$query = "SELECT * FROM users WHERE id='$id_user'";
$result = mysqli_query($koneksi, $query);
$data = mysqli_fetch_assoc($result);
if (!$data) { die("Data user tidak ditemukan!"); }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit User: <?php echo htmlspecialchars($data['username']); ?></title>
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
                    <li><a href="<?php echo BASE_URL; ?>/admin/tambah.php">Tambah Game</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/users.php" class="active">Kelola User</a></li>
                </ul>
            </nav>
            <div class="logout-link">
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['nama']); ?></p>
                <a href="<?php echo BASE_URL; ?>/logout.php" class="btn btn-danger btn-full">Logout</a>
            </div>
        </div>
        <div class="main-content">
            <h2>EDIT AKUN: <?php echo htmlspecialchars($data['username']); ?></h2>
            <a href="<?php echo BASE_URL; ?>/admin/users.php" class="btn btn-secondary" style="margin-bottom: 20px;">Kembali</a>
            <div class="form-container">
                <form action="<?php echo BASE_URL; ?>/admin/aksi_crud.php" method="POST">
                    <input type="hidden" name="id_user" value="<?php echo $data['id']; ?>">
                    <div class="input-group">
                        <label>Username (Tidak dapat diubah):</label>
                        <input type="text" value="<?php echo htmlspecialchars($data['username']); ?>" disabled>
                    </div>
                    <div class="input-group">
                        <label>Nama Lengkap:</label>
                        <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($data['nama_lengkap']); ?>" required>
                    </div>
                    <div class="input-group">
                        <label>Ganti Password (Kosongkan jika tidak diubah):</label>
                        <input type="password" name="password_baru" placeholder="Masukkan password baru">
                    </div>
                    <div class="input-group">
                        <label>Role:</label>
                        <select name="role" required>
                            <option value="admin" <?php echo ($data['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                            <option value="user" <?php echo ($data['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                        </select>
                    </div>
                    <button type="submit" name="edit_user" class="btn btn-primary btn-full">SIMPAN PERUBAHAN</button>
                </form>
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