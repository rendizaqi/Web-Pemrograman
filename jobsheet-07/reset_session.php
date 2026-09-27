<?php
session_start();

session_unset();
session_destroy();

session_start();
$_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Semua data session berhasil dibersihkan!'];

header('Location: debug_session.php');
exit;