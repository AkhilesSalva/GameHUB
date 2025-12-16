<?php
// Wajib Cek Login & Koneksi
include '../config.php';
include 'cek_login.php';

// --- FUNGSI BANTUAN: Handle Upload File ---
function upload_gambar($file_data, $subfolder = 'img') {
    global $koneksi; 

    $direktori = '../assets/' . $subfolder . '/';
    if (!file_exists($direktori)) {
        mkdir($direktori, 0755, true);
    }
    
    $nama_file = $file_data['name'];
    $tmp_file = $file_data['tmp_name'];
    $ukuran_file = $file_data['size'];
    $error = $file_data['error'];
    
    // Check jika tidak ada file di-upload
    if ($error === 4) { return ['status' => true, 'path' => null]; } 

    if ($error !== 0) { return ['status' => false, 'pesan' => 'Error saat upload file. Kode: ' . $error]; }
    if ($ukuran_file > 5000000) { return ['status' => false, 'pesan' => 'Ukuran file terlalu besar (Max 5MB).']; }

    $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
    $nama_baru = uniqid($subfolder . '_') . '.' . $ekstensi;
    $path_lengkap = $direktori . $nama_baru;

    if (move_uploaded_file($tmp_file, $path_lengkap)) {
        $path_db = 'assets/' . $subfolder . '/' . $nama_baru; 
        return ['status' => true, 'path' => $path_db];
    } else {
        return ['status' => false, 'pesan' => 'Gagal memindahkan file upload.'];
    }
}

// --- FUNGSI BANTUAN: Hapus File Gambar Lama ---
function hapus_gambar_lama($koneksi, $id) {
    $query = "SELECT gambar_path, hero_image_path FROM games WHERE id='$id'";
    $result = mysqli_query($koneksi, $query);
    $data = mysqli_fetch_assoc($result);

    if ($data) {
        if (!empty($data['gambar_path']) && file_exists('../' . $data['gambar_path'])) {
            unlink('../' . $data['gambar_path']);
        }
        if (!empty($data['hero_image_path']) && file_exists('../' . $data['hero_image_path'])) {
            unlink('../' . $data['hero_image_path']);
        }
    }
    
    // Also delete screenshots
    $ss_query = mysqli_query($koneksi, "SELECT image_path FROM screenshots WHERE game_id = $id");
    while ($ss = mysqli_fetch_assoc($ss_query)) {
        if (file_exists('../' . $ss['image_path'])) {
            unlink('../' . $ss['image_path']);
        }
    }
    mysqli_query($koneksi, "DELETE FROM screenshots WHERE game_id = $id");
}

// FUNGSI UTAMA: MENGAMBIL NILAI LINK (SINGLE/PART)
function get_link_value($koneksi, $link_type) {
    if ($link_type == 'single' && isset($_POST['link_single'])) {
        return mysqli_real_escape_string($koneksi, $_POST['link_single']);
    } elseif ($link_type == 'part' && isset($_POST['link_part'])) {
        $valid_links = array_filter($_POST['link_part'], function($link) { return !empty(trim($link)); });
        return implode(',', array_map(function($link) use ($koneksi) {
            return mysqli_real_escape_string($koneksi, $link);
        }, $valid_links));
    }
    return '';
}

