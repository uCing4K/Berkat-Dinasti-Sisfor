# DESIGN SYSTEM — BERKAT DINASTI
> Design language guide for Stitch.ai prompt generation and UI development.  
> Bakery UMKM Order Management Application — Web Desktop First

---

## ⚠️ STRICT COLOR RULES — READ FIRST

**ONLY use these exact hex colors. Do NOT invent, add, or substitute any other colors.**

| Role | Hex | 
|------|-----|
| Primary accent | `#FF9B45` |
| Primary hover | `#B86B28` |
| Sidebar / dark bg | `#3E2B21` |
| Sidebar hover | `#5C3D2A` |
| Success | `#27AE60` |
| Warning | `#E2B93B` |
| Error / Danger | `#EB5757` |
| Info | `#2F80ED` |
| Page background | `#F9F7F5` |
| Card background | `#FFFFFF` |
| Primary text | `#1D1D1D` |
| Body text | `#333333` |
| Muted text | `#828282` |
| Border / divider | `#E0E0E0` |
| Alt table row | `#FFF8F2` |

**❌ DO NOT USE:**
- Any teal, cyan, or turquoise color (e.g. `#00C493` or similar)
- Any blue-gray neutral (e.g. `#617A8C` or similar)
- Any purple, green, or other colors not listed above
- Do not add a "Tertiary" color — this design system has NO tertiary color
- Do not auto-generate a color palette — use ONLY the exact hex codes listed above

---

## 1. BRAND IDENTITY

**App Name:** Berkat Dinasti  
**Domain:** UMKM Bakery Order Management System (Sistem Manajemen Pesanan Roti)  
**Visual Personality:** Warm, Professional, Trustworthy, Earthy  
**Theme:** Light theme with rich warm brown & vibrant orange accent  
**Target Device:** Desktop Web (1440px primary), responsive to 1024px tablet

---

## 2. COLOR PALETTE

### Brand Colors (Primary)
| Token | Hex | Usage |
|-------|-----|-------|
| `--color-primary` | `#FF9B45` | Primary buttons, active states, links, highlights, table headers, active nav |
| `--color-primary-hover` | `#B86B28` | Hover state for primary buttons |
| `--color-secondary` | `#3E2B21` | Sidebar background, dark headings, secondary CTAs |
| `--color-secondary-light` | `#5C3D2A` | Sidebar hover states, dark card backgrounds |

### Status Colors
| Token | Hex | Usage |
|-------|-----|-------|
| `--color-info` | `#2F80ED` | Info badges, info alerts, informational text |
| `--color-success` | `#27AE60` | Success badges, completed order status, paid status |
| `--color-warning` | `#E2B93B` | Warning alerts, pending status, below-minimum order warning |
| `--color-error` | `#EB5757` | Error states, validation errors, locked/blocked actions |

### Neutral Colors
| Token | Hex | Usage |
|-------|-----|-------|
| `--color-black-1` | `#000000` | Pure black (rarely used) |
| `--color-black-2` | `#1D1D1D` | Primary text, headings |
| `--color-black-3` | `#282828` | Secondary dark text |
| `--color-white` | `#FFFFFF` | Page backgrounds, card backgrounds, input backgrounds |
| `--color-gray-1` | `#333333` | Body text |
| `--color-gray-2` | `#4F4F4F` | Secondary body text |
| `--color-gray-3` | `#828282` | Placeholder text, disabled text, captions |
| `--color-gray-4` | `#BDBDBD` | Borders, dividers, disabled input borders |
| `--color-gray-5` | `#E0E0E0` | Disabled button fill, table alternating row, input borders |

### Background Colors
| Token | Hex | Usage |
|-------|-----|-------|
| `--color-bg-page` | `#F9F7F5` | Main page background (warm off-white) |
| `--color-bg-card` | `#FFFFFF` | Card and panel backgrounds |
| `--color-bg-sidebar` | `#3E2B21` | Sidebar navigation background |
| `--color-bg-table-header` | `#FF9B45` | Table column headers |
| `--color-bg-row-alt` | `#FFF8F2` | Alternating table rows |

---

## 3. TYPOGRAPHY

**Font Family:** `Inter` (Google Fonts)  

### Heading Scale
| Style | Size | Line Height | Weight | Usage |
|-------|------|-------------|--------|-------|
| H1 | 56px | 61.6px | 700 | Page hero titles only |
| H2 | 48px | 52.8px | 700 | Section major headings |
| H3 | 40px | 44.0px | 700 | Section sub-headings |
| H4 | 32px | 35.2px | 600 | Card titles, panel headings |
| H5 | 24px | 26.4px | 600 | Widget titles, modal headings |
| H6 | 20px | 22.0px | 600 | Sidebar section labels, table captions |

