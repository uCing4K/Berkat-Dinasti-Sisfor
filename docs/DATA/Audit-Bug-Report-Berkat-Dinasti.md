# 🐞 Laporan Audit Mendalam: Temuan Bug & Evaluasi UI/UX Website Berkat Dinasti

* **Target URL**: `https://ex5.ucing4k.my.id/public/login.php`
* **Nama Sistem**: Berkat Dinasti — Sistem Manajemen Pesanan Bakery & Pastry UMKM
* **Waktu Audit**: 26 Oktober 2026
* **Kategori Pengujian**: Fungsionalitas, UI/UX Visual, Interaktivitas, Responsivitas Mobile, Konsistensi Data, dan Aksesibilitas.

---

## 📌 Ringkasan Eksekutif (Executive Summary)
Secara visual, antarmuka Berkat Dinasti memiliki tema palet warna oranye/warm yang elegan, modern, dan tata letak yang rapi. Namun, setelah dilakukan audit dan *deep testing* secara mendalam pada setiap elemen dan interaksi, ditemukan **19 temuan bug dan ketidaksempurnaan**, yang terbagi dalam:
1. **Critical/Functional (6 Masalah)**: Tombol-tombol penting hanya memanggil `alert()` dummy, data tidak tersimpan permanen, dan fungsi update status gagal memperbarui baris pesanan.
2. **UI/Layout & Visual (4 Masalah)**: Pemotongan kolom tabel (*table truncation*), elemen tombol mengambang (*floating link*) di posisi janggal, tumpang-tindih banner notifikasi, dan modal yang terpotong di layar rendah.
3. **UX & Interactivity (5 Masalah)**: Navigasi pagination palsu di semua tabel (*dead pagination*), link `href="#"` buntu, kalender non-interaktif padahal diinstruksikan untuk diklik, dan Kanban card tidak bisa di-*drag-and-drop*.
4. **Data Integrity (2 Masalah)**: Tanggal aplikasi dan widget kalender di-hardcode ke September 2025 (`26 Sep 2025`), tidak dinamis mengikuti waktu server.
5. **Aksesibilitas & Standar Teknis (2 Masalah)**: Peringatan Tailwind CDN di production, 18+ input form tanpa `<label>`/`id` terkait, dan 404 pada `favicon.ico`.

---

## 🛑 Kategori 1: Bug Fungsionalitas & Dummy Action (Critical / Major)

### 1. Tombol Form & Action Hanya Menjalankan `alert()` Dummy (Tidak Ada Logika Nyata)
* **Lokasi**: 
  - `pesanan.php`: Tombol **"Konfirmasi Semua Siap Kirim"** (`alert('Semua pesanan berhasil dikonfirmasi...')`)
  - `pesanan.php`: Tombol **"Cetak Rekap Surat Jalan (Hari Ini)"** (`alert('Mencetak rekap...')`, tidak membuka print dialog atau generate PDF)
  - `pesanan.php`: Tombol **"+ Tambah Catatan"** (`alert('Fitur Tambah Catatan Dapur...')`)
  - `pesanan.php`: Tombol **"Unduh Rekap"** (`alert('Mengunduh dokumen...')`, berbeda dengan `beranda.php` yang sudah bisa ekspor CSV)
  - `pelanggan.php`: Tombol **"Hapus Pelanggan"** (`handleDeleteCustomer` hanya menjalankan `confirm()` lalu `alert()`, baris tabel pelanggan tidak terhapus)
  - `pelanggan.php`: Modal **"Tambah / Edit Pelanggan"** (`handleSaveCustomer` hanya mencegah form submit `e.preventDefault()`, menampilkan `alert()`, dan menutup modal tanpa menyimpan data ke backend)
  - `laporan.php`: Tombol **"Terapkan Filter"** (`applyPeriodFilter` hanya `alert()`)
  - `laporan.php`: Tombol **"Unduh Excel"** (`handleExportExcel` hanya `alert()`)
  - `pengaturan.php`: Tombol **"Ubah Logo"** & **"Hapus"** (`alert()`, tidak ada `<input type="file">`)
  - `pengaturan.php`: Tombol **"Test Print Nota Struk"** (`alert()`)
  - `pengaturan.php`: Tombol **"Simpan Pengaturan Toko"** (`handleSaveStoreInfo` hanya `alert()`)
