<?php
require __DIR__ . '/includes/db.php';

// Config global
$APP_NAME = 'Berkat Dinasti PHP Test Dashboard';
$APP_DOMAIN = 'ucing4k.my.id';

if (isset($_GET['health'])) {
    header('Content-Type: application/json; charset=utf-8');
    $ok = db_fetch_one($mysqli, 'SELECT 1 AS ok');
    echo json_encode([
        'status' => ($ok && (int) $ok['ok'] === 1) ? 'ok' : 'error',
        'database' => $DB_NAME,
        'domain' => $APP_DOMAIN,
    ]);
    exit;
}

$page = $_GET['page'] ?? 'dashboard';
$validPages = ['dashboard', 'pelanggan', 'kategori', 'produk', 'varian', 'order_test'];
if (!in_array($page, $validPages, true)) {
    $page = 'dashboard';
}

$orderTestResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add_pelanggan') {
            $stmt = $mysqli->prepare('INSERT INTO pelanggan (id_zona, nama, alamat, no_wa, tipe, catatan) VALUES (?, ?, ?, ?, ?, ?)');
            $idZona = ($_POST['id_zona'] ?? '') !== '' ? (int) $_POST['id_zona'] : null;
            $nama = trim($_POST['nama'] ?? '');
            $alamat = trim($_POST['alamat'] ?? '');
            $noWa = trim($_POST['no_wa'] ?? '');
            $tipe = $_POST['tipe'] ?? 'retail';
            $catatan = trim($_POST['catatan'] ?? '');
            $catatan = $catatan !== '' ? $catatan : null;
            $stmt->bind_param('isssss', $idZona, $nama, $alamat, $noWa, $tipe, $catatan);
            $stmt->execute();
            $stmt->close();
            redirect_with_message('pelanggan', 'Pelanggan berhasil ditambahkan.');
        }

        if ($action === 'edit_pelanggan') {
            $stmt = $mysqli->prepare('UPDATE pelanggan SET id_zona=?, nama=?, alamat=?, no_wa=?, tipe=?, catatan=? WHERE id_pelanggan=?');
            $id = (int) ($_POST['id'] ?? 0);
            $idZona = ($_POST['id_zona'] ?? '') !== '' ? (int) $_POST['id_zona'] : null;
            $nama = trim($_POST['nama'] ?? '');
            $alamat = trim($_POST['alamat'] ?? '');
            $noWa = trim($_POST['no_wa'] ?? '');
            $tipe = $_POST['tipe'] ?? 'retail';
            $catatan = trim($_POST['catatan'] ?? '');
            $catatan = $catatan !== '' ? $catatan : null;
            $stmt->bind_param('isssssi', $idZona, $nama, $alamat, $noWa, $tipe, $catatan, $id);
            $stmt->execute();
            $stmt->close();
            redirect_with_message('pelanggan', 'Pelanggan berhasil diperbarui.');
        }

        if ($action === 'delete_pelanggan') {
            $stmt = $mysqli->prepare('DELETE FROM pelanggan WHERE id_pelanggan=?');
            $id = (int) ($_POST['id'] ?? 0);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            redirect_with_message('pelanggan', 'Pelanggan berhasil dihapus.');
        }

        if ($action === 'add_kategori') {
            $stmt = $mysqli->prepare('INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, ?)');
            $nama = trim($_POST['nama_kategori'] ?? '');
            $deskripsi = trim($_POST['deskripsi'] ?? '');
            $deskripsi = $deskripsi !== '' ? $deskripsi : null;
            $stmt->bind_param('ss', $nama, $deskripsi);
            $stmt->execute();
            $stmt->close();
            redirect_with_message('kategori', 'Kategori berhasil ditambahkan.');
        }

        if ($action === 'edit_kategori') {
            $stmt = $mysqli->prepare('UPDATE kategori SET nama_kategori=?, deskripsi=? WHERE id_kategori=?');
            $id = (int) ($_POST['id'] ?? 0);
            $nama = trim($_POST['nama_kategori'] ?? '');
            $deskripsi = trim($_POST['deskripsi'] ?? '');
            $deskripsi = $deskripsi !== '' ? $deskripsi : null;
            $stmt->bind_param('ssi', $nama, $deskripsi, $id);
            $stmt->execute();
            $stmt->close();
            redirect_with_message('kategori', 'Kategori berhasil diperbarui.');
        }

        if ($action === 'delete_kategori') {
            $stmt = $mysqli->prepare('DELETE FROM kategori WHERE id_kategori=?');
            $id = (int) ($_POST['id'] ?? 0);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            redirect_with_message('kategori', 'Kategori berhasil dihapus.');
        }

        if ($action === 'add_produk') {
            $stmt = $mysqli->prepare('INSERT INTO produk (id_kategori, nama_produk, deskripsi, shelf_life, status) VALUES (?, ?, ?, ?, ?)');
            $idKategori = (int) ($_POST['id_kategori'] ?? 0);
            $nama = trim($_POST['nama_produk'] ?? '');
            $deskripsi = trim($_POST['deskripsi'] ?? '');
            $deskripsi = $deskripsi !== '' ? $deskripsi : null;
            $shelf = (int) ($_POST['shelf_life'] ?? 7);
            $status = $_POST['status'] ?? 'tersedia';
            $stmt->bind_param('issis', $idKategori, $nama, $deskripsi, $shelf, $status);
            $stmt->execute();
            $stmt->close();
            redirect_with_message('produk', 'Produk berhasil ditambahkan.');
        }

        if ($action === 'edit_produk') {
            $stmt = $mysqli->prepare('UPDATE produk SET id_kategori=?, nama_produk=?, deskripsi=?, shelf_life=?, status=? WHERE id_produk=?');
            $id = (int) ($_POST['id'] ?? 0);
            $idKategori = (int) ($_POST['id_kategori'] ?? 0);
            $nama = trim($_POST['nama_produk'] ?? '');
            $deskripsi = trim($_POST['deskripsi'] ?? '');
            $deskripsi = $deskripsi !== '' ? $deskripsi : null;
            $shelf = (int) ($_POST['shelf_life'] ?? 7);
            $status = $_POST['status'] ?? 'tersedia';
            $stmt->bind_param('issisi', $idKategori, $nama, $deskripsi, $shelf, $status, $id);
            $stmt->execute();
            $stmt->close();
            redirect_with_message('produk', 'Produk berhasil diperbarui.');
        }

        if ($action === 'delete_produk') {
            $stmt = $mysqli->prepare('DELETE FROM produk WHERE id_produk=?');
            $id = (int) ($_POST['id'] ?? 0);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            redirect_with_message('produk', 'Produk berhasil dihapus.');
        }

        if ($action === 'add_varian') {
            $stmt = $mysqli->prepare('INSERT INTO varian_produk (id_produk, nama_varian, harga, min_order, stok) VALUES (?, ?, ?, ?, ?)');
            $idProduk = (int) ($_POST['id_produk'] ?? 0);
            $namaVarian = trim($_POST['nama_varian'] ?? '');
            $harga = (float) ($_POST['harga'] ?? 0);
            $minOrder = (int) ($_POST['min_order'] ?? 1);
            $stok = (int) ($_POST['stok'] ?? 0);
            $stmt->bind_param('isdii', $idProduk, $namaVarian, $harga, $minOrder, $stok);
            $stmt->execute();
            $stmt->close();
            redirect_with_message('varian', 'Varian berhasil ditambahkan.');
        }

        if ($action === 'edit_varian') {
            $stmt = $mysqli->prepare('UPDATE varian_produk SET id_produk=?, nama_varian=?, harga=?, min_order=?, stok=? WHERE id_varian=?');
            $id = (int) ($_POST['id'] ?? 0);
            $idProduk = (int) ($_POST['id_produk'] ?? 0);
            $namaVarian = trim($_POST['nama_varian'] ?? '');
            $harga = (float) ($_POST['harga'] ?? 0);
            $minOrder = (int) ($_POST['min_order'] ?? 1);
            $stok = (int) ($_POST['stok'] ?? 0);
            $stmt->bind_param('isdiii', $idProduk, $namaVarian, $harga, $minOrder, $stok, $id);
            $stmt->execute();
            $stmt->close();
            redirect_with_message('varian', 'Varian berhasil diperbarui.');
        }

        if ($action === 'delete_varian') {
            $stmt = $mysqli->prepare('DELETE FROM varian_produk WHERE id_varian=?');
            $id = (int) ($_POST['id'] ?? 0);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            redirect_with_message('varian', 'Varian berhasil dihapus.');
        }

        if ($action === 'run_order_test') {
            $page = 'order_test';

            $idPelanggan = (int) ($_POST['id_pelanggan'] ?? 0);
            $idVarian = (int) ($_POST['id_varian'] ?? 0);
            $qty = (int) ($_POST['qty'] ?? 0);
            $tglKirim = trim($_POST['tgl_kirim'] ?? date('Y-m-d'));
            $waktuKirim = trim($_POST['waktu_kirim'] ?? '09:00:00');
            $metodeBayar = trim($_POST['metode_bayar'] ?? 'transfer');
            $catatan = trim($_POST['catatan'] ?? 'Order test by dashboard');
            $keepData = isset($_POST['keep_data']) && $_POST['keep_data'] === '1';

            if ($idPelanggan <= 0 || $idVarian <= 0 || $qty <= 0) {
                throw new RuntimeException('Input test order tidak valid. Pilih pelanggan, varian, dan qty > 0.');
            }

            $logs = [];
            $startTime = microtime(true);

            $varianBefore = db_fetch_one(
                $mysqli,
                'SELECT id_varian, nama_varian, harga, min_order, stok FROM varian_produk WHERE id_varian = ' . $idVarian
            );
            if (!$varianBefore) {
                throw new RuntimeException('Varian tidak ditemukan.');
            }

            $stokBefore = (int) ($varianBefore['stok'] ?? 0);
            $hargaSatuan = (float) ($varianBefore['harga'] ?? 0);
            $minOrder = (int) ($varianBefore['min_order'] ?? 1);

            $logs[] = [
                'nama' => 'Validasi minimum order',
                'ok' => $qty >= $minOrder,
                'expected' => 'qty >= ' . $minOrder,
                'actual' => 'qty = ' . $qty,
            ];

            $mysqli->begin_transaction();
            try {
                $stmt = $mysqli->prepare('CALL sp_create_pesanan(?, ?, ?, ?, ?, @new_order_id)');
                $stmt->bind_param('issss', $idPelanggan, $tglKirim, $waktuKirim, $metodeBayar, $catatan);
                $stmt->execute();
                $stmt->close();
                while ($mysqli->more_results() && $mysqli->next_result()) {
                }

                $newOrder = db_fetch_one($mysqli, 'SELECT @new_order_id AS id_pesanan');
                $idPesanan = (int) ($newOrder['id_pesanan'] ?? 0);
                if ($idPesanan <= 0) {
                    throw new RuntimeException('Stored procedure sp_create_pesanan tidak menghasilkan id_pesanan.');
                }

                $stmt = $mysqli->prepare('CALL sp_add_detail_pesanan(?, ?, ?)');
                $stmt->bind_param('iii', $idPesanan, $idVarian, $qty);
                $stmt->execute();
                $stmt->close();
                while ($mysqli->more_results() && $mysqli->next_result()) {
                }

                $pesananAfterDetail = db_fetch_one(
                    $mysqli,
                    'SELECT id_pesanan, no_invoice, total_harga, ongkir, grand_total, status, status_bayar FROM pesanan WHERE id_pesanan = ' . $idPesanan
                );
                if (!$pesananAfterDetail) {
                    throw new RuntimeException('Data pesanan baru tidak ditemukan setelah sp_add_detail_pesanan.');
                }

                $grandTotal = (float) ($pesananAfterDetail['grand_total'] ?? 0);

                $receivedBy = 1;
                $bukti = 'AUTO-TEST';
                $ketBayar = 'Pembayaran otomatis untuk order test';
                $stmt = $mysqli->prepare('CALL sp_bayar(?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('idsssi', $idPesanan, $grandTotal, $metodeBayar, $bukti, $ketBayar, $receivedBy);
                $stmt->execute();
                $stmt->close();
                while ($mysqli->more_results() && $mysqli->next_result()) {
                }

                $statusSelesai = 'selesai';
                $stmt = $mysqli->prepare('CALL sp_update_status(?, ?)');
                $stmt->bind_param('is', $idPesanan, $statusSelesai);
                $stmt->execute();
                $stmt->close();
                while ($mysqli->more_results() && $mysqli->next_result()) {
                }

                $pesananFinal = db_fetch_one(
                    $mysqli,
                    'SELECT id_pesanan, no_invoice, total_harga, ongkir, grand_total, status, status_bayar FROM pesanan WHERE id_pesanan = ' . $idPesanan
                );
                $detailAgg = db_fetch_one(
                    $mysqli,
                    'SELECT COALESCE(SUM(subtotal), 0) AS sum_subtotal FROM detail_pesanan WHERE id_pesanan = ' . $idPesanan
                );
                $detailRow = db_fetch_one(
                    $mysqli,
                    'SELECT qty, harga_satuan, subtotal FROM detail_pesanan WHERE id_pesanan = ' . $idPesanan . ' ORDER BY id_detail DESC LIMIT 1'
                );
                $varianAfter = db_fetch_one(
                    $mysqli,
                    'SELECT stok FROM varian_produk WHERE id_varian = ' . $idVarian
                );
                $pembayaranAgg = db_fetch_one(
                    $mysqli,
                    'SELECT COALESCE(SUM(jumlah), 0) AS total_bayar, MAX(id_pembayaran) AS last_id FROM pembayaran WHERE id_pesanan = ' . $idPesanan
                );

                $lastPembayaranId = (int) ($pembayaranAgg['last_id'] ?? 0);
                $kasRow = $lastPembayaranId > 0
                    ? db_fetch_one(
                        $mysqli,
                        "SELECT nominal FROM transaksi_kas WHERE sumber_tipe = 'pembayaran' AND sumber_id = " . $lastPembayaranId . ' ORDER BY id_transaksi DESC LIMIT 1'
                    )
                    : null;
                $trackingCount = db_fetch_one($mysqli, 'SELECT COUNT(*) AS total FROM tracking_log WHERE id_pesanan = ' . $idPesanan);

                $stokAfter = (int) ($varianAfter['stok'] ?? $stokBefore);
                $expectedStokAfter = $stokBefore - $qty;

                $noInvoice = (string) ($pesananFinal['no_invoice'] ?? '');
                $today = date('Ymd');
                $invoiceOk = (bool) preg_match('/^INV-' . $today . '-[0-9]{4}$/', $noInvoice);

                $detailSubtotal = (float) ($detailRow['subtotal'] ?? 0);
                $expectedSubtotal = ((float) ($detailRow['harga_satuan'] ?? 0)) * ((int) ($detailRow['qty'] ?? 0));
                $sumSubtotal = (float) ($detailAgg['sum_subtotal'] ?? 0);
                $totalHarga = (float) ($pesananFinal['total_harga'] ?? 0);
                $ongkir = (float) ($pesananFinal['ongkir'] ?? 0);
                $expectedGrand = $sumSubtotal + $ongkir;
                $grandFinal = (float) ($pesananFinal['grand_total'] ?? 0);
                $totalBayar = (float) ($pembayaranAgg['total_bayar'] ?? 0);
                $statusBayar = (string) ($pesananFinal['status_bayar'] ?? '');
                $statusOrder = (string) ($pesananFinal['status'] ?? '');
                $kasNominal = (float) ($kasRow['nominal'] ?? 0);
                $logCount = (int) ($trackingCount['total'] ?? 0);

                $logs[] = [
                    'nama' => 'Trigger invoice otomatis',
                    'ok' => $invoiceOk,
                    'expected' => 'Format INV-' . $today . '-####',
                    'actual' => $noInvoice !== '' ? $noInvoice : '(kosong)',
                ];
                $logs[] = [
                    'nama' => 'Trigger subtotal detail',
                    'ok' => abs($detailSubtotal - $expectedSubtotal) < 0.01,
                    'expected' => number_format($expectedSubtotal, 2, '.', ''),
                    'actual' => number_format($detailSubtotal, 2, '.', ''),
                ];
                $logs[] = [
                    'nama' => 'Trigger total_harga pesanan',
                    'ok' => abs($totalHarga - $sumSubtotal) < 0.01,
                    'expected' => number_format($sumSubtotal, 2, '.', ''),
                    'actual' => number_format($totalHarga, 2, '.', ''),
                ];
                $logs[] = [
                    'nama' => 'Trigger grand_total pesanan',
                    'ok' => abs($grandFinal - $expectedGrand) < 0.01,
                    'expected' => number_format($expectedGrand, 2, '.', ''),
                    'actual' => number_format($grandFinal, 2, '.', ''),
                ];
                $logs[] = [
                    'nama' => 'Trigger status_bayar setelah sp_bayar',
                    'ok' => $statusBayar === 'lunas' && $totalBayar >= $grandFinal,
                    'expected' => 'status_bayar=lunas, total_bayar>=grand_total',
                    'actual' => 'status_bayar=' . $statusBayar . ', total_bayar=' . number_format($totalBayar, 2, '.', ''),
                ];
                $logs[] = [
                    'nama' => 'Stored procedure sp_update_status',
                    'ok' => $statusOrder === 'selesai',
                    'expected' => 'status=selesai',
                    'actual' => 'status=' . $statusOrder,
                ];
                $logs[] = [
                    'nama' => 'Trigger transaksi kas dari pembayaran',
                    'ok' => abs($kasNominal - $grandFinal) < 0.01,
                    'expected' => number_format($grandFinal, 2, '.', ''),
                    'actual' => number_format($kasNominal, 2, '.', ''),
                ];
                $logs[] = [
                    'nama' => 'Trigger tracking log status',
                    'ok' => $logCount > 0,
                    'expected' => 'tracking_log bertambah',
                    'actual' => 'jumlah_log=' . $logCount,
                ];
                $logs[] = [
                    'nama' => 'Kontrol stok varian setelah order',
                    'ok' => $stokAfter === $expectedStokAfter,
                    'expected' => 'stok akhir=' . $expectedStokAfter,
                    'actual' => 'stok akhir=' . $stokAfter,
                ];

                $allOk = true;
                foreach ($logs as $log) {
                    if (!$log['ok']) {
                        $allOk = false;
                        break;
                    }
                }

                if ($keepData) {
                    $mysqli->commit();
                } else {
                    $mysqli->rollback();
                }

                $orderTestResult = [
                    'ok' => $allOk,
                    'id_pesanan' => $idPesanan,
                    'no_invoice' => $noInvoice,
                    'keep_data' => $keepData,
                    'duration_ms' => (int) ((microtime(true) - $startTime) * 1000),
                    'logs' => $logs,
                    'stok_before' => $stokBefore,
                    'stok_after' => $stokAfter,
                    'qty' => $qty,
                ];
            } catch (Throwable $e) {
                $mysqli->rollback();
                throw $e;
            }
        }
    } catch (Throwable $e) {
        redirect_with_message($page, 'Operasi gagal: ' . $e->getMessage(), 'danger');
    }
}

