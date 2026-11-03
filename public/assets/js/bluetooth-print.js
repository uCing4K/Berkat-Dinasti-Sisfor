/**
 * =========================================================================
 * SISTEM INFORMASI BERKAT DINASTI - BLUETOOTH PRINT CLIENT (V2 - HYBRID)
 * Sesuai Dokumen Panduan: PANDUAN_IMPLEMENTASI_PRINT_BRIDGE_DAN_TUNING_THERMAL.md
 * 
 * Kapabilitas:
 * 1. HTTP Local Print Bridge (Utamakan 127.0.0.1:8080, lalu localhost:8080)
 * 2. Optimasi Header CORS Simple Request (Cegah Blokir PNA/Mixed Content Chrome)
 * 3. Direct Android Intent / RawBT Scheme (rawbt:base64,...) - Standar Terbaik di HP
 * 4. Web Bluetooth API (Chrome Native BLE Printing)
 * 5. Modal Diagnostik & Solusi Interaktif Otomatis saat Bridge Offline
 * =========================================================================
 */

class PrintBridgeClient {
    constructor() {
        this.candidateUrls = [
            'http://127.0.0.1:8080',
            'http://localhost:8080'
        ];
        this.activeBridgeUrl = this.candidateUrls[0];
        this.timeoutMs = 4500; // Snappy timeout, tidak membuat user menunggu 12 detik
        this.lastStatus = null;
    }

    /**
     * Memeriksa status kesiapan printer bridge (Android / Desktop Daemon)
     * Menguji 127.0.0.1 dahulu lalu localhost
     */
    async checkBridgeStatus() {
        for (const url of this.candidateUrls) {
            try {
                const controller = new AbortController();
                const timer = setTimeout(() => controller.abort(), 1800);

                const res = await fetch(`${url}/status`, {
                    method: 'GET',
                    signal: controller.signal
                });
                clearTimeout(timer);

                if (res.ok) {
                    const data = await res.json();
                    this.activeBridgeUrl = url;
                    this.lastStatus = {
                        online: data.status === 'online',
                        printerConnected: !!data.printer_connected,
                        deviceName: data.device_name || 'Printer Bluetooth',
                        host: url,
                        raw: data
                    };
                    return this.lastStatus;
                }
            } catch (e) {
                // Abaikan dan coba URL kandidat berikutnya
            }
        }

        this.lastStatus = {
            online: false,
            reason: 'Bridge Offline / Tertidur di background'
        };
        return this.lastStatus;
    }

    /**
     * Mengambil raw data ESC/POS (array bytes & base64) dari Backend PHP
     */
    async fetchReceiptData(orderId, paperSize = '58') {
        const apiUrl = `api/get-escpos-data.php?id=${encodeURIComponent(orderId)}&paper_size=${encodeURIComponent(paperSize)}`;
        const apiResponse = await fetch(apiUrl, {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        });

        if (!apiResponse.ok) {
            const errText = await apiResponse.text();
            throw new Error(`Gagal membuat data ESC/POS dari server (HTTP ${apiResponse.status}): ${errText}`);
        }

        const data = await apiResponse.json();
        if (!data.success || !Array.isArray(data.escpos)) {
            throw new Error(data.error || "Format data ESC/POS yang diterima dari server tidak valid.");
        }

        return data;
    }

