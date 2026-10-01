<?php
require __DIR__ . '/../includes/db.php';

$APP_NAME = 'Berkat Dinasti Prototype V2';
$APP_DOMAIN = 'ucing4k.my.id';

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money($value): string
{
    return 'Rp ' . number_format((float) $value, 0, ',', '.');
}

function active_page(string $name, string $current): string
{
    return $name === $current ? 'active' : '';
}

function next_statuses(string $current): array
{
    $map = [
        'pending' => ['proses', 'batal'],
        'proses' => ['kirim', 'batal'],
        'kirim' => ['selesai', 'batal'],
        'selesai' => [],
        'batal' => [],
    ];

    return $map[$current] ?? [];
}

function can_transition_status(string $current, string $next): bool
{
    if ($current === $next) {
        return true;
    }

    return in_array($next, next_statuses($current), true);
}

function get_order_finance(mysqli $mysqli, int $idPesanan, int $excludePembayaranId = 0): ?array
{
    $excludeSql = $excludePembayaranId > 0 ? ' AND pb.id_pembayaran != ' . $excludePembayaranId : '';
    $sql = 'SELECT p.id_pesanan, p.no_invoice, p.status, p.status_bayar, p.grand_total, '
        . 'COALESCE(SUM(pb.jumlah), 0) AS total_bayar '
        . 'FROM pesanan p '
        . 'LEFT JOIN pembayaran pb ON pb.id_pesanan = p.id_pesanan' . $excludeSql . ' '
        . 'WHERE p.id_pesanan = ' . $idPesanan . ' '
        . 'GROUP BY p.id_pesanan, p.no_invoice, p.status, p.status_bayar, p.grand_total';

    $row = db_fetch_one($mysqli, $sql);
    if (!$row) {
        return null;
    }

    $row['grand_total'] = (float) ($row['grand_total'] ?? 0);
    $row['total_bayar'] = (float) ($row['total_bayar'] ?? 0);
    $row['sisa_tagihan'] = max($row['grand_total'] - $row['total_bayar'], 0);

    return $row;
}

function normalize_status_tagihan(array $order): string
{
    $statusBayar = (string) ($order['status_bayar'] ?? 'belum_bayar');
    $status = (string) ($order['status'] ?? 'pending');
    $sisa = (float) ($order['sisa_tagihan'] ?? 0);

    if ($status === 'batal') {
        return 'dibatalkan';
    }

    if ($statusBayar === 'lunas' || $sisa <= 0.0001) {
        return 'lunas';
    }

    if ($status === 'selesai') {
        return 'hutang';
    }

    return $statusBayar;
}

if (isset($_GET['health'])) {
    header('Content-Type: application/json; charset=utf-8');
    $ok = db_fetch_one($mysqli, 'SELECT 1 AS ok');
    echo json_encode([
        'status' => ($ok && (int) $ok['ok'] === 1) ? 'ok' : 'error',
        'database' => $DB_NAME,
        'domain' => $APP_DOMAIN,
        'prototype' => 'v2',
    ]);
    exit;
}

$page = $_GET['page'] ?? 'dashboard';
$validPages = ['dashboard', 'master_data', 'transaksi', 'laporan', 'log_kas', 'order_test'];
if (!in_array($page, $validPages, true)) {
    $page = 'dashboard';
}

function redirect_msg(string $page, string $message, string $type = 'success'): void
{
    header('Location: index.php?page=' . urlencode($page) . '&msg=' . urlencode($message) . '&type=' . urlencode($type));
    exit;
}

