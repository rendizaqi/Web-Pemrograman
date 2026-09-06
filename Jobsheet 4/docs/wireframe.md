# Wireframe & User Flow — SIMPUS-Mini

Sub-CPMK: Merancang UI/UX aplikasi (proyek).

Halaman yang sudah ada (Beranda, Daftar/Tambah Buku, Daftar/Tambah Anggota — Jobsheet 1-3) belum mencakup fitur Login, Dashboard Petugas, dan Peminjaman/Pengembalian. Dokumen ini merancang wireframe untuk halaman-halaman tersebut sebelum diimplementasikan mulai Jobsheet 5 dan seterusnya.

## Aktor
- **Tamu**: hanya bisa melihat katalog buku (Beranda, Daftar Buku) tanpa login.
- **Petugas**: login untuk mengakses seluruh fitur CRUD dan transaksi peminjaman.

## User Flow — Peminjaman Buku

```
[Petugas Login] -> [Dashboard] -> [Pilih menu "Peminjaman Baru"]
        -> [Pilih Anggota] -> [Pilih Buku (stok > 0)]
        -> [Simpan] -> [Stok buku berkurang 1] -> [Kembali ke Dashboard]
```

## User Flow — Pengembalian Buku

```
[Dashboard] -> [Menu "Pengembalian"] -> [Cari transaksi aktif (anggota/buku)]
        -> [Tandai "Dikembalikan"] -> [Stok buku bertambah 1]
        -> [Kembali ke Dashboard]
```

## Wireframe: Halaman Login

```
+--------------------------------------+
|              SIMPUS-Mini             |
|--------------------------------------|
|                                      |
|        [ Login Petugas ]            |
|                                      |
|   Username : [______________]       |
|   Password : [______________]       |
|                                      |
|          [   Masuk   ]              |
|                                      |
|   Belum punya akun? Daftar di sini  |
+--------------------------------------+
```

## Wireframe: Dashboard Petugas

```
+-----------------------------------------------------+
| SIMPUS-Mini      Beranda | Buku | Anggota | Peminjaman | (Nama Petugas) Logout |
|-------------------------------------------------------|
|  [Total Buku]   [Total Anggota]   [Sedang Dipinjam]    |
|                                                         |
|  Aksi Cepat:                                           |
|  [ + Peminjaman Baru ]   [ + Pengembalian ]            |
|                                                         |
|  Transaksi Terbaru                                     |
|  --------------------------------------------------    |
|  Anggota | Buku | Tgl Pinjam | Status                  |
+-----------------------------------------------------+
```

## Wireframe: Form Peminjaman

```
+--------------------------------------+
|  Form Peminjaman Buku                |
|--------------------------------------|
|  Anggota : [ dropdown pilih anggota ]|
|  Buku    : [ dropdown, hanya stok>0 ]|
|  Tanggal Pinjam : [ auto: hari ini ] |
|                                      |
|          [  Simpan Peminjaman  ]    |
+--------------------------------------+
```

## Wireframe: Form Pengembalian

```
+--------------------------------------+
|  Pengembalian Buku                   |
|--------------------------------------|
|  Cari transaksi aktif:               |
|  [ nama anggota / judul buku ______ ]|
|                                      |
|  Anggota | Buku | Tgl Pinjam | [Kembalikan] |
+--------------------------------------+
```

## Wireframe: Riwayat Peminjaman per Anggota

```
+--------------------------------------+
|  Riwayat Peminjaman — Siti Aminah    |
|--------------------------------------|
|  Buku            | Pinjam   | Kembali | Status      |
|  Laskar Pelangi   | 01/07    | 10/07   | Selesai     |
|  Bumi Manusia      | 15/07    | -       | Dipinjam    |
+--------------------------------------+
```

## Konsistensi dengan Desain yang Sudah Berjalan
- Warna aksen, tipografi navbar, dan gaya tabel/kartu mengikuti `assets/css/style.css` yang sudah dibangun sejak Jobsheet 2-3.
- Navbar akan ditambah menu **Peminjaman** dan indikator status login (nama petugas / tombol Logout) mulai implementasi di Jobsheet 10.
- Edge case yang perlu ditangani saat implementasi: buku stok habis tidak boleh dipilih di form peminjaman; anggota dengan tunggakan terlambat divalidasi di Jobsheet 12 (tugas mandiri).


## Latihan Tambahan (Sub-bab 6.4)

## 1. Wireframe Halaman Registrasi Anggota Baru (Aktor Tamu)

+--------------------------------------+
|              SIMPUS-Mini             |
|--------------------------------------|
|                                      |
|       [ Pendaftaran Anggota ]        |
|                                      |
|   Nama Lengkap : [_________________] |
|   Alamat       : [_________________] |
|   No. HP/WA    : [_________________] |
|   Email        : [_________________] |
|                                      |
|          [ Daftar Sekarang ]         |
|                                      |
|   Sudah menjadi anggota? Login       |
+--------------------------------------+

## 2. User Flow — Pencarian & Penanganan Anggota Menunggak

[Petugas Login] -> [Dashboard] -> [Pilih menu "Daftar Anggota"]
        -> [Filter / Cari: Status "Menunggak"]
        -> [Sistem Memfilter Transaksi Terlambat > 7 Hari]
        -> [Klik Nama Anggota -> Tampilkan Riwayat & Detail Denda]
        -> [Proses Pengembalian / Pelunasan Denda]

        
## 3. Penanganan Edge Case Tambahan

- Edge Case 1: Peminjaman Ganda Buku yang Sama
Sistem akan mengecek apakah id_anggota masih meminjam id_buku yang sama dengan status Dipinjam. Jika ya, transaksi ditolak dengan pesan: "Anggota ini masih meminjam judul buku yang sama."

- Edge Case 2: Batas Maksimal Peminjaman (Max Limit)
Sistem mengecek kuota peminjaman aktif. Jika COUNT(status = 'Dipinjam') >= 3, transaksi ditolak dengan pesan: "Anggota telah mencapai batas maksimal 3 peminjaman buku aktif."

## 4. Rancangan Kode HTML Statis — Halaman Login

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Petugas - SIMPUS-Mini</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <header>
        <h1>SIMPUS-Mini</h1>
    </header>

    <main class="container">
        <section class="card-login">
            <h2>Login Petugas</h2>
            <form action="#" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                </div>

                <button type="submit" class="btn-primary">Masuk</button>
            </form>

            <p class="auth-link">
                Belum punya akun? <a href="#">Daftar di sini</a>
            </p>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 SIMPUS-Mini — Perancangan UI/UX Jobsheet 4</p>
    </footer>

</body>
</html>