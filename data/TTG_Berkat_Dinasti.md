# Dokumen Deskripsi Karya Terapan (TTG)
## Aplikasi Basis Data Berkat Dinasti

> Dokumen ini mengikuti struktur TTG pada contoh yang kamu berikan (Judul/Nama Produk s.d. Gambar Produk).
> Beberapa bagian identitas (nama lengkap/NIM/dosen) masih placeholder dan perlu kamu isi.

---

## Ringkasan (1 halaman)
Berkat Dinasti adalah rancangan dan implementasi basis data relasional untuk mendukung aplikasi manajemen pesanan roti berbasis pre-order (ForHomie), mencakup pengelolaan pelanggan, produk, varian (kemasan/harga), pesanan, pembayaran, pengiriman, serta pencatatan kas. Sistem memindahkan proses pencatatan manual (nota/buku/WhatsApp) menjadi data terstruktur di MySQL, sehingga proses operasional lebih mudah ditelusuri, konsisten, dan siap untuk pelaporan.

---

## Dokumen Deskripsi Karya Terapan

| No | Komponen | Isi |
|---:|---|---|
| 1 | **Judul/Nama Produk** | **Rancangan Database Aplikasi Manajemen Pesanan Roti & Pelanggan (Berkat Dinasti / ForHomie)** |
| 2 | **Nama Pencipta** | **Ketua:** _(isi nama lengkap + NIM)_  \
**Anggota:**  \
- Sipa _(isi nama lengkap + NIM)_  \
- Adit _(isi nama lengkap + NIM)_  \
- Bia _(isi nama lengkap + NIM)_  \
- Dzay _(isi nama lengkap + NIM)_  \
**Pembimbing 1:** _(isi nama + email)_  \
**Pembimbing 2:** _(isi nama + email)_  \
**Validator:** _(isi nama + email)_ |
| 3 | **Jenis Produk** | **Rancangan Database / Sistem Informasi Manajemen Pesanan UMKM** (MySQL) |
| 4 | **Link Produk** | **Hosting/Preview:** https://ucing4k.my.id/  \
**Alternatif (dashboard PHP):** https://ucing4k.my.id/simple-dashboard-php/ |
| 5 | **Deskripsi Produk** | Rancangan database Berkat Dinasti dirancang untuk mendigitalisasi dan mengoptimalkan pengelolaan operasional UMKM roti berbasis pre-order (ForHomie), khususnya pada proses manajemen produk, varian (kemasan/harga), pelanggan, pesanan, detail pesanan, pembayaran (DP/cicilan), pengiriman, serta pelaporan kas dan pengeluaran.  \
\
Sistem menggantikan pencatatan manual yang rawan duplikasi dan kesalahan menjadi data relasional yang saling terhubung melalui foreign key. Data transaksi menjadi lebih konsisten dan mudah ditelusuri karena status pesanan dan status pembayaran tercatat terstruktur, termasuk riwayat perubahan status melalui tracking log.  \
\
Selain itu, database dilengkapi mekanisme otomatisasi melalui trigger, seperti pembuatan nomor invoice otomatis dan validasi transisi status pesanan, sehingga alur kerja lebih rapi dan meminimalkan kesalahan operasional. |
| 6 | **Tujuan/Manfaat Produk** | **Pertama,** menyediakan basis data terintegrasi untuk mengelola produk, pelanggan, pesanan, pembayaran, dan pengiriman sehingga pencatatan lebih rapi dan mudah dipantau.  \
**Kedua,** mempercepat proses operasional (pembuatan invoice otomatis, tracking status, rekap kas) dan mengurangi risiko salah catat/duplikasi melalui relasi dan aturan konsistensi data.  \
**Ketiga,** memudahkan pelaporan dan pengambilan keputusan (rekap penjualan, status piutang, arus kas) sehingga proses bisnis lebih transparan dan siap dikembangkan menjadi aplikasi yang lebih lengkap. |
| 7 | **Produk** | **Struktur Entity Relationship Diagram (ERD)** pada Berkat Dinasti (ringkasan entitas & atribut kunci):  \
\
1. **kategori**  \
- id_kategori  \
- nama_kategori  \
- deskripsi  \
- created_by  \
- updated_by  \
- created_at  \
- updated_at  \
\
2. **produk**  \
- id_produk  \
- id_kategori  \
- nama_produk  \
- deskripsi  \
- gambar  \
- shelf_life  \
- status  \
- created_by  \
- updated_by  \
- created_at  \
- updated_at  \
\
3. **varian_produk**  \
- id_varian  \
- id_produk  \
- nama_varian  \
- harga  \
- min_order  \
- stok  \
- created_at  \
- updated_at  \
\
4. **zona**  \
- id_zona  \
- nama_zona  \
- deskripsi  \
- ongkir  \
- created_at  \
- updated_at  \
\
5. **pelanggan**  \
- id_pelanggan  \
- id_zona  \
- nama  \
- alamat  \
- no_wa  \
- tipe (retail/reseller)  \
- total_transaksi  \
- total_hutang  \
- catatan  \
- created_at  \
- updated_at  \
\
6. **pesanan**  \
- id_pesanan  \
- id_pelanggan  \
- no_invoice  \
- tgl_pesan, tgl_kirim, waktu_kirim  \
- total_harga, ongkir, grand_total  \
- status  \
- status_bayar  \
- metode_bayar  \
- catatan  \
- created_by  \
- updated_by  \
- created_at  \
- updated_at  \
\
7. **detail_pesanan**  \
- id_detail  \
- id_pesanan  \
- id_varian  \
- qty  \
- harga_satuan  \
- subtotal  \
- created_at  \
\
8. **pembayaran**  \
- id_pembayaran  \
- id_pesanan  \
- jumlah  \
- tgl_bayar  \
- metode  \
- bukti  \
- keterangan  \
- received_by  \
- created_at  \
\
9. **pengiriman**  \
- id_pengiriman  \
- id_pesanan  \
- tgl_kirim, tgl_sampai  \
- status  \
- driver  \
- catatan  \
- created_at  \
- updated_at  \
\
10. **tracking_log**  \
- id_log  \
- id_pesanan  \
- status  \
- keterangan  \
- created_at  \
\
11. **pengguna**  \
- id_user  \
- username  \
- password (hash)  \
- nama_lengkap  \
- email  \
- no_hp  \
- role (admin/kasir/driver)  \
- status (aktif/nonaktif)  \
- last_login  \
- created_at  \
- updated_at  \
\
12. **pengeluaran**  \
- id_pengeluaran  \
- tanggal  \
- kategori (bahan_baku/operasional/gaji/transportasi/lainnya)  \
- deskripsi  \
- jumlah  \
- created_by  \
- created_at  \
\
13. **transaksi_kas**  \
- id_transaksi  \
- tanggal  \
- jenis (masuk/keluar)  \
- sumber_tipe (pembayaran/pengeluaran/penyesuaian)  \
- sumber_id  \
- nominal  \
- keterangan  \
- created_by  \
- created_at  \
\
14. **setting**  \
- id_setting  \
- setting_key  \
- setting_value  \
- deskripsi  \
- updated_at |
| 8 | **Tahap Pengembangan** | Pengembangan rancangan basis data Berkat Dinasti mengikuti tahapan dalam Software Development Life Cycle (SDLC) secara terstruktur, meliputi:  \
1. **Perencanaan** (identifikasi masalah & kebutuhan mitra)  \
2. **Analisis** (perumusan fitur, kebutuhan data, dan alur proses bisnis)  \
3. **Desain** (perancangan ERD, tabel, relasi, serta aturan konsistensi data) |
| 9 | **Gambar Produk** | **ERD Full**:  \
![](../erd.png) |