$stats = [
    'pelanggan' => (int) (db_fetch_one($mysqli, 'SELECT COUNT(*) AS total FROM pelanggan')['total'] ?? 0),
    'produk' => (int) (db_fetch_one($mysqli, 'SELECT COUNT(*) AS total FROM produk')['total'] ?? 0),
    'pesanan' => (int) (db_fetch_one($mysqli, 'SELECT COUNT(*) AS total FROM pesanan')['total'] ?? 0),
    'pending' => (int) (db_fetch_one($mysqli, "SELECT COUNT(*) AS total FROM pesanan WHERE status = 'pending'")['total'] ?? 0),
];
$kas = db_fetch_one($mysqli, 'SELECT total_masuk, total_keluar, saldo_akhir FROM v_ringkasan_kas');
$latestOrders = db_fetch_all($mysqli, 'SELECT id_pesanan, no_invoice, nama_pelanggan, grand_total, status, status_bayar FROM v_pesanan_lengkap ORDER BY id_pesanan DESC LIMIT 8');
$latestPay = db_fetch_all($mysqli, 'SELECT pb.id_pembayaran, p.no_invoice, pb.jumlah, pb.metode FROM pembayaran pb JOIN pesanan p ON p.id_pesanan = pb.id_pesanan ORDER BY pb.id_pembayaran DESC LIMIT 8');

