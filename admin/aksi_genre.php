<?php
include '../config.php';
include 'cek_login.php';

// Cek Role Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

// =========================================================================
// OPERASI C (CREATE - TAMBAH GENRE)
// =========================================================================
if (isset($_POST['tambah_genre'])) {
    $nama_genre = mysqli_real_escape_string($koneksi, $_POST['nama_genre']);

    $query_insert = "INSERT INTO genre (nama_genre) VALUES ('$nama_genre')";

    if (mysqli_query($koneksi, $query_insert)) {
        header("Location: " . BASE_URL . "/admin/genre.php?pesan=sukses_tambah");
        exit();
    } else {
        header("Location: " . BASE_URL . "/admin/genre.php?pesan=gagal&error=" . urlencode(mysqli_error($koneksi)));
        exit();
    }
} 

// =========================================================================
// OPERASI U (UPDATE - EDIT GENRE)
// =========================================================================
else if (isset($_POST['edit_genre'])) {
    $id = (int)$_POST['id'];
    $nama_genre = mysqli_real_escape_string($koneksi, $_POST['nama_genre']);

    $query_update = "UPDATE genre SET nama_genre = '$nama_genre' WHERE id = $id";

    if (mysqli_query($koneksi, $query_update)) {
        header("Location: " . BASE_URL . "/admin/genre.php?pesan=sukses_edit");
        exit();
    } else {
        header("Location: " . BASE_URL . "/admin/genre.php?pesan=gagal&error=" . urlencode(mysqli_error($koneksi)));
        exit();
    }
}

// =========================================================================
// OPERASI D (DELETE - HAPUS GENRE)
// =========================================================================
else if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = (int)$_GET['id'];
    
    // Karena kita sudah set FOREIGN KEY ON DELETE CASCADE di game_genre,
    // menghapus genre akan otomatis menghapus relasi di tabel game_genre.
    $query_delete = "DELETE FROM genre WHERE id=$id";
    
    if (mysqli_query($koneksi, $query_delete)) {
        header("Location: " . BASE_URL . "/admin/genre.php?pesan=sukses_hapus");
        exit();
    } else {
        header("Location: " . BASE_URL . "/admin/genre.php?pesan=gagal&error=" . urlencode(mysqli_error($koneksi)));
        exit();
    }
} 

// =========================================================================
// OPERASI DEFAULT
// =========================================================================
else {
    header("Location: " . BASE_URL . "/admin/genre.php"); 
    exit();
}