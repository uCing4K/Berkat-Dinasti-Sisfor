# 📋 LAPORAN PROGRESS PENGEMBANGAN SISTEM INFORMASI
## Sistem Manajemen Pesanan Khusus Bakery & Pastry — Berkat Dinasti (Dynasty Food & Beverage)

> **Catatan:** Dokumen ini disusun sebagai ringkasan komprehensif, padat, dan terstruktur untuk mempermudah penyalinan (copy-paste) ke template laporan progres kegiatan / mata kuliah Rekayasa Perangkat Lunak & UI/UX.

---

### 1. Latar Belakang Pemilihan Mitra

* **Profil Mitra Usaha:**  
  Mitra yang dipilih adalah **Dynasty Food & Beverage** (dengan merek operasional **Berkat Dinasti**), sebuah UMKM produsen dan penyedia produk bakery & pastry yang berlokasi di **Desa Songokerto, Kecamatan Batu, Kota Batu, Jawa Timur**. Usaha ini memproduksi aneka roti hajatan (9 varian topping), bolu premium (bolu pisang & non-pisang), dan aneka roti manis berbasis sistem pemesanan di muka (*pre-order* / PO).

* **Segmen Pasar & Jangkauan Operasional:**  
  Dynasty Food & Beverage melayani dua kelompok pelanggan utama di wilayah **Malang Raya (Kota Malang dan Kota Batu)**:
  1. **Pelanggan Retail:** Pemesanan dalam jumlah menengah hingga besar untuk kebutuhan acara keluarga, hajatan, syukuran, kantor, dan arisan.
  2. **Mitra Reseller:** Toko oleh-oleh, warung rekanan, dan pedagang keliling yang memesan secara berkala untuk dijual kembali.

* **Alasan & Urgensi Pemilihan Mitra:**  
  1. **Karakteristik Produk Roti Basah Berkualitas Fresh (*Short Shelf-Life*):**  
     Produk roti basah dan bolu memiliki masa simpan yang singkat (2–3 hari). Oleh karena itu, ketepatan estimasi waktu pembuatan (*lead time* dapur) serta jadwal distribusi harus terkoordinasi secara ketat agar roti tiba di tangan pelanggan dalam kondisi prima.
  2. **Volume Pesanan Fluktuatif & Berbasis Pre-Order:**  
     Jumlah pesanan melonjak tajam pada akhir pekan dan musim hajatan. Sistem pre-order menuntut pencatatan yang sangat teliti agar kapasitas produksi tidak *overload*.
  3. **Layanan Bebas Ongkos Kirim dengan Batasan Armada Motor:**  
     Mitra menerapkan kebijakan strategis **Bebas Ongkir (Rp 0)** untuk seluruh area Kota Malang dan Kota Batu menggunakan kurir sepeda motor obrok. Armada ini memiliki batasan kapasitas angkut maksimal **150 pcs roti per perjalanan (trip)**, sehingga jadwal pengiriman harian harus diatur secara presisi.
  4. **Peluang Efisiensi Melalui Digitalisasi:**  
     Meskipun basis reseller dan pelanggannya terus bertambah, seluruh operasional mitra masih mengandalkan pencatatan manual. Transformasi digital berupa dashboard internal admin memberikan solusi nyata, terukur, dan berdampak langsung pada kelangsungan bisnis UMKM.

---

### 2. Permasalahan yang Dihadapi Mitra (Kondisi As-Is)

Berdasarkan hasil observasi lapangan dan wawancara mendalam bersama pemilik usaha, ditemukan beberapa titik kelemahan (*pain points*) operasional utama:

1. **Pencatatan Pesanan Masih Terfragmentasi & Rawan Kesalahan Manusia:**  
   Pesanan yang masuk melalui WhatsApp dan tatap muka dicatat secara manual di buku tulis atau kertas nota lepas. Hal ini sering mengakibatkan pesanan terselip, kesalahan rincian varian topping/kemasan, hingga risiko pesanan ganda (*double entry*).
2. **Tingginya Risiko Keterlambatan Pengiriman (*Missed Delivery Deadlines*):**  
   Tidak adanya kalender pengiriman terpadu memaksa admin memeriksa buku catatan secara berulang setiap hari. Akibatnya, ada risiko tanggal pengiriman terlewat dan kesulitan dalam membagi jadwal rute kurir motor obrok.