$zones = db_fetch_all($mysqli, 'SELECT id_zona, nama_zona FROM zona ORDER BY nama_zona');
$pelangganRows = db_fetch_all($mysqli, 'SELECT pel.id_pelanggan, pel.id_zona, pel.nama, pel.alamat, pel.no_wa, pel.tipe, pel.catatan, z.nama_zona FROM pelanggan pel LEFT JOIN zona z ON z.id_zona = pel.id_zona ORDER BY pel.id_pelanggan DESC');

$kategoriRows = db_fetch_all($mysqli, 'SELECT id_kategori, nama_kategori, deskripsi FROM kategori ORDER BY id_kategori DESC');
$produkRows = db_fetch_all($mysqli, 'SELECT p.id_produk, p.id_kategori, p.nama_produk, p.deskripsi, p.shelf_life, p.status, k.nama_kategori FROM produk p JOIN kategori k ON k.id_kategori = p.id_kategori ORDER BY p.id_produk DESC');
$varianRows = db_fetch_all($mysqli, 'SELECT v.id_varian, v.id_produk, v.nama_varian, v.harga, v.min_order, v.stok, p.nama_produk FROM varian_produk v JOIN produk p ON p.id_produk = v.id_produk ORDER BY v.id_varian DESC');
$orderPelangganRows = db_fetch_all($mysqli, 'SELECT id_pelanggan, nama FROM pelanggan ORDER BY nama');
$orderVarianRows = db_fetch_all($mysqli, 'SELECT v.id_varian, v.nama_varian, v.stok, v.min_order, v.harga, p.nama_produk FROM varian_produk v JOIN produk p ON p.id_produk = v.id_produk ORDER BY p.nama_produk, v.nama_varian');
$recentOrderTests = db_fetch_all($mysqli, 'SELECT id_pesanan, no_invoice, tgl_pesan, grand_total, status, status_bayar FROM pesanan ORDER BY id_pesanan DESC LIMIT 10');

