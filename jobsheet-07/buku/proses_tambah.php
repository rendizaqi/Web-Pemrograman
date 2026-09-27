<?php
session_start();

$judul     = trim($_POST['judul'] ?? '');
$pengarang = trim($_POST['pengarang'] ?? '');
$tahun     = trim($_POST['tahun'] ?? '');
$stok      = trim($_POST['stok'] ?? '');
$isbn      = trim($_POST['isbn'] ?? '');

$errors = [];

if ($judul === '') {
    $errors[] = "Judul buku wajib diisi.";
}
if ($pengarang === '') {
    $errors[] = "Pengarang wajib diisi.";
}

if ($isbn !== '' && !preg_match('/^[0-9-]+$/', $isbn)) {
    $errors[] = "Format ISBN tidak valid! Hanya boleh angka dan tanda hubung (-).";
}

if (!empty($errors)) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => implode(' ', $errors)];
    header('Location: tambah.php');
    exit;
}

if (!isset($_SESSION['buku'])) {
    $_SESSION['buku'] = [];
}

$_SESSION['buku'][] = [
    'judul'     => $judul,
    'pengarang' => $pengarang,
    'tahun'     => $tahun,
    'stok'      => $stok,
    'isbn'      => $isbn
];

$_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Buku berhasil ditambahkan.'];
header('Location: list.php');
exit;