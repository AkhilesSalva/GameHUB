<?php
include '../config.php';
include 'cek_login.php'; 

$genres_query = mysqli_query($koneksi, "SELECT * FROM genre ORDER BY nama_genre ASC");
$genres = [];
while ($row = mysqli_fetch_assoc($genres_query)) {
    $genres[] = $row;
}

$nama_admin = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Administrator';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Game - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin_style.css">
    <style>
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
            align-items: flex-end;
        }
        .link-row input {
            flex: 1;
        }
        .platform-grid {
            display: flex;
            flex-wrap: wrap;
            gap: var(--space-sm);
        }
        .platform-item {
            padding: var(--space-sm) var(--space-md);
            background: var(--bg-elevated);
            border: 1px solid var(--border-dark);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        .platform-item:hover {
            border-color: var(--accent);
        }
        .platform-item input:checked + span {
            color: var(--accent);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-lg);
        }
        @media (max-width: 768px) {
            .form-row { grid-template-columns: 1fr; }
        }
        .section-title {
            color: var(--primary);
            border-bottom: 1px solid var(--border-dark);
            padding-bottom: var(--space-sm);
            margin-bottom: var(--space-lg);
            margin-top: var(--space-xl);
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
                    <li><a href="<?php echo BASE_URL; ?>/admin/tambah.php" class="active">➕ Tambah Game</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/komentar.php">💬 Komentar</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/genre.php">🏷️ Genre</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/users.php">👤 Users</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/requests.php">🎯 Requests</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/reports.php">🔗 Reports</a></li>
                </ul>
            </nav>
            <div class="logout-link">
                <p>👋 <?php echo htmlspecialchars($nama_admin); ?></p>
                <a href="<?php echo BASE_URL; ?>/logout.php" class="btn btn-danger btn-full">🚪 Logout</a>
            </div>
        </div>

        <div class="main-content">
            <h2>TAMBAH GAME BARU</h2>
            
            <a href="<?php echo BASE_URL; ?>/admin/daftar_game.php" class="btn btn-secondary" style="margin-bottom: var(--space-lg);">← Kembali ke Daftar</a>

            <div class="form-container">
                <form action="<?php echo BASE_URL; ?>/admin/aksi_crud.php" method="POST" enctype="multipart/form-data">
                    
                    <!-- BASIC INFO -->
                    <h3 class="section-title">📋 Informasi Dasar</h3>
                    
                    <div class="input-group">
                        <label>🎮 Nama Game *</label>
                        <input type="text" name="nama" placeholder="Masukkan nama game" required>
                    </div>
                    
                    <div class="input-group">
                        <label>📝 Deskripsi *</label>
                        <textarea name="deskripsi" placeholder="Deskripsi lengkap tentang game ini..." required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="input-group">
                            <label>📦 Versi Game</label>
                            <input type="text" name="version" placeholder="Contoh: v1.2.5, Build 2024">
                        </div>
                        <div class="input-group">
                            <label>💾 Ukuran File</label>
                            <input type="text" name="file_size" placeholder="Contoh: 5.2 GB, 800 MB">
                        </div>
                    </div>
                    
                    <!-- IMAGES -->
                    <h3 class="section-title">🖼️ Gambar</h3>
                    
                    <div class="form-row">
                        <div class="input-group">
                            <label>🖼️ Cover Image (Portrait) *</label>
                            <input type="file" name="gambar_cover" accept="image/*" required>
                            <small style="color: var(--text-muted);">Untuk card game, rasio 3:4</small>
                        </div>
                        <div class="input-group">
                            <label>🌄 Hero Image (Landscape) *</label>
                            <input type="file" name="hero_image" accept="image/*" required>
                            <small style="color: var(--text-muted);">Untuk slideshow, rasio 16:9</small>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label>📸 Screenshots (Multiple)</label>
                        <input type="file" name="screenshots[]" accept="image/*" multiple>
                        <small style="color: var(--text-muted);">Bisa pilih beberapa file sekaligus</small>
                    </div>
                    
                    <!-- PLATFORM & GENRE -->
                    <h3 class="section-title">🎯 Platform & Genre</h3>
                    
                    <div class="input-group">
                        <label>🖥️ Platform</label>
                        <div class="platform-grid">
                            <label class="platform-item">
                                <input type="checkbox" name="platforms[]" value="PC" checked>
                                <span>💻 PC</span>
                            </label>
                            <label class="platform-item">
                                <input type="checkbox" name="platforms[]" value="Android">
                                <span>📱 Android</span>
                            </label>
                            <label class="platform-item">
                                <input type="checkbox" name="platforms[]" value="PS4">
                                <span>🎮 PS4</span>
                            </label>
                            <label class="platform-item">
                                <input type="checkbox" name="platforms[]" value="PS5">
                                <span>🎮 PS5</span>
                            </label>
                            <label class="platform-item">
                                <input type="checkbox" name="platforms[]" value="Xbox">
                                <span>🎮 Xbox</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label>🏷️ Pilih Genre</label>
                        <div class="genre-grid">
                            <?php if (empty($genres)): ?>
                                <p style="color: var(--danger);">⚠️ Belum ada genre. <a href="<?php echo BASE_URL; ?>/admin/genre.php">Tambah genre dulu.</a></p>
                            <?php endif; ?>
                            <?php foreach ($genres as $genre): ?>
                                <label class="genre-item">
                                    <input type="checkbox" name="genres[]" value="<?php echo $genre['id']; ?>">
                                    <span><?php echo htmlspecialchars($genre['nama_genre']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- MEDIA -->
                    <h3 class="section-title">🎬 Media & Info Tambahan</h3>
                    
                    <div class="input-group">
                        <label>🎬 URL Trailer (YouTube)</label>
                        <input type="url" name="trailer_url" placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                    
                    <div class="form-row">
                        <div class="input-group">
                            <label>⚙️ System Requirements (Minimum)</label>
                            <textarea name="system_req_min" placeholder="OS: Windows 10&#10;CPU: Intel Core i5&#10;RAM: 8 GB&#10;GPU: GTX 1050"></textarea>
                        </div>
                        <div class="input-group">
                            <label>⚙️ System Requirements (Recommended)</label>
                            <textarea name="system_req_rec" placeholder="OS: Windows 11&#10;CPU: Intel Core i7&#10;RAM: 16 GB&#10;GPU: RTX 3060"></textarea>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label style="display: flex; align-items: center; gap: var(--space-sm);">
                            <input type="checkbox" name="coming_soon" id="coming_soon_check" value="1" style="width: auto;" onchange="toggleDownloadSection()">
                            <span>🔜 Coming Soon (Game belum tersedia untuk download)</span>
                        </label>
                        <small style="color: var(--text-muted); margin-top: var(--space-xs); display: block;">Jika dicentang, bagian download link akan disembunyikan</small>
                    </div>
                    
                    <!-- DOWNLOAD LINKS -->
                    <div id="download_section">
                    <h3 class="section-title">📥 Link Download</h3>

                    <div class="input-group">
                        <label>🔗 Tipe Link Download</label>
                        <select name="link_type" id="link_type" onchange="toggleLinkFields()" required>
                            <option value="single">Single Link (1 file)</option>
                            <option value="part">Multi-Part Link (beberapa file)</option>
                        </select>
                    </div>
                    
                    <div id="single_link_field" class="input-group">
                        <label>📥 Link Download (Single)</label>
                        <input type="url" name="link_single" id="link_single" placeholder="https://example.com/download-link">
                    </div>
                    
                    <div id="multi_link_fields" style="display:none;">
                        <p style="color: var(--primary); font-size: 0.9em; margin-bottom: var(--space-md);">
                            💡 Tambahkan link untuk setiap part yang tersedia
                        </p>
                        <div id="dynamic_links_container">
                            <div class="dynamic-input">
                                <label>Link Part 1</label>
                                <input type="url" name="link_part[]" id="link_part_1" placeholder="https://example.com/part-1">
                            </div>
                        </div>
                        <button type="button" onclick="addLinkPart()" class="btn btn-secondary" style="margin-top: var(--space-sm);">
                            ➕ Tambah Part
                        </button>
                    </div>
                    </div>
                    
                    <button type="submit" name="tambah_game" class="btn btn-primary btn-full" style="margin-top: var(--space-xl); padding: var(--space-md);">
                        💾 SIMPAN GAME
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
                // Also remove required from link fields
                document.getElementById('link_single').required = false;
            } else {
                downloadSection.style.display = 'block';
                toggleLinkFields(); // Re-apply link type logic
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
            const loader = document.getElementById('loadingOverlay');
            if (loader) {
                loader.style.opacity = '0';
                setTimeout(() => loader.style.display = 'none', 500);
            }
        });
    </script>
</body>
</html>