$msg = $_GET['msg'] ?? '';
$type = $_GET['type'] ?? 'success';

function nav_active(string $name, string $current): string
{
    return $name === $current ? 'active' : '';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= esc($APP_NAME); ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="nav">
    <div class="nav-inner">
        <div class="brand"><?= esc($APP_NAME); ?></div>
        <div class="menu">
            <a class="<?= nav_active('dashboard', $page); ?>" href="index.php?page=dashboard">Dashboard</a>
            <a class="<?= nav_active('pelanggan', $page); ?>" href="index.php?page=pelanggan">Pelanggan</a>
            <a class="<?= nav_active('kategori', $page); ?>" href="index.php?page=kategori">Kategori</a>
            <a class="<?= nav_active('produk', $page); ?>" href="index.php?page=produk">Produk</a>
            <a class="<?= nav_active('varian', $page); ?>" href="index.php?page=varian">Varian</a>
            <a class="<?= nav_active('order_test', $page); ?>" href="index.php?page=order_test">Order Test</a>
        </div>
        <div class="small">Domain: <?= esc($APP_DOMAIN); ?> | DB: <?= esc($DB_NAME); ?></div>
    </div>
</div>

<div class="wrap">
    <?php if ($msg !== ''): ?>
        <div class="alert <?= esc($type); ?>"><?= esc($msg); ?></div>
    <?php endif; ?>

    <?php if ($page === 'dashboard'): ?>
        <div class="grid grid-4">
            <div class="card"><div class="small">Total Pelanggan</div><div class="metric"><?= esc((string) $stats['pelanggan']); ?></div></div>
            <div class="card"><div class="small">Total Produk</div><div class="metric"><?= esc((string) $stats['produk']); ?></div></div>
            <div class="card"><div class="small">Total Pesanan</div><div class="metric"><?= esc((string) $stats['pesanan']); ?></div></div>
            <div class="card"><div class="small">Pesanan Pending</div><div class="metric"><?= esc((string) $stats['pending']); ?></div></div>
        </div>

        <div class="grid grid-4">
            <div class="card"><div class="small">Kas Masuk</div><div class="metric">Rp <?= number_format((float) ($kas['total_masuk'] ?? 0), 0, ',', '.'); ?></div></div>
            <div class="card"><div class="small">Kas Keluar</div><div class="metric">Rp <?= number_format((float) ($kas['total_keluar'] ?? 0), 0, ',', '.'); ?></div></div>
            <div class="card"><div class="small">Saldo Akhir</div><div class="metric">Rp <?= number_format((float) ($kas['saldo_akhir'] ?? 0), 0, ',', '.'); ?></div></div>
        </div>

        <div class="card">
            <h3>Pesanan Terbaru</h3>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Invoice</th><th>Pelanggan</th><th>Total</th><th>Status</th><th>Bayar</th></tr></thead>
                    <tbody>
                    <?php foreach ($latestOrders as $row): ?>
                        <tr>
                            <td><?= esc($row['no_invoice']); ?></td>
                            <td><?= esc($row['nama_pelanggan']); ?></td>
                            <td>Rp <?= number_format((float) $row['grand_total'], 0, ',', '.'); ?></td>
                            <td><?= esc($row['status']); ?></td>
                            <td><?= esc($row['status_bayar']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h3>Pembayaran Terbaru</h3>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Invoice</th><th>Jumlah</th><th>Metode</th></tr></thead>
                    <tbody>
                    <?php foreach ($latestPay as $row): ?>
                        <tr>
                            <td><?= esc($row['no_invoice']); ?></td>
                            <td>Rp <?= number_format((float) $row['jumlah'], 0, ',', '.'); ?></td>
                            <td><?= esc($row['metode']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($page === 'pelanggan'): ?>
        <div class="card">
            <h3>Tambah Pelanggan</h3>
            <form method="post">
                <input type="hidden" name="action" value="add_pelanggan">
                <div class="grid grid-4">
                    <div><label>Nama</label><input name="nama" required></div>
                    <div><label>No WA</label><input name="no_wa" required></div>
                    <div><label>Tipe</label><select name="tipe"><option value="retail">retail</option><option value="reseller">reseller</option></select></div>
                    <div><label>Zona</label><select name="id_zona"><option value="">- Pilih Zona -</option><?php foreach ($zones as $z): ?><option value="<?= (int) $z['id_zona']; ?>"><?= esc($z['nama_zona']); ?></option><?php endforeach; ?></select></div>
                </div>
                <div class="grid" style="margin-top:8px;grid-template-columns:1fr 220px;gap:8px;">
                    <div><label>Alamat</label><input name="alamat" required></div>
                    <div><label>&nbsp;</label><button type="submit">Simpan</button></div>
                </div>
                <div style="margin-top:8px;"><label>Catatan</label><textarea name="catatan"></textarea></div>
            </form>
        </div>

        <div class="card">
            <h3>Data Pelanggan</h3>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID</th><th>Nama</th><th>WA</th><th>Tipe</th><th>Zona</th><th>Alamat</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($pelangganRows as $row): ?>
                        <tr>
                            <td><?= (int) $row['id_pelanggan']; ?></td>
                            <td><?= esc($row['nama']); ?></td>
                            <td><?= esc($row['no_wa']); ?></td>
                            <td><?= esc($row['tipe']); ?></td>
                            <td><?= esc($row['nama_zona']); ?></td>
                            <td><?= esc($row['alamat']); ?></td>
                            <td class="row-actions">
                                <details>
                                    <summary>Edit</summary>
                                    <form method="post" style="margin-top:6px;display:grid;gap:6px;">
                                        <input type="hidden" name="action" value="edit_pelanggan">
                                        <input type="hidden" name="id" value="<?= (int) $row['id_pelanggan']; ?>">
                                        <input name="nama" value="<?= esc($row['nama']); ?>" required>
                                        <input name="no_wa" value="<?= esc($row['no_wa']); ?>" required>
                                        <input name="alamat" value="<?= esc($row['alamat']); ?>" required>
                                        <select name="tipe"><option value="retail" <?= $row['tipe'] === 'retail' ? 'selected' : ''; ?>>retail</option><option value="reseller" <?= $row['tipe'] === 'reseller' ? 'selected' : ''; ?>>reseller</option></select>
                                        <select name="id_zona"><option value="">- Pilih Zona -</option><?php foreach ($zones as $z): ?><option value="<?= (int) $z['id_zona']; ?>" <?= ((int) $row['id_zona'] === (int) $z['id_zona']) ? 'selected' : ''; ?>><?= esc($z['nama_zona']); ?></option><?php endforeach; ?></select>
                                        <textarea name="catatan"><?= esc($row['catatan']); ?></textarea>
                                        <button type="submit">Update</button>
                                    </form>
                                </details>
                                <form method="post" onsubmit="return confirm('Yakin hapus pelanggan ini?')">
                                    <input type="hidden" name="action" value="delete_pelanggan">
                                    <input type="hidden" name="id" value="<?= (int) $row['id_pelanggan']; ?>">
                                    <button type="submit" class="btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($page === 'kategori'): ?>
        <div class="card">
            <h3>Tambah Kategori</h3>
            <form method="post">
                <input type="hidden" name="action" value="add_kategori">
                <div class="grid" style="grid-template-columns:1fr 1fr 180px;gap:8px;">
                    <div><label>Nama Kategori</label><input name="nama_kategori" required></div>
                    <div><label>Deskripsi</label><input name="deskripsi"></div>
                    <div><label>&nbsp;</label><button type="submit">Simpan</button></div>
                </div>
            </form>
        </div>

        <div class="card">
            <h3>Data Kategori</h3>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID</th><th>Nama</th><th>Deskripsi</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($kategoriRows as $row): ?>
                        <tr>
                            <td><?= (int) $row['id_kategori']; ?></td>
                            <td><?= esc($row['nama_kategori']); ?></td>
                            <td><?= esc($row['deskripsi']); ?></td>
                            <td class="row-actions">
                                <details>
                                    <summary>Edit</summary>
                                    <form method="post" style="margin-top:6px;display:grid;gap:6px;">
                                        <input type="hidden" name="action" value="edit_kategori">
                                        <input type="hidden" name="id" value="<?= (int) $row['id_kategori']; ?>">
                                        <input name="nama_kategori" value="<?= esc($row['nama_kategori']); ?>" required>
                                        <input name="deskripsi" value="<?= esc($row['deskripsi']); ?>">
                                        <button type="submit">Update</button>
                                    </form>
                                </details>
                                <form method="post" onsubmit="return confirm('Yakin hapus kategori ini?')">
                                    <input type="hidden" name="action" value="delete_kategori">
                                    <input type="hidden" name="id" value="<?= (int) $row['id_kategori']; ?>">
                                    <button type="submit" class="btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($page === 'produk'): ?>
        <div class="card">
            <h3>Tambah Produk</h3>
            <form method="post">
                <input type="hidden" name="action" value="add_produk">
                <div class="grid grid-4">
                    <div><label>Kategori</label><select name="id_kategori" required><?php foreach ($kategoriRows as $k): ?><option value="<?= (int) $k['id_kategori']; ?>"><?= esc($k['nama_kategori']); ?></option><?php endforeach; ?></select></div>
                    <div><label>Nama Produk</label><input name="nama_produk" required></div>
                    <div><label>Shelf Life (hari)</label><input type="number" name="shelf_life" min="1" value="7"></div>
                    <div><label>Status</label><select name="status"><option value="tersedia">tersedia</option><option value="tidak_tersedia">tidak_tersedia</option></select></div>
                </div>
                <div style="margin-top:8px;"><label>Deskripsi</label><textarea name="deskripsi"></textarea></div>
                <div style="margin-top:8px;"><button type="submit">Simpan</button></div>
            </form>
        </div>

        <div class="card">
            <h3>Data Produk</h3>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID</th><th>Nama</th><th>Kategori</th><th>Shelf</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($produkRows as $row): ?>
                        <tr>
                            <td><?= (int) $row['id_produk']; ?></td>
                            <td><?= esc($row['nama_produk']); ?></td>
                            <td><?= esc($row['nama_kategori']); ?></td>
                            <td><?= (int) $row['shelf_life']; ?></td>
                            <td><?= esc($row['status']); ?></td>
                            <td class="row-actions">
                                <details>
                                    <summary>Edit</summary>
                                    <form method="post" style="margin-top:6px;display:grid;gap:6px;">
                                        <input type="hidden" name="action" value="edit_produk">
                                        <input type="hidden" name="id" value="<?= (int) $row['id_produk']; ?>">
                                        <select name="id_kategori"><?php foreach ($kategoriRows as $k): ?><option value="<?= (int) $k['id_kategori']; ?>" <?= ((int) $row['id_kategori'] === (int) $k['id_kategori']) ? 'selected' : ''; ?>><?= esc($k['nama_kategori']); ?></option><?php endforeach; ?></select>
                                        <input name="nama_produk" value="<?= esc($row['nama_produk']); ?>" required>
                                        <input type="number" name="shelf_life" min="1" value="<?= (int) $row['shelf_life']; ?>">
                                        <select name="status"><option value="tersedia" <?= $row['status'] === 'tersedia' ? 'selected' : ''; ?>>tersedia</option><option value="tidak_tersedia" <?= $row['status'] === 'tidak_tersedia' ? 'selected' : ''; ?>>tidak_tersedia</option></select>
                                        <textarea name="deskripsi"><?= esc($row['deskripsi']); ?></textarea>
                                        <button type="submit">Update</button>
                                    </form>
                                </details>
                                <form method="post" onsubmit="return confirm('Yakin hapus produk ini?')">
                                    <input type="hidden" name="action" value="delete_produk">
                                    <input type="hidden" name="id" value="<?= (int) $row['id_produk']; ?>">
                                    <button type="submit" class="btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($page === 'varian'): ?>
        <div class="card">
            <h3>Tambah Varian</h3>
            <form method="post">
                <input type="hidden" name="action" value="add_varian">
                <div class="grid grid-4">
                    <div><label>Produk</label><select name="id_produk" required><?php foreach ($produkRows as $p): ?><option value="<?= (int) $p['id_produk']; ?>"><?= esc($p['nama_produk']); ?></option><?php endforeach; ?></select></div>
                    <div><label>Nama Varian</label><input name="nama_varian" required></div>
                    <div><label>Harga</label><input type="number" step="0.01" name="harga" min="0" required></div>
                    <div><label>Min Order</label><input type="number" name="min_order" min="1" value="1"></div>
                </div>
                <div class="grid" style="grid-template-columns:240px 1fr;margin-top:8px;">
                    <div><label>Stok</label><input type="number" name="stok" min="0" value="0"></div>
                    <div><label>&nbsp;</label><button type="submit">Simpan</button></div>
                </div>
            </form>
        </div>

        <div class="card">
            <h3>Data Varian</h3>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID</th><th>Produk</th><th>Varian</th><th>Harga</th><th>Min</th><th>Stok</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($varianRows as $row): ?>
                        <tr>
                            <td><?= (int) $row['id_varian']; ?></td>
                            <td><?= esc($row['nama_produk']); ?></td>
                            <td><?= esc($row['nama_varian']); ?></td>
                            <td><?= number_format((float) $row['harga'], 0, ',', '.'); ?></td>
                            <td><?= (int) $row['min_order']; ?></td>
                            <td><?= (int) $row['stok']; ?></td>
                            <td class="row-actions">
                                <details>
                                    <summary>Edit</summary>
                                    <form method="post" style="margin-top:6px;display:grid;gap:6px;">
                                        <input type="hidden" name="action" value="edit_varian">
                                        <input type="hidden" name="id" value="<?= (int) $row['id_varian']; ?>">
                                        <select name="id_produk"><?php foreach ($produkRows as $p): ?><option value="<?= (int) $p['id_produk']; ?>" <?= ((int) $row['id_produk'] === (int) $p['id_produk']) ? 'selected' : ''; ?>><?= esc($p['nama_produk']); ?></option><?php endforeach; ?></select>
                                        <input name="nama_varian" value="<?= esc($row['nama_varian']); ?>" required>
                                        <input type="number" step="0.01" name="harga" value="<?= esc((string) $row['harga']); ?>" min="0" required>
                                        <input type="number" name="min_order" value="<?= (int) $row['min_order']; ?>" min="1">
                                        <input type="number" name="stok" value="<?= (int) $row['stok']; ?>" min="0">
                                        <button type="submit">Update</button>
                                    </form>
                                </details>
                                <form method="post" onsubmit="return confirm('Yakin hapus varian ini?')">
                                    <input type="hidden" name="action" value="delete_varian">
                                    <input type="hidden" name="id" value="<?= (int) $row['id_varian']; ?>">
                                    <button type="submit" class="btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($page === 'order_test'): ?>
        <div class="card">
            <h3>Order Test (Trigger, Stored Procedure, Konsistensi Data)</h3>
            <form method="post">
                <input type="hidden" name="action" value="run_order_test">
                <div class="grid grid-4">
                    <div>
                        <label>Pelanggan</label>
                        <select name="id_pelanggan" required>
                            <option value="">- Pilih Pelanggan -</option>
                            <?php foreach ($orderPelangganRows as $pel): ?>
                                <option value="<?= (int) $pel['id_pelanggan']; ?>"><?= esc($pel['nama']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label>Varian</label>
                        <select name="id_varian" required>
                            <option value="">- Pilih Varian -</option>
                            <?php foreach ($orderVarianRows as $v): ?>
                                <option value="<?= (int) $v['id_varian']; ?>"><?= esc($v['nama_produk']); ?> - <?= esc($v['nama_varian']); ?> | stok <?= (int) $v['stok']; ?> | min <?= (int) $v['min_order']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label>Qty</label>
                        <input type="number" name="qty" min="1" value="10" required>
                    </div>
                    <div>
                        <label>Metode Bayar</label>
                        <select name="metode_bayar">
                            <option value="transfer">transfer</option>
                            <option value="cash">cash</option>
                        </select>
                    </div>
                </div>
                <div class="grid" style="grid-template-columns:1fr 220px 220px;margin-top:8px;">
                    <div>
                        <label>Tanggal Kirim</label>
                        <input type="date" name="tgl_kirim" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div>
                        <label>Waktu Kirim</label>
                        <input type="time" name="waktu_kirim" value="09:00" required>
                    </div>
                    <div style="display:flex;align-items:flex-end;">
                        <label style="display:flex;gap:8px;align-items:center;margin:0;">
                            <input type="checkbox" name="keep_data" value="1" style="width:auto;">
                            Simpan hasil test ke DB
                        </label>
                    </div>
                </div>
                <div style="margin-top:8px;">
                    <label>Catatan</label>
                    <textarea name="catatan">Order test otomatis dari dashboard</textarea>
                </div>
                <div style="margin-top:8px;">
                    <button type="submit">Jalankan Test Order</button>
                </div>
            </form>
        </div>

        <?php if (is_array($orderTestResult)): ?>
            <div class="card">
                <h3>Hasil Test Order</h3>
                <div class="grid grid-4">
                    <div><div class="small">Status</div><div class="metric" style="font-size:1.2rem;color:<?= $orderTestResult['ok'] ? '#86efac' : '#fca5a5'; ?>;"><?= $orderTestResult['ok'] ? 'PASS' : 'FAIL'; ?></div></div>
                    <div><div class="small">Invoice</div><div><?= esc($orderTestResult['no_invoice']); ?></div></div>
                    <div><div class="small">Durasi</div><div><?= (int) $orderTestResult['duration_ms']; ?> ms</div></div>
                    <div><div class="small">Mode Data</div><div><?= $orderTestResult['keep_data'] ? 'COMMIT (tersimpan)' : 'ROLLBACK (tidak tersimpan)'; ?></div></div>
                </div>
                <div class="table-wrap" style="margin-top:12px;">
                    <table>
                        <thead><tr><th>Status</th><th>Pengecekan</th><th>Expected</th><th>Actual</th></tr></thead>
                        <tbody>
                        <?php foreach ($orderTestResult['logs'] as $log): ?>
                            <tr>
                                <td><?= $log['ok'] ? 'OK' : 'ERROR'; ?></td>
                                <td><?= esc($log['nama']); ?></td>
                                <td><?= esc((string) $log['expected']); ?></td>
                                <td><?= esc((string) $log['actual']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (!$orderTestResult['ok']): ?>
                    <div class="alert danger" style="margin-top:10px;">
                        Ditemukan anomali pada fungsi database. Silakan cek baris status ERROR pada log untuk mengetahui trigger/procedure mana yang tidak sesuai.
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h3>Riwayat Pesanan Terbaru</h3>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID</th><th>Invoice</th><th>Tanggal</th><th>Total</th><th>Status</th><th>Bayar</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentOrderTests as $row): ?>
                        <tr>
                            <td><?= (int) $row['id_pesanan']; ?></td>
                            <td><?= esc($row['no_invoice']); ?></td>
                            <td><?= esc($row['tgl_pesan']); ?></td>
                            <td>Rp <?= number_format((float) $row['grand_total'], 0, ',', '.'); ?></td>
                            <td><?= esc($row['status']); ?></td>
                            <td><?= esc($row['status_bayar']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