### Body Text Scale
| Style | Size | Line Height | Weight | Usage |
|-------|------|-------------|--------|-------|
| Large Bold | 20px | 28.0px | 700 | Important body callouts |
| Large Regular | 20px | 28.0px | 400 | Dashboard stat labels |
| Medium Bold | 18px | 25.2px | 700 | Table header text, button labels |
| Medium Regular | 18px | 25.2px | 400 | Form labels, card content |
| Normal Bold | 16px | 22.4px | 700 | Sidebar nav items (active) |
| Normal Regular | 16px | 22.4px | 400 | Default body, input text |
| Small Bold | 14px | 19.6px | 700 | Badge text, tag labels |
| Small Regular | 14px | 19.6px | 400 | Helper text, timestamps, captions |

---

## 4. SPACING SYSTEM

Base unit: `8px`

| Token | Value | Usage |
|-------|-------|-------|
| `--space-1` | `8px` | Tight internal padding, icon margin |
| `--space-2` | `16px` | Standard element gap, input padding vertical |
| `--space-3` | `24px` | Card inner padding, form group gap |
| `--space-4` | `32px` | Section gap, modal padding |
| `--space-5` | `40px` | Large section separators |
| `--space-6` | `56px` | Page section vertical spacing |
| `--space-7` | `72px` | Hero section padding |
| `--space-8` | `80px` | Large content block spacing |
| `--space-9` | `96px` | Page-level vertical margins |
| `--space-10` | `120px` | Maximum spacing for landing areas |

---

## 5. LAYOUT & GRID

### App Shell Layout
```
SIDEBAR (240px fixed)  |  MAIN CONTENT AREA
bg: #3E2B21            |  bg: #F9F7F5
─────────────────      |  ──────────────────────────
Logo (top 80px)        |  Top Bar (64px) 
Nav Items              |  Page Content
User Info (bottom)     |
```

### Grid Breakpoints
| Device | Width | Columns | Column Width | Gutter |
|--------|-------|---------|--------------|--------|
| Desktop HD | 1440px | 12 | 65px | 30px |
| Desktop | 1024px | 12 | 50px | 30px |
| Tablet | 768px | 6 | 88px | 30px |
| Mobile | 320px | 2 | 130px | 30px |

### Sidebar Specs
- **Width:** 240px (fixed, collapsible to 64px icon-only mode)
- **Background:** `#3E2B21`
- **Logo area height:** 80px
- **Nav item height:** 48px
- **Nav item padding:** 16px horizontal
- **Active nav:** bg `#FF9B45` rounded corners `8px`, text white
- **Hover nav:** bg `#5C3D2A`, text `#FF9B45`
- **Icon size:** 20px (outline style)

---

## 6. BORDER RADIUS

| Token | Value | Usage |
|-------|-------|-------|
| `--radius-sm` | `4px` | Badges, tags, small chips |
| `--radius-md` | `8px` | Buttons, input fields, nav items |
| `--radius-lg` | `12px` | Cards, modals, dropdowns |
| `--radius-xl` | `16px` | Large panels, modals |
| `--radius-full` | `9999px` | Toggle switches, pill badges |

---

## 7. ELEVATION / SHADOWS

| Level | CSS Value | Usage |
|-------|-----------|-------|
| None | `none` | Flat elements, table rows |
| Low | `0 1px 3px rgba(0,0,0,0.08)` | Subtle card lift |
| Medium | `0 4px 12px rgba(0,0,0,0.10)` | Cards, dropdowns |
| High | `0 8px 24px rgba(0,0,0,0.14)` | Modals, popovers |
| Overlay | `0 16px 48px rgba(0,0,0,0.20)` | Full-screen modals |

---

## 8. ICONOGRAPHY

**Library:** Lucide Icons (outline style preferred)  
**Sizes:**
- Sidebar icons: `20px`
- Button icons: `16px`
- Status icons: `16px`
- Empty state icons: `48px`

**Color rules:**
- Default: `#828282`
- Active / highlighted: `#FF9B45`
- On dark background: `#FFFFFF`

---

## 9. COMPONENTS

### 9.1 Buttons

