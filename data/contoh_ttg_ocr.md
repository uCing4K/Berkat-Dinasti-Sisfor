# OCR hasil dari contoh ttg.pdf

- Sumber: data/contoh ttg.pdf
- Diekstrak (OCR): 2026-05-03T17:33:50
- Total halaman: 13
- Engine: Tesseract (lang=ind)



---

## Halaman 1

KARYA TERAPAN
TUGAS PROYEK BASIS DATA
Rancangan Database Depot Air Minum RO OXY dalam mendukung SDGs 8
» se KD
a
“) Ay
NS
Ketua
Putu Alvin Mahendra Putra 240535606978
Anggota
Anggota I : Muhammad Romadhoni Al-Bukhori 240535600335
Anggota2 — : Putri Yuni Agsyah 240535602196
Anggota 3 — : Tegar Jatu Ramadhani 240535605782
Asisten 1 : Kardynan Parulian 230535607270
Asisten 2 : Muhammad Lugman 230535604938
Pembimbing I: Dr. Ir Triyanna Widyaningtyas. M.T. triyannaw.ft@um.ac.id
Pembimbing 2: Heni Vidia Sari, S.Pd., M.Kom. heni.vidia.ft@um.ac.id
Validator : Andriana Kusuma Dewi, S.T.. M.T. andriana.kusuma.ft@um.ac.id
PRODI SI TEKNIK INFORMATIKA
DEPARTEMEN TEKNIK ELEKTRO DAN INFORMATIKA
FAKULTAS TEKNIK
UNIVERSITAS NEGERI MALANG


---

## Halaman 2

