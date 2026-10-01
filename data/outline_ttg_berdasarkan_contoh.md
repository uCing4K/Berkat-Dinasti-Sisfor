# Outline TTG (berdasarkan `contoh ttg.pdf`)

Catatan: PDF contoh berbentuk scan (gambar), jadi struktur ini disarikan dari hasil OCR di `data/contoh_ttg_ocr.md`.

## 1) Halaman Sampul (Cover)
- Judul besar: **KARYA TERAPAN**
- Subjudul: **TUGAS PROYEK BASIS DATA**
- Judul karya/proyek (1 kalimat)
- Keterangan mendukung SDGs (contoh: SDGs 8)
- Identitas tim:
  - Ketua (Nama + NIM)
  - Anggota (Nama + NIM)
  - Asisten 1 & 2 (Nama + NIM)
- Pembimbing 1 & 2 (Nama + email)
- Validator (Nama + email)
- Identitas institusi (Prodi, Departemen, Fakultas, Universitas)

## 2) Dokumen Deskripsi Karya Terapan (Metadata Produk)
Biasanya berupa tabel/daftar isian:
- Produk mendukung SDGs (contoh: SDGs 8)
- Nama pencipta:
  - Ketua
  - Anggota
- Pembimbing 1 & 2
- Link produk

## 3) Deskripsi Produk
- 1–3 paragraf ringkas tentang:
  - Sistem/produk apa yang dibuat
  - Ruang lingkup modul utama
  - Masalah yang diselesaikan (metode manual, duplikasi, rawan salah, dll.)
  - Dampak/manfaat ke operasional + keterkaitan SDGs

## 4) Tujuan/Manfaat Produk
Umumnya 3 poin (paragraf) seperti:
1. Mempermudah/mengintegrasikan operasional dan data inti (real-time monitoring).
2. Menyediakan analisis data (pola transaksi, perencanaan stok, strategi layanan).
3. Mendukung digitalisasi & daya saing UMKM (transparansi/akuntabilitas/layanan profesional).

## 5) Produk: Struktur ERD
- Bagian “Produk” berisi daftar entitas + atribut (ringkas), lalu disusul ERD lengkap.
- Contoh entitas yang muncul pada contoh:
  - Admin
  - Transaksi
  - Detail Transaksi
  - Pemasukan
  - Pengeluaran
  - Kategori Pengeluaran
  - Kurir
  - Gaji Kurir
  - Karyawan
  - Gaji Karyawan
  - Absensi
  - Produk
  - Kategori Produk

## 6) Tahap Pengembangan (SDLC)
- Biasanya hanya daftar tahap utama (contoh yang terlihat):
  1. Perencanaan
  2. Analisis
  3. Desain

## 7) Gambar Produk: ERD Full
- Sisipkan gambar ERD lengkap.
- Setelah ERD full, biasanya ada penjelasan tiap tabel/entitas disertai screenshot.

## 8) Penjelasan Entitas per Tabel
Untuk setiap entitas:
- Sisipkan gambar/screenshot tabel/ERD entitas
- Jelaskan:
  - Fungsi entitas di sistem
  - Primary key
  - Foreign key (relasi)
  - Arti atribut penting
  - Kaitan ke proses bisnis

(Contoh di PDF menjelaskan per entitas: Admin, Transaksi, DetailTransaksi, Pemasukan, Pengeluaran, Kategori Pengeluaran, Kurir, Gaji Kurir, Karyawan, Gaji Karyawan, Absensi, Produk, Kategori Produk.)
