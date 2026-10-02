<?php
// Deteksi environment berdasarkan HTTP_HOST
if (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1')) {
    // Kredensial LOKAL (XAMPP/Laragon)
    define("DB_HOST", "localhost");
    define("DB_USER", "root");
    define("DB_PASS", "");
    define("DB_NAME", "berkat_dinasti");
    define("BASE_URL", "http://localhost/Berkat-Dinasti/public/");
} else {
    // Kredensial PRODUKSI (cPanel)
    define("DB_HOST", "localhost"); // Di cPanel biasanya host database tetap 'localhost'
    define("DB_USER", "rodd1157_berkat_dinasti");
    define("DB_PASS", "kekuatanberkatdinasti");
    define("DB_NAME", "rodd1157_berkat_dinasti_db");
    define("BASE_URL", "https://ex5.ucing4k.my.id/");
}
?>
