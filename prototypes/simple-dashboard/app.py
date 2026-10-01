import os
from datetime import datetime

import pymysql
from dotenv import load_dotenv
from flask import Flask, flash, redirect, render_template, request, url_for
from werkzeug.middleware.proxy_fix import ProxyFix


BASE_DIR = os.path.dirname(os.path.abspath(__file__))
load_dotenv(os.path.join(BASE_DIR, ".env"))


app = Flask(__name__)
app.config["SECRET_KEY"] = os.getenv("FLASK_SECRET", "dev-secret-ganti-di-prod")


DB_CONFIG = {
    "host": os.getenv("DB_HOST", "localhost"),
    "port": int(os.getenv("DB_PORT", "3306")),
    "user": os.getenv("DB_USER", "rodd1157_dinasti"),
    "password": os.getenv("DB_PASSWORD", "rodd1157_dinasti"),
    "database": os.getenv("DB_NAME", "rodd1157_berkat_dinasti_db"),
    "charset": "utf8mb4",
    "cursorclass": pymysql.cursors.DictCursor,
    "autocommit": True,
}

app.config["DB_NAME"] = DB_CONFIG["database"]
app.config["APP_DOMAIN"] = os.getenv("APP_DOMAIN", "ucing4k.my.id")
app.wsgi_app = ProxyFix(app.wsgi_app, x_for=1, x_proto=1, x_host=1)


def get_connection():
    return pymysql.connect(**DB_CONFIG)


def fetch_all(query, params=None):
    with get_connection() as conn:
        with conn.cursor() as cur:
            cur.execute(query, params or ())
            return cur.fetchall()


def fetch_one(query, params=None):
    with get_connection() as conn:
        with conn.cursor() as cur:
            cur.execute(query, params or ())
            return cur.fetchone()


def execute(query, params=None):
    with get_connection() as conn:
        with conn.cursor() as cur:
            cur.execute(query, params or ())
            return cur.lastrowid


@app.context_processor
def inject_now():
    return {"now": datetime.now()}


@app.route("/")
def dashboard():
    stats = {
        "total_pelanggan": fetch_one("SELECT COUNT(*) AS total FROM pelanggan")["total"],
        "total_produk": fetch_one("SELECT COUNT(*) AS total FROM produk")["total"],
        "total_pesanan": fetch_one("SELECT COUNT(*) AS total FROM pesanan")["total"],
        "pesanan_pending": fetch_one("SELECT COUNT(*) AS total FROM pesanan WHERE status = 'pending'")["total"],
    }

    kas = fetch_one("SELECT total_masuk, total_keluar, saldo_akhir FROM v_ringkasan_kas")
    latest_orders = fetch_all(
        """
        SELECT id_pesanan, no_invoice, nama_pelanggan, grand_total, status, status_bayar, tgl_pesan
        FROM v_pesanan_lengkap
        ORDER BY id_pesanan DESC
        LIMIT 10
        """
    )
    latest_payments = fetch_all(
        """
        SELECT pb.id_pembayaran, pb.id_pesanan, p.no_invoice, pb.jumlah, pb.metode, pb.tgl_bayar
        FROM pembayaran pb
        JOIN pesanan p ON p.id_pesanan = pb.id_pesanan
        ORDER BY pb.id_pembayaran DESC
        LIMIT 10
        """
    )

    return render_template(
        "dashboard.html",
        stats=stats,
        kas=kas,
        latest_orders=latest_orders,
        latest_payments=latest_payments,
    )


@app.route("/health")
def health():
    try:
        result = fetch_one("SELECT 1 AS ok")
        return {"status": "ok", "db": result["ok"] == 1, "database": DB_CONFIG["database"]}
    except Exception as exc:
        return {"status": "error", "error": str(exc)}, 500


@app.route("/pelanggan")
def pelanggan_list():
    rows = fetch_all(
        """
        SELECT pel.id_pelanggan, pel.nama, pel.no_wa, pel.tipe, pel.alamat, z.nama_zona
        FROM pelanggan pel
        LEFT JOIN zona z ON z.id_zona = pel.id_zona
        ORDER BY pel.id_pelanggan DESC
        """
    )
    zones = fetch_all("SELECT id_zona, nama_zona FROM zona ORDER BY nama_zona")
    return render_template("pelanggan.html", rows=rows, zones=zones)


