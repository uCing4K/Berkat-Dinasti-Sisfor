/**
 * =========================================================================
 * SISTEM INFORMASI BERKAT DINASTI - BLUETOOTH PRINT BRIDGE CLIENT
 * Diadaptasi langsung dari arsitektur teruji Rodeo Laundry POS
 * 
 * Terintegrasi penuh dengan:
 * 1. Rodeo Print Bridge Android App (port 8080)
 * 2. Bridge Proxy Iframe (Bypass Mixed Content HTTPS di Chrome Mobile)
 * 3. Fallback direct fetch localhost:8080 & 127.0.0.1:8080
 * =========================================================================
 */

// Helper URL Generator untuk API ESC/POS (kompatibel cPanel & Localhost)
function getEscposApiUrl(orderId, paperSize = '58') {
    const isPublic = window.location.pathname.includes('/public/');
    const basePath = isPublic 
        ? window.location.pathname.substring(0, window.location.pathname.indexOf('/public/') + 8)
        : '/public/';
    return `${basePath}api/get-escpos-data.php?id=${encodeURIComponent(orderId)}&paper_size=${encodeURIComponent(paperSize)}`;
}

class BluetoothPrintBridge {
    constructor() {
        this.candidateUrls = [
            'http://localhost:8080',
            'http://127.0.0.1:8080'
        ];
        this.bridgeUrl = this.candidateUrls[0];
        this.isAvailable = false;
        this.useProxy = false;
        this.proxyIframe = null;
        this.proxyReady = false;
        this.requestId = 0;
        this.pendingRequests = new Map();
        
        // Cek apakah halaman berada di HTTPS (Mixed Content -> Gunakan Proxy Iframe)
        if (window.location.protocol === 'https:') {
            console.log('🔒 HTTPS terdeteksi - Menginisialisasi bridge-proxy.html untuk mixed content...');
            this.initProxy();
        }
    }
    
    initProxy() {
        this.useProxy = true;
        
        // Tentukan path proxy yang tepat
        const isPublic = window.location.pathname.includes('/public/');
        const proxyPath = isPublic
            ? window.location.pathname.substring(0, window.location.pathname.indexOf('/public/') + 8) + 'bridge-proxy.html'
            : '/bridge-proxy.html';
        
        this.proxyIframe = document.createElement('iframe');
        this.proxyIframe.src = proxyPath;
        this.proxyIframe.style.display = 'none';
        this.proxyIframe.setAttribute('sandbox', 'allow-scripts allow-same-origin');
        document.body.appendChild(this.proxyIframe);
        
        // Tangani pesan bolak-balik dari iframe proxy
        window.addEventListener('message', (event) => {
            if (event.data && event.data.type === 'proxyReady') {
                console.log('✅ Proxy iframe ready');
                this.proxyReady = true;
            } else if (event.data && event.data.requestId) {
                const callback = this.pendingRequests.get(event.data.requestId);
                if (callback) {
                    callback(event.data);
                    this.pendingRequests.delete(event.data.requestId);
                }
            }
        });
        
        console.log('📡 Proxy iframe dimuat:', proxyPath);
    }
    
    async sendProxyRequest(action, data = null) {
        return new Promise((resolve, reject) => {
            if (!this.proxyReady) {
                let attempts = 0;
                const checkReady = setInterval(() => {
                    attempts++;
                    if (this.proxyReady) {
                        clearInterval(checkReady);
                        this.sendProxyRequest(action, data).then(resolve).catch(reject);
                    } else if (attempts > 35) { // 3.5 detik timeout
                        clearInterval(checkReady);
                        reject(new Error('Proxy iframe tidak merespons (bridge-proxy.html).'));
                    }
                }, 100);
                return;
            }
            
            const requestId = ++this.requestId;
            
            this.pendingRequests.set(requestId, (response) => {
                if (response.success) {
                    resolve(response.result);
                } else {
                    reject(new Error(response.error || 'Proxy request gagal'));
                }
            });
            
            try {
                this.proxyIframe.contentWindow.postMessage({
                    action,
                    data,
                    requestId
                }, '*');
            } catch (err) {
                this.pendingRequests.delete(requestId);
                reject(err);
                return;
            }
            
            // Timeout 12 detik
            setTimeout(() => {
                if (this.pendingRequests.has(requestId)) {
                    this.pendingRequests.delete(requestId);
                    reject(new Error('Waktu tunggu proxy habis (12 detik).'));
                }
            }, 12000);
        });
    }
    
