<?php
session_start();
include '../config.php';

// 1. Ambil ID Game dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: " . BASE_URL);
    exit();
}
$game_id = (int)$_GET['id'];
$part = isset($_GET['part']) ? (int)$_GET['part'] : 0;

// 2. Ambil data game
$query = "SELECT link_type, link_single FROM games WHERE id = $game_id"; 
$result = mysqli_query($koneksi, $query);

if (!$result) {
    die("Query Gagal: " . mysqli_error($koneksi));
}

$game = mysqli_fetch_assoc($result);

if (!$game) {
    header("Location: " . BASE_URL . "/pages/detail.php?id=$game_id&error=notfound");
    exit();
}

// 3. Tentukan URL download
if ($game['link_type'] == 'multi' && $part > 0) {
    // Multi-part download
    $parts = explode(',', $game['link_single']);
    $download_url = isset($parts[$part - 1]) ? trim($parts[$part - 1]) : '';
} else {
    // Single download
    $download_url = $game['link_single'];
}

if (empty($download_url)) {
    header("Location: " . BASE_URL . "/pages/detail.php?id=$game_id&error=nolink");
    exit();
}

// 4. Catat ke download history jika user login
if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
    $ip_address = mysqli_real_escape_string($koneksi, $_SERVER['REMOTE_ADDR'] ?? '');
    $user_agent = mysqli_real_escape_string($koneksi, substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255));
    
    // Cek apakah sudah download dalam 5 menit terakhir (hindari spam)
    $check = mysqli_query($koneksi, "SELECT id FROM download_history WHERE user_id = $user_id AND game_id = $game_id AND downloaded_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
    if (mysqli_num_rows($check) == 0) {
        mysqli_query($koneksi, "INSERT INTO download_history (user_id, game_id, ip_address, user_agent) VALUES ($user_id, $game_id, '$ip_address', '$user_agent')");
    }
}

// 5. Update download count
mysqli_query($koneksi, "UPDATE games SET download_count = download_count + 1 WHERE id = $game_id");

// 6. Redirect ke URL download
header("Location: " . $download_url);
exit();
?>