* **Dampak**: Pengguna atau admin kasir yang mengira aksi mereka berhasil disimpan ternyata kehilangan data begitu halaman di-refresh.
* **Rekomendasi**: Hubungkan formulir ke endpoint backend PHP (`action="...php"` method POST atau via AJAX `fetch()`) untuk menyimpan ke database/session riil, dan buka `window.print()` untuk cetak nota/rekap.

---

### 2. Gagal Update Status Pesanan pada Modal Detail di `beranda.php`
* **Lokasi**: `beranda.php` -> Modal `orderDetailModal` -> Tombol `Oven/Proses`, `Siap Kirim`, `Selesai`.
* **Kondisi**:
  - Saat memilih pesanan `#ORD-20250926-01` dan menekan status **"Siap Kirim"**, form `updateStatusForm` melakukan submit via POST ke `beranda.php`.
  - Halaman dimuat ulang, tetapi baris tabel tetap memiliki `data-status="proses"` dan label status tidak berubah menjadi "Siap Kirim".
  - Tidak ada feedback toast sukses/gagal yang muncul ke pengguna.
* **Dampak**: Admin operasional tidak dapat memproses alur pesanan dari dashboard beranda.
* **Rekomendasi**: Periksa penanganan `$_POST['action'] === 'update_status'` di file `beranda.php`, pastikan status tersimpan ke database/session dan render feedback alert toast.

---

### 3. Tidak Ada Respons / Feedback pada Form Reset Password
* **Lokasi**: `https://ex5.ucing4k.my.id/public/reset-password.php`
* **Kondisi**:
  - Menginput username `admin` lalu mengklik tombol **"Kirim Permintaan Reset"**.
  - Form melakukan POST ke halaman yang sama, namun halaman hanya me-reload tampilan tanpa memunculkan pesan konfirmasi atau alert sukses ("Tautan reset telah dikirim ke admin" / banner hijau).
* **Dampak**: Pengguna bingung apakah permintaannya sudah terkirim atau gagal.
* **Rekomendasi**: Tambahkan logika `if ($_SERVER['REQUEST_METHOD'] === 'POST')` di PHP untuk menampilkan alert banner sukses berwarna hijau.

---

## 🎨 Kategori 2: Bug UI / Visual & Tata Letak (Layout Glitches)

### 4. Tabel Terpotong Secara Horizontal (*Horizontal Table Truncation*)
* **Lokasi**: 
  - `produk.php`: Lebar tabel 1130px di dalam kontainer 975px. Kolom **"STATUS"** terpotong teksnya (hanya terbaca "Akt", "Nona"), dan seluruh kolom **"AKSI"** (tombol Edit & Hapus) tersembunyi di luar layar kanan tanpa indikator scroll.
  - `laporan.php`: Tabel riwayat pesanan (lebar 1033px di dalam kontainer 975px). Kolom **"Status Bayar"** terpotong di tepi kanan card.
* **Dampak**: Admin tidak menyadari adanya tombol Edit & Hapus di halaman produk sebelum menggeser layar secara manual.
* **Rekomendasi**: 
  - Sesuaikan *padding* kolom (`px-3` bukan `px-5`).
  - Atur `min-w-full` dan jadikan kolom aksi memiliki opsi *sticky right* atau kurangi ukuran kolom deskripsi/nama.

---

### 5. Tumpang-Tindih Banner Alert pada `login.php`
* **Lokasi**: `https://ex5.ucing4k.my.id/public/login.php?status=logged_out`
* **Kondisi**:
  - Setelah logout (`?status=logged_out`), jika pengguna mencoba login dengan password salah, sistem menampilkan **DUA BANNER SEKALIGUS**:
    1. Banner hijau: *"Anda telah berhasil keluar dari sistem."*
    2. Banner merah: *"Username atau password yang Anda masukkan salah. Silakan periksa kembali."*
  - Dua banner yang bertumpuk mendorong input form ke bawah dan membuat tombol **"Masuk"** terpotong di luar viewport.
* **Dampak**: Membingungkan secara UX (keluar atau salah password?) dan merusak estetika halaman login.
* **Rekomendasi**: Jika ada `$login_error`, abaikan parameter `$_GET['status'] == 'logged_out'` agar banner hijau tidak dirender bersamaan dengan banner merah.

---

### 6. Elemen Tombol "Kembali ke Login" Mengambang di `reset-password.php`
* **Lokasi**: `reset-password.php`
* **Kondisi**:
  - Tombol `<- Kembali ke Login` diberi class `absolute top-8 left-8` di dalam pembungkus `flex-1 relative`.
  - Pada layar desktop lebar, tombol ini melayang di area kosong putih di sebelah kiri atas kartu form.
  - Di bagian bawah form di dalam kartu, sudah ada link serupa: `<- Kembali ke Halaman Login`.
