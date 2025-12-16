<?php
session_start();
include '../config.php';

// Cek ID game
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: " . BASE_URL);
    exit();
}
$game_id = (int)$_GET['id'];

// Tambah view count
mysqli_query($koneksi, "UPDATE games SET view_count = view_count + 1 WHERE id = $game_id");

// Ambil data game
$query = "SELECT * FROM games WHERE id = $game_id";
$result = mysqli_query($koneksi, $query);
$game = mysqli_fetch_assoc($result);

if (!$game) {
    header("Location: ". BASE_URL . "/index.php?error=notfound");
    exit();
}

// Ambil genre game
$genres_query = mysqli_query($koneksi, "SELECT g.nama_genre FROM genre g JOIN game_genre gg ON g.id = gg.genre_id WHERE gg.game_id = $game_id");
$game_genres = [];
while ($row = mysqli_fetch_assoc($genres_query)) {
    $game_genres[] = $row['nama_genre'];
}

// Cek Status User & Admin
$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$is_admin = $is_logged_in && ($_SESSION['role'] === 'admin');
$user_id = $is_logged_in ? (int)$_SESSION['user_id'] : 0;

// Get user's rating
$user_rating = 0;
if ($is_logged_in) {
    $rating_check = mysqli_query($koneksi, "SELECT rating FROM ratings WHERE user_id = $user_id AND game_id = $game_id");
    if (mysqli_num_rows($rating_check) > 0) {
        $ur = mysqli_fetch_assoc($rating_check);
        $user_rating = (int)$ur['rating'];
    }
}

// Get screenshots
$screenshots_query = mysqli_query($koneksi, "SELECT * FROM screenshots WHERE game_id = $game_id ORDER BY order_num ASC");
$screenshots = [];
while ($row = mysqli_fetch_assoc($screenshots_query)) {
    $screenshots[] = $row;
}

// Submit Komentar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : 0;
    $comment_text = mysqli_real_escape_string($koneksi, trim($_POST['comment_text']));
    $is_admin_reply = $is_admin ? 1 : 0;

    if ($is_logged_in) {
        $username = mysqli_real_escape_string($koneksi, $_SESSION['username']);
    } else {
        $username = mysqli_real_escape_string($koneksi, trim($_POST['guest_username'] ?? 'Pengunjung'));
        if (empty($username) || strtolower($username) == 'pengunjung') { $username = 'Pengunjung'; }
    }

    if (!empty($comment_text)) {
        $insert_query = "INSERT INTO komentar (game_id, user_id, username, isi_komentar, parent_id, is_admin_reply, created_at) 
                         VALUES ('$game_id', '$user_id', '$username', '$comment_text', '$parent_id', '$is_admin_reply', NOW())";
        mysqli_query($koneksi, $insert_query);
        
        header("Location: detail.php?id=$game_id#comments");
        exit();
    }
}

// Ambil Komentar
$comments_query = "SELECT * FROM komentar WHERE game_id = $game_id AND parent_id = 0 ORDER BY created_at DESC";
$comments_result = mysqli_query($koneksi, $comments_query);
$comments = [];
while ($row = mysqli_fetch_assoc($comments_result)) { $comments[] = $row; }

function get_replies($koneksi, $parent_id) {
    $replies_query = "SELECT * FROM komentar WHERE parent_id = $parent_id ORDER BY created_at ASC";
    $replies_result = mysqli_query($koneksi, $replies_query);
    $replies = [];
    while ($row = mysqli_fetch_assoc($replies_result)) { $replies[] = $row; }
    return $replies;
}

$username_display = $is_logged_in ? ($_SESSION['username'] ?? 'User') : '';

// Generate share URLs
$share_url = BASE_URL . '/detail.php?id=' . $game_id;
$share_title = urlencode($game['nama'] . ' - Game Hub');
$share_whatsapp = "https://wa.me/?text=" . urlencode($game['nama'] . " - Download di Game Hub: " . $share_url);
$share_twitter = "https://twitter.com/intent/tweet?text=" . $share_title . "&url=" . urlencode($share_url);
$share_facebook = "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($share_url);

