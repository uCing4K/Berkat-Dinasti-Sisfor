# STITCH.AI PROMPTS — BERKAT DINASTI
> Prompts siap pakai per halaman untuk generate UI Berkat Dinasti di Stitch.ai  
> Selalu attach `DESIGN.md` sebelum menggunakan prompt ini.

---

## ⚙️ CARA PENGGUNAAN

1. Buka [Stitch.ai](https://stitch.withgoogle.com)
2. Buat project baru: **"Berkat Dinasti"**
3. Upload `DESIGN.md` sebagai **Design Context** (fitur `+ Design`)
4. Gunakan prompt per halaman di bawah ini satu per satu
5. Setelah generate, review dan iterasikan dengan **refinement prompts** yang tersedia

---

## 📌 CONTEXT HEADER (Sertakan di awal SETIAP prompt baru)

```
App: Berkat Dinasti — UMKM Bakery Order Management System
Design: Warm brown & orange theme (#3E2B21 sidebar, #FF9B45 primary, #F9F7F5 bg)
Font: Inter (Google Fonts)
Layout: Sidebar fixed 240px (dark brown) + main content area (warm off-white)
Style: Clean, professional, warm, desktop-first (1440px)
Refer to the uploaded DESIGN.md for all design tokens and component specs.
```

---

---

# PAGE 1 — LOGIN
**Route:** `/login`

## Prompt Utama

```
Design a login page for "Berkat Dinasti" — a bakery UMKM order management web app.

LAYOUT:
- Full viewport, 2-column split
- Left 40%: brand panel with dark warm brown bg (#3E2B21), centered logo text "Berkat Dinasti" in white (bold 36px), tagline "Kelola Pesanan Rotimu dengan Mudah" (16px, warm beige), decorative warm radial glow or subtle bread illustration in monochrome amber tones
- Right 60%: login form panel, bg #F9F7F5 (warm off-white), vertically centered

FORM CARD (right panel):
- White card (#FFFFFF), border-radius 16px, shadow: 0 8px 24px rgba(0,0,0,0.12)
- Width: 440px, padding: 40px 48px
- Title: "Selamat Datang" H4 (32px, 700, color #1D1D1D)
- Subtitle: "Masuk ke akun Anda" (14px, #828282)
- Username field: label "Username", input with person icon on left, placeholder "Masukkan username"
- Password field: label "Password", input with lock icon, show/hide toggle eye icon on right
- "Lupa Password?" link: right-aligned, 14px, color #FF9B45
- Submit button: full-width, "Masuk", bg #FF9B45, text white, 16px 600, border-radius 8px, height 48px
- Button hover: bg #B86B28
- Footer note: "© 2025 Berkat Dinasti" (12px, #BDBDBD, centered bottom of card)

All inputs: border 1.5px solid #E0E0E0, border-radius 8px, padding 12px 16px, focus ring #FF9B45

No registration link (closed system — admin only).
```

## Reset Password Sub-Page Prompt

```
Design a "Reset Password" page for Berkat Dinasti, same layout as login page.

Right panel form card content:
- Back arrow link top-left: "← Kembali ke Login" (14px, #FF9B45)
- Title: "Reset Password" (H4, 32px, 700)
- Description: "Masukkan username Anda dan kami akan mengirim tautan reset ke admin." (14px, #828282, max-width 320px)
- Username field: same styling as login
- Submit button: full-width, "Kirim Permintaan Reset", primary orange
- State feedback: success state shows green checkmark illustration + message "Permintaan berhasil dikirim. Hubungi admin."
```

## Refinement Tips
- Tambahkan: "make the left panel taller, add a subtle warm gradient overlay from top #3E2B21 to bottom #5C3D2A"
- Jika terlalu plain: "add a decorative diagonal stripe or honeycomb pattern in amber at 8% opacity on the left panel background"
- Jika form terlalu kecil: "increase form card width to 480px and increase padding to 48px 56px"

---

---

# PAGE 2 — BERANDA (DASHBOARD)
**Route:** `/`

## Prompt Utama

```
Design a dashboard page "Beranda" for Berkat Dinasti bakery management app.

SHELL LAYOUT:
- Fixed sidebar left 240px, bg #3E2B21 (dark warm brown)
- Main content area right, bg #F9F7F5, padding 32px

SIDEBAR:
- Top: logo area 80px — bread emoji icon + "Berkat Dinasti" text (18px, 700, white)
- Nav items (48px height, 16px h-padding):
  [🏠] Beranda — ACTIVE (bg #FF9B45, text+icon white, border-radius 8px)
  [📦] Produk
  [📋] Pesanan
  [👥] Pelanggan
  [📊] Laporan
  [⚙️] Pengaturan
- Bottom: user avatar circle (36px, initials "BD"), name "Admin", role "Pemilik", logout icon right

TOP BAR (64px, bg #FFFFFF, border-bottom 1px solid #E0E0E0):
- Left: "Beranda" (H5, 24px, 700) with subtitle "Selamat pagi, Admin 👋"
- Right: notification bell icon + date "Jumat, 26 Sep 2025"

CONTENT — 4 KPI STAT CARDS (top row, 4 columns):
Each card: bg #FFFFFF, border-radius 12px, shadow medium, left accent 4px solid (varies)
1. "Total Pesanan Hari Ini" — value "12", icon 📋, accent #FF9B45, icon bg rgba(255,155,69,0.12)
2. "Pesanan Pending" — value "5", icon ⏳, accent #E2B93B, icon bg rgba(226,185,59,0.12)
3. "Total Pemasukan Bulan Ini" — value "Rp 4.250.000", icon 💰, accent #27AE60, icon bg rgba(39,174,96,0.12)
4. "Pelanggan Baru" — value "3", icon 👥, accent #2F80ED, icon bg rgba(47,128,237,0.12)

CONTENT — SECOND ROW (2 columns, 60/40 split):
Left (60%): "Pesanan Pending" table card
  - Header: "Pesanan Perlu Tindakan" + badge count warning amber
  - Compact table: Nama Pelanggan | Produk | Deadline | Pembayaran | Aksi
  - 5 rows of sample data
  - Payment badge: Belum Bayar (gray), Hutang (amber), Lunas (green)
  - Deadline: show in red if today or past
  - Aksi column: "Lihat Detail" link in #FF9B45
  - "Lihat Semua →" footer link

Right (40%): Mini calendar card
  - Month view September 2025
  - Dates with delivery dot indicator in #FF9B45
  - Today circle outlined in #FF9B45
  - Title "Jadwal Pengiriman" (16px, 700)

CONTENT — THIRD ROW: "Aktivitas Terbaru" timeline card
  - Timeline dots with color by type (order=orange, payment=green, new customer=blue)
  - 5 recent activity items with timestamp
  - Compact list, full-width card

All cards: bg #FFFFFF, border-radius 12px, padding 24px, shadow 0 4px 12px rgba(0,0,0,0.10)
```

## Refinement Tips
- Untuk KPI cards lebih kaya: "add a small sparkline trend line (last 7 days) below each KPI value"
- Jika layout terlalu crowded: "add 20px gap between rows"
- Untuk header lebih menarik: "add a full-width welcome banner card below the top bar in gradient #FF9B45 to #B86B28 with white text 'Ringkasan Hari Ini' and 3 inline stats"

---

---

# PAGE 3 — PRODUK
**Route:** `/produk`

## Prompt Utama

```
Design the "Produk" (Products) page for Berkat Dinasti bakery management app.

Use the same sidebar and top bar layout as the dashboard.
Active nav item: "Produk"
Top bar title: "Manajemen Produk"

TOP ACTION BAR (below top bar, above table):
- Left: search input (icon + placeholder "Cari produk..."), width 280px
- Left: filter dropdown "Kategori" (all categories)
- Right: "Tambah Produk" button — primary orange, "+ Tambah Produk", icon plus

PRODUCT TABLE CARD:
- Full-width card, bg #FFFFFF, border-radius 12px, shadow medium
- Table header: bg #FF9B45, text #FFFFFF, 14px, 700
- Columns: No | Nama Produk | Kategori | Kemasan | Minimal Order | Harga | Status | Aksi
- Row data examples:
  1. Roti Tawar | Roti | Satuan | 5 pcs | Rp 5.000/pcs | Active badge green
  2. Donat Coklat | Donat | Mika (12pcs) | 1 mika | Rp 45.000/mika | Active badge green
  3. Roti Sisir | Roti | Kardus (20pcs) | 1 kardus | Rp 80.000/kardus | Active badge green
  4. Kue Bolu | Kue | Satuan | 10 pcs | Rp 8.500/pcs | Inactive badge gray
- Alternating row bg: white and #FFF8F2
- Hover row: bg #FFF0E0
- Kemasan badge: small pill — "Mika" info-blue, "Kardus" warning-amber, "Satuan" gray
- Status badge: Active = success-green, Inactive = gray
- Aksi column: edit icon (pencil, #FF9B45) + delete icon (trash, #EB5757)
- Pagination: bottom-right, page 1 of 3

KEMASAN INFO TOOLTIP:
- Hovering the kemasan badge shows tooltip: "Mika (12pcs) — minimal order 1 mika"
```

## Add/Edit Product Modal Prompt

```
Design an "Add Product" modal dialog for Berkat Dinasti.

Modal: centered overlay, bg white, border-radius 16px, padding 32px, width 560px, shadow overlay
Header: "Tambah Produk Baru" (H5, 24px, 700) + × close button
Form fields (2-column grid where applicable):
- Nama Produk (full-width text input)
- Kategori (dropdown: Roti / Donat / Kue / Lainnya)
- Harga Satuan (number input, prefix "Rp", right-aligned)
- Kemasan (dropdown: Satuan / Mika / Kardus)
  → If Mika or Kardus selected: show additional field "Jumlah per Kemasan" (number input)
- Minimal Order (number input + unit label dynamic: "pcs" or "mika" or "kardus")
- Status (toggle switch: Aktif / Nonaktif — ON=orange, OFF=gray)
- Deskripsi (textarea, 3 rows, optional)
Footer: [Batal] outline button + [Simpan Produk] primary orange button
```

## Refinement Tips
- Jika tabel terlalu lebar: "add horizontal scroll on the table for smaller viewports"
- Untuk pencarian lebih canggih: "add active filter chips below the search bar showing current active filters with × to remove"

---

---

# PAGE 4 — PESANAN
**Route:** `/pesanan`

## Prompt Utama

```
Design the "Pesanan" (Orders) page for Berkat Dinasti bakery management app.

Same sidebar layout, active nav: "Pesanan"
Top bar title: "Manajemen Pesanan"

PAGE TABS (below top bar):
Two tabs: [Kanban Board] [Kalender Pengiriman]
Active tab: "Kanban Board" — underline style, color #FF9B45, 14px 600

TOP ACTION BAR:
- Left: search input "Cari pesanan atau pelanggan..."
- Left: filter "Pembayaran" dropdown (Semua / Lunas / Hutang / Belum Bayar)
- Right: "Tambah Pesanan" button — primary orange, "+ Tambah Pesanan"

KANBAN BOARD (main content):
Display: flex row, gap 20px, overflow-x scroll

Column 1 — "Pending" (status warning amber):
  Header: badge "Pending" in amber + count "5"
  Column bg: #F9F7F5, border-radius 12px, padding 16px, width 300px, min-height 600px

  Sample kanban cards (3 cards):
  Card 1: customer "Ibu Sari", items "Roti Tawar 20pcs, Donat 1 mika", deadline "Hari Ini", payment "Belum Bayar" badge gray, left border accent amber
  Card 2: customer "Pak Budi", items "Roti Sisir 2 kardus", deadline "Besok", payment "Hutang" badge amber, left border accent amber  
  Card 3: customer "Toko Maju", items "Kue Bolu 15pcs", deadline "28 Sep", payment "Lunas" badge green, left border accent amber

Column 2 — "Selesai" (status success green):
  Header: badge "Selesai" in green + count "8"
  Column bg: #F9F7F5, same width
  
  Sample kanban cards (2 cards showing):
  Card 1: customer "Bu Rina", items "Donat Coklat 2 mika", deadline "25 Sep", payment "Lunas" badge green, left border accent green
  Card 2: customer "Warung Pak Haji", items "Roti Tawar 50pcs", deadline "25 Sep", payment "Lunas" badge green, left border accent green

Each Kanban Card:
  - bg #FFFFFF, border-radius 8px, padding 16px, shadow 0 2px 6px rgba(0,0,0,0.06)
  - Left border 3px solid (pending=amber, selesai=green)
  - Customer name: 14px, 700, #1D1D1D
  - Items: 13px, #828282, truncate to 1 line
  - Deadline row: calendar icon 12px + date text, red color if overdue
  - Payment badge: pill bottom-right
  - Drag handle icon (⠿) top-right corner, #BDBDBD
  - Hover: shadow increase, cursor grab

"+ Tambah Kartu" button at bottom of Pending column: dashed border, text #828282, full-width
```

## Calendar Tab Prompt

```
Design the "Kalender Pengiriman" tab for Pesanan page.

Full calendar monthly view (September 2025):
- Calendar container: bg #FFFFFF, border-radius 12px, padding 24px, shadow medium
- Header: "September 2025" (H5, 700) + prev/next arrows (< >) in #FF9B45
- Day-of-week headers: Sen Sel Rab Kam Jum Sab Min — 14px 600 #828282
- Date cells: 48px × 48px each, hover bg #FFF0E0
- Today (26): circle border 2px solid #FF9B45
- Dates with deliveries: bg #FF9B45, text #FFFFFF, border-radius 50%
  - Dates: 26, 27, 29 Sep have deliveries
- Overdue delivery: bg #EB5757, text #FFFFFF
- Click on a date: show sidebar panel or popover with list of deliveries for that day

Day delivery popover (when date clicked):
  - Small card popover with title "Pengiriman 26 Sep"
  - List: customer name + total items, each with payment badge
  - "Lihat Detail" link per item
```

## Add Order Modal Prompt

```
Design a "Tambah Pesanan" modal for Berkat Dinasti — large multi-section form.

Modal: centered overlay, bg white, border-radius 16px, width 720px, max-height 90vh, overflow-y scroll, padding 32px, shadow overlay

HEADER: "Tambah Pesanan Baru" (H5, 24px, 700) + × close button

SECTION 1 — Data Pelanggan:
Section title: "Data Pelanggan" (16px, 700, color #333333, border-bottom 1px solid #E0E0E0, margin-bottom 16px)
- Row: Customer search field "Cari atau pilih pelanggan..." (autocomplete dropdown)
- OR link: "+ Tambah Pelanggan Baru" (text link in #FF9B45, inline below search)
  → Clicking expands inline form: Nama, No. Telp, Alamat (3 fields, collapsible)

SECTION 2 — Detail Pesanan:
Section title: "Detail Pesanan"
- Table-style product input rows:
  Header: Produk | Kemasan | Jumlah | Harga Satuan | Subtotal
  Row 1: dropdown [pilih produk] | auto-fill kemasan | number input qty | auto-fill harga | auto-calc subtotal
  Row 2: (same structure)
  "+ Tambah Produk" button: dashed card, full-width, #FF9B45 text

VALIDATION ROW (shows when qty < minimum):
  Red warning box: lock icon + "Roti Tawar — Minimum order 5 pcs. Pesanan dikunci." (#EB5757 border-left, bg red-tint)
  Submit button disabled (bg #E0E0E0)

SECTION 3 — Pengiriman & Pembayaran:
Two columns:
  Left: Tanggal Pengiriman (date picker), Catatan (textarea)
  Right: 
    Total Pesanan: Rp XX.XXX (large, bold, #FF9B45)
    Status Pembayaran toggle group:
      [Belum Bayar] [Hutang] [Lunas] — 3 buttons, selected = filled, unselected = outline
    Catatan Pembayaran (optional textarea small)

FOOTER: [Batal] + [Simpan Pesanan] primary orange
```

## Refinement Tips
- Kanban: "add a count summary bar above the kanban showing: Total 13 pesanan | 5 Pending | 8 Selesai | in pill stats"
- Modal: "add step indicators at the top of the add order modal: Step 1: Pelanggan → Step 2: Produk → Step 3: Pembayaran"

---

---

# PAGE 5 — PELANGGAN
**Route:** `/pelanggan`

## Prompt Utama

```
Design the "Pelanggan" (Customers) page for Berkat Dinasti bakery management app.

Same sidebar layout, active nav: "Pelanggan"
Top bar title: "Data Pelanggan"

TOP ACTION BAR:
- Left: search input "Cari nama pelanggan, no. telp..."
- Right: "Tambah Pelanggan" — primary orange button

CUSTOMERS TABLE CARD (left ~65% when detail panel is closed, full-width by default):
Full-width card, border-radius 12px, shadow medium
Header: bg #FF9B45, columns: No | Nama Pelanggan | No. Telp | Alamat | Total Pesanan | Total Tagihan | Aksi
Row data examples:
  1. Ibu Sari Dewi | 081234567890 | Jl. Mawar No. 5 | 12 pesanan | Rp 560.000 | (badge Lunas green)
  2. Pak Budi Santoso | 082345678901 | Jl. Kenanga 10 | 8 pesanan | Rp 320.000 | (badge Hutang amber)
  3. Toko Maju Jaya | 083456789012 | Jl. Raya Industri 20 | 25 pesanan | Rp 1.250.000 | (badge Lunas green)
  4. Warung Pak Haji | 085678901234 | Jl. Pesantren 3 | 5 pesanan | Rp 180.000 | (badge Belum Bayar gray)

Tagihan badge: Lunas=green, Hutang=amber, Belum Bayar=gray
Aksi: eye icon (view detail, #FF9B45) + edit icon (pencil, #828282) + delete icon (trash, #EB5757)
Clicking eye opens right-side detail panel

DETAIL SLIDE PANEL (right 35%, slides in when clicking a customer):
  - bg #FFFFFF, left border 1px solid #E0E0E0, height 100%, padding 24px
  - Header: customer name H5 + × close button
  - Customer info card: phone, address, notes icons + values
  - Stats row: 3 mini stats — Total Pesanan / Total Tagihan / Pesanan Aktif
  - TABS: [Riwayat Pesanan] [Info Kontak]
  
  Riwayat Pesanan tab:
    - List of past orders (compact)
    - Each: date | items summary | total | payment badge
    - Sorted newest first
    - "Lihat Semua" link at bottom
  
  Info Kontak tab:
    - Editable form fields: nama, telp, alamat, catatan
    - "Simpan Perubahan" button
```

## Refinement Tips
- Untuk panel detail lebih polish: "add a colored avatar circle at top of detail panel with customer initials, large (64px), bg #FF9B45, white initials"
- Filter: "add filter chips above table: Semua | Ada Hutang | Pelanggan Aktif"

---

---

# PAGE 6 — LAPORAN
**Route:** `/laporan`

## Prompt Utama

```
Design the "Laporan" (Reports) page for Berkat Dinasti bakery management app.

Same sidebar layout, active nav: "Laporan"
Top bar title: "Laporan & Keuangan"

TOP FILTER BAR:
- "Periode:" label + dropdown "Bulan Ini" (options: Hari Ini / 7 Hari / Bulan Ini / 3 Bulan / Tahun Ini / Custom Range)
- Custom range: 2 date pickers (dari – sampai)
- "Terapkan Filter" button — primary orange
- Export button: "Unduh Excel" — outline orange button, download icon

SUMMARY KPI CARDS (4 cards, top row):
1. "Total Pemasukan" — Rp 4.250.000 | accent green | up trend +12% vs last month (green ▲ tag)
2. "Total Pesanan" — 48 pesanan | accent orange
3. "Pesanan Selesai" — 42 | accent green  
4. "Pesanan Pending" — 6 | accent amber

CHART SECTION (below KPIs, 2 columns):
Left (65%): "Grafik Pemasukan Bulanan" card
  - Bar chart, 6 months Jan–Jun, bars in #FF9B45 gradient
  - Y-axis: Rupiah formatted (Rp 1jt, Rp 2jt, etc.)
  - X-axis: bulan labels
  - Current month bar: darker shade #B86B28
  - Chart container: #FFFFFF, border-radius 12px, padding 24px, shadow medium
  - Toggle: [Pemasukan] [Jumlah Pesanan] toggle tabs above chart

Right (35%): "Ringkasan Bulan Ini" card
  - Donut/pie chart: visual split of Lunas vs Hutang vs Belum Bayar
  - Legend below: each status with count and percentage
  - Total in center of donut: "48 Pesanan"
  - Color: Lunas=#27AE60, Hutang=#E2B93B, Belum Bayar=#BDBDBD

RIWAYAT PESANAN TABLE (full-width, filterable):
Card header: "Riwayat Semua Pesanan" + filter chips on right (Semua | Lunas | Hutang | Belum Bayar)
Table: same orange header style
Columns: No | Tanggal | Pelanggan | Produk | Total | Status Bayar | Aksi
Sample rows: 5 orders with varied statuses
Row hover: #FFF0E0
Aksi: eye icon "Detail"

Pagination: 1 2 3 ... 5, bottom-right
```

## Refinement Tips  
- Chart: "replace bar chart with a combination chart — bars for revenue amount, line for number of orders, dual Y-axis"
- Export: "add print icon next to download, tooltip 'Cetak Laporan'"
- Jika ingin insight lebih: "add a 'Top Produk Terlaris' table card below the chart — rank, nama produk, qty terjual, revenue"

---

---

# PAGE 7 — PENGATURAN
**Route:** `/pengaturan`

## Prompt Utama

```
Design the "Pengaturan" (Settings) page for Berkat Dinasti bakery management app.

Same sidebar layout, active nav: "Pengaturan"
Top bar title: "Pengaturan"

PAGE TABS (tab style, underline, #FF9B45 active):
[Profil UMKM]  [Manajemen User]

TAB 1 — PROFIL UMKM (active):
Card: #FFFFFF, border-radius 12px, padding 32px, max-width 760px

- Business logo section:
  Large circle avatar 96px, shows current logo (bakery icon placeholder in #FF9B45 bg, white icon)
  "Ubah Logo" text button below (14px, #FF9B45)

- Form grid (2 column):
  Nama Toko (full-width)
  No. Telp | WhatsApp Business
  Alamat Lengkap (full-width textarea)
  Provinsi | Kota/Kabupaten (dropdowns)
  Kode Pos | Email Bisnis

- Thermal Receipt Settings section:
  Section divider + title "Pengaturan Struk Printer"
  Toggle: Aktifkan Cetak Struk (ON/OFF toggle, orange)
  Ukuran Kertas: radio options [58mm] [80mm]
  Pesan Penutup: textarea "Terima kasih telah berbelanja di Berkat Dinasti!"
  Preview button: "Preview Struk" — outline button

- Footer: [Reset] danger button + [Simpan Perubahan] primary orange button

TAB 2 — MANAJEMEN USER:
"Tambah User" button top-right — primary orange

User table card:
Header: bg #FF9B45
Columns: Avatar | Nama | Username | Role | Status | Aksi
Rows:
  1. [BD avatar] | Admin Utama | admin | Pemilik | Active badge | edit icon
  2. [AS avatar] | Andi Susanto | andi.s | Karyawan | Active badge | edit icon + delete
  3. [RD avatar] | Rina Dewi | rina.d | Karyawan | Inactive badge gray | edit icon + activate link

Role badge: Pemilik = dark brown pill, Karyawan = orange outline pill
Status: Active = green, Inactive = gray

Add/Edit User Modal (separate):
  Fields: Nama Lengkap, Username, Password (with show/hide), Konfirmasi Password, Role dropdown
  Footer: [Batal] + [Simpan User]
```

## Refinement Tips
- Logo upload: "add a dashed upload zone that accepts drag-and-drop: 'Seret logo ke sini atau klik untuk pilih' with image icon"
- Security: "add a 'Keamanan' sub-section below form: Change Password fields (current, new, confirm)"

---

---

# PAGE 8 — LOGOUT CONFIRMATION
**Trigger:** Klik "Logout" di sidebar

## Prompt Utama

```
Design a logout confirmation modal dialog for Berkat Dinasti.

Centered modal overlay, bg rgba(0,0,0,0.5) backdrop blur
Modal card: bg #FFFFFF, border-radius 16px, padding 40px, width 400px, shadow overlay
Content:
  - Icon: large (64px) door-exit icon in circle, bg rgba(235,87,87,0.10), icon color #EB5757
  - Title: "Keluar dari Akun?" (H5, 24px, 700, center)
  - Description: "Anda akan keluar dari sesi ini. Pastikan semua perubahan sudah tersimpan." (14px, #828282, center)
  - Two buttons (full-width, stacked):
    [Batal] — outline button, border #E0E0E0, text #4F4F4F, full-width
    [Ya, Keluar] — danger button bg #EB5757, text #FFFFFF, full-width
  - Small spacing between buttons: 12px gap

Animation: modal appears with fade-in + scale from 0.95 to 1.0
```

---

---

# 🎯 TIPS STITCH.AI — SUPAYA HASILNYA SESUAI EKSPEKTASI

## 1. Selalu Upload DESIGN.md Pertama
```
Sebelum generate apapun, klik "+ Design" di Stitch dan upload file DESIGN.md.
Ini membuat AI punya referensi warna, font, dan komponen yang konsisten.
```

## 2. Gunakan Color Hex yang Spesifik
```
Jangan bilang "orange" — selalu tulis "#FF9B45"
Jangan bilang "dark brown" — tulis "#3E2B21"
Ini mencegah AI memilih warna yang berbeda dari design system.
```

## 3. Iterasi dengan Refinement Prompts
```
Setelah generate, jangan buat ulang dari nol. Gunakan chat follow-up:
"Change the table header background to #FF9B45 and make text white"
"Make the sidebar darker, use #2E1F17 as background"
"Add a 4px left border accent in #FF9B45 to each KPI card"
```

## 4. Referensi Komponen Sebelumnya
```
Ketika generate halaman baru, referensikan halaman sebelumnya:
"Use the same sidebar and top bar as the Beranda page"
"Apply the same table style as the Produk page (orange header, alternating rows)"
```

## 5. Spesifikasikan State Interaktif
```
Selalu sebutkan state yang dibutuhkan:
"Show hover state for nav items (bg rgba(255,155,69,0.15))"
"Include disabled state for submit button when form is invalid (bg #E0E0E0)"
"Show error state for input fields (border #EB5757, red helper text)"
```

## 6. Gunakan Bahasa Layout yang Jelas
```
"2-column layout, left 60%, right 40%"
"4 cards in a row, equal width"
"Full-width table with sticky header"
"Modal centered, max-width 560px, overlay backdrop blur"
```

## 7. Data Sample = Lebih Hidup
```
Selalu masukkan data sample di prompt:
"Show 3 sample kanban cards with customer names: Ibu Sari, Pak Budi, Toko Maju"
"Table with 4 sample products: Roti Tawar, Donat Coklat, Roti Sisir, Kue Bolu"
AI akan membuat UI yang terlihat nyata, bukan placeholder teks "Lorem Ipsum"
```

## 8. Badge & Status = Warna Spesifik
```
"Payment status badges:
Lunas = green pill (bg rgba(39,174,96,0.12), text #27AE60)
Hutang = amber pill (bg rgba(226,185,59,0.15), text #B8920A)
Belum Bayar = gray pill (bg #F5F5F5, text #4F4F4F)"
```

## 9. Thermal Receipt — Berikan Contoh Layout
```
"Design a thermal receipt (58mm wide) with:
Top: Berkat Dinasti logo text centered, address
Divider line ─────────────────
Order: date, customer name
Items: left-aligned name, right-aligned qty×price
Total row (bold)
Divider
Footer: terima kasih message"
```

## 10. Validasi & Locked State
```
Untuk minimum order lock, selalu tambahkan:
"Show validation state: when quantity is below minimum,
add red border to qty input, show inline warning box with lock icon,
and disable the submit button (bg #E0E0E0, text #828282, cursor not-allowed)"
```

---

## 📁 FILE STRUCTURE YANG DIREKOMENDASIKAN DI STITCH.AI

```
Project: Berkat Dinasti
├── Design Context
│   └── DESIGN.md ← UPLOAD INI PERTAMA
├── Pages
│   ├── 01-Login
│   ├── 02-Beranda
│   ├── 03-Produk
│   ├── 04-Pesanan-Kanban
│   ├── 04-Pesanan-Kalender
│   ├── 05-Pelanggan
│   ├── 06-Laporan
│   ├── 07-Pengaturan
│   └── 08-Logout-Modal
└── Components (generate terpisah kalau perlu)
    ├── Sidebar Navigation
    ├── Top Bar
    ├── KPI Card
    ├── Order Kanban Card
    └── Add Order Modal
```

---

*Dibuat untuk project Berkat Dinasti — UMKM Bakery Order Management System*  
*Versi: 1.0 | September 2025*
