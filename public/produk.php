<?php
session_start();

// Proteksi Autentikasi: Hanya user yang sudah login yang diizinkan mengakses halaman Produk
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Inisialisasi Database Connection (PDO) & Handler Backend
$db_connected = false;
$pdo = null;
$configFile = __DIR__ . "/../config/config.php";
if (file_exists($configFile)) {
    try {
        @require_once $configFile;
        if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER')) {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 2
            ]);
            $db_connected = true;
        }
    } catch (Exception $e) {
        $db_connected = false;
    }
}

// Helper Metadata Kemasan
function getBerkatPackagingMeta($nv, $isi = 1) {
    $nvLower = strtolower($nv);
    if (strpos($nvLower, 'mika') !== false) {
        return [
            'type' => 'Mika',
            'label' => ($isi > 1 && strpos($nvLower, 'pcs') === false) ? "Mika ({$isi} pcs)" : $nv,
            'badge' => 'bg-[#EBF3FC] text-[#2F80ED] border-[#D5E6F9]',
            'icon' => 'mika',
            'unit' => 'mika'
        ];
    } elseif (strpos($nvLower, 'batik') !== false) {
        return [
            'type' => 'Kardus Batik',
            'label' => ($isi > 1 && strpos($nvLower, 'pcs') === false) ? "Kardus Batik ({$isi} pcs)" : $nv,
            'badge' => 'bg-[#FFF0E6] text-[#924C00] border-[#FFD8BF]',
            'icon' => 'kardus',
            'unit' => 'box'
        ];
    } elseif (strpos($nvLower, 'coklat') !== false) {
        return [
            'type' => 'Kardus Coklat',
            'label' => ($isi > 1 && strpos($nvLower, 'pcs') === false) ? "Kardus Coklat ({$isi} pcs)" : $nv,
            'badge' => 'bg-[#FEF6EE] text-[#D97706] border-[#FDE6D2]',
            'icon' => 'kardus',
            'unit' => 'box'
        ];
    } elseif (strpos($nvLower, 'putih') !== false) {
        return [
            'type' => 'Kardus Putih',
            'label' => ($isi > 1 && strpos($nvLower, 'pcs') === false) ? "Kardus Putih ({$isi} pcs)" : $nv,
            'badge' => 'bg-[#F5F3F1] text-[#544337] border-[#EBE8E5]',
            'icon' => 'kardus',
            'unit' => 'box'
        ];
    } elseif (strpos($nvLower, 'kardus') !== false) {
        return [
            'type' => 'Kardus',
            'label' => ($isi > 1 && strpos($nvLower, 'pcs') === false) ? "Kardus ({$isi} pcs)" : $nv,
            'badge' => 'bg-[#FEF6EE] text-[#D97706] border-[#FDE6D2]',
            'icon' => 'kardus',
            'unit' => 'box'
        ];
    } else {
        return [
            'type' => 'Satuan',
            'label' => ($isi > 1 && strpos($nvLower, 'pcs') === false) ? "Satuan ({$isi} pcs)" : $nv,
            'badge' => 'bg-[#F5F3F1] text-[#544337] border-[#EBE8E5]',
            'icon' => 'satuan',
            'unit' => 'pcs'
        ];
    }
}

// Daftar Produk Resmi Sesuai Pricelist Aktual Berkat Dinasti (Dynasty Food & Beverage)
$initial_products = [
    [
        'id' => 1,
        'sku' => 'BD-RH-01',
        'name' => 'Roti Hajatan (9 Pilihan Topping)',
        'category' => 'Roti Hajatan',
        'packaging_type' => 'Kardus Putih',
        'packaging_label' => 'Kardus Putih',
        'packaging_badge_class' => 'bg-[#F5F3F1] text-[#544337] border-[#EBE8E5]',
        'packaging_icon' => 'kardus',
        'min_order' => '1 box',
        'moq_num' => 1,
        'isi' => 1,
        'price' => 10000,
        'price_unit' => 'box',
        'status' => 'Aktif',
        'description' => 'Roti hajatan bulat 7 kelopak lembut dengan 9 topping pilihan (Coco Chip, Keju, Meses, Wijen, Sprinkle) dalam kemasan Kardus Putih bersih.'
    ],
    [
        'id' => 2,
        'sku' => 'BD-RH-02',
        'name' => 'Roti Hajatan (9 Pilihan Topping)',
        'category' => 'Roti Hajatan',
        'packaging_type' => 'Kardus Coklat',
        'packaging_label' => 'Kardus Coklat',
        'packaging_badge_class' => 'bg-[#FEF6EE] text-[#D97706] border-[#FDE6D2]',
        'packaging_icon' => 'kardus',
        'min_order' => '1 box',
        'moq_num' => 1,
        'isi' => 1,
        'price' => 10000,
        'price_unit' => 'box',
        'status' => 'Aktif',
        'description' => 'Roti hajatan bulat 7 kelopak lembut kemasan Kardus Coklat kraft elegan dengan 9 topping pilihan.'
    ],
    [
        'id' => 3,
        'sku' => 'BD-RH-03',
        'name' => 'Roti Hajatan (9 Pilihan Topping)',
        'category' => 'Roti Hajatan',
        'packaging_type' => 'Kardus Batik',
        'packaging_label' => 'Kardus Batik',
        'packaging_badge_class' => 'bg-[#FFF0E6] text-[#924C00] border-[#FFD8BF]',
        'packaging_icon' => 'kardus',
        'min_order' => '1 box',
        'moq_num' => 1,
        'isi' => 1,
        'price' => 11000,
        'price_unit' => 'box',
        'status' => 'Aktif',
        'description' => 'Roti hajatan bulat 7 kelopak kemasan Kardus Batik premium cantik cocok untuk hantaran & syukuran.'
    ],
    [
        'id' => 4,
        'sku' => 'BD-RH-04',
        'name' => 'Roti Hajatan (9 Pilihan Topping)',
        'category' => 'Roti Hajatan',
        'packaging_type' => 'Mika',
        'packaging_label' => 'Mika',
        'packaging_badge_class' => 'bg-[#EBF3FC] text-[#2F80ED] border-[#D5E6F9]',
        'packaging_icon' => 'mika',
        'min_order' => '1 mika',
        'moq_num' => 1,
        'isi' => 1,
        'price' => 11000,
        'price_unit' => 'mika',
        'status' => 'Aktif',
        'description' => 'Roti hajatan bulat 7 kelopak dalam wadah Mika bening transparan higienis.'
    ],
    [
        'id' => 5,
        'sku' => 'BD-BP-01',
        'name' => 'Bolu Pisang Premium',
        'category' => 'Bolu Premium',
        'packaging_type' => 'Kardus Coklat',
        'packaging_label' => 'Kardus Coklat',
        'packaging_badge_class' => 'bg-[#FEF6EE] text-[#D97706] border-[#FDE6D2]',
        'packaging_icon' => 'kardus',
        'min_order' => '1 box',
        'moq_num' => 1,
        'isi' => 1,
        'price' => 21000,
        'price_unit' => 'box',
        'status' => 'Aktif',
        'description' => 'Bolu pisang premium moist beraroma harum pisang asli Dynasty Food & Beverage dalam kemasan Kardus Coklat.'
    ],
    [
        'id' => 6,
        'sku' => 'BD-BP-02',
        'name' => 'Bolu Pisang Premium',
        'category' => 'Bolu Premium',
        'packaging_type' => 'Mika',
        'packaging_label' => 'Mika',
        'packaging_badge_class' => 'bg-[#EBF3FC] text-[#2F80ED] border-[#D5E6F9]',
        'packaging_icon' => 'mika',
        'min_order' => '1 mika',
        'moq_num' => 1,
        'isi' => 1,
        'price' => 22000,
        'price_unit' => 'mika',
        'status' => 'Aktif',
        'description' => 'Bolu pisang premium moist dalam kemasan Mika bulat transparan eksklusif.'
    ],
    [
        'id' => 7,
        'sku' => 'BD-BT-01',
        'name' => 'Bolu Tanpa Pisang Premium',
        'category' => 'Bolu Premium',
        'packaging_type' => 'Kardus Putih',
        'packaging_label' => 'Kardus Putih',
        'packaging_badge_class' => 'bg-[#F5F3F1] text-[#544337] border-[#EBE8E5]',
        'packaging_icon' => 'kardus',
        'min_order' => '1 box',
        'moq_num' => 1,
        'isi' => 1,
        'price' => 19000,
        'price_unit' => 'box',
        'status' => 'Aktif',
        'description' => 'Bolu panggang klasik premium tanpa pisang, harum mentega dan lembut dalam kemasan Kardus Putih.'
    ],
    [
        'id' => 8,
        'sku' => 'BD-BT-02',
        'name' => 'Bolu Tanpa Pisang Premium',
        'category' => 'Bolu Premium',
        'packaging_type' => 'Mika',
        'packaging_label' => 'Mika',
        'packaging_badge_class' => 'bg-[#EBF3FC] text-[#2F80ED] border-[#D5E6F9]',
        'packaging_icon' => 'mika',
        'min_order' => '1 mika',
        'moq_num' => 1,
        'isi' => 1,
        'price' => 20000,
        'price_unit' => 'mika',
        'status' => 'Aktif',
        'description' => 'Bolu panggang klasik premium tanpa pisang dalam wadah Mika bulat transparan.'
    ],
    [
        'id' => 9,
        'sku' => 'BD-RM-01',
        'name' => 'Roti Kopi',
        'category' => 'Roti Manis',
        'packaging_type' => 'Satuan',
        'packaging_label' => 'Satuan (Pcs)',
        'packaging_badge_class' => 'bg-[#F5F3F1] text-[#544337] border-[#EBE8E5]',
        'packaging_icon' => 'satuan',
        'min_order' => '50 pcs',
        'moq_num' => 50,
        'isi' => 1,
        'price' => 4000,
        'price_unit' => 'pcs',
        'status' => 'Aktif',
        'description' => 'Roti manis dengan crust aroma kopi harum semerbak ala coffee bun.'
    ],
    [
        'id' => 10,
        'sku' => 'BD-RM-02',
        'name' => 'Roti Bijian Besar',
        'category' => 'Roti Manis',
        'packaging_type' => 'Satuan',
        'packaging_label' => 'Satuan (Pcs)',
        'packaging_badge_class' => 'bg-[#F5F3F1] text-[#544337] border-[#EBE8E5]',
        'packaging_icon' => 'satuan',
        'min_order' => '20 pcs',
        'moq_num' => 20,
        'isi' => 1,
        'price' => 4000,
        'price_unit' => 'pcs',
        'status' => 'Aktif',
        'description' => 'Roti manis bijian ukuran besar, empuk dan mengenyangkan.'
    ],
    [
        'id' => 11,
        'sku' => 'BD-RM-03',
        'name' => 'Roti Bijian Kecil',
        'category' => 'Roti Manis',
        'packaging_type' => 'Satuan',
        'packaging_label' => 'Satuan (Pcs)',
        'packaging_badge_class' => 'bg-[#F5F3F1] text-[#544337] border-[#EBE8E5]',
        'packaging_icon' => 'satuan',
        'min_order' => '50 pcs',
        'moq_num' => 50,
        'isi' => 1,
        'price' => 3000,
        'price_unit' => 'pcs',
        'status' => 'Aktif',
        'description' => 'Roti manis bijian ukuran kecil praktis untuk snack dan konsumsi harian.'
    ]
];