---

## Penjelasan Entitas (siap ditempeli gambar/screenshot)

Berikut adalah full ERD yang menggambarkan hubungan antar-entitas dalam Berkat Dinasti. Setelahnya, akan dijelaskan lebih lanjut setiap entitasnya dengan gambar atau screenshot supaya lebih jelas dan mudah dipahami.

Catatan penggunaan: pada setiap subbagian di bawah, tempelkan gambar/screenshot entitas (misalnya potongan ERD untuk tabel tersebut, atau screenshot struktur tabel dari DB/DrawSQL) tepat di area yang disediakan.

---

### Entitas `pengguna`

**[Tempel gambar/screenshot entitas pengguna di sini]**

Entitas pengguna menyimpan data akun yang mengoperasikan sistem Berkat Dinasti. Setiap pengguna memiliki identitas unik berupa id_user sebagai primary key. Informasi penting seperti username dan password (dalam bentuk hash) digunakan untuk autentikasi, sedangkan nama_lengkap, email, dan no_hp berfungsi sebagai data profil dan kontak. Kolom role membedakan hak akses (misalnya admin, kasir, dan driver), sementara status menunjukkan akun aktif atau nonaktif. Melalui entitas ini, aktivitas input dan pembaruan data dapat ditelusuri karena beberapa tabel lain menyimpan created_by/updated_by yang mengacu ke pengguna.