* **Dampak**: Visual tampak tidak simetris dan canggung (*awkward floating element*).
* **Rekomendasi**: Hapus link absolut yang mengambang di luar kartu, cukup gunakan link kembali yang sudah rapi di dalam kartu form.

---

### 7. Modal Form Tidak Memiliki Sticky Footer (Terpotong di Layar Vertikal Pendek)
* **Lokasi**: Modal `Tambah Pesanan Baru` (`orderModal`) dan `Tambah Produk Baru` (`productModal`).
* **Kondisi**: 
  - Seluruh isi modal dari judul hingga tombol Batal/Simpan berada dalam satu kontainer scroll.
  - Saat modal terbuka pertama kali di resolusi vertikal standar (misal laptop 1366x768 atau viewport pendek), tombol **"Simpan Pesanan"** atau **"Simpan Produk"** berada jauh di bawah dan terpotong.
* **Dampak**: Pengguna mengira form tidak bisa disimpan karena tidak melihat tombol aksi di layar utama.
* **Rekomendasi**: Pisahkan header (fixed top), body form (scrollable `overflow-y-auto max-h-[60vh]`), dan footer aksi (fixed bottom) agar tombol simpan selalu terlihat.

---

## 🖱️ Kategori 3: UX & Interaktivitas (Dead Links & Fake Elements)

### 8. Link Buntu Berisi Hash Kosong (`href="#"`)
* **Lokasi**:
  - `beranda.php`: Link **"Lihat Semua Pesanan"** mengarah ke `href="#"` (seharusnya ke `pesanan.php`).
  - `beranda.php`: Link **"Kelola Pengiriman"** mengarah ke `href="#"` (seharusnya ke `pesanan.php?tab=kalender`).
  - `beranda.php`: Link nomor pesanan **"#ORD-20250926-01"** di Aktivitas Terbaru mengarah ke `href="#"` (seharusnya membuka modal detail pesanan).
  - `produk.php`: Banner bawah **"Pelajari Alur Produksi"** mengarah ke `href="#"`.
* **Dampak**: Ketika diklik, layar hanya melompat ke atas (*jump to top*) tanpa melakukan aksi apapun.
* **Rekomendasi**: Arahkan ke URL tujuan yang relevan atau pasang handler JavaScript yang sesuai.

---

### 9. Pagination Palsu / Statis di Semua Tabel
* **Lokasi**:
  - `produk.php`: Menampilkan teks *"Menampilkan 7 dari 7 produk bakery"*, tetapi memiliki tombol halaman `1`, `2`, `3` yang tidak memiliki event listener (`onclick: null`, `href: null`).
  - `pelanggan.php`: Menampilkan tombol `Prev`, `1`, `2`, `3`, `...`, `10`, `Next` yang semuanya tidak berfungsi.
  - `laporan.php`: Menampilkan pagination angka `1`, `2`, `3`, `10` yang statis dan mati.
* **Dampak**: Menipu ekspektasi pengguna yang mengira bisa membuka halaman berikutnya.
* **Rekomendasi**: Sembunyikan pagination jika jumlah data tidak melebihi limit per halaman, atau terapkan pagination dinamis via query string (`?page=2`) atau JavaScript slicing.

---

### 10. Navigasi Kalender Tidak Interaktif
* **Lokasi**:
  - `beranda.php`: Tombol bulan sebelumnya (`<`) dan berikutnya (`>`) pada widget kalender mini tidak memiliki fungsi `onclick`.
  - `pesanan.php` (Tab Kalender): Teks petunjuk menuliskan: *"Klik tanggal untuk melihat daftar pengiriman roti dan status kurir."* Namun seluruh elemen tanggal (25, 26, 27, 29) tidak memiliki atribut `onclick` maupun event listener. Mengklik tanggal apapun tidak memperbarui daftar pengiriman di bawahnya.
* **Dampak**: Interaktivitas yang dijanjikan di teks antarmuka tidak terwujud dalam fungsionalitas nyata.
* **Rekomendasi**: Tambahkan fungsi `selectDeliveryDate(day)` untuk menyaring daftar pengiriman di bagian bawah secara dinamis sesuai tanggal yang diklik.

---

