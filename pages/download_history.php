<?php
session_start();
include '../config.php';

// Riwayat download berbasis localStorage - tidak perlu login

$is_logged_in = isset($_SESSION['user_id']);
$is_admin = $is_logged_in && ($_SESSION['role'] === 'admin');

// Admin diarahkan ke admin panel
if ($is_admin) {
    header("Location: " . BASE_URL . "/admin/index.php");
    exit();
}

$username_display = $is_logged_in ? ($_SESSION['username'] ?? 'User') : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Download - Game Hub</title>
    <meta name="description" content="Riwayat download game kamu">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <style>
        .history-page {
            padding: var(--space-2xl) 0;
        }
        .history-header {
            margin-bottom: var(--space-xl);
        }
        .history-header h1 {
            font-size: 2em;
            margin-bottom: var(--space-sm);
        }
        .history-list {
            background: var(--bg-card);
            border: 1px solid var(--border-dark);
            border-radius: var(--radius-lg);
            overflow: hidden;
        }
        .history-item {
            display: flex;
            align-items: center;
            gap: var(--space-lg);
            padding: var(--space-lg);
            border-bottom: 1px solid var(--border-dark);
            transition: background var(--transition-fast);
        }
        .history-item:last-child {
            border-bottom: none;
        }
        .history-item:hover {
            background: var(--bg-elevated);
        }
        .history-item img {
            width: 60px;
            height: 80px;
            object-fit: cover;
            border-radius: var(--radius-sm);
        }
        .history-info {
            flex: 1;
        }
        .history-info h3 {
            color: var(--text-primary);
            margin-bottom: var(--space-xs);
        }
        .history-info h3 a {
            color: inherit;
        }
        .history-info h3 a:hover {
            color: var(--primary);
        }
        .history-date {
            color: var(--text-muted);
            font-size: 0.9em;
        }
        .history-action {
            display: flex;
            gap: var(--space-sm);
        }
        .empty-history {
            text-align: center;
            padding: var(--space-2xl);
        }
        .empty-history h3 {
            margin-bottom: var(--space-md);
            color: var(--text-muted);
        }
        .clear-history-btn {
            margin-top: var(--space-lg);
            padding: 8px 16px;
            background: transparent;
            border: 1px solid var(--danger);
            color: var(--danger);
            border-radius: var(--radius-md);
            cursor: pointer;
            font-size: 0.85em;
        }
        .clear-history-btn:hover {
            background: var(--danger);
            color: white;
        }
    </style>
