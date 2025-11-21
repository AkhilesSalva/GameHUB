<?php
session_start();
include 'config.php';

// 1. Ambil ID Game dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: " . BASE_URL);
    exit();
}
$game_id = (int)$_GET['id'];

// 2. Ambil data game untuk mendapatkan URL download
// PERBAIKAN: Mengganti 'download_link' menjadi 'link_single'
$query = "SELECT link_single FROM games WHERE id = $game_id"; 

$result = mysqli_query($koneksi, $query);

// Cek apakah query berhasil
if (!$result) {
    // Jika query gagal (misalnya koneksi putus), tampilkan error
    die("Query Gagal: " . mysqli_error($koneksi) . ". GAGAL MEMBACA DATA GAME.");
}

$game = mysqli_fetch_assoc($result);

// Cek apakah game ditemukan
if (!$game) {
    header("Location: " . BASE_URL . "/detail.php?id=$game_id&error=notfound");
    exit();
}

// 3. Ambil URL download yang benar (link_single)
$download_url = $game['link_single'];

if (empty($download_url)) {
    // Jika link kosong, kembalikan ke detail page
    header("Location: " . BASE_URL . "/detail.php?id=$game_id&error=nolink");
    exit();
}

// 4. Catat penambahan download count (download_count + 1)
$update_query = "UPDATE games SET download_count = download_count + 1 WHERE id = $game_id";
mysqli_query($koneksi, $update_query); 

// 5. Redirect user ke URL download yang sebenarnya
header("Location: " . htmlspecialchars($download_url));

exit();
?>