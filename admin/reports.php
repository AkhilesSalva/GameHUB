<?php
include '../config.php';
include 'cek_login.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$nama_admin = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Administrator';

// Handle status update
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'fixed') {
        mysqli_query($koneksi, "UPDATE broken_link_reports SET status = 'fixed', resolved_at = NOW() WHERE id = $id");
    } elseif ($action === 'dismiss') {
        mysqli_query($koneksi, "UPDATE broken_link_reports SET status = 'dismissed', resolved_at = NOW() WHERE id = $id");
    } elseif ($action === 'delete') {
        mysqli_query($koneksi, "DELETE FROM broken_link_reports WHERE id = $id");
    }
    
    header("Location: reports.php?msg=updated");
    exit();
}

// Get all reports
$reports_query = "SELECT blr.*, g.nama as game_name, u.username 
                  FROM broken_link_reports blr 
                  JOIN games g ON blr.game_id = g.id
                  LEFT JOIN users u ON blr.user_id = u.id 
                  ORDER BY blr.status = 'pending' DESC, blr.created_at DESC";
$reports_result = mysqli_query($koneksi, $reports_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Broken Link Reports - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin_style.css">
    <style>
        .status-badge {
            padding: 4px 10px;
            border-radius: var(--radius-full);
            font-size: 0.75em;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending { background: var(--warning); color: var(--bg-darkest); }
        .status-fixed { background: var(--success); color: var(--bg-darkest); }
        .status-dismissed { background: var(--text-muted); color: var(--bg-darkest); }
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
                    <li><a href="<?php echo BASE_URL; ?>/admin/users.php">👤 Users</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/requests.php">🎯 Requests</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/reports.php" class="active">🔗 Reports</a></li>
                </ul>
            </nav>
            <div class="logout-link">
                <p>👋 <?php echo htmlspecialchars($nama_admin); ?></p>
                <a href="<?php echo BASE_URL; ?>/logout.php" class="btn btn-danger btn-full">🚪 Logout</a>
            </div>
        </div>

        <div class="main-content">
            <h2>LAPORAN LINK RUSAK</h2>
            
            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success">✓ Report berhasil diupdate!</div>
            <?php endif; ?>

            <div class="welcome-message">
                <p>Daftar link download yang dilaporkan rusak oleh user.</p>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Game</th>
                            <th>Pelapor</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($reports_result) > 0): ?>
                            <?php while ($report = mysqli_fetch_assoc($reports_result)): ?>
                            <tr>
                                <td><strong>#<?php echo $report['id']; ?></strong></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/pages/detail.php?id=<?php echo $report['game_id']; ?>" target="_blank" style="color: var(--text-primary); font-weight: 500;">
                                        <?php echo htmlspecialchars($report['game_name']); ?> ↗
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($report['username'] ?? 'Guest'); ?></td>
                                <td style="color: var(--text-secondary);">
                                    <?php echo htmlspecialchars($report['description'] ?? 'Tidak ada keterangan'); ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $report['status']; ?>">
                                        <?php echo ucfirst($report['status']); ?>
                                    </span>
                                </td>
                                <td style="white-space: nowrap;"><?php echo date('d M Y', strtotime($report['created_at'])); ?></td>
                                <td class="aksi-group">
                                    <?php if ($report['status'] === 'pending'): ?>
                                        <a href="<?php echo BASE_URL; ?>/admin/edit.php?id=<?php echo $report['game_id']; ?>" class="btn btn-warning btn-sm" target="_blank">✏️ Edit</a>
                                        <a href="?action=fixed&id=<?php echo $report['id']; ?>" class="btn btn-primary btn-sm">✓ Fixed</a>
                                        <a href="?action=dismiss&id=<?php echo $report['id']; ?>" class="btn btn-secondary btn-sm">✕</a>
                                    <?php else: ?>
                                        <a href="?action=delete&id=<?php echo $report['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus report ini?');">🗑️</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: var(--space-xl); color: var(--text-muted);">
                                    🔗 Tidak ada laporan link rusak.
                                </td>
                            </tr>
                        <?php endif; ?>
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
