<?php
session_start();
include '../config.php';

// Cek Role Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

// =========================================================================
// LOGIKA PAGINATION
// =========================================================================
$limit = 10; // Komentar per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Total data untuk menghitung total halaman
$total_q = mysqli_query($koneksi, "SELECT COUNT(id) FROM komentar WHERE parent_id = 0");
$total_rows = mysqli_fetch_row($total_q)[0];
$total_pages = ceil($total_rows / $limit);


// Logika Hapus Komentar
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $komentar_id = (int)$_GET['id'];
    // Hapus komentar dan semua balasannya (reply)
    mysqli_query($koneksi, "DELETE FROM komentar WHERE id = $komentar_id OR parent_id = $komentar_id");
    header("Location: komentar.php?pesan=deleted");
    exit();
}

// Query untuk mengambil SEMUA komentar utama beserta nama gamenya (Dibatasi oleh LIMIT & OFFSET)
$query = "SELECT k.*, g.nama as game_nama FROM komentar k 
          JOIN games g ON k.game_id = g.id
          WHERE k.parent_id = 0
          ORDER BY k.created_at DESC
          LIMIT $limit OFFSET $offset";

$result = mysqli_query($koneksi, $query);

// =========================================================================
// FUNGSI PERBAIKAN: Mengecek apakah Admin sudah membalas thread ini
// =========================================================================
function get_reply_status($koneksi, $parent_id) {
    // 1. Hitung total balasan
    $count_q = mysqli_query($koneksi, "SELECT COUNT(id) FROM komentar WHERE parent_id = $parent_id");
    $count = mysqli_fetch_row($count_q)[0];
    
    // 2. Cek apakah ada balasan dari Admin (is_admin_reply = 1)
    $admin_reply_q = mysqli_query($koneksi, "SELECT id FROM komentar WHERE parent_id = $parent_id AND is_admin_reply = 1 LIMIT 1");
    $has_admin_reply = mysqli_num_rows($admin_reply_q) > 0;

    return [
        'count' => $count,
        'has_admin_reply' => $has_admin_reply
    ];
}
// =========================================================================


$nama_admin = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Administrator';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Kelola Komentar</title>
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>/assets/css/admin_style.css">
    <style>
        /* Tambahkan CSS untuk badge status */
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75em;
            font-weight: bold;
            color: #0f0f1a;
        }
        .status-badge.unreplied {
            background-color: #ff9800; /* Orange untuk Belum Dibalas */
        }
        .status-badge.replied {
            background-color: #4CAF50; /* Hijau untuk Sudah Dibalas */
        }
    </style>
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
                    <li><a href="<?php echo BASE_URL; ?>/admin/komentar.php" class="active">Kelola Komentar</a></li>
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
            <h2>KELOLA KOMENTAR GAME</h2>
            
            <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'deleted'): ?>
                <div class="alert alert-success">Komentar berhasil dihapus!</div>
            <?php endif; ?>

            <p class="welcome-message">Di sini Anda dapat melihat dan mengelola semua komentar utama dari pengunjung dan admin. (Total: <?php echo $total_rows; ?> komentar)</p>

            <h3>Daftar Komentar Terbaru (Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?>)</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Dari Game</th>
                            <th>Penulis</th>
                            <th>Komentar</th>
                            <th>Tanggal</th>
                            <th>Balasan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($komentar = mysqli_fetch_assoc($result)): 
                                $reply_status = get_reply_status($koneksi, $komentar['id']);
                                
                                // Tentukan badge status
                                if ($reply_status['has_admin_reply']) {
                                    $status_class = 'replied';
                                    $status_text = 'Sudah Dibalas';
                                } else {
                                    $status_class = 'unreplied';
                                    $status_text = 'Belum Dibalas';
                                }
                            ?>
                            <tr>
                                <td><?php echo $komentar['id']; ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL . '/detail.php?id=' . $komentar['game_id']; ?>" target="_blank" class="game-link">
                                        <?php echo htmlspecialchars($komentar['game_nama']); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($komentar['username']); ?>
                                    <?php if ($komentar['is_admin_reply'] == 1): ?>
                                        <span class="btn btn-primary" style="padding: 2px 5px; font-size: 0.7em;">ADMIN</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(substr($komentar['isi_komentar'], 0, 70)) . '...'; ?></td>
                                <td><?php echo date('d M Y', strtotime($komentar['created_at'])); ?></td>
                                <td><span style="font-weight: bold; color: #00bcd4;"><?php echo $reply_status['count']; ?></span></td>
                                <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                                <td class="aksi-group">
                                    <a href="<?php echo BASE_URL . '/detail.php?id=' . $komentar['game_id'] . '#comment-' . $komentar['id']; ?>" target="_blank" class="btn btn-warning" style="margin-bottom: 5px;">Lihat & Balas</a>
                                    <a href="komentar.php?action=delete&id=<?php echo $komentar['id']; ?>" onclick="return confirm('Yakin ingin menghapus komentar ini dan semua balasannya?');" class="btn btn-danger">Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8" style="text-align:center;">Tidak ada komentar di halaman ini.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 30px; text-align: center;">
                <?php if ($total_pages > 1): ?>
                    <span style="color: #b0b0b0;">Halaman: </span>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="komentar.php?page=<?php echo $i; ?>" 
                           class="btn <?php echo ($i == $page) ? 'btn-primary' : 'btn-secondary'; ?>"
                           style="padding: 5px 10px; margin: 0 5px; box-shadow: none;">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                <?php endif; ?>
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