<?php
session_start();
include '../config.php';

header('Content-Type: application/json');

$game_id = isset($_POST['game_id']) ? (int)$_POST['game_id'] : 0;
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$description = isset($_POST['description']) ? mysqli_real_escape_string($koneksi, trim($_POST['description'])) : '';

if (!$game_id) {
    echo json_encode(['success' => false, 'message' => 'Game ID tidak valid']);
    exit();
}

// Check if game exists
$game_check = mysqli_query($koneksi, "SELECT id, nama FROM games WHERE id = $game_id");
if (mysqli_num_rows($game_check) == 0) {
    echo json_encode(['success' => false, 'message' => 'Game tidak ditemukan']);
    exit();
}

$game = mysqli_fetch_assoc($game_check);

// Check for duplicate reports (same game, same user within 24 hours)
if ($user_id) {
    $check = mysqli_query($koneksi, "SELECT id FROM broken_link_reports WHERE game_id = $game_id AND user_id = $user_id AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    if (mysqli_num_rows($check) > 0) {
        echo json_encode(['success' => false, 'message' => 'Anda sudah melaporkan link ini dalam 24 jam terakhir']);
        exit();
    }
}

// Insert report
$user_id_val = $user_id ? $user_id : 'NULL';
$ip_address = mysqli_real_escape_string($koneksi, $_SERVER['REMOTE_ADDR']);

$insert = mysqli_query($koneksi, "INSERT INTO broken_link_reports (game_id, user_id, description, status) VALUES ($game_id, $user_id_val, '$description', 'pending')");

if ($insert) {
    echo json_encode([
        'success' => true, 
        'message' => 'Laporan berhasil dikirim! Admin akan segera memeriksa link tersebut.'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal mengirim laporan. Silakan coba lagi.']);
}