// Check if game is coming soon
$is_coming_soon = !empty($game['coming_soon']) && $game['coming_soon'] == 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($game['nama']); ?> - Game Hub</title>
    <meta name="description" content="Download <?php echo htmlspecialchars($game['nama']); ?> - <?php echo htmlspecialchars(substr($game['deskripsi'], 0, 150)); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <style>
        .notify-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--warning));
            border: none;
            cursor: pointer;
            font-size: 1.5em;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-normal);
            z-index: 10; color: var(--bg-darkest); } .notify-btn:hover { transform: scale(1.1); } .notify-btn.active { background: var(--success); } .detail-header { position: relative; } .share-buttons { display: flex; gap: var(--space-sm); margin-top: var(--space-md); flex-wrap: wrap; } .share-btn { padding: 8px 12px; border-radius: var(--radius-md); font-size: 0.85em; font-weight: 500; display: inline-flex; align-items: center; gap: var(--space-xs); transition: all var(--transition-fast); text-decoration: none; } .share-btn.whatsapp { background: #25D366; color: white; } .share-btn.twitter { background: #1DA1F2; color: white; } .share-btn.facebook { background: #4267B2; color: white; } .share-btn.copy { background: var(--bg-elevated); color: var(--text-primary); border: 1px solid var(--border-dark); } .share-btn:hover { transform: translateY(-2px); opacity: 0.9; } .rating-form { background: var(--bg-elevated); padding: var(--space-sm) var(--space-md); border-radius: var(--radius-md); display: inline-flex; align-items: center; gap: var(--space-md); } .star-picker { display: flex; gap: 4px; } .star-picker .star { font-size: 1.3em; cursor: pointer; color: var(--text-muted); transition: all var(--transition-fast); } .star-picker .star:hover, .star-picker .star.selected { color: var(--warning); transform: scale(1.1); } .game-info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: var(--space-md); margin-top: var(--space-lg); padding: var(--space-lg); background: var(--bg-elevated); border-radius: var(--radius-md); } .info-item { text-align: center; } .info-item label { display: block; color: var(--text-muted); font-size: 0.85em; margin-bottom: var(--space-xs); } .info-item span { color: var(--accent); font-weight: 600; } .screenshot-gallery { display: flex; gap: var(--space-md); overflow-x: auto; padding: var(--space-md) 0; } .screenshot-item { flex-shrink: 0; border-radius: var(--radius-md); overflow: hidden; cursor: pointer; transition: transform var(--transition-fast); }
        .screenshot-item:hover { transform: scale(1.05); }
        .screenshot-item img { height: 150px; width: auto; display: block; }
        .report-link-btn { color: var(--text-muted); font-size: 0.85em; margin-top: var(--space-md); cursor: pointer; display: inline-flex; align-items: center; gap: var(--space-xs); }
        .report-link-btn:hover { color: var(--danger); }
        .trailer-embed { margin-top: var(--space-lg); border-radius: var(--radius-md); overflow: hidden; }
        .trailer-embed iframe { width: 100%; aspect-ratio: 16/9; border: none; }
        .coming-soon-box {
            background: linear-gradient(135deg, var(--accent), var(--warning));
            padding: var(--space-lg);
            border-radius: var(--radius-lg);
            color: var(--bg-darkest);
            text-align: center;
        }
        .follow-btn {
            width: 100%;
            padding: var(--space-sm) var(--space-md);
            background: var(--bg-darkest);
            border: none;
            color: var(--accent);
            border-radius: var(--radius-md);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-sm);
            transition: all var(--transition-fast);
            font-weight: 600;
            margin-top: var(--space-md);
        }
        .follow-btn:hover { opacity: 0.9; transform: scale(1.02); }
        .follow-btn.following { background: var(--success); color: white; }
        .toast-notification {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: var(--bg-elevated);
            color: var(--text-primary);
            padding: 16px 24px;
            border-radius: var(--radius-md);
            border: 1px solid var(--accent);
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
            z-index: 9999;
            opacity: 0;
            transition: all 0.4s ease;
        }
        .toast-notification.show { transform: translateX(-50%) translateY(0); opacity: 1; }
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
                    <a href="<?php echo BASE_URL; ?>/koleksi.php">&#128276; Coming Soon</a>
                </div>
            </div>
            <div class="nav-right">
                <form class="search-box" action="<?php echo BASE_URL; ?>/pages/semua_game.php" method="GET">
                    <input type="text" name="search" placeholder="Cari game..." autocomplete="off">
                    <button type="submit">&#128269;</button>
                </form>
                <?php if ($is_logged_in): ?>
                    <div class="user-menu-container">
                        <button class="profile-btn"><?php echo htmlspecialchars($username_display); ?></button>
                        <div class="user-menu">
                            <?php if ($is_admin): ?>
                                <a href="<?php echo BASE_URL; ?>/admin/index.php">&#127918; Admin Console</a>
                                <div class="menu-separator"></div>
                                <a href="<?php echo BASE_URL; ?>/auth/logout.php">&#128682; Logout</a>
                            <?php else: ?>
                                <a href="<?php echo BASE_URL; ?>/pages/koleksi.php">&#128276; Coming Soon</a>
                                <a href="<?php echo BASE_URL; ?>/pages/download_history.php">&#128229; Downloads</a>
                                <a href="<?php echo BASE_URL; ?>/pages/request_game.php">&#127919; Request</a>
                                <div class="menu-separator"></div>
                                <a href="<?php echo BASE_URL; ?>/auth/logout.php">&#128682; Logout</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Background -->
    <?php if (!empty($game['hero_image_path'])): ?>
    <div class="detail-hero" style="background-image: url('<?php echo BASE_URL . '/' . htmlspecialchars($game['hero_image_path']); ?>');">
        <div class="detail-hero-overlay"></div>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="detail-content">
        <div class="detail-header">
            <?php if ($is_coming_soon): ?>
            <button class="notify-btn" id="notifyBtn" onclick="toggleFollow(<?php echo $game_id; ?>, '<?php echo htmlspecialchars(addslashes($game['nama'])); ?>')" title="Notify Me When Released">
                &#128276;
            </button>
            <?php endif; ?>
            
            <img src="<?php echo BASE_URL . '/' . htmlspecialchars($game['gambar_path']); ?>" alt="<?php echo htmlspecialchars($game['nama']); ?>" class="detail-cover">
            
            <div class="detail-info">
                <h1 class="detail-title"><?php echo htmlspecialchars($game['nama']); ?></h1>
                
                <?php if ($is_coming_soon): ?>
                <span style="display: inline-block; background: var(--accent); color: var(--bg-darkest); padding: 4px 12px; border-radius: var(--radius-md); font-weight: 600; margin-bottom: var(--space-sm);">&#128284; Coming Soon</span>
                <?php endif; ?>
                
                <div class="detail-meta">
                    <span>&#128229; <?php echo number_format($game['download_count']); ?> Downloads</span>
                    <span>&#128065; <?php echo number_format($game['view_count']); ?> Views</span>
                    <span>&#128172; <?php echo count($comments); ?> Komentar</span>
                    <?php if (!empty($game['file_size'])): ?>
                        <span>&#128190; <?php echo htmlspecialchars($game['file_size']); ?></span>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($game_genres)): ?>
                <div class="genre-tags" style="margin-bottom: var(--space-lg);">
                    <?php foreach ($game_genres as $genre): ?>
                        <a href="<?php echo BASE_URL; ?>/pages/genre.php?nama=<?php echo urlencode($genre); ?>" class="genre-tag" style="padding: 6px 14px; font-size: 0.85em;">
                            <?php echo htmlspecialchars($genre); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Rating Display -->
                <div class="detail-rating">
                    <div class="stars">
                        <?php 
                        $avg = floatval($game['avg_rating'] ?? 0);
                        for ($i = 1; $i <= 5; $i++):
                            $activeClass = ($i <= round($avg)) ? 'active' : '';
                        ?>
                            <span class="star <?php echo $activeClass; ?>">&#9733;</span>
                        <?php endfor; ?>
                    </div>
                    <span class="rating-text" id="avgRating">
                        <?php echo number_format($avg, 1); ?> / 5.0 
                        (<?php echo (int)($game['rating_count'] ?? 0); ?> rating)
                    </span>
                </div>
                
                <!-- Game Info Grid -->
                <?php if (!empty($game['platform']) || !empty($game['version']) || !empty($game['file_size'])): ?>
                <div class="game-info-grid">
                    <?php if (!empty($game['platform'])): ?>
                    <div class="info-item">
                        <label>Platform</label>
                        <span>&#128421;&#65039; <?php echo htmlspecialchars($game['platform']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($game['version'])): ?>
                    <div class="info-item">
                        <label>Versi</label>
                        <span>&#128230; <?php echo htmlspecialchars($game['version']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($game['file_size'])): ?>
                    <div class="info-item">
                        <label>Ukuran</label>
                        <span>&#128190; <?php echo htmlspecialchars($game['file_size']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Download Section -->
                <?php if (!$is_coming_soon): ?>
                <div class="download-section">
                    <?php if ($game['link_type'] == 'single'): ?>
                        <a href="<?php echo BASE_URL . '/download.php?id=' . $game['id']; ?>" class="btn btn-primary btn-download btn-lg">
                            &#128229; DOWNLOAD SEKARANG
                        </a>
                    <?php else: 
                        $link_parts = explode(',', $game['link_single']);
                    ?>
                        <h4 style="color: var(--accent); margin-bottom: var(--space-md);">&#128230; Pilih Part Download:</h4>
                        <div class="download-parts">
                            <?php if (!empty($link_parts) && !empty(trim($link_parts[0]))): ?>
                                <?php foreach ($link_parts as $index => $link): ?>
                                    <a href="<?php echo BASE_URL . '/download.php?id=' . $game['id'] . '&part=' . ($index + 1); ?>" class="download-part">
                                        <span><strong>PART <?php echo $index + 1; ?></strong></span>
                                        <span style="font-size: 0.85em; color: var(--text-muted);">Klik untuk Download</span>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="color: var(--danger);">&#9888;&#65039; Link belum tersedia.</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="report-link-btn" onclick="reportBrokenLink()">
                        &#128279; Link tidak berfungsi? Laporkan
                    </div>
                </div>
                <?php else: ?>
                <!-- Coming Soon Notice -->
                <div class="download-section">
                    <div class="coming-soon-box">
                        <h3 style="margin: 0 0 var(--space-sm) 0;">&#128284; Coming Soon!</h3>
                        <p style="margin: 0; opacity: 0.9;">Game ini belum tersedia untuk didownload. Klik tombol dibawah untuk mendapat notifikasi saat game dirilis!</p>
                        <button class="follow-btn" id="followBtn" onclick="toggleFollow(<?php echo $game['id']; ?>, '<?php echo htmlspecialchars(addslashes($game['nama'])); ?>')">
                            <span class="follow-icon">&#128276;</span>
                            <span class="follow-text">Notify Me</span>
                        </button>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Share Buttons -->
                <div class="share-buttons">
                    <a href="<?php echo $share_whatsapp; ?>" target="_blank" class="share-btn whatsapp">&#128241; WhatsApp</a>
                    <a href="<?php echo $share_twitter; ?>" target="_blank" class="share-btn twitter">&#128038; Twitter</a>
                    <a href="<?php echo $share_facebook; ?>" target="_blank" class="share-btn facebook">&#128216; Facebook</a>
                    <button class="share-btn copy" onclick="copyLink()">&#128203; Copy Link</button>
                </div>
            </div>
        </div>
        
        <!-- Trailer Video -->
        <?php if (!empty($game['trailer_url'])): ?>
        <div class="glass-card">
            <h3>&#127916; Trailer</h3>
            <div class="trailer-embed">
                <?php 
                $video_id = '';
                if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $game['trailer_url'], $matches)) {
                    $video_id = $matches[1];
                }
                if ($video_id): ?>
                    <iframe src="https://www.youtube.com/embed/<?php echo $video_id; ?>" allowfullscreen></iframe>
                <?php else: ?>
                    <a href="<?php echo htmlspecialchars($game['trailer_url']); ?>" target="_blank" class="btn btn-secondary">&#128279; Lihat Trailer</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Screenshots -->
        <?php if (!empty($screenshots)): ?>
        <div class="glass-card">
            <h3>&#128248; Screenshot</h3>
            <div class="screenshot-gallery">
                <?php foreach ($screenshots as $ss): ?>
                    <div class="screenshot-item" onclick="viewImage('<?php echo BASE_URL . '/' . htmlspecialchars($ss['image_path']); ?>')">
                        <img src="<?php echo BASE_URL . '/' . htmlspecialchars($ss['image_path']); ?>" alt="Screenshot">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Description -->
        <div class="glass-card">
            <h3>&#128214; Deskripsi Game</h3>
            <p><?php echo nl2br(htmlspecialchars($game['deskripsi'])); ?></p>
            
            <!-- System Requirements -->
            <?php if (!empty($game['system_req_min']) || !empty($game['system_req_rec'])): ?>
            <div style="margin-top: var(--space-xl);">
                <h4 style="color: var(--accent); margin-bottom: var(--space-md);">&#9881;&#65039; System Requirements</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-lg);">
                    <?php if (!empty($game['system_req_min'])): ?>
                    <div style="background: var(--bg-elevated); padding: var(--space-md); border-radius: var(--radius-md);">
                        <strong style="color: var(--warning);">Minimum:</strong>
                        <p style="margin-top: var(--space-sm); white-space: pre-line;"><?php echo htmlspecialchars($game['system_req_min']); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($game['system_req_rec'])): ?>
                    <div style="background: var(--bg-elevated); padding: var(--space-md); border-radius: var(--radius-md);">
                        <strong style="color: var(--success);">Recommended:</strong>
                        <p style="margin-top: var(--space-sm); white-space: pre-line;"><?php echo htmlspecialchars($game['system_req_rec']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Rating Section (Only for non-coming-soon games) -->
        <?php if (!$is_coming_soon): ?>
        <div class="glass-card" id="ratings">
            <h3>&#11088; Beri Rating</h3>
            
            <?php if (!$is_admin): ?>
            <div class="rating-form">
                <div class="star-picker" id="starPicker">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="star <?php echo ($user_rating && $i <= $user_rating) ? 'selected' : ''; ?>" data-rating="<?php echo $i; ?>">&#9733;</span>
                    <?php endfor; ?>
                </div>
                <button class="btn btn-primary" onclick="submitRating()">
                    <?php echo $user_rating ? 'Update Rating' : 'Kirim Rating'; ?>
                </button>
            </div>
            <?php else: ?>
            <p style="color: var(--text-muted);">Admin tidak bisa memberikan rating.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Comments Section -->
        <div class="glass-card comment-section" id="comments">
            <h3>&#128172; Komentar dan Diskusi</h3>
            
            <!-- Comment Form -->
            <div class="comment-form" style="margin-bottom: var(--space-xl);">
                <form method="POST" action="detail.php?id=<?php echo $game['id']; ?>#comments">
                    <input type="hidden" name="submit_comment" value="1">
                    <input type="hidden" name="parent_id" value="0">
                    
                    <?php if (!$is_logged_in): ?>
                        <input type="text" name="guest_username" placeholder="Nama kamu (opsional)" style="margin-bottom: var(--space-md);">
                    <?php endif; ?>
                    
                    <textarea name="comment_text" placeholder="Tulis komentar kamu..." required></textarea>
                    <button type="submit" class="btn btn-accent" style="margin-top: var(--space-md);">Kirim Komentar</button>
                </form>
            </div>
            
            <!-- Comments List -->
            <div class="comment-list">
                <?php if (!empty($comments)): ?>
                    <?php foreach ($comments as $comment): ?>
                        <?php 
                            $is_comment_admin = $comment['is_admin_reply'] == 1;
                            $author_class = $is_comment_admin ? 'admin' : '';
                        ?>
                        <div class="comment-item <?php echo $is_comment_admin ? 'admin-reply' : ''; ?>" id="comment-<?php echo $comment['id']; ?>">
                            <div class="comment-header">
                                <span class="comment-author <?php echo $author_class; ?>">
                                    <?php echo htmlspecialchars($comment['username']); ?>
                                    <?php if ($is_comment_admin) echo ' (Admin)'; ?>
                                </span>
                                <span class="comment-date"><?php echo date('d M Y, H:i', strtotime($comment['created_at'])); ?></span>
                            </div>
                            <div class="comment-body">
                                <?php echo nl2br(htmlspecialchars($comment['isi_komentar'])); ?>
                            </div>
                            
                            <?php if ($is_admin): ?>
                                <button class="comment-reply-btn" onclick="toggleReplyForm(<?php echo $comment['id']; ?>)">&#8617;&#65039; Balas</button>
                                
                                <div class="reply-form" id="reply-form-<?php echo $comment['id']; ?>" style="display: none; margin-top: var(--space-md);">
                                    <form method="POST" action="detail.php?id=<?php echo $game['id']; ?>#comment-<?php echo $comment['id']; ?>">
                                        <input type="hidden" name="submit_comment" value="1">
                                        <input type="hidden" name="parent_id" value="<?php echo $comment['id']; ?>">
                                        <textarea name="comment_text" placeholder="Balasan Admin..." required style="min-height: 80px;"></textarea>
                                        <button type="submit" class="btn btn-primary btn-sm" style="margin-top: var(--space-sm);">Kirim Balasan</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Replies -->
                            <?php $replies = get_replies($koneksi, $comment['id']); ?>
                            <?php if (!empty($replies)): ?>
                                <div class="replies">
                                    <?php foreach ($replies as $reply): ?>
                                        <?php $is_reply_admin = $reply['is_admin_reply'] == 1; ?>
                                        <div class="comment-item <?php echo $is_reply_admin ? 'admin-reply' : ''; ?>">
                                            <div class="comment-header">
                                                <span class="comment-author <?php echo $is_reply_admin ? 'admin' : ''; ?>">
                                                    <?php echo htmlspecialchars($reply['username']); ?>
                                                    <?php if ($is_reply_admin) echo ' (Admin)'; ?>
                                                </span>
                                                <span class="comment-date"><?php echo date('d M Y, H:i', strtotime($reply['created_at'])); ?></span>
                                            </div>
                                            <div class="comment-body">
                                                <?php echo nl2br(htmlspecialchars($reply['isi_komentar'])); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: var(--text-muted); text-align: center; padding: var(--space-xl);">
                        Belum ada komentar. Jadilah yang pertama! &#127918;
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <span class="footer-brand" id="secretLogin" style="cursor: default;">&#127918; GAMEHUB</span>
            <div class="footer-links">
                <a href="<?php echo BASE_URL; ?>">Beranda</a>
                <a href="<?php echo BASE_URL; ?>/pages/semua_game.php">Semua Game</a>
                <a href="<?php echo BASE_URL; ?>/pages/koleksi.php">Coming Soon</a>
                <?php if ($is_admin): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/index.php">Admin</a>
                <?php endif; ?>
            </div>
            <p class="footer-text">&copy; <?php echo date('Y'); ?> Game Hub</p>
        </div>
    </footer>

    <!-- Toast Notification -->
    <div class="toast-notification" id="toast"></div>

    <script>
        const gameId = <?php echo $game_id; ?>;
        let selectedRating = <?php echo $user_rating; ?>;
        
        // Toast notification
        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 2500);
        }
        
        // Get followed games from localStorage
        function getFollowedGames() {
            try { return JSON.parse(localStorage.getItem('followedGames') || '[]'); }
            catch (e) { return []; }
        }
        
        // Toggle follow for Coming Soon games
        function toggleFollow(gId, gameName) {
            let followed = getFollowedGames();
            const index = followed.indexOf(gId);
            const btn = document.getElementById('followBtn');
            const notifyBtn = document.getElementById('notifyBtn');
            
            if (index === -1) {
                followed.push(gId);
                showToast(`Kamu akan dinotifikasi saat "${gameName}" dirilis!`);
                if (btn) {
                    btn.classList.add('following');
                    btn.innerHTML = '<span class="follow-icon">&#10004;</span><span class="follow-text">Following</span>';
                }
                if (notifyBtn) notifyBtn.classList.add('active');
            } else {
                followed.splice(index, 1);
                showToast(`Notifikasi untuk "${gameName}" dinonaktifkan.`);
                if (btn) {
                    btn.classList.remove('following');
                    btn.innerHTML = '<span class="follow-icon">&#128276;</span><span class="follow-text">Notify Me</span>';
                }
                if (notifyBtn) notifyBtn.classList.remove('active');
            }
            
            localStorage.setItem('followedGames', JSON.stringify(followed));
        }
        
        // Update follow button state on load
        function updateFollowButtonState() {
            const followed = getFollowedGames();
            const btn = document.getElementById('followBtn');
            const notifyBtn = document.getElementById('notifyBtn');
            
            if (followed.includes(gameId)) {
                if (btn) {
                    btn.classList.add('following');
                    btn.innerHTML = '<span class="follow-icon">&#10004;</span><span class="follow-text">Following</span>';
                }
                if (notifyBtn) notifyBtn.classList.add('active');
            }
        }
        
        // Track download
        function trackDownload(gId) {
            fetch('<?php echo BASE_URL; ?>/track_download.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'game_id=' + gId
            });
        }
        
        // Star picker for rating
        const starPicker = document.getElementById('starPicker');
        if (starPicker) {
            starPicker.querySelectorAll('.star').forEach(star => {
                star.addEventListener('click', function() {
                    selectedRating = parseInt(this.dataset.rating);
                    starPicker.querySelectorAll('.star').forEach((s, i) => {
                        s.classList.toggle('selected', i < selectedRating);
                    });
                });
                
                star.addEventListener('mouseenter', function() {
                    const hoverRating = parseInt(this.dataset.rating);
                    starPicker.querySelectorAll('.star').forEach((s, i) => {
                        s.style.color = i < hoverRating ? 'var(--warning)' : 'var(--text-muted)';
                    });
                });
            });
            
            starPicker.addEventListener('mouseleave', function() {
                starPicker.querySelectorAll('.star').forEach((s, i) => {
                    s.style.color = '';
                });
            });
        }
        
        // Submit rating
        function submitRating() {
            if (selectedRating < 1) {
                showToast('Pilih rating terlebih dahulu!');
                return;
            }
            
            fetch('<?php echo BASE_URL; ?>/rating_action.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=submit&game_id=${gameId}&rating=${selectedRating}&review=`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Rating berhasil disimpan!');
                    document.getElementById('avgRating').textContent = `${data.avg_rating} / 5.0 (${data.rating_count} rating)`;
                } else {
                    showToast(data.message || 'Gagal menyimpan rating');
                }
            })
            .catch(err => showToast('Terjadi kesalahan'));
        }
        
        // Report broken link
        function reportBrokenLink() {
            if (confirm('Laporkan link download rusak?')) {
                fetch('<?php echo BASE_URL; ?>/report_link.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'game_id=' + gameId
                }).then(r => r.json()).then(data => showToast(data.message || 'Laporan dikirim!'));
            }
        }
        
        // Copy link
        function copyLink() {
            navigator.clipboard.writeText('<?php echo $share_url; ?>');
            showToast('Link berhasil disalin!');
        }
        
        // Reply form toggle
        function toggleReplyForm(commentId) {
            const form = document.getElementById(`reply-form-${commentId}`);
            if (form) form.style.display = form.style.display === 'block' ? 'none' : 'block';
        }
        
        // View screenshot
        function viewImage(src) { window.open(src, '_blank'); }
        
        // Download tracking
        document.querySelectorAll('.btn-download, .download-part').forEach(link => {
            link.addEventListener('click', () => trackDownload(gameId));
        });
        
        // On load
        window.addEventListener('load', function() {
            // Hide loader
            const loader = document.getElementById('loadingOverlay');
            if (loader) { loader.style.opacity = '0'; setTimeout(() => loader.style.display = 'none', 500); }
            
            // Update follow button
            updateFollowButtonState();
        });
        
        // Navbar scroll
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
                if (clickCount >= 3) { window.location.href = '<?php echo BASE_URL; ?>/auth/login.php'; }
            });
        }
    </script>
</body>
</html>