3. **Ketiadaan Database Pelanggan Terpusat (CRM Tercecer):**  
   Data identitas, alamat kirim, preferensi kemasan, dan riwayat pesanan pelanggan tidak terdata dalam database digital. Mitra kesulitan mengidentifikasi pelanggan loyal (*VIP Member*) maupun memantau keaktifan mitra reseller.
4. **Pengelolaan Piutang & Status Pembayaran Tidak Terstruktur:**  
   Pola transaksi sering melibatkan pembayaran bertahap (Uang Muka/DP, pelunasan saat kirim, atau tempo untuk reseller). Pencatatan arus kas pada buku kas manual sering menimbulkan selisih saldo serta piutang yang terlambat atau lupa ditagih.
5. **Format Faktur/Invoice yang Belum Standar:**  
   Nota tulis tangan manual tidak memiliki nomor transaksi unik, rentan hilang atau rusak, serta membutuhkan waktu lama saat pelanggan instansi meminta bukti faktur resmi.
6. **Ketiadaan Rekapitulasi Laporan Penjualan Real-Time:**  
   Pemilik usaha tidak memiliki visualisasi tren omzet harian maupun bulanan. Hal ini menyulitkan evaluasi performa bisnis, penentuan stok bahan baku, dan pengambilan keputusan ekspansi.
7. **Beban Operasional Terpusat pada Satu Individu:**  
   Belum adanya pembagian hak akses (*Role-Based Access Control*) antara pemilik toko dan staf pelaksana dalam mengelola operasional harian.

---

### 3. Kebutuhan Fungsional Sistem (Functional Requirements)

Berdasarkan analisis Data Flow Diagram (DFD Level 0, 1, dan 2) serta perancangan UI/UX (berfokus pada antarmuka *internal dashboard* admin tanpa portal publik customer), sistem menyediakan kebutuhan fungsional berikut:

* **F1: Sistem Autentikasi & Role-Based Access Control (RBAC)**  
  Menyediakan login terproteksi enkripsi `bcrypt` dengan pembagian hak akses jelas:
  * *Admin / Pemilik:* Akses penuh ke seluruh modul, termasuk master data, konfigurasi harga, laporan keuangan, dan manajemen user.
  * *Staf Operasional:* Akses operasional untuk input pesanan harian, pembaruan status pengerjaan dapur, dan pelacakan pengiriman.
* **F2: Dashboard & Ringkasan KPI Bisnis Real-Time**  
  Menampilkan ringkasan data penting harian: Total Pesanan, Pesanan Menunggu/Diproses, Total Pendapatan, dan Total Pelanggan Aktif, disertai daftar pengiriman yang jatuh tempo hari ini.
* **F3: Manajemen Pesanan & Pelacakan Kanban Board**  
  * Pencatatan pesanan lengkap (nama pemesan, item roti, varian kemasan: kardus putih/coklat/batik/mika, jumlah pesanan, waktu kirim, dan catatan khusus).
  * Penomoran invoice digital otomatis dengan format standar: `INV-YYYYMMDD-XXXX`.
  * Pelacakan status pesanan melalui papan Kanban 4 alur: **Menunggu Konfirmasi ➔ Proses Dapur ➔ Siap Kirim ➔ Selesai**.
* **F4: Kalender Deadline Pengiriman & Manajemen Armada**  
  Visualisasi tanggal kirim dalam format kalender bulanan dan rincian harian untuk mencegah keterlambatan, terintegrasi dengan perhitungan kapasitas armada motor (maks. 150 pcs/trip) dan kebijakan Bebas Ongkir se-Malang & Batu.
* **F5: Katalog Master Produk & Varian Kemasan**  
  Tabel katalog cepat tanpa beban visual gambar untuk operasional efisien. Mengakomodasi varian kemasan, batas minimum order (MOQ), harga dinamis, status ketersediaan (*Tersedia / Habis*), dan ekspor data ke format CSV.
* **F6: Manajemen Pelanggan & Pelacakan Piutang (CRM Sederhana)**  
  Penyimpanan data pelanggan dengan segmentasi (*Reguler, Reseller, VIP Member*), pencatatan saldo akumulasi piutang (`total_hutang`), dan integrasi tautan langsung ke WhatsApp (*click-to-chat*) untuk konfirmasi pesanan atau penagihan.
