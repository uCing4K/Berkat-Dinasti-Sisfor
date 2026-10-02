# Perbaikan pada Manual Book v2

Dokumen ini merangkum perbaikan yang sudah disatukan ke [database/schema.sql](database/schema.sql) sebagai sumber final tunggal.

## Ringkasan

Perubahan utama yang sudah masuk ke schema final:
- kontrol stok detail pesanan
- validasi status pesanan forward-only
- validasi pembayaran dan anti overpayment
- pembatalan pesanan yang mengembalikan stok dan kas
- ongkir zona otomatis masuk ke total transaksi
- hotfix `sp_update_status` agar selalu tersedia saat schema dijalankan ulang

## Catatan Penting

Hotfix yang sebelumnya sempat dipisah sekarang dianggap sudah merged ke [database/schema.sql](database/schema.sql). Jadi, schema itu adalah satu-satunya sumber definisi final untuk database.

## Sinkronisasi Dokumen

Penjelasan teknis di [database/penjelasan_source_code_manual_book_v2.md](database/penjelasan_source_code_manual_book_v2.md) sudah disesuaikan agar selaras dengan schema final, termasuk:
- trigger ongkir otomatis dari zona pelanggan
- status bayar `hutang`
- validasi pembayaran di trigger dan procedure
- prosedur `sp_update_status`

## Status Akhir

- semua patch final sudah dianggap menyatu di [database/schema.sql](database/schema.sql)
- dokumen manual book sudah diselaraskan
- file hotfix terpisah hanya bersifat historis

## Audit Sinkronisasi Terbaru (25 April 2026)

- Blok `trg_before_insert_pesanan` pada [database/penjelasan_source_code_manual_book_v2.md](database/penjelasan_source_code_manual_book_v2.md) sudah disamakan dengan [database/schema.sql](database/schema.sql), termasuk pengisian `ongkir` otomatis dari zona pelanggan.
- Alur status forward-only dan status bayar (`belum_bayar`, `dp`, `hutang`, `lunas`) tetap konsisten antara dokumen penjelasan dan schema final.
- Catatan ini menegaskan bahwa dokumen manual book sudah diaudit ulang terhadap schema final setelah rangkaian patch transaksi.