</head>
<body>
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loader"></div>
    </div>

    <!-- Navbar -->
    <nav class="navbar" id="navbar">
        <div class="navbar-content">
            <a href="<?php echo BASE_URL; ?>" class="navbar-brand">GAMEHUB</a>
            <div class="nav-center">
                <div class="nav-links">
                    <a href="<?php echo BASE_URL; ?>/index.php">Beranda</a>
                    <a href="<?php echo BASE_URL; ?>/pages/semua_game.php">Semua Game</a>
                </div>
            </div>
            <div class="nav-right">
                <form class="search-box" action="<?php echo BASE_URL; ?>/pages/semua_game.php" method="GET">
                    <input type="text" name="search" placeholder="Cari game..." autocomplete="off">
                    <button type="submit">&#128269;</button>
                </form>
                <?php if ($is_logged_in && !$is_admin): ?>
                <div class="user-menu-container">
                    <button class="profile-btn"><?php echo htmlspecialchars($username_display); ?></button>
                    <div class="user-menu">
                        <a href="<?php echo BASE_URL; ?>/pages/koleksi.php">&#10084;&#65039; Wishlist</a>
                        <a href="<?php echo BASE_URL; ?>/pages/download_history.php">&#128229; Downloads</a>
                        <a href="<?php echo BASE_URL; ?>/pages/request_game.php">&#127919; Request</a>
                        <div class="menu-separator"></div>
                        <a href="<?php echo BASE_URL; ?>/auth/logout.php">&#128682; Logout</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container history-page">
        <div class="history-header">
            <h1>📥 Riwayat Download</h1>
            <p style="color: var(--text-secondary);">Game yang pernah kamu download (<span id="historyCount">0</span> item)</p>
        </div>

        <div class="history-list" id="historyList">
            <div id="emptyHistory" class="empty-history" style="display: none;">
                <h3>📭 Belum ada riwayat download</h3>
                <p style="color: var(--text-muted); margin-bottom: var(--space-lg);">
                    Mulai download game dan riwayatmu akan muncul di sini.
                </p>
                <a href="<?php echo BASE_URL; ?>/pages/semua_game.php" class="btn btn-primary">🎮 Jelajahi Game</a>
            </div>
            <div id="historyItems"></div>
        </div>
        
        <button id="clearHistoryBtn" class="clear-history-btn" style="display: none;" onclick="clearHistory()">
            🗑️ Hapus Semua Riwayat
        </button>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <span class="footer-brand" id="secretLogin" style="cursor: default;">🎮 GAMEHUB</span>
            <div class="footer-links">
                <a href="<?php echo BASE_URL; ?>">Beranda</a>
                <a href="<?php echo BASE_URL; ?>/pages/semua_game.php">Semua Game</a>
            </div>
            <p class="footer-text">&copy; <?php echo date('Y'); ?> Game Hub</p>
        </div>
    </footer>

    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        
        function getDownloadHistory() {
            try {
                return JSON.parse(localStorage.getItem('gamehub_downloads') || '[]');
            } catch (e) {
                return [];
            }
        }
        
        function clearHistory() {
            if (confirm('Hapus semua riwayat download?')) {
                localStorage.removeItem('gamehub_downloads');
                loadHistory();
            }
        }
        
        function loadHistory() {
            const history = getDownloadHistory();
            const countEl = document.getElementById('historyCount');
            const emptyEl = document.getElementById('emptyHistory');
            const itemsEl = document.getElementById('historyItems');
            const clearBtn = document.getElementById('clearHistoryBtn');
            
            countEl.textContent = history.length;
            
            if (history.length === 0) {
                emptyEl.style.display = 'block';
                itemsEl.innerHTML = '';
                clearBtn.style.display = 'none';
                return;
            }
            
            emptyEl.style.display = 'none';
            clearBtn.style.display = 'block';
            
            // Get game IDs
            const gameIds = history.map(h => h.id);
            
            fetch(BASE_URL + '/wishlist_action.php?action=get_games_bulk&ids=' + gameIds.join(','))
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.games.length > 0) {
                        // Map games by id
                        const gamesMap = {};
                        data.games.forEach(g => gamesMap[g.id] = g);
                        
                        // Reverse to show newest first
                        const reversed = [...history].reverse();
                        
                        itemsEl.innerHTML = reversed.map(h => {
                            const game = gamesMap[h.id];
                            if (!game) return '';
                            return `
                                <div class="history-item">
                                    <img src="${BASE_URL}/${game.gambar_path}" alt="${game.nama}">
                                    <div class="history-info">
                                        <h3><a href="${BASE_URL}/pages/detail.php?id=${game.id}">${game.nama}</a></h3>
                                        <div class="history-date">
                                            📅 ${new Date(h.timestamp).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}
                                        </div>
                                    </div>
                                    <div class="history-action">
                                        <a href="${BASE_URL}/pages/detail.php?id=${game.id}" class="btn btn-secondary btn-sm">Lihat Game</a>
                                    </div>
                                </div>
                            `;
                        }).join('');
                    }
                });
        }
        
        document.addEventListener('DOMContentLoaded', loadHistory);

        window.addEventListener('load', function() {
            const loader = document.getElementById('loadingOverlay');
            if (loader) {
                loader.style.opacity = '0';
                setTimeout(() => loader.style.display = 'none', 500);
            }
        });
        
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            navbar.classList.toggle('scrolled', window.scrollY > 50);
        });
        
        // Secret login
        let clickCount = 0; let clickTimer = null;
        const secretLogin = document.getElementById('secretLogin');
        if (secretLogin) {
            secretLogin.addEventListener('click', function() {
                clickCount++;
                clearTimeout(clickTimer);
                clickTimer = setTimeout(() => { clickCount = 0; }, 800);
                if (clickCount >= 3) { window.location.href = BASE_URL + '/auth/login.php'; }
            });
        }
    </script>
</body>
</html>
