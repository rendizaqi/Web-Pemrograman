# Checklist Keamanan — Jobsheet 11

Audit menyeluruh terhadap kode Jobsheet 7-10, dengan bukti before/after.

| # | Kerentanan | Ditemukan di | Sebelum | Sesudah (perbaikan) |
|---|---|---|---|---|
| 1 | SQL Injection | Semua query di `buku/`, `anggota/`, `auth/` | Sejak Jobsheet 8 sudah memakai prepared statement PDO (`:parameter`) | **Diaudit ulang, sudah aman** — tidak ada satupun query yang menyisipkan `$_POST`/`$_GET` langsung ke string SQL. Diuji input `' OR '1'='1` di form login → tidak berhasil bypass. |
| 2 | XSS (Cross-Site Scripting) | `buku/list.php`, `buku/edit.php`, `anggota/list.php`, `anggota/edit.php`, `includes/header.php` (nama petugas) | Output `judul`, `pengarang`, `nama`, `alamat`, `no_hp`, dan nilai pencarian (`q`) dicetak langsung tanpa escaping | Dibungkus fungsi `e()` (`includes/helpers.php`, `htmlspecialchars` dengan `ENT_QUOTES`). Diuji simpan judul buku `<script>alert(1)</script>` → tampil sebagai teks biasa, bukan dieksekusi. |
| 3 | CSRF (Cross-Site Request Forgery) | Form Tambah/Edit/Hapus Buku & Anggota, Login, Register | Form POST tidak memiliki token verifikasi — bisa dipicu dari situs lain | Ditambah `includes/csrf.php` (`csrf_field()` + `csrf_verify()`), token disimpan di `$_SESSION['csrf_token']`, diverifikasi di setiap `proses_*.php` dan `hapus.php` sebelum query dijalankan. |
| 4 | Validasi & Sanitasi Input | `proses_tambah.php`, `proses_edit.php` (buku & anggota) | Sudah ada validasi tipe (`is_numeric`) dan wajib-isi sejak Jobsheet 7-9 | **Diaudit ulang, tetap dipertahankan** — ditambah cast eksplisit `(int)` pada `id` di form Edit untuk mencegah nilai non-numerik masuk sebagai hidden input. |
| 5 | Session Fixation | `auth/proses_login.php` | Session ID tidak diperbarui setelah login | `session_regenerate_id(true)` dipanggil tepat setelah `password_verify()` berhasil. |
| 6 | XSS laten pada flash message | `auth/login.php`, `auth/register.php`, `buku/list.php`, `buku/tambah.php`, `buku/edit.php`, `anggota/list.php`, `anggota/tambah.php`, `anggota/edit.php` | `$flash['pesan']` dan `$flash['type']` dicetak langsung tanpa `e()`. Belum ada input pengguna yang masuk ke pesan ini saat ini (semua teks statis), tapi celah ini **laten**: begitu ada developer menambah pesan yang menyisipkan `$_POST`/`$_GET` (contoh: pesan error "No. Anggota '\$noAnggota' sudah terdaftar!" dari latihan Jobsheet 8), langsung jadi XSS tanpa disadari. | Dibungkus `e()` di kedelapan file: `<?php echo e($flash['pesan']); ?>` dan `<?php echo e($flash['type']); ?>` — konsisten dengan kebijakan "semua output data dinamis wajib lewat `e()`", bukan hanya yang jelas-jelas berasal dari `$_GET`/`$_POST` saat ini. |
| 7 | Informasi bocor lewat pesan error koneksi database | `includes/koneksi.php` | Blok `catch (PDOException $e)` menampilkan `$e->getMessage()` mentah ke pengunjung — berisi host, port, SQLSTATE, dan kadang detail kredensial/struktur server saat database bermasalah. | Pesan detail dicatat lewat `error_log()` (hanya terlihat developer di log server), pengunjung hanya melihat pesan generik "Layanan sedang tidak tersedia..." dengan status HTTP 503. Diuji dengan mengarahkan `$port` ke port yang salah → sebelum: HTML berisi teks `SQLSTATE[08006] ... connection to server at "localhost" ...`; sesudah: hanya pesan generik. |