// FUNGSI: Upload multiple screenshots
function upload_screenshots($koneksi, $game_id, $files) {
    if (empty($files['name'][0])) return;
    
    for ($i = 0; $i < count($files['name']); $i++) {
        $file_data = [
            'name' => $files['name'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'size' => $files['size'][$i],
            'error' => $files['error'][$i]
        ];
        
        $upload = upload_gambar($file_data, 'screenshots');
        if ($upload['status'] && $upload['path']) {
            mysqli_query($koneksi, "INSERT INTO screenshots (game_id, image_path, order_num) VALUES ($game_id, '{$upload['path']}', $i)");
        }
    }
}

// =========================================================================
// OPERASI C (CREATE - TAMBAH GAME)
// =========================================================================
if (isset($_POST['tambah_game'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $link_type = $_POST['link_type'];
    $link_value = get_link_value($koneksi, $link_type);
    $genres = $_POST['genres'] ?? []; 
    
    // New fields
    $version = mysqli_real_escape_string($koneksi, $_POST['version'] ?? '');
    $file_size = mysqli_real_escape_string($koneksi, $_POST['file_size'] ?? '');
    $trailer_url = mysqli_real_escape_string($koneksi, $_POST['trailer_url'] ?? '');
    $system_req_min = mysqli_real_escape_string($koneksi, $_POST['system_req_min'] ?? '');
    $system_req_rec = mysqli_real_escape_string($koneksi, $_POST['system_req_rec'] ?? '');
    $coming_soon = isset($_POST['coming_soon']) ? 1 : 0;
    
    // Platform checkboxes to string
    $platforms = isset($_POST['platforms']) ? implode(', ', $_POST['platforms']) : 'PC';
    $platform = mysqli_real_escape_string($koneksi, $platforms);
    
    $upload_cover = upload_gambar($_FILES['gambar_cover']);
    $upload_hero = upload_gambar($_FILES['hero_image']);

    if ($upload_cover['status'] && $upload_hero['status'] && $upload_cover['path'] && $upload_hero['path']) {
        $cover_path_db = $upload_cover['path'];
        $hero_path_db = $upload_hero['path'];

        $query_insert = "INSERT INTO games (nama, deskripsi, link_type, link_single, gambar_path, hero_image_path, 
                         file_size, platform, trailer_url, system_req_min, system_req_rec, version, coming_soon, created_at) 
                         VALUES ('$nama', '$deskripsi', '$link_type', '$link_value', '$cover_path_db', '$hero_path_db',
                         '$file_size', '$platform', '$trailer_url', '$system_req_min', '$system_req_rec', '$version', $coming_soon, NOW())";

        if (mysqli_query($koneksi, $query_insert)) {
            $game_id = mysqli_insert_id($koneksi);
            
            // Simpan Genre ke tabel pivot game_genre
            foreach ($genres as $genre_id) {
                $genre_id = (int)$genre_id;
                mysqli_query($koneksi, "INSERT INTO game_genre (game_id, genre_id) VALUES ($game_id, $genre_id)");
            }
            
            // Upload screenshots
            if (isset($_FILES['screenshots'])) {
                upload_screenshots($koneksi, $game_id, $_FILES['screenshots']);
            }

            header("Location: " . BASE_URL . "/admin/index.php?pesan=sukses_tambah");
            exit();
        } else {
            unlink('../' . $cover_path_db); 
            unlink('../' . $hero_path_db); 
            die("Gagal menambahkan data: " . mysqli_error($koneksi));
        }
    } else {
        $error_message = !$upload_cover['status'] ? $upload_cover['pesan'] : ($upload_hero['status'] ? "Silakan upload Hero Image." : $upload_hero['pesan']);
        die("Gagal upload gambar: " . $error_message);
    }
} 

// =========================================================================
// OPERASI U (UPDATE - EDIT GAME)
// =========================================================================
else if (isset($_POST['edit_game'])) {
    $id_game = (int)$_POST['id_game'];
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $link_type = $_POST['link_type'];
    $link_value = get_link_value($koneksi, $link_type);
    $genres = $_POST['genres'] ?? []; 
    
    // New fields
    $version = mysqli_real_escape_string($koneksi, $_POST['version'] ?? '');
    $file_size = mysqli_real_escape_string($koneksi, $_POST['file_size'] ?? '');
    $trailer_url = mysqli_real_escape_string($koneksi, $_POST['trailer_url'] ?? '');
    $system_req_min = mysqli_real_escape_string($koneksi, $_POST['system_req_min'] ?? '');
    $system_req_rec = mysqli_real_escape_string($koneksi, $_POST['system_req_rec'] ?? '');
    $coming_soon = isset($_POST['coming_soon']) ? 1 : 0;
    $changelog = mysqli_real_escape_string($koneksi, $_POST['changelog'] ?? '');
    
    // Platform checkboxes to string
    $platforms = isset($_POST['platforms']) ? implode(', ', $_POST['platforms']) : 'PC';
    $platform = mysqli_real_escape_string($koneksi, $platforms);
    
    // Ambil path lama untuk di-update
    $query_old = mysqli_query($koneksi, "SELECT gambar_path, hero_image_path FROM games WHERE id='$id_game'");
    $old_paths = mysqli_fetch_assoc($query_old);

    // 1. Handle Upload Cover
    $cover_path_db = $old_paths['gambar_path'];
    if (!empty($_FILES['gambar_cover']['name'])) {
        $upload_cover = upload_gambar($_FILES['gambar_cover']);
        if (!$upload_cover['status']) { die("Gagal upload Cover: " . $upload_cover['pesan']); }
        
        if ($old_paths['gambar_path'] && file_exists('../' . $old_paths['gambar_path'])) { unlink('../' . $old_paths['gambar_path']); }
        $cover_path_db = $upload_cover['path'];
    }

    // 2. Handle Upload Hero Image
    $hero_path_db = $old_paths['hero_image_path'];
    if (!empty($_FILES['hero_image']['name'])) {
        $upload_hero = upload_gambar($_FILES['hero_image']);
        if (!$upload_hero['status']) { die("Gagal upload Hero Image: " . $upload_hero['pesan']); }
        
        if ($old_paths['hero_image_path'] && file_exists('../' . $old_paths['hero_image_path'])) { unlink('../' . $old_paths['hero_image_path']); }
        $hero_path_db = $upload_hero['path'];
    }

    // 3. Update Database (Game Data)
    $query_update = "UPDATE games SET 
                     nama = '$nama', 
                     deskripsi = '$deskripsi', 
                     link_type = '$link_type', 
                     link_single = '$link_value', 
                     gambar_path = '$cover_path_db', 
                     hero_image_path = '$hero_path_db',
                     file_size = '$file_size',
                     platform = '$platform',
                     trailer_url = '$trailer_url',
                     system_req_min = '$system_req_min',
                     system_req_rec = '$system_req_rec',
                     version = '$version',
                     changelog = '$changelog',
                     coming_soon = $coming_soon
                     WHERE id = '$id_game'";

    if (mysqli_query($koneksi, $query_update)) {
        
        // 4. Update Genre Relasi: Hapus semua relasi lama
        mysqli_query($koneksi, "DELETE FROM game_genre WHERE game_id=$id_game");

        // 5. Update Genre Relasi: Masukkan relasi yang baru
        foreach ($genres as $genre_id) {
            $genre_id = (int)$genre_id;
            mysqli_query($koneksi, "INSERT INTO game_genre (game_id, genre_id) VALUES ($id_game, $genre_id)");
        }
        
        // Upload new screenshots (appending)
        if (isset($_FILES['screenshots']) && !empty($_FILES['screenshots']['name'][0])) {
            $max_order = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COALESCE(MAX(order_num), 0) as max_order FROM screenshots WHERE game_id = $id_game"))['max_order'];
            
            for ($i = 0; $i < count($_FILES['screenshots']['name']); $i++) {
                $file_data = [
                    'name' => $_FILES['screenshots']['name'][$i],
                    'tmp_name' => $_FILES['screenshots']['tmp_name'][$i],
                    'size' => $_FILES['screenshots']['size'][$i],
                    'error' => $_FILES['screenshots']['error'][$i]
                ];
                
                $upload = upload_gambar($file_data, 'screenshots');
                if ($upload['status'] && $upload['path']) {
                    $order = $max_order + $i + 1;
                    mysqli_query($koneksi, "INSERT INTO screenshots (game_id, image_path, order_num) VALUES ($id_game, '{$upload['path']}', $order)");
                }
            }
        }
        
        header("Location: " . BASE_URL . "/admin/index.php?pesan=sukses_edit");
        exit();
    } else {
        die("Gagal mengupdate data: " . mysqli_error($koneksi));
    }
}

// =========================================================================
// OPERASI D (DELETE - HAPUS GAME)
// =========================================================================
else if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id_game = (int)$_GET['id'];
    hapus_gambar_lama($koneksi, $id_game);
    // ON DELETE CASCADE pada tabel game_genre akan otomatis menghapus relasi
    $query_delete = "DELETE FROM games WHERE id='$id_game'";
    if (mysqli_query($koneksi, $query_delete)) {
        header("Location: " . BASE_URL . "/admin/index.php?pesan=sukses_hapus");
        exit();
    } else {
        die("Gagal menghapus data: " . mysqli_error($koneksi));
    }
} 

// =========================================================================
// OPERASI: DELETE SCREENSHOT
// =========================================================================
else if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus_screenshot') {
    $ss_id = (int)$_GET['ss_id'];
    $game_id = (int)$_GET['game_id'];
    
    $ss = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT image_path FROM screenshots WHERE id = $ss_id"));
    if ($ss && file_exists('../' . $ss['image_path'])) {
        unlink('../' . $ss['image_path']);
    }
    mysqli_query($koneksi, "DELETE FROM screenshots WHERE id = $ss_id");
    
    header("Location: " . BASE_URL . "/admin/edit.php?id=$game_id&msg=ss_deleted");
    exit();
}

