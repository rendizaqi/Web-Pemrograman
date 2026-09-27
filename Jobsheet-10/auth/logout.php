<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/remember.php';

// Token di database ikut dihapus, bukan cuma cookie di browser, supaya
// salinan cookie yang mungkin tersimpan di tempat lain ikut tidak berlaku.
$pdo = null;
if (isset($_SESSION['user_id'])) {
    require __DIR__ . '/../includes/koneksi.php';
}
hapusRememberCookie($pdo, $_SESSION['user_id'] ?? null);

session_destroy();
header('Location: login.php');
exit;
