# 🎨 Panduan UI/UX & Design System - Berkat Dinasti

Dokumen ini berisi keputusan antarmuka, *design system*, hasil *usability testing*, dan panduan visual untuk aplikasi **Sistem Informasi Berkat Dinasti**.

---

## 1. Keputusan Utama Perancangan UI/UX

Berdasarkan hasil wawancara lanjutan dengan mitra (UMKM Dynasty Food & Beverage), terdapat penyederhanaan antarmuka agar sesuai dengan operasional riil admin:

| Komponen UI/UX | Keputusan Final | Alasan Keputusan |
| :--- | :--- | :--- |
| **Akses Pengguna** | Dashboard Khusus Admin Internal | Pelanggan tetap memesan via WhatsApp / offline. Tidak perlu portal web customer. |
| **Katalog Produk** | Tabel Nama & Harga Roti | Gambar foto roti tidak diperlukan untuk admin, mempercepat *page load* & pencarian. |
| **Kalender Operasional**| Kalender Deadline Pengiriman | Admin butuh tanggal pengiriman pesanan, bukan jadwal kerja dapur. |
| **Status Pesanan** | Simplified Swimlanes / Status | Memudahkan pengelolaan status tanpa tahapan yang membingungkan. |
| **Informasi Keuangan** | Fokus Pemasukan & Piutang | Pemilik hanya perlu fokus memantau *inflow* pemasukan dan tagihan belum lunas. |

---

## 2. Design System & Typography

- **Font Family:** `Inter` (Variable Font) — Tersedia di `docs/UI UX/Inter/`.
- **Warna & Layout Theme:** Modern Dashboard dengan nuansa kontras tinggi, bersih, dan meminimalisir distraksi visual.
- **Form Controls:** Tombol CTA utama (seperti `+ Tambah Pesanan Baru` dan `Simpan`) dibuat dengan kontras dan ukuran lebih besar berdasarkan masukan *usability testing*.

---

## 3. Hasil Usability Testing (Feedback Pengguna)

| Pengujian Task | Hasil | User Feedback | Tindakan Perbaikan |
| :--- | :---: | :--- | :--- |
| **Tambah Pesanan Baru** | ✅ Berhasil | "Cukup mudah dan cepat, tapi tombol simpannya kurang besar." | Memperbesar ukuran tombol 'Simpan' pada modal form. |
| **Cek Deadline Pengiriman**| ✅ Berhasil | "Mudah dilihat di kalender, tanggal yang ada pesanannya jelas." | Pertahankan tampilan indikator tanggal aktif. |
| **Ubah Status Pesanan** | ✅ Berhasil | "Sangat praktis karena tinggal geser-geser (Kanban)." | Pertahankan interaksi Kanban board. |
| **Cek Total Pemasukan** | ✅ Berhasil | "Grafiknya bagus, tapi awalnya agak bingung ganti filter bulannya." | Memperjelas & memperbesar tombol dropdown filter periode. |

> 📌 **Tautan Prototype Figma:** [Figma Interactive Prototype - Berkat Dinasti](https://www.figma.com/proto/nyxxCopYBjWGphWFkbYawy/SISFOR---BERKAT-DINASTI?node-id=10-841&t=5CYRnaYdXR7Nwk3x-1&scaling=min-zoom&content-scaling=fixed&page-id=0%3A1)

---
*Dokumen ini merupakan standar panduan UI/UX proyek Berkat Dinasti.*