    async checkStatus() {
        try {
            // Pada HTTPS, hindari pre-check yang bisa memicu mixed content warning sebelum user klik cetak
            if (window.location.protocol === 'https:') {
                console.warn('⚠️ HTTPS terdeteksi - status check optimis diasumsikan aktif');
                this.isAvailable = true;
                return true;
            }
            
            if (this.useProxy) {
                const result = await this.sendProxyRequest('checkStatus');
                this.isAvailable = !!result.ok;
                return this.isAvailable;
            }
            
            for (const url of this.candidateUrls) {
                try {
                    const response = await fetch(`${url}/status`, {
                        method: 'GET',
                        headers: { 'Content-Type': 'application/json' },
                        signal: AbortSignal.timeout(2000)
                    });
                    if (response.ok) {
                        this.bridgeUrl = url;
                        this.isAvailable = true;
                        return true;
                    }
                } catch (e) {
                    // Coba host berikutnya
                }
            }
            
            this.isAvailable = false;
            return false;
        } catch (error) {
            console.error('Bridge status check failed:', error.message);
            if (window.location.protocol === 'https:') {
                return true;
            }
            this.isAvailable = false;
            return false;
        }
    }
    
    /**
     * Mengirim raw ESC/POS byte array ke Rodeo Print Bridge
     * @param {Array<number>} escposBytes - Array byte integer (0-255)
     */
    async printRawESCPOS(escposBytes) {
        if (!escposBytes || !Array.isArray(escposBytes) || escposBytes.length === 0) {
            throw new Error('Data ESC/POS tidak valid atau kosong.');
        }

        console.log(`[PrintBridge] Mempersiapkan pengiriman ${escposBytes.length} bytes ke Rodeo Print Bridge...`);
        
        // Format payload persis sesuai kontrak MainActivity.kt
        const payload = {
            escpos: escposBytes,
            timestamp: Date.now()
        };
        
        let lastError = null;

        // 1. Coba Direct Fetch ke localhost:8080 dan 127.0.0.1:8080
        // HANYA header 'Content-Type': 'application/json' (Persis seperti di rodeolaundry.online)
        for (const targetHost of this.candidateUrls) {
            try {
                console.log(`[PrintBridge] Mencoba direct fetch ke ${targetHost}/print...`);
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 8000); // 8s timeout
                
                const response = await fetch(`${targetHost}/print`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload),
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                console.log(`[PrintBridge] Respons direct fetch dari ${targetHost}:`, response.status);
                
                if (response.ok) {
                    const result = await response.json();
                    console.log('[PrintBridge] Hasil dari bridge:', result);
                    
                    if (result && result.success !== false) {
                        this.bridgeUrl = targetHost;
                        return {
                            success: true,
                            message: result.message || 'Struk berhasil dikirim ke printer thermal!'
                        };
                    } else {
                        throw new Error(result.message || result.error || 'Gagal mencetak. Periksa koneksi Bluetooth printer.');
                    }
                } else {
                    const errorText = await response.text();
                    throw new Error(`HTTP ${response.status}: ${errorText}`);
                }
            } catch (fetchErr) {
                console.warn(`[PrintBridge] Direct fetch ke ${targetHost} gagal:`, fetchErr.message);
                lastError = fetchErr;
                
                // Jika printer memang merespons tapi gagal print, langsung teruskan
                if (fetchErr.message && fetchErr.message.includes('Periksa koneksi Bluetooth printer')) {
                    throw fetchErr;
                }
            }
        }
        