// =========================================================================
// OPERASI U (UPDATE - EDIT USER)
// =========================================================================
else if (isset($_POST['edit_user'])) {
    $id_user = (int)$_POST['id_user'];
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $role = $_POST['role'];
    $status = isset($_POST['status']) ? mysqli_real_escape_string($koneksi, $_POST['status']) : 'active';
    $password_baru = $_POST['password_baru'];
    
    $set_password = "";
    if (!empty($password_baru)) {
        $password_hash = password_hash($password_baru, PASSWORD_BCRYPT);
        $set_password = ", password = '$password_hash'";
    }

    $query_update = "UPDATE users SET nama_lengkap = '$nama_lengkap', role = '$role', status = '$status' $set_password WHERE id = '$id_user'";

    if (mysqli_query($koneksi, $query_update)) {
        header("Location: " . BASE_URL . "/admin/users.php?pesan=user_edit");
        exit();
    } else {
        die("Gagal mengupdate data user: " . mysqli_error($koneksi));
    }
}

// =========================================================================
// OPERASI D (DELETE - HAPUS USER)
// =========================================================================
else if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus_user') {
    $id_user = (int)$_GET['id'];
    
    $check_user_q = mysqli_query($koneksi, "SELECT username FROM users WHERE id='$id_user'");
    $check_user = mysqli_fetch_assoc($check_user_q);

    if ($check_user['username'] === $_SESSION['username']) {
        die("Error: Anda tidak bisa menghapus akun yang sedang digunakan.");
    }
    
    $query_delete = "DELETE FROM users WHERE id='$id_user'";
    
    if (mysqli_query($koneksi, $query_delete)) {
        header("Location: " . BASE_URL . "/admin/users.php?pesan=user_hapus");
        exit();
    } else {
        die("Gagal menghapus data user: " . mysqli_error($koneksi));
    }
}

// =========================================================================
// OPERASI: BAN/UNBAN USER
// =========================================================================
else if (isset($_GET['aksi']) && $_GET['aksi'] == 'toggle_ban') {
    $id_user = (int)$_GET['id'];
    
    // Check current status
    $user_q = mysqli_query($koneksi, "SELECT status, username FROM users WHERE id = $id_user");
    $user = mysqli_fetch_assoc($user_q);
    
    if ($user['username'] === $_SESSION['username']) {
        die("Error: Anda tidak bisa ban akun sendiri.");
    }
    
    $new_status = ($user['status'] == 'banned') ? 'active' : 'banned';
    mysqli_query($koneksi, "UPDATE users SET status = '$new_status' WHERE id = $id_user");
    
    header("Location: " . BASE_URL . "/admin/users.php?pesan=status_changed");
    exit();
}

// =========================================================================
// OPERASI DEFAULT
// =========================================================================
else {
    header("Location: " . BASE_URL . "/admin/index.php"); 
    exit();
}