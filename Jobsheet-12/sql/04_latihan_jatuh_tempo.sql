-- Ide Latihan 2 (Jobsheet 12): kolom jatuh tempo, otomatis 14 hari setelah tanggal_pinjam
-- Jalankan: psql -d simpus_mini -f sql/04_latihan_jatuh_tempo.sql

ALTER TABLE peminjaman ADD COLUMN IF NOT EXISTS tanggal_jatuh_tempo DATE;

UPDATE peminjaman
SET tanggal_jatuh_tempo = tanggal_pinjam + INTERVAL '14 days'
WHERE tanggal_jatuh_tempo IS NULL;