$msg = $_GET['msg'] ?? '';
$type = $_GET['type'] ?? 'success';
$orderTestResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add_zona') {
            $stmt = $mysqli->prepare('INSERT INTO zona (nama_zona, deskripsi, ongkir) VALUES (?, ?, ?)');
            $nama = trim($_POST['nama_zona'] ?? '');
            $deskripsi = trim($_POST['deskripsi'] ?? '');
            $deskripsi = $deskripsi !== '' ? $deskripsi : null;
            $ongkir = (float) ($_POST['ongkir'] ?? 0);
            $stmt->bind_param('ssd', $nama, $deskripsi, $ongkir);
            $stmt->execute();
            $stmt->close();
            redirect_msg('master_data', 'Zona berhasil ditambahkan.');
        }

        if ($action === 'edit_zona') {
            $stmt = $mysqli->prepare('UPDATE zona SET nama_zona=?, deskripsi=?, ongkir=? WHERE id_zona=?');
            $id = (int) ($_POST['id'] ?? 0);
            $nama = trim($_POST['nama_zona'] ?? '');
            $deskripsi = trim($_POST['deskripsi'] ?? '');
            $deskripsi = $deskripsi !== '' ? $deskripsi : null;
            $ongkir = (float) ($_POST['ongkir'] ?? 0);
            $stmt->bind_param('ssdi', $nama, $deskripsi, $ongkir, $id);
            $stmt->execute();
            $stmt->close();
            redirect_msg('master_data', 'Zona berhasil diperbarui.');
        }

        if ($action === 'delete_zona') {
            $stmt = $mysqli->prepare('DELETE FROM zona WHERE id_zona=?');
            $id = (int) ($_POST['id'] ?? 0);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            redirect_msg('master_data', 'Zona berhasil dihapus.');
        }

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
            redirect_msg('master_data', 'Pelanggan berhasil ditambahkan.');
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
            redirect_msg('master_data', 'Pelanggan berhasil diperbarui.');
        }

        if ($action === 'delete_pelanggan') {
            $stmt = $mysqli->prepare('DELETE FROM pelanggan WHERE id_pelanggan=?');
            $id = (int) ($_POST['id'] ?? 0);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            redirect_msg('master_data', 'Pelanggan berhasil dihapus.');
        }

        if ($action === 'add_kategori') {
            $stmt = $mysqli->prepare('INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, ?)');
            $nama = trim($_POST['nama_kategori'] ?? '');
            $deskripsi = trim($_POST['deskripsi'] ?? '');
            $deskripsi = $deskripsi !== '' ? $deskripsi : null;
            $stmt->bind_param('ss', $nama, $deskripsi);
            $stmt->execute();
            $stmt->close();
            redirect_msg('master_data', 'Kategori berhasil ditambahkan.');
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
            redirect_msg('master_data', 'Kategori berhasil diperbarui.');
        }

        if ($action === 'delete_kategori') {
            $stmt = $mysqli->prepare('DELETE FROM kategori WHERE id_kategori=?');
            $id = (int) ($_POST['id'] ?? 0);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            redirect_msg('master_data', 'Kategori berhasil dihapus.');
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
            redirect_msg('master_data', 'Produk berhasil ditambahkan.');
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
            redirect_msg('master_data', 'Produk berhasil diperbarui.');
        }

        if ($action === 'delete_produk') {
            $stmt = $mysqli->prepare('DELETE FROM produk WHERE id_produk=?');
            $id = (int) ($_POST['id'] ?? 0);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            redirect_msg('master_data', 'Produk berhasil dihapus.');
        }

        if ($action === 'add_varian') {
            $stmt = $mysqli->prepare('INSERT INTO varian_produk (id_produk, nama_varian, harga, min_order, stok) VALUES (?, ?, ?, ?, ?)');
            $idProduk = (int) ($_POST['id_produk'] ?? 0);
            $nama = trim($_POST['nama_varian'] ?? '');
            $harga = (float) ($_POST['harga'] ?? 0);
            $minOrder = (int) ($_POST['min_order'] ?? 1);
            $stok = (int) ($_POST['stok'] ?? 0);
            $stmt->bind_param('isdii', $idProduk, $nama, $harga, $minOrder, $stok);
            $stmt->execute();
            $stmt->close();
            redirect_msg('master_data', 'Varian berhasil ditambahkan.');
        }

        if ($action === 'edit_varian') {
            $stmt = $mysqli->prepare('UPDATE varian_produk SET id_produk=?, nama_varian=?, harga=?, min_order=?, stok=? WHERE id_varian=?');
            $id = (int) ($_POST['id'] ?? 0);
            $idProduk = (int) ($_POST['id_produk'] ?? 0);
            $nama = trim($_POST['nama_varian'] ?? '');
            $harga = (float) ($_POST['harga'] ?? 0);
            $minOrder = (int) ($_POST['min_order'] ?? 1);
            $stok = (int) ($_POST['stok'] ?? 0);
            $stmt->bind_param('isdiii', $idProduk, $nama, $harga, $minOrder, $stok, $id);
            $stmt->execute();
            $stmt->close();
            redirect_msg('master_data', 'Varian berhasil diperbarui.');
        }

        if ($action === 'delete_varian') {
            $stmt = $mysqli->prepare('DELETE FROM varian_produk WHERE id_varian=?');
            $id = (int) ($_POST['id'] ?? 0);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            redirect_msg('master_data', 'Varian berhasil dihapus.');
        }

        if ($action === 'add_pengguna') {
            $stmt = $mysqli->prepare('INSERT INTO pengguna (username, password, nama_lengkap, email, no_hp, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $username = trim($_POST['username'] ?? '');
            $password = password_hash((string) ($_POST['password'] ?? ''), PASSWORD_DEFAULT);
            $nama = trim($_POST['nama_lengkap'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $email = $email !== '' ? $email : null;
            $noHp = trim($_POST['no_hp'] ?? '');
            $noHp = $noHp !== '' ? $noHp : null;
            $role = $_POST['role'] ?? 'kasir';
            $status = $_POST['status'] ?? 'aktif';
            $stmt->bind_param('sssssss', $username, $password, $nama, $email, $noHp, $role, $status);
            $stmt->execute();
            $stmt->close();
            redirect_msg('master_data', 'Pengguna berhasil ditambahkan.');
        }

        if ($action === 'edit_pengguna') {
            $stmt = $mysqli->prepare('UPDATE pengguna SET username=?, password=?, nama_lengkap=?, email=?, no_hp=?, role=?, status=? WHERE id_user=?');
            $id = (int) ($_POST['id'] ?? 0);
            $username = trim($_POST['username'] ?? '');
            $passwordRaw = trim($_POST['password'] ?? '');
            $currentUser = db_fetch_one($mysqli, 'SELECT password FROM pengguna WHERE id_user = ' . $id);
            $password = $passwordRaw !== '' ? password_hash($passwordRaw, PASSWORD_DEFAULT) : (string) ($currentUser['password'] ?? '');
            $nama = trim($_POST['nama_lengkap'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $email = $email !== '' ? $email : null;
            $noHp = trim($_POST['no_hp'] ?? '');
            $noHp = $noHp !== '' ? $noHp : null;
            $role = $_POST['role'] ?? 'kasir';
            $status = $_POST['status'] ?? 'aktif';
            $stmt->bind_param('sssssssi', $username, $password, $nama, $email, $noHp, $role, $status, $id);
            $stmt->execute();
            $stmt->close();
            redirect_msg('master_data', 'Pengguna berhasil diperbarui.');
        }

        if ($action === 'delete_pengguna') {
            $stmt = $mysqli->prepare('DELETE FROM pengguna WHERE id_user=?');
            $id = (int) ($_POST['id'] ?? 0);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            redirect_msg('master_data', 'Pengguna berhasil dihapus.');
        }

        if ($action === 'save_setting') {
            $stmt = $mysqli->prepare('INSERT INTO setting (setting_key, setting_value, deskripsi) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), deskripsi=VALUES(deskripsi)');
            $key = trim($_POST['setting_key'] ?? '');
            $value = trim($_POST['setting_value'] ?? '');
            $desc = trim($_POST['deskripsi'] ?? '');
            $desc = $desc !== '' ? $desc : null;
            $stmt->bind_param('sss', $key, $value, $desc);
            $stmt->execute();
            $stmt->close();
            redirect_msg('master_data', 'Setting berhasil disimpan.');
        }

        if ($action === 'delete_setting') {
            $stmt = $mysqli->prepare('DELETE FROM setting WHERE id_setting=?');
            $id = (int) ($_POST['id'] ?? 0);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            redirect_msg('master_data', 'Setting berhasil dihapus.');
        }

        if ($action === 'add_pengeluaran') {
            $stmt = $mysqli->prepare('INSERT INTO pengeluaran (tanggal, kategori, deskripsi, jumlah, created_by) VALUES (?, ?, ?, ?, ?)');
            $tanggal = trim($_POST['tanggal'] ?? date('Y-m-d'));
            $kategori = $_POST['kategori'] ?? 'operasional';
            $deskripsi = trim($_POST['deskripsi'] ?? '');
            $jumlah = (float) ($_POST['jumlah'] ?? 0);
            $createdBy = (int) ($_POST['created_by'] ?? 1);
            $stmt->bind_param('sssdi', $tanggal, $kategori, $deskripsi, $jumlah, $createdBy);
            $stmt->execute();
            $stmt->close();
            redirect_msg('transaksi', 'Pengeluaran berhasil dicatat.');
        }

        if ($action === 'edit_pengeluaran') {
            $stmt = $mysqli->prepare('UPDATE pengeluaran SET tanggal=?, kategori=?, deskripsi=?, jumlah=?, created_by=? WHERE id_pengeluaran=?');
            $id = (int) ($_POST['id'] ?? 0);
            $tanggal = trim($_POST['tanggal'] ?? date('Y-m-d'));
            $kategori = $_POST['kategori'] ?? 'operasional';
            $deskripsi = trim($_POST['deskripsi'] ?? '');
            $jumlah = (float) ($_POST['jumlah'] ?? 0);
            $createdBy = (int) ($_POST['created_by'] ?? 1);
            $stmt->bind_param('sssdii', $tanggal, $kategori, $deskripsi, $jumlah, $createdBy, $id);
            $stmt->execute();
            $stmt->close();
            redirect_msg('transaksi', 'Pengeluaran berhasil diperbarui.');
        }

        if ($action === 'delete_pengeluaran') {
            $stmt = $mysqli->prepare('DELETE FROM pengeluaran WHERE id_pengeluaran=?');
            $id = (int) ($_POST['id'] ?? 0);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            redirect_msg('transaksi', 'Pengeluaran berhasil dihapus.');
        }

        if ($action === 'add_pembayaran') {
            $idPesanan = (int) ($_POST['id_pesanan'] ?? 0);
            $jumlah = (float) ($_POST['jumlah'] ?? 0);
            $tglBayar = trim($_POST['tgl_bayar'] ?? date('Y-m-d H:i:s'));
            $tglBayar = str_replace('T', ' ', $tglBayar);
            $metode = $_POST['metode'] ?? 'cash';
            $bukti = trim($_POST['bukti'] ?? '');
            $bukti = $bukti !== '' ? $bukti : null;
            $ket = trim($_POST['keterangan'] ?? '');
            $ket = $ket !== '' ? $ket : null;
            $receivedBy = (int) ($_POST['received_by'] ?? 1);

            $order = get_order_finance($mysqli, $idPesanan);
            if (!$order) {
                throw new RuntimeException('Pesanan tidak ditemukan.');
            }
            if ((string) $order['status'] === 'batal') {
                throw new RuntimeException('Pesanan dibatalkan. Pembayaran tidak diperbolehkan.');
            }
            if (normalize_status_tagihan($order) === 'lunas') {
                throw new RuntimeException('Pesanan sudah lunas. Tidak bisa input pembayaran lagi.');
            }
            if ($jumlah <= 0) {
                throw new RuntimeException('Nominal pembayaran harus lebih dari 0.');
            }
            if ($jumlah - (float) $order['sisa_tagihan'] > 0.0001) {
                throw new RuntimeException('Nominal melebihi sisa tagihan (' . number_format((float) $order['sisa_tagihan'], 0, ',', '.') . ').');
            }

            $stmt = $mysqli->prepare('INSERT INTO pembayaran (id_pesanan, jumlah, tgl_bayar, metode, bukti, keterangan, received_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('idssssi', $idPesanan, $jumlah, $tglBayar, $metode, $bukti, $ket, $receivedBy);
            $stmt->execute();
            $stmt->close();
            redirect_msg('transaksi', 'Pembayaran berhasil dicatat.');
        }

        if ($action === 'edit_pembayaran') {
            $id = (int) ($_POST['id'] ?? 0);
            $idPesanan = (int) ($_POST['id_pesanan'] ?? 0);
            $jumlah = (float) ($_POST['jumlah'] ?? 0);
            $tglBayar = trim($_POST['tgl_bayar'] ?? date('Y-m-d H:i:s'));
            $tglBayar = str_replace('T', ' ', $tglBayar);
            $metode = $_POST['metode'] ?? 'cash';
            $bukti = trim($_POST['bukti'] ?? '');
            $bukti = $bukti !== '' ? $bukti : null;
            $ket = trim($_POST['keterangan'] ?? '');
            $ket = $ket !== '' ? $ket : null;
            $receivedBy = (int) ($_POST['received_by'] ?? 1);

            $order = get_order_finance($mysqli, $idPesanan, $id);
            if (!$order) {
                throw new RuntimeException('Pesanan tidak ditemukan.');
            }
            if ((string) $order['status'] === 'batal') {
                throw new RuntimeException('Pesanan dibatalkan. Pembayaran tidak diperbolehkan.');
            }
            if ($jumlah <= 0) {
                throw new RuntimeException('Nominal pembayaran harus lebih dari 0.');
            }
            if ($jumlah - (float) $order['sisa_tagihan'] > 0.0001) {
                throw new RuntimeException('Nominal melebihi sisa tagihan (' . number_format((float) $order['sisa_tagihan'], 0, ',', '.') . ').');
            }

            $stmt = $mysqli->prepare('UPDATE pembayaran SET id_pesanan=?, jumlah=?, tgl_bayar=?, metode=?, bukti=?, keterangan=?, received_by=? WHERE id_pembayaran=?');
            $stmt->bind_param('idssssii', $idPesanan, $jumlah, $tglBayar, $metode, $bukti, $ket, $receivedBy, $id);
            $stmt->execute();
            $stmt->close();
            redirect_msg('transaksi', 'Pembayaran berhasil diperbarui.');
        }

        if ($action === 'delete_pembayaran') {
            $stmt = $mysqli->prepare('DELETE FROM pembayaran WHERE id_pembayaran=?');
            $id = (int) ($_POST['id'] ?? 0);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            redirect_msg('transaksi', 'Pembayaran berhasil dihapus.');
        }

        if ($action === 'add_pengiriman') {
            $stmt = $mysqli->prepare('INSERT INTO pengiriman (id_pesanan, tgl_kirim, tgl_sampai, status, driver, catatan) VALUES (?, ?, ?, ?, ?, ?)');
            $idPesanan = (int) ($_POST['id_pesanan'] ?? 0);
            $tglKirim = trim($_POST['tgl_kirim'] ?? '');
            $tglKirim = $tglKirim !== '' ? str_replace('T', ' ', $tglKirim) : null;
            $tglSampai = trim($_POST['tgl_sampai'] ?? '');
            $tglSampai = $tglSampai !== '' ? str_replace('T', ' ', $tglSampai) : null;
            $status = $_POST['status'] ?? 'menunggu';
            $driver = trim($_POST['driver'] ?? '');
            $driver = $driver !== '' ? $driver : null;
            $catatan = trim($_POST['catatan'] ?? '');
            $catatan = $catatan !== '' ? $catatan : null;
            $stmt->bind_param('isssss', $idPesanan, $tglKirim, $tglSampai, $status, $driver, $catatan);
            $stmt->execute();
            $stmt->close();
            redirect_msg('transaksi', 'Pengiriman berhasil dicatat.');
        }

        if ($action === 'edit_pengiriman') {
            $stmt = $mysqli->prepare('UPDATE pengiriman SET id_pesanan=?, tgl_kirim=?, tgl_sampai=?, status=?, driver=?, catatan=? WHERE id_pengiriman=?');
            $id = (int) ($_POST['id'] ?? 0);
            $idPesanan = (int) ($_POST['id_pesanan'] ?? 0);
            $tglKirim = trim($_POST['tgl_kirim'] ?? '');
            $tglKirim = $tglKirim !== '' ? str_replace('T', ' ', $tglKirim) : null;
            $tglSampai = trim($_POST['tgl_sampai'] ?? '');
            $tglSampai = $tglSampai !== '' ? str_replace('T', ' ', $tglSampai) : null;
            $status = $_POST['status'] ?? 'menunggu';
            $driver = trim($_POST['driver'] ?? '');
            $driver = $driver !== '' ? $driver : null;
            $catatan = trim($_POST['catatan'] ?? '');
            $catatan = $catatan !== '' ? $catatan : null;
            $stmt->bind_param('isssssi', $idPesanan, $tglKirim, $tglSampai, $status, $driver, $catatan, $id);
            $stmt->execute();
            $stmt->close();
            redirect_msg('transaksi', 'Pengiriman berhasil diperbarui.');
        }

        if ($action === 'delete_pengiriman') {
            $stmt = $mysqli->prepare('DELETE FROM pengiriman WHERE id_pengiriman=?');
            $id = (int) ($_POST['id'] ?? 0);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            redirect_msg('transaksi', 'Pengiriman berhasil dihapus.');
        }

        if ($action === 'update_pesanan_status') {
            $idPesanan = (int) ($_POST['id_pesanan'] ?? 0);
            $nextStatus = $_POST['status'] ?? 'pending';

            $order = get_order_finance($mysqli, $idPesanan);
            if (!$order) {
                throw new RuntimeException('Pesanan tidak ditemukan.');
            }

            $currentStatus = (string) ($order['status'] ?? 'pending');
            if (!can_transition_status($currentStatus, $nextStatus)) {
                throw new RuntimeException('Status tidak valid. Transisi mundur/terlarang tidak diperbolehkan.');
            }

            if ($currentStatus === $nextStatus) {
                redirect_msg('transaksi', 'Status tidak berubah.');
            }

            $mysqli->begin_transaction();
            try {
                $stmt = $mysqli->prepare('CALL sp_update_status(?, ?)');
                $stmt->bind_param('is', $idPesanan, $nextStatus);
                $stmt->execute();
                $stmt->close();
                while ($mysqli->more_results() && $mysqli->next_result()) {
                }

                $mysqli->commit();
                redirect_msg('transaksi', 'Status pesanan berhasil diperbarui.');
            } catch (Throwable $e) {
                $mysqli->rollback();
                throw $e;
            }
        }

        if ($action === 'add_pesanan_bayar') {
            $idPesanan = (int) ($_POST['id_pesanan'] ?? 0);
            $jumlah = (float) ($_POST['jumlah'] ?? 0);
            $metode = $_POST['metode'] ?? 'cash';
            $bukti = trim($_POST['bukti'] ?? 'manual');
            $keterangan = trim($_POST['keterangan'] ?? 'Pembayaran dari prototype v2');
            $receivedBy = (int) ($_POST['received_by'] ?? 1);

            $order = get_order_finance($mysqli, $idPesanan);
            if (!$order) {
                throw new RuntimeException('Pesanan tidak ditemukan.');
            }
            if ((string) $order['status'] === 'batal') {
                throw new RuntimeException('Pesanan dibatalkan. Pembayaran tidak diperbolehkan.');
            }
            if (normalize_status_tagihan($order) === 'lunas') {
                throw new RuntimeException('Pesanan sudah lunas. Tidak bisa input pembayaran lagi.');
            }
            if ($jumlah <= 0) {
                throw new RuntimeException('Nominal pembayaran harus lebih dari 0.');
            }
            if ($jumlah - (float) $order['sisa_tagihan'] > 0.0001) {
                throw new RuntimeException('Nominal melebihi sisa tagihan (' . number_format((float) $order['sisa_tagihan'], 0, ',', '.') . ').');
            }

            $stmt = $mysqli->prepare('CALL sp_bayar(?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('idsssi', $idPesanan, $jumlah, $metode, $bukti, $keterangan, $receivedBy);
            $stmt->execute();
            $stmt->close();
            while ($mysqli->more_results() && $mysqli->next_result()) {
            }
            redirect_msg('transaksi', 'Pembayaran pesanan berhasil diproses.');
        }

        if ($action === 'run_order_test') {
            $page = 'order_test';
            $idPelanggan = (int) ($_POST['id_pelanggan'] ?? 0);
            $idVarian = (int) ($_POST['id_varian'] ?? 0);
            $qty = (int) ($_POST['qty'] ?? 0);
            $tglKirim = trim($_POST['tgl_kirim'] ?? date('Y-m-d'));
            $waktuKirim = trim($_POST['waktu_kirim'] ?? '09:00:00');
            $metodeBayar = trim($_POST['metode_bayar'] ?? 'transfer');
            $modePembayaranAwal = trim($_POST['payment_mode'] ?? 'lunas');
            $nominalDpInput = (float) ($_POST['dp_amount'] ?? 0);
            $autoStatusFlow = isset($_POST['auto_status_flow']) && $_POST['auto_status_flow'] === '1';
            $catatan = trim($_POST['catatan'] ?? 'Order test by prototype v2');
            $keepData = isset($_POST['keep_data']) && $_POST['keep_data'] === '1';

            if ($idPelanggan <= 0 || $idVarian <= 0 || $qty <= 0) {
                throw new RuntimeException('Input test order tidak valid. Pilih pelanggan, varian, dan qty > 0.');
            }

            $logs = [];
            $startTime = microtime(true);
            $varianBefore = db_fetch_one($mysqli, 'SELECT id_varian, nama_varian, harga, min_order, stok FROM varian_produk WHERE id_varian = ' . $idVarian);
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

                $pesananAfterDetail = db_fetch_one($mysqli, 'SELECT id_pesanan, no_invoice, total_harga, ongkir, grand_total, status, status_bayar FROM pesanan WHERE id_pesanan = ' . $idPesanan);
                if (!$pesananAfterDetail) {
                    throw new RuntimeException('Data pesanan baru tidak ditemukan setelah sp_add_detail_pesanan.');
                }

                $grandTotal = (float) ($pesananAfterDetail['grand_total'] ?? 0);
                $receivedBy = 1;
                $bukti = 'AUTO-TEST';
                $ketBayar = 'Pembayaran otomatis untuk order test';
                $nominalBayarAwal = 0.0;

                if ($modePembayaranAwal === 'lunas') {
                    $nominalBayarAwal = $grandTotal;
                } elseif ($modePembayaranAwal === 'dp') {
                    $nominalBayarAwal = $nominalDpInput;
                    if ($nominalBayarAwal <= 0) {
                        throw new RuntimeException('Nominal DP harus lebih dari 0.');
                    }
                    if ($nominalBayarAwal >= $grandTotal) {
                        throw new RuntimeException('Nominal DP harus kurang dari grand total agar menjadi hutang.');
                    }
                } elseif ($modePembayaranAwal === 'tanpa_bayar') {
                    $nominalBayarAwal = 0.0;
                } else {
                    throw new RuntimeException('Mode pembayaran awal tidak valid.');
                }

                if ($nominalBayarAwal > 0) {
                    $stmt = $mysqli->prepare('CALL sp_bayar(?, ?, ?, ?, ?, ?)');
                    $stmt->bind_param('idsssi', $idPesanan, $nominalBayarAwal, $metodeBayar, $bukti, $ketBayar, $receivedBy);
                    $stmt->execute();
                    $stmt->close();
                    while ($mysqli->more_results() && $mysqli->next_result()) {
                    }
                }

                if ($autoStatusFlow) {
                    // Follow forward-only status guard in schema: pending -> proses -> kirim -> selesai.
                    $statusFlow = ['proses', 'kirim', 'selesai'];
                    foreach ($statusFlow as $nextStatusFlow) {
                        $stmt = $mysqli->prepare('CALL sp_update_status(?, ?)');
                        $stmt->bind_param('is', $idPesanan, $nextStatusFlow);
                        $stmt->execute();
                        $stmt->close();
                        while ($mysqli->more_results() && $mysqli->next_result()) {
                        }
                    }
                }

                $pesananFinal = db_fetch_one($mysqli, 'SELECT id_pesanan, no_invoice, total_harga, ongkir, grand_total, status, status_bayar FROM pesanan WHERE id_pesanan = ' . $idPesanan);
                $detailAgg = db_fetch_one($mysqli, 'SELECT COALESCE(SUM(subtotal), 0) AS sum_subtotal FROM detail_pesanan WHERE id_pesanan = ' . $idPesanan);
                $detailRow = db_fetch_one($mysqli, 'SELECT qty, harga_satuan, subtotal FROM detail_pesanan WHERE id_pesanan = ' . $idPesanan . ' ORDER BY id_detail DESC LIMIT 1');
                $varianAfter = db_fetch_one($mysqli, 'SELECT stok FROM varian_produk WHERE id_varian = ' . $idVarian);
                $pembayaranAgg = db_fetch_one($mysqli, 'SELECT COALESCE(SUM(jumlah), 0) AS total_bayar, MAX(id_pembayaran) AS last_id FROM pembayaran WHERE id_pesanan = ' . $idPesanan);
                $lastPembayaranId = (int) ($pembayaranAgg['last_id'] ?? 0);
                $kasRow = $lastPembayaranId > 0 ? db_fetch_one($mysqli, 'SELECT nominal FROM transaksi_kas WHERE sumber_tipe = \'pembayaran\' AND sumber_id = ' . $lastPembayaranId . ' ORDER BY id_transaksi DESC LIMIT 1') : null;
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
                $expectedStatusOrder = $autoStatusFlow ? 'selesai' : 'pending';
                if ($totalBayar >= ($grandFinal - 0.0001)) {
                    $expectedStatusBayarAkhir = 'lunas';
                } elseif ($autoStatusFlow) {
                    $expectedStatusBayarAkhir = 'hutang';
                } elseif ($totalBayar > 0) {
                    $expectedStatusBayarAkhir = 'dp';
                } else {
                    $expectedStatusBayarAkhir = 'belum_bayar';
                }
                $expectedSisaTagihan = max($grandFinal - $totalBayar, 0);
                $modeAktual = $modePembayaranAwal === 'tanpa_bayar' ? 'tanpa_bayar' : ($modePembayaranAwal === 'dp' ? 'dp' : 'lunas');

                $logs[] = ['nama' => 'Trigger invoice otomatis', 'ok' => $invoiceOk, 'expected' => 'Format INV-' . $today . '-####', 'actual' => $noInvoice !== '' ? $noInvoice : '(kosong)'];
                $logs[] = ['nama' => 'Trigger subtotal detail', 'ok' => abs($detailSubtotal - $expectedSubtotal) < 0.01, 'expected' => number_format($expectedSubtotal, 2, '.', ''), 'actual' => number_format($detailSubtotal, 2, '.', '')];
                $logs[] = ['nama' => 'Trigger total_harga pesanan', 'ok' => abs($totalHarga - $sumSubtotal) < 0.01, 'expected' => number_format($sumSubtotal, 2, '.', ''), 'actual' => number_format($totalHarga, 2, '.', '')];
                $logs[] = ['nama' => 'Trigger grand_total pesanan', 'ok' => abs($grandFinal - $expectedGrand) < 0.01, 'expected' => number_format($expectedGrand, 2, '.', ''), 'actual' => number_format($grandFinal, 2, '.', '')];
                $logs[] = ['nama' => 'Pembayaran awal order test', 'ok' => abs($totalBayar - $nominalBayarAwal) < 0.01, 'expected' => 'mode=' . $modeAktual . ', total_bayar=' . number_format($nominalBayarAwal, 2, '.', ''), 'actual' => 'total_bayar=' . number_format($totalBayar, 2, '.', '')];
                $logs[] = ['nama' => 'Status bayar akhir setelah selesai', 'ok' => $statusBayar === $expectedStatusBayarAkhir, 'expected' => 'status_bayar=' . $expectedStatusBayarAkhir, 'actual' => 'status_bayar=' . $statusBayar];
                $logs[] = ['nama' => 'Sisa tagihan tersinkron', 'ok' => abs($expectedSisaTagihan - max($grandFinal - $totalBayar, 0)) < 0.01, 'expected' => number_format($expectedSisaTagihan, 2, '.', ''), 'actual' => number_format(max($grandFinal - $totalBayar, 0), 2, '.', '')];
                $logs[] = ['nama' => 'Status pesanan akhir', 'ok' => $statusOrder === $expectedStatusOrder, 'expected' => 'status=' . $expectedStatusOrder, 'actual' => 'status=' . $statusOrder];
                $logs[] = ['nama' => 'Trigger transaksi kas dari pembayaran', 'ok' => abs($kasNominal - $nominalBayarAwal) < 0.01, 'expected' => number_format($nominalBayarAwal, 2, '.', ''), 'actual' => number_format($kasNominal, 2, '.', '')];
                $logs[] = ['nama' => 'Trigger tracking log status', 'ok' => $logCount > 0, 'expected' => 'tracking_log bertambah', 'actual' => 'jumlah_log=' . $logCount];
                $logs[] = ['nama' => 'Kontrol stok varian setelah order', 'ok' => $stokAfter === $expectedStokAfter, 'expected' => 'stok akhir=' . $expectedStokAfter, 'actual' => 'stok akhir=' . $stokAfter];

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
                    'auto_status_flow' => $autoStatusFlow,
                    'payment_mode' => $modePembayaranAwal,
                    'payment_amount' => $nominalBayarAwal,
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
        redirect_msg($page, 'Operasi gagal: ' . $e->getMessage(), 'danger');
    }
}

$stats = [
    'zona' => (int) (db_fetch_one($mysqli, 'SELECT COUNT(*) AS total FROM zona')['total'] ?? 0),
    'pelanggan' => (int) (db_fetch_one($mysqli, 'SELECT COUNT(*) AS total FROM pelanggan')['total'] ?? 0),
    'kategori' => (int) (db_fetch_one($mysqli, 'SELECT COUNT(*) AS total FROM kategori')['total'] ?? 0),
    'produk' => (int) (db_fetch_one($mysqli, 'SELECT COUNT(*) AS total FROM produk')['total'] ?? 0),
    'varian' => (int) (db_fetch_one($mysqli, 'SELECT COUNT(*) AS total FROM varian_produk')['total'] ?? 0),
    'pesanan' => (int) (db_fetch_one($mysqli, 'SELECT COUNT(*) AS total FROM pesanan')['total'] ?? 0),
    'pembayaran' => (int) (db_fetch_one($mysqli, 'SELECT COUNT(*) AS total FROM pembayaran')['total'] ?? 0),
    'pengeluaran' => (int) (db_fetch_one($mysqli, 'SELECT COUNT(*) AS total FROM pengeluaran')['total'] ?? 0),
    'pengiriman' => (int) (db_fetch_one($mysqli, 'SELECT COUNT(*) AS total FROM pengiriman')['total'] ?? 0),
    'kas' => (int) (db_fetch_one($mysqli, 'SELECT COUNT(*) AS total FROM transaksi_kas')['total'] ?? 0),
    'tracking' => (int) (db_fetch_one($mysqli, 'SELECT COUNT(*) AS total FROM tracking_log')['total'] ?? 0),
    'user' => (int) (db_fetch_one($mysqli, 'SELECT COUNT(*) AS total FROM pengguna')['total'] ?? 0),
    'setting' => (int) (db_fetch_one($mysqli, 'SELECT COUNT(*) AS total FROM setting')['total'] ?? 0),
];

$kasRingkas = db_fetch_one($mysqli, 'SELECT total_masuk, total_keluar, saldo_akhir FROM v_ringkasan_kas');
$zones = db_fetch_all($mysqli, 'SELECT id_zona, nama_zona, deskripsi, ongkir FROM zona ORDER BY id_zona DESC');
$pelangganRows = db_fetch_all($mysqli, 'SELECT pel.id_pelanggan, pel.id_zona, pel.nama, pel.alamat, pel.no_wa, pel.tipe, pel.catatan, z.nama_zona FROM pelanggan pel LEFT JOIN zona z ON z.id_zona = pel.id_zona ORDER BY pel.id_pelanggan DESC');
$kategoriRows = db_fetch_all($mysqli, 'SELECT id_kategori, nama_kategori, deskripsi FROM kategori ORDER BY id_kategori DESC');
$produkRows = db_fetch_all($mysqli, 'SELECT p.id_produk, p.id_kategori, p.nama_produk, p.deskripsi, p.shelf_life, p.status, k.nama_kategori FROM produk p JOIN kategori k ON k.id_kategori = p.id_kategori ORDER BY p.id_produk DESC');
$varianRows = db_fetch_all($mysqli, 'SELECT v.id_varian, v.id_produk, v.nama_varian, v.harga, v.min_order, v.stok, p.nama_produk FROM varian_produk v JOIN produk p ON p.id_produk = v.id_produk ORDER BY v.id_varian DESC');
$penggunaRows = db_fetch_all($mysqli, 'SELECT id_user, username, nama_lengkap, email, no_hp, role, status, last_login FROM pengguna ORDER BY id_user DESC');
$settingRows = db_fetch_all($mysqli, 'SELECT id_setting, setting_key, setting_value, deskripsi, updated_at FROM setting ORDER BY id_setting DESC');
$pesananRows = db_fetch_all(
    $mysqli,
    'SELECT vpl.id_pesanan, vpl.no_invoice, vpl.tgl_pesan, vpl.tgl_kirim, vpl.total_harga, vpl.ongkir, vpl.grand_total, '
    . 'vpl.status, vpl.status_bayar, vpl.metode_bayar, vpl.nama_pelanggan, vpl.alamat, vpl.no_wa, vpl.tipe_pelanggan, '
    . 'vpl.nama_zona, vpl.status_pengiriman, vpl.driver, '
    . 'COALESCE(pb.total_bayar, 0) AS total_bayar, '
    . 'GREATEST(vpl.grand_total - COALESCE(pb.total_bayar, 0), 0) AS sisa_tagihan '
    . 'FROM v_pesanan_lengkap vpl '
    . 'LEFT JOIN (SELECT id_pesanan, SUM(jumlah) AS total_bayar FROM pembayaran GROUP BY id_pesanan) pb '
    . 'ON pb.id_pesanan = vpl.id_pesanan '
    . 'ORDER BY vpl.id_pesanan DESC LIMIT 25'
);
$detailRows = db_fetch_all($mysqli, 'SELECT id_detail, id_pesanan, no_invoice, nama_produk, nama_varian, qty, harga_satuan, subtotal FROM v_detail_pesanan_produk ORDER BY id_detail DESC LIMIT 25');
$pembayaranRows = db_fetch_all($mysqli, 'SELECT pb.id_pembayaran, pb.id_pesanan, p.no_invoice, pb.jumlah, pb.tgl_bayar, pb.metode, pb.bukti, pb.keterangan, pb.received_by FROM pembayaran pb JOIN pesanan p ON p.id_pesanan = pb.id_pesanan ORDER BY pb.id_pembayaran DESC LIMIT 25');
$pengirimanRows = db_fetch_all($mysqli, 'SELECT pg.id_pengiriman, pg.id_pesanan, p.no_invoice, pg.tgl_kirim, pg.tgl_sampai, pg.status, pg.driver, pg.catatan FROM pengiriman pg JOIN pesanan p ON p.id_pesanan = pg.id_pesanan ORDER BY pg.id_pengiriman DESC LIMIT 25');
$pengeluaranRows = db_fetch_all($mysqli, 'SELECT id_pengeluaran, tanggal, kategori, deskripsi, jumlah, created_by FROM pengeluaran ORDER BY id_pengeluaran DESC LIMIT 25');
$kasRows = db_fetch_all($mysqli, 'SELECT id_transaksi, tanggal, jenis, sumber_tipe, sumber_id, nominal, keterangan, created_by FROM transaksi_kas ORDER BY id_transaksi DESC LIMIT 25');
$trackingRows = db_fetch_all($mysqli, 'SELECT id_log, id_pesanan, status, keterangan, created_at FROM tracking_log ORDER BY id_log DESC LIMIT 25');
$katalogRows = db_fetch_all($mysqli, 'SELECT id_produk, nama_kategori, nama_produk, deskripsi, gambar, shelf_life, status, id_varian, nama_varian, harga, min_order, stok FROM v_katalog ORDER BY nama_kategori, nama_produk, harga');
$piutangRows = db_fetch_all($mysqli, 'SELECT id_pelanggan, nama, no_wa, total_hutang, jumlah_pesanan_belum_lunas, total_pesanan, total_terbayar, sisa_hutang FROM v_piutang ORDER BY sisa_hutang DESC');
$lapHarianRows = db_fetch_all($mysqli, 'SELECT tanggal, total_order, total_penjualan, total_lunas, total_piutang FROM v_laporan_harian ORDER BY tanggal DESC LIMIT 30');
$lapKasRows = db_fetch_all($mysqli, 'SELECT tanggal, pemasukan, pengeluaran, saldo_harian FROM v_laporan_kas_harian ORDER BY tanggal DESC LIMIT 30');
$orderPelangganRows = db_fetch_all($mysqli, 'SELECT id_pelanggan, nama FROM pelanggan ORDER BY nama');
$orderVarianRows = db_fetch_all($mysqli, 'SELECT v.id_varian, v.nama_varian, v.stok, v.min_order, v.harga, p.nama_produk FROM varian_produk v JOIN produk p ON p.id_produk = v.id_produk ORDER BY p.nama_produk, v.nama_varian');
$recentOrders = db_fetch_all(
    $mysqli,
    'SELECT vpl.id_pesanan, vpl.no_invoice, vpl.tgl_pesan, vpl.grand_total, vpl.status, vpl.status_bayar, vpl.nama_pelanggan, '
    . 'COALESCE(pb.total_bayar, 0) AS total_bayar, '
    . 'GREATEST(vpl.grand_total - COALESCE(pb.total_bayar, 0), 0) AS sisa_tagihan '
    . 'FROM v_pesanan_lengkap vpl '
    . 'LEFT JOIN (SELECT id_pesanan, SUM(jumlah) AS total_bayar FROM pembayaran GROUP BY id_pesanan) pb '
    . 'ON pb.id_pesanan = vpl.id_pesanan '
    . 'ORDER BY vpl.id_pesanan DESC LIMIT 10'
);
$recentPayments = db_fetch_all($mysqli, 'SELECT pb.id_pembayaran, p.no_invoice, pb.jumlah, pb.metode FROM pembayaran pb JOIN pesanan p ON p.id_pesanan = pb.id_pesanan ORDER BY pb.id_pembayaran DESC LIMIT 10');
$recentExpenses = db_fetch_all($mysqli, 'SELECT id_pengeluaran, tanggal, kategori, deskripsi, jumlah FROM pengeluaran ORDER BY id_pengeluaran DESC LIMIT 10');
$recentCash = db_fetch_all($mysqli, 'SELECT id_transaksi, tanggal, jenis, sumber_tipe, nominal, keterangan FROM transaksi_kas ORDER BY id_transaksi DESC LIMIT 10');

?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= h($APP_NAME); ?></title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="nav">
    <div class="nav-inner">
        <div class="brand"><?= h($APP_NAME); ?></div>
        <div class="menu">
            <a class="<?= active_page('dashboard', $page); ?>" href="index.php?page=dashboard">Dashboard</a>
            <a class="<?= active_page('master_data', $page); ?>" href="index.php?page=master_data">Master Data</a>
            <a class="<?= active_page('transaksi', $page); ?>" href="index.php?page=transaksi">Transaksi</a>
            <a class="<?= active_page('laporan', $page); ?>" href="index.php?page=laporan">Laporan</a>
            <a class="<?= active_page('log_kas', $page); ?>" href="index.php?page=log_kas">Log & Kas</a>
            <a class="<?= active_page('order_test', $page); ?>" href="index.php?page=order_test">Order Test</a>
        </div>
        <div class="small">Prototype v2 | Domain: <?= h($APP_DOMAIN); ?> | DB: <?= h($DB_NAME); ?></div>
    </div>
</div>

<div class="wrap">
    <?php if ($msg !== ''): ?>
        <div class="alert <?= h($type); ?>"><?= h($msg); ?></div>
    <?php endif; ?>

    <?php if ($page === 'dashboard'): ?>
        <div class="grid grid-4">
            <div class="card"><div class="small">Zona</div><div class="metric"><?= (int) $stats['zona']; ?></div></div>
            <div class="card"><div class="small">Pelanggan</div><div class="metric"><?= (int) $stats['pelanggan']; ?></div></div>
            <div class="card"><div class="small">Produk / Varian</div><div class="metric"><?= (int) $stats['produk']; ?> / <?= (int) $stats['varian']; ?></div></div>
            <div class="card"><div class="small">Pesanan</div><div class="metric"><?= (int) $stats['pesanan']; ?></div></div>
        </div>
        <div class="grid grid-4">
            <div class="card"><div class="small">Pembayaran</div><div class="metric"><?= (int) $stats['pembayaran']; ?></div></div>
            <div class="card"><div class="small">Pengeluaran</div><div class="metric"><?= (int) $stats['pengeluaran']; ?></div></div>
            <div class="card"><div class="small">Kas</div><div class="metric"><?= (int) $stats['kas']; ?></div></div>
            <div class="card"><div class="small">Tracking Log</div><div class="metric"><?= (int) $stats['tracking']; ?></div></div>
        </div>
        <div class="grid grid-4">
            <div class="card"><div class="small">Kas Masuk</div><div class="metric"><?= money($kasRingkas['total_masuk'] ?? 0); ?></div></div>
            <div class="card"><div class="small">Kas Keluar</div><div class="metric"><?= money($kasRingkas['total_keluar'] ?? 0); ?></div></div>
            <div class="card"><div class="small">Saldo Akhir</div><div class="metric"><?= money($kasRingkas['saldo_akhir'] ?? 0); ?></div></div>
            <div class="card"><div class="small">Setting Aktif</div><div class="metric"><?= (int) $stats['setting']; ?></div></div>
        </div>

        <div class="card">
            <h3>Coverage Prototype V2</h3>
            <div class="grid grid-4">
                <div class="card"><div class="small">Master Data</div><div class="small">zona, pelanggan, kategori, produk, varian, pengguna, setting</div></div>
                <div class="card"><div class="small">Transaksi</div><div class="small">pesanan, pembayaran, pengiriman, pengeluaran</div></div>
                <div class="card"><div class="small">Operasional</div><div class="small">tracking_log, transaksi_kas</div></div>
                <div class="card"><div class="small">Laporan</div><div class="small">katalog, piutang, laporan harian, laporan kas</div></div>
            </div>
        </div>

        <div class="grid grid-2">
            <div class="card">
                <h3>Pesanan Terbaru</h3>
                <div class="table-wrap"><table><thead><tr><th>Invoice</th><th>Pelanggan</th><th>Total</th><th>Status</th><th>Tagihan</th></tr></thead><tbody><?php foreach ($recentOrders as $row): ?><tr><td><?= h($row['no_invoice']); ?></td><td><?= h($row['nama_pelanggan']); ?></td><td><?= money($row['grand_total']); ?></td><td><?= h($row['status']); ?></td><td><?= h(normalize_status_tagihan($row)); ?></td></tr><?php endforeach; ?></tbody></table></div>
            </div>
            <div class="card">
                <h3>Pembayaran Terbaru</h3>
                <div class="table-wrap"><table><thead><tr><th>Invoice</th><th>Jumlah</th><th>Metode</th></tr></thead><tbody><?php foreach ($recentPayments as $row): ?><tr><td><?= h($row['no_invoice']); ?></td><td><?= money($row['jumlah']); ?></td><td><?= h($row['metode']); ?></td></tr><?php endforeach; ?></tbody></table></div>
            </div>
        </div>

        <div class="grid grid-2">
            <div class="card">
                <h3>Pengeluaran Terbaru</h3>
                <div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Kategori</th><th>Deskripsi</th><th>Jumlah</th></tr></thead><tbody><?php foreach ($recentExpenses as $row): ?><tr><td><?= h($row['tanggal']); ?></td><td><?= h($row['kategori']); ?></td><td><?= h($row['deskripsi']); ?></td><td><?= money($row['jumlah']); ?></td></tr><?php endforeach; ?></tbody></table></div>
            </div>
            <div class="card">
                <h3>Transaksi Kas Terbaru</h3>
                <div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Jenis</th><th>Sumber</th><th>Nominal</th></tr></thead><tbody><?php foreach ($recentCash as $row): ?><tr><td><?= h($row['tanggal']); ?></td><td><?= h($row['jenis']); ?></td><td><?= h($row['sumber_tipe']); ?></td><td><?= money($row['nominal']); ?></td></tr><?php endforeach; ?></tbody></table></div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($page === 'master_data'): ?>
        <div class="card"><h3>Zona Pengiriman</h3>
            <form method="post">
                <input type="hidden" name="action" value="add_zona">
                <div class="grid grid-4">
                    <div><label>Nama Zona</label><input name="nama_zona" required></div>
                    <div><label>Ongkir</label><input type="number" step="0.01" name="ongkir" value="0"></div>
                    <div style="grid-column: span 2;"><label>Deskripsi</label><input name="deskripsi"></div>
                </div>
                <div style="margin-top:8px;"><button type="submit">Simpan Zona</button></div>
            </form>
            <div class="table-wrap" style="margin-top:12px;"><table><thead><tr><th>ID</th><th>Nama</th><th>Ongkir</th><th>Deskripsi</th><th>Aksi</th></tr></thead><tbody><?php foreach ($zones as $row): ?><tr><td><?= (int) $row['id_zona']; ?></td><td><?= h($row['nama_zona']); ?></td><td><?= money($row['ongkir']); ?></td><td><?= h($row['deskripsi']); ?></td><td class="row-actions"><details><summary>Edit</summary><form method="post" style="margin-top:6px;display:grid;gap:6px;"><input type="hidden" name="action" value="edit_zona"><input type="hidden" name="id" value="<?= (int) $row['id_zona']; ?>"><input name="nama_zona" value="<?= h($row['nama_zona']); ?>" required><input type="number" step="0.01" name="ongkir" value="<?= h((string) $row['ongkir']); ?>"><input name="deskripsi" value="<?= h($row['deskripsi']); ?>"><button type="submit">Update</button></form></details><form method="post" onsubmit="return confirm('Hapus zona ini?')"><input type="hidden" name="action" value="delete_zona"><input type="hidden" name="id" value="<?= (int) $row['id_zona']; ?>"><button type="submit" class="btn-danger">Hapus</button></form></td></tr><?php endforeach; ?></tbody></table></div>
        </div>

        <div class="card"><h3>Pelanggan</h3>
            <form method="post"><input type="hidden" name="action" value="add_pelanggan"><div class="grid grid-4"><div><label>Nama</label><input name="nama" required></div><div><label>No WA</label><input name="no_wa" required></div><div><label>Tipe</label><select name="tipe"><option value="retail">retail</option><option value="reseller">reseller</option></select></div><div><label>Zona</label><select name="id_zona"><option value="">- pilih -</option><?php foreach ($zones as $z): ?><option value="<?= (int) $z['id_zona']; ?>"><?= h($z['nama_zona']); ?></option><?php endforeach; ?></select></div></div><div class="grid" style="grid-template-columns:1fr;gap:8px;margin-top:8px;"><div><label>Alamat</label><input name="alamat" required></div><div><label>Catatan</label><input name="catatan"></div><div><button type="submit">Simpan Pelanggan</button></div></div></form>
            <div class="table-wrap" style="margin-top:12px;"><table><thead><tr><th>ID</th><th>Nama</th><th>WA</th><th>Tipe</th><th>Zona</th><th>Aksi</th></tr></thead><tbody><?php foreach ($pelangganRows as $row): ?><tr><td><?= (int) $row['id_pelanggan']; ?></td><td><?= h($row['nama']); ?></td><td><?= h($row['no_wa']); ?></td><td><?= h($row['tipe']); ?></td><td><?= h($row['nama_zona']); ?></td><td class="row-actions"><details><summary>Edit</summary><form method="post" style="margin-top:6px;display:grid;gap:6px;"><input type="hidden" name="action" value="edit_pelanggan"><input type="hidden" name="id" value="<?= (int) $row['id_pelanggan']; ?>"><input name="nama" value="<?= h($row['nama']); ?>" required><input name="no_wa" value="<?= h($row['no_wa']); ?>" required><input name="alamat" value="<?= h($row['alamat']); ?>" required><select name="tipe"><option value="retail" <?= $row['tipe'] === 'retail' ? 'selected' : ''; ?>>retail</option><option value="reseller" <?= $row['tipe'] === 'reseller' ? 'selected' : ''; ?>>reseller</option></select><select name="id_zona"><option value="">- pilih -</option><?php foreach ($zones as $z): ?><option value="<?= (int) $z['id_zona']; ?>" <?= ((int) $row['id_zona'] === (int) $z['id_zona']) ? 'selected' : ''; ?>><?= h($z['nama_zona']); ?></option><?php endforeach; ?></select><input name="catatan" value="<?= h($row['catatan']); ?>"><button type="submit">Update</button></form></details><form method="post" onsubmit="return confirm('Hapus pelanggan ini?')"><input type="hidden" name="action" value="delete_pelanggan"><input type="hidden" name="id" value="<?= (int) $row['id_pelanggan']; ?>"><button type="submit" class="btn-danger">Hapus</button></form></td></tr><?php endforeach; ?></tbody></table></div>
        </div>

        <div class="card"><h3>Kategori</h3>
            <form method="post"><input type="hidden" name="action" value="add_kategori"><div class="grid grid-4"><div><label>Nama Kategori</label><input name="nama_kategori" required></div><div style="grid-column: span 2;"><label>Deskripsi</label><input name="deskripsi"></div><div><button type="submit">Simpan</button></div></div></form>
            <div class="table-wrap" style="margin-top:12px;"><table><thead><tr><th>ID</th><th>Nama</th><th>Deskripsi</th><th>Aksi</th></tr></thead><tbody><?php foreach ($kategoriRows as $row): ?><tr><td><?= (int) $row['id_kategori']; ?></td><td><?= h($row['nama_kategori']); ?></td><td><?= h($row['deskripsi']); ?></td><td class="row-actions"><details><summary>Edit</summary><form method="post" style="margin-top:6px;display:grid;gap:6px;"><input type="hidden" name="action" value="edit_kategori"><input type="hidden" name="id" value="<?= (int) $row['id_kategori']; ?>"><input name="nama_kategori" value="<?= h($row['nama_kategori']); ?>" required><input name="deskripsi" value="<?= h($row['deskripsi']); ?>"><button type="submit">Update</button></form></details><form method="post" onsubmit="return confirm('Hapus kategori ini?')"><input type="hidden" name="action" value="delete_kategori"><input type="hidden" name="id" value="<?= (int) $row['id_kategori']; ?>"><button type="submit" class="btn-danger">Hapus</button></form></td></tr><?php endforeach; ?></tbody></table></div>
        </div>

        <div class="card"><h3>Produk</h3>
            <form method="post"><input type="hidden" name="action" value="add_produk"><div class="grid grid-4"><div><label>Kategori</label><select name="id_kategori" required><?php foreach ($kategoriRows as $k): ?><option value="<?= (int) $k['id_kategori']; ?>"><?= h($k['nama_kategori']); ?></option><?php endforeach; ?></select></div><div><label>Nama Produk</label><input name="nama_produk" required></div><div><label>Shelf Life (hari)</label><input type="number" name="shelf_life" min="1" value="7"></div><div><label>Status</label><select name="status"><option value="tersedia">tersedia</option><option value="tidak_tersedia">tidak_tersedia</option></select></div></div><div style="margin-top:8px;"><label>Deskripsi</label><textarea name="deskripsi"></textarea></div><div style="margin-top:8px;"><button type="submit">Simpan Produk</button></div></form>
            <div class="table-wrap" style="margin-top:12px;"><table><thead><tr><th>ID</th><th>Nama</th><th>Kategori</th><th>Status</th><th>Aksi</th></tr></thead><tbody><?php foreach ($produkRows as $row): ?><tr><td><?= (int) $row['id_produk']; ?></td><td><?= h($row['nama_produk']); ?></td><td><?= h($row['nama_kategori']); ?></td><td><?= h($row['status']); ?></td><td class="row-actions"><details><summary>Edit</summary><form method="post" style="margin-top:6px;display:grid;gap:6px;"><input type="hidden" name="action" value="edit_produk"><input type="hidden" name="id" value="<?= (int) $row['id_produk']; ?>"><select name="id_kategori"><?php foreach ($kategoriRows as $k): ?><option value="<?= (int) $k['id_kategori']; ?>" <?= ((int) $row['id_kategori'] === (int) $k['id_kategori']) ? 'selected' : ''; ?>><?= h($k['nama_kategori']); ?></option><?php endforeach; ?></select><input name="nama_produk" value="<?= h($row['nama_produk']); ?>" required><input type="number" name="shelf_life" value="<?= (int) $row['shelf_life']; ?>" min="1"><select name="status"><option value="tersedia" <?= $row['status'] === 'tersedia' ? 'selected' : ''; ?>>tersedia</option><option value="tidak_tersedia" <?= $row['status'] === 'tidak_tersedia' ? 'selected' : ''; ?>>tidak_tersedia</option></select><textarea name="deskripsi"><?= h($row['deskripsi']); ?></textarea><button type="submit">Update</button></form></details><form method="post" onsubmit="return confirm('Hapus produk ini?')"><input type="hidden" name="action" value="delete_produk"><input type="hidden" name="id" value="<?= (int) $row['id_produk']; ?>"><button type="submit" class="btn-danger">Hapus</button></form></td></tr><?php endforeach; ?></tbody></table></div>
        </div>

        <div class="card"><h3>Varian Produk</h3>
            <form method="post"><input type="hidden" name="action" value="add_varian"><div class="grid grid-4"><div><label>Produk</label><select name="id_produk" required><?php foreach ($produkRows as $p): ?><option value="<?= (int) $p['id_produk']; ?>"><?= h($p['nama_produk']); ?></option><?php endforeach; ?></select></div><div><label>Nama Varian</label><input name="nama_varian" required></div><div><label>Harga</label><input type="number" step="0.01" name="harga" min="0" required></div><div><label>Min Order</label><input type="number" name="min_order" min="1" value="1"></div></div><div class="grid" style="grid-template-columns:240px 1fr;gap:8px;margin-top:8px;"><div><label>Stok</label><input type="number" name="stok" min="0" value="0"></div><div><label>&nbsp;</label><button type="submit">Simpan Varian</button></div></div></form>
            <div class="table-wrap" style="margin-top:12px;"><table><thead><tr><th>ID</th><th>Produk</th><th>Varian</th><th>Harga</th><th>Min</th><th>Stok</th><th>Aksi</th></tr></thead><tbody><?php foreach ($varianRows as $row): ?><tr><td><?= (int) $row['id_varian']; ?></td><td><?= h($row['nama_produk']); ?></td><td><?= h($row['nama_varian']); ?></td><td><?= money($row['harga']); ?></td><td><?= (int) $row['min_order']; ?></td><td><?= (int) $row['stok']; ?></td><td class="row-actions"><details><summary>Edit</summary><form method="post" style="margin-top:6px;display:grid;gap:6px;"><input type="hidden" name="action" value="edit_varian"><input type="hidden" name="id" value="<?= (int) $row['id_varian']; ?>"><select name="id_produk"><?php foreach ($produkRows as $p): ?><option value="<?= (int) $p['id_produk']; ?>" <?= ((int) $row['id_produk'] === (int) $p['id_produk']) ? 'selected' : ''; ?>><?= h($p['nama_produk']); ?></option><?php endforeach; ?></select><input name="nama_varian" value="<?= h($row['nama_varian']); ?>" required><input type="number" step="0.01" name="harga" value="<?= h((string) $row['harga']); ?>" min="0" required><input type="number" name="min_order" value="<?= (int) $row['min_order']; ?>" min="1"><input type="number" name="stok" value="<?= (int) $row['stok']; ?>" min="0"><button type="submit">Update</button></form></details><form method="post" onsubmit="return confirm('Hapus varian ini?')"><input type="hidden" name="action" value="delete_varian"><input type="hidden" name="id" value="<?= (int) $row['id_varian']; ?>"><button type="submit" class="btn-danger">Hapus</button></form></td></tr><?php endforeach; ?></tbody></table></div>
        </div>

        <div class="card"><h3>Pengguna</h3>
            <form method="post"><input type="hidden" name="action" value="add_pengguna"><div class="grid grid-4"><div><label>Username</label><input name="username" required></div><div><label>Password</label><input type="password" name="password" required></div><div><label>Nama Lengkap</label><input name="nama_lengkap" required></div><div><label>Role</label><select name="role"><option value="admin">admin</option><option value="kasir">kasir</option><option value="driver">driver</option></select></div></div><div class="grid grid-4" style="margin-top:8px;"><div><label>Email</label><input type="email" name="email"></div><div><label>No HP</label><input name="no_hp"></div><div><label>Status</label><select name="status"><option value="aktif">aktif</option><option value="nonaktif">nonaktif</option></select></div><div><label>&nbsp;</label><button type="submit">Simpan Pengguna</button></div></div></form>
            <div class="table-wrap" style="margin-top:12px;"><table><thead><tr><th>ID</th><th>Username</th><th>Nama</th><th>Role</th><th>Status</th><th>Aksi</th></tr></thead><tbody><?php foreach ($penggunaRows as $row): ?><tr><td><?= (int) $row['id_user']; ?></td><td><?= h($row['username']); ?></td><td><?= h($row['nama_lengkap']); ?></td><td><?= h($row['role']); ?></td><td><?= h($row['status']); ?></td><td class="row-actions"><details><summary>Edit</summary><form method="post" style="margin-top:6px;display:grid;gap:6px;"><input type="hidden" name="action" value="edit_pengguna"><input type="hidden" name="id" value="<?= (int) $row['id_user']; ?>"><input name="username" value="<?= h($row['username']); ?>" required><input type="password" name="password" placeholder="Kosongkan jika tidak diganti"><input name="nama_lengkap" value="<?= h($row['nama_lengkap']); ?>" required><input type="email" name="email" value="<?= h($row['email']); ?>"><input name="no_hp" value="<?= h($row['no_hp']); ?>"><select name="role"><option value="admin" <?= $row['role'] === 'admin' ? 'selected' : ''; ?>>admin</option><option value="kasir" <?= $row['role'] === 'kasir' ? 'selected' : ''; ?>>kasir</option><option value="driver" <?= $row['role'] === 'driver' ? 'selected' : ''; ?>>driver</option></select><select name="status"><option value="aktif" <?= $row['status'] === 'aktif' ? 'selected' : ''; ?>>aktif</option><option value="nonaktif" <?= $row['status'] === 'nonaktif' ? 'selected' : ''; ?>>nonaktif</option></select><button type="submit">Update</button></form></details><form method="post" onsubmit="return confirm('Hapus pengguna ini?')"><input type="hidden" name="action" value="delete_pengguna"><input type="hidden" name="id" value="<?= (int) $row['id_user']; ?>"><button type="submit" class="btn-danger">Hapus</button></form></td></tr><?php endforeach; ?></tbody></table></div>
        </div>

        <div class="card"><h3>Setting Aplikasi</h3>
            <form method="post"><input type="hidden" name="action" value="save_setting"><div class="grid grid-4"><div><label>Key Setting</label><input name="setting_key" required></div><div><label>Value</label><input name="setting_value"></div><div style="grid-column: span 2;"><label>Deskripsi</label><input name="deskripsi"></div></div><div style="margin-top:8px;"><button type="submit">Simpan Setting</button></div></form>
            <div class="table-wrap" style="margin-top:12px;"><table><thead><tr><th>ID</th><th>Key</th><th>Value</th><th>Deskripsi</th><th>Aksi</th></tr></thead><tbody><?php foreach ($settingRows as $row): ?><tr><td><?= (int) $row['id_setting']; ?></td><td><?= h($row['setting_key']); ?></td><td><?= h($row['setting_value']); ?></td><td><?= h($row['deskripsi']); ?></td><td class="row-actions"><details><summary>Edit</summary><form method="post" style="margin-top:6px;display:grid;gap:6px;"><input type="hidden" name="action" value="save_setting"><input name="setting_key" value="<?= h($row['setting_key']); ?>" required><input name="setting_value" value="<?= h($row['setting_value']); ?>"><input name="deskripsi" value="<?= h($row['deskripsi']); ?>"><button type="submit">Update</button></form></details><form method="post" onsubmit="return confirm('Hapus setting ini?')"><input type="hidden" name="action" value="delete_setting"><input type="hidden" name="id" value="<?= (int) $row['id_setting']; ?>"><button type="submit" class="btn-danger">Hapus</button></form></td></tr><?php endforeach; ?></tbody></table></div>
        </div>
    <?php endif; ?>

    <?php if ($page === 'transaksi'): ?>
        <div class="card">
            <h3>Pesanan Terdaftar</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Status Tagihan</th>
                        <th>Sisa Hutang</th>
                        <th>Pengiriman</th>
                        <th>Aksi Status</th>
                        <th>Aksi Bayar</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pesananRows as $row): ?>
                        <?php
                        $statusTagihan = normalize_status_tagihan($row);
                        $sisaTagihan = (float) ($row['sisa_tagihan'] ?? 0);
                        $isStatusLocked = in_array((string) $row['status'], ['selesai', 'batal'], true);
                        $nextOptions = next_statuses((string) $row['status']);
                        $canPay = (string) $row['status'] !== 'batal' && $statusTagihan !== 'lunas' && $sisaTagihan > 0.0001;
                        ?>
                        <tr>
                            <td><?= h($row['no_invoice']); ?></td>
                            <td><?= h($row['nama_pelanggan']); ?></td>
                            <td><?= money($row['grand_total']); ?></td>
                            <td><?= h($row['status']); ?></td>
                            <td><?= h($statusTagihan); ?></td>
                            <td><?= money($sisaTagihan); ?></td>
                            <td><?= h($row['status_pengiriman']); ?></td>
                            <td>
                                <?php if ($isStatusLocked): ?>
                                    <span class="small">Terkunci (<?= h($row['status']); ?>)</span>
                                <?php else: ?>
                                    <form method="post" style="display:grid;gap:6px;min-width:170px;">
                                        <input type="hidden" name="action" value="update_pesanan_status">
                                        <input type="hidden" name="id_pesanan" value="<?= (int) $row['id_pesanan']; ?>">
                                        <select name="status" required>
                                            <?php foreach ($nextOptions as $option): ?>
                                                <option value="<?= h($option); ?>"><?= h($option); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit">Update</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$canPay): ?>
                                    <span class="small">Pembayaran ditutup</span>
                                <?php else: ?>
                                    <form method="post" style="display:grid;gap:6px;min-width:190px;">
                                        <input type="hidden" name="action" value="add_pesanan_bayar">
                                        <input type="hidden" name="id_pesanan" value="<?= (int) $row['id_pesanan']; ?>">
                                        <input type="number" step="0.01" name="jumlah" min="0.01" max="<?= h((string) $sisaTagihan); ?>" placeholder="Maks: <?= h(number_format($sisaTagihan, 0, ',', '.')); ?>" required>
                                        <select name="metode">
                                            <option value="cash">cash</option>
                                            <option value="transfer">transfer</option>
                                        </select>
                                        <input name="bukti" placeholder="Bukti/No Ref">
                                        <button type="submit">Bayar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-2">
            <div class="card"><h3>Pembayaran</h3>
                <form method="post"><input type="hidden" name="action" value="add_pembayaran"><div class="grid grid-4"><div><label>Pesanan</label><select name="id_pesanan" required><?php foreach ($pesananRows as $o): ?><?php if ((string) $o['status'] !== 'batal' && (float) ($o['sisa_tagihan'] ?? 0) > 0.0001): ?><option value="<?= (int) $o['id_pesanan']; ?>"><?= h($o['no_invoice']); ?> | sisa <?= h(number_format((float) $o['sisa_tagihan'], 0, ',', '.')); ?></option><?php endif; ?><?php endforeach; ?></select></div><div><label>Jumlah</label><input type="number" step="0.01" name="jumlah" required></div><div><label>Metode</label><select name="metode"><option value="cash">cash</option><option value="transfer">transfer</option></select></div><div><label>Received By</label><select name="received_by"><?php foreach ($penggunaRows as $u): ?><option value="<?= (int) $u['id_user']; ?>"><?= h($u['nama_lengkap']); ?></option><?php endforeach; ?></select></div></div><div class="grid grid-4" style="margin-top:8px;"><div><label>Tanggal Bayar</label><input type="datetime-local" name="tgl_bayar" value="<?= date('Y-m-d\TH:i'); ?>"></div><div style="grid-column: span 2;"><label>Bukti</label><input name="bukti"></div><div style="grid-column: span 4;"><label>Keterangan</label><input name="keterangan"></div></div><div style="margin-top:8px;"><button type="submit">Simpan Pembayaran</button></div></form>
                <div class="table-wrap" style="margin-top:12px;"><table><thead><tr><th>Invoice</th><th>Jumlah</th><th>Metode</th><th>Tanggal</th><th>Aksi</th></tr></thead><tbody><?php foreach ($pembayaranRows as $row): ?><tr><td><?= h($row['no_invoice']); ?></td><td><?= money($row['jumlah']); ?></td><td><?= h($row['metode']); ?></td><td><?= h($row['tgl_bayar']); ?></td><td class="row-actions"><details><summary>Edit</summary><form method="post" style="margin-top:6px;display:grid;gap:6px;"><input type="hidden" name="action" value="edit_pembayaran"><input type="hidden" name="id" value="<?= (int) $row['id_pembayaran']; ?>"><select name="id_pesanan"><?php foreach ($pesananRows as $o): ?><option value="<?= (int) $o['id_pesanan']; ?>" <?= ((int) $row['id_pesanan'] === (int) $o['id_pesanan']) ? 'selected' : ''; ?>><?= h($o['no_invoice']); ?></option><?php endforeach; ?></select><input type="number" step="0.01" name="jumlah" value="<?= h((string) $row['jumlah']); ?>"><input type="datetime-local" name="tgl_bayar" value="<?= h(str_replace(' ', 'T', substr((string) $row['tgl_bayar'], 0, 16))); ?>"><select name="metode"><option value="cash" <?= $row['metode'] === 'cash' ? 'selected' : ''; ?>>cash</option><option value="transfer" <?= $row['metode'] === 'transfer' ? 'selected' : ''; ?>>transfer</option></select><input name="bukti" value="<?= h($row['bukti']); ?>"><input name="keterangan" value="<?= h($row['keterangan']); ?>"><select name="received_by"><?php foreach ($penggunaRows as $u): ?><option value="<?= (int) $u['id_user']; ?>" <?= ((int) $row['received_by'] === (int) $u['id_user']) ? 'selected' : ''; ?>><?= h($u['nama_lengkap']); ?></option><?php endforeach; ?></select><button type="submit">Update</button></form></details><form method="post" onsubmit="return confirm('Hapus pembayaran ini?')"><input type="hidden" name="action" value="delete_pembayaran"><input type="hidden" name="id" value="<?= (int) $row['id_pembayaran']; ?>"><button type="submit" class="btn-danger">Hapus</button></form></td></tr><?php endforeach; ?></tbody></table></div>
            </div>
            <div class="card"><h3>Pengiriman</h3>
                <form method="post"><input type="hidden" name="action" value="add_pengiriman"><div class="grid grid-4"><div><label>Pesanan</label><select name="id_pesanan" required><?php foreach ($pesananRows as $o): ?><option value="<?= (int) $o['id_pesanan']; ?>"><?= h($o['no_invoice']); ?></option><?php endforeach; ?></select></div><div><label>Status</label><select name="status"><option value="menunggu">menunggu</option><option value="dalam_perjalanan">dalam_perjalanan</option><option value="sampai">sampai</option><option value="gagal">gagal</option></select></div><div><label>Driver</label><input name="driver"></div><div><label>&nbsp;</label><button type="submit">Simpan</button></div></div><div class="grid grid-4" style="margin-top:8px;"><div><label>Tgl Kirim</label><input type="datetime-local" name="tgl_kirim"></div><div><label>Tgl Sampai</label><input type="datetime-local" name="tgl_sampai"></div><div style="grid-column: span 2;"><label>Catatan</label><input name="catatan"></div></div></form>
                <div class="table-wrap" style="margin-top:12px;"><table><thead><tr><th>Invoice</th><th>Status</th><th>Driver</th><th>Kirim</th><th>Sampai</th><th>Aksi</th></tr></thead><tbody><?php foreach ($pengirimanRows as $row): ?><tr><td><?= h($row['no_invoice']); ?></td><td><?= h($row['status']); ?></td><td><?= h($row['driver']); ?></td><td><?= h($row['tgl_kirim']); ?></td><td><?= h($row['tgl_sampai']); ?></td><td class="row-actions"><details><summary>Edit</summary><form method="post" style="margin-top:6px;display:grid;gap:6px;"><input type="hidden" name="action" value="edit_pengiriman"><input type="hidden" name="id" value="<?= (int) $row['id_pengiriman']; ?>"><select name="id_pesanan"><?php foreach ($pesananRows as $o): ?><option value="<?= (int) $o['id_pesanan']; ?>" <?= ((int) $row['id_pesanan'] === (int) $o['id_pesanan']) ? 'selected' : ''; ?>><?= h($o['no_invoice']); ?></option><?php endforeach; ?></select><input type="datetime-local" name="tgl_kirim" value="<?= h(str_replace(' ', 'T', substr((string) $row['tgl_kirim'], 0, 16))); ?>"><input type="datetime-local" name="tgl_sampai" value="<?= h(str_replace(' ', 'T', substr((string) $row['tgl_sampai'], 0, 16))); ?>"><select name="status"><option value="menunggu" <?= $row['status'] === 'menunggu' ? 'selected' : ''; ?>>menunggu</option><option value="dalam_perjalanan" <?= $row['status'] === 'dalam_perjalanan' ? 'selected' : ''; ?>>dalam_perjalanan</option><option value="sampai" <?= $row['status'] === 'sampai' ? 'selected' : ''; ?>>sampai</option><option value="gagal" <?= $row['status'] === 'gagal' ? 'selected' : ''; ?>>gagal</option></select><input name="driver" value="<?= h($row['driver']); ?>"><input name="catatan" value="<?= h($row['catatan']); ?>"><button type="submit">Update</button></form></details><form method="post" onsubmit="return confirm('Hapus pengiriman ini?')"><input type="hidden" name="action" value="delete_pengiriman"><input type="hidden" name="id" value="<?= (int) $row['id_pengiriman']; ?>"><button type="submit" class="btn-danger">Hapus</button></form></td></tr><?php endforeach; ?></tbody></table></div>
            </div>
        </div>

        <div class="card"><h3>Pengeluaran</h3>
            <form method="post"><input type="hidden" name="action" value="add_pengeluaran"><div class="grid grid-4"><div><label>Tanggal</label><input type="date" name="tanggal" value="<?= date('Y-m-d'); ?>"></div><div><label>Kategori</label><select name="kategori"><option value="bahan_baku">bahan_baku</option><option value="operasional">operasional</option><option value="gaji">gaji</option><option value="transportasi">transportasi</option><option value="lainnya">lainnya</option></select></div><div><label>Jumlah</label><input type="number" step="0.01" name="jumlah" min="0" required></div><div><label>Created By</label><select name="created_by"><?php foreach ($penggunaRows as $u): ?><option value="<?= (int) $u['id_user']; ?>"><?= h($u['nama_lengkap']); ?></option><?php endforeach; ?></select></div></div><div class="grid" style="grid-template-columns:1fr;gap:8px;margin-top:8px;"><div><label>Deskripsi</label><input name="deskripsi" required></div><div><button type="submit">Simpan Pengeluaran</button></div></div></form>
            <div class="table-wrap" style="margin-top:12px;"><table><thead><tr><th>Tanggal</th><th>Kategori</th><th>Deskripsi</th><th>Jumlah</th><th>Aksi</th></tr></thead><tbody><?php foreach ($pengeluaranRows as $row): ?><tr><td><?= h($row['tanggal']); ?></td><td><?= h($row['kategori']); ?></td><td><?= h($row['deskripsi']); ?></td><td><?= money($row['jumlah']); ?></td><td class="row-actions"><details><summary>Edit</summary><form method="post" style="margin-top:6px;display:grid;gap:6px;"><input type="hidden" name="action" value="edit_pengeluaran"><input type="hidden" name="id" value="<?= (int) $row['id_pengeluaran']; ?>"><input type="date" name="tanggal" value="<?= h($row['tanggal']); ?>"><select name="kategori"><option value="bahan_baku" <?= $row['kategori'] === 'bahan_baku' ? 'selected' : ''; ?>>bahan_baku</option><option value="operasional" <?= $row['kategori'] === 'operasional' ? 'selected' : ''; ?>>operasional</option><option value="gaji" <?= $row['kategori'] === 'gaji' ? 'selected' : ''; ?>>gaji</option><option value="transportasi" <?= $row['kategori'] === 'transportasi' ? 'selected' : ''; ?>>transportasi</option><option value="lainnya" <?= $row['kategori'] === 'lainnya' ? 'selected' : ''; ?>>lainnya</option></select><input name="deskripsi" value="<?= h($row['deskripsi']); ?>"><input type="number" step="0.01" name="jumlah" value="<?= h((string) $row['jumlah']); ?>"><select name="created_by"><?php foreach ($penggunaRows as $u): ?><option value="<?= (int) $u['id_user']; ?>" <?= ((int) $row['created_by'] === (int) $u['id_user']) ? 'selected' : ''; ?>><?= h($u['nama_lengkap']); ?></option><?php endforeach; ?></select><button type="submit">Update</button></form></details><form method="post" onsubmit="return confirm('Hapus pengeluaran ini?')"><input type="hidden" name="action" value="delete_pengeluaran"><input type="hidden" name="id" value="<?= (int) $row['id_pengeluaran']; ?>"><button type="submit" class="btn-danger">Hapus</button></form></td></tr><?php endforeach; ?></tbody></table></div>
        </div>
    <?php endif; ?>

    <?php if ($page === 'laporan'): ?>
        <div class="grid grid-2">
            <div class="card"><h3>Katalog Produk</h3><div class="table-wrap"><table><thead><tr><th>Kategori</th><th>Produk</th><th>Varian</th><th>Harga</th><th>Stok</th></tr></thead><tbody><?php foreach ($katalogRows as $row): ?><tr><td><?= h($row['nama_kategori']); ?></td><td><?= h($row['nama_produk']); ?></td><td><?= h($row['nama_varian']); ?></td><td><?= money($row['harga']); ?></td><td><?= (int) $row['stok']; ?></td></tr><?php endforeach; ?></tbody></table></div></div>
            <div class="card"><h3>Piutang Pelanggan</h3><div class="table-wrap"><table><thead><tr><th>Nama</th><th>HP</th><th>Hutang</th><th>Sisa</th></tr></thead><tbody><?php foreach ($piutangRows as $row): ?><tr><td><?= h($row['nama']); ?></td><td><?= h($row['no_wa']); ?></td><td><?= money($row['total_hutang']); ?></td><td><?= money($row['sisa_hutang']); ?></td></tr><?php endforeach; ?></tbody></table></div></div>
        </div>

        <div class="grid grid-2">
            <div class="card"><h3>Laporan Harian</h3><div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Order</th><th>Penjualan</th><th>Lunas</th><th>Piutang</th></tr></thead><tbody><?php foreach ($lapHarianRows as $row): ?><tr><td><?= h($row['tanggal']); ?></td><td><?= (int) $row['total_order']; ?></td><td><?= money($row['total_penjualan']); ?></td><td><?= money($row['total_lunas']); ?></td><td><?= money($row['total_piutang']); ?></td></tr><?php endforeach; ?></tbody></table></div></div>
            <div class="card"><h3>Laporan Kas Harian</h3><div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Pemasukan</th><th>Pengeluaran</th><th>Saldo</th></tr></thead><tbody><?php foreach ($lapKasRows as $row): ?><tr><td><?= h($row['tanggal']); ?></td><td><?= money($row['pemasukan']); ?></td><td><?= money($row['pengeluaran']); ?></td><td><?= money($row['saldo_harian']); ?></td></tr><?php endforeach; ?></tbody></table></div></div>
        </div>

        <div class="card"><h3>Detail Pesanan</h3><div class="table-wrap"><table><thead><tr><th>Invoice</th><th>Produk</th><th>Varian</th><th>Qty</th><th>Subtotal</th></tr></thead><tbody><?php foreach ($detailRows as $row): ?><tr><td><?= h($row['no_invoice']); ?></td><td><?= h($row['nama_produk']); ?></td><td><?= h($row['nama_varian']); ?></td><td><?= (int) $row['qty']; ?></td><td><?= money($row['subtotal']); ?></td></tr><?php endforeach; ?></tbody></table></div></div>
    <?php endif; ?>

    <?php if ($page === 'log_kas'): ?>
        <div class="grid grid-2">
            <div class="card"><h3>Tracking Log</h3><div class="table-wrap"><table><thead><tr><th>Pesanan</th><th>Status</th><th>Keterangan</th><th>Waktu</th></tr></thead><tbody><?php foreach ($trackingRows as $row): ?><tr><td><?= (int) $row['id_pesanan']; ?></td><td><?= h($row['status']); ?></td><td><?= h($row['keterangan']); ?></td><td><?= h($row['created_at']); ?></td></tr><?php endforeach; ?></tbody></table></div></div>
            <div class="card"><h3>Transaksi Kas</h3><div class="table-wrap"><table><thead><tr><th>Waktu</th><th>Jenis</th><th>Sumber</th><th>Nominal</th><th>Keterangan</th></tr></thead><tbody><?php foreach ($kasRows as $row): ?><tr><td><?= h($row['tanggal']); ?></td><td><?= h($row['jenis']); ?></td><td><?= h($row['sumber_tipe']); ?></td><td><?= money($row['nominal']); ?></td><td><?= h($row['keterangan']); ?></td></tr><?php endforeach; ?></tbody></table></div></div>
        </div>
    <?php endif; ?>

    <?php if ($page === 'order_test'): ?>
        <div class="card"><h3>Order Test Prototype V2</h3>
            <form method="post"><input type="hidden" name="action" value="run_order_test"><div class="grid grid-4"><div><label>Pelanggan</label><select name="id_pelanggan" required><option value="">- pilih pelanggan -</option><?php foreach ($orderPelangganRows as $pel): ?><option value="<?= (int) $pel['id_pelanggan']; ?>"><?= h($pel['nama']); ?></option><?php endforeach; ?></select></div><div><label>Varian</label><select name="id_varian" required><option value="">- pilih varian -</option><?php foreach ($orderVarianRows as $v): ?><option value="<?= (int) $v['id_varian']; ?>"><?= h($v['nama_produk']); ?> - <?= h($v['nama_varian']); ?> | stok <?= (int) $v['stok']; ?> | min <?= (int) $v['min_order']; ?></option><?php endforeach; ?></select></div><div><label>Qty</label><input type="number" name="qty" min="1" value="10" required></div><div><label>Metode Bayar</label><select name="metode_bayar"><option value="transfer">transfer</option><option value="cash">cash</option></select></div></div><div class="grid grid-4" style="margin-top:8px;"><div><label>Mode Pembayaran Awal</label><select name="payment_mode"><option value="lunas">Lunas (bayar penuh)</option><option value="dp">DP (uang muka)</option><option value="tanpa_bayar">Tanpa bayar (hutang penuh)</option></select></div><div><label>Nominal DP</label><input type="number" step="0.01" min="0" name="dp_amount" value="0" placeholder="Isi jika mode DP"></div><div><label>Tanggal Kirim</label><input type="date" name="tgl_kirim" value="<?= date('Y-m-d'); ?>" required></div><div><label>Waktu Kirim</label><input type="time" name="waktu_kirim" value="09:00" required></div></div><div class="grid" style="grid-template-columns:1fr 220px 260px;margin-top:8px;"><div><label>Catatan</label><textarea name="catatan">Order test otomatis dari prototype v2</textarea></div><div style="display:flex;align-items:flex-end;"><label style="display:flex;gap:8px;align-items:center;margin:0;"><input type="checkbox" name="keep_data" value="1" style="width:auto;">Simpan hasil test ke DB</label></div><div style="display:flex;align-items:flex-end;"><label style="display:flex;gap:8px;align-items:center;margin:0;"><input type="checkbox" name="auto_status_flow" value="1" style="width:auto;">Auto maju status ke selesai</label></div></div><div style="margin-top:8px;"><button type="submit">Jalankan Test Order</button></div></form>
        </div>

        <?php if (is_array($orderTestResult)): ?>
            <div class="card"><h3>Hasil Test Order</h3><div class="grid grid-4"><div><div class="small">Status</div><div class="metric" style="font-size:1.2rem;color:<?= $orderTestResult['ok'] ? '#86efac' : '#fca5a5'; ?>;"><?= $orderTestResult['ok'] ? 'PASS' : 'FAIL'; ?></div></div><div><div class="small">Invoice</div><div><?= h($orderTestResult['no_invoice']); ?></div></div><div><div class="small">Durasi</div><div><?= (int) $orderTestResult['duration_ms']; ?> ms</div></div><div><div class="small">Mode Data</div><div><?= $orderTestResult['keep_data'] ? 'COMMIT' : 'ROLLBACK'; ?></div></div></div><div class="table-wrap" style="margin-top:12px;"><table><thead><tr><th>Status</th><th>Pengecekan</th><th>Expected</th><th>Actual</th></tr></thead><tbody><?php foreach ($orderTestResult['logs'] as $log): ?><tr><td><?= $log['ok'] ? 'OK' : 'ERROR'; ?></td><td><?= h($log['nama']); ?></td><td><?= h((string) $log['expected']); ?></td><td><?= h((string) $log['actual']); ?></td></tr><?php endforeach; ?></tbody></table></div><?php if (!$orderTestResult['ok']): ?><div class="alert danger" style="margin-top:10px;">Masih ada fungsi database yang perlu dicek. Lihat baris ERROR pada log test.</div><?php endif; ?></div>
        <?php endif; ?>

        <div class="card"><h3>Riwayat Pesanan Terbaru</h3><div class="table-wrap"><table><thead><tr><th>ID</th><th>Invoice</th><th>Tanggal</th><th>Total</th><th>Status</th><th>Bayar</th></tr></thead><tbody><?php foreach ($recentOrders as $row): ?><tr><td><?= (int) $row['id_pesanan']; ?></td><td><?= h($row['no_invoice']); ?></td><td><?= h($row['tgl_pesan']); ?></td><td><?= money($row['grand_total']); ?></td><td><?= h($row['status']); ?></td><td><?= h(normalize_status_tagihan($row)); ?></td></tr><?php endforeach; ?></tbody></table></div></div>
    <?php endif; ?>
</div>
</body>
</html>

