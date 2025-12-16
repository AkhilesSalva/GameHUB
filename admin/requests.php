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
    
    if ($action === 'approve') {
        mysqli_query($koneksi, "UPDATE game_requests SET status = 'approved' WHERE id = $id");
    } elseif ($action === 'reject') {
        mysqli_query($koneksi, "UPDATE game_requests SET status = 'rejected' WHERE id = $id");
    } elseif ($action === 'complete') {
        mysqli_query($koneksi, "UPDATE game_requests SET status = 'completed' WHERE id = $id");
    } elseif ($action === 'delete') {
        mysqli_query($koneksi, "DELETE FROM game_requests WHERE id = $id");
    }
    
    header("Location: requests.php?msg=updated");
    exit();
}

// Get all requests
$requests_query = "SELECT gr.*, u.username 
                   FROM game_requests gr 
                   LEFT JOIN users u ON gr.user_id = u.id 
                   ORDER BY gr.created_at DESC";
$requests_result = mysqli_query($koneksi, $requests_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Requests - Admin</title>
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
        .status-approved { background: var(--accent); color: var(--bg-darkest); }
        .status-completed { background: var(--success); color: var(--bg-darkest); }
        .status-rejected { background: var(--danger); color: white; }
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
                    <li><a href="<?php echo BASE_URL; ?>/admin/requests.php" class="active">🎯 Requests</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/reports.php">🔗 Reports</a></li>
                </ul>
            </nav>
            <div class="logout-link">
                <p>👋 <?php echo htmlspecialchars($nama_admin); ?></p>
                <a href="<?php echo BASE_URL; ?>/logout.php" class="btn btn-danger btn-full">🚪 Logout</a>
            </div>
        </div>

        <div class="main-content">
            <h2>GAME REQUESTS</h2>
            
            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success">✓ Request berhasil diupdate!</div>
            <?php endif; ?>

            <div class="welcome-message">
                <p>Permintaan game dari user. Kelola status request di sini.</p>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Game</th>
                            <th>User</th>
                            <th>Platform</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($requests_result) > 0): ?>
                            <?php while ($req = mysqli_fetch_assoc($requests_result)): ?>
                            <tr>
                                <td><strong>#<?php echo $req['id']; ?></strong></td>
                                <td style="color: var(--text-primary); font-weight: 500;">
                                    <?php echo htmlspecialchars($req['game_name']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($req['username'] ?? 'Guest'); ?></td>
                                <td><?php echo htmlspecialchars($req['platform'] ?? '-'); ?></td>
                                <td style="max-width: 200px; color: var(--text-secondary);">
                                    <?php echo htmlspecialchars(substr($req['description'] ?? '-', 0, 50)); ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $req['status']; ?>">
                                        <?php echo ucfirst($req['status']); ?>
                                    </span>
                                </td>
                                <td style="white-space: nowrap;"><?php echo date('d M Y', strtotime($req['created_at'])); ?></td>
                                <td class="aksi-group">
                                    <?php if ($req['status'] === 'pending'): ?>
                                        <a href="?action=approve&id=<?php echo $req['id']; ?>" class="btn btn-primary btn-sm">✓</a>
                                        <a href="?action=reject&id=<?php echo $req['id']; ?>" class="btn btn-warning btn-sm">✕</a>
                                    <?php elseif ($req['status'] === 'approved'): ?>
                                        <a href="?action=complete&id=<?php echo $req['id']; ?>" class="btn btn-primary btn-sm">Done</a>
                                    <?php endif; ?>
                                    <a href="?action=delete&id=<?php echo $req['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus request ini?');">🗑️</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: var(--space-xl); color: var(--text-muted);">
                                    🎯 Belum ada game request.
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