---

### Entitas `kategori`

**[Tempel gambar/screenshot entitas kategori di sini]**

Entitas kategori digunakan untuk mengelompokkan produk ke dalam klasifikasi tertentu agar pengelolaan katalog lebih terstruktur. Primary key id_kategori menjadi identitas unik setiap kategori, sedangkan nama_kategori dan deskripsi menyimpan informasi kategori. Atribut created_by dan updated_by (bila digunakan) mengacu pada entitas pengguna untuk merekam siapa yang membuat atau memperbarui data kategori. Dengan adanya entitas ini, produk dapat dikelompokkan sehingga proses pencarian, pelaporan, dan manajemen katalog menjadi lebih mudah.

---

### Entitas `produk`

**[Tempel gambar/screenshot entitas produk di sini]**

Entitas produk menyimpan data utama barang yang dijual. Primary key id_produk menjadi identitas unik produk, sedangkan id_kategori adalah foreign key yang menghubungkan produk dengan kategori tertentu. Atribut nama_produk dan deskripsi menjelaskan produk, sementara gambar menyimpan referensi file/foto produk. Kolom shelf_life digunakan untuk menyimpan masa kedaluwarsa dalam satuan hari, dan status menandai ketersediaan produk (tersedia/tidak tersedia). Entitas produk menjadi dasar untuk pembentukan varian (kemasan/harga) dan merupakan salah satu referensi utama dalam proses pemesanan.

---

### Entitas `varian_produk`

**[Tempel gambar/screenshot entitas varian_produk di sini]**

Entitas varian_produk menyimpan variasi dari satu produk, misalnya perbedaan kemasan, ukuran, atau bentuk penjualan. Primary key id_varian menjadi identitas unik setiap varian, sedangkan id_produk merupakan foreign key yang menghubungkan varian dengan produk induknya. Atribut nama_varian menjelaskan jenis varian, harga menyimpan harga jual, min_order menyimpan batas minimum pembelian, dan stok dapat digunakan untuk memantau ketersediaan. Entitas ini dipakai pada detail_pesanan sehingga sistem dapat mencatat item yang dipesan secara lebih spesifik dan akurat.

---

### Entitas `zona`

**[Tempel gambar/screenshot entitas zona di sini]**

Entitas zona menyimpan data wilayah pengiriman beserta biaya ongkir standar. Primary key id_zona menjadi identitas unik setiap zona, sedangkan nama_zona dan deskripsi menjelaskan cakupan wilayah. Kolom ongkir menyimpan tarif pengiriman untuk zona tersebut. Data zona digunakan oleh entitas pelanggan agar ongkir dapat ditentukan secara konsisten, serta membantu pengelompokan pengiriman berdasarkan wilayah.