// Inisialisasi ke session dengan versioning agar selalu sinkron
$catalog_version = '2026-10-26_berkat_dinasti_pricelist_v3';
if (!isset($_SESSION['product_list']) || !isset($_SESSION['catalog_ver']) || $_SESSION['catalog_ver'] !== $catalog_version) {
    $_SESSION['product_list'] = $initial_products;
    $_SESSION['catalog_ver'] = $catalog_version;
}

// -------------------------------------------------------------
// 1. BACKEND HANDLER: EXPORT KATALOG PRODUK KE CSV / EXCEL
// -------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    $filename = "katalog-produk-berkat-dinasti-" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Output UTF-8 BOM for Microsoft Excel compatibility
    echo "\xEF\xBB\xBF";
    $output = fopen('php://output', 'w');
    fputcsv($output, ['No', 'SKU', 'Nama Produk', 'Kategori', 'Tipe Kemasan', 'Label Kemasan', 'Minimal Order (MOQ)', 'Harga Jual (Rp)', 'Status', 'Deskripsi']);
    
    $exportList = $_SESSION['product_list'];
    foreach ($exportList as $idx => $p) {
        fputcsv($output, [
            $idx + 1,
            $p['sku'],
            $p['name'],
            $p['category'],
            $p['packaging_type'],
            $p['packaging_label'],
            $p['min_order'],
            'Rp ' . number_format($p['price'], 0, ',', '.'),
            $p['status'],
            $p['description'] ?? ''
        ]);
    }
    
    fclose($output);
    exit();
}

// -------------------------------------------------------------
// 2. BACKEND HANDLER: TAMBAH PRODUK BARU
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $nama = trim($_POST['nama_produk'] ?? '');
    $kategori = trim($_POST['kategori'] ?? 'Roti');
    $harga_raw = str_replace(['.', ',', ' '], '', $_POST['harga'] ?? '0');
    $harga = (float)$harga_raw;
    $kemasan = trim($_POST['kemasan'] ?? 'Mika');
    $isi = (int)($_POST['isi'] ?? 1);
    $moq = (int)($_POST['moq'] ?? 1);
    $status = isset($_POST['status']) && $_POST['status'] === 'on' ? 'Aktif' : 'Nonaktif';
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    $packMeta = getBerkatPackagingMeta($kemasan, $isi);
    $packaging_label = $packMeta['label'];
    $packaging_badge_class = $packMeta['badge'];
    $packaging_icon = $packMeta['icon'];
    $unit = $packMeta['unit'];

    $skuPrefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $kategori), 0, 2));
    if (empty($skuPrefix)) $skuPrefix = 'BD';
    $newId = count($_SESSION['product_list']) + 1;
    $sku = 'BD-' . $skuPrefix . '-' . sprintf('%02d', $newId);

    // Sync ke DB jika terkoneksi
    if ($db_connected && $pdo) {
        try {
            $stmtK = $pdo->prepare("SELECT id_kategori FROM kategori WHERE nama_kategori = ? LIMIT 1");
            $stmtK->execute([$kategori]);
            $katId = $stmtK->fetchColumn();
            if (!$katId) {
                $stmtNewK = $pdo->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
                $stmtNewK->execute([$kategori]);
                $katId = $pdo->lastInsertId();
            }

            $statusDb = ($status === 'Aktif') ? 'tersedia' : 'tidak_tersedia';
            $stmtProd = $pdo->prepare("INSERT INTO produk (id_kategori, nama_produk, deskripsi, status) VALUES (?, ?, ?, ?)");
            $stmtProd->execute([$katId, $nama, $deskripsi, $statusDb]);
            $dbProdId = $pdo->lastInsertId();
            $newId = $dbProdId;
            $sku = 'BD-' . $skuPrefix . '-' . sprintf('%02d', $dbProdId);

            $stmtVar = $pdo->prepare("INSERT INTO varian_produk (id_produk, nama_varian, harga, min_order) VALUES (?, ?, ?, ?)");
            $stmtVar->execute([$dbProdId, $packaging_label, $harga, $moq]);
        } catch (Exception $e) {}
    }

    $new_item = [
        'id' => $newId,
        'sku' => $sku,
        'name' => $nama,
        'category' => $kategori,
        'packaging_type' => $kemasan,
        'packaging_label' => $packaging_label,
        'packaging_badge_class' => $packaging_badge_class,
        'packaging_icon' => $packaging_icon,
        'min_order' => $moq . ' ' . $unit,
        'moq_num' => $moq,
        'isi' => $isi,
        'price' => $harga,
        'price_unit' => $unit,
        'status' => $status,
        'description' => $deskripsi
    ];

    array_unshift($_SESSION['product_list'], $new_item);
    $_SESSION['toast_msg'] = 'Produk "' . htmlspecialchars($nama) . '" berhasil ditambahkan ke katalog!';
    header("Location: produk.php");
    exit();
}

// -------------------------------------------------------------
// 3. BACKEND HANDLER: EDIT / UPDATE PRODUK
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_product') {
    $prod_id = intval($_POST['product_id'] ?? 0);
    $nama = trim($_POST['nama_produk'] ?? '');
    $kategori = trim($_POST['kategori'] ?? 'Roti');
    $harga_raw = str_replace(['.', ',', ' '], '', $_POST['harga'] ?? '0');
    $harga = (float)$harga_raw;
    $kemasan = trim($_POST['kemasan'] ?? 'Mika');
    $isi = (int)($_POST['isi'] ?? 1);
    $moq = (int)($_POST['moq'] ?? 1);
    $status = isset($_POST['status']) && $_POST['status'] === 'on' ? 'Aktif' : 'Nonaktif';
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    $packMeta = getBerkatPackagingMeta($kemasan, $isi);
    $packaging_label = $packMeta['label'];
    $packaging_badge_class = $packMeta['badge'];
    $packaging_icon = $packMeta['icon'];
    $unit = $packMeta['unit'];

    // Sync ke DB
    if ($db_connected && $pdo && $prod_id > 0) {
        try {
            $statusDb = ($status === 'Aktif') ? 'tersedia' : 'tidak_tersedia';
            $stmtUp = $pdo->prepare("UPDATE produk SET nama_produk = ?, deskripsi = ?, status = ? WHERE id_produk = ?");
            $stmtUp->execute([$nama, $deskripsi, $statusDb, $prod_id]);

            $stmtUpV = $pdo->prepare("UPDATE varian_produk SET nama_varian = ?, harga = ?, min_order = ? WHERE id_produk = ?");
            $stmtUpV->execute([$packaging_label, $harga, $moq, $prod_id]);
        } catch (Exception $e) {}
    }

    // Update di session
    foreach ($_SESSION['product_list'] as &$item) {
        if ($item['id'] == $prod_id) {
            $item['name'] = $nama;
            $item['category'] = $kategori;
            $item['packaging_type'] = $kemasan;
            $item['packaging_label'] = $packaging_label;
            $item['packaging_badge_class'] = $packaging_badge_class;
            $item['packaging_icon'] = $packaging_icon;
            $item['min_order'] = $moq . ' ' . $unit;
            $item['moq_num'] = $moq;
            $item['isi'] = $isi;
            $item['price'] = $harga;
            $item['price_unit'] = $unit;
            $item['status'] = $status;
            $item['description'] = $deskripsi;
            break;
        }
    }

    $_SESSION['toast_msg'] = 'Perubahan data produk "' . htmlspecialchars($nama) . '" berhasil disimpan!';
    header("Location: produk.php");
    exit();
}

// -------------------------------------------------------------
// 4. BACKEND HANDLER: HAPUS PRODUK
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_product') {
    $prod_id = intval($_POST['product_id'] ?? 0);
    $deleted_name = 'Produk';

    // Sync ke DB
    if ($db_connected && $pdo && $prod_id > 0) {
        try {
            $stmtDelV = $pdo->prepare("DELETE FROM varian_produk WHERE id_produk = ?");
            $stmtDelV->execute([$prod_id]);
            $stmtDelP = $pdo->prepare("DELETE FROM produk WHERE id_produk = ?");
            $stmtDelP->execute([$prod_id]);
        } catch (Exception $e) {}
    }

    // Hapus dari session
    $_SESSION['product_list'] = array_values(array_filter($_SESSION['product_list'], function($item) use ($prod_id, &$deleted_name) {
        if ($item['id'] == $prod_id) {
            $deleted_name = $item['name'];
            return false;
        }
        return true;
    }));

    $_SESSION['toast_msg'] = 'Produk "' . htmlspecialchars($deleted_name) . '" berhasil dihapus dari katalog!';
    header("Location: produk.php");
    exit();
}

// -------------------------------------------------------------
// 5. BACKEND HANDLER: QUICK TOGGLE STATUS PRODUK
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    $prod_id = intval($_POST['product_id'] ?? 0);
    $new_status_label = 'Aktif';

    foreach ($_SESSION['product_list'] as &$item) {
        if ($item['id'] == $prod_id) {
            $item['status'] = ($item['status'] === 'Aktif') ? 'Nonaktif' : 'Aktif';
            $new_status_label = $item['status'];
            break;
        }
    }

    if ($db_connected && $pdo && $prod_id > 0) {
        try {
            $statusDb = ($new_status_label === 'Aktif') ? 'tersedia' : 'tidak_tersedia';
            $stmtTog = $pdo->prepare("UPDATE produk SET status = ? WHERE id_produk = ?");
            $stmtTog->execute([$statusDb, $prod_id]);
        } catch (Exception $e) {}
    }

    $_SESSION['toast_msg'] = 'Status produk berhasil diubah menjadi ' . $new_status_label . '!';
    header("Location: produk.php");
    exit();
}

