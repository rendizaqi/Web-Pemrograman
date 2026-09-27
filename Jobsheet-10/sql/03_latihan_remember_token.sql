-- Latihan 6.4 no. 2 (Ingat Saya): simpan HASH token remember-me per user
ALTER TABLE users ADD COLUMN IF NOT EXISTS remember_token VARCHAR(64);
