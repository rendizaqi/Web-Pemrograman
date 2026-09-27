<?php
$host = '127.0.0.1';
$port = '5432';
$db   = 'simpus_mini';
$user = 'postgres';
$pass = '21032007';

$dsn = "pgsql:host=$host;port=$port;dbname=$db";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Koneksi database PostgreSQL gagal: " . $e->getMessage());
}