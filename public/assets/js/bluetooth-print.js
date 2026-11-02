/**
 * =========================================================================
 * SISTEM INFORMASI BERKAT DINASTI - BLUETOOTH PRINT BRIDGE CLIENT
 * Sesuai Dokumen Panduan: PANDUAN_IMPLEMENTASI_PRINT_BRIDGE_DAN_TUNING_THERMAL.md
 * =========================================================================
 */

class PrintBridgeClient {
    constructor() {
        this.bridgeUrl = 'http://localhost:8080';
        this.timeoutMs = 12000;
        this.lastStatus = null;
    }

    /**
     * Memeriksa status kesiapan printer bridge (Android / Desktop Daemon)
     * Endpoint: GET http://localhost:8080/status
     */
    async checkBridgeStatus() {
        try {
            const controller = new AbortController();
            const timer = setTimeout(() => controller.abort(), 2500);

            const res = await fetch(`${this.bridgeUrl}/status`, {
                method: 'GET',
                signal: controller.signal,
                headers: {
                    'Accept': 'application/json'
                }
            });
            clearTimeout(timer);

            if (!res.ok) {
                this.lastStatus = { online: false, reason: `HTTP ${res.status}` };
                return this.lastStatus;
            }

            const data = await res.json();
            this.lastStatus = {
                online: data.status === 'online',
                printerConnected: !!data.printer_connected,
                deviceName: data.device_name || 'Printer Bluetooth',
                raw: data
            };
            return this.lastStatus;
        } catch (e) {
            this.lastStatus = {
                online: false,
                reason: e.name === 'AbortError' ? 'Timeout' : e.message
            };
            return this.lastStatus;
        }
    }

    /**
     * Mengambil raw bytes ESC/POS dari Backend PHP dan mengirimkannya ke Print Bridge
     * @param {string|number} orderId - ID Pesanan / Invoice / 'test'
     * @param {string} paperSize - '58' atau '80'
     */
    async printOrder(orderId, paperSize = '58') {
        if (!orderId) {
            throw new Error("ID Pesanan tidak valid.");
        }

        console.log(`[PrintBridge] 1. Mengambil data ESC/POS untuk pesanan: ${orderId} (${paperSize}mm)...`);

        // 1. Ambil data raw ESC/POS dari PHP Server (Same-Origin)
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

        console.log(`[PrintBridge] 2. Data ESC/POS berhasil dibuat (${data.byte_count || data.escpos.length} bytes). Mengirim ke Print Bridge (${this.bridgeUrl}/print)...`);

        // 2. Kirim payload raw bytes ke Print Bridge lokal via HTTP POST
        const payload = {
            type: 'raw_escpos',
            escpos: data.escpos,
            order_id: orderId,
            paper_size: paperSize,
            timestamp: Date.now()
        };

        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), this.timeoutMs);

        try {
            const bridgeResponse = await fetch(`${this.bridgeUrl}/print`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload),
                signal: controller.signal
            });
            clearTimeout(timer);

            if (!bridgeResponse.ok) {
                const bridgeErr = await bridgeResponse.text();
                throw new Error(`Print Bridge merespons error (HTTP ${bridgeResponse.status}): ${bridgeErr}`);
            }

            const result = await bridgeResponse.json();
            if (!result.success) {
                throw new Error(result.message || "Printer gagal mencetak data struk.");
            }

            console.log(`[PrintBridge] 3. Data berhasil dikirim ke printer:`, result);
            return {
                success: true,
                message: result.message || "Data struk berhasil dikirim ke printer thermal.",
                byteCount: data.escpos.length,
                order: data.order
            };
        } catch (fetchErr) {
            if (fetchErr.name === 'AbortError') {
                throw new Error("Waktu tunggu habis (Timeout): Aplikasi Print Bridge tidak merespons dalam 12 detik. Periksa koneksi Bluetooth printer.");
            }
            if (fetchErr.message.includes('Failed to fetch') || fetchErr.message.includes('NetworkError') || fetchErr.message.includes('ERR_CONNECTION_REFUSED')) {
                throw new Error(
                    "Tidak dapat terhubung ke Aplikasi Print Bridge di http://localhost:8080.\n\n" +
                    "Langkah Pengecekan:\n" +
                    "1. Pastikan Aplikasi 'Print Bridge' di perangkat Android / PC Anda sudah DIBUKA dan statusnya AKTIF.\n" +
                    "2. Pastikan printer thermal Bluetooth sudah dalam status 'Connected'.\n" +
                    "3. Jika membuka web dari HP/Tablet kasir, buka dari perangkat yang SAMA dengan aplikasi bridge."
                );
            }
            throw fetchErr;
        }
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
        const apiUrl = `api/get-escpos-data.php?id=${encodeURIComponent(orderId)}&paper_size=${encodeURIComponent(paperSize)}`;
        const res = await fetch(apiUrl);
        const data = await res.json();
        if (!data.success || !Array.isArray(data.escpos)) {
            throw new Error(data.error || "Gagal membuat raw ESC/POS");
        }
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
 * Trigger Cetak untuk Tombol di UI Pesanan / Kanban / Detail
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
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Gagal Menghubungi Printer',
                text: err.message,
                confirmButtonColor: '#D46E15',
                confirmButtonText: 'Mengerti'
            });
        } else {
            alert(`❌ Gagal mencetak:\n${err.message}`);
        }
    } finally {
        if (btnElement) {
            btnElement.disabled = false;
            btnElement.innerHTML = originalHtml;
        }
    }
}

/**
 * Trigger Cetak Uji Coba dari Halaman Pengaturan (Modal Cetak Uji Coba)
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
        printerStatus.innerHTML = '<span class="text-blue-600 font-bold"><i class="fas fa-sync fa-spin"></i> Menghubungi Bridge (Port 8080)...</span>';
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
            printerStatus.innerHTML = '<span class="text-rose-600 font-bold"><i class="fas fa-exclamation-triangle"></i> Bridge Offline / Gagal</span>';
            setTimeout(() => {
                printerStatus.textContent = 'Printer Thermal: Siap Mencetak';
            }, 5000);
        }

        // Tampilkan pesan panduan yang sangat jelas
        const guidanceMsg = 
            "⚠️ KONEKSI PRINT BRIDGE BELUM TERHUBUNG\n\n" +
            err.message + "\n\n" +
            "💡 Info: Sistem Web Berkat Dinasti sudah siap 100% menghasilkan raw data ESC/POS dengan logo binarisasi dan tuning teks tajam.\n" +
            "Gunakan tombol 'Cetak via Browser' untuk simulasi cetak langsung di PC, atau buka aplikasi Print Bridge di Android Anda.";
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Print Bridge Offline',
                html: `<div class="text-left text-xs space-y-2 whitespace-pre-line leading-relaxed text-[#544337]">${guidanceMsg}</div>`,
                confirmButtonColor: '#D46E15',
                confirmButtonText: 'Siap Mas'
            });
        } else {
            alert(guidanceMsg);
        }
    } finally {
        if (btnElement) {
            btnElement.disabled = false;
            btnElement.innerHTML = originalHtml;
        }
    }
}