Dokumen Deskripsi Karya Terapan
Produk mendukung SDGs 8
2 | Nama Pencipta Ketua:
Putu Alvin Mahendra Putra
Anggota:
Muhammad Romadhoni Al-Bukhori
Putri Yuni Agsyah
Tegar Jatu Ramadhani
Kardynan Parulian
Muhammad Lugman
Dr. Ir. Triyanna Widyaningtyas, M.T.
Heni Vidia Sari, S.Pd., M.Kom
(a|Link Produk ”—”|
5 | Deskripsi Produk Rancangan sistem database Depot Air Minum RO OXY
dirancang untuk mengoptimalkan pengelolaan operasional dengan
memadukan berbagai aspek penting dalam satu platform
terintegrasi. Sistem ini mencakup pengelolaan transaksi,
pemasukan, pengeluaran, kategori produk, penggajian karyawan
dan kurir, absensi, serta manajemen stok galon dan produk.
Dengan mengadopsi teknologi database modern, sistem ini
menggantikan metode manual yang rawan kesalahan dan duplikasi
data, memberikan kemudahan dalam memantau dan menganalisis
setiap elemen yang berhubungan dengan operasional depot. Hal ini
memungkinkan pemilik usaha untuk meningkatkan efisiensi dan
mempercepat pengambilan keputusan yang lebih berbasis data.
Selain itu, Sistem ini memberikan kemudahan bagi
pengelola depot untuk memonitor riwayat transaksi, menganalisis
pola pembelian pelanggan, serta melakukan perencanaan stok
yang lebih matang. Implementasi sistem ini diharapkan tidak
hanya meningkatkan kualitas pelayanan kepada pelanggan, tetapi
juga mendukung pertumbuhan bisnis yang lebih berkelanjutan,
sejalan dengan tujuan SDGs 8 untuk menciptakan pertumbuhan
ekonomi yang inklusif dan berkelanjutan, khususnya dalam sektor
UMKM di Indonesia.
6 | Tujuan/Manfaat Pertama, sistem database ini dirancang untuk
Produk mempermudah pengelolaan operasional Depot Air Minum RO
OXY dengan menyediakan akses yang terintegrasi dan mudah
untuk data transaksi, pemasukan, pengeluaran, serta stok produk.
Dengan demikian, pemilik usaha dapat memantau secara real-time
status keuangan dan ketersediaan produk, yang akan


---

## Halaman 3

meningkatkan efisiensi dalam pengambilan keputusan dan
operasional sehari-hari. Pengelolaan data yang lebih efisien akan
mengurangi risiko kesalahan pencatatan dan mempercepat proses
transaksi, memberikan pengalaman yang lebih baik bagi
pelanggan.

Kedua, sistem ini dilengkapi dengan fitur analisis data
yang memungkinkan pemilik depot untuk memantau pola
transaksi dan kebutuhan pelanggan dengan lebih akurat. Hal ini
dapat membantu merencanakan pengadaan stok yang lebih tepat
waktu serta mengidentifikasi potensi peningkatan layanan.
Dengan fitur ini, Depot Air Minum RO OXY tidak hanya dapat
merespons permintaan pasar dengan cepat. tetapi juga
menciptakan strategi pengelolaan yang lebih berkelanjutan,
mendukung pertumbuhan usaha jangka panjang.

Ketiga, implementasi sistem ini diharapkan dapat
mendukung digitalisasi usaha depot air minum. memperluas
jangkauan pemasaran, serta meningkatkan daya saing bisnis di
pasar yang semakin berkembang. Dengan pengelolaan data yang
lebih baik, Depot Air Minum RO OXY dapat meningkatkan
transparansi dan akuntabilitas, memberikan layanan yang lebih
profesional, dan membantu memperkuat posisinya di industri air
minum isi ulang.

7 | Produk Struktur Entity Relationship Diagram (ERD) pada Depot Air
Minum RO OXY sebagai berikut:

Il. Admin

e# Id Admin

e PIN

e No. HP

e Email

e Nama Admin
2 Transaksi

e Id Transaksi

e Id Kurir

e Status Pesanan

# Pembayaran

e Tanggal

e Total Harga
3. Detail Transaksi

e Detail

e Id Transaksi

e Id Produk

e Jumlah

@  Subtotal


---

## Halaman 4

4. Pemasukan
e# Id Pemasukan
e Sumber
@ Manual
e Transaksi
e Tanggal
e Jumlah
5. Pengeluaran
e Id Pengeluaran
@ Id Kategori Pengeluaran
e Tanggal
e Jumlah
e Keterangan
e# Sumber
e Gaji Karyawan
e Gaji Kurir
e Manual
6. Kategori Pengeluaran
e Id Kategori Pengeluaran
# Nama Kategori
7. Kurir
e Id Kurir
e Nama Kurir
e No. HP
e Total Gajiku
8. Gaji Kurir
e Keterangan
e Jumlah galon antar
e Gaji per galon
e Galon Pecah
e Potongan
e Jumlah Gajiku
e Tanggal Gajiku
e Id Kurir
e Id Gajiku
9. Karyawan
e Id Karyawan
e Nama Karyawan
e Jabatan
# Mekanik
e Non Kurir


---

## Halaman 5

e No. HP
e Total Gajinon
10. Gaji Karyawan
e Id Gajika
e Id Karyawan
e Tanggal Gajika
e Jumlah Gajika
e Keterangan
11. Absensi
# Id Absensi
e Id Karyawan
e# Tanggal
e Ket. Kehadiran
12 Produk
# Id Produk
e Id Kategori Produk
e Nama Produk
e Harga
e Grosir Minimal
«# Harga Grosir
13. Kategori Produk
@# Nama Kategori
« Id Kategori
Dengan struktur yang jelas dan hubungan antar entitas yang
terintegrasi dengan baik, sistem database ini bertujuan untuk
mengoptimalkan setiap aspek operasional dari Depot Air Minum
RO OXY. Mulai dari pengelolaan produk dan kategori,
pemantauan transaksi dan pengeluaran, hingga pengelolaan
karyawan dan kurir. semuanya saling mendukung untuk
menciptakan sistem yang efisien dan akurat dalam mendukung
keputusan manajerial yang lebih baik.
Tahap Pengembangan sistem database untuk Depot Air Minum RO OXY
Pengembangan mengikuti tahapan dalam Sof tware Development Life Cycle
(SDLC). yang bertujuan untuk memastikan kualitas dan efisiensi
pengelolaan data yang lebih baik. Dalam proyek ini, tahapan
pengembangan dilakukan dengan cermat dan terstruktur untuk
memastikan setiap komponen sistem dapat bekerja dengan
optimal. Beberapa komponen penting yang dikembangkan dalam
sistem ini meliputi:
|. Perencanaan
2 Analisis
3. Desain


---

## Halaman 6

Gambar Produk Berikut adalah full ERD yang menggambarkan hubungan antar
entitas dalam Depot Air Minum RO OXY. Setelahnya, akan
dijelaskan lebih lanjut setiap entitasnya dengan gambar atau
screenshot supaya lebih jelas dan mudah dipahami.

P £ 3 ! &
Setelah ERD. penjelasan lebih lanjut akan diberikan untuk tiap
tabel yang direpresentasikan menggunakan entitas dengan
menyertakan gambar atau screenshot, agar lebih mudah dipahami
terkait atribut dan fungsinya dalam mendukung operasional depot.
Entitas Admin dalam sistem Depot Air Minum RO OXY
menyimpan informasi terkait para pengelola sistem. Setiap admin
memiliki ID unik (Id Admin) yang berfungsi sebagai identifikasi
mereka dalam sistem. Atribut Nama Admin menyimpan nama
lengkap admin yang bertugas, sehingga memudahkan dalam
pengelolaan dan pelacakan aktivitas mereka. Untuk keperluan
komunikasi, terdapat juga atribut No Hp, yang menyimpan nomor
telepon yang dapat dihubungi. Selain itu, Email berfungsi untuk
menyimpan alamat email admin, yang penting untuk pengiriman
notifikasi atau komunikasi penting lainnya. Terakhir, Pin adalah
kode keamanan yang digunakan oleh admin untuk mengakses


---

## Halaman 7

sistem, menjaga keamanan data dan memastikan hanya admin
yang berwenang yang dapat mengelola informasi dalam sistem
G kansaksi y terbayar)
1. PF una)
( ! total harga 3
Entitas Transaksi mencatat setiap transaksi yang terjadi di Depot
Air Minum RO OXY, dengan id transaksi sebagai identifikasi
unik. Atribut-atribut utama dalam entitas ini meliputi id kurir
untuk mengetahui kurir yang mengirimkan produk, status pesanan
yang menunjukkan kondisi pesanan. pembayaran yang mencatat
metode pembayaran yang digunakan, tanggal yang merekam
waktu transaksi, dan total harga yang menyimpan jumlah total
yang dibayar oleh pelanggan. Dengan entitas ini, setiap transaksi
dapat dikelola dan dilacak secara efisien dalam sistem.
| GetailTransaksi | aa am
fo sub total
id transaksi Sana
id detail) (Ga)
id produk
Entitas detailTransaksi berfungsi untuk mencatat rincian setiap
transaksi yang terjadi di Depot Air Minum RO OXY. Setiap entitas
detailTransaksi memiliki id detail sebagai pengenal unik. yang
membedakan satu transaksi dari transaksi lainnya. Entitas ini
berhubungan dengan entitas Transaksi melalui id transaksi, yang
memungkinkan sistem untuk menghubungkan rincian detail
dengan transaksi utama yang tercatat. Selain itu, id produk
mencatat produk yang terlibat dalam transaksi, misalnya air galon
atau produk lainnya, sedangkan jumlah mencatat banyaknya
produk yang dibeli dalam transaksi tersebut. Atribut sub total
menyimpan total harga untuk produk yang dibeli, sebelum
penambahan biaya lain seperti pengiriman atau pajak. Dengan
entitas ini, sistem dapat memantau dan menghitung rincian setiap


