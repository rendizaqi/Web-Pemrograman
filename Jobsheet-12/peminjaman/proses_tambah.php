<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/koneksi.php';

csrf_verify();

$anggotaId = $_POST['anggota_id'] ?? '';
$bukuId = $_POST['buku_id'] ?? '';

if ($anggotaId === '' || $bukuId === '') {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Anggota dan buku wajib dipilih.'];
    header('Location: tambah.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // Anggota dengan peminjaman aktif yang sudah terlambat lebih dari 14 hari
    // tidak boleh meminjam buku baru sampai transaksi lamanya dikembalikan.
    $cekTerlambat = $pdo->prepare(
        "SELECT id FROM peminjaman
         WHERE anggota_id = :anggota_id AND status = 'dipinjam'
           AND CURRENT_DATE - tanggal_pinjam > 14
         LIMIT 1"
    );
    $cekTerlambat->execute(['anggota_id' => $anggotaId]);
    if ($cekTerlambat->fetch()) {
        throw new Exception('Anggota ini punya peminjaman yang sudah terlambat lebih dari 14 hari. Kembalikan buku itu dahulu sebelum meminjam buku baru.');
    }

    // Kunci baris buku (FOR UPDATE) agar stok tidak berubah oleh transaksi lain
    // di tengah proses ini — mencegah stok menjadi negatif akibat race condition.
    $cek = $pdo->prepare("SELECT stok FROM buku WHERE id = :id FOR UPDATE");
    $cek->execute(['id' => $bukuId]);
    $buku = $cek->fetch(PDO::FETCH_ASSOC);

    if (!$buku || $buku['stok'] < 1) {
        throw new Exception('Stok buku tidak tersedia.');
    }

    $insert = $pdo->prepare(
        "INSERT INTO peminjaman (buku_id, anggota_id, tanggal_pinjam, tanggal_jatuh_tempo, status)
         VALUES (:buku_id, :anggota_id, CURRENT_DATE, CURRENT_DATE + INTERVAL '14 days', 'dipinjam')"
    );
    $insert->execute(['buku_id' => $bukuId, 'anggota_id' => $anggotaId]);

    $update = $pdo->prepare("UPDATE buku SET stok = stok - 1 WHERE id = :id");
    $update->execute(['id' => $bukuId]);

    $pdo->commit();

    $_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Peminjaman berhasil dicatat.'];
    header('Location: ../index.php');
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Gagal mencatat peminjaman: ' . $e->getMessage()];
    header('Location: tambah.php');
    exit;
}