    /**
     * Mengirim raw ESC/POS ke Print Bridge lokal via HTTP POST
     * Menggunakan CORS Simple Request (text/plain) agar tidak terganjal preflight ketat Chrome
     */
    async printOrder(orderId, paperSize = '58') {
        if (!orderId) {
            throw new Error("ID Pesanan tidak valid.");
        }

        console.log(`[PrintBridge] 1. Mengambil data ESC/POS untuk pesanan: ${orderId} (${paperSize}mm)...`);
        const data = await this.fetchReceiptData(orderId, paperSize);

        const escposBytes = data.escpos;
        const base64Data = data.base64 || '';
        const byteCount = data.byte_count || escposBytes.length;

        console.log(`[PrintBridge] 2. Data ESC/POS siap (${byteCount} bytes). Mengirim ke Print Bridge...`);

        const payload = {
            type: 'raw_escpos',
            escpos: escposBytes,
            base64: base64Data,
            order_id: orderId,
            paper_size: paperSize,
            timestamp: Date.now()
        };

        const jsonBody = JSON.stringify(payload);
        let bridgeSuccess = false;
        let bridgeResult = null;
        let lastError = null;

        // Coba koneksi ke kandidat URL (127.0.0.1 dahulu, baru localhost)
        for (const targetHost of this.candidateUrls) {
            try {
                console.log(`[PrintBridge] Mencoba HTTP POST ke ${targetHost}/print...`);
                const controller = new AbortController();
                const timer = setTimeout(() => controller.abort(), this.timeoutMs);

                // Menggunakan text/plain agar browser memperlakukan sebagai CORS Simple Request
                const bridgeResponse = await fetch(`${targetHost}/print`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'text/plain;charset=UTF-8'
                    },
                    body: jsonBody,
                    signal: controller.signal
                });
                clearTimeout(timer);

                if (bridgeResponse.ok) {
                    const result = await bridgeResponse.json();
                    if (result && result.success !== false) {
                        bridgeSuccess = true;
                        bridgeResult = result;
                        this.activeBridgeUrl = targetHost;
                        break;
                    } else {
                        throw new Error(result.message || "Printer merespons gagal mencetak.");
                    }
                } else {
                    const errText = await bridgeResponse.text();
                    throw new Error(`HTTP ${bridgeResponse.status}: ${errText}`);
                }
            } catch (err) {
                console.warn(`[PrintBridge] Koneksi ke ${targetHost} gagal:`, err.message);
                lastError = err;
            }
        }

        if (bridgeSuccess) {
            console.log(`[PrintBridge] 3. Sukses terkirim ke printer via ${this.activeBridgeUrl}:`, bridgeResult);
            return {
                success: true,
                message: bridgeResult?.message || "Data struk berhasil dikirim ke printer thermal.",
                byteCount: byteCount,
                order: data.order,
                base64: base64Data
            };
        }

        // Jika semua port HTTP lokal gagal merespons
        const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
        const errObj = new Error(
            isMobile
                ? "Aplikasi Print Bridge di HP tidak merespons dalam " + Math.round(this.timeoutMs / 1000) + " detik.\nKemungkinan aplikasi terminimalkan dan dibekukan oleh sistem Android (Doze Mode), atau terhalang restriksi jaringan privat Chrome."
                : "Tidak dapat terhubung ke Aplikasi Print Bridge di http://127.0.0.1:8080 atau localhost:8080."
        );
        errObj.isBridgeUnavailable = true;
        errObj.isMobile = isMobile;
        errObj.base64 = base64Data;
        errObj.orderId = orderId;
        errObj.paperSize = paperSize;
        errObj.order = data.order;
        throw errObj;
    }

    /**
     * Cetak Struk Langsung via Android Intent / RawBT / Deep Link (100% Reliable di HP)
     * Format: rawbt:base64,<BASE64>
     */
    printViaIntent(base64Data, orderId = 'test') {
        if (!base64Data) {
            throw new Error("Data struk Base64 tidak tersedia.");
        }

        console.log(`[PrintBridge] Memicu Android Intent / RawBT (${base64Data.length} karakter Base64)...`);

        // Format Standar RawBT / Print Bridge URI Scheme
        const rawbtUrl = `rawbt:base64,${base64Data}`;

        try {
            window.location.href = rawbtUrl;
            return {
                success: true,
                message: "Perintah cetak telah diteruskan ke Aplikasi Android (RawBT / Print Bridge)."
            };
        } catch (e) {
            window.open(rawbtUrl, '_blank');
            return {
                success: true,
                message: "Membuka aplikasi cetak Android..."
            };
        }
    }

    /**
     * Mengambil data nota lalu langsung meneruskan ke RawBT / Mobile Intent
     */
    async printViaRawBT(orderId = 'test', paperSize = '58') {
        const data = await this.fetchReceiptData(orderId, paperSize);
        if (!data.base64) {
            throw new Error("Gagal meng-generate payload Base64 untuk RawBT.");
        }
        return this.printViaIntent(data.base64, orderId);
    }

    /**
     * Cetak Langsung dari Web via Web Bluetooth API (Native Chrome BLE)
     * Tidak membutuhkan aplikasi perantara jika printer mendukung BLE
     */
    async printViaWebBluetooth(orderId = 'test', paperSize = '58') {
        if (!navigator.bluetooth) {
            throw new Error("Web Bluetooth tidak didukung di browser ini. Gunakan Google Chrome di Android atau Desktop.");
        }

        console.log("[PrintBridge] Memulai Web Bluetooth pairing...");
        const data = await this.fetchReceiptData(orderId, paperSize);
        const bytes = new Uint8Array(data.escpos);

        // Standard Serial Port / Printer Service UUIDs
        const device = await navigator.bluetooth.requestDevice({
            acceptAllDevices: true,
            optionalServices: [
                '000018f0-0000-1000-8000-00805f9b34fb',
                '49535343-fe7d-4ae5-8fa9-9fafd205e455',
                'e7810a71-73ae-499d-8c15-faa9aef0c3f2'
            ]
        });

        console.log(`[WebBluetooth] Menghubungkan ke ${device.name}...`);
        const server = await device.gatt.connect();
        
        // Cari primary service dan writable characteristic
        const services = await server.getPrimaryServices();
        let writeChar = null;

        for (const service of services) {
            const chars = await service.getCharacteristics();
            for (const ch of chars) {
                if (ch.properties.write || ch.properties.writeWithoutResponse) {
                    writeChar = ch;
                    break;
                }
            }
            if (writeChar) break;
        }

        if (!writeChar) {
            throw new Error("Tidak menemukan GATT Characteristic untuk menulis data ke printer.");
        }

        // Tulis dalam potongan (chunk) 512 bytes + jeda 20ms
        const chunkSize = 512;
        for (let i = 0; i < bytes.length; i += chunkSize) {
            const chunk = bytes.slice(i, i + chunkSize);
            if (writeChar.properties.writeWithoutResponse) {
                await writeChar.writeValueWithoutResponse(chunk);
            } else {
                await writeChar.writeValue(chunk);
            }
            await new Promise(r => setTimeout(r, 20));
        }

        console.log("[WebBluetooth] Cetak selesai via BLE.");
        return { success: true, message: `Struk berhasil dicetak langsung via Web Bluetooth (${device.name}).` };
    }

    /**
     * Uji Coba Cetak Struk (Test Print ESC/POS)
     */
    async printTestReceipt(paperSize = '58') {
        return await this.printOrder('test', paperSize);
    }

    /**
     * Download Raw Binary file (.bin) untuk pengujian manual / debugging
     */
    async downloadRawEscpos(orderId = 'test', paperSize = '58') {
        const data = await this.fetchReceiptData(orderId, paperSize);
        const uint8 = new Uint8Array(data.escpos);
        const blob = new Blob([uint8], { type: 'application/octet-stream' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `receipt-${orderId}-${paperSize}mm.bin`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
}

// Inisialisasi Instance Global
window.printBridge = new PrintBridgeClient();

/**
 * =========================================================================
 * MODAL PANDUAN & SOLUSI CETAK MOBILE (ANTI-DEAD-END)
 * Menampilkan pilihan solutif instan ketika port 8080 offline/tertidur di HP
 * =========================================================================
 */
function showPrintFallbackModal({ orderId = 'test', paperSize = '58', base64 = '', message = '' }) {
    let modal = document.getElementById('printFallbackModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'printFallbackModal';
        modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm';
        document.body.appendChild(modal);
    }

    modal.innerHTML = `
        <div class="bg-[#FDFBF7] border border-[#E2D7C8] rounded-2xl max-w-md w-full p-5 shadow-2xl space-y-4 animate-scale-in text-[#251A14]">
            <!-- Header Modal -->
            <div class="flex items-start justify-between gap-3 border-b border-[#E2D7C8] pb-3.5">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-amber-500/15 text-amber-700 flex items-center justify-center shrink-0">
                        <i class="fas fa-satellite-dish text-base"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-[#251A14]">Koneksi Print Bridge Belum Merespons</h4>
                        <p class="text-[11px] text-[#8E7E73]">Solusi Cetak Cepat Thermal Bluetooth</p>
                    </div>
                </div>
                <button type="button" onclick="closePrintFallbackModal()" class="text-[#8E7E73] hover:text-[#251A14] p-1.5 rounded-lg hover:bg-[#EFE8DE] transition-colors">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            <!-- Penjelasan Teknis Singkat & Akurat -->
            <div class="bg-amber-50 border border-amber-200/80 rounded-xl p-3 text-[11px] text-amber-900 leading-relaxed space-y-1.5">
                <p class="font-semibold flex items-center gap-1.5">
                    <i class="fas fa-info-circle text-amber-600"></i>
                    <span>Penyebab Utama di Smartphone Android:</span>
                </p>
                <p>
                    Aplikasi Print Bridge di HP Anda <strong>sedang terminimalkan</strong> sehingga koneksi port 8080 dibekukan oleh sistem Android (Doze Mode), atau terhalang kebijakan keamanan Chrome (Private Network Access).
                </p>
            </div>

            <!-- Action Buttons Solutif -->
            <div class="space-y-2.5 pt-1">
                <!-- Tombol 1: Rekomendasi Emas Mobile (RawBT / Intent) -->
                <button type="button" id="btnFallbackRawbt" class="w-full bg-[#D46E15] hover:bg-[#B85207] text-white font-bold text-xs py-3 px-4 rounded-xl flex items-center justify-center gap-2 shadow-button-orange active:scale-95 transition-all">
                    <i class="fas fa-bolt text-amber-300"></i>
                    <span>📱 Cetak Instan via RawBT / Mobile Intent</span>
                </button>

                <!-- Tombol 2: Cetak via Browser (Android Print Spooler) -->
                <button type="button" id="btnFallbackBrowser" class="w-full bg-white hover:bg-[#F4EFEA] text-[#3E2B21] border border-[#DDD3C5] font-semibold text-xs py-2.5 px-4 rounded-xl flex items-center justify-center gap-2 transition-colors">
                    <i class="fas fa-print text-[#D46E15]"></i>
                    <span>🖨️ Cetak via Dialog Browser</span>
                </button>

                <!-- Tombol 3: Tips Split Screen Android -->
                <button type="button" id="btnToggleSplitScreenTips" class="w-full bg-[#EFE8DE] hover:bg-[#E5DDD0] text-[#544337] font-medium text-xs py-2 px-3 rounded-xl flex items-center justify-between transition-colors">
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-columns text-[#0082FC]"></i>
                        <span>💡 Cara Tetap Pakai Print Bridge di HP</span>
                    </span>
                    <i class="fas fa-chevron-down text-[10px]" id="iconTipsChevron"></i>
                </button>

                <!-- Collapsible Tips Android -->
                <div id="splitScreenTipsContent" class="hidden bg-white p-3 rounded-xl border border-[#E2D7C8] text-[11px] text-[#544337] space-y-1.5 leading-relaxed">
                    <p class="font-bold text-[#251A14]">Agar Print Bridge tidak dibekukan Android:</p>
                    <ol class="list-decimal pl-4 space-y-1">
                        <li>Buka menu <strong>Recent Apps</strong> di HP Anda.</li>
                        <li>Ketuk ikon aplikasi Print Bridge lalu pilih <strong>Layar Terpisah (Split Screen)</strong> atau <strong>Pop-up View</strong>.</li>
                        <li>Buka Google Chrome di separuh layar lainnya.</li>
                        <li>Di <em>Info Aplikasi HP &rarr; Baterai</em>, pilih <strong>Tidak Dibatasi (Unrestricted)</strong> agar server tidak mati saat layar terkunci.</li>
                    </ol>
                </div>
            </div>

            <!-- Footer Close -->
            <div class="pt-2 text-center">
                <button type="button" onclick="closePrintFallbackModal()" class="text-xs text-[#8E7E73] hover:text-[#251A14] font-medium transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    `;

    // Event Listeners
    document.getElementById('btnFallbackRawbt').onclick = async function() {
        closePrintFallbackModal();
        try {
            if (base64) {
                window.printBridge.printViaIntent(base64, orderId);
            } else {
                await window.printBridge.printViaRawBT(orderId, paperSize);
            }
        } catch (e) {
            alert('Gagal memicu RawBT: ' + e.message);
        }
    };

    document.getElementById('btnFallbackBrowser').onclick = function() {
        closePrintFallbackModal();
        if (typeof executeDirectPrint === 'function') {
            executeDirectPrint();
        } else {
            window.print();
        }
    };

    const btnTips = document.getElementById('btnToggleSplitScreenTips');
    const tipsContent = document.getElementById('splitScreenTipsContent');
    const tipsChevron = document.getElementById('iconTipsChevron');
    if (btnTips && tipsContent) {
        btnTips.onclick = function() {
            const isHidden = tipsContent.classList.toggle('hidden');
            tipsChevron.className = isHidden ? 'fas fa-chevron-down text-[10px]' : 'fas fa-chevron-up text-[10px]';
        };
    }

    modal.classList.remove('hidden');
}

function closePrintFallbackModal() {
    const modal = document.getElementById('printFallbackModal');
    if (modal) modal.classList.add('hidden');
}

/**
 * =========================================================================
 * TRIGGER CETAK PESANAN (KANBAN / DETAIL / BERANDA)
 * =========================================================================
 */
async function handlePrintReceipt(orderId, btnElement = null, paperSize = '58') {
    let originalHtml = '';
    if (btnElement) {
        originalHtml = btnElement.innerHTML;
        btnElement.disabled = true;
        btnElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Mencetak...</span>';
    }

    try {
        const res = await window.printBridge.printOrder(orderId, paperSize);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Struk Terkirim ke Printer!',
                text: res.message || 'Nota pesanan berhasil dikirim ke printer thermal.',
                timer: 2500,
                showConfirmButton: false
            });
        } else {
            alert(`✅ ${res.message || 'Nota pesanan berhasil dikirim ke printer thermal.'}`);
        }
    } catch (err) {
        console.error('[PrintBridge Error]', err);

        // Jika terdeteksi di mobile atau bridge tidak merespons, tampilkan modal solutif
        if (err.isBridgeUnavailable || /Android|iPhone/i.test(navigator.userAgent)) {
            showPrintFallbackModal({
                orderId: orderId,
                paperSize: paperSize,
                base64: err.base64 || '',
                message: err.message
            });
        } else {
            alert(`❌ Gagal Menghubungi Printer:\n\n${err.message}`);
        }
    } finally {
        if (btnElement) {
            btnElement.disabled = false;
            btnElement.innerHTML = originalHtml;
        }
    }
}