### 11. Kartu Kanban Tidak Dapat Digeser (*Not Draggable*) & Kolom Workflow Kurang
* **Lokasi**: `pesanan.php` (Tab Kanban Board).
* **Kondisi**:
  - Kartu pesanan memiliki ikon *drag handle* titik enam (`:::`) yang mengindikasikan kartu bisa ditarik, namun atribut `draggable="true"` dan event handler `ondragstart` tidak ada.
  - Alur kolom Kanban hanya ada **"Pending"** dan **"Selesai"**, sedangkan di operasional bakery dan modal pesanan terdapat status **"Oven/Baking"** dan **"Siap Kirim"**.
  - Mengklik kartu di Kanban tidak membuka rincian pesanan.
* **Dampak**: Admin operasional tidak dapat memindahkan kartu pesanan antar status secara visual.
* **Rekomendasi**: Tambahkan HTML5 Drag-and-Drop sederhana atau pasang perpindahan status saat kartu di-*drop*, serta lengkapi kolom Kanban sesuai tahapan dapur.

---

### 12. Tombol Notifikasi dan Avatar Header Tidak Berfungsi
* **Lokasi**: Header navigasi atas di seluruh halaman (`beranda.php`, `produk.php`, `pesanan.php`, `pelanggan.php`, `laporan.php`, `pengaturan.php`).
* **Kondisi**:
  - Ikon Lonceng Notifikasi memiliki dot indikator oranye (menandakan ada notifikasi belum dibaca), tetapi mengkliknya tidak memunculkan *popover* / *dropdown list* notifikasi.
  - Avatar User di kanan atas juga tidak memiliki menu dropdown (*Profil*, *Ganti Password*, *Logout*).
* **Rekomendasi**: Tambahkan dropdown panel notifikasi sederhana dan menu pengguna.

---

## 📅 Kategori 4: Konsistensi Data & Hardcoded Values

### 13. Tanggal Header & Kalender Di-hardcode ke September 2025
* **Lokasi**: Seluruh halaman aplikasi.
* **Kondisi**:
  - Badge kalender di header selalu bertuliskan: `Jumat, 26 Sep 2025`.
  - Kalender mini beranda terkunci di `September 2025` dengan tanggal aktif 26.
  - Format kode pesanan memakai `#ORD-20250926-xx`.
* **Dampak**: Aplikasi terlihat seperti mockup atau data usang karena tidak mencerminkan waktu nyata saat ini (Oktober 2026).
* **Rekomendasi**: Gunakan fungsi PHP dinamis:
  ```php
  <?php echo date('D, d M Y'); ?>
  ```
  atau sesuaikan dengan format bahasa Indonesia (`IntlDateFormatter`).

---

## ⚙️ Kategori 5: Standar Teknis, Linter & Aksesibilitas

### 14. Peringatan Tailwind CDN di Mode Produksi
* **Lokasi**: Console browser di seluruh halaman.
* **Log Error**:
  ```
  cdn.tailwindcss.com should not be used in production. To use Tailwind CSS in production, install it as a PostCSS plugin or use the Tailwind CLI.
  ```
* **Dampak**: Performa halaman lambat karena compiler Tailwind JS harus mem-parse seluruh DOM setiap kali navigasi halaman, dan berisiko mengalami *flash of unstyled content* (FOUC).
* **Rekomendasi**: Kompilasi Tailwind CSS menjadi file CSS statis (`assets/css/style.css`) atau minified build.

---

### 15. Form Field Aksesibilitas (Missing Label & ID/Name)
* **Lokasi**: `pengaturan.php` (18 input tanpa `<label>`), `beranda.php` (5 input tanpa `<label>`), `pesanan.php` (6 form field tanpa `name`/`id`).
* **Log Warning**:
  ```
  [issue] No label associated with a form field
  [issue] A form field element should have an id or name attribute
  ```
* **Dampak**: Menurunkan nilai SEO/A11y (WCAG 2.1) dan menyulitkan pengguna disabilitas yang memakai pembaca layar (*screen reader*).
* **Rekomendasi**: Pastikan setiap input memiliki `<label for="inputId">` atau `aria-label` yang sesuai, serta atribut `name` untuk form submit.

---

### 16. Error 404 pada Favicon
* **Lokasi**: `https://ex5.ucing4k.my.id/favicon.ico`
* **Log Error**: `Failed to load resource: the server responded with a status of 404 ()`
* **Dampak**: Browser melakukan request otomatis setiap halaman dimuat dan mencatat error merah di console.
* **Rekomendasi**: Tambahkan tag `<link rel="icon" type="image/png" href="assets/images/logo.png">` di bagian `<head>` seluruh file template.