@app.post("/pelanggan/add")
def pelanggan_add():
    try:
        execute(
            """
            INSERT INTO pelanggan (id_zona, nama, alamat, no_wa, tipe, catatan)
            VALUES (%s, %s, %s, %s, %s, %s)
            """,
            (
                request.form.get("id_zona") or None,
                request.form.get("nama"),
                request.form.get("alamat"),
                request.form.get("no_wa"),
                request.form.get("tipe", "retail"),
                request.form.get("catatan") or None,
            ),
        )
        flash("Pelanggan berhasil ditambahkan.", "success")
    except Exception as exc:
        flash(f"Gagal menambah pelanggan: {exc}", "danger")
    return redirect(url_for("pelanggan_list"))


@app.post("/pelanggan/<int:pelanggan_id>/edit")
def pelanggan_edit(pelanggan_id):
    try:
        execute(
            """
            UPDATE pelanggan
            SET id_zona=%s, nama=%s, alamat=%s, no_wa=%s, tipe=%s, catatan=%s
            WHERE id_pelanggan=%s
            """,
            (
                request.form.get("id_zona") or None,
                request.form.get("nama"),
                request.form.get("alamat"),
                request.form.get("no_wa"),
                request.form.get("tipe", "retail"),
                request.form.get("catatan") or None,
                pelanggan_id,
            ),
        )
        flash("Pelanggan berhasil diperbarui.", "success")
    except Exception as exc:
        flash(f"Gagal update pelanggan: {exc}", "danger")
    return redirect(url_for("pelanggan_list"))


@app.post("/pelanggan/<int:pelanggan_id>/delete")
def pelanggan_delete(pelanggan_id):
    try:
        execute("DELETE FROM pelanggan WHERE id_pelanggan=%s", (pelanggan_id,))
        flash("Pelanggan berhasil dihapus.", "success")
    except Exception as exc:
        flash(f"Gagal hapus pelanggan: {exc}", "danger")
    return redirect(url_for("pelanggan_list"))


@app.route("/kategori")
def kategori_list():
    rows = fetch_all("SELECT id_kategori, nama_kategori, deskripsi FROM kategori ORDER BY id_kategori DESC")
    return render_template("kategori.html", rows=rows)


@app.post("/kategori/add")
def kategori_add():
    try:
        execute(
            "INSERT INTO kategori (nama_kategori, deskripsi) VALUES (%s, %s)",
            (request.form.get("nama_kategori"), request.form.get("deskripsi") or None),
        )
        flash("Kategori berhasil ditambahkan.", "success")
    except Exception as exc:
        flash(f"Gagal tambah kategori: {exc}", "danger")
    return redirect(url_for("kategori_list"))


@app.post("/kategori/<int:kategori_id>/edit")
def kategori_edit(kategori_id):
    try:
        execute(
            "UPDATE kategori SET nama_kategori=%s, deskripsi=%s WHERE id_kategori=%s",
            (request.form.get("nama_kategori"), request.form.get("deskripsi") or None, kategori_id),
        )
        flash("Kategori berhasil diperbarui.", "success")
    except Exception as exc:
        flash(f"Gagal update kategori: {exc}", "danger")
    return redirect(url_for("kategori_list"))


@app.post("/kategori/<int:kategori_id>/delete")
def kategori_delete(kategori_id):
    try:
        execute("DELETE FROM kategori WHERE id_kategori=%s", (kategori_id,))
        flash("Kategori berhasil dihapus.", "success")
    except Exception as exc:
        flash(f"Gagal hapus kategori: {exc}", "danger")
    return redirect(url_for("kategori_list"))


@app.route("/produk")
def produk_list():
    rows = fetch_all(
        """
        SELECT p.id_produk, p.nama_produk, p.status, p.shelf_life, k.nama_kategori, p.id_kategori
        FROM produk p
        JOIN kategori k ON k.id_kategori = p.id_kategori
        ORDER BY p.id_produk DESC
        """
    )
    categories = fetch_all("SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori")
    return render_template("produk.html", rows=rows, categories=categories)


@app.post("/produk/add")
def produk_add():
    try:
        execute(
            """
            INSERT INTO produk (id_kategori, nama_produk, deskripsi, shelf_life, status)
            VALUES (%s, %s, %s, %s, %s)
            """,
            (
                request.form.get("id_kategori"),
                request.form.get("nama_produk"),
                request.form.get("deskripsi") or None,
                request.form.get("shelf_life") or 7,
                request.form.get("status") or "tersedia",
            ),
        )
        flash("Produk berhasil ditambahkan.", "success")
    except Exception as exc:
        flash(f"Gagal tambah produk: {exc}", "danger")
    return redirect(url_for("produk_list"))