/**
 * =========================================================================
 * TRIGGER CETAK UJI COBA DARI HALAMAN PENGATURAN (MODAL UJI COBA)
 * =========================================================================
 */
async function handleTestPrintBluetooth(btnElement = null) {
    let originalHtml = '';
    if (btnElement) {
        originalHtml = btnElement.innerHTML;
        btnElement.disabled = true;
        btnElement.innerHTML = '<i class="fas fa-spinner fa-spin text-blue-500"></i> <span>Mengirim ke Bridge...</span>';
    }

    const printerStatus = document.getElementById('modalPrinterStatus');
    const is58 = document.getElementById('paper58') && document.getElementById('paper58').checked;
    const paperSize = is58 ? '58' : '80';

    if (printerStatus) {
        printerStatus.innerHTML = '<span class="text-blue-600 font-bold"><i class="fas fa-sync fa-spin"></i> Menghubungi Bridge (127.0.0.1:8080)...</span>';
    }

    try {
        const res = await window.printBridge.printTestReceipt(paperSize);
        if (printerStatus) {
            printerStatus.innerHTML = '<span class="text-emerald-600 font-bold"><i class="fas fa-check-circle"></i> Berhasil! Byte terkirim: ' + res.byteCount + ' bytes</span>';
            setTimeout(() => {
                printerStatus.textContent = 'Printer Thermal: Siap Mencetak';
            }, 3500);
        }
        if (typeof showToast === 'function') {
            showToast('Uji coba cetak berhasil dikirim ke printer thermal Bluetooth!', '<i class="fas fa-check-circle text-emerald-600"></i>');
        } else {
            alert('✅ Uji coba cetak berhasil dikirim ke printer thermal Bluetooth!');
        }
    } catch (err) {
        console.error('[PrintBridge Test Error]', err);
        if (printerStatus) {
            printerStatus.innerHTML = '<span class="text-rose-600 font-bold"><i class="fas fa-exclamation-triangle"></i> Bridge Offline / Tertidur</span>';
            setTimeout(() => {
                printerStatus.textContent = 'Printer Thermal: Siap Mencetak';
            }, 5000);
        }

        // Tampilkan modal panduan & tombol aksi RawBT langsung
        showPrintFallbackModal({
            orderId: 'test',
            paperSize: paperSize,
            base64: err.base64 || '',
            message: err.message
        });
    } finally {
        if (btnElement) {
            btnElement.disabled = false;
            btnElement.innerHTML = originalHtml;
        }
    }
}

/**
 * =========================================================================
 * TRIGGER CETAK LANGSUNG VIA RAWBT / MOBILE INTENT (TOMBOL SPESIFIK HP)
 * =========================================================================
 */
async function handlePrintViaRawBT(orderId = 'test', btnElement = null) {
    let originalHtml = '';
    if (btnElement) {
        originalHtml = btnElement.innerHTML;
        btnElement.disabled = true;
        btnElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Membuka App...</span>';
    }

    const is58 = document.getElementById('paper58') && document.getElementById('paper58').checked;
    const paperSize = is58 ? '58' : '80';

    try {
        await window.printBridge.printViaRawBT(orderId, paperSize);
        if (typeof showToast === 'function') {
            showToast('Data struk dikirim ke aplikasi printer Bluetooth!', '<i class="fas fa-mobile-alt text-[#2563EB]"></i>');
        }
    } catch (err) {
        console.error('[RawBT Print Error]', err);
        alert(`❌ Gagal memicu cetak mobile: ${err.message}`);
    } finally {
        if (btnElement) {
            btnElement.disabled = false;
            btnElement.innerHTML = originalHtml;
        }
    }
}
