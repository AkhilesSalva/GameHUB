<?php
session_start();
include '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? 'submit';
$game_id = isset($_POST['game_id']) ? (int)$_POST['game_id'] : 0;

if (!$game_id) {
    echo json_encode(['success' => false, 'message' => 'Game ID tidak valid']);
    exit();
}

// Check if game exists
$game_check = mysqli_query($koneksi, "SELECT id FROM games WHERE id = $game_id");
if (mysqli_num_rows($game_check) == 0) {
    echo json_encode(['success' => false, 'message' => 'Game tidak ditemukan']);
    exit();
}

// Use session ID or IP as guest identifier
$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? (int)$_SESSION['user_id'] : 0;
$guest_id = $is_logged_in ? '' : (session_id() ?: md5($_SERVER['REMOTE_ADDR']));

if ($action === 'submit') {
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    
    // Validate rating
    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Rating harus antara 1-5']);
        exit();
    }
    
    if ($is_logged_in) {
        // Logged in user - use user_id
        $check = mysqli_query($koneksi, "SELECT id FROM ratings WHERE user_id = $user_id AND game_id = $game_id");
        
        if (mysqli_num_rows($check) > 0) {
            mysqli_query($koneksi, "UPDATE ratings SET rating = $rating, updated_at = NOW() WHERE user_id = $user_id AND game_id = $game_id");
            $message = 'Rating berhasil diupdate!';
        } else {
            mysqli_query($koneksi, "INSERT INTO ratings (user_id, game_id, rating) VALUES ($user_id, $game_id, $rating)");
            $message = 'Rating berhasil disimpan!';
        }
    } else {
        // Guest user - use guest_id
        $guest_id_safe = mysqli_real_escape_string($koneksi, $guest_id);
        $check = mysqli_query($koneksi, "SELECT id FROM ratings WHERE guest_id = '$guest_id_safe' AND game_id = $game_id");
        
        if (mysqli_num_rows($check) > 0) {
            mysqli_query($koneksi, "UPDATE ratings SET rating = $rating, updated_at = NOW() WHERE guest_id = '$guest_id_safe' AND game_id = $game_id");
            $message = 'Rating berhasil diupdate!';
        } else {
            mysqli_query($koneksi, "INSERT INTO ratings (user_id, guest_id, game_id, rating) VALUES (0, '$guest_id_safe', $game_id, $rating)");
            $message = 'Terima kasih atas ratingnya!';
        }
    }
    
    // Update game's average rating
    $avg_query = mysqli_query($koneksi, "SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM ratings WHERE game_id = $game_id");
    $avg_data = mysqli_fetch_assoc($avg_query);
    $avg_rating = round($avg_data['avg_rating'], 2);
    $rating_count = $avg_data['count'];
    
    mysqli_query($koneksi, "UPDATE games SET avg_rating = $avg_rating, rating_count = $rating_count WHERE id = $game_id");
    
    echo json_encode([
        'success' => true, 
        'message' => $message,
        'avg_rating' => number_format($avg_rating, 1),
        'rating_count' => $rating_count
    ]);
    
} elseif ($action === 'get') {
    if ($is_logged_in) {
        $rating_query = mysqli_query($koneksi, "SELECT rating FROM ratings WHERE user_id = $user_id AND game_id = $game_id");
    } else {
        $guest_id_safe = mysqli_real_escape_string($koneksi, $guest_id);
        $rating_query = mysqli_query($koneksi, "SELECT rating FROM ratings WHERE guest_id = '$guest_id_safe' AND game_id = $game_id");
    }
    
    if (mysqli_num_rows($rating_query) > 0) {
        $data = mysqli_fetch_assoc($rating_query);
        echo json_encode(['success' => true, 'has_rated' => true, 'rating' => (int)$data['rating']]);
    } else {
        echo json_encode(['success' => true, 'has_rated' => false]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
}