@app.post("/produk/<int:produk_id>/edit")
def produk_edit(produk_id):
    try:
        execute(
            """
            UPDATE produk
            SET id_kategori=%s, nama_produk=%s, deskripsi=%s, shelf_life=%s, status=%s
            WHERE id_produk=%s
            """,
            (
                request.form.get("id_kategori"),
                request.form.get("nama_produk"),
                request.form.get("deskripsi") or None,
                request.form.get("shelf_life") or 7,
                request.form.get("status") or "tersedia",
                produk_id,
            ),
        )
        flash("Produk berhasil diperbarui.", "success")
    except Exception as exc:
        flash(f"Gagal update produk: {exc}", "danger")
    return redirect(url_for("produk_list"))


@app.post("/produk/<int:produk_id>/delete")
def produk_delete(produk_id):
    try:
        execute("DELETE FROM produk WHERE id_produk=%s", (produk_id,))
        flash("Produk berhasil dihapus.", "success")
    except Exception as exc:
        flash(f"Gagal hapus produk: {exc}", "danger")
    return redirect(url_for("produk_list"))


@app.route("/varian")
def varian_list():
    rows = fetch_all(
        """
        SELECT v.id_varian, v.id_produk, p.nama_produk, v.nama_varian, v.harga, v.min_order, v.stok
        FROM varian_produk v
        JOIN produk p ON p.id_produk = v.id_produk
        ORDER BY v.id_varian DESC
        """
    )
    products = fetch_all("SELECT id_produk, nama_produk FROM produk ORDER BY nama_produk")
    return render_template("varian.html", rows=rows, products=products)


@app.post("/varian/add")
def varian_add():
    try:
        execute(
            """
            INSERT INTO varian_produk (id_produk, nama_varian, harga, min_order, stok)
            VALUES (%s, %s, %s, %s, %s)
            """,
            (
                request.form.get("id_produk"),
                request.form.get("nama_varian"),
                request.form.get("harga"),
                request.form.get("min_order") or 1,
                request.form.get("stok") or 0,
            ),
        )
        flash("Varian berhasil ditambahkan.", "success")
    except Exception as exc:
        flash(f"Gagal tambah varian: {exc}", "danger")
    return redirect(url_for("varian_list"))


@app.post("/varian/<int:varian_id>/edit")
def varian_edit(varian_id):
    try:
        execute(
            """
            UPDATE varian_produk
            SET id_produk=%s, nama_varian=%s, harga=%s, min_order=%s, stok=%s
            WHERE id_varian=%s
            """,
            (
                request.form.get("id_produk"),
                request.form.get("nama_varian"),
                request.form.get("harga"),
                request.form.get("min_order") or 1,
                request.form.get("stok") or 0,
                varian_id,
            ),
        )
        flash("Varian berhasil diperbarui.", "success")
    except Exception as exc:
        flash(f"Gagal update varian: {exc}", "danger")
    return redirect(url_for("varian_list"))


@app.post("/varian/<int:varian_id>/delete")
def varian_delete(varian_id):
    try:
        execute("DELETE FROM varian_produk WHERE id_varian=%s", (varian_id,))
        flash("Varian berhasil dihapus.", "success")
    except Exception as exc:
        flash(f"Gagal hapus varian: {exc}", "danger")
    return redirect(url_for("varian_list"))


@app.route("/pesanan")
def pesanan_list():
    rows = fetch_all(
        """
        SELECT id_pesanan, no_invoice, nama_pelanggan, tgl_pesan, tgl_kirim, waktu_kirim,
               grand_total, status, status_bayar, metode_bayar
        FROM v_pesanan_lengkap
        ORDER BY id_pesanan DESC
        """
    )
    customers = fetch_all("SELECT id_pelanggan, nama FROM pelanggan ORDER BY nama")
    return render_template("pesanan.html", rows=rows, customers=customers)


def get_ongkir_pelanggan(id_pelanggan):
    row = fetch_one(
        """
        SELECT COALESCE(z.ongkir, 0) AS ongkir
        FROM pelanggan pel
        LEFT JOIN zona z ON z.id_zona = pel.id_zona
        WHERE pel.id_pelanggan = %s
        """,
        (id_pelanggan,),
    )
    if not row:
        return 0
    return row["ongkir"]


