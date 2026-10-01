**LAPORAN PERANCANGAN**

**USER INTERFACE / USER EXPERIENCE**

**DYNASTY FOOD & BEVERAGE (BERKAT DINASTI)**

![][image1]

**Disusun Oleh:**

Aditya Widyatamaka Tri Utomo (250535623587)

Chasbiah Azzahra (250535621731)

Dzauq Bachrul 'Ulum (250535621835)

Elshifa Viorissa (250535621859)

**PROGRAM STUDI S1 TEKNIK INFORMATIKA**

**DEPARTEMEN TEKNIK ELEKTRO DAN INFORMATIKA**

**FAKULTAS TEKNIK**

**UNIVERSITAS NEGERI MALANG**

**2026**

**BAB I**

**PENDAHULUAN DAN DASAR PERANCANGAN UI/UX**

1. Latar Belakang   
   Dynasty Food & Beverage merupakan UMKM produsen dan penjual roti berbasis pre-order di Desa Songokerto, Kota Batu. Berdasarkan laporan DFD sebelumnya, penerimaan pesanan dilakukan melalui WhatsApp dan tatap muka, sedangkan pencatatan pesanan, pembayaran, dan rekap penjualan masih banyak dilakukan secara manual. Kondisi tersebut menjadi dasar pengembangan antarmuka digital untuk membantu kegiatan internal usaha.  
   Setelah dilakukan wawancara lanjutan, ruang lingkup antarmuka dipersempit agar benar-benar sesuai dengan kebutuhan mitra. Pelanggan tidak memerlukan website. Website ditujukan untuk admin/pemilik sebagai alat bantu pencatatan pesanan dan pemantauan pemasukan. Fitur utama yang dibutuhkan adalah daftar pesanan, kalender deadline pengiriman, tombol tambah pesanan, tabel jenis roti, status pesanan Proses dan Selesai, serta informasi pemasukan. Jadwal pembuatan atau produksi tidak menjadi fokus antarmuka   
2. Tujuan Perancangan   
* Merancang website internal admin yang sederhana dan sesuai alur kerja toko roti.  
*  Mempermudah pencatatan dan pencarian data pesanan.  
* Membantu admin memantau deadline pengiriman melalui kalender.  
* Menyederhanakan pengelolaan status pesanan menjadi Proses dan Selesai.  
* Menyediakan informasi jenis roti dalam bentuk tabel tanpa visual produk yang tidak diperlukan.  
* Memusatkan informasi pemasukan agar mudah dipantau oleh pemilik.  
3. Ruang Lingkup UI/UX 

| Komponen | Keputusan UI/UX |
| :---- | :---- |
| Pengguna utama | Admin/Pemilik toko |
| Customer website | Tidak dibuat; pemesanan tetap melalui WhatsApp/offline |
| Pesanan | Daftar, detail, tambah pesanan, dan perubahan status |
| Deadline | Kalender tanggal pengiriman pesanan |
| Jenis roti | Tabel nama/jenis roti dan harga; tanpa gambar |
| Status | Proses dan Selesai |
| Produksi | Tidak menampilkan jadwal pembuatan/produksi |
| Keuangan | Fokus pada pemasukan dan riwayat pemasukan |

4. Metode Design Thinking  
   Perancangan menggunakan Design Thinking Framework yang terdiri atas lima tahap: Empathize, Define, Ideate, Prototype, dan Testing. Tahapan ini digunakan agar rancangan antarmuka tidak hanya mengikuti struktur sistem pada DFD, tetapi juga menyesuaikan kebutuhan nyata pengguna.

   

**BAB II**

**EMPATHIZE \- USER RESEARCH DAN USER NEEDS**

1. ## Tujuan Empathize

   ## Tahap empathize bertujuan memahami aktivitas, kebutuhan, dan kendala admin/pemilik dalam mengelola pesanan roti. Data kebutuhan diperoleh dari laporan DFD yang telah dibuat dan wawancara lanjutan dengan mitra.

2. ## Ringkasan Kondisi Pengguna

| Aspek | Temuan |
| :---- | :---- |
| Cara menerima pesanan | Pesanan diterima melalui WhatsApp dan tatap muka/offline. |
| Pencatatan | Pencatatan sebelumnya dilakukan secara manual sehingga data perlu dicari kembali ketika dibutuhkan. |
| Pengguna sistem | Website hanya diperlukan untuk admin/pemilik, bukan customer. |
| Deadline | Admin perlu mengetahui tanggal pengiriman pesanan dengan cepat. |
| Status | Status yang dibutuhkan cukup Proses dan Selesai. |
| Produk | Jenis roti cukup disajikan dalam tabel; gambar produk tidak diperlukan. |
| Produksi | Jadwal pembuatan tidak perlu ditampilkan. |
| Keuangan | Pemilik ingin fokus pada informasi pemasukan. |

3. User Needs  
   Berdasarkan hasil empathize, admin membutuhkan sistem yang sederhana, tidak memiliki terlalu banyak menu, dan mengutamakan informasi yang digunakan setiap hari. Admin perlu dapat menambahkan pesanan dengan cepat, melihat daftar pesanan, mengetahui deadline pengiriman, memperbarui status, melihat data roti, serta mengetahui pemasukan tanpa menghitung ulang catatan manual.

4. ## Prioritas Kebutuhan

| Prioritas | Kebutuhan | Alasan |
| :---- | :---- | :---- |
| Tinggi | Tambah dan daftar pesanan | Merupakan aktivitas utama admin. |
| Tinggi | Kalender deadline pengiriman | Mengurangi risiko deadline terlewat. |
| Tinggi | Pemasukan | Menjadi fokus informasi keuangan mitra. |
| Tinggi | Status Proses/Selesai | Memudahkan pemantauan progres pesanan. |
| Sedang | Tabel jenis roti | Membantu input pesanan dan pengelolaan harga. |
| Tidak diprioritaskan | Jadwal produksi dan website customer | Tidak dibutuhkan berdasarkan wawancara lanjutan. |

 

**BAB III**

**DEFINE \- PERUMUSAN MASALAH PENGGUNA**

1. ##  Affinity Diagram

   ## Temuan wawancara dikelompokkan berdasarkan kesamaan kebutuhan agar permasalahan utama lebih mudah ditentukan.

| Kelompok | Temuan Utama |
| :---- | :---- |
| Pesanan | Daftar pesanan, tambah pesanan, detail pesanan, dan pencarian data. |
| Deadline | Kalender untuk melihat tanggal pengiriman dan pesanan terdekat. |
| Status | Status dibuat sederhana: Proses dan Selesai. |
| Produk | Jenis roti disajikan dalam tabel tanpa gambar. |
| Keuangan | Informasi pemasukan harus mudah ditemukan dan dapat dilihat berdasarkan periode. |
| Batasan | Tidak ada website customer dan tidak ada jadwal pembuatan/produksi. |

2. User Persona

| Elemen Persona | Deskripsi |
| :---- | :---- |
| Persona | Admin/Pemilik Dynasty Food & Beverage |
| Peran | Menerima pesanan dari WhatsApp/offline dan mengelola operasional pesanan. |
| Tujuan | Mencatat pesanan secara rapi, mengetahui deadline, memantau status, dan melihat pemasukan. |
| Kebutuhan | Website internal yang sederhana dan cepat dipahami. |
| Frustrasi | Pencatatan manual membuat pencarian data, pemantauan deadline, dan rekap pemasukan kurang praktis. |
| Kebiasaan | Mengelola pesanan berdasarkan informasi yang masuk dari pelanggan melalui WhatsApp atau secara langsung. |

3. ##  Pain Points

* ## Data pesanan manual lebih sulit dicari kembali.

* Deadline pengiriman berpotensi terlewat jika hanya mengandalkan catatan.  
* Status pesanan tidak dapat dipantau secara terpusat.  
* Pemasukan perlu direkap dari catatan transaksi.  
* Fitur yang terlalu kompleks justru dapat memperlambat penggunaan admin.  
4.  Whys Analysis

| Tahap | Pertanyaan dan Jawaban |
| :---- | :---- |
| Why 1 | Mengapa admin kesulitan mengelola pesanan? Karena pencatatan masih dilakukan secara manual. |
| Why 2 | Mengapa pencatatan manual menjadi masalah? Karena data harus dicari kembali saat admin membutuhkan detail atau deadline. |
| Why 3 | Mengapa pencarian data membutuhkan waktu? Karena informasi pesanan belum tersedia secara terpusat. |
| Why 4 | Mengapa informasi belum terpusat? Karena belum ada antarmuka admin yang menyatukan pesanan, deadline, status, dan pemasukan. |
| Why 5 | Mengapa antarmuka tersebut diperlukan? Agar aktivitas rutin dapat dilakukan lebih cepat dan tidak bergantung pada catatan manual. |

   Root Cause: belum adanya sistem internal sederhana yang memusatkan data pesanan, deadline, status, dan pemasukan sesuai alur kerja admin.

5. Problem Statement  
   Admin Dynasty Food & Beverage membutuhkan sistem berbasis web yang sederhana untuk mencatat dan memantau pesanan, deadline pengiriman, status pesanan, data jenis roti, dan pemasukan karena proses pencatatan manual membuat pengelolaan informasi pesanan dan keuangan menjadi kurang praktis.

6. ## How Might We

   ## Bagaimana kita dapat merancang website admin yang sederhana agar pemilik toko dapat mencatat pesanan, memantau deadline pengiriman, mengelola status pesanan, serta melihat pemasukan dengan lebih cepat dan terorganisir.

 

**BAB IV**

**IDEATE \- PERANCANGAN SOLUSI DAN ALUR**

1. ## Solusi dari How Might We

   ## Solusi yang diusulkan adalah Dashboard Admin Dynasty Food & Beverage. Dashboard berfungsi sebagai pusat informasi internal dengan CTA '+ Tambah Pesanan', ringkasan pesanan Proses dan Selesai, deadline terdekat, serta total pemasukan. Fitur sengaja dibatasi pada kebutuhan yang paling sering digunakan agar antarmuka tetap sederhana.

2. ## Daftar Fitur Prioritas

| Fitur | Fungsi UI/UX |
| :---- | :---- |
| Dashboard | Menampilkan ringkasan status pesanan, deadline, dan pemasukan. |
| Tambah Pesanan | CTA utama untuk memasukkan pesanan baru. |
| Daftar Pesanan | Menampilkan pesanan dalam tabel dan membuka detail. |
| Kalender Deadline | Menampilkan tanggal pengiriman pesanan. |
| Jenis Roti | Tabel master jenis roti dan harga. |
| Pemasukan | Menampilkan total dan riwayat pemasukan dengan filter periode. |
| Status | Mengubah pesanan dari Proses menjadi Selesai. |

 

3. ## Site Map

   LOGIN  
      |  
      \+-- DASHBOARD  
      	|-- Ringkasan Pesanan  
      	|-- Deadline Terdekat  
      	|-- Total Pemasukan  
      	|-- \+ Tambah Pesanan  
      	|  
      	\+-- PESANAN  
      	|   |-- Daftar Pesanan  
      	|   |-- Tambah Pesanan  
      	|   |-- Detail Pesanan  
      	|   \+-- Status: Proses / Selesai  
      	|  
      	\+-- KALENDER  
      	|   \+-- Deadline Pengiriman  
      	|  
      	\+-- JENIS ROTI  
      	|   \+-- Tabel Jenis Roti & Harga  
      	|  
      	\+-- PEMASUKAN  
          	|-- Total Pemasukan  
          	|-- Riwayat Pemasukan  
          	\+-- Filter Periode

4. ## User Flow Utama \- Tambah Pesanan

   ## Login \-\> Dashboard \-\> Klik '+ Tambah Pesanan' \-\> Isi data pelanggan \-\> Pilih jenis roti \-\> Masukkan jumlah \-\> Tentukan tanggal pengiriman \-\> Masukkan total/pembayaran \-\> Simpan \-\> Pesanan masuk ke daftar dengan status Proses \-\> Admin mengubah menjadi Selesai ketika pesanan telah selesai.

5. ## User Flow \- Cek Deadline

   ## Dashboard \-\> Kalender \-\> Pilih tanggal \-\> Sistem menampilkan pesanan pada tanggal tersebut \-\> Admin membuka detail pesanan \-\> Admin dapat kembali ke daftar atau memperbarui status.

6. ## User Flow \- Cek Pemasukan

   ## Dashboard \-\> Pemasukan \-\> Pilih/filter periode \-\> Sistem menampilkan total pemasukan dan riwayat transaksi \-\> Admin dapat membuka transaksi/pesanan terkait.

7. ## Wireflow

| Halaman Awal | Aksi | Halaman Tujuan |
| :---- | :---- | :---- |
| Login | Login berhasil | Dashboard |
| Dashboard | Klik \+ Tambah Pesanan | Form Tambah Pesanan |
| Form Tambah Pesanan | Simpan | Detail/Daftar Pesanan |
| Dashboard/Pesanan | Klik salah satu pesanan | Detail Pesanan |
| Dashboard | Klik Kalender | Kalender Deadline |
| Kalender Deadline | Klik pesanan pada tanggal | Detail Pesanan |
| Dashboard | Klik Jenis Roti | Tabel Jenis Roti |
| Dashboard | Klik Pemasukan | Halaman Pemasukan |

 

**BAB V**

**PROTOTYPE \- LOW-FIDELITY DAN HIGH-FIDELITY**

1. ##  Prinsip Prototype

   ## Prototype dikembangkan dalam dua tingkat. Low-Fidelity digunakan untuk memvalidasi susunan informasi, navigasi, dan prioritas tombol tanpa fokus pada visual akhir. High-Fidelity kemudian mengembangkan struktur tersebut menjadi tampilan yang mendekati produk akhir dengan typography, warna, ikon, spacing, komponen tabel, kalender, dan state tombol yang konsisten.

2. ##  Low-Fidelity Dashboard

3. ##  Rancangan Low-Fidelity per Halaman 

   

4. ##  Arah High-Fidelity

   ## 

 

**BAB VI**

**TESTING \- USER FEEDBACK**

1. ## Tujuan Testing

   ## Testing dilakukan untuk mengetahui apakah admin dapat menggunakan prototype sesuai kebutuhan nyata. Pengujian berfokus pada kemudahan menemukan fitur, memahami informasi, dan menyelesaikan aktivitas utama.

2. ## Skenario Usability Testing

| No. | Task Pengujian | Indikator Keberhasilan |
| :---- | :---- | :---- |
| 1 | Tambahkan satu pesanan baru dengan tanggal pengiriman tertentu. | Pengguna dapat menemukan CTA dan menyimpan pesanan tanpa bantuan. |
| 2 | Cari pesanan dengan deadline paling dekat. | Pengguna dapat menemukan informasi deadline melalui dashboard/kalender. |
| 3 | Ubah status pesanan dari Proses menjadi Selesai. | Pengguna memahami kontrol status dan perubahan tersimpan. |
| 4 | Cari total pemasukan pada periode tertentu. | Pengguna menemukan halaman pemasukan dan menggunakan filter periode. |
| 5 | Cari harga salah satu jenis roti. | Pengguna menemukan data pada tabel jenis roti. |

 