#### Primary Button
```
Background: #FF9B45
Text: #FFFFFF, 16px, font-weight 600
Border-radius: 8px
Padding: 10px 32px
Hover: background #B86B28
Active: scale 0.98
Disabled: background #E0E0E0, text #828282, cursor not-allowed
```

#### Secondary Button (Outline)
```
Background: transparent
Border: 2px solid #FF9B45
Text: #FF9B45, 16px, font-weight 600
Border-radius: 8px
Padding: 10px 32px
Hover: background #FFF0E0
```

#### Danger Button
```
Background: #EB5757 | Text: #FFFFFF | Hover: #C94444
```

#### Button Sizes
| Size | Font | Padding H | Padding V |
|------|------|-----------|-----------|
| Small | 14px | 20px | 8px |
| Normal | 16px | 32px | 10px |
| Medium | 18px | 40px | 12px |
| Large | 20px | 48px | 14px |

---

### 9.2 Form Inputs

```
Background: #FFFFFF
Border: 1.5px solid #E0E0E0
Border-radius: 8px
Padding: 12px 16px
Font-size: 16px | Color: #1D1D1D | Placeholder: #828282

Focus: border-color #FF9B45, box-shadow 0 0 0 3px rgba(255,155,69,0.15)
Success: border-color #27AE60
Warning: border-color #E2B93B
Error: border-color #EB5757

Label: 14px, font-weight 600, color #333333, margin-bottom 6px
Helper text: 12px, color #828282
Error message: 12px, color #EB5757
```

#### Special Input Controls
- **Dropdown / Select:** Same as text input + caret icon on right
- **Date Picker:** Calendar popup, header `#FF9B45`, selected day `#FF9B45` filled circle
- **Toggle Switch:** ON = `#FF9B45`, OFF = `#E0E0E0`, pill `border-radius: 9999px`
- **Checkbox:** Custom styled, checked = `#FF9B45` fill + white checkmark
- **Radio:** Selected = `#FF9B45` inner dot

---

### 9.3 Cards

#### Standard Card
```
Background: #FFFFFF | Border-radius: 12px | Padding: 24px
Shadow: 0 4px 12px rgba(0,0,0,0.10)
```

#### KPI / Stat Card (Dashboard)
```
Background: #FFFFFF | Border-radius: 12px | Padding: 24px
Left accent border: 4px solid #FF9B45
Icon: 48px circle bg rgba(255,155,69,0.12), icon #FF9B45
Value: 32px, font-weight 700, color #1D1D1D
Label: 14px, font-weight 500, color #828282
```

#### Dark Accent Card
```
Background: #3E2B21 | Text: #FFFFFF | Accent: #FF9B45
```

---

### 9.4 Tables

```
Container: bg #FFFFFF, border-radius 12px, shadow medium

Header row:
  Background: #FF9B45 | Text: #FFFFFF, 14px, 700
  Padding: 12px 16px

Body rows:
  Odd: bg #FFFFFF | Even: bg #FFF8F2
  Padding: 12px 16px | Border-bottom: 1px solid #E0E0E0
  Text: #333333, 14px

Hover row: bg #FFF0E0

Pagination: right-aligned | Active page: #FF9B45 filled | Others: hover #FFF0E0
```

---

### 9.5 Badges & Tags

| Variant | Background | Text | Border |
|---------|-----------|------|--------|
| Primary | `#FF9B45` | `#FFFFFF` | — |
| Secondary | `#3E2B21` | `#FFFFFF` | — |
| Success | `rgba(39,174,96,0.12)` | `#27AE60` | 1px solid |
| Info | `rgba(47,128,237,0.12)` | `#2F80ED` | 1px solid |
| Warning | `rgba(226,185,59,0.15)` | `#B8920A` | 1px solid |
| Danger | `rgba(235,87,87,0.12)` | `#EB5757` | 1px solid |

```
Border-radius: 9999px | Padding: 4px 10px | Font: 12px, 600
```

#### Order Status Badges
| Status | Variant |
|--------|---------|
| Pending | Warning |
| Selesai | Success |
| Lunas | Success |
| Hutang | Danger |
| Belum Bayar | Warning |

---

### 9.6 Modals

```
Overlay: rgba(0,0,0,0.5) backdrop-blur 4px
Container: bg #FFFFFF, border-radius 16px, padding 32px
  Standard: max-width 560px | Large: max-width 720px
  Shadow: 0 16px 48px rgba(0,0,0,0.20)
Header: H5 title + × close button (top-right, color #828282)
Footer: right-aligned — [Cancel outline] [Confirm primary]
Animation: fade-in + translateY from 20px, 0.25s ease
```

