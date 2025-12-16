<?php
// Wajib: Mulai session dulu
session_start();

// Hapus semua variabel session
$_SESSION = array();

// Jika ingin menghapus session secara penuh, hapus juga cookie session.
// Catatan: Ini akan menghancurkan session, bukan hanya data session!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Redirect ke halaman login dengan pesan
header("location:login.php?pesan=logout");
exit();
?>