// -------------------------------------------------------------
// 6. SINKRONISASI DATA DARI DATABASE (JIKA TERSEDIA)
// -------------------------------------------------------------
$products = $_SESSION['product_list'];
if ($db_connected && $pdo) {
    try {
        // Auto-Sync: Pastikan produk resmi Berkat Dinasti tersimpan di MySQL
        $chkSync = $pdo->query("SELECT COUNT(*) FROM produk WHERE nama_produk LIKE '%Bolu%'")->fetchColumn();
        if ($chkSync == 0) {
            // 1. Kategori
            $cats = [
                1 => ['Roti Hajatan', 'Roti untuk acara hajatan, syukuran, khitanan, dan acara spesial'],
                2 => ['Bolu Premium', 'Bolu premium lembut aneka rasa khas Berkat Dinasti'],
                3 => ['Roti Manis', 'Berbagai macam roti manis bijian untuk konsumsi harian']
            ];
            foreach ($cats as $cId => $cData) {
                $st = $pdo->prepare("INSERT INTO kategori (id_kategori, nama_kategori, deskripsi) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE nama_kategori = VALUES(nama_kategori), deskripsi = VALUES(deskripsi)");
                $st->execute([$cId, $cData[0], $cData[1]]);
            }

            // 2. Master Produk
            $prods = [
                1 => [1, 'Roti Hajatan (9 Pilihan Topping)', 'Roti hajatan bulat 7 kelopak lembut dengan 9 aneka topping (Coco Chip, Keju, Meses, Wijen, Sprinkle)', 7],
                2 => [2, 'Bolu Pisang Premium', 'Bolu pisang premium bertekstur moist dengan aroma pisang harum khas Berkat Dinasti', 7],
                3 => [2, 'Bolu Tanpa Pisang Premium', 'Bolu panggang klasik premium tanpa pisang, harum mentega lembut dan gurih manis pas', 7],
                4 => [3, 'Roti Kopi', 'Roti manis dengan crust aroma kopi harum semerbak ala coffee bun', 7],
                5 => [3, 'Roti Bijian Besar', 'Roti manis bijian ukuran besar, empuk dan mengenyangkan', 7],
                6 => [3, 'Roti Bijian Kecil', 'Roti manis bijian ukuran kecil praktis untuk snack harian', 7]
            ];
            foreach ($prods as $pId => $pData) {
                $st = $pdo->prepare("INSERT INTO produk (id_produk, id_kategori, nama_produk, deskripsi, shelf_life, status) VALUES (?, ?, ?, ?, ?, 'tersedia') ON DUPLICATE KEY UPDATE id_kategori = VALUES(id_kategori), nama_produk = VALUES(nama_produk), deskripsi = VALUES(deskripsi), status = 'tersedia'");
                $st->execute([$pId, $pData[0], $pData[1], $pData[2], $pData[3]]);
            }

            // 3. Varian Kemasan & Harga
            $vars = [
                1  => [1, 'Kardus Putih', 10000.00, 1],
                2  => [1, 'Kardus Coklat', 10000.00, 1],
                3  => [1, 'Kardus Batik', 11000.00, 1],
                4  => [1, 'Mika', 11000.00, 1],
                5  => [6, 'Satuan', 3000.00, 50],
                6  => [2, 'Kardus Coklat', 21000.00, 1],
                7  => [2, 'Mika', 22000.00, 1],
                8  => [3, 'Kardus Putih', 19000.00, 1],
                9  => [3, 'Mika', 20000.00, 1],
                10 => [4, 'Satuan', 4000.00, 50],
                11 => [5, 'Satuan', 4000.00, 20]
            ];
            foreach ($vars as $vId => $vData) {
                $st = $pdo->prepare("INSERT INTO varian_produk (id_varian, id_produk, nama_varian, harga, min_order, stok) VALUES (?, ?, ?, ?, ?, 100) ON DUPLICATE KEY UPDATE id_produk = VALUES(id_produk), nama_varian = VALUES(nama_varian), harga = VALUES(harga), min_order = VALUES(min_order)");
                $st->execute([$vId, $vData[0], $vData[1], $vData[2], $vData[3]]);
            }
        }

        $stmtLoad = $pdo->query("
            SELECT p.id_produk as id, p.nama_produk as name, p.deskripsi as description,
                   p.status as p_status, k.nama_kategori as category,
                   vp.id_varian, vp.nama_varian, vp.harga as price, vp.min_order
            FROM produk p
            LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
            LEFT JOIN varian_produk vp ON p.id_produk = vp.id_produk
            ORDER BY k.id_kategori ASC, p.id_produk ASC, vp.harga ASC
        ");
        $dbRows = $stmtLoad->fetchAll();
        if (!empty($dbRows)) {
            $loadedProducts = [];
            foreach ($dbRows as $dp) {
                $cat = !empty($dp['category']) ? $dp['category'] : 'Roti Hajatan';
                $st = ($dp['p_status'] === 'tersedia') ? 'Aktif' : 'Nonaktif';
                $nv = !empty($dp['nama_varian']) ? $dp['nama_varian'] : 'Satuan';
                
                $isi = 1;
                if (preg_match('/\((\d+)\s*pcs\)/i', $nv, $m)) $isi = (int)$m[1];
                $packMeta = getBerkatPackagingMeta($nv, $isi);

                $skuPrefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $cat), 0, 2));
                if (empty($skuPrefix)) $skuPrefix = 'BD';
                $rowId = $dp['id_varian'] ?: $dp['id'];
                $sku = 'BD-' . $skuPrefix . '-' . sprintf('%02d', $rowId);

                $loadedProducts[] = [
                    'id' => $rowId,
                    'sku' => $sku,
                    'name' => $dp['name'],
                    'category' => $cat,
                    'packaging_type' => $packMeta['type'],
                    'packaging_label' => $packMeta['label'],
                    'packaging_badge_class' => $packMeta['badge'],
                    'packaging_icon' => $packMeta['icon'],
                    'min_order' => ($dp['min_order'] ?? 1) . ' ' . $packMeta['unit'],
                    'moq_num' => (int)($dp['min_order'] ?? 1),
                    'isi' => $isi,
                    'price' => (float)$dp['price'],
                    'price_unit' => $packMeta['unit'],
                    'status' => $st,
                    'description' => $dp['description'] ?? ''
                ];
            }
            $products = $loadedProducts;
            $_SESSION['product_list'] = $products;
        }
    } catch (Exception $e) {}
}

// Flash Toast Message
$toast_message = '';
if (isset($_SESSION['toast_msg'])) {
    $toast_message = $_SESSION['toast_msg'];
    unset($_SESSION['toast_msg']);
}

// Hitung data ringkasan KPI
$total_katalog = count($products);
$produk_aktif = count(array_filter($products, fn($p) => $p['status'] === 'Aktif'));
$varian_kemasan = count(array_unique(array_column($products, 'packaging_type')));