---

## Halaman 8

transaksi dengan akurat, serta memudahkan dalam melakukan
analisis terhadap produk yang sering terjual
Haa sma)

0 (sumber) engga ——
Entitas Pemasukan digunakan untuk mencatat semua pemasukan
yang diterima oleh Depot Air Minum RO OXY. Setiap pemasukan
memiliki id pemasukan yang menjadi identifikasi unik untuk
setiap entri. Atribut lainnya termasuk sumber, yang mencatat asal
dari pemasukan tersebut. seperti hasil penjualan produk atau
pengembalian galon. Selain itu, ada jumlah, yang mencatat nilai
uang yang diterima dalam setiap pemasukan, dan tanggal, yang
berguna untuk mencatat kapan pemasukan diterima agar bisa
dipantau secara tepat. Terdapat pula atribut manual, yang mencatat
apakah pemasukan dicatat secara manual oleh admin atau otomatis
melalui sistem. Terakhir, entitas ini juga berhubungan dengan
transaksi, yang memungkinkan untuk melacak pemasukan yang
terkait dengan transaksi tertentu, memberikan kemudahan dalam
menghubungkan setiap pemasukan dengan kegiatan bisnis yang
spesifik.

luaran
Mana AN
@ karyawan ea, 2
Pe (sumber)
(Goa —


---

## Halaman 9

Entitas Pengeluaran mencatat semua pengeluaran yang terjadi di
Depot Air Minum RO OXY, dengan id pengeluaran sebagai
pengenal unik. Atributid kategori pengeluaran mengelompokkan
pengeluaran berdasarkan jenisnya, seperti biaya gaji karyawan,
kurir, atau operasional lainnya. Jumlah mencatat nominal
pengeluaran, sementara tanggal menyimpan tanggal terjadinya
pengeluaran. Keterangan memberikan informasi tambahan, dan
sumber menunjukkan apakah pengeluaran dicatat secara manual
atau sistematis. Dengan entitas ini, pengelolaan dan pelacakan
pengeluaran menjadi lebih terorganisir dan transparan.
AN
ran
luaran

Entitas Kategori Pengeluaran berfungsi untuk
mengelompokkan jenis pengeluaran yang terjadi di Depot Air
Minum RO OXY. Setiap kategori pengeluaran memiliki
id kategori pengeluaran sebagai pengenal unik, yang
memungkinkan setiap kategori dapat dibedakan dengan jelas
Selain itu, atribut nama kategori digunakan untuk
memberikan nama atau deskripsi dari kategori pengeluaran
tersebut, seperti biaya operasional, gaji karyawan, atau
pemeliharaan Dengan adanya entitas ini, sistem dapat
mengelompokkan dan memonitor pengeluaran berdasarkan
kategori, memudahkan dalam pelaporan keuangan dan
pengelolaan anggaran


---

## Halaman 10

TES
g total gajiku
Entitas Kurir mencatat informasi mengenai kurir yang bekerja di
Depot Air Minum RO OXY. Setiap kurir memiliki id kurir sebagai
pengenal unik, yang membedakan satu kurir dari yang lain. Atribut
nama kurir mencatat nama lengkap kurir, sedangkan no hp
menyimpan nomor telepon kurir yang bisa dihubungi untuk
keperluan pengiriman atau komunikasi lainnya. Selain itu,
total gajiku mencatat jumlah gaji yang diterima oleh kurir, yang
mungkin didasarkan pada jumlah pengiriman atau sistem
pembayaran lain yang berlaku. Dengan entitas Kurir, depot dapat
memonitor kinerja pengiriman dan mengelola data gaji kurir
dengan lebih efektif.
(mi

Ni Gowa 0) - Mar ) s
ERD ini menggambarkan entitas — Gaji Kurir, — yang
merepresentasikan data gaji yang diterima oleh kurir berdasarkan
pengantaran barang. Entitas ini memiliki atribut id gaji ku
sebagai identifikasi unik. id kurir yang menghubungkan gaji
dengan kurir melalui kunci asing, dan keterangan untuk informasi
tambahan mengenai gaji. Atribut jumlah galon antar mencatat
jumlah galon yang diantar, yang mempengaruhi gaji, sedangkan
gaji per galon menentukan tarif per galon yang diantar. Selain itu,
ada atribut galon pecah dan potongan untuk mencatat kondisi
galon dan potongan terhadap gaji. memastikan gaji yang diterima
sesuai dengan kinerja kurir dan keadaan barang yang diantar.


