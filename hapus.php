<?php
require 'config/koneksi.php';
require 'auth.php';

// Pastikan penghapusan hanya melalui POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: buku.php");
    exit;
}

// Periksa ID buku
if (!isset($_POST['id']) || !ctype_digit($_POST['id'])) {
    header("Location: buku.php");
    exit;
}

$id = (int) $_POST['id'];

if ($id <= 0) {
    header("Location: buku.php");
    exit;
}

// Hapus buku menggunakan prepared statement
$query = mysqli_prepare(
    $koneksi,
    "DELETE FROM buku WHERE id = ?"
);

mysqli_stmt_bind_param($query, "i", $id);
mysqli_stmt_execute($query);

mysqli_stmt_close($query);

header("Location: buku.php");
exit;
?>
