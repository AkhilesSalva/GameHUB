<?php
include '../config.php';
include 'cek_login.php'; 

// Ambil semua genre dari database
$genres_query = mysqli_query($koneksi, "SELECT * FROM genre ORDER BY nama_genre ASC");
$genres = [];
while ($row = mysqli_fetch_assoc($genres_query)) {
    $genres[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Game Baru</title>
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
                    <li><a href="<?php echo BASE_URL; ?>/admin/daftar_game.php">Daftar Game</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/tambah.php" class="active">Tambah Game</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/komentar.php">Kelola Komentar</a></li> 
                    <li><a href="<?php echo BASE_URL; ?>/admin/genre.php">Kelola Genre</a></li> 
                    <li><a href="<?php echo BASE_URL; ?>/admin/users.php">Kelola User</a></li>
                </ul>
            </nav>
            <div class="logout-link">
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></p>
                <a href="<?php echo BASE_URL; ?>/logout.php" class="btn btn-danger btn-full">Logout</a>
            </div>
        </div>
        <div class="main-content">
            <h2>TAMBAH GAME BARU</h2>
            
            <a href="<?php echo BASE_URL; ?>/admin/daftar_game.php" class="btn btn-secondary" style="margin-bottom: 20px;">Kembali ke Daftar Game</a>

            <div class="form-container">
                <form action="<?php echo BASE_URL; ?>/admin/aksi_crud.php" method="POST" enctype="multipart/form-data">
                    <div class="input-group">
                        <label>Nama Game:</label>
                        <input type="text" name="nama" required>
                    </div>
                    <div class="input-group">
                        <label>Deskripsi:</label>
                        <textarea name="deskripsi" required></textarea>
                    </div>
                    <div class="input-group">
                        <label>Upload Cover Gambar (Potret, untuk card):</label>
                        <input type="file" name="gambar_cover" required> 
                    </div>
                    <div class="input-group">
                        <label>Upload Hero Image (Lanskap, untuk slideshow):</label>
                        <input type="file" name="hero_image" required> 
                    </div>
                    
                    <div class="input-group">
                        <label>Pilih Genre:</label>
                        <div style="display: flex; flex-wrap: wrap; gap: 15px; background-color: #2a3c58; padding: 10px; border-radius: 5px;">
                            <?php if (empty($genres)): ?>
                                <p style="color: #f44336;">*Belum ada genre. Tambahkan di Kelola Genre.</p>
                            <?php endif; ?>
                            <?php foreach ($genres as $genre): ?>
                                <label style="display: flex; align-items: center; gap: 5px; color: #e4e6eb;">
                                    <input type="checkbox" name="genres[]" value="<?php echo $genre['id']; ?>">
                                    <?php echo htmlspecialchars($genre['nama_genre']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Tipe Link:</label>
                        <select name="link_type" id="link_type" onchange="toggleLinkFields()" required>
                            <option value="single">Single Link</option>
                            <option value="part">Multi-Part Link</option>
                        </select>
                    </div>
                    <div id="single_link_field" class="input-group">
                        <label>Link Download (Single):</label>
                        <input type="url" name="link_single" id="link_single" placeholder="Masukkan URL Download Tunggal"> 
                    </div>
                    <div id="multi_link_fields" style="display:none;" class="input-group">
                        <p style="color: #4CAF50; font-style: italic; margin-bottom: 15px;">*Anda dapat menambahkan link sebanyak part yang dibutuhkan.</p>
                        <div id="dynamic_links_container">
                            <label for="link_part_1">Link Part 1:</label>
                            <input type="url" name="link_part[]" id="link_part_1" placeholder="URL Part 1">
                        </div>
                        <button type="button" onclick="addLinkPart()" class="btn btn-primary" style="padding: 8px 15px; margin-top: 5px;">+ Tambah Part Link</button>
                    </div>
                    <button type="submit" name="tambah_game" class="btn btn-primary btn-full" style="margin-top: 30px;">SIMPAN DATA GAME</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>