// Dynamic Indonesian date helper
$hariArr = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
$bulanArr = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
$todayFormatted = $hariArr[date('l')] . ', ' . date('d') . ' ' . $bulanArr[(int)date('n')] . ' ' . date('Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk - Berkat Dinasti</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome 5 Free CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Modern Craft Design System -->
    <link rel="stylesheet" href="css/modern-craft.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            brown: '#3E2B21',
                            brownDark: '#2C1E17',
                            orange: '#D46E15',
                            orangeHover: '#B85207',
                            lightText: '#E8D7CD',
                            bg: '#F5F1EB',
                            surface: '#FDFBF7',
                            border: '#E2D7C8',
                            muted: '#8E7E73',
                            dark: '#1C1C1C',
                            darkText: '#1B1C1B',
                            brownText: '#544337',
                            brownDeep: '#924C00',
                            accentGreen: '#27AE60',
                            accentYellow: '#E2B93B',
                            accentBlue: '#2F80ED',
                            accentRed: '#EB5757'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    boxShadow: {
                        'card': '0px 4px 12px rgba(0, 0, 0, 0.06)',
                        'button-orange': '0px 2px 4px rgba(212, 110, 21, 0.25)',
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F5F1EB;
            color: #251A14;
        }
    </style>
</head>
<body class="min-h-screen bg-[#F5F1EB] flex flex-col antialiased selection:bg-[#D46E15] selection:text-white">

    <!-- OVERALL WRAPPER -->
    <div class="flex flex-1 w-full relative">

        <!-- MOBILE SIDEBAR OVERLAY -->
        <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/40 z-40 lg:hidden hidden transition-opacity duration-300"></div>

        <!-- ASIDE / SIDEBAR (Width 240px) -->
        <aside id="main-sidebar" class="fixed top-0 bottom-0 left-0 w-[240px] bg-[#3E2B21] border-r border-[#2C1E17] z-50 flex flex-col justify-between -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out shadow-xl lg:shadow-none">
            
            <!-- Top Logo & Navigation -->
            <div class="flex flex-col flex-1 overflow-y-auto">
                
                <!-- Logo & Brand Header (Height 64px - Aligned with Topbar) -->
                <div class="h-16 px-4 flex items-center gap-3 border-b border-white/10 shrink-0">
                    <div class="w-8 h-8 rounded-full bg-white p-0.5 flex items-center justify-center shrink-0 shadow-sm overflow-hidden">
                        <img src="assets/images/logo.png" alt="Berkat Dinasti Logo" class="w-full h-full object-contain">
                    </div>
                    <span class="text-white font-bold text-lg tracking-tight">Berkat Dinasti</span>
                    
                    <!-- Mobile Close Button -->
                    <button onclick="toggleSidebar()" class="ml-auto text-white/70 hover:text-white lg:hidden p-1">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <!-- Navigation List -->
                <nav class="p-4 space-y-1.5 flex-1">
                    <!-- Beranda -->
                    <a href="beranda.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <i class="fas fa-th-large w-5 text-center text-sm opacity-90"></i>
                        <span>Beranda</span>
                    </a>

                    <!-- Produk (Active) -->
                    <a href="produk.php" class="bg-[#D46E15] text-white rounded-lg px-4 py-3 flex items-center gap-3 font-semibold text-sm transition-all shadow-sm">
                        <i class="fas fa-box-open w-5 text-center text-sm"></i>
                        <span>Produk</span>
                    </a>

                    <!-- Pesanan -->
                    <a href="pesanan.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <i class="fas fa-shopping-bag w-5 text-center text-sm opacity-90"></i>
                        <span>Pesanan</span>
                    </a>

                    <!-- Pelanggan -->
                    <a href="pelanggan.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <i class="fas fa-users w-5 text-center text-sm opacity-90"></i>
                        <span>Pelanggan</span>
                    </a>

                    <!-- Laporan -->
                    <a href="laporan.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <i class="fas fa-chart-line w-5 text-center text-sm opacity-90"></i>
                        <span>Laporan</span>
                    </a>

                    <!-- Pengaturan -->
                    <a href="pengaturan.php" class="text-white/80 hover:text-white hover:bg-white/10 rounded-lg px-4 py-3 flex items-center gap-3 font-medium text-sm transition-colors">
                        <i class="fas fa-cog w-5 text-center text-sm opacity-90"></i>
                        <span>Pengaturan</span>
                    </a>
                </nav>
            </div>

            <!-- Bottom User Profile Card -->
            <div class="p-4 border-t border-white/10 shrink-0">
                <div class="bg-black/25 border border-white/5 rounded-xl p-2.5 flex items-center justify-between">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-9 h-9 rounded-full bg-[#D46E15] text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-sm">
                            BD
                        </div>
                        <div class="truncate">
                            <p class="text-white font-semibold text-sm leading-tight truncate"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['user'] ?? 'Admin') ?></p>
                            <p class="text-[#D8C7B8] text-xs leading-tight mt-0.5"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Admin')) ?></p>
                        </div>
                    </div>
                    <button type="button" onclick="openLogoutModal()" title="Keluar / Logout" class="p-1.5 text-white/70 hover:text-white transition-colors shrink-0">
                        <i class="fas fa-sign-out-alt text-base"></i>
                    </button>
                </div>
            </div>
        </aside>

        <!-- MAIN LAYOUT WRAPPER (Offset 240px on lg screens) -->
        <div class="flex-1 flex flex-col min-w-0 lg:ml-[240px]">

            <!-- TOP HEADER (Height 64px) -->
            <header class="h-16 bg-[#FDFBF7]/95 backdrop-blur-md border-b border-[#E2D7C8] sticky top-0 z-30 px-4 md:px-8 flex items-center justify-between">
                
                <!-- Left: Hamburger (Mobile) + Breadcrumb & Title -->
                <div class="flex items-center gap-3 md:gap-4">
                    <button onclick="toggleSidebar()" class="lg:hidden p-1.5 text-[#3E2B21] hover:bg-[#EFE8DE] rounded-lg">
                        <i class="fas fa-bars text-lg text-[#3E2B21]"></i>
                    </button>

                    <div>
                        <!-- Breadcrumbs -->
                        <div class="flex items-center gap-1.5 text-xs">
                            <a href="produk.php" class="text-[#8E7E73] hover:underline">Produk</a>
                            <span class="text-[#D8C7B8]">/</span>
                            <span class="text-[#3E2B21] font-semibold">Daftar Produk</span>
                        </div>
                        <!-- Section Heading -->
                        <h1 class="text-[#251A14] font-bold text-base md:text-lg leading-tight mt-0.5">Daftar Produk</h1>
                    </div>
                </div>

                <!-- Right: Date Indicator, Notification Bell, User Avatar -->
                <div class="flex items-center gap-3 md:gap-5">
                    
                    <!-- Date Button -->
                    <div class="hidden sm:flex items-center gap-2 bg-[#EFE8DE] text-[#3E2B21] px-3.5 py-1.5 rounded-lg text-xs md:text-sm font-semibold border border-[#DDD3C5]">
                        <i class="far fa-calendar-alt text-[#D46E15] text-xs"></i>
                        <span><?= $todayFormatted ?></span>
                    </div>

                    <!-- Notification Bell with Popover -->
                    <div class="relative">
                        <button onclick="toggleNotifPopover()" class="relative p-2 text-[#544337] hover:bg-[#F5F3F1] rounded-lg transition-colors cursor-pointer" title="Notifikasi" id="notificationBtn">
                            <i class="far fa-bell text-[#3E2B21] text-base"></i>
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-[#D46E15] rounded-full ring-2 ring-white"></span>
                        </button>
                        <div id="notifPopover" class="hidden absolute right-0 mt-2.5 w-80 craft-dropdown py-2 z-50">
                            <div class="px-4 py-2 border-b border-[#ECE5DD] flex items-center justify-between">
                                <span class="font-semibold text-xs text-[#251A14]">Notifikasi Sistem</span>
                                <span class="text-[10px] craft-badge-warning font-medium px-2 py-0.5 rounded-full">2 Baru</span>
                            </div>
                            <div class="divide-y divide-[#F0ECE9]/70 max-h-60 overflow-y-auto py-1">
                                <div class="px-3.5 py-2.5 hover:bg-[#F8F5F1] transition-colors cursor-pointer rounded-lg mx-1">
                                    <p class="text-xs font-medium text-[#251A14]">Pembaruan Stok Katalog</p>
                                    <p class="text-[11px] text-[#8E7E73] mt-0.5">Semua data varian kemasan produk sinkron dengan dapur.</p>
                                </div>
                                <div class="px-3.5 py-2.5 hover:bg-[#F8F5F1] transition-colors cursor-pointer rounded-lg mx-1">
                                    <p class="text-xs font-medium text-[#251A14]">Kapasitas Armada Kurir</p>
                                    <p class="text-[11px] text-[#8E7E73] mt-0.5">Batas 150 pcs/trip obrok motor aktif untuk rute Malang & Batu.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Circle Icon with Dropdown -->
                    <div class="relative">
                        <div onclick="toggleUserDropdown()" class="w-8 h-8 rounded-full bg-[#3E2B21] border border-white/20 flex items-center justify-center text-white shadow-xs cursor-pointer hover:opacity-95 transition-all active:scale-95" title="Profil Pengguna" id="userAvatarBtn">
                            <i class="fas fa-user text-xs text-[#FAF8F5]"></i>
                        </div>
                        <div id="userDropdown" class="hidden absolute right-0 mt-2.5 w-56 craft-dropdown py-1.5 z-50 text-xs">
                            <div class="px-4 py-2.5 border-b border-[#ECE5DD]">
                                <p class="text-xs font-semibold text-[#251A14] truncate"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['user'] ?? 'Admin') ?></p>
                                <p class="text-[11px] text-[#8E7E73] mt-0.5"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Admin')) ?> @ Berkat Dinasti</p>
                            </div>
                            <div class="py-1">
                                <a href="pengaturan.php" class="craft-dropdown-item flex items-center gap-2">
                                    <i class="fas fa-cog text-xs text-[#8E7E73]"></i>
                                    <span>Pengaturan Akun & Toko</span>
                                </a>
                                <a href="pengaturan.php?tab=user" class="craft-dropdown-item flex items-center gap-2">
                                    <i class="fas fa-users-cog text-xs text-[#8E7E73]"></i>
                                    <span>Manajemen User</span>
                                </a>
                            </div>
                            <div class="border-t border-[#ECE5DD] pt-1">
                                <button type="button" onclick="openLogoutModal()" class="w-full text-left craft-dropdown-item text-[#991B1B] hover:text-[#7F1D1D] hover:bg-rose-50/70 flex items-center gap-2">
                                    <i class="fas fa-sign-out-alt text-xs text-[#991B1B]"></i>
                                    <span>Keluar (Logout)</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- MAIN CONTENT CONTAINER -->
            <main class="flex-1 p-4 md:p-8 w-full min-w-0">
                
                <!-- Notifikasi Toast Sukses Tambah Produk -->
                <?php if (!empty($toast_message)): ?>
                    <div id="toastNotification" class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center justify-between shadow-xs animate-in fade-in slide-in-from-top-2">
                        <div class="flex items-center gap-2.5">
                            <i class="fas fa-check-circle text-emerald-600 text-base"></i>
                            <span class="text-sm font-medium"><?= $toast_message ?></span>
                        </div>
                        <button onclick="document.getElementById('toastNotification').remove()" class="text-emerald-700 hover:text-emerald-900 p-1">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- TOP SECTION: GREETING & 3 STAT CARDS -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-6">
                    <div>
                        <h2 class="text-[#251A14] text-2xl font-bold tracking-tight">Daftar Produk Bakery</h2>
                        <p class="text-[#8E7E73] text-sm mt-1 max-w-xl">Kelola katalog roti hajatan, bolu premium, roti manis, varian kemasan, dan batas minimal pemesanan produksi.</p>
                    </div>

                    <!-- 3 KPI Cards Cluster (Dark Chocolate Espresso - Identical Size with Pesanan) -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 shrink-0">
                        <!-- Card 1: Total Produk -->
                        <div class="craft-card-espresso rounded-xl p-3 border border-white/10 shadow-lg flex items-center justify-between min-w-[140px]">
                            <div class="pr-2">
                                <span class="text-[#D8C7B8] text-[10px] font-semibold uppercase tracking-wider block">TOTAL PRODUK</span>
                                <span class="font-bold text-base text-[#FAF8F5]"><?= $total_katalog ?> Katalog</span>
                            </div>
                            <div class="w-8 h-8 rounded-lg bg-[#D46E15]/20 ring-1 ring-[#D46E15]/30 flex items-center justify-center text-[#D46E15] shrink-0">
                                <i class="fas fa-boxes text-xs"></i>
                            </div>
                        </div>

                        <!-- Card 2: Produk Aktif -->
                        <div class="craft-card-espresso rounded-xl p-3 border border-white/10 shadow-lg flex items-center justify-between min-w-[140px]">
                            <div class="pr-2">
                                <span class="text-[#D8C7B8] text-[10px] font-semibold uppercase tracking-wider block">PRODUK AKTIF</span>
                                <span class="font-bold text-base text-[#FAF8F5]"><?= $produk_aktif ?> Siap Pesan</span>
                            </div>
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/20 ring-1 ring-emerald-400/30 flex items-center justify-center text-emerald-400 shrink-0">
                                <i class="fas fa-check-circle text-xs"></i>
                            </div>
                        </div>

                        <!-- Card 3: Varian Kemasan -->
                        <div class="craft-card-espresso rounded-xl p-3 border border-white/10 shadow-lg flex items-center justify-between min-w-[140px]">
                            <div class="pr-2">
                                <span class="text-[#D8C7B8] text-[10px] font-semibold uppercase tracking-wider block">VARIAN KEMASAN</span>
                                <span class="font-bold text-base text-[#FAF8F5]"><?= $varian_kemasan ?> Tipe Box/Pcs</span>
                            </div>
                            <div class="w-8 h-8 rounded-lg bg-amber-500/20 ring-1 ring-amber-400/30 flex items-center justify-center text-amber-400 shrink-0">
                                <i class="fas fa-box text-xs"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CONTROLS & FILTER BAR -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                    <!-- Left Filters: Search, Kategori, Status -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Search Box -->
                        <div class="relative w-full sm:w-64 md:w-72">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#8E7E73]">
                                <i class="fas fa-search text-xs"></i>
                            </div>
                            <input type="text" id="searchInput" onkeyup="filterProducts()" placeholder="Cari nama produk, SKU..." 
                                class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-lg pl-9 pr-3.5 py-2.5 text-sm text-[#251A14] placeholder-[#8E7E73] focus:outline-none focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] transition-colors shadow-2xs">
                        </div>

                        <!-- Dropdown Kategori -->
                        <div class="relative">
                            <select id="categoryFilter" onchange="filterProducts()" 
                                class="appearance-none bg-[#EFE8DE] border border-[#DDD3C5] rounded-lg pl-3.5 pr-9 py-2.5 text-sm text-[#251A14] font-medium focus:outline-none focus:border-[#D46E15] cursor-pointer transition-colors shadow-2xs">
                                <option value="">Semua Kategori</option>
                                <option value="Roti Hajatan">Roti Hajatan</option>
                                <option value="Bolu Premium">Bolu Premium</option>
                                <option value="Roti Manis">Roti Manis</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#8E7E73]">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>

                        <!-- Dropdown Status -->
                        <div class="relative">
                            <select id="statusFilter" onchange="filterProducts()" 
                                class="appearance-none bg-[#EFE8DE] border border-[#DDD3C5] rounded-lg pl-3.5 pr-9 py-2.5 text-sm text-[#251A14] font-medium focus:outline-none focus:border-[#D46E15] cursor-pointer transition-colors shadow-2xs">
                                <option value="">Semua Status</option>
                                <option value="Aktif">Aktif</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#8E7E73]">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Right Buttons: Ekspor Data & Tambah Produk -->
                    <div class="flex items-center gap-3 shrink-0">
                        <!-- Ekspor Data (Live CSV Export) -->
                        <a href="produk.php?action=export_csv" title="Unduh katalog produk format CSV/Excel" 
                            class="bg-[#EFE8DE] hover:bg-[#E5DDD0] border border-[#DDD3C5] text-[#3E2B21] text-sm font-medium px-4 py-2.5 rounded-lg flex items-center gap-2 transition-colors shadow-2xs">
                            <i class="fas fa-file-download text-xs text-[#D46E15]"></i>
                            <span>Ekspor Data</span>
                        </a>

                        <!-- Tambah Produk Button (Triggers Modal) -->
                        <button onclick="openProductModal()" class="bg-[#D46E15] hover:bg-[#B85207] text-white text-sm font-semibold px-4 py-2.5 rounded-lg flex items-center gap-2 transition-all shadow-button-orange active:scale-95">
                            <i class="fas fa-plus text-xs"></i>
                            <span>Tambah Produk</span>
                        </button>
                    </div>
                </div>

                <!-- TABLE CARD (FULL WIDTH) -->
                <div class="bg-[#FDFBF7] rounded-xl shadow-sm border border-[#E2D7C8] overflow-hidden mb-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse border-b border-[#E2D7C8]" id="productsTable">
                            <thead>
                                <tr class="bg-[#3E2B21] text-[#FAF8F5] text-xs font-bold uppercase tracking-wider">
                                    <th class="px-3.5 py-3 w-12 text-center border-r border-white/10">NO</th>
                                    <th class="px-3.5 py-3 border-r border-white/10">NAMA PRODUK</th>
                                    <th class="px-3.5 py-3 border-r border-white/10">KATEGORI</th>
                                    <th class="px-3.5 py-3 border-r border-white/10">KEMASAN</th>
                                    <th class="px-3.5 py-3 border-r border-white/10">MINIMAL ORDER</th>
                                    <th class="px-3.5 py-3 border-r border-white/10">HARGA JUAL</th>
                                    <th class="px-3.5 py-3 text-center border-r border-white/10">STATUS</th>
                                    <th class="px-3.5 py-3 text-center">AKSI</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E2D7C8] text-xs sm:text-sm" id="productsTableBody">
                                <?php foreach ($products as $idx => $prod): ?>
                                    <tr class="<?= $idx % 2 === 1 ? 'bg-[#F8F5F0]' : 'bg-[#FDFBF7]' ?> hover:bg-[#F0E9DF] transition-colors product-row border-b border-[#E2D7C8]"
                                        data-name="<?= strtolower(htmlspecialchars($prod['name'])) ?>"
                                        data-sku="<?= strtolower(htmlspecialchars($prod['sku'])) ?>"
                                        data-category="<?= htmlspecialchars($prod['category']) ?>"
                                        data-status="<?= htmlspecialchars($prod['status']) ?>">
                                        
                                        <!-- No -->
                                        <td class="px-3.5 py-3 text-center text-[#8E7E73] font-medium row-number border-r border-[#E2D7C8]/60">
                                            <?= $idx + 1 ?>
                                        </td>

                                        <!-- Nama Produk -->
                                        <td class="px-3.5 py-3 text-[#251A14] font-semibold max-w-[200px] border-r border-[#E2D7C8]/60">
                                            <div class="truncate font-semibold text-[#251A14]" title="<?= htmlspecialchars($prod['name']) ?>"><?= htmlspecialchars($prod['name']) ?></div>
                                            <span class="text-[11px] text-[#8E7E73] font-mono"><?= htmlspecialchars($prod['sku']) ?></span>
                                        </td>

                                        <!-- Kategori -->
                                        <td class="px-3.5 py-3 text-[#544337] whitespace-nowrap border-r border-[#E2D7C8]/60">
                                            <div class="flex items-center gap-1.5">
                                                <?php if ($prod['category'] === 'Roti Hajatan'): ?>
                                                    <i class="fas fa-bread-slice text-[#D46E15] text-sm"></i>
                                                <?php elseif ($prod['category'] === 'Bolu Premium'): ?>
                                                    <i class="fas fa-birthday-cake text-[#B85207] text-sm"></i>
                                                <?php elseif ($prod['category'] === 'Roti Manis'): ?>
                                                    <i class="fas fa-cookie text-amber-600 text-sm"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-box text-[#8E7E73] text-sm"></i>
                                                <?php endif; ?>
                                                <span class="font-medium"><?= htmlspecialchars($prod['category']) ?></span>
                                            </div>
                                        </td>

                                        <!-- Kemasan Badge -->
                                        <td class="px-3.5 py-3 whitespace-nowrap border-r border-[#E2D7C8]/60">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border <?= $prod['packaging_badge_class'] ?>">
                                                <?php if ($prod['packaging_icon'] === 'mika'): ?>
                                                    <i class="far fa-window-restore text-xs"></i>
                                                <?php elseif ($prod['packaging_icon'] === 'kardus'): ?>
                                                    <i class="fas fa-box-open text-xs"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-circle text-[8px]"></i>
                                                <?php endif; ?>
                                                <?= htmlspecialchars($prod['packaging_label']) ?>
                                            </span>
                                        </td>

                                        <!-- Minimal Order -->
                                        <td class="px-3.5 py-3 text-[#3E2B21] font-medium whitespace-nowrap border-r border-[#E2D7C8]/60">
                                            <?= htmlspecialchars($prod['min_order']) ?>
                                        </td>

                                        <!-- Harga Jual -->
                                        <td class="px-3.5 py-3 whitespace-nowrap border-r border-[#E2D7C8]/60">
                                            <span class="text-[#251A14] font-bold">Rp <?= number_format($prod['price'], 0, ',', '.') ?></span>
                                            <span class="text-[#8E7E73] text-xs">/ <?= htmlspecialchars($prod['price_unit']) ?></span>
                                        </td>

                                        <!-- Status dengan Quick Interactive Toggle -->
                                        <td class="px-3.5 py-3 text-center whitespace-nowrap border-r border-[#E2D7C8]/60">
                                            <button type="button" onclick="toggleProductStatus(<?= $prod['id'] ?>, '<?= $prod['status'] ?>')" 
                                                title="Klik untuk ubah status ke <?= $prod['status'] === 'Aktif' ? 'Nonaktif' : 'Aktif' ?>"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold transition-all hover:scale-105 active:scale-95 cursor-pointer <?= $prod['status'] === 'Aktif' ? 'bg-[#27AE60]/15 text-[#27AE60] hover:bg-[#27AE60]/25' : 'bg-[#8E7E73]/15 text-[#8E7E73] hover:bg-[#8E7E73]/25' ?>">
                                                <span class="w-1.5 h-1.5 rounded-full <?= $prod['status'] === 'Aktif' ? 'bg-[#27AE60]' : 'bg-[#8E7E73]' ?>"></span>
                                                <span><?= htmlspecialchars($prod['status']) ?></span>
                                            </button>
                                        </td>

                                        <!-- Aksi (Edit & Delete) -->
                                        <td class="px-3.5 py-3 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-2">
                                                <!-- Edit Button -->
                                                <button onclick="editProductRow(<?= htmlspecialchars(json_encode($prod)) ?>)" class="p-1.5 text-[#D46E15] hover:bg-[#D46E15]/10 rounded-md transition-colors cursor-pointer" title="Edit Produk">
                                                    <i class="fas fa-edit text-sm"></i>
                                                </button>
                                                <!-- Delete Button (Custom Modal Confirmation) -->
                                                <button onclick="confirmDeleteProduct(<?= $prod['id'] ?>, '<?= addslashes($prod['name']) ?>')" class="p-1.5 text-[#EB5757] hover:bg-[#EB5757]/10 rounded-md transition-colors cursor-pointer" title="Hapus Produk">
                                                    <i class="fas fa-trash-alt text-sm"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <!-- Empty State Row (Muncul saat filter / pencarian tidak menemukan hasil) -->
                                <tr id="emptyStateRow" class="hidden">
                                    <td colspan="8" class="py-12 text-center">
                                        <div class="flex flex-col items-center justify-center max-w-sm mx-auto text-center">
                                            <div class="w-12 h-12 rounded-full bg-[#EFE8DE] text-[#D46E15] flex items-center justify-center mb-3">
                                                <i class="fas fa-search text-lg"></i>
                                            </div>
                                            <h4 class="text-sm font-bold text-[#251A14]">Tidak Ada Produk Ditemukan</h4>
                                            <p class="text-xs text-[#8E7E73] mt-1 mb-4">Coba sesuaikan kata kunci pencarian atau ganti pilihan filter kategori / status Anda.</p>
                                            <button type="button" onclick="resetFilters()" class="text-xs font-semibold text-[#D46E15] hover:text-[#B85207] underline cursor-pointer">
                                                Reset Semua Filter
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Table Footer: Record count & Pagination -->
                    <div class="px-6 py-4 border-t border-[#E2D7C8] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-[#FDFBF7]">
                        <p class="text-xs text-[#8E7E73]" id="tableSummary">
                            Menampilkan <span id="visibleCount" class="font-semibold text-[#251A14]"><?= count($products) ?></span> dari <span class="font-semibold text-[#251A14]"><?= count($products) ?></span> produk bakery
                        </p>

                        <!-- Pagination Buttons (Sesuai jumlah halaman aktif: < 1 > jika hanya 1 halaman) -->
                        <div class="flex items-center gap-1.5 text-xs font-semibold" id="paginationControls">
                            <button id="prevPageBtn" onclick="changePage(currentPage - 1)" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#8E7E73] bg-[#EFE8DE] cursor-not-allowed opacity-60" title="Sebelumnya" disabled>
                                <i class="fas fa-chevron-left text-xs"></i>
                            </button>
                            <div id="pageNumbersContainer" class="flex items-center gap-1.5">
                                <button class="w-8 h-8 rounded-lg bg-[#D46E15] text-white flex items-center justify-center shadow-xs">1</button>
                            </div>
                            <button id="nextPageBtn" onclick="changePage(currentPage + 1)" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#8E7E73] bg-[#EFE8DE] cursor-not-allowed opacity-60" title="Berikutnya" disabled>
                                <i class="fas fa-chevron-right text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- BOTTOM INFO CALLOUT BANNER -->
                <div class="bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-2xs">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-[#D46E15]/15 flex items-center justify-center text-[#D46E15] shrink-0">
                            <i class="fas fa-info-circle text-base"></i>
                        </div>
                        <div>
                            <h4 class="text-[#251A14] text-sm font-semibold">Aturan Batas Minimal Order (MOQ) Produksi</h4>
                            <p class="text-[#8E7E73] text-xs mt-0.5">Pesanan dari pelanggan di kasir atau WhatsApp akan otomatis diverifikasi sesuai batas minimal per varian kemasan ini.</p>
                        </div>
                    </div>
                    <a href="javascript:void(0)" onclick="openWorkflowModal()" class="text-[#D46E15] hover:text-[#B85207] font-semibold text-xs flex items-center gap-1.5 transition-colors shrink-0">
                        <span>Pelajari Alur Produksi</span>
                        <i class="fas fa-arrow-right text-xs"></i>
                    </a>
    <!-- ============================================================== -->
    <!-- MODAL POPOUT: TAMBAH PRODUK BARU (SESUAI FIGMA SPEC)          -->
    <!-- ============================================================== -->
    <div id="productModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <!-- Backdrop with blur -->
        <div onclick="closeProductModal()" class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity duration-200"></div>

        <!-- Modal Card Container with sticky header & footer -->
        <div class="bg-[#FDFBF7] rounded-2xl max-w-[560px] w-full shadow-2xl relative z-10 animate-in fade-in zoom-in-95 duration-150 flex flex-col max-h-[90vh] overflow-hidden border border-[#E2D7C8]">
            
            <!-- Sticky Header -->
            <div class="flex items-start justify-between px-6 py-5 border-b border-[#E2D7C8] shrink-0 bg-[#FDFBF7]">
                <div>
                    <h3 class="text-xl font-bold text-[#251A14]" id="modalTitle">Tambah Produk Baru</h3>
                    <p class="text-[#8E7E73] text-xs mt-1">Tambahkan item roti atau pastry baru ke katalog toko</p>
                </div>
                <button type="button" onclick="closeProductModal()" class="text-[#8E7E73] hover:text-[#251A14] p-1.5 rounded-lg hover:bg-[#EFE8DE] transition-colors cursor-pointer" title="Tutup">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            <!-- Form -->
            <form id="productForm" action="produk.php" method="POST" class="flex flex-col flex-1 overflow-hidden">
                <input type="hidden" name="action" id="productModalFormAction" value="add_product">
                <input type="hidden" name="product_id" id="editProductId" value="">

                <!-- Scrollable Body -->
                <div class="p-6 space-y-4 flex-1 overflow-y-auto">
                    <!-- Nama Produk -->
                    <div>
                        <label for="prod_nama" class="block text-xs font-semibold text-[#3E2B21] mb-1.5">Nama Produk <span class="text-[#EB5757]">*</span></label>
                        <input type="text" id="prod_nama" name="nama_produk" required placeholder="Contoh: Roti Hajatan (9 Pilihan Topping) / Bolu Pisang" 
                            class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-lg px-3.5 py-2.5 text-sm text-[#251A14] placeholder-[#8E7E73] focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] focus:outline-none transition-colors">
                    </div>

                    <!-- Row 1: Kategori & Harga Jual Satuan -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="prod_kategori" class="block text-xs font-semibold text-[#3E2B21] mb-1.5">Kategori <span class="text-[#EB5757]">*</span></label>
                            <div class="relative">
                                <select id="prod_kategori" name="kategori" required 
                                    class="appearance-none w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-lg pl-3.5 pr-9 py-2.5 text-sm text-[#251A14] focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] focus:outline-none cursor-pointer transition-colors">
                                    <option value="" disabled selected>Pilih Kategori</option>
                                    <option value="Roti Hajatan">Roti Hajatan</option>
                                    <option value="Bolu Premium">Bolu Premium</option>
                                    <option value="Roti Manis">Roti Manis</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#8E7E73]">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="prod_harga" class="block text-xs font-semibold text-[#3E2B21] mb-1.5">Harga Jual Satuan <span class="text-[#EB5757]">*</span></label>
                            <div class="relative flex rounded-lg shadow-2xs">
                                <span class="inline-flex items-center px-3.5 rounded-l-lg border border-r-0 border-[#DDD3C5] bg-[#E5DDD0] text-[#3E2B21] text-xs font-semibold">Rp</span>
                                <input type="text" id="prod_harga" name="harga" required placeholder="15.000" onkeyup="formatCurrency(this)" 
                                    class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-r-lg px-3.5 py-2.5 text-sm text-[#251A14] placeholder-[#8E7E73] focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] focus:outline-none transition-colors">
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Tipe Kemasan & Jumlah Isi -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="prod_kemasan" class="block text-xs font-semibold text-[#3E2B21] mb-1.5">Tipe Kemasan</label>
                            <div class="relative">
                                <select id="prod_kemasan" name="kemasan" onchange="handleKemasanChange()" 
                                    class="appearance-none w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-lg pl-3.5 pr-9 py-2.5 text-sm text-[#251A14] focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] focus:outline-none cursor-pointer transition-colors">
                                    <option value="Kardus Putih">Kardus Putih</option>
                                    <option value="Kardus Coklat">Kardus Coklat</option>
                                    <option value="Kardus Batik">Kardus Batik</option>
                                    <option value="Mika" selected>Mika</option>
                                    <option value="Satuan">Satuan</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[#8E7E73]">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="prod_isi" class="block text-xs font-semibold text-[#3E2B21]">Jumlah Isi</label>
                                <span class="text-[10px] font-medium text-[#D46E15] bg-[#EFE8DE] px-1.5 py-0.5 rounded border border-[#DDD3C5]">[Khusus Mika/Kardus]</span>
                            </div>
                            <div class="relative flex rounded-lg shadow-2xs">
                                <input type="number" id="prod_isi" name="isi" value="12" min="1" 
                                    class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-l-lg px-3.5 py-2.5 text-sm text-[#251A14] focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] focus:outline-none transition-colors">
                                <span class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-[#DDD3C5] bg-[#E5DDD0] text-[#544337] text-xs">pcs</span>
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: Minimal Order (MOQ) & Status Produk -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="prod_moq" class="block text-xs font-semibold text-[#3E2B21] mb-1.5">Minimal Order (MOQ)</label>
                            <div class="relative flex rounded-lg shadow-2xs">
                                <input type="number" id="prod_moq" name="moq" value="1" min="1" 
                                    class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-l-lg px-3.5 py-2.5 text-sm text-[#251A14] focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] focus:outline-none transition-colors">
                                <span id="moq_unit" class="inline-flex items-center px-3.5 rounded-r-lg border border-l-0 border-[#DDD3C5] bg-[#E5DDD0] text-[#3E2B21] text-xs font-medium">mika</span>
                            </div>
                            <p class="text-[11px] text-[#8E7E73] mt-1">Batas terendah pesanan diproses</p>
                        </div>

                        <div>
                            <label for="prod_status" class="block text-xs font-semibold text-[#3E2B21] mb-1.5">Status Produk</label>
                            <div class="flex items-center justify-between p-2 border border-[#DDD3C5] rounded-lg bg-[#EFE8DE] h-[42px]">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-medium text-[#251A14]" id="status_label">Aktif Siap Pesan</span>
                                    <span class="bg-[#27AE60]/15 text-[#27AE60] text-[10px] font-semibold px-2 py-0.5 rounded-full" id="status_badge">Aktif</span>
                                </div>
                                <!-- Toggle Switch -->
                                <label for="prod_status" class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="prod_status" name="status" checked onchange="handleStatusToggle(this)" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#D46E15]"></div>
                                </label>
                            </div>
                            <p class="text-[11px] text-[#8E7E73] mt-1">Item muncul di formulir kasir</p>
                        </div>
                    </div>

                    <!-- Deskripsi Produk -->
                    <div>
                        <label for="prod_deskripsi" class="block text-xs font-semibold text-[#3E2B21] mb-1.5">Deskripsi Produk (Opsional)</label>
                        <textarea id="prod_deskripsi" name="deskripsi" rows="3" placeholder="Tuliskan keterangan rasa, komposisi utama, atau instruksi simpan..." 
                            class="w-full bg-[#EFE8DE] border border-[#DDD3C5] rounded-lg px-3.5 py-2.5 text-sm text-[#251A14] placeholder-[#8E7E73] focus:border-[#D46E15] focus:ring-1 focus:ring-[#D46E15] focus:outline-none transition-colors"></textarea>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl p-3 flex items-start gap-2.5">
                        <i class="fas fa-info-circle text-[#D46E15] mt-0.5 shrink-0 text-sm"></i>
                        <p class="text-xs text-[#544337] leading-relaxed">Produk akan otomatis disinkronkan ke rekap kebutuhan bahan harian dapur.</p>
                    </div>
                </div>

                <!-- Sticky Action Buttons Footer -->
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-[#E2D7C8] bg-[#FDFBF7] shrink-0">
                    <button type="button" onclick="closeProductModal()" 
                        class="bg-[#EFE8DE] hover:bg-[#E5DDD0] text-[#3E2B21] text-sm font-medium px-5 py-2.5 rounded-lg border border-[#DDD3C5] transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" 
                        class="bg-[#D46E15] hover:bg-[#B85207] text-white text-sm font-semibold px-5 py-2.5 rounded-lg flex items-center gap-2 transition-all shadow-button-orange active:scale-95 cursor-pointer">
                        <i class="fas fa-check text-xs"></i>
                        <span id="submitProductBtnText">Simpan Produk</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL POPOUT: ALUR PRODUKSI & ATURAN ARMADA (FIGMA SPEC)      -->
    <!-- ============================================================== -->
    <div id="workflowModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div onclick="closeWorkflowModal()" class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity duration-200"></div>
        <div class="bg-[#FDFBF7] rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl relative z-10 animate-in fade-in zoom-in-95 duration-150 border border-[#E2D7C8]">
            <div class="flex items-center justify-between pb-4 border-b border-[#E2D7C8] mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#EFE8DE] text-[#D46E15] flex items-center justify-center">
                        <i class="fas fa-dolly-flatbed text-base"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-[#251A14]">Alur Produksi & Distribusi</h3>
                        <p class="text-xs text-[#8E7E73]">Sistem Informasi Berkat Dinasti Bakery</p>
                    </div>
                </div>
                <button type="button" onclick="closeWorkflowModal()" class="text-[#8E7E73] hover:text-[#251A14] p-1.5 rounded-lg hover:bg-[#EFE8DE] cursor-pointer">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>
            
            <div class="space-y-4 text-xs text-[#544337] leading-relaxed">
                <div class="bg-[#EFE8DE] border border-[#DDD3C5] rounded-xl p-3.5">
                    <h4 class="font-bold text-[#3E2B21] mb-2 flex items-center gap-1.5 text-sm">
                        <span>4 Swimlanes Kanban Produksi</span>
                    </h4>
                    <ol class="list-decimal list-inside space-y-1 text-[#3E2B21]">
                        <li><strong class="text-[#D46E15]">Menunggu Konfirmasi:</strong> Pesanan masuk diverifikasi admin & DP minimal 50% atau Pelunasan.</li>
                        <li><strong class="text-[#2F80ED]">Proses Dapur:</strong> Resep, takaran, dan batas MOQ di-baking oleh tim dapur sesuai jadwal kirim.</li>
                        <li><strong class="text-[#E2B93B]">Siap Kirim:</strong> Roti di-packing ke box/mika/kardus dan ditata ke rak pengiriman.</li>
                        <li><strong class="text-[#27AE60]">Selesai:</strong> Pesanan diterima pelanggan dan invoice ditandai Lunas.</li>
                    </ol>
                </div>

                <div class="bg-blue-50/70 border border-blue-200 rounded-xl p-3.5">
                    <h4 class="font-bold text-blue-900 mb-1.5 flex items-center gap-1.5 text-sm">
                        <i class="fas fa-motorcycle text-blue-600 mr-1"></i>
                        <span>Ketentuan Pengiriman & Obrok Motor</span>
                    </h4>
                    <ul class="list-disc list-inside space-y-1 text-blue-800">
                        <li><strong>Bebas Ongkir:</strong> Pengiriman area Kota Malang & Kota Batu bersifat Bebas Ongkir (Rp 0).</li>
                        <li><strong>Kapasitas Armada:</strong> Kurir motor obrok dibatasi maksimal <strong>150 pcs roti</strong> per trip/keberangkatan untuk menjaga bentuk fisik roti tetap prima.</li>
                    </ul>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" onclick="closeWorkflowModal()" class="bg-[#D46E15] hover:bg-[#B85207] text-white text-xs sm:text-sm font-semibold px-5 py-2.5 rounded-xl transition-all cursor-pointer">
                    Saya Mengerti
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL POPOUT: LOGOUT / KELUAR AKUN (SESUAI FIGMA SCREENSHOT)   -->
    <!-- ============================================================== -->
    <div id="logoutModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <!-- Backdrop with subtle blur -->
        <div onclick="closeLogoutModal()" class="fixed inset-0 craft-modal-overlay"></div>

        <!-- Modal Card Container -->
        <div class="craft-modal-dialog max-w-sm w-full p-6 sm:p-7 relative z-10 text-center">
            <!-- Top Icon -->
            <div class="w-12 h-12 rounded-xl bg-rose-50 border border-rose-200/60 text-rose-600 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-sign-out-alt text-xl"></i>
            </div>

            <!-- Title -->
            <h3 class="text-base sm:text-lg font-bold text-[#251A14] tracking-tight">Keluar dari Akun?</h3>
            <p class="text-xs text-[#8E7E73] mt-1.5 mb-6 leading-relaxed max-w-[280px] mx-auto">
                Anda akan keluar dari sesi ini. Pastikan semua perubahan sudah tersimpan.
            </p>

            <!-- Action Buttons -->
            <div class="space-y-2">
                <a href="logout.php" 
                    class="w-full bg-rose-600 hover:bg-rose-700 active:scale-[0.98] text-white text-xs sm:text-sm font-medium py-2.5 px-4 rounded-lg flex items-center justify-center gap-2 shadow-xs transition-all">
                    <i class="fas fa-sign-out-alt text-sm"></i>
                    <span>Ya, Keluar</span>
                </a>

                <button type="button" onclick="closeLogoutModal()" 
                    class="w-full craft-btn-secondary text-xs sm:text-sm py-2 px-4 rounded-lg">
                    Batal
                </button>
            </div>

            <!-- Encrypted Session Note -->
            <div class="mt-5 flex items-center justify-center gap-1.5 text-[11px] text-[#A89C92]">
                <i class="fas fa-shield-alt text-xs"></i>
                <span>Sesi aman terenkripsi Berkat Dinasti</span>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL POPOUT: KONFIRMASI HAPUS PRODUK                          -->
    <!-- ============================================================== -->
    <div id="deleteConfirmModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <!-- Backdrop with subtle blur -->
        <div onclick="closeDeleteModal()" class="fixed inset-0 craft-modal-overlay"></div>

        <!-- Modal Card Container -->
        <div class="craft-modal-dialog max-w-sm w-full p-6 sm:p-7 relative z-10 text-center">
            <!-- Top Trash Icon -->
            <div class="w-12 h-12 rounded-xl bg-rose-50 border border-rose-200/60 text-rose-600 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-trash-alt text-xl"></i>
            </div>

            <!-- Title -->
            <h3 class="text-base sm:text-lg font-bold text-[#251A14] tracking-tight">Hapus Produk Katalog?</h3>
            <p class="text-xs text-[#8E7E73] mt-1.5 mb-6 leading-relaxed max-w-[280px] mx-auto">
                Apakah Anda yakin ingin menghapus <span id="deleteProductName" class="font-bold text-[#251A14]"></span>? Tindakan ini tidak dapat dibatalkan.
            </p>

            <!-- Action Buttons -->
            <form action="produk.php" method="POST" class="space-y-2">
                <input type="hidden" name="action" value="delete_product">
                <input type="hidden" name="product_id" id="deleteProductId" value="">
                
                <button type="submit" 
                    class="w-full bg-rose-600 hover:bg-rose-700 active:scale-[0.98] text-white text-xs sm:text-sm font-medium py-2.5 px-4 rounded-lg flex items-center justify-center gap-2 shadow-xs transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    <span>Ya, Hapus Produk</span>
                </button>

                <button type="button" onclick="closeDeleteModal()" 
                    class="w-full craft-btn-secondary text-xs sm:text-sm py-2 px-4 rounded-lg cursor-pointer">
                    Batal
                </button>
            </form>
        </div>
    </div>

    <!-- Hidden Form untuk Quick Toggle Status -->
    <form id="quickToggleStatusForm" action="produk.php" method="POST" class="hidden">
        <input type="hidden" name="action" value="toggle_status">
        <input type="hidden" name="product_id" id="toggleProductId" value="">
    </form>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        // Toggle Sidebar Mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('main-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        // Modal Popout Open / Close
        function openProductModal() {
            // Reset ke mode Tambah Produk Baru
            document.getElementById('modalTitle').textContent = 'Tambah Produk Baru';
            document.getElementById('productModalFormAction').value = 'add_product';
            document.getElementById('editProductId').value = '';
            document.getElementById('submitProductBtnText').textContent = 'Simpan Produk';
            
            document.getElementById('productForm').reset();
            document.getElementById('prod_isi').value = '12';
            document.getElementById('prod_moq').value = '1';
            
            // Set status default Aktif
            const chk = document.getElementById('prod_status');
            chk.checked = true;
            handleStatusToggle(chk);
            handleKemasanChange();

            const modal = document.getElementById('productModal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            setTimeout(() => document.getElementById('prod_nama').focus(), 50);
        }

        function closeProductModal() {
            const modal = document.getElementById('productModal');
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Dynamic Kemasan Unit Handling
        function handleKemasanChange() {
            const kemasan = document.getElementById('prod_kemasan').value;
            const moqUnit = document.getElementById('moq_unit');
            const isiInput = document.getElementById('prod_isi');
            
            if (kemasan === 'Mika') {
                moqUnit.textContent = 'mika';
                isiInput.disabled = false;
                if (!isiInput.value || isiInput.value < 1) isiInput.value = 1;
            } else if (kemasan.toLowerCase().includes('kardus')) {
                moqUnit.textContent = 'box';
                isiInput.disabled = false;
                if (!isiInput.value || isiInput.value < 1) isiInput.value = 1;
            } else {
                moqUnit.textContent = 'pcs';
                isiInput.disabled = true;
                isiInput.value = 1;
            }
        }

        // Status Toggle Switch
        function handleStatusToggle(checkbox) {
            const label = document.getElementById('status_label');
            const badge = document.getElementById('status_badge');
            if (checkbox.checked) {
                label.textContent = 'Aktif Siap Pesan';
                badge.textContent = 'Aktif';
                badge.className = 'bg-[#27AE60]/15 text-[#27AE60] text-[10px] font-semibold px-2 py-0.5 rounded-full';
            } else {
                label.textContent = 'Nonaktif';
                badge.textContent = 'Nonaktif';
                badge.className = 'bg-gray-100 text-[#828282] text-[10px] font-semibold px-2 py-0.5 rounded-full';
            }
        }

        // Format Currency Helper
        function formatCurrency(input) {
            let val = input.value.replace(/\D/g, '');
            if (val === '') {
                input.value = '';
                return;
            }
            input.value = new Intl.NumberFormat('id-ID').format(val);
        }

        // Edit row (Pre-fill modal untuk mode Edit)
        function editProductRow(prod) {
            document.getElementById('modalTitle').textContent = 'Edit Produk: ' + prod.name;
            document.getElementById('productModalFormAction').value = 'edit_product';
            document.getElementById('editProductId').value = prod.id;
            document.getElementById('submitProductBtnText').textContent = 'Perbarui Data Produk';

            document.getElementById('prod_nama').value = prod.name;
            document.getElementById('prod_kategori').value = prod.category;
            document.getElementById('prod_harga').value = new Intl.NumberFormat('id-ID').format(prod.price);
            document.getElementById('prod_kemasan').value = prod.packaging_type;
            
            handleKemasanChange();
            
            document.getElementById('prod_isi').value = prod.isi || (prod.packaging_type === 'Mika' ? 12 : (prod.packaging_type === 'Kardus' ? 20 : 1));
            document.getElementById('prod_moq').value = prod.moq_num || 1;
            
            const chk = document.getElementById('prod_status');
            chk.checked = (prod.status === 'Aktif');
            handleStatusToggle(chk);

            document.getElementById('prod_deskripsi').value = prod.description || '';

            const modal = document.getElementById('productModal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            setTimeout(() => document.getElementById('prod_nama').focus(), 50);
        }

        // Custom Delete Confirmation Modal
        function confirmDeleteProduct(id, name) {
            document.getElementById('deleteProductId').value = id;
            document.getElementById('deleteProductName').textContent = `"${name}"`;
            
            const modal = document.getElementById('deleteConfirmModal');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteConfirmModal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }

        // Quick Toggle Status via POST form
        function toggleProductStatus(id, currentStatus) {
            const nextStatus = currentStatus === 'Aktif' ? 'Nonaktif' : 'Aktif';
            document.getElementById('toggleProductId').value = id;
            document.getElementById('quickToggleStatusForm').submit();
        }

        let currentPage = 1;
        const itemsPerPage = 15;

        function updatePagination(totalItems) {
            const totalPages = Math.max(1, Math.ceil(totalItems / itemsPerPage));
            if (currentPage > totalPages) currentPage = totalPages;

            const container = document.getElementById('pageNumbersContainer');
            if (container) {
                container.innerHTML = '';
                for (let i = 1; i <= totalPages; i++) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = (i === currentPage)
                        ? 'w-8 h-8 rounded-lg bg-[#D46E15] text-white flex items-center justify-center shadow-xs cursor-default'
                        : 'w-8 h-8 rounded-lg text-[#251A14] hover:bg-[#EFE8DE] flex items-center justify-center transition-colors cursor-pointer';
                    btn.textContent = i;
                    btn.onclick = () => changePage(i);
                    container.appendChild(btn);
                }
            }

            const prevBtn = document.getElementById('prevPageBtn');
            const nextBtn = document.getElementById('nextPageBtn');
            if (prevBtn) {
                prevBtn.disabled = (currentPage <= 1);
                prevBtn.className = (currentPage <= 1)
                    ? 'w-8 h-8 rounded-lg flex items-center justify-center text-[#8E7E73] bg-[#EFE8DE] cursor-not-allowed opacity-60'
                    : 'w-8 h-8 rounded-lg flex items-center justify-center text-[#251A14] bg-[#EFE8DE] hover:bg-[#E5DDD0] transition-colors cursor-pointer';
            }
            if (nextBtn) {
                nextBtn.disabled = (currentPage >= totalPages);
                nextBtn.className = (currentPage >= totalPages)
                    ? 'w-8 h-8 rounded-lg flex items-center justify-center text-[#8E7E73] bg-[#EFE8DE] cursor-not-allowed opacity-60'
                    : 'w-8 h-8 rounded-lg flex items-center justify-center text-[#251A14] bg-[#EFE8DE] hover:bg-[#E5DDD0] transition-colors cursor-pointer';
            }
        }

        function changePage(newPage) {
            const rows = document.querySelectorAll('.product-row');
            const searchVal = document.getElementById('searchInput').value.toLowerCase().trim();
            const categoryVal = document.getElementById('categoryFilter').value;
            const statusVal = document.getElementById('statusFilter').value;
            
            let matchCount = 0;
            rows.forEach((row) => {
                const name = (row.getAttribute('data-name') || '').toLowerCase();
                const sku = (row.getAttribute('data-sku') || '').toLowerCase();
                const category = row.getAttribute('data-category') || '';
                const status = row.getAttribute('data-status') || '';
                if ((!searchVal || name.includes(searchVal) || sku.includes(searchVal)) &&
                    (!categoryVal || category === categoryVal) &&
                    (!statusVal || status === statusVal)) {
                    matchCount++;
                }
            });
            const totalPages = Math.max(1, Math.ceil(matchCount / itemsPerPage));
            if (newPage < 1 || newPage > totalPages) return;
            currentPage = newPage;
            filterProducts(false);
        }

        // Live Filter Function (Search, Category, Status, Pagination)
        function filterProducts(resetPage = true) {
            if (resetPage) currentPage = 1;

            const searchVal = document.getElementById('searchInput').value.toLowerCase().trim();
            const categoryVal = document.getElementById('categoryFilter').value;
            const statusVal = document.getElementById('statusFilter').value;

            const rows = document.querySelectorAll('.product-row');
            const matchingRows = [];

            rows.forEach((row) => {
                const name = (row.getAttribute('data-name') || '').toLowerCase();
                const sku = (row.getAttribute('data-sku') || '').toLowerCase();
                const category = row.getAttribute('data-category') || '';
                const status = row.getAttribute('data-status') || '';

                const matchesSearch = !searchVal || name.includes(searchVal) || sku.includes(searchVal);
                const matchesCategory = !categoryVal || category === categoryVal;
                const matchesStatus = !statusVal || status === statusVal;

                if (matchesSearch && matchesCategory && matchesStatus) {
                    matchingRows.push(row);
                } else {
                    row.style.display = 'none';
                }
            });

            const totalMatches = matchingRows.length;
            const totalPages = Math.max(1, Math.ceil(totalMatches / itemsPerPage));
            if (currentPage > totalPages) currentPage = totalPages;

            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;

            matchingRows.forEach((row, idx) => {
                if (idx >= startIndex && idx < endIndex) {
                    row.style.display = '';
                    const numCell = row.querySelector('.row-number');
                    if (numCell) numCell.textContent = idx + 1;
                } else {
                    row.style.display = 'none';
                }
            });

            // Update visible count summary
            const visibleCountEl = document.getElementById('visibleCount');
            if (visibleCountEl) visibleCountEl.textContent = totalMatches;

            // Show or hide empty state row
            const emptyStateRow = document.getElementById('emptyStateRow');
            if (emptyStateRow) {
                if (totalMatches === 0) {
                    emptyStateRow.classList.remove('hidden');
                } else {
                    emptyStateRow.classList.add('hidden');
                }
            }

            updatePagination(totalMatches);
        }

        // Reset Filter function
        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('categoryFilter').value = '';
            document.getElementById('statusFilter').value = '';
            filterProducts();
        }

        // Popover Notifikasi & User Dropdown
        function toggleNotifPopover() {
            const pop = document.getElementById('notifPopover');
            if (pop) pop.classList.toggle('hidden');
        }

        function toggleUserDropdown() {
            const drop = document.getElementById('userDropdown');
            if (drop) drop.classList.toggle('hidden');
        }

        // Modal Alur Produksi & Armada Obrok Motor
        function openWorkflowModal() {
            const modal = document.getElementById('workflowModal');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeWorkflowModal() {
            const modal = document.getElementById('workflowModal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }

        // Logout Modal Open / Close
        function openLogoutModal() {
            const modal = document.getElementById('logoutModal');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeLogoutModal() {
            const modal = document.getElementById('logoutModal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLogoutModal();
                closeProductModal();
                closeDeleteModal();
                closeWorkflowModal();
            }
        });

        // Close dropdowns on outside click
        document.addEventListener('click', function(e) {
            const notifPop = document.getElementById('notifPopover');
            const userDrop = document.getElementById('userDropdown');
            if (notifPop && !e.target.closest('#notifPopover') && !e.target.closest('button[onclick="toggleNotifPopover()"]')) {
                notifPop.classList.add('hidden');
            }
            if (userDrop && !e.target.closest('#userDropdown') && !e.target.closest('div[onclick="toggleUserDropdown()"]')) {
                userDrop.classList.add('hidden');
            }
        });

        // Auto-dismiss toast notification after 5 seconds
        const toastEl = document.getElementById('toastNotification');
        if (toastEl) {
            setTimeout(() => {
                toastEl.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                setTimeout(() => toastEl.remove(), 500);
            }, 5000);
        }
    </script>
</body>
</html>
