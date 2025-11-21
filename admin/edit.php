<?php
// PASTIKAN config.php di-include DULU
include '../config.php';
// Baru panggil cek_login.php
include 'cek_login.php'; 

// Cek Role Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

// --- Logika PHP untuk mengambil data game dan genre ---
$id_game = (int)$_GET['id'];

// Ambil data game utama
$game_q = mysqli_query($koneksi, "SELECT * FROM games WHERE id='$id_game'");
$game = mysqli_fetch_assoc($game_q);

// Jika game tidak ditemukan
if (!$game) {
    header("Location: " . BASE_URL . "/admin/daftar_game.php?pesan=game_not_found"); // Diarahkan ke daftar_game.php
    exit();
}

// Ambil semua genre
$genres_query = mysqli_query($koneksi, "SELECT * FROM genre ORDER BY nama_genre ASC");
$genres = [];
while ($row = mysqli_fetch_assoc($genres_query)) {
    $genres[] = $row;
}

// Ambil genre yang sudah dipilih untuk game ini
$selected_genres_q = mysqli_query($koneksi, "SELECT genre_id FROM game_genre WHERE game_id='$id_game'");
$selected_genres_ids = [];
while ($row = mysqli_fetch_assoc($selected_genres_q)) {
    $selected_genres_ids[] = $row['genre_id'];
}

// Persiapan Link Part
$link_parts_array = [];
if ($game['link_type'] == 'part' && !empty($game['link_single'])) {
    $link_parts_array = explode(',', $game['link_single']);
}

$nama_admin = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Administrator';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Game: <?php echo htmlspecialchars($game['nama']); ?></title>
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>/assets/css/admin_style.css">

    <script>
        function toggleLinkFields() {
            const type = document.getElementById('link_type').value;
            const single = document.getElementById('single_link_field');
            const multi = document.getElementById('multi_link_fields');
            
            if (type === 'part') {
                single.style.display = 'none';
                multi.style.display = 'block';
                document.getElementById('link_single').required = false;
                const part1 = document.getElementById('link_part_1');
                if (part1) part1.required = true;

            } else {
                single.style.display = 'block';
                multi.style.display = 'none';
                document.getElementById('link_single').required = true;
                const part1 = document.getElementById('link_part_1');
                if (part1) part1.required = false;
            }
        }

        function addLinkPart() {
            const container = document.getElementById('dynamic_links_container');
            const div = document.createElement('div');
            div.classList.add('input-group', 'dynamic-input');
            
            let newPartNumber = container.querySelectorAll('.dynamic-input').length + 1;

            div.innerHTML = `
                <label for="link_part_${newPartNumber}">Link Part ${newPartNumber}:</label>
                <div style="display: flex; gap: 10px;">
                    <input type="url" name="link_part[]" id="link_part_${newPartNumber}" placeholder="URL Part ${newPartNumber}" required>
                    <button type="button" class="btn btn-danger btn-remove" onclick="this.closest('.dynamic-input').remove()">Hapus</button>
                </div>
            `;
            container.appendChild(div);
        }
        
        window.onload = function() {
            toggleLinkFields();
            hideLoader(); 
        }

        function hideLoader() {
            const loader = document.getElementById('loadingOverlay');
            if (loader) {
                loader.style.opacity = '0';
                setTimeout(() => { loader.style.display = 'none'; }, 500); 
            }
        }
    </script>
