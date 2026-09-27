<?php
// Guard lanjutan: di-require SETELAH auth.php (session sudah dipastikan
// ada & user sudah login), lalu membatasi akses hanya untuk role admin.
if (($_SESSION['role'] ?? '') !== 'admin') {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Akses ditolak: hanya admin yang boleh menghapus data.'];
    header('Location: list.php');
    exit;
}