---

### 9.7 Navigation Sidebar

```
Width: 240px | Background: #3E2B21

Logo area (top 80px): logo + "Berkat Dinasti", #FFFFFF, 18px, 700

Nav items (h: 48px, padding 0 16px):
  Icon 20px + label 16px, 500
  Default: text #D4B896
  Hover: bg rgba(255,155,69,0.15), text+icon #FF9B45
  Active: bg #FF9B45, text+icon #FFFFFF, border-radius 8px

Section labels: 12px, 700, #828282, UPPERCASE, letter-spacing 0.8px

User bottom: avatar 36px, name 14px #FFFFFF, role 12px #828282
Divider: 1px solid rgba(255,255,255,0.1)
```

---

### 9.8 Kanban Board

```
Board: display flex, gap 20px, overflow-x auto
Status columns (Pending, Selesai only):
  Width: 280px | bg: #F9F7F5 | border-radius: 12px | padding: 16px
  Column header: label bold + count badge

Kanban Card:
  bg: #FFFFFF | border-radius: 8px | padding: 16px
  Shadow: 0 2px 6px rgba(0,0,0,0.06)
  Left accent: 3px solid (by status color)
  Customer name: 14px, 700
  Items: 13px, #828282
  Deadline: 12px + calendar icon, red if overdue
  Payment badge: Lunas/Hutang/Belum Bayar pill
  Drag handle: dots icon top-right
  Dragging: opacity 0.6, rotate 2deg
```

---

### 9.9 Calendar

```
Container: #FFFFFF, border-radius 12px, shadow medium
Header: month/year + prev/next arrows in #FF9B45
Day headers: 14px, 600, #828282
Date cells: 36x36px, border-radius 50%
  Default hover: bg #FFF0E0
  Today: border 2px solid #FF9B45
  Has delivery: bg #FF9B45, text #FFFFFF
  Overdue: bg #EB5757, text #FFFFFF
```

---

### 9.10 Top Bar (Header)

```
Height: 64px | bg: #FFFFFF | border-bottom: 1px solid #E0E0E0
Left: page title (H5, 700) + breadcrumb
Right: notification bell + user avatar dropdown
```

---

## 10. MICRO-ANIMATIONS

```css
transition: all 0.2s ease;                          /* all interactive */
.btn:active { transform: scale(0.97); }             /* press feedback */
.card:hover { transform: translateY(-2px); }        /* card lift */
.nav-item { transition: background 0.15s ease; }    /* sidebar nav */
.kanban-card.dragging { opacity: 0.6; transform: rotate(2deg); }
```

---

## 11. BUSINESS RULES (Visual Enforcement)

### Minimum Order Lock
```
When below minimum quantity:
  - Input border: 2px solid #EB5757
  - Warning box: bg rgba(235,87,87,0.08), border-left 4px solid #EB5757
  - Message: "Pesanan di bawah minimum — tidak dapat dilanjutkan"
  - Submit: disabled, bg #E0E0E0, lock icon shown
```

### Payment Status Toggle
```
3-state toggle button group (NOT a number input):
  [Lunas]       → filled bg #27AE60, text #FFFFFF
  [Hutang]      → filled bg #E2B93B, text #1D1D1D
  [Belum Bayar] → filled bg #828282, text #FFFFFF
  Unselected: outline style, bg transparent
```

### Thermal Receipt Print
```
Button: "Cetak Struk" in order detail view
Width: 80mm thermal format
Font: monospace | Logo: B&W centered top
Divider: ─────── | Footer: alamat toko + terima kasih
```

---

## 12. PAGE INVENTORY

| # | Page | Route | Key Components |
|---|------|-------|---------------|
| 1 | Login | `/login` | Card form, logo, username/password, reset password link |
| 2 | Beranda | `/` | KPI cards (4), pending orders list, calendar mini, recent activity |
| 3 | Produk | `/produk` | Table list, add/edit modal, packaging variant badge |
| 4 | Pesanan | `/pesanan` | Kanban board (Pending/Selesai), calendar delivery, add order modal with inline customer add |
| 5 | Pelanggan | `/pelanggan` | Customer table, side detail panel with order history tabs |
| 6 | Laporan | `/laporan` | Revenue summary cards, bar/line chart, filterable order history table |
| 7 | Pengaturan | `/pengaturan` | Tabs: Profil UMKM / Manajemen User |
| 8 | Logout | action | Confirm dialog modal |