## Catatan Implementasi
- Guard `includes/auth.php` selalu dijalankan **sebelum** `includes/csrf.php` di halaman proses — memastikan pengguna yang belum login tidak bisa memicu pengecekan CSRF sama sekali (langsung di-redirect ke login).
- Fungsi `csrf_verify()` mengembalikan HTTP 403 dan menghentikan eksekusi (`die()`) bila token tidak cocok atau tidak ada — diverifikasi manual dengan mengirim POST tanpa `csrf_token` ke `proses_tambah.php` (setelah login).

## Diskusi: Kenapa Form Pencarian (GET) Tidak Diberi Token CSRF?

Form pencarian di `buku/list.php` (`method="get"`) **sengaja tidak** diberi
`csrf_field()`, berbeda dari semua form `method="post"` di atas. Alasannya
ada di sifat dasar serangan CSRF itu sendiri:

- **CSRF mengeksploitasi permintaan yang MENGUBAH data** atas nama korban
  tanpa sepengetahuannya (menghapus akun, mengubah password, transfer
  dana, dst). Form pencarian **hanya membaca** data (`SELECT ... WHERE
  judul ILIKE ...`) — walau situs lain berhasil memaksa korban mengirim
  request pencarian, dampaknya cuma korban melihat hasil pencarian yang
  tidak diminta. **Tidak ada state yang berubah**, jadi tidak ada yang
  perlu "dilindungi".
- Menambahkan token ke form `GET` justru **berisiko baru**: token akan
  ikut tampil di **URL** (`list.php?q=...&csrf_token=abcd1234...`), yang
  berarti bocor ke **riwayat browser**, **log server**, dan **header
  `Referer`** setiap kali form ini disubmit — kebalikan dari tujuan token
  itu sendiri (harus dirahasiakan).
- Prinsip ini konsisten dengan pembahasan `GET` vs `POST` di dokumentasi
  Jobsheet 9 bab 3 ("`GET` untuk operasi aman, `POST` untuk operasi yang
  mengubah data"): proteksi CSRF hanya relevan untuk permintaan yang
  **mengubah data**, dan form pencarian ini secara sengaja didesain
  **read-only**.

**Kesimpulan:** form `GET` yang murni membaca data tidak butuh proteksi
CSRF — justru menambahkannya adalah anti-pattern. Proteksi CSRF hanya
perlu dipasang di form `POST` yang mengubah state (Tambah/Edit/Hapus,
Login, Register), yang sudah diterapkan di baris #3 checklist di atas.

## Lapisan Pertahanan Tambahan: Content-Security-Policy (CSP)

`e()` mencegah XSS dengan meng-escape output — tapi ini bergantung pada
**disiplin manusia** untuk selalu membungkus setiap output baru dengan
`e()` (baris #6 di atas membuktikan celah seperti ini bisa lolos
bertahun-tahun tanpa disadari). **Content-Security-Policy (CSP)** adalah
header HTTP yang memberi **lapisan pertahanan kedua**: memberi tahu
browser skrip dari sumber mana saja yang boleh dijalankan di halaman ini.

Header yang diterapkan di `includes/header.php`:
```
Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; base-uri 'self'; form-action 'self'
```

- **`script-src 'self'`** — browser hanya boleh menjalankan file `<script>`
  yang di-load dari domain sendiri (`assets/js/app.js`). Kalau XSS tetap
  lolos dari `e()` di suatu tempat dan penyerang berhasil menyisipkan
  `<script>...</script>` inline atau dari domain lain, **browser akan
  menolak menjalankannya** karena melanggar CSP — walau escaping gagal,
  eksekusinya tetap diblokir.
- **`form-action 'self'`** — mencegah form di halaman ini diam-diam
  di-submit ke domain lain (skenario lanjutan dari XSS: mencuri data
  form ke server penyerang).
- CSP bukan pengganti `e()`/CSRF — ini **lapisan pertahanan berlapis
  (defense in depth)**: kalau satu lapisan gagal, lapisan lain tetap
  menahan dampaknya.