* **F7: Pencatatan Pembayaran & Simulator Kasir POS Thermal**  
  Pencatatan pembayaran fleksibel (Lunas, DP, atau Hutang) dengan metode Tunai atau Transfer. Dilengkapi simulator cetak nota thermal 58mm/80mm siap cetak dengan rincian invoice dan QRIS.
* **F8: Laporan Keuangan & Analisis Penjualan**  
  Rekapitulasi penjualan dengan filter rentang tanggal, grafik interaktif tren omzet 12 bulan, diagram persentase status pelunasan (Lunas vs Piutang), dan pencatatan arus kas operasional.

---

### 4. Progress Pengembangan Web Saat Ini

Pengembangan sistem telah memasuki fase implementasi penuh dan berada pada tahap produksi fungsional (*live preview*), dengan rincian pencapaian teknis sebagai berikut:

* **Arsitektur Teknis & Tech Stack:**
  * **Backend:** PHP Native terstruktur dengan pola **Model-View-Controller (MVC)** mandiri tanpa framework berat, memastikan kinerja responsif dan kompatibilitas tinggi pada lingkungan hosting FTP.
  * **Frontend:** **Tailwind CSS** dipadukan dengan desain *Modern Craft & Warm Artisan Dark Chocolate* (palette coklat hangat bernuansa toko roti), tipografi font **Inter**, dan interaktivitas menggunakan **Vanilla JavaScript**.
  * **Database:** **MySQL** terdiri dari **13 tabel fisik ternormalisasi (T1 s.d. T13)** yang mencakup 7 kelompok penyimpanan data (*D1 Produk, D2 Pelanggan, D3 Pesanan, D4 Pembayaran, D5 Pengiriman, D6 Keuangan, D7 Pengguna*).

* **Status Implementasi Modul Aplikasi (100% Core Selesai & Aktif):**
  1. **Modul Autentikasi:** Fitur login multi-role, validasi sesi, enkripsi keamanan, dan proteksi rute telah aktif.
  2. **Modul Beranda (Dashboard):** 4 Kartu KPI tersambung ke database real-time, badge jadwal pengiriman mendesak hari ini, dan jalan pintas (*quick action*) tambah pesanan.
  3. **Modul Pesanan:** Papan Kanban 4 kolom interaktif, generator invoice `INV-YYYYMMDD-XXXX`, modal pesanan dinamis dengan kalkulasi harga otomatis, dan sinkronisasi instan ke database.
  4. **Modul Produk:** Sinkronisasi 1:1 katalog master resmi Berkat Dinasti (Roti Hajatan 9 topping, Bolu Pisang/Non-Pisang, Roti Manis), fitur CRUD produk, filter kategori, dan ekspor CSV.
  5. **Modul Pelanggan:** Manajemen profil pelanggan retail & reseller, kalkulator piutang otomatis, dan tombol WhatsApp direct chat.
  6. **Modul Kalender:** Grid kalender interaktif bulanan dengan penanda tanggal kirim dan rincian pesanan harian.
  7. **Modul Laporan:** Grafik tren pendapatan 12 bulan berbasis canvas, donut chart status pembayaran, rekap mutasi kas, dan filter tanggal dinamis.
  8. **Modul Pengaturan:** Konfigurasi identitas UMKM Berkat Dinasti, simulator nota kasir thermal 58mm & 80mm, serta panel manajemen user akun staf.

* **Penyelarasan Desain & Quality Assurance (QA):**
  * Telah dilakukan penyelesaian terhadap **19 temuan bug audit UI/UX** (penyeragaman styling topbar, konsistensi breadcrumbs, format tanggal singkatan bulan, integrasi sticky footer pada modal, dan perbaikan tampilan nota thermal).
  * Seluruh script PHP telah lolos uji sintaks bebas error (`php -l`) tanpa peringatan runtime.

* **Integrasi CI/CD & Deployment Server Live:**
  * Sistem terintegrasi dengan **GitHub Actions** (`.github/workflows/main.yml`) sehingga setiap pembaruan kode pada branch `main` langsung ter-deploy otomatis via FTP ke server hosting.
  * **Domain Live Preview:** [https://ex5.ucing4k.my.id/](https://ex5.ucing4k.my.id/)
  * **Kredensial Testing Admin:**
    * *Username:* `admin`
    * *Password:* `kekuatanadmindinasti`

