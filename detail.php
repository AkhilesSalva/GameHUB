<?php
session_start();
include 'config.php';

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

// Cek Status User & Admin untuk Komentar
$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$is_admin = $is_logged_in && ($_SESSION['role'] === 'admin');


// =================================================================
// LOGIKA SUBMIT KOMENTAR BARU
// =================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : 0;
    $comment_text = mysqli_real_escape_string($koneksi, trim($_POST['comment_text']));
    $user_id = $is_logged_in ? (int)$_SESSION['user_id'] : 0;
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

// =================================================================
// LOGIKA AMBIL KOMENTAR
// =================================================================
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

// Navigasi Display Name
$username_display = $is_logged_in ? ($_SESSION['username'] ?? 'User') : '';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($game['nama']); ?> - Game Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* ========================================================================= */
        /* 1. CSS UTILITIES & BASE */
        /* ========================================================================= */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { line-height: 1.6; background-color: #0f0f1a; color: #e4e6eb; }
        a { text-decoration: none; color: #00bcd4; transition: color 0.3s; }
        a:hover { color: #4CAF50; }
        .container-content { max-width: 1200px; margin: 0 auto; padding: 0 40px; }
        
        /* ========================================================================= */
        /* 2. NAVBAR (Dropdown Styling) */
        /* ========================================================================= */
        .public-navbar { width: 100%; background-color: #11111d; padding: 10px 0; box-shadow: 0 4px 15px rgba(0,0,0,0.5); position: sticky; top: 0; z-index: 1000; }
        .navbar-content { max-width: 1600px; margin: 0 auto; padding: 0 40px; display: flex; justify-content: space-between; align-items: center; }
        .navbar-brand { color: #4CAF50; font-size: 1.8em; font-weight: 700; text-shadow: 0 0 5px #4CAF50; }
        .nav-right { display: flex; align-items: center; gap: 30px; }
        .nav-links { display: flex; gap: 30px; }
        .nav-links a { color: #b0b0b0; font-weight: 500; padding: 5px 0; transition: color 0.2s; border-bottom: 2px solid transparent; }
        .nav-links a:hover { color: white; }
        .btn-action { background-color: #4CAF50; color: white; padding: 8px 15px; border-radius: 5px; font-weight: 500; transition: background-color 0.3s; }
        .btn-action:hover { background-color: #45a049; color: white; }
        
        /* Dropdown Admin/User */
        .user-menu-container { position: relative; cursor: pointer; z-index: 1001; padding: 5px 0; }
        .profile-btn { background-color: #4CAF50; color: white; padding: 8px 15px; border-radius: 5px; font-weight: 500; display: flex; align-items: center; gap: 8px; }
        .profile-btn::after { content: '▼'; font-size: 0.7em; transition: transform 0.3s; }
        .user-menu { position: absolute; top: 100%; right: 0; background-color: #1a1a2e; border: 1px solid #2a3c58; border-radius: 8px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5); min-width: 180px; padding: 10px 0; display: none; }
        .user-menu a { display: block; padding: 10px 15px; color: #e4e6eb; font-weight: 400; border-bottom: none; }
        .user-menu a:hover { background-color: #2a3c58; color: white; }
        .user-menu-container:hover .user-menu { display: block; }
        .menu-separator { border-top: 1px solid #2a3c58; margin: 5px 0; }

        /* ========================================================================= */
        /* 3. DETAIL PAGE STYLING */
        /* ========================================================================= */
        .detail-hero { height: 50vh; width: 100%; background-size: cover; background-position: center; position: relative; }
        .detail-hero-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(to top, rgba(15, 15, 26, 1) 5%, rgba(15, 15, 26, 0.5) 50%, transparent 100%); }
        .detail-page-content { margin-top: -150px; position: relative; z-index: 10; }
        .detail-header { display: flex; align-items: flex-end; gap: 40px; margin-bottom: 50px; }
        .detail-cover { width: 250px; height: 350px; object-fit: cover; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.7); flex-shrink: 0; }
        .detail-info h1 { font-size: 3em; font-weight: 700; color: white; text-shadow: 2px 2px 10px rgba(0,0,0,0.5); margin-bottom: 10px; }
        .detail-stats { color: #b0b0b0; margin-bottom: 25px; }

        /* SINGLE DOWNLOAD BUTTON */
        .btn-play {
            display: inline-block; background-color: #4CAF50; color: white; padding: 15px 40px;
            font-size: 1.2em; font-weight: bold; border-radius: 8px; transition: transform 0.2s, background-color 0.3s;
        }
        .btn-play:hover { background-color: #5cc460; transform: scale(1.05); color: white; }

        /* MULTI-PART DOWNLOAD STYLING BARU */
        .download-action { margin-top: 20px; max-width: 400px; }
        .download-action h4 { margin-bottom: 15px; color: #00bcd4; font-size: 1.1em; }
        .download-list { display: flex; flex-direction: column; gap: 10px; }
        
        .btn-download-part {
            background-color: #1e293b; 
            color: white; 
            padding: 15px 20px;
            font-size: 1em; 
            font-weight: 500; 
            border-radius: 8px; 
            transition: all 0.3s ease;
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            border-left: 5px solid #4CAF50;
        }
        .btn-download-part:hover { 
            background-color: #2a3c58;
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            border-left-color: #00bcd4;
        }
        .btn-download-part span { font-weight: bold; font-size: 1.1em; }
        .btn-download-part small { font-weight: normal; font-size: 0.8em; color: #b0b0b0; }


        .detail-description { background-color: #1c1c2b; padding: 40px; border-radius: 12px; margin-bottom: 40px; }
        .detail-description h3 { font-size: 1.8em; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #2a3c58; color: white; }
        .detail-description p { color: #b0b0b0; font-size: 1.1em; line-height: 1.8; }

        /* ========================================================================= */
        /* 4. COMMENT SECTION STYLING (PERBAIKAN FORM REPLY) */
        /* ========================================================================= */
        .comment-section { padding: 40px; background-color: #1c1c2b; border-radius: 12px; }
        .comment-section h3 { font-size: 1.8em; margin-bottom: 25px; padding-bottom: 10px; border-bottom: 2px solid #2a3c58; color: white; }
        .comment-form input[type="text"], 
        .comment-form textarea { 
            width: 100%; padding: 15px; margin-bottom: 15px; border: 1px solid #2a3c58; 
            border-radius: 6px; background-color: #0f0f1a; color: #e4e6eb; font-size: 1em; 
        }
        .comment-form textarea { resize: vertical; min-height: 100px; }
        .comment-form button {
            background-color: #00bcd4; color: white; padding: 10px 20px; border: none;
            border-radius: 6px; cursor: pointer; font-weight: bold; transition: background-color 0.3s;
        }
        .comment-form button:hover { background-color: #0097a7; }

        /* Style Form Reply Admin */
        .reply-form form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .reply-form textarea {
            min-height: 60px; /* Lebih kecil untuk reply */
            padding: 10px;
            margin-bottom: 0;
            background-color: #1a1a2e; /* Kontras dengan form area utama */
            border: 1px solid #4CAF50;
        }
        .reply-form button {
            background-color: #4CAF50; /* Tombol Balas pakai warna Admin */
            width: fit-content;
            padding: 8px 15px;
        }
        .reply-form button:hover {
            background-color: #5cc460;
        }

        /* List Komentar Styling */
        .comment-list { margin-top: 30px; }
        .comment-item { border-left: 3px solid #00bcd4; padding: 15px; margin-bottom: 20px; background-color: #11111d; transition: all 0.3s; border-radius: 8px; }
        .comment-item.admin-reply { margin-left: 5%; border-left: none; border-right: 3px solid #4CAF50; background-color: #1e293b; }
        .replies .comment-item { margin-left: 20px; padding: 15px; background-color: #1e293b; border-left: 3px solid #00bcd4; border-right: none; }
        .replies .comment-item.admin-reply { margin-left: 0; }
        .comment-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .comment-author { font-weight: bold; color: #00bcd4; }
        .comment-author.admin { color: #4CAF50; text-transform: uppercase; }
        .comment-date { font-size: 0.8em; color: #8c98a3; }
        .comment-body { color: #e4e6eb; margin-bottom: 10px; }
        .comment-reply-btn { background: none; border: none; color: #4CAF50; cursor: pointer; font-size: 0.9em; font-weight: 500; margin-bottom: 10px; }
        .replies { margin-left: 30px; margin-top: 15px; border-left: 2px dashed #2a3c58; padding-left: 15px; }
        .reply-form { background-color: #0f0f1a; padding: 15px; border-radius: 6px; margin-top: 10px; display: none; }
    </style>
</head>
<body onload="hideLoader()">
    <nav class="public-navbar">
        <div class="navbar-content">
            <a href="<?php echo BASE_URL; ?>" class="navbar-brand">GAMEHUB</a>
            <div class="nav-right">
                <div class="nav-links">
                    <a href="<?php echo BASE_URL; ?>/index.php">Beranda</a>
                    <a href="<?php echo BASE_URL; ?>/semua_game.php">Semua Game</a>
                </div>
                
                <?php if ($is_logged_in): ?>
                    <div class="user-menu-container">
                        <div class="profile-btn">
                            <?php echo htmlspecialchars($username_display); ?>
                        </div>
                        <div class="user-menu">
                            <?php if ($is_admin): ?>
                                <a href="<?php echo BASE_URL; ?>/admin/index.php">Admin Console</a>
                                <div class="menu-separator"></div>
                            <?php else: ?>
                                <a href="<?php echo BASE_URL; ?>/koleksiku.php">Koleksiku</a>
                                <div class="menu-separator"></div>
                            <?php endif; ?>
                            <a href="<?php echo BASE_URL; ?>/logout.php">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/login.php" class="btn-action">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="detail-hero" style="background-image: url('<?php echo BASE_URL . '/' . htmlspecialchars($game['hero_image_path']); ?>');">
        <div class="detail-hero-overlay"></div>
    </div>

    <div class="container-content detail-page-content">
        <div class="detail-header">
            <img src="<?php echo BASE_URL . '/' . htmlspecialchars($game['gambar_path']); ?>" alt="Cover <?php echo htmlspecialchars($game['nama']); ?>" class="detail-cover">
            <div class="detail-info">
                <h1><?php echo htmlspecialchars($game['nama']); ?></h1>
                <p class="detail-stats">Downloads: <?php echo number_format($game['download_count']); ?> | Dilihat: <?php echo number_format($game['view_count']); ?></p>

                <div class="download-action">
                <?php if ($game['link_type'] == 'single'): ?>
                    <a href="<?php echo BASE_URL . '/download.php?id=' . $game['id']; ?>" class="btn-play">DOWNLOAD SEKARANG</a>
                <?php else: 
                    // Opsi Multi-Part Link
                    $link_parts = explode(',', $game['link_single']);
                    ?>
                    <h4>Pilih Part Download:</h4>
                    <div class="download-list">
                        <?php 
                        if (!empty($link_parts) && !empty(trim($link_parts[0]))):
                            foreach ($link_parts as $index => $link):
                        ?>
                        <a href="<?php echo BASE_URL . '/download.php?id=' . $game['id'] . '&part=' . ($index + 1); ?>" class="btn-download-part" target="_blank">
                            <span>PART <?php echo $index + 1; ?></span>
                            <small>Klik untuk Mengunduh</small>
                        </a>
                        <?php endforeach; 
                        else: ?>
                        <p style="color: #f44336; font-weight: bold;">⚠️ Link Multi-Part belum tersedia atau belum diatur.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                </div>

            </div>
        </div>
        
        <div class="detail-description">
            <h3>Deskripsi Game</h3>
            <p><?php echo nl2br(htmlspecialchars($game['deskripsi'])); ?></p>
        </div>
        
        <div class="comment-section" id="comments">
            <h3>Komentar dan Diskusi</h3>

            <div class="comment-form">
                <h4>Tinggalkan Komentar</h4>
                <form method="POST" action="detail.php?id=<?php echo $game['id']; ?>#comments">
                    <input type="hidden" name="submit_comment" value="1">
                    <input type="hidden" name="parent_id" value="0">
                    
                    <?php if (!$is_logged_in): ?>
                        <input type="text" name="guest_username" placeholder="Nama Kamu (Opsional)">
                    <?php endif; ?>

                    <textarea name="comment_text" placeholder="Tulis komentar kamu di sini..." required></textarea>
                    <button type="submit">Kirim Komentar</button>
                </form>
            </div>

            <div class="comment-list">
                <?php if (!empty($comments)): ?>
                    <?php foreach ($comments as $comment): ?>
                        <?php 
                            $is_comment_admin = $comment['is_admin_reply'] == 1;
                            $author_class = $is_comment_admin ? 'admin' : '';
                            $reply_style_class = $comment['parent_id'] == 0 && $is_comment_admin ? 'admin-reply' : '';
                        ?>
                        <div class="comment-item <?php echo $reply_style_class; ?>" id="comment-<?php echo $comment['id']; ?>">
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
                            <button class="comment-reply-btn" onclick="toggleReplyForm(<?php echo $comment['id']; ?>)">Balas</button>
                            <?php endif; ?>

                            <?php if ($is_admin): ?>
                            <div class="reply-form" id="reply-form-<?php echo $comment['id']; ?>">
                                <form method="POST" action="detail.php?id=<?php echo $game['id']; ?>#comment-<?php echo $comment['id']; ?>">
                                    <input type="hidden" name="submit_comment" value="1">
                                    <input type="hidden" name="parent_id" value="<?php echo $comment['id']; ?>">
                                    <textarea name="comment_text" placeholder="Balasan Admin..." required></textarea>
                                    <button type="submit">Kirim Balasan</button>
                                </form>
                            </div>
                            <?php endif; ?>
                            
                            <div class="replies">
                                <?php foreach (get_replies($koneksi, $comment['id']) as $reply): ?>
                                    <?php 
                                        $is_reply_admin = $reply['is_admin_reply'] == 1;
                                        $author_class_reply = $is_reply_admin ? 'admin' : '';
                                        $reply_style_class = $is_reply_admin ? 'admin-reply' : '';
                                    ?>
                                    <div class="comment-item <?php echo $reply_style_class; ?>" 
                                         style="border-left: 3px solid #00bcd4; background-color: #1e293b;">
                                        <div class="comment-header">
                                            <span class="comment-author <?php echo $author_class_reply; ?>">
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
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #8c98a3;">Belum ada komentar. Jadilah yang pertama berkomentar!</p>
                <?php endif; ?>
            </div>
        </div>
    </div> 

    <script>
        function hideLoader() {
            // Fungsi ini biasanya hanya ada di halaman yang ada loading overlay
        }

        function toggleReplyForm(commentId) {
            const form = document.getElementById(`reply-form-${commentId}`);
            if (form) {
                if (form.style.display === 'block') {
                    form.style.display = 'none';
                } else {
                    form.style.display = 'block';
                }
            }
        }
    </script>
</body>
</html>