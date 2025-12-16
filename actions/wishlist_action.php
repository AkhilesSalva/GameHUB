<?php
session_start();
include '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$game_id = isset($_POST['game_id']) ? (int)$_POST['game_id'] : (isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0);

// Wishlist sekarang disimpan di localStorage browser
// Endpoint ini hanya untuk get game details jika diperlukan

switch ($action) {
    case 'get_game_info':
        // Get game info untuk ditampilkan di wishlist
        if ($game_id > 0) {
            $query = mysqli_query($koneksi, "SELECT id, nama, gambar_path, download_count FROM games WHERE id = $game_id");
            if ($game = mysqli_fetch_assoc($query)) {
                echo json_encode(['success' => true, 'game' => $game]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Game tidak ditemukan']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'ID game tidak valid']);
        }
        break;
        
    case 'get_games_bulk':
        // Get multiple games by IDs (for wishlist page)
        $ids = $_POST['ids'] ?? $_GET['ids'] ?? '';
        if (!empty($ids)) {
            $id_array = array_map('intval', explode(',', $ids));
            $id_string = implode(',', $id_array);
            $query = mysqli_query($koneksi, "SELECT id, nama, gambar_path, download_count, avg_rating FROM games WHERE id IN ($id_string)");
            $games = [];
            while ($row = mysqli_fetch_assoc($query)) {
                $games[] = $row;
            }
            echo json_encode(['success' => true, 'games' => $games]);
        } else {
            echo json_encode(['success' => true, 'games' => []]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Action tidak valid. Wishlist disimpan di browser localStorage.']);
}
?>
