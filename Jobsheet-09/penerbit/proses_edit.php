<?php
session_start();
require __DIR__ . '/../includes/koneksi.php';

$id = $_POST['id'] ?? null;
$nama = trim($_POST['nama'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$telepon = trim($_POST['telepon'] ?? '');

if (!$id) {
    header('Location: list.php');
    exit;
}

$errors = [];
if ($nama === '') {
    $errors[] = "Nama penerbit wajib diisi.";
}

if (!empty($errors)) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => implode(' ', $errors)];
    header('Location: edit.php?id=' . urlencode($id));
    exit;
}

$stmt = $pdo->prepare(
    "UPDATE penerbit SET nama = :nama, alamat = :alamat, telepon = :telepon WHERE id = :id"
);
$stmt->execute([
    'nama' => $nama,
    'alamat' => $alamat,
    'telepon' => $telepon,
    'id' => $id,
]);

$_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Penerbit berhasil diperbarui.'];
header('Location: list.php');
exit;
