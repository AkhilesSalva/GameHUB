<?php
// --- FILE KONFIGURASI UTAMA ---

/**
 * PENTING: Memulai sesi di awal file konfigurasi 
 * agar semua skrip yang meng-include file ini otomatis punya sesi.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/**
 * 1. BASE URL
 * Alamat dasar website Anda. Ganti 'game-hub' jika nama folder proyek Anda berbeda.
 */
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/game-hub'); 
}


/**
 * 2. KONEKSI DATABASE
 * Kredensial untuk menghubungkan PHP ke database MySQL Anda.
 */
$host = "localhost";        
$user = "root";             
$pass = "";                 
$db   = "db_game_crud";     

// Membuat koneksi
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$koneksi) {
    die("KONEKSI DATABASE GAGAL: " . mysqli_connect_error()); 
}


/**
 * 3. INISIALISASI VARIABEL SESI (Opsional)
 * Memastikan variabel penting selalu ada untuk menghindari error "Undefined array key" 
 * di file frontend (index.php, detail.php).
 */
$_SESSION['user_id'] = $_SESSION['user_id'] ?? null;
$_SESSION['username'] = $_SESSION['username'] ?? 'Pengunjung';
$_SESSION['nama'] = $_SESSION['nama'] ?? 'Pengunjung';
$_SESSION['role'] = $_SESSION['role'] ?? 'guest'; // Default role adalah guest
?>