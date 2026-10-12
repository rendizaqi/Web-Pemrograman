<?php
$host = "localhost";
$port = "5432";
$db   = "simpus_mini";
$user = "postgres";
$pass = "postgres";

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Detail koneksi (host, port, SQLSTATE, kadang kredensial) dicatat ke
    // log server saja, TIDAK ditampilkan ke pengunjung — pesan mentah PDO
    // membocorkan struktur internal server/database ke siapa pun yang
    // kebetulan mengakses halaman saat database sedang bermasalah.
    error_log('Koneksi database gagal: ' . $e->getMessage());
    http_response_code(503);
    die('Layanan sedang tidak tersedia. Silakan coba lagi beberapa saat lagi.');
}
