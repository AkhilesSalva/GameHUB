<?php
session_start();
include '../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_q = mysqli_query($koneksi, "SELECT COUNT(id) FROM komentar WHERE parent_id = 0");
$total_rows = mysqli_fetch_row($total_q)[0];
$total_pages = ceil($total_rows / $limit);

// Delete comment
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $komentar_id = (int)$_GET['id'];
    mysqli_query($koneksi, "DELETE FROM komentar WHERE id = $komentar_id OR parent_id = $komentar_id");
    header("Location: komentar.php?pesan=deleted");
    exit();
}

$query = "SELECT k.*, g.nama as game_nama FROM komentar k 
          JOIN games g ON k.game_id = g.id
          WHERE k.parent_id = 0
          ORDER BY k.created_at DESC
          LIMIT $limit OFFSET $offset";
$result = mysqli_query($koneksi, $query);

function get_reply_status($koneksi, $parent_id) {
    $count_q = mysqli_query($koneksi, "SELECT COUNT(id) FROM komentar WHERE parent_id = $parent_id");
    $count = mysqli_fetch_row($count_q)[0];
    
    $admin_reply_q = mysqli_query($koneksi, "SELECT id FROM komentar WHERE parent_id = $parent_id AND is_admin_reply = 1 LIMIT 1");
    $has_admin_reply = mysqli_num_rows($admin_reply_q) > 0;

    return ['count' => $count, 'has_admin_reply' => $has_admin_reply];
}

$nama_admin = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Administrator';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Komentar - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin_style.css">
    <style>
        .status-badge {
            padding: 4px 10px;
            border-radius: var(--radius-full);
            font-size: 0.75em;
            font-weight: 600;
        }
        .status-badge.unreplied {
            background: var(--warning);
            color: var(--bg-darkest);
        }
        .status-badge.replied {
            background: var(--success);
            color: var(--bg-darkest);
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: var(--space-sm);
            margin-top: var(--space-xl);
        }
        .pagination a {
            padding: 8px 14px;
            border-radius: var(--radius-sm);
            font-weight: 500;
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
                    <li><a href="<?php echo BASE_URL; ?>/admin/komentar.php" class="active">💬 Komentar</a></li>
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
            <h2>KELOLA KOMENTAR</h2>
            
            <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'deleted'): ?>
                <div class="alert alert-success">✓ Komentar berhasil dihapus!</div>
            <?php endif; ?>

            <div class="welcome-message">
                <div>
                    <p style="color: var(--text-primary); font-weight: 500;">Total <?php echo number_format($total_rows); ?> komentar</p>
                    <p style="color: var(--text-muted); font-size: 0.9em;">Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?></p>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Game</th>
                            <th>Penulis</th>
                            <th>Komentar</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($komentar = mysqli_fetch_assoc($result)): 
                                $reply_status = get_reply_status($koneksi, $komentar['id']);
                                $status_class = $reply_status['has_admin_reply'] ? 'replied' : 'unreplied';
                                $status_text = $reply_status['has_admin_reply'] ? '✓ Dibalas' : 'Pending';
                            ?>
                            <tr>
                                <td><strong>#<?php echo $komentar['id']; ?></strong></td>
                                <td>
                                    <a href="<?php echo BASE_URL . '/pages/detail.php?id=' . $komentar['game_id']; ?>" target="_blank">
                                        <?php echo htmlspecialchars($komentar['game_nama']); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($komentar['username']); ?>
                                    <?php if ($komentar['is_admin_reply'] == 1): ?>
                                        <span style="color: var(--primary); font-size: 0.75em;">(Admin)</span>
                                    <?php endif; ?>
                                </td>
                                <td style="max-width: 250px; color: var(--text-secondary);">
                                    <?php echo htmlspecialchars(substr($komentar['isi_komentar'], 0, 60)) . '...'; ?>
                                </td>
                                <td style="white-space: nowrap;"><?php echo date('d M Y', strtotime($komentar['created_at'])); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                    <?php if ($reply_status['count'] > 0): ?>
                                        <span style="color: var(--accent); font-size: 0.85em; margin-left: 5px;">(<?php echo $reply_status['count']; ?> balasan)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="aksi-group">
                                    <a href="<?php echo BASE_URL . '/pages/detail.php?id=' . $komentar['game_id'] . '#comment-' . $komentar['id']; ?>" target="_blank" class="btn btn-warning btn-sm">👁️ Lihat</a>
                                    <a href="komentar.php?action=delete&id=<?php echo $komentar['id']; ?>" onclick="return confirm('Yakin ingin menghapus komentar ini dan semua balasannya?');" class="btn btn-danger btn-sm">🗑️</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: var(--space-xl); color: var(--text-muted);">
                                    💬 Tidak ada komentar.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="komentar.php?page=<?php echo $i; ?>" 
                       class="btn <?php echo ($i == $page) ? 'btn-primary' : 'btn-secondary'; ?> btn-sm">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
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