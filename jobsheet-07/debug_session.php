<?php
session_start();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Debug Session - SIMPUS Mini</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f9f9f9; }
        pre { background: #fff; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .btn-reset { color: white; background: #d9534f; padding: 6px 12px; text-decoration: none; border-radius: 4px; }
        .success { color: green; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Isi Data $_SESSION Saat Ini:</h2>

    <?php if ($flash): ?>
        <p class="success"><?php echo $flash['pesan']; ?></p>
    <?php endif; ?>

    <pre><?php print_r($_SESSION); ?></pre>
    
    <hr>
    <p>
        <a href="index.php">← Kembali ke Beranda</a> | 
        <a href="reset_session.php" class="btn-reset" onclick="return confirm('Yakin mau menghapus semua data session?')">Reset Data Session</a>
    </p>
</body>
</html>