3. ## **Form User Feedback**

| Task | Hasil | Feedback User | Perbaikan |
| :---- | :---- | :---- | :---- |
|  | Belum diuji | Diisi setelah testing mitra | Diisi berdasarkan feedback |
|  | Belum diuji | Diisi setelah testing mitra | Diisi berdasarkan feedback |
|  | Belum diuji | Diisi setelah testing mitra | Diisi berdasarkan feedback |
|  | Belum diuji | Diisi setelah testing mitra | Diisi berdasarkan feedback |
|  | Belum diuji | Diisi setelah testing mitra | Diisi berdasarkan feedback |

 

4. ## Pertanyaan Setelah Testing

**BAB VII**

**VALIDASI UI/UX TERHADAP DFD DAN KEBUTUHAN MITRA**

1.  Pemetaan DFD ke UI/UX  
   Rancangan UI/UX tidak menampilkan seluruh proses DFD sebagai menu. DFD menggambarkan aliran dan penyimpanan data sistem secara lengkap, sedangkan UI/UX menampilkan fungsi yang perlu berinteraksi langsung dengan admin. Karena wawancara lanjutan mempersempit kebutuhan antarmuka, beberapa proses tetap dapat berada pada logika/data sistem tetapi tidak perlu menjadi menu utama.

| Kebutuhan UI/UX | Sumber Data/Proses DFD Terkait | Implementasi Antarmuka |
| :---- | :---- | :---- |
| Daftar dan tambah pesanan | Proses 3.0; D3 pesanan/detail\_pesanan | Menu Pesanan dan CTA Tambah Pesanan |
| Deadline pengiriman | Tanggal kirim pada pesanan; proses pengiriman | Kalender Deadline |
| Jenis roti | D1 Produk & Katalog | Tabel Jenis Roti |
| Status Proses/Selesai | Kelola status pesanan | Kontrol status sederhana |
| Pemasukan | Data pesanan/pembayaran dan rekap penjualan | Dashboard dan halaman Pemasukan |
| Customer | Entitas pelanggan tetap ada pada aliran bisnis | Tidak dibuatkan website customer |
| Jadwal produksi | Sebelumnya ada kalender produksi | Tidak ditampilkan karena tidak dibutuhkan mitra |

 

2. ## Perubahan Kebutuhan Setelah Wawancara Lanjutan

| Rancangan Sebelumnya | Keputusan UI/UX Terbaru | Alasan |
| :---- | :---- | :---- |
| Katalog dapat diakses pelanggan | Tidak ada website customer | Customer tetap memesan melalui WhatsApp/offline. |
| Kalender produksi | Kalender deadline pengiriman | Admin membutuhkan tanggal deadline, bukan jadwal pembuatan. |
| Status lebih banyak tahapan | Proses dan Selesai | Mitra meminta status yang lebih sederhana. |
| Produk dapat memiliki foto | Jenis roti berupa tabel | Gambar tidak diperlukan untuk pekerjaan admin. |
| Pelaporan keuangan luas | Fokus pemasukan | Kebutuhan UI utama adalah pemantauan pemasukan. |

3. ## Kesimpulan Validasi

   ## Rancangan UI/UX tetap konsisten dengan data inti pada DFD, terutama pesanan, detail pesanan, produk, pembayaran, dan informasi keuangan. Namun, bentuk antarmuka disederhanakan berdasarkan wawancara lanjutan. Penyederhanaan ini bukan menghapus seluruh struktur data DFD, melainkan menentukan informasi mana yang perlu ditampilkan kepada admin agar sistem lebih sesuai dengan aktivitas pengguna.

 

**BAB VIII**

**KESIMPULAN** 

Perancangan UI/UX Dynasty Food & Beverage menggunakan Design Thinking Framework untuk menerjemahkan kebutuhan bisnis dari DFD dan hasil wawancara menjadi antarmuka yang lebih sederhana. Pengguna utama adalah admin/pemilik, sedangkan pelanggan tetap melakukan pemesanan melalui WhatsApp atau secara offline. Fitur utama yang diprioritaskan adalah pengelolaan pesanan, kalender deadline pengiriman, tabel jenis roti, status Proses/Selesai, dan pemasukan.

Melalui tahap Empathize dan Define, ditemukan bahwa masalah utama bukan kurangnya banyak fitur, melainkan belum adanya tempat terpusat yang mudah digunakan untuk mengelola informasi penting. Tahap Ideate menghasilkan sitemap, user flow, dan wireflow yang berfokus pada aktivitas admin. Tahap Prototype selanjutnya diwujudkan dalam Low-Fidelity dan High-Fidelity, kemudian divalidasi melalui usability testing kepada mitra.