---

## Halaman 11

Ga
(Ga (Jabatan ) no.hp
— (ama nyam — £ total gajinon 5
Id Karyawan ——
| nan |
ERD ini menggambarkan entitas Karyawan, yang menyimpan data
penting seperti id karyawan sebagai identifikasi unik,
nama karyawan untuk nama lengkap, dan no hp untuk nomor
telepon. Atribut jabatan menunjukkan posisi karyawan, sementara
mekanik dan non kurir menandakan spesialisasi pekerjaan
mereka. Atribut total gaji mencatat jumlah gaji yang diterima.
Secara keseluruhan, ERD ini menggambarkan struktur data
karyawan beserta informasi jabatan dan gaji dalam perusahaan.
tanggal gajika
aa id karyawan
Oa jumlah gajika

ERD ini menggambarkan entitas Gaji Karyawan, yang
menyimpan data terkait gaji karyawan dalam perusahaan. Atribut
id gaji berfungsi sebagai identifikasi unik untuk setiap catatan
gaji, sementara id karyawan menghubungkan gaji dengan
karyawan tertentu. tanggal gajika mencatat tanggal pemberian
gaji, dan jumlah gajika menyimpan jumlah gaji yang diterima.
Atribut keterangan memberikan informasi tambahan terkait gaji,
seperti potongan atau bonus. Secara keseluruhan, ERD ini
menggambarkan cara informasi gaji karyawan dicatat dan
dihubungkan dengan data karyawan lainnya.


---

## Halaman 12

ERD ini menggambarkan entitas Absensi, yang digunakan untuk
mencatat data absensi karyawan. Atribut id absensi berfungsi
sebagai identifikasi unik untuk setiap catatan absensi, sementara
id karyawan menghubungkan absensi dengan karyawan tertentu
melalui kunci asing. Atribut tanggal mencatat tanggal absensi
dilakukan, dan ket kehadiran menyimpan keterangan tentang
status kehadiran karyawan, seperti "Hadir", "Izin", atau "Tidak
Hadir". Secara keseluruhan, ERD ini menggambarkan bagaimana
data absensi karyawan dicatat dan dihubungkan dengan informasi
karyawan lainnya.

ERD ini menggambarkan entitas Produk. yang menyimpan
informasi terkait produk dalam sistem. Atribut id produk
berfungsi sebagai identifikasi unik untuk setiap produk, sementara
id kategori produk menghubungkan produk dengan kategori
tertentu. Nama Produk mencatat nama produk yang dijual, dan
harga menyimpan harga satuan produk. Selain itu, terdapat atribut
grosir minimal, yang menunjukkan jumlah minimal produk yang
harus dibeli untuk mendapatkan harga grosir, serta harga grosir,
yang mencatat harga produk jika dibeli dalam jumlah grosir. ERD
ini menggambarkan bagaimana data produk diatur, termasuk
kategori, harga, dan harga grosir dalam sistem.


---

## Halaman 13

—

ERD ini menggambarkan entitas Kategori Produk, yang
digunakan untuk menyimpan informasi terkait kategori suatu
produk. Entitas ini memiliki dua atribut utama, yaitu Id Kategori,
yang berfungsi sebagai identifikasi unik setiap kategori, dan
Nama Kategori, yang mencatat nama kategori produk tersebut.
Dengan entitas ini, setiap produk dalam sistem dapat
dikelompokkan berdasarkan kategori tertentu, — sehingga
memudahkan dalam pengelolaan dan pencarian produk sesuai
dengan kelompok yang relevan.