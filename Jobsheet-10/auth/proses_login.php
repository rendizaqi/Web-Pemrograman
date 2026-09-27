<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/remember.php';

const BATAS_PERCOBAAN = 5;
const MULAI_PERINGATAN = 3;
const LAMA_KUNCI = 60; // detik

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$usernameAman = htmlspecialchars($username);

// Penghitung disimpan di $_SESSION (sesuai petunjuk latihan). Catatan:
// penyerang bisa me-reset-nya dengan menghapus cookie sesi, jadi untuk
// produksi penghitung sebaiknya disimpan di database / per alamat IP.
$gagal = $_SESSION['gagal_login'][$username] ?? ['jumlah' => 0, 'kunci_sampai' => 0];

if ($gagal['kunci_sampai'] > time()) {
    $sisaDetik = $gagal['kunci_sampai'] - time();
    $_SESSION['flash'] = [
        'type' => 'error',
        'pesan' => "Terlalu banyak percobaan gagal untuk \"$usernameAman\". Coba lagi dalam $sisaDetik detik.",
    ];
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
$stmt->execute(['username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    unset($_SESSION['gagal_login'][$username]);
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['role'] = $user['role'];

    if (!empty($_POST['ingat_saya'])) {
        setRememberCookie($pdo, (int) $user['id']);
    }

    header('Location: ../index.php');
    exit;
}

$gagal['jumlah']++;
$pesan = 'Username atau password salah.';

if ($gagal['jumlah'] >= BATAS_PERCOBAAN) {
    $gagal = ['jumlah' => 0, 'kunci_sampai' => time() + LAMA_KUNCI];
    $pesan = "Login gagal " . BATAS_PERCOBAAN . " kali berturut-turut. Akun \"$usernameAman\" dikunci sementara selama " . LAMA_KUNCI . " detik.";
} elseif ($gagal['jumlah'] >= MULAI_PERINGATAN) {
    $sisa = BATAS_PERCOBAAN - $gagal['jumlah'];
    $pesan .= " Peringatan: sudah {$gagal['jumlah']}x gagal, sisa $sisa percobaan sebelum dikunci sementara.";
}

$_SESSION['gagal_login'][$username] = $gagal;
$_SESSION['flash'] = ['type' => 'error', 'pesan' => $pesan];
header('Location: login.php');
exit;