[image1]: <data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAASUAAAElCAYAAACiZ/R3AAB/FUlEQVR4XuydB3wURfvH0fdV1Lf4+vctSksCJCQEQgrpudxdCum90nsJEHoVMDTpvXekCIgNGyiKiEgRUAFRsCCKUlU6gnX+92yYzewzu3t7/RLu9/l8P7C303Z35sns7MwztWp55BHSZ5999uiOHW+0KMnNHREdGT7fGBPzamRoyIEgf7/zvj5e130bet3w9Wlwo7F33Vs+9R7/xbve47951fnfH151H/sdjht717llOn/Tt2GDGxC2mV/DHyNCgo/FRkbuiI+LXpJo1D21Zs0a/Ycf7vHCeXvkkUd3kQgh9yYmJvr171M2wxgbe7RR/Tq3TMaEuCPhwUGn25QUPh8RHNz0zTff/Bu+Fo888qgayWR8/rpx48ZW8VEtdzSuX9fhhsfUKyIBjX1Ik4Ze3Dl741338V9Dmgd+Oqi8fNAXX3zxH3ztHnnkkYu1atWq+q2L854Obup/ATdgrTT2qkdaNG1CenTpRNY+vYrcunWLOEO///472bb1NdK3rCeJbhlik1EL8Gt8Sa+LPty2uDgc3yOPPPLICTp58uT/khN0G7zrPf4rbqDm8DP1bFo09Sczp08le97fLRgHd9GJ48fJ0ytXkrjIcOLfyJsruxZiwkM+3LRpk8c4eeSRo7Rz586/xoSE1Alp6n8eN0A1/Bv5mHohPchvv/2G276ifvnlF3Lp0k/kwvnz5PDHH5GF8+eR3Mw0k5FoScJDgkhYUDOhZ4Xz0kqEKY3C3GzyxtbXyckvvySXL18mf/zxBy6GomZOm0pCgwJNr3GPcWkrAYPtkyePD5tbUfFPfG898sgjjTK1v3ujWgbvxw1MieYBfkJD//PPP3E7lujF558jXTq0I/qYKKHHhNMBAps0Ji0C/YVw69esITvf2UH27nmfnPr6a3Ljxg2cpEW6fv06+ezTY2THW9vJs5s2kiKTgYLxKDZ/6CGVFuaRoQP7ky+//AInwenggQ9IalKC5te/pn6NLzz//KZYfM898sgjpG6dO+ThBiRHWItmpHP7drhtirp58wb5/PMTQo+ExvGp9ziJjWhJRo0YJvSG3F1Qxr2mV8v2bUo445mWnEiOffIJ+emnH3E0UZs2PENaFxVo6lHlpqe/YopyL34eHnl018oYE1mmi4o8iBsLBnoUG9atxe1P1KVLl8iEsRWSsZjs9FShd/Td6dM4eLXSG9teJwvnzSXN/f3Ea2vUoC4p69GdfHjoEA4u6syZMxLjrERAQ6+rGamtpuFn45FHd42mDB/+sKn3chs3Dhbo3bz84ou4nQmC17R333mHNLljgKBHYIiLIb/++isOWqN19epVEhnaQnLf8rMzhC+ISq+yUWHB3L3GLJg1K3Dz5s1/wc/NI49qlHbv3t3Q17v+z7gBsJT3LlP8GjZn5gxx3AReadasXomDeGTSyuVLhV4UvacpiUby1Vdf4mCC4OujPxrTklD3sd/aFBX1ws/SI4+qrcrK2jzSsL5yj8hkpMgH+/bitiLo22++IUEBla8rDevXET7hu0rPmF4boRwxEWH4lEWCNNqWFJFBA/qRra+9JrxyOlrLFi8iDRkjlZ2WSn755TYOJqh96xLuGbGUlXUNws/YI4+qjUYOHarzqfv4L7hiU1oZ9bKDzjvefov06t5VCNPYZLSeXrXCos/6jtBb298UytPc3xefskj4Hgj3IdFABvbrS4YNGkhWr1xB9sGXvlNf2/2aX3rxedLUr5GQJ3xhHDlsCA4iaNWK5VwZpeU1dsHP2iOP3FqvbdmSYaq8f+LKDCTqdbJjHb///pswrwjCwFygo0cP4yBWa+yYUZIyXLhwAQcxq3PnzorxbRG+H2rAGJmjBK/Ixfk5Yl6vvQof4HgtmDuHKxclSR+/Bz97jzxyG50+ffrBgMYNr+CKC8Arwe5du3B9J2+/tV38UtbM9Jcb5gE5Qvv27uHKBIPolorGtUW4HG++sU08d/PmTbL3/ffJwvlzSZviQvLE8KFMTMcJeqsJ8XHi9AHoKWF99umnZEDf3lz57/Dn4P79DbhOeOSRS9S5ffsxXnUeA9cduKKSoYMG4LpNvjv9rXgelno4S3iuD8USWRMHC+cPEyndTfBaTcv3/m7+j8mmjRu466DERYWfwHXEI4+cJl1UVEdcKSlzZ83EdZls2/q6cA6WacBgrzMVqfAZfDvTUzEnGsda/fTjj1z+7ipYzhLUtEnls5zNP8vXXnlZcWJmYnzMh7iueOSRQ7V///4WXjJjRvAlDQ/OXr58SXhVgvPwJQ2fd5bK7gycy/H0Km1TC2h4azVs8EAu7+qgYYP6C2WF5yj3ip1k0HHXBcRFhO3Fdccjj+yqFIP+FVzxAJhpjY3Nrnd3Cudgvgys93K1Dh48wJUbY05awykpPibK4jxtFcxPkpvRDV/h2hQXkA/278dRVNUqwSDEhy+iWOPHPin7mhzQ0PsqrkseeWSL7pk5fepoXNGAXt26SColfFWj84qg1+RugnKZW+Ev92WQioaxRpBuI5m8HaX4mEguLzX8fLzI+fPncDKKyk5LEeKBgzs5Lwe0d8wSEdbiG71e/1dcwTzyyCK1DG1+ClcuoEObUlwPhYWycC7Q9FfYHUXLnnWnQcnRr08ZjiaKhrFGcuNJ1qZlTh9//BGXD0ZpPOjzEydwcorq3qWTMKkVesq3b0snY8KUAziH0/epV+d2RUXFA7ieeeSRWeXnZg/FFQrYv2+vpDdx4sRxEhvZUjgHC1/dWfQa8DEG5gfh11E2/PVr1/ApszrwwX4uHwAG4DNSksnEcRWC6xVb9d6uXVwelEUL5uHgguBaoWdLw0Fv0hL98MMPQrxm/r7k1Ze3SM5BXQn0a8yVJTIs6DCucx55JKvC3OwpXjKD2J3at5VUtrNnzgi/Q2WGyYnVQfRaWLHLLzDQ2FjR3+W+LJoTGHOcvhrWCHorOB2gqW9DHFRR7OstjEVZIvgDlZeVIcQF1yqswM0KLhcQ1NT/DK6DHnkkykvGGMG6KbZntGDeHLFbjrvs7i56TVj4mllOHP9MDBfdMlT4TRcdwcTWpnVrVnNpA9BL+cV0H69cuSI4a3ty1Eih52mpwFsATptiqdi4I4bKL0VRU2XvqBEs5iUDyvtIzsFrHS4fsHHd6kRcHz26y9VYZvV+h7bScaMjRw6L5776Un7VuTtLqZHCAC8doJeDChyuwXHL4OZMbG2aNX0al65cWaxVR9OzwmkD4DrXUnXv3FGMn5naCp/WLNrrAhczrM6drexlI/6oqKh4CNdLj+5CNWnkfVmmgnB+iajLkNtO2unDEaLXpqSQZk25+0ABj5Xr164R/m/NolwYFMZp5mal42BWC6dNUfuaqCaIC9M57CHwrAAD67gs4SbjjstrAj7l3YPrqUd3gbLTklriCoErIbye0a80lsx8dlfR61Ty1wTKz8nEjUSkqe+dFfZWfF2Mi4rg0lNaBGupflT4speTkYaDapYjZtz71Kt87Yc5Y6yy0/mvoUW5WUtwnfWoBivIv8lXuBKsXL5EUlHyc7KE33+wYkW9u4pe6zs73sanJBqHvArIITc3R004PnDyq69wMKtU1r0blzaM+7mjoLcEc5hgATarazJjYrAdeq9e3abj+utRDRN4EsQPHxzqs/r4o8p5Lvv27JH8Xt1Fdw1ZtXwZPsVppsIYEOXs99/jKKrC8YU0zp7BwaySTqYXlpqcgIO5laAHbtTFSKZX9C/vLTe36c+BAwc+iOuxRzVEuOJGhLRgqgkhL77wnPB7i8AAye81RalJRuH65LwXyOnAfvm5RcBzz27CwVWF4wNyDu6sEU4XqA4Cx3ZQVjz1YPCAftz1JOp0Y3B99qgayxAX8wR+yOxEPXgVgfEkmMhXk9Wvdy/h2tNbJeFTimKdu7HA9kWWCMe3ZrBcSThtwNV64bnNwmvaB/v24VOcJo4bK5SZXSN56+efuWsKDmhyftiwYQG4fntUjVReXh6FH+xzmzYy1YFUzicx/Q7zZGq6Nj6zXrjWkOZN8SlV/Wrq0eRlSwfAc9JTcTBV4ecAfwRGjxxOPthvvtGaE04bcLXYssBXTXMCz6NgxOC1jhVMwsTXVpidvQHXdY+qgYrz8lbih8luXvjdd6eFCvCz6S/S3SKYnAj3wdJlFFRTnpog3ktYZW+J8LNQo2vH9ji6qoryc7k0LBGEhykLsBTGHpIZFxKABdxa6hsY7Oc3Pyv5DacVFOB3Htd5j9xYpmd4nxeanT1j6mTJQwY3tNtet32tVXXSiePHxfsBrmctFXw5gk0NgC+/+ByfVtXLL70ouPbAjUuOnl074+iqgq2ncBqWiI1XUpCHT1ssXBaWZk3Mv7Z+880pISw75gabceK0BpSXF+O675EbCn9dA9/LrGAswxr/1DVFsDmBIS7aZFS+wKeqtXCD1SqYloDjHkLziLQK5n/BbHecHtS3NiWFkt+6duqAo3OCNXv4WvKy0rn0d+7c6fE64K4yvY79zj4s7FERKsfdMHZ0Nyq0eaCkoXZs2wYHkZXcCn5rhdOhsJNV9++TbuJgbnME+AgDr4Os14b3dr3L5XH8+PE6uD145EKtWLHiH/ghXblyWXyIR48cuat7R3eLoIfD1gF4RTcnXG8CLPAowGr3e/IuU/r06o6DCqKLm1sEats8Arbmemr8WPEYxqZwXsMGD8jAbcMjF6hHjx4PhQU1+5J9OLmZVcsLpk+dIhikS5d+En/zqOYKO287c0Z5giasb8QNe/LE8TiYJrUMDuLSgvWSeA0lFXUCBx8ftAjG8yBNXVS4+Fv7NtJFyDADvH/vnt1xG/HIiZoxY5Kf5KGgz6kd2rY2GSj7Lfq8GwXr/8Cv0pnvvxd6IvPmzCI9unQSvnjJjZ8AMM0CVtfDfncTxlaQ7W++QU5/+y05d+4cuXHjBs7C7irMk/rkbl2Uj4MIGjSgckMAFmsEQwI4HWvTMid4nQNjxwob4tzs9JdwW/HICRo1bFhPtUoQFNCE+6zqES8wOpMmjCOlhfmkuYr7EkcCPYb0lCThszx8IbSH6JdCnFdMeBjp2qk9yUfzrgBf7wY4GU3C6QBbXngBB7ObYHwJ8mDld8eLBcUQG/kJbjMeOVAl+bl92Qfg39hHfDiff35CmOdx6SfP6xorqMjQSNU8Tbors2ZMIz8iL5iWatSI4UQfG8WlzWLpAmEwfNTHFAY2InC0oIcUGxEmHuPdYpr5N/5h6NCh/8DtxyM7yxATNcGLmYMEi0zZOTPwoKJbhojHd7s+OXqEFOXlKE7oq06EBTWzuff74aFD4pozFmuMCB5YxxTkZAqvvY4SeHuA8dKbNytfiW9cv24yvNGSMgT6+13EbcgjO8r0kOewN7xlC6kHRPjtbtfnzARJa/Cp34AEtcwhEcY+JKn1cocRnfoECY4qIT4NvE35SsdELAFmmav5h3KkcFkoJ7/6kvvNkQps0phcv17lbaB3T6krF19vr+u4LXlkByUYdLPYG40djZl78CmJlZsIOqOSOFM/XLzIddvN0cjHnzQPyyS6rAmcsXAHoFzNQtNIQ29+DpEa8Nr+4vOb8S1yiMCBHM4flt6wX9v69alcAE0ZUN6bScG+gvlWH330oXg8a8Z0Sd6Bvo1+xG3KIxuUZNRNZW8w63Kk4M7up0pasXwpV3mAQwcP4qDVRrBkI6Bx5Wxfc0DDjk4ZRhJLl3GNvzqRWLqURCT2Jw29KhdQa6F921KH9KJg+QfOC1BSlw7tVM9rlTnHehkpSZJ85s6eKSlfi0C/sx076j2zv21VXMtQGEP6g97Y8JAg03v6d+KNh99KCnLFYyxccSjW7MjhasHAKmyCia9FDu/69Ulk0gCucdcEwg1lxKuutgF7mEB5+vS3+FbapMH9eX9HsFOummCg3VYZYqOFjTbVBG58P2Z6TDOnTZGUMzos9FPcxjyyQD26dRmu9ODpZDI10XhyrlHt5WDM0YLrDAuSLqFQIi5zLNeA7waikgdx90IJpcmMWkXrHcbRouvgAPCMqiZjXIzgt5wKJoSyZTXERn6E25pHGrRo3jwdeyOxuw1zFaFbpw52qYSuEowVsRsjyuEfEEkSShZzjfRuJtF0P/z8Qrh7hVm35ml8yzUJpwPAPC9HaunihVyesIuxmsDF89Ejh8Vj2I6djR8VHrIQtzmPVNSuXbu/sTeQnak9Y9pk4TdzonGrk+CvMIyX4QrI0rhxc64helCmSdMY7h5irlmwDXlCfCwX35GCsuH8KH6mNwe1stP1dVTFBVK/U726di3Dbc8jGW3durV2aIum39EbBwZp5zs7xBsLv6UkGsVjObG7kFYXgUHSx0RyFY8lJLo11+g8mCdM14W7lxj2dceccFyYsOsI3bp1izRpBFMm+PJS8NIqrKiwEHL+3Dnh//DWYIxj5jHVfey3YYP6JeM26BESvums4Jh14aCkV195WYyPNwB0N33/3XdcRWPx9W1R7b+cuQtwH4NNhh3fY5a+ZT3xI5LViy88L4kHvRZ7C5eNAnX6t9+qFhOb84ABs/jZr5DBzQIk6eE26BEjWOXM3ixWML7CflVQ0+cnjktu+sDyvpLze97fTTJSksVdb+U4/tlnkjj2ltIGipU8RlpEFHKNyoP9iEzqTxo3Uv6AsGrFcvzIZPUTeo6wG649hMsDDCjvIwkD/sLoOXPr9nCPCs/wX7Bgwd9xe7zr1cirwc/sTaKCr2TwWXfP7t3MLTUv/ECtAXox9ta2rbxbU5G6j3ONx4PjgekT3LO4A7z+aBH84WPjvf3WdhxEs3AZgLAWzXAwQbOmTxXDvPSi+iJgCDNq+DDJMQtuk3e1Tp8+/SB7c9hP+MGBlV1NS4VvuDXA+7w99emxY1weIiaDlFCyhGswHpxD40ZN+Wdyh4rRo/CjlJUuOlKMY63AdzrOH7ip4PKlrHtXMQy8TcA4lJJ63dlJmGpQ/3JJHt06d/b4/AZ17thmvhezwHbu7FniTevTq4fgk8cawcPBD9Yadu/ahZO2WDADG7rXOG2gSUAk10A8uI7IRN7HEkWpt4IVGSrd5FSrosMrv5Zhli1djIMK+ujDD7mwkIaa6B6H7DEbv6mfz929gHfOnOnN2BuS3ipRvFkvPreZW3Brqfbsfo97aNagZXBdSTgtCixExQ3Cg/vQtHnlbsJyHP74Y/yYbRasi8P5AC2aNsFBReGwFHPav2+vsFaOCpzvsfELcrPX4rZ618iL6SGxXxDo2iJ7CRyZYe98T00YJ3yRkPs6h92kwqC4pYL02TQojRoGer6mVSPCjdJJhxRzX7ws0cEPKvfjw8AKBvj3s0+P4ShcWMqwwQNxUFnBDscTxj4pHuPJlX26d2+O22uNV2RocG/2Jrz2ysvCzYHJYOA1cs6smeINc4XYskWEBOHTqjp3Vn6ra0/vqHrSJEDqo4gybcokm3rRVHIz9/GXYbqTMPwRhTcIHJ5y6tTXKHVlQXja7ugxJbRZ4BncZmu0SOVmkeINaFNcKLkx1r6T21OwSyst35KFC/BpReFKUsljJD5nClfZPVQfwEtBSExbmWf7P7J54wZcDTSrf1/51zYQ/s0clkwABUEPjO3xgXdP1hhGh4ZOxG23xoq9kae+rrLs8BfjvXd3iseuFLgZhfLBoKDcKx4WdIVxJQF02RO5Cu6heoOfMQCuZCyR0sJegNY3/HtsZEvuN4rWXVHkBPHxMaWioqLmuzrx92t0hV5wcX6Vy5GY8FBi1KkvMnSW2Nmy5hb0wkRNue63V906nrGjGgx4zeSeuYm8rAxcRWSF41HoJ325NW90nRuMhV68eJFseeF5svd9y+bvyenrkyclkyvpxgSUpYsWtcftuMYoJjz8Q/ZiWcHxpUuXJL+5SrSXhGfBYqn9tcOVuLqTULyAGLPziT7NwJ1TIy7ZaIqXJ8TH56o7la58+Wd//vx5XFUkUtq4kl3VDz6g8HlHCtJnx8fgtY7J+3fclmuEBvXt68/eYJjZDILPqzBZcs5M5w9sw1Y7rMp799RcCeAvFa40QHzuNK7yVkdgImdO+xCy6r3a5LlP7hOIz6ngwmkhoXCymMaqXbVJZutQk5FaxIWrruA6AJjbjptOCqb06NJZcv7NN7ZxaTpSYJDgj/CmDc+Iv7F5F+fkLMBtutqLvUBYEMheODuZy5mKCJV3EyLnFI4VTOfHcWAwG1fW6gQM5KYUpZH1ByuNB8voRf8mScUzuDiWMnJ+XS7tjR/dJ+QL+ePw1QnweY7rBIwzXbig3GvqdWc2dvfOnfApblcSwNGCL95sPm9tfxPnfx9u19VW/g29fpC7uc2a+JJB/fuJx64QfvBqY0h49iulkbcfV0mrA4mm3lDf8Y9zhoJlyPR/cPHkSMifzP0mR5ch3lweLL3Hwlhc9TRQhoK5pKEXv9nBgQ+UB6GV/G7jNABnCHYwZvOCDgNThj9w2662Ym8sbN4HUnNne+3aVfyTw8SWbfqUyfi0RKEybmkDg1tVuwFtaPSGgilk5U7eKGASiyZx8WXJ6W0yctqMCc4Ds/yd+03lm1xtjROuI0r1XE04vjVpWCvIi7qLXrJogaQMmzdvrv7eBIKbNT1LL+j7706LF97Ep4Hg5hOL9bwYHhxEPjlyBAexq27fuqXaO6JifSRTYtOf5CqkO2MsmE2mbvonZwSUMOSN4tKQI6FoJrl2vhbp3aMBd06eZVxeSkx55p9Enz9LJg33Rm4vOy1TS6hwXPCU4SzRdaNUaJHwn4cPH/bB7bzayIvZhQR2sqUCf0ZK4zb4YbDAzrfw+dIes2i16sNDB7lyANVpoDa/U1OyZg/f4JXYfOQ+kt46k0tHCXKzlojWXqMhfzhZ8e79XN5KrD9wH8lpH8il48409PHl6018LK5inK5evcrFi4sKx8Ecqj69upO87EzxOP3O9k0U3Narhbp1bj+AvQhW+JjVIQUjIEeUyUgd++QoTsJu2r79DS7PZiEwKKut4bma5NwornFrwZLP9xPH+0mM0o/f3cuFUQPnbY7NR03ly47h0nFXYNwO16HQ5oG4qkm09/33uTiw1bi91bl95Qx1Jc8CcA6c2LHHlOb+fqdxm3d7+Xo3uMFeBNXpb781O/+H7kRiCVnpKeT69es4KZuE8wBwpXNHwGgac3txDVoL6w/cz6WnBmuQKEkWjAW17f0YVwYt6FLbV5sxp8DgFK4e3br1M65uEuHwq1dq84SpVae+PilJX+7VsqPJaEWGBovHQU2bSOLgNu/WmjJlSjJbePqFYeK4scLxL7/cFi9USfihWEO+qfv5/u73rNolFacF4MrmjiRkp3ENWCvPml7bcHpqHDv4V84gAX/eqMWFVQNezXBZtJKUl8ql547IbV6g5pANxPrSBk8X9lJoc96hXSO0hRkVnIP1cOwxJbRZ4DHc9t1WbMG7mno97AV169RRPFYT7HtG09i7533SpqSIu5HWcOSw+kZ+7yvMtsWVzB1ZvVv7GI0cseljuDRlKV1CFs39H2eMMEn5I/i4CuCyWMKmjy0zpq4iKnkwV6/AQKiJ7sxjDzX1Vd/yHLxbyAnebGAyJ2jDM+skcSoqKu7H7d/tFBcZcYAW+Jn1a8ULA2dV4FzKEoUwFp0VDHRveelF7qaaA/ZaV9Oxo0e4OLEZ7v+FbdjsOsJYC26sllD25H+4dJXAxkcNGFPB8eXoPFR9/pIWnlxaPSaw4jqWGB+Hq6JdderUKS5PFlhSojRfCoQN480b19m4v4SFhbnvpMqdO3c+5sU4bmMvFI7l3lvVtH/vXvHiz549g08L6S1dvIi7yUqYyx+HB3CFcjf0uVVLOKxl1Xv3EUPuSC5tOeKSO3KGR40Fc724NOQw5I8ni96oWtJiLdEpg7m03Y2o5CFcPXOkcF4YLV+zIRy7kQYbPy46/AtsC9xGbEHbtykVLyA3M52MHTNaPLZErMdIrFs//yy5OSCYbtCrW5UzdcrUSRNRbKnwXlgArkzuRkH3fK5RWoPWr23Q68FGRwut8su5tORILF3Mlc0astq34tJ2N8J0nbn6Zm9hb6uY1SuWm/1DTUU9B1CtW7tGkha2BW6hlGTjRLkbjC/GGsFrF6QBM6qp8Ap9OUHeLYObm92VBNzd4geGK5G7kdq6O9cYraH/U49waSuBjY0laH2Nm7LhIa6M1pBS2olL290ICOL9gB+xw2ThAWi7Jwy42rFGMASz3mSMqNg0fX3q/4xtgsvFFnDN6pViwcEggGNyW0XTvnL5suQYJmWa+7yqJvzAWuq7c5XH3Vi12/bXHEpiqTZjMWGcL2doLOHsyb+ShDzz43PGotlcGa1l9e4HuPTdDbl5TPCRxxqNHDoEtuLm0qPAqoRdNjpRhHSMdyaA4g1VsU1wudjC0U/wuZlpwrE9FH9nTy1YPQ2iednyqXT/3j3cg3P3iZHGollc47MWrVMAkvP6kN+v84bGUpYu0DaYjstpC4nFc7j03Y2G3tJFvLBQ3VLhzS7k0LKcypzoAl0qNv0kg+E9bBdcJlOBfqcFM8RVeY+E40Xz54rHtgrf5AXz5uAgmvU78q4n3FSZCuNu4EZnC/qMeC59ObBxsQVjkfnxq95jG3BltQWcvjuC6+KkCeNwlVVUXGQ4F5+lNeMD3x7y82lA3nn7LeH/2A00tg0ukak3lEYLxO5RpY+NItvffEM8toeOHDksuQHWCo9HAVrHPJwBzFQOj08jc1+Svn5M2aB9Ma0WcL5y5BYmcIbFFg7vf5DLQw5cVluYvO5hSdqzX/w7iUoscLsZ4bhOmtsIAJZZ4Tgs0B6pC117Cnv42LXzHTHPyJbBX2Eb4VTNnTEjhr0JrMwtJbFWbH7g+8VSwRc7n/p1JOlEJg3gKoir6DWmkdiY2N/j7fDpn2Xeq7W5vOXARsUeJBTN5/LB4PLaiqFgpmz65RO0TVlwFo18/CV1s02Jci8n0E95MuSVK1dwcLsqyaAjE8ePFY/ZvGMjQlxnmMKaN/2eLQzVK1teUr2ZtmjB3Llifj27Sl2IalEI+vTfLDSNqxiuIj7FKDaWdfulRql9+X+5hmYLpT0f5/LHQE8CGxR7YMgo4/LCrN3Pl9kWeldIX88XbH1QPBefqufydxUJRVL/RWy7who1YhgXFvjss09xULvr66+/Jr7e9cVjcDPElgHbCqeJLcQ335wSC6h2I+2hV1/eYvaByemtN6Wr/r3r1eMqhSsw5rQni7ZVfVFbuau2UDnp+fKJ9bhGZgur37+P6HPMfwnLzNFxBsUegO8lnBcmq20sV25b6TNO2itq26fqvj5z6H6SmNeaK4crCI2p2nMQYN1HY9HdpIHXX30Vn3aoIE/6NZweUwaVl8dge+FwxUaEfsIWggq2fHHGVkk0X5gif+OGNu8AbHkBXBlcwcS1/DiR0quGvSgbI79Tb25RK7Jk/r+JsXihcAwLa7FBsRdivklNyK9XeCNlyJtEFr1h21o+Odg84EsrPj989r+4srgC7CQOxm2UlJFq+bby9hBMXYCvcfSrHtrV909sMxwu9obRATlY28YaKFad2rchxz/7DP9stbC/bHPC40jusGPtmn38fKOlb0kHghPz23FhbCUmuZgrC/DbVanh2Pri/eSXy7xBsZVNT9fmfsNlAfpN5DcasJWEXOm153UO4sKs+6C2W0wNYesroLY+zVUCZ43edSt32cUfkLDNcKhMmddmM6dT1cE7npxHyZfvLJ7NzUjFp2wSW4YPDx3Cp0XJzeHAFcDZpBREco0BaFveTBKutFcdLoytxGXIL/vAhgI49L68exJrmTXpb9xvAC4LUNhN/h7ZQrt+0lf2uMwxXBggraglVx5n4+sbLKmzy5cuxlXb5erepZNQNipJGzPoP8C2w2HyYhbdsm464fjTY8fEY/Z3QMvCP0t07NgnYtpKws6sgISiytcTV4EbAIslYa1Fyec1NhQsJz7+C/ebJWxey/eOWHTJbbnyxCSXcGW3BzgffJ5Fy7wqRxKbUSGpuzBHyN2Ul5lOVixbKvx/GVogj22HQ3Tq1KkHaIas3+3Swnxy7hzvj+WX27eFsI6aIgDvsWozVbFBcnW3HFd6lnUfWNZgrCWhZDGXD4ANBcbaMaaff+R/w7TryE/kjE0fyJXdHuB8sjuEcmHUwjsbHy8fSR3+6ssvcTV3uaBcVE+Nr3TmCES2DOmMbYhdtXnz5r80C2hykWbICh9TxYRXboHtihsZ3TJU8jDB+x9+4M5k02G+wrNktYuUhI9K7MSFsQcGmZ4SGCpsKOzB9Qv8b3KMfbIpVyZd1gSu7PYgOlm6vhE2gcBhWGDDTFw2Z8PWY6W25kplpaWY3kq+Fo/Zsvbs2bkFtiV2U3Az/3NyN6Zfn15k4fx54jFV6+ICLqyzhAfdAPygnYWhcD5X0eUw5E+XxOswoBkXxh7EZ3TmyghgQ6EEDIjLDVaznP/6HnLhm3u435V4YmQQV564tA5c2e1B2/IWXF6wDhCHwxgKXLeOTpczSVKXdVERuMq7XGw7TzbGS8qLbYndxGZSkJMlFgBeoejmdazgc72rjJI+JkpyU0JjO3AP2lms3s1XcDnwq+Ww2fadMEmJS8nlyghgQ6HGl0fv5X5jefW5+7nf1OhVJu0lAvFpOVzZ7cHgGbyHylUaXAlv+NC1PSa2PruiTZkTW6a3tm93vlFihY+p8E2s3Ka7HAezu6gPJxb8gJ1F+/5NuMqtBI47dSM/h8keDJ7+Xy4vABsKc5R1+Sf3G/D7Nf43c8Qn5XDlGTbrUa7s9mDyM//k8uozVtuuKvjrqDNpGd9DUqcXzrPfYnd76M03t5HVK1eIx2xZ01KSh2F7YrMiQpp/zGYCglekorxscupU1bskVY+uvGc9FvhqZ80uI+ZEd/dkSbgzGdDZxGWM5Cq1EnhZCbBip/m/3taw4dB9RJ87nMsPGwpngr9y6XOGCz0TXHZ7sOxtfhup8LgYLpwS8TkVXHxnYSycL6nbMJbjToIy0dUddF85wLvuY79jm2Kz2BtBfRgNGzxQOJYTNgxKwMQrXUwkjm61cPquXAluiTP/ZTt4h2Rr9/Hh7EWPkf/m8ps64T+csTDHhlXSsaWdW+/jwmgBl6Xr0P9xZbYXT+/hjVJM6gAunBLwXHF8Z4LruDspOLBybSkIj+vadecTU2L3sonTyZIwlgTTzOWEb5w5Gtava7XHPVY4XfxAnUV8tvZKDix5izdKMEUAh7MXG2W2JSooNnLGwhw7XpMaoQ2rHuDCaAGXZf1Bvsz2Ys1e3ijFZVRw4dTQZQzi0nAWoXEdJXX8lZdfws3AZZoxdbJQJiq2nFOmTLDferiYlmFfVBmPqhnbcPzOjrfFY6qivBzh3IsvPCccT530FGcs1GjZojlKUZsiw6QzYONzp3EP1FngSmyOJdt5P0O27uNmDpyfPbwCWDOXqaCI34Ybl9WerHqPN0q6rPFcOHPgNJwJbjPuJFj7+u47O4T/jx0zSlJObFusFpvoSy++IGQ2euRwMnhAP7YsopRu1NkzZ0i70mLuhqrxxratgg8kLcJx8YN0Fr0rGnIV2BzL3+F7SvNfe4ALZ086D23I5fnFUctfv55bpz49wBy4DDkd+PVo9mTuyw9xeUYldefCmaPLUF8uHWehz5shqeszZ0zDzcGlasy4NTHERYvlxLbFKu3YscOLJtihTWsxIziW06GDB4RzyYZ4fEqizz49xhkRc1y8eAEnIwp8u7BhjRq3DbI3Y5ZY98XomUP8X96KZQ9z4ewNzjM+ZwJnNMzx1Z3pAZ3bPsydM8em9fyXQFxGewP3FefZMrYlF04LFcv4sTlngduHO4ktDzu2lJZknIttjMUy6mPH0ARv3rwpmykVZF5ckCuce2PrVnxaVi2aSj3tqZGWrLzDLQ6LH6CzwJXWEnBapb3Vlz/YA2Nefy5fbDjMceOiyTB9ci9Zv9zy8SR91lBJ3oZcy1+jLKW4B7/YtrSXLxdOKzgtZ+EfGCup8z9rfKNwhoICmkjWutIy+ns3uAYrQ7Cd0azC7OwmpoT+oAlSPTFsKOndo5t4TMX66rVU8Ck/IkTqvQ6jtHHec5uflYQLjirhHqAzCIuybVU7Tk+X/RQXxhHgfDOKunDGwxzL5z+oaY0byy6ZcTRLvlhai5zbGnh9xuG0EhrFj4k5C78mlcu4APjo5C6G6ZOjR4g/s98ibO9Ey2nqXLyFbY1mhTRtcp4mFM98todjNU2ZNAH/ZLHomjkWOR3++GNJmBbhedyDcxa4slqKPkfaa7BHmlqY+fxDJLF4Bpc3NiJqvLvNshncbTsmS/KCXXpHL3bMDHYMvk573GecnjPR0k5cIbYsn336qaSM2NZoFpsIHc/57bdK30TO0to1q4W/AN9/X7WPOSv8QPADcxaJeba72Ri9iJ9p3HFAfS6co8B5J+Q/yRkTJS6c0r7ObeiAulxeMEUBl8cRlPTk847P1j7JVYmk/EIuXWfhHyBdUuUuAi8iRw5/LB7b3ShRvbltq8PckFgjdzFKAyZbN8CNweka8kZwYRxFRnF9Lv+jBx/kjIoct37if5MDpgzg7awiDYlcWRyFIZt3cJfX0Y8LZynl4x/l0nUWMemjJW1g553P8a7W0MEDybTJT4nHYDdoGbds2RKC7Y1ZpSUap9AEqBGCLY38GnqRCxeUv4I5UwP7S/dM1+dLfVs7E1xJrSU+d6rD0tbCiHn817DxTzbijAvmiyPmncBdv8gb3aRc5xkkgMvfjvcXp+tM8B9ndxD96nb69LfCMWxsQMvXtJHPj9jmmFVjnwY3aAJrn14tJJqZ2sptLhiEHwR+UM4iMj6Cq6DWsugNfvA3plV7LpwjAf9BKYVGrhxrVtThDA3l/beU5zed/pxvsIb8p8jQGY9weTuSljr+FcuYUbWtla1EGVw34B2GZnk7Yl2pNYKysJOh2TJim2NWbGQ2wSbMiLor9dNPP0ouMLrVYO5BOYOopF52XzhqyJ/E5aPPm8aFczSwHAMvZjYWzSM/nubHj955XX6gOyF/DHct41b+H5eXo8E7xFBwOFtYf+A+Eps2kMvDWbDtAdwGuYPKenTjbAhlRFnZI9juKMoU969KRikrrZV47Ephr5KuWnhb0DWaq5y2UtJdfkKeMz6XYxZuvZ+7t0ZTL2fUkH9IlpRsf1lqlF58pjaJigyWxAN/URnFj3N5OBqlBbSGXNsHuDHZ7Xm3vs6CbQ9su3WlttzZNISqsVc9sXyzZ89Ow7ZHUX3LysTxpKZ+jYTEwIkbWN8L58+LGbhS+AHgB+Qs4K8jrpj2YI3MSvaE4sVcOGfSc5SP4DqWlkefM44snP1vsnfHfWTu1IfIW6/eTzY+/Q9iyOonKXdMcqGp18Wn5yywWxRg4BTHeCGQm5nvLFrqukjahDXb2jtCiXqdsGs2aPqdBbtAw/p1bmPboyj2wl564XkhMRhJ79C2lM3LZcJLVMIN5reBdgTQg8CV0p6Ujea/6CSWqPuTdhZgjBOyQoixcKrJUM02MVf411g4g+gyupG+Y/9FnjXjk9wZwP3C9zAhrysXzp7gnqUz8WtS9Qahj41SnHDsTF27epU0D/ATj9klYdj2KIpGyGR23pTbz80V+tXUY/P1aSBelH9ANPdgnEVUUg+uQtqb7k/4cfnCa1BeJ8cvQanO5HVsLGscdJmOf2bRrfpw+ToT9g/2jKlTcBNyiaAsVIMGlFtvlCY/VTUzm03Ulfryi88lN91YNJ97KM4iPMr2+S3mUBoPAVa+61jXJtWVpTv4V1/KOge9brO0jOL/kDgTtn24y5xC1n4cO1b1prNk4UIjtj+cunds355GoKJbZLuD2BsO4AfiTBw1niTHoCnyg99xafbf1rs6E5fajrtHQJs+zutZunpLpkYNK70/4nbsSoU0b0p++OEH8ZiWLSIk+Ci2QZyCAgPO4Is5dvQoycvKEI+p2As/c+Z7fNohYvNs5OPPPRBngiujM8CzoVlGL3L+p3Z3YMgMeYNNmbOlNhfH0eAyOBND/kxJO/n0k09wM3K6tr72KgltHiges+UrLciOxHZIVLLBEMsGpmoZ3JycOP6ZeEzFhsXAl7q+ZT3JsxuewdGsFkyfZ/MAR1f4gTgTXBGdRWlZqOIut4AuYyhJLc4RFtriuNYw6/kHSfn4/5CU4lzSMi6XO6/EMwfvI1FJXU3xckifsf8ms1+0fiU+y4znHiKppRlEl8kvYKYI40l5yVxcZ4HL42zYdhLSLAA3JZeItSmBTRqL5YuLCD+ObZGosu5dhtCAEIlN7M8//xCPQdgpuBrgy3vE0MGS+NaoW6cOknTxg3A2uCI6k1XvPcDtE4dJKJpPYpMLSbvyx8ik9doNwuLt95OhMx8m8UkNTQ1/CEkoni/00KKS+wuvJji8Gus+uF+IC0YCyhOfPZwYUhqRoTMeJove0D4mNuWZB0jrno+RuJTWQnnwtWIWbXvQJfO6KLg8zga3QXcQW47C3GyxbAE+XtewLRIV4NvoMg1I3d7ixKjatynlLtwSwOitWrFc8KOkVTgN/CCciT5vOlcRXcGC1x4QtiPC5bMX8VnDyYTVtnvAXLf/ftLzibqqr5+2YiyYQkYtdO7SFSX0+a7txbeIqNqdGnCHqQEw6E7LcejgQUn5sC0SxQZinUXJjeDDNt3YSNgK7BOlJjZsYHAr7kE4krjM8ZLjmLShXEV0NZuP3Ed0yWFc2S0lMtaPjFtun1c/OaAHE6ULIPE5E7m8LUWX1pqs3KW9x+UsYlJHcGV1Nmx72fXuTtycnC5jXAxZtmSxeMyWD9siUWwgKhigyslIFY/VNH/uHGH2JjY2lqCkw4elztzwA3A041f/Q3Ick9qTq4juyLxXapPB0x4lPUd7CUtiWhWmkdTiFJLVzkC6DG1iek17hCwxva5ZOtFx08eVs5fx7wCMJVn66jRp3d/JkOn/J5QxJrmExKV1JLr0rkSX0ZnEtiolaSUppG15EBk++2FhM00c392ITZVO6O3xBL9Jg6PR2racpZ9+/JEEB/qLvSW2bIcOHWqA7ZEguQuYP3c22fjMevFYi74+eZLoY6t2MLAEJRXnV/r+puAH4GigorHHsanduYp4t7B2f22SkNuW+53FmFVA1h+wvgcDRg/GrwBLDZw7EJvaQ1JfFm2rzdUpR6O1bTlT8AHs0qVLwv/ZsvXq1mU0tke1Jk2Y0JkGoCuMYX5Sc39fNk2rdeb774UVw4nxcdzN0nLjcDj8AByJLmusUNHY3+LSenMV8W4go00CMeaPIAu2mh84z+6YY+qRxXG/3w3EpfeV1Bf4zdyHCXvTIkq6nZk7CMqxft0a4f9hQYFi2YICm3yJbVKtiNCgA2KDN1Zuj/TpsU8cejHXr10jFaOfIM1Mhg/y0UVH4CCi2JvrzPGk+OzJ5NkjlRWN/T0uo9JQ3Q1AbyWzxEu8dnxejb4TmghxSnrWr5Y9HmuJy5wgqS/wW2SsD1e/HAkYQa+6dcR2c/jjj3CzcrqgHM3ufNkfMWRwVbuu9/hv2CbV8qlf5zYNsP2NbUKkfr17OdQoadXFCxckRslQMJd7AI5i1ot/Fysa+zt8RcIVsaYAxmPB6w+Q9NatTNdZtX4spTiLC6sFeBWLS+9dOS2geBHJ6agn8155oEYbKdwror/r0tpydcyRtIjIF9tNfnYmblpOF2y9xNoUtl1jmwTjSX/Sk7/++qsQgW6D7Wq9+PxzYsF9Gjj3rw180aIVCp/DFbG6s3BbbZLTtoEwxcCQX7XlOUzUjG2VxoW3BBicHr/qEfEPirFguimfJ4T8Frzm/BnXjkaprpQ9WdXjdAbsTrqwqJ7di80Vate6xCKjJJ5kIwT6VU2idJVgb3JatnBDL+7GOwp9rvQVDfwZsedxRXRXwDPmnC0PkTZ9A0lsSh7R58/i/pKzQG9GnzuGFHevx6VlLxZurU06Dfyf6fV4gGxZ4DfwuR5nKm/bfk3JnJcesruHT0fCXktsRoXiOWfAtu2dO1y7qcCWF1/gbAxl7969D2oySuOeHC0eu0Kff35CUnB8wx0JuINlK1NMK6mPZ6VP4u6EnPsOTHzuNBKh05NlO8wPXjsKGLdavvNBEmnI5crHAsYKx3U3nt4jNTxhUdLFwIZc3kWwI2HbD/gycqVgOgB4n6SLc9myZbRq1UI0SGVlZfWVjNKOt94Sj12hfn3KXGaUcGXLaectOR+TnMeFcRfUXJ6AoYrLGErGLPonF89SoPcyfPb/keIe4WTk3EfEjwK2MnjaY0Sf2YUrO5Dg5uN5kcYsSXnHr5Te51XvKbtWcQRs+2Hbt6vUpriQTBw/Vvi/H+MbrTA36w3RKBljYwPlCg1TA77/7rR47ArBhnauMErgAB5Xtqkb/y4Jk+AmXiDl6DSI38ONMnL+/4S5RjiOJaz74D7T611Dos+pmrkMvZj47KGktOdjXHhriVDoOXUd5s2FdRdgt1+2rKt28/caX48j8fWtHBvG7dtV6tenNxnUr1z4P3yJo+UKax74vWiUOrQp3UxP+JuMAAi2aIFP9a4WezOdaZSmbPwHV5Hk/C+zA+HuAi4jkFiymDy1zrp1YfAaO/3Zfwg9Q5yuFmKT4siStx6w+l5BvMQS3s82gMO6GugpailjXMZoLpyjiEoe4lZG6YeLF0lUWIjw/wljKyRlE41ScHP/c/RHGB0HvbrlJdK+TeX/XSm2wM1DM7gb7gjUxi1w2LiUfLf6tC03aPzMQctnVq96z/QXP7+bbHq2kFA0j6QXNeby08L6A/KzonE4VwEGKS6tSFI2Y+E8LhxQsez/uOtwFPC6605GCQSvbaA339gmb5TYH59Zt1YIPGRAf7J65Qo2HZeILVtM2kjuhjuCSEMOV4kohtwBXHh97jgunLOB3gwuV3GPMC6cGtM3/53EZTrnHlOik/LJip38640axoLJXDr2GsuyhZjUIVy5iro148JRcFhHwrajJQsX4GbmdFHjCF5CZDcSYAv82isvC4HblhSRjz+SnwF6/vw5/JPDxJZNn1s1d8aR9BqlvP1OXucILjyAwzmTOS89SBIL+kvKo88ZxYVTo+84mEM0h7suZ2DIe5JM3fg3rkxKzNnyTy4NfVZP0+8PcmGdCS6T8Mq8tmryLYbdrsrRsO2oe5dOuJk5XdQogWAbN0WjBAPbP975VBd4Z783LJhYScNnpSYLO9Uq6eLFC5LRdZaRw4fi4JyOHHG+Z4DopGSu8mCgEeF4hrwRXDhnMPN5voHiMEqAZ4DYtJ5cfDWMhTNJUrYfGTLjUS49lvUf3EcGTv63yVj0s+gVMMqYSzZp8FiwbEdtklwiXcoBPLX2X1xYZ6DLGsaVJau0LheORd/KeRsMsO0IJkW7WlAOqtKiKt9PnFGCL11U9J2PFQx+g7Gi4RfMnSMstJXTiRPS+UVKqKm/C6YDaJmrAxsG4HhATrv6XFhHklIkHWMzFs7RNL4FX85yO7bkyq+ELruC5HUK4dKxhDkv/c2UZyiXthKppdlkyVvmX+twPCClKJ0L50hKezbiygDgcHJEtRrExXME3vWqdqY11+6cIbYMo0eOEMvFGaWMVkmykaje2LpVjHzMjDNy9gaoYdTFkj//kLrapUoySH0z4RvtCHClUSKpVN5H9uyXbPfSaI72/QNMvQ+pB8fEktlcODlyO7TgyqyEPrsfF98e9BoN26xr6z0ZsszPBUss4d3jwqtR3wm+XFh7M+/Vf3F5A/ldtHlHmL6Z7+k6Ar8mYZK25Go19W0kOpL85OhRsVwHDhxoKDFKnRjPj3IF79iujaaLWrp4IWd81Dh06CBOQlAo49oAwDfa3ih9KZEjMVf+83hi8VNk7iuO89q4fCf/BUqXNYaMXmT+c/+q92prmuENxGcP5uLbk15PepO4DOk4mBJ9xiqP8QGdhzTn4gAwpvP0Hsu/PGoF/IDHZ/Ov8gDMTsfh5Vj5Lv88HUFwpDY3Jt98c0pwWeRowY65V65cEf5/9eoVsVyrV6xIlBiloYMGiJHkCk7DtTa9AyoJdrBlL57l9u3bYrjLly+bvUmS+HXrcDfa7uSlcJVGDS4+w4p3lQc4rQFey2KS87l8EksWcmHleHqPtsqvS83k4rKs3l2bxLcKIBE6HdHlTuXiC2Uy9YKiknuT8Lg4Mnb5w6peLZ/e86AmQ5mYYyQbP1I2MIa8Ci4OJTYlnwtvK6MXP8LlQ0kotuxrLI7vCOAem2tvoOICqTNFSm5mOjl48AC5du2auGDfFnVs20Yy9EPzSYqPm1TrzIkT/6Y/sB4m5QpOwylZ0ps3b3IXQ/npp59wcDJp4njxvJzY+D71G3A32t7Mf838eBJL/0nKFRMo6OzPxbEUMEaGPOWJdji8HPG547h4cizYKv8FrHWZl6lHMJALbwmwk7E+R7n3pUuXX1KCgfEwHJfSd4J0GRBGnz1E05ibOXI7JXBps+Dw5gBvCTgNR8C2JyUpGSU1GjWoSwpyssiKpUskHQ81PbNuDXl/93viMU3LoItaVmvjxo06+gN03dhAWDTckSOH8SlBuLAUmLWpJBpGTmwa/gHR3E22N7iyaCG5hJ+bgpn/muWvc4vffJBEt+rHpWVpefUajMngaf/m4gFTnpH6JbcXMcnFXF4U1mWKErBtE45HMeab39klJnUQWbxd2+sVywLTc8RpsRjzhpANB/l45iju5oS3gNZSowQ9HksFH7peeG4zGV8xRhjvZZeImAML9nCETgkVDRcbGbarVsXo0W3oD/Qdj+7phlVaWOkwKiYiDJ8ip77+misIRW0bJaVCg9g0moWmczfZ3uDKooVpz/6dJBarO52DQde00lTB2RmOj1m2435iyCwS3IvgdFhyOiRycTHgwwjHwyQUjObiUXBYe9Kmb7DshMcFW80P/Ga2Vf+6hsPLEZ83nSTkFHCeIOSAZS6tCjNNvT35pS4UazfbnLzuQS4tR8C2J3Dibw99/913ZN/ePVybx2BBnC4d2onHNFzLkOaf1kpKMEzAEX/55RcS0LiheEx18eJFSUZgbG6bCG3elCsE5e23tuNkRJ09c0ax0CA2nchE9V6DragtLdGCJbv16rInknBjdxIeG0vCdRkkLkv7VkNg4HDeSuC4mISCci4OoEst4MI6CiVDjcNhej+pvkU5Dq+F+NwppGVsmgnTczH2MD1T8702ysI3LO952VpeS2Hb0+nT3+LmZrFgGAe3dyWw4K2MnS9FwwU2bvRTrajwkDk44o0bN4TRcTm99uorXIZKKI09UcG7KM6bFZsWfBnDN9meRCW25iqKpejztBsXa0gu0L5ldlJBMRefxZjbnosDpJbmcGEdDS4DsP7AA6Zr6MCFZUktKeLiUWDsqKyiMRfHEeC8rQGn6QjY9nTggw9wc7NIGSlJXHuXIzwkiLyy5SUcXXgrg40DqGh434ZeN2oZ42OX0x+oYFA6IrSFeIyl1jOiXL5cuY2Kkq4wX9/YSZus2PTwDbY3bfoEcRXFGnLah5LEEjsvH8gvsGi77Lh09Ql5SoO9C16XumaxhBRTryul2LoNGNX8nRsL1XugMJEVx8EkFM3k4tkK9KxX2HETTGf4nWfb08b1lm2bBrp+/TrXzpXQx0bh6BLB+FRQ0ybiMY3X2KcBGCXdOvoDFXiFg90s1YQLwdLUl3/1w2L9JMGMTizcNcQ32N4MnWk/H0Aj5z3MpW8txtxeXPrmwGmwxGYor4mLSijlwmshoWg2+e7LBwi5WYs7pxUltyYzX1AfbI9rZX5sbenbtYmxQH76grX0fMJ+9QWISeEXetsbtj0tWjAfNzlVnTp5kmvnSrQpVp4yRAXj1nRXExCN6+td/+daiQbdS/QHqgsXzgve4cwJrB2MCw0Z2F94P/z665Oqr2zgpgA7bQNn5jCGhQWfFtlw+Abbm6ky/pNspeeo/3D5aEGY56MPUezRqGHtFzv41I7DagWMEaVv/yjuvBYMuUPIuv18uYCibkFceBYta+Uobfv8m4tvCQOnOGbGfmxyMpeXvWHb07DBVXMS1eTfyJszOkqAPbBE0PapaBqN69e5VcsQHb2d/kB17tw5MqC8r3jsCMEFwJiSkn5Gc57wDbY34KIUVxR7AcYFvswU9wwncWmdiS77KcEpPhCfNYLo0opJYfdoMmG1bRMuYYNIfF0sy9+RH4xdt/9+UzmGcuG1MHJkkMQo2dJb0mX25cpG0edN4cJTDLnKXxDVAFe+/Sb+l+gzC0zX38eUxxMkPmcK0eeMqXxOpufSZ2wdsmYvH9felE9U9hRqL9j2VNa9K25yomDHkxZN/TmjIwe4OqLbcFsq1ubQ9LzrPvYLDHTvpj9QnTt7lox18YYBN9D7K77B9kbu83R1A5x54euixCbpuPCUuFT5JTNawAZJMEoG+QWqWsBlY0nM42e0U6rTTidyTFxjfiqErbDtqX2bUtzkBGmdPAnzlWwVa3NgM4E7af9RKyIk+H2aERUYpafGjxOPXSE8qIZvsL3BlaQ6gq+JYiyaRhZtU55DY4tfH2yQgMtnrO8tqfVYZ2z+GxeeYkiP5sJXJ6ZsUB87swdse2pTIj88Y84ohTYPJKtWLMfRrBJrc2Ac+k4ef9bSRYS/SzOkOnv2DJk66Snx2BWCaQnszcA32N7gSlLd0OcoD5RuVJgLRMHhtRKpK+AMEvDnDeuNUkJuCVc+FkP+bC4OpVWx8hQBd2fuy8oG116w7alrxw64yYmqGP0EZ4wAe29kydoc8OUGx6bXt99rGXRxW7FRgp4S3QLFVQK3Bh6jpA19trJBgiUVODxL3wrrXxvmTHuUM0jiK5xMeC2Ymxxqbpa6NR8H3IEl29XHA+0B25769emFmxynkcOkGw4AmzdtwMGslskAif+n6ZuM0y+1EuN1z3FG6dw5MmLoYPHYFWI9XHqMkjKwKQC+FhZzYy2GnDIujlZuX+KNESU+x/pP8LiMmPjc8VwcSqTeMk8P7sKiNx2/1IRtT09N0D4888cfv3PGCb6cWTvATcXulkTTbexd91atJL1uJTZK58+fJ907dxSPXSVnGiVLJie6E/q86dy1UIwF5h2/GQuV19jpkkvImuX/IUc+uJ9c+OYecvOHWuSb438hG9f8iwwdGkp+vcIbI0pcQjbpPyCcvP3a38ilM/eQa+drkQun7iHbtvyD9O0XSXSZY0hiibzLkoWvK49/AaMWqo+/4PDVgTlbrJ+4qhW2PW24s0GIJQL31uwqDCBRryPv7XoXBzUrMGgwPkVF0/OFyZMGXfQibJQuXrhASgryxGNXyZlGae0+vqJUB/B1sOizzE+8TJAxDKXFzcjRA7U5Q4OBsSP8G0XNYAGXz9xLDu97gOiS+Ambk9er79yrtvsvMHSGeYd37saM59QNrT1g29PW117DzU2TYL3rW9vflKQFfHpM3RMtFrwJyRslrxu1oloGz8RG6dKlSyQ+Rn2auL0E+a5b8zT+WRB70XFZvJN4ewJ/qXBFcXf0WW2462CZb6bHAeA4+vwZnAFxJF8c4V8/45LNu5LFcTA4vLsz4Wn7rQJQgm1PJ09+hZubxVq/bg1nnHa9u1PTax3YmOjwUPGYMUrXa5Xk5o7ARunGjeukBbMuxRG6du2qWBCldXbsxYbEqDdAWxm30jEzdR0JvgYMDo+BSYE4DpBV2pUzHo4C5w3Epo/hyoqJMcpvdUXpOsS+y0AcTc/R1s/t0grbnuBjlr0Eqzh86teRpA97uanp8xPHSWfkfhsI8vc9V2vl0qVJ2ChB10ppiyV7aPOzmyQXwObNSnKRfsHcTbYn2e2qzzyXtftqE33uJO4aWMx9xQJmvqD+GRobEHsyZpS/KQ/lzQNwWTFL3npQ8GaJ41FgqU5pme2eP51FbHIr7hrsDdueLHHyBl/Ct7/5humZjRQ2+mAmOmpCbh3t7vd2Sd6QaNjolsH7a7377vbm9AcqWAJiztKBzp75nvTt3ZO0DG5Okgzx5LvvTuMgssKFZvNmxZ73aaDu6tRWIg1pXEVxV7Lbx5ndDSQyqTcXDzN2ufrW0d27t+CMib1IKF7I5ceCy4oB7wCx6eobD8BXOhzPXYk0Srf7dgRse5JbbwqaPmUyyUpPIYn6OJOBCOXWqloL1js73hZe9ahoOGN87BZTDSH30R/YnW/lEmKFM2VRciwe1qIZF5YiJ/a8d7263E22J7AeDVcUdwSWw+Cyy5HfOYCLi0ktMr/3W2xKOWdQbOHAbm1fmXBZ5SjsEcnFw1jj9tYVmPNqaSvQqzTX3kDmZnRbC9bSRQvJmTP8xgFR4WELJLuZ7GC8RMolRAV+UHCmGCyY14DDqIUHed+Z5UnBN9re4IrijsSmj+LKjYFXN9j+B8fFRCdlcXGVgI0uT316D2dktFJaHEgSCrR7csRllePJpdomfuJ47ggus70JiW5rtr2BnGWU0lslCT7VqGi4kqLcERKjNL7iSUkgJeEM5aBe5fAkSMzt27dQ6lWCLiQbFt9oe4MrirsxfpW2LzQxyW01LTCOV9giSY2d2/7JGRxzWOP0buUuvrwYc1MDKK17N+Piuhu4zPbGx0tcWyagJEuMEiwNga2Xynp0I2tWrSQnv9L+RQ/GrOmyFXbx/c6dO5tJjNKg/uViJLWC48IpAUqIlxoWFnaLFTkV5mVLwuMbbW9wRXE3jLnaZl+nt07i4sqhZb81jNrcJCUMCeZfEzETnzbf0wNwPDngowCO527gMtsb7/r1ubYpJzWjlJGaTJYvWUzefWcH+ebU15o+/SuJdVv02WefinmcPHnyYYlR6ti2tRhQqeBHjxzhCtunVw9y4sRxMmv6NFKYm8Wdx7BrXtS0Ypl0Az18o+1NXoe6XGVxF2BbIVxeJUbO0zZ5EMczhy61F2dwtILTMkdRtzpceeXA8ZQYt1J9QqYryW1fjyuvvWHbEetcDatzh3bCDiWOFpSDCrZsomUTDBJrlNi5SX4+DcT/s9q18x0xATWxN4Elw/QuqVW/m7p3bFx8o+2NoWAaV2HcgY4DLdsXTMurG4DjmeOXy7yx0Up87mQuPVUK1L0FWHMNOR0iufjugJoDO3shaYOmHo+rxdqPtqVVW4pzRgneEemIeHhIC9nu2ZHDHwth2S2+5QS9JvZG+DX0wkE0iU0jNl1+33Z7orTlj6vQ+rWNBaehBI5nDmxoWPbsfIRse/nf3O+UJ8cEcOmpoc9+gqzXsLFjXKJlr4awgBmn4UpgzSUuoyNg29GCeXNxM3O6GppsDVVTv0YyRqnuY7/TH9995x0hIFgvuW7cV199JYRbv3YNPiXRieOfSW7E+XNV0w0sEZtGhLEPd7PtzdyXLd/N1pG0K/flymgOnIYSOJ45sKGhXDj1V2LMm0gSiuZz5ygbVj9MEkoWc2kqYcibSha8XpsrMyYhzbJeZEHXplwargR2T8ZldARsO/r4449wM3O6mjTyFv799ZdfhDmRnFEKbh54lv44c/pUITBMopLrDdHdcw8dPIBPcYJw8dGRghdJ2FTg5ZdeIpMnjic9u3UlTUw9J/ZGwQCanNgwvo3VHcjbA0PBHK7iuBJcPnNoLf8GC/9Cx2WN4wwN8NFe6bwjGFTGYYDfr9UixtwKLl01hs6U306cBXZBwfHMofX11tHA10OdBRuRWgusG2XbkTvIEBct/PsuMxwkMUqjRg4roz8GB/oLgcGQwCd5OUE42B4Fpol/+eUXZOK4saQoL0fYDw4GsdlMtKK0LQsOh2+4I9DnTuQqkCswFs3hymaOaH0jLh05+o6z7F6m5JRyhqZXtwZcOABmm9/8kTdMxa3jubCq5Klvz03h4pnBUGjepYszMNh52yclglpKv2K7Wq+8/BIZfsdf2/SpU8Ry+cFiXKr8jIyGcoWGz3ZyLjCxobAHqUlGnI0gmAPBhsM33FGsVvEV7Qz0GUlcmbTwrMbthvI6hXBx1Zg1ra5oXBbN8yLxOepr7yhXzt4rxps6wbLtjWIznuDKLQeOpwVjpoFLx5k8vac2VyZHgduaq9WhbWthSgEoKMBPLFdKYsJ+0Si9tHr1v+QKDb2eT44eFY+p8EXaAxhkl9MrW7ZIwuEb7ig6DwvkKpKz6DvOuv3iAK3uYBPz23NxlYD5TGuWPywYlk1rtU3gpCTkDBbnNh16/6/ceTUSihdz5ZbD2iUa5RP+x6XlLNqWO3aBOQtua65WqwS9+BGNHU8aOnjgYNEogeQKDf+fNWOaeMz+bg8aedUjwc0CSFTLEDJ10kScjSBn72rCgiuSM7D1awxOT47Vu+8n8dljubhKJBRMJPNnPcb9bglxqX3I9MmPc7+bA5ddDmNOWy6eVlzlcRSXw5Gw7adPWU/cxETBkAzsju1IgTGS24QSSEtM9DNrlMCKsVvrUmHjAjQ3dcMyU5JJu9JiMnvmdLLn/d3kzPffk5s3b+LoFmtQ/35iPi0iHb+ammLIG8VVJkeDy2AJcekDuPTkGDzd+p6Ys8Fll2PIjEe4eJaA03M0hvwxXBkcCdtOT319EjcvQSuXLRPDfLB/Hz5tN732yssSG4Pszr0So+RTv85tevLokcNChNKifEkCrhJsDU7L5l1ffnDVURR1D9X8SmQruqwhQp7JhXmkXd8qJ2XjVj5EctvXNb3OzOXKx9Jr9H+5NOWITirh4roree21zbLH8TAJOa1Jfse64hbfAyb9m6QWhQuD8rrMgVx6jqLrMMvma9lKs5BUse3kZKThpiUK3lZYA6HkDdZW9e/bW9EoSQwSqLFPgxv05KgRQ4UIL7/0olsYJRBbeH2+srN7ewNzawZM/g9XuezNsNmVg8AdBiqPZU1co+72Y8FW8/N6gNiM0Vxcd0WfoW13EhyPJaVY2VdWrzGVfrqmbfo/7py9GTb7cZKosouxI2joU+XRY9rkSbhZSUT3XqM4QpGhLUiLgKqVI6pGKToi/AV6MtCv8pXt8uXLxL+Rj5iAK8UWPrCFdV+mbGHsCvNzZqyBjiHBX2x8TgklB284nBI4njsDzuBw+eWAeVQ4LjB2RQMurBwwmA/3lfak7M3gaY9yZXM0USlDrTIybBwl32jWCtKcN2eW8P8B/fqI+TSuX+cWtkm19u3b5yt3AQP79SW3b98Wj10ltmwAfgDOoNMgbXOAtJLbSSekG5nYzaIB17Z9HucMU6ReuTeAwdfl7mi5N3Nf4WdGa3EJzAIbFkC8krIg7pwtdOhfnyubM2B7SZYYJRDr/8z/zuxrewh6Y3SlCOz7RvOIjWz5BrZJguQu4NgnR4kuOkI8dpUOHvjA5UYJMBROJ0Nmahu7kQPmEYXHF0rSxGG0YCyqGl+Ky+gr7ByLw8hhSHPumIY9KOgaxl2HHIOnSb8QGnO7cGHMwRr7SGOJTWsh+014jOjzLFyIbEfY9mKpUQLZGl9OGSlVC/LZtLe98ko0tkeC2EB0HgF038BRuKt18+YNtzBKlHEr/8VVQnPA3nIxrTpI0rHktY0lPrdqeUK3Eb7ceSX0efKvOe6MLnMIdx1ywP1l47UqyubCmMOQJ93KKy61o1XLUsau+Bd3Hc6GbS9xUeG4SZkVNkoz7ixBs0Uzp08R/8+mjW2RKDYQ64ANjt1BbPn8mli2OtxRhEbEmF6nlCfh9Rj5KInLVJ4TpM8q5+KYA8Y9EksqHe8nlmibYEjB+VcX8HUokVqcIcYx5nTlzpujtKfyq1ZcRoWp9/MoF4fSpux/JCQylovnChKKFkjaC3h3tFQ07tMrV4j/V5rkrEVtWxcLnQsqtnzYFokKbhZ4jgaKi6yyrO5ilJr6Vrk4APCDqI7kdgzmKrc52verGlPC59ToNKgpl391ITG/A3c9Shjz+gpxtC5OZhmzxPU9HHvgVUe6BtVSffPNN0K8+JhI4Rh2OGLTe2r8WBTDvFiDtnvXLm1GKTS0hVHuQvJzssjrr70qHjtSsC0w7DElpze2bZNcCB7srY7kdbLcKOnS+xJDih/3uzlw3tWN2NQy7pqUgN6kMa2J6ZXOsjWMAyY5/yuZI2DbidwEaHOC7ZUgLv76xqZrqY801qbASg6aTnDTJuexLRK1bNmyel51H/sNGyVwLzB8yCDx2FFasrCqy6kk9qbEZVa/8RFMSnEu1zDMYc0YB+yThvOujuDrMoelE1/b9rHMP5M7An+s2XZSrrK0RElK7RBme4cFVW2VttXUWZFzBiknWFZGxc6J6tS+7VxsiyRKMeo34QLBtrxyBbS3Vi2vuplKYm+2Vx3L11K5G/rcKVzDsDcwKG8sVJ8NXl1IKJrBXZ89gdndOM/qRovwypUY5tqSkmBLb3NxVyxdIsnDnGE6fvwz8jbawg1o7FXn52effbYutkMS7dy5sx6NAGvY2EScIZr3yKFD8ClBBWhjAvxAqiPW9HwsAedX3clqF89do73AeVVH2Pah1G6vXr0qDJXAnKFLly5JzlGfaHlZGeTpVSvItCmTyZOjRpLePbqRhvXrculTNj6zXpIOK1gXC+NSVDROcqJhFbZBsqIRYCoAtYBxURHkxo2qkXNLBb0tcAZnTuZuJmw3zDqSi2pVuV6sOhOhi+Yah71IsGLPtepAWkmk4HUSX68thEXFcvlUN/Cr24RxFbgJCYJNQrBRsQcBjRvKfumDc1R9Ta+TNLxJD2D7Iys2k/179wgJwWj7E8Mr18RZIzp789df5fcwpxrzxEi2wLIaNKDKawBssocfTHVkyjN/5xqJrfQZ59zFy84muTCTu2ZrGTCpZvS6I5MGSozEd9+dxs1HEBvG3shtocbOdYyPiRLDYtujqOYBTX6gkWIiwsTE1PaLMieaXhCzGE9J2emVK5uVNqukfsIpzUPTuYdTHbHna1xe5ygu/ZqI1rVxaiza9jcu3eqIPn+mpF2sWrEcNx1Bt37+mTMk9uT8eX6TkLGjRwn/vvDcZklYbHsUNWzAgEA2IhX8/4nhw8RjVi+98Dz+SaIlixZy6anJXFh8I/ADqq6s3a9tpb8Svcf6cGneDcSmdDNdu7bFtywlZeFcWtUV73r1JG1CSbBsDLcfS4Att8F7ZPvWJWRAeW/yxLChggeChfPmko8+/BBnR7LSUsT/N/evcn8bHhb0DbY9qmILQUXnFmDhcEqi4S5evIBPCfrZZMG1uk/46aefJOHgLyZ+SNWVCF0U13jUgAWr6UU+NWLelj2ITR9JRi1U3ioL1rNFtyqvcfeLbQ9qbYcNAxuFwFScW7d+Fvzxs4PR9tAe09sOWxY277yUlMex3VEVGxmMBejZZzfKXmzzO+NFVy5fxqckounNmFq1/oVq7uxZkgJTlG4SfoXzD6hZryvG4kVkwmr17bdhDs7g6Y8RQ8FsLr4H0+tM3kwSZTSS8gn1ycj5/yX9J9UjkXoDF64mYCicK2kPsOW9kn50sMtbViOGDpbM5GbLaNI92O6oqqmf73kamW7nDYYALCt8ScOiYeHrmJI6tm3NFsjUBTRICimHIbZyfyg54bD4QdUUwNePsWi+sK24oWCG4JKjpv2V92AbuC2YmzfkLEFZXn35ZeH/+/ftlZQR2xyz2rx5czCbANXRI0eIURcrHlPRcGpG5MTx49zN08KCeXNwUoK+PnlSEq5lfDfuYXnwUNNJLFkqaQdpyQm4qbhEYBjZNbTs0ExsRNgubHM0ib3Qs2fPionDMdbEcRViWCzY1JJNSwuwD5zcnnNYOB5+YB481HR8GnhL2oC79JLatykhp7/9Vjxmy0jwJgFaxSayacMzksSxfvzxB8lNuXHjOpkzS/qJUgtRYSHkmbVrcPKK8vPxksTHD8xDzQR8XScUTCHRyX24c3cbuA25i9iy0KVqFGxrNCvZaDhAE/Fh5ii1a11Ctrz4gnhMhW+OJex+bxdOTpOgNxXg21BMx6e+F/fQ7iZq6liTPm8qSUrLIvvffYj8fq1qK/CbP9Tiwt5NxOdUbXsNLF28CDcRlwnKQ1Xeu0xSTmxrNOvUzp0P+Hs3uEYTYgVbemM1aSjttajR2Lu+2a91WgXuFdi0GzVswj28uwHoPdDGCnx97F4ydEAdkpzXhxhzRzt9Jw1rMBZMJ6kFvcgTQ/9L3nuztuR6lMBp3C2A0zm23k+d/BRuGi4TTJI8cvhj8ZgtZ7vS0lRsayzShAkVcTSxMU+MEDORM0rYOMgxd9YM2a93tgqmsbP5wBcr/BBrOgmZXbkGKwf0NK5fuMf06tOPS8NVPL/+71w5tWLMvDs/cOC25U7CS01oGfWxkdYNcGPRBCNbhoiZwAi/3EB0fnYGd7OAgf3LcVC7CrtSqAkLdS2l/8CWXINVA1ym4jSUiM8eRxJyh5GEgsmmeDNJosgs8f8JhdNNYUaR5KzWpCAvgOTnNND8x+HCN3z5tDJp/N33yo4X3mIjwIruHuJMsUZyw7o1YjlbJcb3wPbFKrEXP2zQACEj6BXJbb1Ce0twkz7+6CN82qFiywngB1nT+eyjv3ANVg0cX41JExty8bXQv782X+o4nqXg9Go6sFM0W9eVdOXKZTHMxYsX8WmH6ML58+LGl+CbiZ0KgG2L1Zo/Z1ZHuRsA/8/LyhSPXS26TzmlafME7mHWZHBDNQeOrwR85fr+y3u5+Fr47aq2fHA8S6mpA/xywJIqST33a4SbgqC9e96XhKOAnyRHTRv47bdfTUao6qNYcGCV29tGPg1uYNtik9iLomNC1M2IOwk/gLupsuKGqsbB97Q7NCtpo+fiWwLsuILTZDEUzePiWMrwwTXbTQsLruNKBgaHw+RkppEf7Nx7CmvRjAQ0rtpVm82vvLz8n9iu2CRTon/SxJfd+ex45coV4Rg7F3elYEY5eyOiUodzD7UmAstQcENVY/yY/3JpKNG352NcfEvAe6lhYpN7cXEs5YfT9xJjDVqUrQQeSwKUhMMpcfv2LRzVakF6wwYPFP6P16dim2KzDHHR89kMqHa8tZ1EhLQQj91B8D7LljUwqOa/xmVmNuMaqhrphZ24NJT44zof3xI+P/wXLk2WfuX+XBxrmDW9ZrttwctJgGvXruHqLyikWVMxTEZKMhcPA2PAdOG9tQKPk0V5OeIx9Jho+r4+XtexTbGL2Iu4cKHK/Qgcu5vwTdf6Fag6YixaQC5+q33M5+gB2Cl3BpeOEji+NeA0WU4eu48Lby047ZoErtNKi9/Z2dOrV64Qf0/S67g05JgxZTKTmnZBXHxM2bdvnze2J3aRv2+jyzQT9r0RnDiBc3F30uVLl7ibjR9yTSEjN5VrnGo8NcGPS0MNHN8a9Bm9uXQptvbEWBJLtE9zqE40D5NumAEuZZXUv7yPEAacrsnpyOHDXNuQQ4tPfVbs3MWVzM5EALYldtP4iop2bEZUYJn9fBqIx+4ifJPxg64pLJxbn2ucaqTlZHJpKBGXMYaLbw2DBilPDcBhbUGf1oNLvyaA67LaBGQtYWB6AE5TjjWrV+Kostq8aSMpyM0Wj9mlXwGNva5gW2JXtUrQL6SZnT17RiyE2uQtV2nf3j3Sm1y3+m80KMePp+/hGqca8blTuTSUKC8P4+JbA0wNSMx/gks/oXgxF9YWrl+oea9wLSKk+7jB7rVKevXlLUIYuRUXStLixQO8zqoZOQiDj4HG3g1ujBgx4hFsR+wumiEYoqtXrgiF2PXuThIe3FxSMEcLvho8u3EDSUkw4FOiYLMD9uaGxXbgHnp1BzdMc+D4ahzZr239mRZ2b7+fS9+YO4oLZys4j+pMQol0pQJu/KwGM7v8KE0TUJOWbZfkBFuvFeRU9ZLSkhPF8MsXLgzC9sMh8m/c4ArNlJ3VDcdZaa3EY3tq/do1JDs9hbtJlMiwYBxFVJ9ePSRha8q2TEBiyUKuUZoDp6EGjmsrOP0e3S37aqiFxMx2XD7VkcikAVw9/15hyySqM2e+J4XMFzBrtHTRAi5fIDE+Dgcln316TDhHNXzIYDbOn9h2OFRsYWm37syZM8KxNVYa1tGdOP4ZKSnI4zYP0IK510d2A0ugpqyNS06J5hqlGme+uodLQwn4K43j24ouTer/6D1T7wmHsZVPP/wrdy3VDfhajOu4Ne3KFlWMHiXJX07wmhh0x102iA2/dvXqAmw3HCo2cxi7AcFNg33iznz/vVhINf3ww0Xy9KqVZFD/qm6ntex8ZwdOXqKnxo/j4uCKUB3p3eNxrlGqMWvKI1waSiQWzeHiK3Fwtzbj0qtMOuD9y2U+jBwwVoR/UwNfS3UjJKadpK76etfHVdopWrxwAWkeULktkpzgd7phLXQs2DJjm+FwRYa1GM0WABcUa92apzmjIAf0aGAcSB8bRV5/7VWcDBceaNLQGweTFY7XtAZMqsSN0RzJOSVcGkpkZcdx8eW4eu5eEpc+kvtdjl/RWjh8XonsoiLuNzWq8/IiS8aRXKmwoGbk3Z3viMdgOGl5A30b/YRthlPE3rQ3t20VC2fUxZC339ouHlPJvZbpY6PJ1ElPkVu3zE91x4PWwLgxo3EwVeH48bnTuEpRncCN0RwJRdq3Yzr03l+5+HLk51bOe8K/K8Hu0YfPKQGvM08v/z/udyWeGPYYdz3VBVxHPzx0EFdjtxCUDR9TTp069QC2F05Rj64dxOkBcgWUEywCVNrLTU2wKwJ+WNZ0aWGdHjaODX0sm0zoLujzZ3GN0Rw4DTVwXCWokfloj7YvdYV5jYXwuuyJ3Dk56DIVfdYI7pwSf96oRQytcrhrcmcMhfO4Op5k1OMqLArWnoKb6qtXr+JTDhcsLdu29TXxmG5UC3jXe/w2thVOU48ePR5q7NXgupxRgob/hxXGR0n4YQF7338fB9Ok93a9y6WFK0h1QJ/ek2uM5sBpqIHjKkHDp6Vq+5L24Z7KgWh9cil3To6Zkx+1uEzAonn1uWtyZ7zrVb3+ALDHoppg/BbCwRcvZwu3d7bcfXr0KMe2wqmqqKi4lxYG3jGpwP82uzOmtYLBcG+Z1z6wzLYoycCvA8KVxN2ZM6Mu1xDVsOTLG4DjK2FtnGmTfbjf5cgo6mhx+gD0lvA1uSsBzeK5+mhOloS1p2ZOn0patqiakwiO3Gg5wps3b4NthEvk37jhJVoofUykWFjY/siSmaVywg/Kng8Bpwn7Z+HK4s7s2GqZX+vVy7SPsxhSW3Px5XjzlYck8fB5JWIS25PjH2n7YmfInymmn5nehDuvhiVjaK4iIqEvVxe1aC6zhVn71iX4tEPUq3sXSflg8jJTbufOSzIn9oayLhBglii7IZ0lwg8KSEuy7+6fOH3fxkHV5svN5TN8I1QjoVUyl4YccP3Prf8HF1+OnOICSdwFsx7lwtiKpGwli7nzapSXPc5dnzthyJ/N1UFLHK9duvSTGM/Rov6RUhON4m9subdu3ZqI7YJL5cU4gaNOnkAwIbKlFctPrl+7xj0sR9z4Hl07c3kENNNxlccd+fUK3wjViE8u5NKQI7FgAvnuc23r6fSZAyRx81vncmFsQe4VDIdR4+f/b+88oKq41j1+b+5b99337n1565XclxsLRXoRBKmHDiIoKohYwN5N7GgEG9GoaGwoKhoTW6pRr7EldgzGRI0lqLEES2IJxm4s1+Sa7He+wZmz59tz5sycAkfZv7X+a8HM7D0zu8zZ9ftusOGdRWByxqUBmnSh/CtqRQx7jTIn5AiOVVbKej5379YYeRSFvwl1ztuLFrmb+3hYM7aEPxSg5W8txZfZBSXXUCExpnEMZxWugJYUlzWTiUNJRYX/y4Q1JxwWWln4GltUfZ41EHfgsz8x16kJh3cGKa3YXrliGS6ampg1o8YhJazxA0NrjgLuQfeC6GfPaJW6Bn8TnALjw/0qPmSzQD/qdYjMw64aO7ZtZTILBH7dHMm3Z84w92ye4NxmMGAhIq6AasLhzQmHUxMOqze8JQ3szy7XiG/ZiblOTSkZ2lqItSlc1sCYvy3QcTmCnPaZJCYyTPofTJrQ98TfAqcBZuIC/LyuiQ9KW8aLCgsh+zRM4ePMcmRCY6B5iu8b1bKAKVDOom8rtVubBIGZEBwHliEhnQmnJhwelJ2pz76TmqLNpD++Tk1Vx5xrLxwuY36e7rgo6saRdUUcS6Kh77dz06ZY/C1wKgb06dlKfNiIENPO/Qvnzgmuus2Z8AT69erBZBjImjEpa8ELK0GRqa8yBcsZtHmdtsFoUbFtJjNxYO36RHucNy8rf5QMqb2Za60VjlsUvs6ScPi6kq8/a7vowf37uBjqonSefLC8bNECfIlNLJhfIrM0e+b0Kele4c2CjuNvgFPi0uDFf4oP7e/lIb0MrAKFY0rAIB3OLNCM4in4UpsBQ+pqbPx4PfMczeOczzX0mDHBTOVT07hx3kwctJI6zGbCqCk+SXlCwJ7jSjhuUUVjX2CuVVNyZ8utREcLlymQOcP/Wpk5o1iKKzUpQfrbXhgimgvxiRYKCkaPlD0/rvtODf3gR48cll5y9MgRJH/4MOl/kVsKNrWtGSBXo7ratMgL9typgT2igJpF5zEFrS4V2+pVpvJZUkqXxUw8oCSdU+0gNWcMt69om72zJByv9LzZ08mdH7TfY2LBC0wctSVIW1yW/L1NP9ZKgDmg3I4dyL7P9+JTEvReUBHR5lhWW9vGqIDr168LcS17603hf+wyKa9Th3G43js1rdNafEy/AA3+X6RLjtzspz3tx+zeuVMWN2hA3z74MhmbkMddkLdvpFOtY8KVT4vaZviS+LYTpTimT36BPP6Jvc6S8LPQ6tMvlrler1avep6Jl1aX7q2ZMGqKTUpj4nC0YOEnLkPmyj8Nvh5PHNHn7qEZN633sATEcevmTdn/olwb/u1nXOednurq6j/TL1FZ+bX0ckMGDSCDjVJCvN7fjCtia8BfeFpgA0YNpTGmJu6+TOGrK+GKp0c/nPs9OX3kD8xxLarYIV/JjRXfaiD5x002nB5Nm9SAiZdWQtvRTBg1LVtSuwspY9uZula0LE3bzyieyoQB+Xi4kc/3fkbOnzsrO44Ry+zK5dYtMQAePnjAGE6k79m9a+5kXOefCnw8XK6aSzz4X8kQefOgAOZaW8jMSGcyV9TcOTPx5YocP36MCevWyDncRCe27sZUPkfr9g/PkdiMCcyzYA15xYsJq1WP7/2OJGQMZOLEwuHUpLQQ01EKie7GlBnYr2nJRA82lKamovFjFXsTdBxg3NAaICw9AA8fROrezrWdRC9DBvbrK74Mbc/79u2aMSQlHj16hA9ZBcza4YwUVb5rJ75clfPnzzFxgJyhKweeQnAFdKQikwcxz6AkGHPCYbXq853/TpI7KY9/0ao6pq+ll5TtePtZTVxrLDXS6t+nNy5SDJcvXRJMy+KwWtQ9twu5dNFkvxvWFInn9BLo40V65OVK/2/b+qkUV4Cvx3Vcx59KjM3JR3QCivTr3YO0bpki/W9PlAzCiYJdzdagtPIb5F7HjgiSOswhNy7qW7NkrbDlSEvC4bUqo10CE5eSYtsUMWHVNKif47pwUWmFzLYRkCVTzUrAj3ZWRmsmLi2ChcbQ/RL/BxdKWjlz5oxsoTL9cQPhuv3UkpPZdi79YnRLCP5ftWK59L+tQHN2y6aNTEaJstUgFvaMIiq2zRSmkNamQiNTmQroCA0cFMXcW01abXBjxaV2YeIyJxxWSZer/kA6dvBjwtpLSttGQPbYiVAya6bM1Kw10gKsIQzw9iBzZpuGNeg4PN1cbuG6/VTj6db4vlIirVv7keZE0wLODFq080xbwXGDvH0imMJamxo2PJypjPbUo9v6Wkmg/oO02fnGwvGoCYelNXb0iySp4yImjD1laF3ElAXQA2NrxRGA6Wl8L0uaXzIHR8MA13m5u0j/QzeOjgPX6WcC+gXpfTQAHLMVnBG0LA0wWkOrVJPTPVphCZYHaB2l+JRkpmLaQ9Xnn2PupUWwEx7HpUU4HjXNnuEqC7vug9pZk5TYYT7x9KxZEIxVm3Rsn8ncX0lqwPhr8TTTQmUwdUuHLSoq8sD1+Zmgffv2MksC586dlRJhx7Ztxi+zp/S/XrBfN1E5We0UZ/nsxcH9+xXHr1zrcHYuKaeE5OUGMRXdWiW2n8TcQ49+vcfGaUk4DjUl5pSSzA5pJCl7KnPOUfL1Zy2XgjLSHOOMVQvgrXrXju3MM4ny82yi+OO8feunsq1cv/zysyxckK/PSVyXnykGDxw4W3xZvGIbjlnDkUOHmAwQVVvg+4L8g7UZVXOURg13FabBcYXXo6656ltTtOjeNe0rr0FHv3CuDbRYcZkzmLwGjRrB7lSoC8Axh7exG4afDyx1wBonDJwDu2ci/Xr1lMJ4ujR8gOvwM4mHS6OH4ku/OmqklBjgnQFaPF8dPCAdswROeFq20i6jlTDwd/v2bXxKEXOtNVyoa1uJbQaT/BG+gsEz/AFQ0oE9fySjRtpvgaghPpG5h5p69w5m4nAWubt6MPkLukmtfFZj966anQXWePSxBjDE1tS3ZpmBEu1apwvdP5FzZ+ULM3HdfaahX5xuMY0YWmOv2FKX686d20zBEIVXolrDT3fvyuK09DwiC0vnK64CB8EeKFzI61Lg4iiyxQgSnV5I4tpNZ87bU/jDI+rnO78jF8/8kezc8jwpLPAl0XFpDh+Y1itYi+byknKehgaZnGVoIT46Ugpb18Az0HvwuuV2kr0brrPPPCmhof9JJ0CLhDgpcWDWQi3Tjh9j7R6JosepbAHHu0fnWpOJ4wqYOEBeXqFMoa8PSsx6jcRnFgsD3/icMys4oiOThyBbpvrFOGKjwvGpWiPAx5OsWrlc+n/Xzh2y94sJC6tbN0l1BW3iBEQvmYfWzuaNG6T/ac6erWIKia0FhQbWa+C431q6BF9mEbBnjOMBBYVnO8VKcC7zgvyJz5rF5J0oW+jSMVuKZ9rr1m0DsQUlo21o6OHp3kZiK3RG448KJBQYlFKiYo/coaQ9rPcBBw8cYAogKC3F5MFBD98cP87EJcrT03nHTuqz/INSmbwSNXbMaJzFVkHHWdvAPek1VGDqln6e95Yt88T1tF6xYcOG//NyaXRXTJCkOINs5z4cS4hRtn00fPDLwvmIkCB8yirUrAnYWnjA4DqOT1TT8CzJ9TVX3QhWZEe2HMPkjai3rWgpq4E33tYGFXvKhXtdumhyebZh/Tr6OX7r2S1vCa6j9RZcCETAVoyvsRVUOHqUdIzGkukRPZibPcPPZAtvL4VBUzZukGvDhkxl4XK8mrixG2hFxUQ6btznlQGmLUsfffgBPm1XxI/gO9Q4EvRM6HctLy//F1wv6zUD+/cpoRPo9aKJUuJduXxZOOZo6Ps3QRlmz/v36pbHxC2pwd/4eFMtyrWR+p4yrbOu1gAtc/GH0Bp/b3qA8c1ZM4plx+j3bOLS+CGukxwjPp5uVXRCVX5tMgy3ds1HdpnqN4e44bFZQI2Fv4H9+jAF1N7s//JL5h60AkNaM5WIy3aFGLozaU2rUMe4kT0+WuJ9Fy6Yj0/ZBbDjlNepg+yY7J0b/O0XXBc5FMZEekwnGL3IbOb0acIvysED+6nktZ1tn34q3U/kyGF2lbgW4NcvwRAluJRSMsClxOcVFRZ2gb9IotPHM5WLS7tg7ZNPQIxC2pqUFBeLs8Ys165dY8LDIDiYxdGa7yIXzp+X4sB7Qm0BnqNFYhwJaeovO46fG9dBjgLGVkoPOtHapJv2E5XMrpmmvXzJZNDKFsDWjHif3Tt3yM7hzHt35QrZeSXAjK94vdI+I0vAGAa+Ly0v71BVg/1ccgWGtmXSEOu77y7gbLBIy+REJh4s2HHfIbMtOfTVQRycIdjfRwp3tboan7YKiAt2JIiA30V63DQmvPmp4uKC/8L1j2MGnMEi8PWfMW2qzKe5LSjdQ+kcqGfXLvgSBrX4tADdgbAnrqjMCQbE/ZpqM4JWXxWXOZ00cfdj0g7LWhdHsK0Ex6WmkEB/cvTIERyNxDcnTkjXJpqZbdYDWLCADe737pnezxUZoEtLS3se1zuOCsY0fA5nLM34wjEyB3nWQJse+ax8Nz7NFCw/C84M6O0l9gC2uoAJYfwcWM3j+5OE7BKmYtYnweRATMZripYfsTZv2oiT2irA1TaOW49e7t+PrF+3VoqPbi1Z+7EEuud1IUHGuGjw8ECkn99/4zrH0cbvcUbS9O7eVdj5bC1inGAUXQl8b3x/mn17K6RrkmKj8WmbgNahOVO8rMCoWf1Z8+TmIjNob1abN3xsl0FpDNjVxveyVjBeSn88rCE1OUGIgx7Xwj9sy5YubYUrGkcHM2bM+I+gAN8rdKIO6t9XSvCeXXOFY2BhQA9iXG9Mn4ZPSeBCo1ZQxPMrl72NT9mdubNnkWYBvsyzKQl2tTeP6yeY28AV+mkSOBGITiskXl7BzDuaU+ec7FrZjQ+tEvGe76xYTvKHD2WexRp5Gz8mWgfN4YMLYeidDTCmGdm8mRSfsfv2S5tWqWNwHeNYwcyZM2U+5EAiUOiGvDxI13IBcd0TSK3Q4nvS96UBQ2/iea2FyB5MKhrPPJ+aPJr4EUP6WKbCO7NgUN+vaZKmbpmo8JAgweBZbQH3Eu/t5dZYOAZjTmPyR5rd/6hVcVER6G7KRBg/PjCoDS1qEdHltqjUlIQiXLc4NhAZGflvOMNoiiaM07QAbcyofCn8krJF+LQMJUNZmL69ekjnKo8exadrjQsXzpM4yiyGHnn7Rgi74Q2tJ9bJzB5YDohMzSchhm7E3cU0e6lVHsbuyjsrVwgbqesKWAYiPs/a1avxaQGYOW7bKo15fkvauWM7jkoGzPga0FIC7KopMT5qNq5THDvQokVTsy0mALpOcKzwVeUtKYC5sEooeSo9ddJkoe/xY9P+JVsH3UV+uGK7cwP4tbx166bNv9KSGrwkzPi5NXY1dp9CSNPQDBIc1YVEJA8XjOaDLSbYUQ+D7TFtp5ColgUkPGkICUvoTwKbpQvbONwaudSsntbR4lHTru3byD0bBoMdAf18loAW9c2bN0jJHPOWCCzFBz9E0ENo3zZDdpweMAe1Sk2ZhusSx44kJyf/T1Cg3w90omdmtJIyBHb5Qwsnt1MOlU0mvv/uOxIWHKhp0BOuxYVjUek86bylQqOVxFgDcx9aHbPa2WVv1IL5JcIMIo7f2QVjNNXV1vnqEwHXWmULFzBxY1V8tgcH1cy1H3+U4tHja43m3NkqYW2TGE9eZ+VyPPuN6cIHad6c2dKxW7duycYaYQwpu00rPoZUG2RltfgrLkz0xtzvLlwQjpnjn1S/Ww2ltSh9e3YXzoFLY/q4tRRPfZ25hzmtfv99HNwqKr8+Spa/vZSJ35k0esQwsmPbVnKfch1tLVA2zFkDVZItlC2Yb5d4YNfCurVr8GEJiB97eQ4Lbip7j87Z2UNx3eE4GFyYvjM2Z2ng2P4vv5Ad0wu+B4g2OzG5aAIOohnaQLvw/Aori2HKOQiND2hp5VnLscpKYbtEnrGlSc/c2FMw3gGtWxiP27J5E34EuwH5hLuw8HGCLUDujdS7tufPncPRaUZcNU17CLEX169fF+LG4Ocvnjq1K64vnFoATC3gzFho7KKIiNOkSpmoFRw/CAq1rfECdJxnq6rwaRnY1lNstLZZmfrIdbQvbcumTWZnRZWsjIqyZZxQjEPL5ItWxNYeHtDHz52VlfVXXFc4tcyAvr264Iyhp/rF7pw1G3lxvLRsoV9v06yd2rIEDH3//GFD8el6z6D+JusO0WGh+LRZcN6K2vDxenypJj54710pjtEjh+PTuoC9cBDP0cOHZcfx7HCgr9eeoqIibhPJWXBB1gVaJMXLMhCmYrGfOS3gQkrLWuAjZG089nqGZxHYmiGmC3ShfvrpLr7ELOa8HUPFtxZ75RPsmwPzIzRKxgJxneA4AaHB/ufpTMJN5z3lu4XjsKhMK2BKAme+rYWMjgc2YurBns/xLKFkNbRzTnt8mSqVlV8zcYDMdf0scfr0KSmO7rmd8WmL9H0y5jgTGWdLS0mSPZ/x3R/jusBxIrLapJfjQtUjz7S7H1opwwa/onkFuOiHjlaBGdO8WqHj0gt+FrD7xKlZm6XkPl0vSnE0C6wx/GcNt2+ZZnD1fNygVR/s7ysMbIuUlpQws4i+Xu53cB3gOCk+bo0lZwQg3AwXfct9smWz7Djmwvlzsl/hL/btxZfoYsyokVJcXx81b8rCHPQ7gTgmTp38hkmfk998gy9ThZ4csVc6Q9dLjAdsGqkhboF6c7F8t4G/F+OZ91dc5jlOzqpVSz1wwXr9tSJZRpfMmS0cH/rKINlxDKy8tUfhBOhfOr1T++A+HL8TxwSeoQThsUUt4DhsTWewRglxdO3cEZ+Ssah0vrCEAVuKBP+H+HleHth/NC7znKcEF2N/m87MJo0bCoWEplN2lqaC93fK9o21iM8RE6Hf7ClMUePCyZGjtP5IL9j2EKiq6lt8mV1xN36MoDV//qzJwzMsbQBLAehZfisvL38Rl3POU8bYsWMb4EKW21FuSF1sDdnbvxfN0iWLra4oSq2AqZNr37uqs/PggXylPWjnDrmZY0t8c4J1IAp23B3BF/v2CfGvX7dOdrxDFmvGNzuzTUdctjlPOW4NX3pEZzKMFeEulDg1vGLZW7Lj9kC8L+2dVCs5We2YQspRBqfTaxPH40tU+RUt2QDZe+X5VwcPCvFis85mjPnVbzfazzrJCTEVONOhuY7p2S2XcSNuCxe/N23u1YvoLAGLowyk1yTjh+ji9yZPsHrBaX3m9Gl8idX4NHERumvYkSqeWQMFBfhU4zLMeQYpnjKpDc781qkpsgICgNEs2FV/9epVfEoX0PUqGF1jxwmvndIC7KHCzzsmfwS+jGNHcHqDp2ZbgV38TX1rvPHiVfz+3szsGjGEhx3AZZfzjGNsIT3ABeH+fXnhO3zooGDqxJwdby10fjKQDqLXnWgFPyPsBtez5oWjH5zmttI8qOaHhTazA5w4zo5fgU6cOPEXXF459YQ4Q+RyY7P5Z1woLl26JCs8sAkSunP9e/fU7WnClsL9/rurmAILdns4juPKlSuy9J4+dQq+RBOwzaVw9CghjrEFcq+7+z7fy+QrKDjA+wQuo5x6SnZm27m4gICZUcyhr74SmuBaV/nS3i70LuQD8DOBOI4Fb1mxhuZBAULY9NRkfEqw443zNDI0qBKXSQ7nd81DAi7gwgKmRZU83UKrKdD4cdLK1k+24EOawM9Dezzl2B+89OKzPeX4ElUgPKxBg7B4PdyjR4+UVmaD+OpsjnnKyso8U+INB3DBaaIwE1f17RnhVxV05cplfNpmwPYyfg6O48DWG1Yu1+4uC7aHuD6ZPfvw/ffwacWFrz6e7rfjDJFZuAxyOGYBG8e4IEWHhypaiEww1HgSAVvQ9gLfW+smYo6cw8YuN6QfdL2VeGflSlk6Y++yakB+QxgvNxd8ily7do20bpnC5CPovRUrwnF543A08fLLAzoEenncwIXKnG0m0dRJnx7d8CldHHqyuI4W7g5wtIPT0pwuXbyIgyoCOwLgem93ZYeRngrjRqD27TJW4jLG4eiGFBU9ZyxQv+ICptRygQIKM3dwHlpV1kKb3BXFsR6clli9unVlzMyaIzK0xnb5p1s2MzsCAKWBbFBamse/4rLF4djE3JkzI4yF6zdc2EAls2fiskkOH/pKWC0Os3hv6dxT99HqD2Txw3gFx3qy2rQWPvSQH/Bj0rdXd11uq8oWlJJAH08h7G7kRQQAh5G4TIgyhIXk47LE4dgdL5eGMntNokYOG0L+8fAhLrPkzbJFwnno3in9uqqhtA2G43hg4Du0qb+Qb6NHDmO6aaJDSVwGnojPqHFqnxMnTvzRw+WlhwoFUvhFVRqfOFb5tTAoCtd0y+2ET3OcgLzOHYX8gR8Dc5Y9UxJimTx/ot9GDh0ag8sKh1Nr5Od3/XNel87vKRRO4cN0+tQpXJ7JQ2NL6uP1fxeu8fN0F3aGc+oe2CDr88R+EbRs8ZYjETDChvMaFBsZcaa0tDQQlxEOp84An+24oIqCdU5K+96gIqS3SBJMWIDAyiCn9lj70WphkzTMpqa3SGZ27ossKVto/AFRdnmeEB21A5cFDsfpCPT1uI4Lr6hxBWOY8QmRgX17S9eB1xVz13GsA9JT3BwLggFvc8C1So4EnojbOOI8nbRMSfxQoUBLUtvsCZs2YZsLXAddwSmTX8OXcDQwa0ax5L4bTIRUfLYHXyKxfdtWxdXXoiJCgk/jPOZwnjrGFxbm+3q638EFXFS8IZLMmf0Grh8Cj43diUULS4UNwHCtp2tjUrZoge4ZvPrI/i/3CWN2kG7wcZ/2+mSz3bNrP14lXXKymbyhlRRjOG689DmcvxzOU010ZNhCFzNrnUDwi/753gpcZySgSzFx/FhpAR8ot1MHsm7NR/X6Q/XlF/vIm4vLiE+TmlZOgLcnyR8+lDGmRnPsWCVpFlDzsTcnQ0TYQZyHHM4zS9GEwgJcCbAyM9JxXZIBH6Id27eRuKgIKQx09WA621Yf9s4MpEvwk9YjKDoslKxa/rbqRwhITUpg0hirRUIMfIh+j/OLw6lXDBs8KNPT3eU+riC03Bq9JMwCWeLB/fukfPcu0qlDe8buM7QkJk2cQN5asliw9eSsPHz4gByrrCRzZr5Bmvp6y94B1ghBy/DWzZs4mAwwFQKtJZyOjBq8+DjBEJFfXV39Z5wvHE69pry8/C8pCYYjTKVBCvLzIb2652mambt6tZqULVxAkuPZxX4wswQG67p16SS4Jz9bVYWD1xqwVuvUyZNk6CsDSVx0hLCEgn5WMAE8vrCAfK/BGcDB/fuFmUuVmTNJAX4e18eNG9cI5wWHw1Fg0fz5SWBzB1ckJXkYu2uz3phOrlZX4zpqFujqwAzfhLEFZPiQwcKHCxYPwoZS3MISBV1D2MsH+8DAxAfsJ2uREEfSU5JIO2OXqk16S+H/aONHARwewHVwvVJ8YCgPdtnD+NiAPr0EZwjr160lD3W4oDp96iTp1S2PiducPN1c7udktZmA05rD4VhBQX5+qJ+n5y1c0cwJPiDwYZg4vpCcNrZC7AW00GAsC2ayYGc9dJNEwf9wHM5raclpBbqkqz/4gCTGRhvfi/3Aqei3ogljBxuj+ANOTw6HY2c2btzok5oUt9UVuSbXKhgMT02KJ28uXkTWrP6QVH37rV0/JHqArTewJefdVSuNLbYYoRWFn1eLIkODTk6aNCEdpxWHw6lFigoKvGKjw1ODfX1+xJVUr+BDlWCIIjntM8mIIa+Q0nlzhQHn6h+u4O+IVfx09y45euQwmTdntrCyfVC/PiQ2KtzqjxDIz9P9Tu9ueb3zOnYMw2nD4XCciIqKiqaGyOYfe7o2fqDkRuop068erg3+Edk8tKKstJTbteZwniXmzZv3Qnp6uterI4YVBgV6V0OFV/gI1Ima+nlfG9i31+KwsCD/kSNH8lkxDocjx9jj+tOOHZ80Ky0pycjMyBgfFRo8Nz4uanFinGF5UpxhbWJc9Na4qPA94WEh+6LCmu1NiInanpwQvT45NnpVUqzhzciwZiWtWyZPKp4yJWf9mjXRN27ceB7fg8Oh+X9aLqh+lh26ugAAAABJRU5ErkJggg==>