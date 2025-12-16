<?php 
// PASTIKAN kamu sudah meng-include config.php di file admin/index.php
// sebelum memanggil cek_login.php!
// Contoh:
// include '../config.php';
// include 'cek_login.php'; 


// PENTING: Pengecekan Login dan Role
// Mengarahkan user jika tidak login atau role bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    // Gunakan BASE_URL untuk membuat URL absolut, ini mencegah redirect loop
    header("location: " . BASE_URL . "/auth/login.php?pesan=belum_login");
    exit();
}
?>