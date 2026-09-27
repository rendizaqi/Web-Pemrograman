<?php
session_start();
require __DIR__ . '/../includes/koneksi.php';

$nama = trim($_POST['nama'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$telepon = trim($_POST['telepon'] ?? '');

$errors = [];
if ($nama === '') {
    $errors[] = "Nama penerbit wajib diisi.";
}

if (!empty($errors)) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => implode(' ', $errors)];
    header('Location: tambah.php');
    exit;
}

$stmt = $pdo->prepare(
    "INSERT INTO penerbit (nama, alamat, telepon) VALUES (:nama, :alamat, :telepon)"
);
$stmt->execute([
    'nama' => $nama,
    'alamat' => $alamat,
    'telepon' => $telepon,
]);

$_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Penerbit berhasil ditambahkan.'];
header('Location: list.php');
exit;
