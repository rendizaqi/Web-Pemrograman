<?php
session_start();
require_once __DIR__ . '/../includes/koneksi.php';

$nama      = trim($_POST['nama'] ?? '');
$noAnggota = trim($_POST['no_anggota'] ?? '');
$alamat    = trim($_POST['alamat'] ?? '');
$noHp      = trim($_POST['no_hp'] ?? '');

$errors = [];

if ($nama === '') {
    $errors[] = "Nama wajib diisi.";
}
if ($noAnggota === '') {
    $errors[] = "No. Anggota wajib diisi.";
} elseif (!is_numeric($noAnggota)) {
    $errors[] = "No. Anggota harus berupa angka.";
}

if ($noHp !== '' && !is_numeric($noHp)) {
    $errors[] = "No. HP harus berupa angka.";
}

if (!empty($errors)) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => implode(' ', $errors)];
    header('Location: tambah.php');
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO anggota (nama, no_anggota, alamat, no_hp) VALUES (:nama, :no_anggota, :alamat, :no_hp)");
    $stmt->execute([
        ':nama'       => $nama,
        ':no_anggota' => $noAnggota,
        ':alamat'     => $alamat,
        ':no_hp'      => $noHp
    ]);

    $_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Data anggota berhasil disimpan ke database!'];
    header('Location: list.php');
    exit;

} catch (PDOException $e) {
    if ($e->getCode() === '23505') {
        $_SESSION['flash'] = [
            'type'  => 'error', 
            'pesan' => "No. Anggota '{$noAnggota}' sudah terdaftar! Gunakan nomor anggota lain."
        ];
    } else {
        $_SESSION['flash'] = [
            'type'  => 'error', 
            'pesan' => 'Gagal menyimpan data: ' . $e->getMessage()
        ];
    }
    header('Location: tambah.php');
    exit;
}