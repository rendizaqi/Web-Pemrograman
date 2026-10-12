# 7. Hasil Pengerjaan Ide Latihan (§6.4)

Catatan hasil uji coba nyata atas 4 ide latihan opsional di
[06-rangkuman-latihan.md §6.4](06-rangkuman-latihan.md#64-ide-latihan-tambahan-opsional),
dijalankan lewat `php -S localhost:8003` + PostgreSQL lokal.

## Latihan 1 — Validasi anggota terlambat >14 hari

Ditambahkan di `peminjaman/proses_tambah.php`, sebelum pengecekan stok,
di dalam transaction yang sama:

```php
$cekTerlambat = $pdo->prepare(
    "SELECT id FROM peminjaman
     WHERE anggota_id = :anggota_id AND status = 'dipinjam'
       AND CURRENT_DATE - tanggal_pinjam > 14
     LIMIT 1"
);
$cekTerlambat->execute(['anggota_id' => $anggotaId]);
if ($cekTerlambat->fetch()) {
    throw new Exception('Anggota ini punya peminjaman yang sudah terlambat lebih dari 14 hari. ...');
}
```

**Uji coba**: anggota #1 dibuatkan peminjaman aktif dengan `tanggal_pinjam`
dimundurkan 20 hari (lebih dari batas 14 hari), lalu anggota yang sama
mencoba meminjam buku lain.

- Hasil: request ditolak, flash error muncul: *"Anggota ini punya
  peminjaman yang sudah terlambat lebih dari 14 hari..."*
- Stok buku yang dicoba dipinjam **tidak berkurang** (tetap 6) — karena
  `throw` terjadi sebelum `UPDATE buku`, dan `catch` memanggil
  `rollBack()`. Transaction bekerja sesuai desain.

## Latihan 2 — Kolom `tanggal_jatuh_tempo`

- `sql/04_latihan_jatuh_tempo.sql`: `ALTER TABLE peminjaman ADD COLUMN
  tanggal_jatuh_tempo DATE`.
- `proses_tambah.php`: `INSERT` kini menyertakan
  `CURRENT_DATE + INTERVAL '14 days'` untuk kolom ini.
- `kembali.php`: kolom "Jatuh Tempo" ditambahkan ke tabel daftar
  peminjaman aktif; baris yang `tanggal_jatuh_tempo` nya sudah lewat
  hari ini ditandai merah tebal + label "(terlambat)".

**Uji coba**: peminjaman baru pada tanggal 2026-10-12 otomatis
tercatat `tanggal_jatuh_tempo = 2026-10-26` (persis +14 hari). Saat
kolom itu diset ke masa lalu secara manual, baris di `kembali.php`
langsung tampil merah dengan label "(terlambat)".

## Latihan 3 — Uji race condition (dua sesi bersamaan)

Disimulasikan dengan **dua sesi login berbeda** (bukan satu sesi yang
dipakai dua tab, karena PHP session file akan mengunci dan membuat
request kedua menunggu request pertama selesai — itu sendiri bukan
bukti `FOR UPDATE` di level database). Buku "Sapiens" (id 7) distok
jadi **1**, lalu dua request POST ke `proses_tambah.php` ditembakkan
**bersamaan** (`&` + `wait` di shell) dari dua sesi:

| Sesi | Anggota | Hasil |
|---|---|---|
| A (admin) | #1 Muhammad Rendy | ✅ "Peminjaman berhasil dicatat." |
| B (petugas) | #3 (anggota uji) | ❌ "Gagal mencatat peminjaman: Stok buku tidak tersedia." |

Stok akhir buku: **0** (bukan -1). Hanya **satu** baris `peminjaman`
baru tercatat. Ini membuktikan `SELECT ... FOR UPDATE` pada
`proses_tambah.php` benar-benar mengunci baris `buku` sehingga request
kedua terpaksa menunggu request pertama `commit`, baru membaca ulang
stok (yang sudah 0) dan ditolak — bukan race condition yang
menghasilkan stok negatif.

## Latihan 4 — Tambahan `JOIN` kolom `no_hp`

`kembali.php`: query JOIN diperluas dengan `a.no_hp`, dan kolom
"No. HP" ditambahkan ke tabel daftar peminjaman aktif — sehingga
Petugas bisa langsung menghubungi anggota yang belum mengembalikan
buku tanpa membuka halaman anggota secara terpisah.

(`riwayat.php` tidak perlu `JOIN` tambahan untuk `no_hp` karena data
anggota terpilih — termasuk `no_hp` — sudah diambil lewat query
`SELECT * FROM anggota WHERE id = :id` yang sudah ada.)

---

Semua data uji (anggota percobaan, peminjaman percobaan, perubahan
stok sementara) sudah dibersihkan kembali setelah pengujian selesai —
database kembali ke kondisi semula.