</head>
<body>
    <div id="loadingOverlay" class="loading-overlay"><div class="loader"></div></div>

    <div class="admin-wrapper">
        <div class="sidebar">
            <h2>GAME HUB</h2>
            <nav>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/admin/index.php">Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/daftar_game.php" class="active">Daftar Game</a></li> 
                    <li><a href="<?php echo BASE_URL; ?>/admin/tambah.php">Tambah Game</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/komentar.php">Kelola Komentar</a></li> 
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
            <h2>EDIT GAME: <?php echo htmlspecialchars($game['nama']); ?></h2>
            
            <a href="<?php echo BASE_URL; ?>/admin/daftar_game.php" class="btn btn-secondary" style="margin-bottom: 20px;">Kembali ke Daftar Game</a>

            <div class="form-container">
                <form action="<?php echo BASE_URL; ?>/admin/aksi_crud.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_game" value="<?php echo $game['id']; ?>">
                    <input type="hidden" name="edit_game" value="1">
                    
                    <div class="input-group">
                        <label>Nama Game:</label>
                        <input type="text" name="nama" value="<?php echo htmlspecialchars($game['nama']); ?>" required>
                    </div>
                    <div class="input-group">
                        <label>Deskripsi:</label>
                        <textarea name="deskripsi" required><?php echo htmlspecialchars($game['deskripsi']); ?></textarea>
                    </div>
                    
                    <div style="display: flex; flex-wrap: wrap; gap: 30px; margin-bottom: 30px; padding-top: 15px;">
                        <div>
                            <label>Cover Saat Ini:</label>
                            <img src="<?php echo BASE_URL . '/' . htmlspecialchars($game['gambar_path']); ?>" style="max-width: 150px; height: auto; border-radius: 8px;">
                        </div>
                        <div>
                            <label>Hero Image Saat Ini:</label>
                            <img src="<?php echo BASE_URL . '/' . htmlspecialchars($game['hero_image_path']); ?>" style="max-width: 250px; height: auto; border-radius: 8px;">
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Ganti Cover Gambar (Kosongkan jika tidak diubah):</label>
                        <input type="file" name="gambar_cover"> 
                    </div>
                    <div class="input-group">
                        <label>Ganti Hero Image (Kosongkan jika tidak diubah):</label>
                        <input type="file" name="hero_image"> 
                    </div>
                    
                    <div class="input-group">
                        <label>Pilih Genre:</label>
                        <div style="display: flex; flex-wrap: wrap; gap: 15px; background-color: #2a3c58; padding: 10px; border-radius: 5px;">
                            <?php foreach ($genres as $genre): ?>
                                <label style="display: flex; align-items: center; gap: 5px; color: #e4e6eb;">
                                    <input type="checkbox" name="genres[]" value="<?php echo $genre['id']; ?>"
                                        <?php echo in_array($genre['id'], $selected_genres_ids) ? 'checked' : ''; ?>>
                                    <?php echo htmlspecialchars($genre['nama_genre']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Tipe Link:</label>
                        <select name="link_type" id="link_type" onchange="toggleLinkFields()" required>
                            <option value="single" <?php echo $game['link_type'] == 'single' ? 'selected' : ''; ?>>Single Link</option>
                            <option value="part" <?php echo $game['link_type'] == 'part' ? 'selected' : ''; ?>>Multi-Part Link</option>
                        </select>
                    </div>
                    
                    <div id="single_link_field" class="input-group">
                        <label>Link Download (Single):</label>
                        <input type="url" name="link_single" id="link_single" placeholder="Masukkan URL Download Tunggal" value="<?php echo $game['link_type'] == 'single' ? htmlspecialchars($game['link_single']) : ''; ?>"> 
                    </div>
                    
                    <div id="multi_link_fields" style="display:none;" class="input-group">
                        <p style="color: #4CAF50; font-style: italic; margin-bottom: 15px;">*Anda dapat menambahkan link sebanyak part yang dibutuhkan.</p>
                        <div id="dynamic_links_container">
                            <?php if ($game['link_type'] == 'part' && !empty($link_parts_array)): ?>
                                <?php foreach ($link_parts_array as $index => $link): ?>
                                <div class="input-group dynamic-input">
                                    <label for="link_part_<?php echo $index + 1; ?>">Link Part <?php echo $index + 1; ?>:</label>
                                    <div style="display: flex; gap: 10px;">
                                        <input type="url" name="link_part[]" id="link_part_<?php echo $index + 1; ?>" placeholder="URL Part <?php echo $index + 1; ?>" value="<?php echo htmlspecialchars($link); ?>" required>
                                        <button type="button" class="btn btn-danger btn-remove" onclick="this.closest('.dynamic-input').remove()">Hapus</button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="input-group dynamic-input">
                                    <label for="link_part_1">Link Part 1:</label>
                                    <div style="display: flex; gap: 10px;">
                                        <input type="url" name="link_part[]" id="link_part_1" placeholder="URL Part 1">
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <button type="button" onclick="addLinkPart()" class="btn btn-primary" style="padding: 8px 15px; margin-top: 5px;">+ Tambah Part Link</button>
                    </div>
                    <button type="submit" name="edit_game" class="btn btn-primary btn-full" style="margin-top: 30px;">UPDATE DATA GAME</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>