        // 2. Jika Direct Fetch gagal (misal diblokir Mixed Content di browser mobile), coba via Proxy Iframe
        if (this.useProxy) {
            console.log('[PrintBridge] Direct fetch tidak tembus, mencoba via bridge-proxy.html iframe...');
            try {
                const proxyRes = await this.sendProxyRequest('print', payload);
                if (proxyRes && proxyRes.ok) {
                    const resData = proxyRes.data;
                    if (resData && resData.success !== false) {
                        return {
                            success: true,
                            message: resData.message || 'Struk berhasil dicetak via Print Bridge Proxy!'
                        };
                    } else {
                        throw new Error(resData.message || resData.error || 'Gagal mencetak. Periksa koneksi Bluetooth printer.');
                    }
                }
            } catch (proxyErr) {
                console.warn('[PrintBridge] Proxy iframe gagal:', proxyErr.message);
                if (proxyErr.message && proxyErr.message.includes('Periksa koneksi Bluetooth printer')) {
                    throw proxyErr;
                }
                lastError = proxyErr;
            }
        }
        
        // 3. Jika kedua metode tidak berhasil menghubungi bridge
        throw new Error(
            'Koneksi ke Rodeo Print Bridge tidak terhubung.\n\n' +
            'Pastikan:\n' +
            '1. Aplikasi Rodeo Print Bridge sudah dibuka di HP Android Anda\n' +
            '2. Printer thermal sudah connected (status hijau di aplikasi)\n' +
            '3. Website ini dibuka dari perangkat HP yang sama dengan aplikasi Print Bridge'
        );
    }

    /**
     * Cek status koneksi aplikasi Rodeo Print Bridge (port 8080)
     */
    async checkBridgeStatus() {
        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 1200);
            const resp = await fetch('http://localhost:8080/status', {
                method: 'GET',
                signal: controller.signal
            });
            clearTimeout(timeoutId);
            if (resp.ok) {
                const data = await resp.json().catch(() => ({}));
                return {
                    online: true,
                    deviceName: data.printer || data.device || 'Rodeo Print Bridge'
                };
            }
        } catch (e) {
            // Standby / Mixed content fallback
        }
        return { online: false, deviceName: null };
    }
    
    /**
     * Mengambil data ESC/POS dari PHP Server lalu mengirimkannya ke printer
     * @param {string|number} orderId - ID Pesanan atau 'test'
     * @param {string} paperSize - '58' atau '80'
     */
    async printOrder(orderId, paperSize = '58') {
        if (!orderId) {
            throw new Error('ID Pesanan tidak valid.');
        }
        
        const apiUrl = getEscposApiUrl(orderId, paperSize);
        console.log(`[PrintBridge] Mengambil data ESC/POS dari server: ${apiUrl}`);
        
        const response = await fetch(apiUrl);
        if (!response.ok) {
            const errText = await response.text();
            throw new Error(`API Error (${response.status}): ${errText}`);
        }
        
        const data = await response.json();
        if (data.error) {
            throw new Error(data.error);
        }
        
        if (!data.success || !Array.isArray(data.escpos)) {
            throw new Error('Format data ESC/POS dari server tidak valid.');
        }
        
        const printResult = await this.printRawESCPOS(data.escpos);
        return {
            ...printResult,
            byteCount: data.byte_count || data.escpos.length,
            order: data.order
        };
    }
    
    /**
     * Cetak Uji Coba dari Pengaturan
     */
    async printTestReceipt(paperSize = '58') {
        return await this.printOrder('test', paperSize);
    }
    
    /**
     * Download Raw Binary file (.bin) untuk pengujian manual
     */
    async downloadRawEscpos(orderId = 'test', paperSize = '58') {
        const apiUrl = getEscposApiUrl(orderId, paperSize);
        const res = await fetch(apiUrl);
        const data = await res.json();
        if (!data.success || !Array.isArray(data.escpos)) {
            throw new Error(data.error || 'Gagal membuat raw ESC/POS');
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
window.printBridge = new BluetoothPrintBridge();

// Helper global yang persis seperti di rodeolaundry.online
window.printOrderReceipt = function(orderId, paperSize = '58') {
    return handlePrintReceipt(orderId, null, paperSize);
};

/**
 * Trigger Cetak untuk Tombol di UI Pesanan / Detail / Beranda
 */
async function handlePrintReceipt(orderId, btnElement = null, paperSize = null) {
    let originalHtml = '';
    if (btnElement) {
        originalHtml = btnElement.innerHTML;
        btnElement.disabled = true;
        btnElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Mencetak...</span>';
    }

    // Ambil default ukuran kertas dari setting toko jika tidak dispesifikasikan
    if (!paperSize) {
        const is58 = (document.getElementById('storePaperSize58') && document.getElementById('storePaperSize58').checked) ||
                     (window.STORE_PAPER_SIZE === '58');
        paperSize = is58 ? '58' : (window.STORE_PAPER_SIZE || '58');
    }

    try {
        const res = await window.printBridge.printOrder(orderId, paperSize);
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Struk Berhasil Dicetak!',
                text: res.message || 'Data nota pesanan berhasil dikirim ke printer thermal.',
                timer: 2500,
                showConfirmButton: false
            });
        } else {
            alert(`✅ ${res.message || 'Data nota pesanan berhasil dikirim ke printer thermal.'}`);
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
            alert(`⚠️ PRINT GAGAL:\n\n${err.message}`);
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
        btnElement.innerHTML = '<i class="fas fa-spinner fa-spin text-[#D46E15]"></i> <span>Mengirim ke Bridge...</span>';
    }

    const printerStatus = document.getElementById('modalPrinterStatus');
    const is58 = (document.getElementById('storePaperSize58') && document.getElementById('storePaperSize58').checked) ||
                 (document.getElementById('paper58') && document.getElementById('paper58').checked) ||
                 (window.STORE_PAPER_SIZE === '58');
    const paperSize = is58 ? '58' : '80';

    if (printerStatus) {
        printerStatus.innerHTML = '<span class="text-blue-600 font-bold"><i class="fas fa-sync fa-spin"></i> Menghubungi Rodeo Print Bridge...</span>';
    }

    try {
        const res = await window.printBridge.printTestReceipt(paperSize);
        
        if (printerStatus) {
            printerStatus.innerHTML = '<span class="text-emerald-600 font-bold"><i class="fas fa-check-circle"></i> Berhasil! Byte terkirim: ' + (res.byteCount || 0) + ' bytes</span>';
            setTimeout(() => {
                printerStatus.textContent = 'Printer Thermal: Siap Mencetak';
            }, 3500);
        }
        
        if (typeof showToast === 'function') {
            showToast('Uji coba cetak berhasil dikirim ke printer Bluetooth!', '<i class="fas fa-check-circle text-emerald-600"></i>');
        } else {
            alert('✅ Uji coba cetak berhasil dikirim ke printer thermal Bluetooth!');
        }
    } catch (err) {
        console.error('[PrintBridge Test Error]', err);
        
        if (printerStatus) {
            printerStatus.innerHTML = '<span class="text-rose-600 font-bold"><i class="fas fa-exclamation-triangle"></i> Bridge Belum Merespons</span>';
            setTimeout(() => {
                printerStatus.textContent = 'Printer Thermal: Siap Mencetak';
            }, 5000);
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Print Bridge Belum Terhubung',
                text: err.message,
                confirmButtonColor: '#D46E15',
                confirmButtonText: 'Siap Mas'
            });
        } else {
            alert(`⚠️ ${err.message}`);
        }
    } finally {
        if (btnElement) {
            btnElement.disabled = false;
            btnElement.innerHTML = originalHtml;
        }
    }
}

// Export window methods
window.handlePrintReceipt = handlePrintReceipt;
window.handleTestPrintBluetooth = handleTestPrintBluetooth;

console.log('✅ Bluetooth Print Bridge Berkat Dinasti terinisialisasi.');
