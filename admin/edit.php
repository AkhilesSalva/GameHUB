<?php
include '../config.php';
include 'cek_login.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$id_game = (int)$_GET['id'];

$game_q = mysqli_query($koneksi, "SELECT * FROM games WHERE id='$id_game'");
$game = mysqli_fetch_assoc($game_q);

if (!$game) {
    header("Location: " . BASE_URL . "/admin/daftar_game.php?pesan=game_not_found");
    exit();
}

$genres_query = mysqli_query($koneksi, "SELECT * FROM genre ORDER BY nama_genre ASC");
$genres = [];
while ($row = mysqli_fetch_assoc($genres_query)) {
    $genres[] = $row;
}

$selected_genres_q = mysqli_query($koneksi, "SELECT genre_id FROM game_genre WHERE game_id='$id_game'");
$selected_genres_ids = [];
while ($row = mysqli_fetch_assoc($selected_genres_q)) {
    $selected_genres_ids[] = $row['genre_id'];
}

$link_parts_array = [];
if ($game['link_type'] == 'part' && !empty($game['link_single'])) {
    $link_parts_array = explode(',', $game['link_single']);
}

$nama_admin = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Administrator';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit: <?php echo htmlspecialchars($game['nama']); ?> - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin_style.css">
    <style>
        .image-preview-grid {
            display: flex;
            flex-wrap: wrap;
            gap: var(--space-xl);
            margin-bottom: var(--space-xl);
            padding: var(--space-md);
            background: var(--bg-elevated);
            border-radius: var(--radius-md);
            border: 1px solid var(--border-dark);
        }
        .image-preview-item {
            text-align: center;
        }
        .image-preview-item label {
            display: block;
            margin-bottom: var(--space-sm);
            color: var(--text-muted);
            font-size: 0.85em;
        }
        .image-preview-item img {
            border-radius: var(--radius-md);
            border: 2px solid var(--border-light);
            box-shadow: var(--shadow-sm);
        }
        .genre-grid {
            display: flex;
            flex-wrap: wrap;
            gap: var(--space-sm);
            background: var(--bg-elevated);
            padding: var(--space-md);
            border-radius: var(--radius-md);
            border: 1px solid var(--border-dark);
        }
        .genre-item {
            display: flex;
            align-items: center;
            gap: var(--space-xs);
            padding: var(--space-sm) var(--space-md);
            background: var(--bg-card);
            border: 1px solid var(--border-dark);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        .genre-item:hover {
            border-color: var(--primary);
        }
        .genre-item input:checked + span {
            color: var(--primary);
        }
        .genre-item input {
            accent-color: var(--primary);
        }
        .dynamic-input {
            background: var(--bg-elevated);
            padding: var(--space-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-md);
            border: 1px solid var(--border-dark);
        }
        .dynamic-input label {
            font-size: 0.9em;
            margin-bottom: var(--space-sm);
            display: block;
            color: var(--accent);
        }
        .link-row {
            display: flex;
            gap: var(--space-md);
            align-items: center;
        }
        .link-row input {
            flex: 1;
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
                    <li><a href="<?php echo BASE_URL; ?>/admin/daftar_game.php" class="active">🎮 Daftar Game</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/tambah.php">➕ Tambah Game</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/komentar.php">💬 Komentar</a></li>
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
            <h2>EDIT GAME</h2>
            
            <a href="<?php echo BASE_URL; ?>/admin/daftar_game.php" class="btn btn-secondary" style="margin-bottom: var(--space-lg);">← Kembali ke Daftar</a>

            <div class="form-container">
                <form action="<?php echo BASE_URL; ?>/admin/aksi_crud.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_game" value="<?php echo $game['id']; ?>">
                    <input type="hidden" name="edit_game" value="1">
                    
                    <div class="input-group">
                        <label>🎮 Nama Game</label>
                        <input type="text" name="nama" value="<?php echo htmlspecialchars($game['nama']); ?>" required>
                    </div>
                    
                    <div class="input-group">
                        <label>📝 Deskripsi</label>
                        <textarea name="deskripsi" required><?php echo htmlspecialchars($game['deskripsi']); ?></textarea>
                    </div>
                    
                    <label style="color: var(--accent); display: block; margin-bottom: var(--space-sm);">📸 Gambar Saat Ini</label>
                    <div class="image-preview-grid">
                        <div class="image-preview-item">
                            <label>Cover (Portrait)</label>
                            <img src="<?php echo BASE_URL . '/' . htmlspecialchars($game['gambar_path']); ?>" style="max-width: 120px; max-height: 160px;">
                        </div>
                        <div class="image-preview-item">
                            <label>Hero Image (Landscape)</label>
                            <img src="<?php echo BASE_URL . '/' . htmlspecialchars($game['hero_image_path']); ?>" style="max-width: 250px; max-height: 140px;">
                        </div>
                    </div>

                    <div class="input-group">
                        <label>🖼️ Ganti Cover (kosongkan jika tidak diubah)</label>
                        <input type="file" name="gambar_cover" accept="image/*">
                    </div>
                    
                    <div class="input-group">
                        <label>🌄 Ganti Hero Image (kosongkan jika tidak diubah)</label>
                        <input type="file" name="hero_image" accept="image/*">
                    </div>
                    
                    <div class="input-group">
                        <label>🏷️ Pilih Genre</label>
                        <div class="genre-grid">
                            <?php foreach ($genres as $genre): ?>
                                <label class="genre-item">
                                    <input type="checkbox" name="genres[]" value="<?php echo $genre['id']; ?>"
                                        <?php echo in_array($genre['id'], $selected_genres_ids) ? 'checked' : ''; ?>>
                                    <span><?php echo htmlspecialchars($genre['nama_genre']); ?></span>
                                </label>
                            <?php endforeach; ?>
                                                </div>
                    </div>
                    
                    <!-- Version & File Size -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-lg); margin-top: var(--space-lg);">
                        <div class="input-group">
                            <label>📦 Versi Game</label>
                            <input type="text" name="version" value="<?php echo htmlspecialchars($game['version'] ?? ''); ?>" placeholder="Contoh: v1.2.5">
                        </div>
                        <div class="input-group">
                            <label>💾 Ukuran File</label>
                            <input type="text" name="file_size" value="<?php echo htmlspecialchars($game['file_size'] ?? ''); ?>" placeholder="Contoh: 5.2 GB">
                        </div>
                    </div>
                    
                    <!-- Trailer URL -->
                    <div class="input-group">
                        <label>🎬 URL Trailer (YouTube)</label>
                        <input type="url" name="trailer_url" value="<?php echo htmlspecialchars($game['trailer_url'] ?? ''); ?>" placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                    
                    <!-- System Requirements -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-lg);">
                        <div class="input-group">
                            <label>⚙️ System Requirements (Minimum)</label>
                            <textarea name="system_req_min" placeholder="OS: Windows 10..."><?php echo htmlspecialchars($game['system_req_min'] ?? ''); ?></textarea>
                        </div>
                        <div class="input-group">
                            <label>⚙️ System Requirements (Recommended)</label>
                            <textarea name="system_req_rec" placeholder="OS: Windows 11..."><?php echo htmlspecialchars($game['system_req_rec'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Coming Soon Toggle -->
                    <div class="input-group">
                        <label style="display: flex; align-items: center; gap: var(--space-sm);">
                            <input type="checkbox" name="coming_soon" id="coming_soon_check" value="1" style="width: auto;" <?php echo ($game['coming_soon'] ?? 0) == 1 ? 'checked' : ''; ?> onchange="toggleDownloadSection()">
                            <span>🔜 Coming Soon (Game belum tersedia untuk download)</span>
                        </label>
                        <small style="color: var(--text-muted);">Jika dicentang, bagian download link disembunyikan</small>
                    </div>

                    <!-- DOWNLOAD SECTION -->
                    <div id="download_section">
                    <div class="input-group">
                        <label>🔗 Tipe Link Download</label>
                        <select name="link_type" id="link_type" onchange="toggleLinkFields()" required>
                            <option value="single" <?php echo $game['link_type'] == 'single' ? 'selected' : ''; ?>>Single Link (1 file)</option>
                            <option value="part" <?php echo $game['link_type'] == 'part' ? 'selected' : ''; ?>>Multi-Part Link (beberapa file)</option>
                        </select>
                    </div>
                    
                    <div id="single_link_field" class="input-group">
                        <label>📥 Link Download (Single)</label>
                        <input type="url" name="link_single" id="link_single" placeholder="https://example.com/download-link" 
                               value="<?php echo $game['link_type'] == 'single' ? htmlspecialchars($game['link_single']) : ''; ?>">
                    </div>
                    
                    <div id="multi_link_fields" style="display:none;">
                        <p style="color: var(--primary); font-size: 0.9em; margin-bottom: var(--space-md);">
                            💡 Tambahkan link untuk setiap part yang tersedia
                        </p>
                        <div id="dynamic_links_container">
                            <?php if ($game['link_type'] == 'part' && !empty($link_parts_array)): ?>
                                <?php foreach ($link_parts_array as $index => $link): ?>
                                <div class="dynamic-input">
                                    <label>Link Part <?php echo $index + 1; ?></label>
                                    <div class="link-row">
                                        <input type="url" name="link_part[]" value="<?php echo htmlspecialchars($link); ?>" placeholder="https://example.com/part-<?php echo $index + 1; ?>">
                                        <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.dynamic-input').remove()">🗑️</button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="dynamic-input">
                                    <label>Link Part 1</label>
                                    <input type="url" name="link_part[]" placeholder="https://example.com/part-1">
                                </div>
                            <?php endif; ?>
                        </div>
                        <button type="button" onclick="addLinkPart()" class="btn btn-secondary" style="margin-top: var(--space-sm);">
                            ➕ Tambah Part
                        </button>
                    </div>
                    
                    </div>
                    
                    </div>
                    
                    <button type="submit" name="edit_game" class="btn btn-primary btn-full" style="margin-top: var(--space-xl); padding: var(--space-md);">
                        💾 UPDATE GAME
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function toggleLinkFields() {
            const type = document.getElementById('link_type').value;
            const single = document.getElementById('single_link_field');
            const multi = document.getElementById('multi_link_fields');
            
            if (type === 'part') {
                single.style.display = 'none';
                multi.style.display = 'block';
                document.getElementById('link_single').required = false;
            } else {
                single.style.display = 'block';
                multi.style.display = 'none';
                document.getElementById('link_single').required = true;
            }
        }

        function toggleDownloadSection() {
            const isComingSoon = document.getElementById('coming_soon_check').checked;
            const downloadSection = document.getElementById('download_section');
            if (isComingSoon) {
                downloadSection.style.display = 'none';
                document.getElementById('link_single').required = false;
            } else {
                downloadSection.style.display = 'block';
                toggleLinkFields();
            }
        }

        function addLinkPart() {
            const container = document.getElementById('dynamic_links_container');
            const count = container.querySelectorAll('.dynamic-input').length + 1;
            
            const div = document.createElement('div');
            div.classList.add('dynamic-input');
            div.innerHTML = `
                <label>Link Part ${count}</label>
                <div class="link-row">
                    <input type="url" name="link_part[]" placeholder="https://example.com/part-${count}">
                    <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.dynamic-input').remove()">🗑️</button>
                </div>
            `;
            container.appendChild(div);
        }
        
        window.addEventListener('load', function() {
            toggleLinkFields();
            toggleDownloadSection();
            const loader = document.getElementById('loadingOverlay');
            if (loader) {
                loader.style.opacity = '0';
                setTimeout(() => loader.style.display = 'none', 500);
            }
        });
    </script>
</body>
</html>