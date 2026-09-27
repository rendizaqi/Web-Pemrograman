<?php
// Cookie "remember_me" berisi "<user_id>:<token acak>". Yang disimpan di
// database hanya hash SHA-256 dari token, jadi bocornya isi tabel users
// tidak langsung bisa dipakai untuk membuat cookie palsu.
const REMEMBER_COOKIE = 'remember_me';
const REMEMBER_DURASI = 60 * 60 * 24 * 30; // 30 hari

function setRememberCookie(PDO $pdo, int $userId): void
{
    $token = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare("UPDATE users SET remember_token = :hash WHERE id = :id");
    $stmt->execute(['hash' => hash('sha256', $token), 'id' => $userId]);

    setcookie(REMEMBER_COOKIE, $userId . ':' . $token, [
        'expires'  => time() + REMEMBER_DURASI,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function hapusRememberCookie(?PDO $pdo, ?int $userId): void
{
    if ($pdo && $userId) {
        $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = :id");
        $stmt->execute(['id' => $userId]);
    }
    setcookie(REMEMBER_COOKIE, '', ['expires' => time() - 3600, 'path' => '/']);
}

// Dipanggil saat session belum berisi user_id. Database hanya disentuh
// kalau cookie remember_me memang ada.
function pulihkanSesiDariCookie(): void
{
    if (isset($_SESSION['user_id']) || empty($_COOKIE[REMEMBER_COOKIE])) {
        return;
    }

    $bagian = explode(':', $_COOKIE[REMEMBER_COOKIE], 2);
    if (count($bagian) !== 2 || !ctype_digit($bagian[0])) {
        hapusRememberCookie(null, null);
        return;
    }
    [$userId, $token] = $bagian;

    require __DIR__ . '/koneksi.php';
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['remember_token'] && hash_equals($user['remember_token'], hash('sha256', $token))) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        // Rotasi token setiap kali dipakai, supaya cookie lama yang dicuri tidak bisa dipakai ulang.
        setRememberCookie($pdo, (int) $user['id']);
        return;
    }

    hapusRememberCookie(null, null);
}
