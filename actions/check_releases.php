<?php
// check_releases.php - Check if any followed games have been released
header('Content-Type: application/json');
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get followed game IDs from request
$game_ids_raw = isset($_POST['game_ids']) ? $_POST['game_ids'] : '';

if (empty($game_ids_raw)) {
    echo json_encode(['released' => []]);
    exit;
}

// Sanitize - only allow comma-separated integers
$game_ids = array_filter(array_map('intval', explode(',', $game_ids_raw)));

if (empty($game_ids)) {
    echo json_encode(['released' => []]);
    exit;
}

$ids_str = implode(',', $game_ids);

// Query for games that are no longer coming_soon (released!)
$query = "SELECT id, nama FROM games WHERE id IN ($ids_str) AND coming_soon = 0";
$result = mysqli_query($koneksi, $query);

$released = [];
while ($row = mysqli_fetch_assoc($result)) {
    $released[] = [
        'id' => (int)$row['id'],
        'nama' => $row['nama']
    ];
}

echo json_encode(['released' => $released]);
