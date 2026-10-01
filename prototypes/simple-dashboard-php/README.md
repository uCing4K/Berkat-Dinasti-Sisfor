# Simple Dashboard PHP (Tanpa Install Framework)

Versi ini dibuat khusus agar mudah dipasang di hosting shared cPanel tanpa Composer/pip.

## Teknologi
- PHP native (mysqli)
- CSS native
- Tanpa framework

## Konfigurasi
Edit file `config.php` jika diperlukan:
- host: localhost
- db name: rodd1157_berkat_dinasti_db
- db user: rodd1157_dinasti
- db pass: rodd1157_dinasti
- domain: ucing4k.my.id

## Cara Deploy di Hosting (cPanel)
1. Upload folder `simple-dashboard-php` ke `public_html` (atau subfolder yang Anda mau).
2. Pastikan file utama `index.php` ada di folder yang diakses domain.
3. Pastikan ekstensi `mysqli` aktif di hosting (umumnya aktif default).
4. Buka URL aplikasi, contoh:
   - `https://ucing4k.my.id/simple-dashboard-php/`
   - atau `https://ucing4k.my.id/` jika isinya dipindah langsung ke `public_html`.
5. Cek health:
   - `https://ucing4k.my.id/simple-dashboard-php/?health=1`

## Fitur
- Dashboard ringkas (jumlah data + ringkasan kas + order/pembayaran terbaru)
- CRUD Pelanggan
- CRUD Kategori
- CRUD Produk
- CRUD Varian Produk

## Catatan
- Hapus data bisa gagal jika terbentur foreign key, itu normal untuk menjaga integritas data.
- Untuk keamanan production, ganti password DB dan batasi akses dashboard dengan login.

## Troubleshooting

### "Unsafe attempt to load URL" atau muncul error di browser
1. Buka `https://ucing4k.my.id/simple-dashboard-php/?health=1` untuk cek database.
2. Jika JSON muncul: Database OK, masalah di browser cache.
3. Jika error 404: File index.php tidak ter-upload dengan benar.

### "Connection refused" atau "Unknown database"
1. Buka file `debug.php` di folder root aplikasi.
2. Akses `https://ucing4k.my.id/simple-dashboard-php/debug.php`.
3. Lihat informasi koneksi dan error message yang spesifik.
4. Pastikan di file `config.php`:
   - host = `localhost`
   - name = `rodd1157_berkat_dinasti_db`
   - user = `rodd1157_dinasti`
   - pass = `rodd1157_dinasti`

### Dashboard kosong atau "Cannot find table"
1. Cek apakah database sudah diimport dari dump `rodd1157_berkat_dinasti_db.sql`.
2. Hubungi support hosting untuk pastikan database sudah dibuat dan user sudah punya akses.

### Fatal error atau blank page
1. Cek file `error_log` di hosting untuk lihat error detail.
2. Pastikan PHP version minimal 7.4.
3. Pastikan extension `mysqli` aktif (bisa cek di `debug.php`).
