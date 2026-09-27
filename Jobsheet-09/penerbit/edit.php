<?php
$page_title = "Edit Penerbit";
include __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/koneksi.php';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: list.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM penerbit WHERE id = :id");
$stmt->execute(['id' => $id]);
$penerbit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$penerbit) {
    header('Location: list.php');
    exit;
}
?>
        <section>
            <h2>Edit Penerbit</h2>

            <?php if ($flash): ?>
                <p class="flash flash-<?php echo $flash['type']; ?>"><?php echo $flash['pesan']; ?></p>
            <?php endif; ?>

            <form id="form-tambah" class="form-update" method="post" action="proses_edit.php">
                <input type="hidden" name="id" value="<?php echo $penerbit['id']; ?>">
                <p>
                    <label for="nama">Nama Penerbit</label><br>
                    <input type="text" id="nama" name="nama" value="<?php echo $penerbit['nama']; ?>" required>
                </p>
                <p>
                    <label for="alamat">Alamat</label><br>
                    <input type="text" id="alamat" name="alamat" value="<?php echo $penerbit['alamat']; ?>">
                </p>
                <p>
                    <label for="telepon">Telepon</label><br>
                    <input type="text" id="telepon" name="telepon" value="<?php echo $penerbit['telepon']; ?>">
                </p>
                <p>
                    <button type="submit">Update</button>
                </p>
            </form>
        </section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
