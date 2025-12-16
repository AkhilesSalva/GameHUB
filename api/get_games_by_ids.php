<?php
/**
 * API to get games by IDs
 * Used by koleksi.php (Coming Soon wishlist) to fetch game details
 */
include '../config.php';

header('Content-Type: application/json');

if (!isset($_GET['ids']) || empty($_GET['ids'])) {
    echo json_encode(['success' => false, 'message' => 'No IDs provided']);
    exit;
}

// Parse and sanitize IDs
$ids_raw = explode(',', $_GET['ids']);
$ids = [];
foreach ($ids_raw as $id) {
    $id = (int)trim($id);
    if ($id > 0) $ids[] = $id;
}

if (empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'Invalid IDs']);
    exit;
}

$ids_str = implode(',', $ids);

$query = "SELECT id, nama, gambar_path, view_count, download_count, coming_soon FROM games WHERE id IN ($ids_str)";
$result = mysqli_query($koneksi, $query);

$games = [];
while ($row = mysqli_fetch_assoc($result)) {
    $games[] = $row;
}

echo json_encode(['success' => true, 'games' => $games]);