@app.post("/pesanan/add")
def pesanan_add():
    try:
        id_pelanggan = request.form.get("id_pelanggan")
        ongkir = get_ongkir_pelanggan(id_pelanggan)
        execute(
            """
            INSERT INTO pesanan (id_pelanggan, tgl_pesan, tgl_kirim, waktu_kirim, ongkir, metode_bayar, catatan)
            VALUES (%s, CURDATE(), %s, %s, %s, %s, %s)
            """,
            (
                id_pelanggan,
                request.form.get("tgl_kirim") or None,
                request.form.get("waktu_kirim") or None,
                ongkir,
                request.form.get("metode_bayar") or "cash",
                request.form.get("catatan") or None,
            ),
        )
        flash("Pesanan berhasil ditambahkan. Tambahkan detail produk langsung di database/modul lanjutan.", "success")
    except Exception as exc:
        flash(f"Gagal tambah pesanan: {exc}", "danger")
    return redirect(url_for("pesanan_list"))


@app.post("/pesanan/<int:pesanan_id>/edit")
def pesanan_edit(pesanan_id):
    try:
        execute(
            """
            UPDATE pesanan
            SET status=%s, status_bayar=%s, tgl_kirim=%s, waktu_kirim=%s, metode_bayar=%s, catatan=%s
            WHERE id_pesanan=%s
            """,
            (
                request.form.get("status") or "pending",
                request.form.get("status_bayar") or "belum_bayar",
                request.form.get("tgl_kirim") or None,
                request.form.get("waktu_kirim") or None,
                request.form.get("metode_bayar") or "cash",
                request.form.get("catatan") or None,
                pesanan_id,
            ),
        )
        flash("Pesanan berhasil diperbarui.", "success")
    except Exception as exc:
        flash(f"Gagal update pesanan: {exc}", "danger")
    return redirect(url_for("pesanan_list"))


@app.post("/pesanan/<int:pesanan_id>/delete")
def pesanan_delete(pesanan_id):
    try:
        execute("DELETE FROM pesanan WHERE id_pesanan=%s", (pesanan_id,))
        flash("Pesanan berhasil dihapus.", "success")
    except Exception as exc:
        flash(f"Gagal hapus pesanan: {exc}", "danger")
    return redirect(url_for("pesanan_list"))


@app.route("/pembayaran")
def pembayaran_list():
    rows = fetch_all(
        """
        SELECT pb.id_pembayaran, pb.id_pesanan, p.no_invoice, pb.jumlah, pb.metode, pb.tgl_bayar, pb.keterangan
        FROM pembayaran pb
        JOIN pesanan p ON p.id_pesanan = pb.id_pesanan
        ORDER BY pb.id_pembayaran DESC
        """
    )
    orders = fetch_all("SELECT id_pesanan, no_invoice FROM pesanan ORDER BY id_pesanan DESC")
    return render_template("pembayaran.html", rows=rows, orders=orders)


@app.post("/pembayaran/add")
def pembayaran_add():
    try:
        execute(
            """
            INSERT INTO pembayaran (id_pesanan, jumlah, tgl_bayar, metode, keterangan, received_by)
            VALUES (%s, %s, NOW(), %s, %s, NULL)
            """,
            (
                request.form.get("id_pesanan"),
                request.form.get("jumlah"),
                request.form.get("metode") or "cash",
                request.form.get("keterangan") or None,
            ),
        )
        flash("Pembayaran berhasil ditambahkan.", "success")
    except Exception as exc:
        flash(f"Gagal tambah pembayaran: {exc}", "danger")
    return redirect(url_for("pembayaran_list"))


@app.post("/pembayaran/<int:pembayaran_id>/delete")
def pembayaran_delete(pembayaran_id):
    try:
        execute("DELETE FROM pembayaran WHERE id_pembayaran=%s", (pembayaran_id,))
        flash("Pembayaran berhasil dihapus.", "success")
    except Exception as exc:
        flash(f"Gagal hapus pembayaran: {exc}", "danger")
    return redirect(url_for("pembayaran_list"))


if __name__ == "__main__":
    app.run(debug=True, port=int(os.getenv("APP_PORT", "5000")))