---

### Entitas `pelanggan`

**[Tempel gambar/screenshot entitas pelanggan di sini]**

Entitas pelanggan menyimpan data pelanggan yang melakukan pemesanan. Primary key id_pelanggan menjadi identitas unik pelanggan, sedangkan id_zona (foreign key) menghubungkan pelanggan dengan zona pengiriman. Atribut nama, alamat, dan no_wa menyimpan informasi identitas dan kontak. Kolom tipe (misalnya retail/reseller) digunakan untuk klasifikasi pelanggan. Selain itu, total_transaksi dan total_hutang dapat digunakan untuk ringkasan akumulasi belanja dan piutang. Dengan entitas pelanggan, riwayat pesanan dapat ditelusuri dan layanan dapat dipersonalisasi berdasarkan karakteristik pelanggan.

---

### Entitas `pesanan`

**[Tempel gambar/screenshot entitas pesanan di sini]**

Entitas pesanan mencatat transaksi pemesanan yang dilakukan pelanggan. Primary key id_pesanan menjadi identitas unik pesanan, sedangkan id_pelanggan adalah foreign key yang menghubungkan pesanan dengan pelanggan. Atribut no_invoice menyimpan nomor invoice yang bersifat unik, dan pada implementasi dapat dibuat otomatis untuk memudahkan pencatatan. Atribut tgl_pesan, tgl_kirim, dan waktu_kirim merekam jadwal pesanan dan pengiriman. Kolom total_harga, ongkir, dan grand_total menyimpan ringkasan biaya. Status digunakan untuk memantau proses pesanan (misalnya pending, proses, kirim, selesai, batal), sedangkan status_bayar dan metode_bayar menyimpan informasi pembayaran. Entitas ini menjadi pusat relasi karena terhubung dengan detail_pesanan, pembayaran, pengiriman, dan tracking_log.

---

### Entitas `detail_pesanan`

**[Tempel gambar/screenshot entitas detail_pesanan di sini]**

Entitas detail_pesanan berfungsi untuk mencatat rincian item pada setiap pesanan. Primary key id_detail menjadi identitas unik untuk setiap baris detail. Foreign key id_pesanan menghubungkan detail dengan entitas pesanan, sedangkan id_varian menghubungkan item yang dipesan dengan varian_produk. Atribut qty menyimpan jumlah item yang dipesan, harga_satuan menyimpan harga pada saat transaksi, dan subtotal menyimpan hasil perhitungan (qty × harga_satuan). Dengan entitas ini, perhitungan total pesanan dapat dilakukan secara akurat dan pelaporan penjualan per item/varian dapat dibuat lebih detail.

---

### Entitas `pembayaran`

**[Tempel gambar/screenshot entitas pembayaran di sini]**

Entitas pembayaran menyimpan catatan pembayaran untuk pesanan, termasuk skenario DP atau cicilan. Primary key id_pembayaran menjadi identitas unik pembayaran, sedangkan id_pesanan (foreign key) menghubungkan pembayaran dengan pesanan yang dibayar. Atribut jumlah menyimpan nominal pembayaran, tgl_bayar menyimpan waktu pembayaran, dan metode mencatat cara bayar (misalnya cash atau transfer). Kolom bukti dapat digunakan untuk menyimpan path bukti transfer, sedangkan received_by dapat mengacu ke pengguna yang menerima atau memverifikasi pembayaran. Dengan adanya entitas ini, sistem dapat menghitung total pembayaran per pesanan dan menentukan status pembayaran secara lebih konsisten.

---

### Entitas `pengiriman`

**[Tempel gambar/screenshot entitas pengiriman di sini]**

