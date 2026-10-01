# Berkat Dinasti - Simple Dashboard Tester

Aplikasi web sederhana untuk menguji bahwa database Berkat Dinasti berjalan baik.

## Fitur

- Dashboard ringkas: total pelanggan, produk, pesanan, status pending, ringkasan kas.
- CRUD Pelanggan.
- CRUD Kategori.
- CRUD Produk.
- CRUD Varian Produk.
- CRUD Pesanan (basic).
- CRUD Pembayaran (tambah/hapus).
- Health check koneksi database di endpoint `/health`.

## Konfigurasi Hosting (ucing4k.my.id)

Default konfigurasi aplikasi sudah disesuaikan dengan hosting Anda:

- Domain: `ucing4k.my.id`
- DB Host: `localhost`
- DB Name: `rodd1157_berkat_dinasti_db`
- DB User: `rodd1157_dinasti`
- DB Password: `rodd1157_dinasti`

Anda tetap bisa override lewat environment variable jika perlu.

## Prasyarat

- Python 3.10+
- MySQL/MariaDB yang sudah diimport dari dump:
  - `database/rodd1157_berkat_dinasti_db.sql`

## Cara Menjalankan

1. Masuk ke folder project ini:
   - `cd simple-dashboard`
2. Buat virtual env (opsional tapi disarankan):
   - `python -m venv .venv`
   - `.venv\Scripts\activate`
3. Install dependency:
   - `pip install -r requirements.txt`
4. Copy file env:
   - `copy .env.example .env`
5. Ubah isi `.env` sesuai koneksi database Anda.
6. Jalankan aplikasi:
   - `python app.py`
7. Buka browser:
   - `http://127.0.0.1:5000`

## Catatan Uji

- Jika endpoint `/health` mengembalikan status `ok`, koneksi aplikasi ke DB berhasil.
- Operasi CRUD akan memicu trigger/proses DB sesuai schema (misal update status bayar melalui tabel pembayaran).
- Untuk skenario lebih lengkap (misal detail pesanan), Anda bisa tambah modul lanjutan di atas fondasi ini.

## Deploy di cPanel Python App

1. Upload folder `simple-dashboard` ke server.
2. Di cPanel, buka **Setup Python App** lalu buat app baru:
   - Python version: sesuai yang tersedia (disarankan 3.10+)
   - Application root: path folder `simple-dashboard`
   - Application URL: `ucing4k.my.id`
   - Application startup file: `passenger_wsgi.py`
   - Application entry point: `application`
3. Install dependency di terminal cPanel:
   - `pip install -r requirements.txt`
4. Upload file `.env` (sudah tersedia di project ini) ke dalam folder aplikasi.
5. Atau set environment variable manual di cPanel (opsional, akan override `.env` jika sama):
   - `DB_HOST=localhost`
   - `DB_NAME=rodd1157_berkat_dinasti_db`
   - `DB_USER=rodd1157_dinasti`
   - `DB_PASSWORD=rodd1157_dinasti`
   - `APP_DOMAIN=ucing4k.my.id`
6. Restart Python App dari cPanel.
7. Verifikasi:
   - `https://ucing4k.my.id/health`
