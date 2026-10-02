# 🍞 Sistem Informasi Berkat Dinasti

Selamat datang di repositori resmi **Sistem Informasi Berkat Dinasti**. Aplikasi ini adalah sistem manajemen pesanan khusus (Internal Admin) untuk UMKM Bakery & Pastry yang dirancang untuk memudahkan pencatatan pesanan, penjadwalan pengiriman, hingga pelaporan pemasukan.

🔗 **Live Preview (Auto-Deploy):** [ex5.ucing4k.my.id](https://ex5.ucing4k.my.id)

---

## 🚀 Fitur Utama
- **Manajemen Pesanan:** Alur (*User Flow*) pencatatan pesanan baru yang terstruktur.
- **Auto Invoice:** Pembuatan format tagihan otomatis `INV-YYYYMMDD-XXXX`.
- **Kalender Deadline:** Pemantauan jadwal dan deadline pengiriman pesanan.
- **Laporan Pemasukan:** Rekapitulasi data transaksi.
- **Role-Based Access Control:** Pemisahan hak akses antara **Admin** dan **Staf**.

## 🏗️ Arsitektur & Desain
Repositori ini dilengkapi dengan dokumentasi terstruktur di dalam folder `docs/` yang mencakup:
1. **[Arsitektur Sistem (DFD)](docs/01-Architecture)**: DFD Level 0 (*Context Diagram*) hingga Level 2, lengkap dengan Kamus Data.
2. **[UI/UX Guidelines](docs/03-UI-UX-Guidelines)**: Menggunakan font **Inter**, dengan fokus pada kemudahan Admin (tanpa portal *customer*). Tampilan dibangun *Pixel-Perfect* berdasarkan referensi Figma.
3. **Database**: Semua *schema*, *trigger*, dan *stored procedure* tersimpan rapi di folder `database/`.

## ⚙️ Tech Stack & Deployment
- **Stack**: PHP, HTML, CSS, Tailwind CSS (via CDN).
- **Database**: MySQL.
- **CI/CD (Auto-Deploy)**: Repositori ini sudah dilengkapi dengan **GitHub Actions**. Setiap kode yang di-`push` ke branch `main` akan otomatis ter-deploy (*upload*) ke server cPanel via FTP dalam hitungan detik.

---

## 🤝 Panduan Setup Lokal (Untuk Developer)
Bagi tim yang ingin menjalankan project ini di komputer masing-masing, ikuti langkah berikut:

1. **Clone** repository ini ke folder `htdocs` (jika pakai XAMPP) atau folder `www` (jika pakai Laragon):
   ```bash
   git clone https://github.com/uCing4K/Berkat-Dinasti-Sisfor.git
   ```
2. Buat database baru di phpMyAdmin lokalmu dengan nama `berkat_dinasti`.
3. Import file `.sql` terbaru yang ada di folder `database/` ke dalam database tersebut.
4. Buka aplikasi di browser lewat `http://localhost/Berkat-Dinasti`.
5. *(Catatan: File `config/config.php` sudah disetting pintar. Dia akan otomatis menggunakan kredensial root/kosong di localhost, dan otomatis menggunakan password cPanel saat online di server).*

---
*© 2026 Berkat Dinasti. All rights reserved.*