Entitas pengiriman digunakan untuk mencatat proses distribusi/pengantaran pesanan. Primary key id_pengiriman menjadi identitas unik pengiriman, sedangkan id_pesanan (foreign key) menghubungkan pengiriman dengan pesanan terkait. Atribut tgl_kirim dan tgl_sampai mencatat waktu pengiriman dan waktu sampai. Kolom status menyimpan progres pengiriman (misalnya menunggu, dalam_perjalanan, sampai, gagal). Atribut driver dapat menyimpan informasi petugas pengantar, dan catatan dapat diisi untuk informasi tambahan. Dengan entitas ini, proses tracking pengiriman dapat dibuat lebih terstruktur dan mudah dipantau.

---

### Entitas `tracking_log`

**[Tempel gambar/screenshot entitas tracking_log di sini]**

Entitas tracking_log menyimpan riwayat perubahan status pesanan sehingga alur kerja dapat ditelusuri. Primary key id_log menjadi identitas unik log, sedangkan id_pesanan (foreign key) menghubungkan log dengan pesanan yang mengalami perubahan. Atribut status menyimpan status yang dicatat, keterangan dapat diisi alasan atau konteks perubahan, dan created_at menyimpan waktu pencatatan log. Dengan adanya tracking_log, sistem memiliki jejak audit untuk memantau perkembangan pesanan dan membantu evaluasi operasional.

---

### Entitas `pengeluaran`

**[Tempel gambar/screenshot entitas pengeluaran di sini]**

Entitas pengeluaran mencatat biaya operasional yang terjadi dalam kegiatan usaha. Primary key id_pengeluaran menjadi identitas unik pengeluaran. Atribut tanggal mencatat waktu pengeluaran, kategori mengelompokkan jenis biaya (misalnya bahan_baku, operasional, gaji, transportasi, lainnya), deskripsi menjelaskan detail pengeluaran, dan jumlah menyimpan nominal. Kolom created_by dapat mengacu ke pengguna yang mencatat data. Entitas ini mendukung pelaporan keuangan karena pengeluaran dapat direkap dan dianalisis per periode maupun per kategori.

---

### Entitas `transaksi_kas`

**[Tempel gambar/screenshot entitas transaksi_kas di sini]**

Entitas transaksi_kas berfungsi sebagai buku kas untuk mencatat arus kas masuk dan keluar. Primary key id_transaksi menjadi identitas unik transaksi kas. Atribut tanggal mencatat waktu transaksi, jenis membedakan kas masuk dan kas keluar, sedangkan nominal menyimpan nilai transaksi. Kolom sumber_tipe dan sumber_id dapat digunakan untuk mengaitkan transaksi kas dengan sumber data (misalnya pembayaran, pengeluaran, atau penyesuaian), sehingga pencatatan kas tetap konsisten dan mudah ditelusuri. Dengan entitas ini, ringkasan pemasukan/pengeluaran dapat ditampilkan dan menjadi dasar laporan kas.

---

### Entitas `setting`

**[Tempel gambar/screenshot entitas setting di sini]**

Entitas setting menyimpan konfigurasi aplikasi yang bersifat dinamis. Primary key id_setting menjadi identitas unik konfigurasi, sedangkan setting_key bersifat unik sebagai kunci pengaturan. Kolom setting_value menyimpan nilai pengaturan, dan deskripsi dapat digunakan untuk menjelaskan fungsi pengaturan tersebut. Dengan adanya entitas ini, beberapa parameter sistem dapat diubah tanpa harus mengubah kode aplikasi secara langsung.

## Catatan Pengisian (yang perlu kamu konfirmasi)
1. Nama lengkap + NIM untuk Ketua dan Anggota (Sipa/Adit/Bia/Dzay).
2. Nama Pembimbing 1 & 2 + email, serta Validator + email (kalau dibutuhkan di TTG final).
3. Judul final apakah ingin tetap “Berkat Dinasti”, atau disertai konteks usaha (mis. “Manajemen Pesanan Roti”).
4. Link produk final: apakah menggunakan domain ucing4k.my.id sebagai link utama, atau ada link aplikasi lain.
