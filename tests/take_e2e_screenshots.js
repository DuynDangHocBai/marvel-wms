const { spawn } = require('child_process');
const fs = require('fs');
const path = require('path');

const CHROME_PATH = "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe";
const BASE_URL = 'http://localhost:81/marvel-wms/public/';
const USER_DATA_DIR = path.join(__dirname, '..', '.chrome_temp');
const SCREENSHOT_DIR = path.join(__dirname, '..', 'docs', 'screenshots');

if (!fs.existsSync(SCREENSHOT_DIR)) {
    fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });
}

async function sleep(ms) {
    return new Promise(r => setTimeout(r, ms));
}

async function getWsUrl() {
    for (let i = 0; i < 30; i++) {
        try {
            const res = await fetch('http://127.0.0.1:9222/json/version');
            const data = await res.json();
            if (data.webSocketDebuggerUrl) return data.webSocketDebuggerUrl;
        } catch (e) {
            await sleep(300);
        }
    }
    throw new Error('Could not connect to Chrome debugging port');
}

class CDPClient {
    constructor(wsUrl) {
        this.ws = new WebSocket(wsUrl);
        this.id = 1;
        this.callbacks = new Map();
        this.ws.onmessage = (event) => {
            const msg = JSON.parse(event.data);
            if (msg.id && this.callbacks.has(msg.id)) {
                const { resolve, reject } = this.callbacks.get(msg.id);
                this.callbacks.delete(msg.id);
                if (msg.error) reject(msg.error);
                else resolve(msg.result);
            }
        };
    }

    async ready() {
        if (this.ws.readyState === WebSocket.OPEN) return;
        return new Promise(resolve => this.ws.onopen = resolve);
    }

    async send(method, params = {}) {
        await this.ready();
        return new Promise((resolve, reject) => {
            const id = this.id++;
            this.callbacks.set(id, { resolve, reject });
            this.ws.send(JSON.stringify({ id, method, params }));
        });
    }

    async navigate(url) {
        await this.send('Page.navigate', { url });
        await sleep(1200);
    }

    async evaluate(expression) {
        const res = await this.send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
        return res.result ? res.result.value : null;
    }

    async screenshot(filepath) {
        const res = await this.send('Page.captureScreenshot', { format: 'png', quality: 100 });
        const buffer = Buffer.from(res.data, 'base64');
        fs.writeFileSync(filepath, buffer);
        console.log(`[OK] Saved screenshot: ${path.basename(filepath)}`);
    }

    async login(username, password = '123456') {
        await this.navigate(`${BASE_URL}?route=auth/logout`);
        await sleep(400);
        await this.navigate(`${BASE_URL}?route=auth/login`);
        await sleep(400);
        await this.evaluate(`
            document.getElementById('username').value = '${username}';
            document.getElementById('password').value = '${password}';
            document.querySelector('button[type="submit"]').click();
        `);
        await sleep(1500);
    }
}

async function run() {
    console.log('=== CHỤP ẢNH KỊCH BẢN ĐẦU-CUỐI MARVEL WMS (E2E) ===');
    const chrome = spawn(CHROME_PATH, [
        '--headless=new',
        '--remote-debugging-port=9222',
        `--user-data-dir=${USER_DATA_DIR}`,
        '--disable-gpu',
        '--no-first-run',
        '--no-default-browser-check',
        '--window-size=1440,920'
    ]);

    try {
        const wsUrl = await getWsUrl();
        const newTabRes = await fetch('http://127.0.0.1:9222/json/new', { method: 'PUT' });
        const tabInfo = await newTabRes.json();
        const client = new CDPClient(tabInfo.webSocketDebuggerUrl);

        await client.send('Page.enable');
        await client.send('Runtime.enable');
        await client.send('Emulation.setDeviceMetricsOverride', {
            width: 1440,
            height: 920,
            deviceScaleFactor: 1,
            mobile: false
        });

        // 1. Kịch bản A: LKH lập đề nghị mua NVL cho KHSX004
        console.log('\n--- 1. Kịch bản A: LKH lập đề nghị mua NVL (KHSX004) ---');
        await client.login('lkh01');
        await client.navigate(`${BASE_URL}?route=de-nghi-mua/create&plan_id=KHSX004`);
        await sleep(800);
        await client.screenshot(path.join(SCREENSHOT_DIR, 'e2e_01_lkh_de_nghi_mua_khsx004.png'));

        // 2. Kịch bản B: LKH tạo lệnh sản xuất từ KHSX001
        console.log('\n--- 2. Kịch bản B: LKH tạo lệnh sản xuất (KHSX001) ---');
        await client.navigate(`${BASE_URL}?route=lenh-san-xuat/create&plan_id=KHSX001`);
        await sleep(800);
        await client.screenshot(path.join(SCREENSHOT_DIR, 'e2e_02_lkh_tao_lenh_san_xuat.png'));
        
        await client.navigate(`${BASE_URL}?route=lenh-san-xuat/index`);
        await sleep(800);
        await client.screenshot(path.join(SCREENSHOT_DIR, 'e2e_02b_lkh_danh_sach_lenh_sx.png'));

        // 3. Kịch bản C: QLK phân bổ NVL cho lệnh sản xuất
        console.log('\n--- 3. Kịch bản C: QLK phân bổ NVL lưu kho (FEFO) ---');
        await client.login('qlk01');
        await client.navigate(`${BASE_URL}?route=phan-bo/create&order_id=LSX26100001`);
        await sleep(800);
        await client.screenshot(path.join(SCREENSHOT_DIR, 'e2e_03_qlk_phan_bo_nvl.png'));

        await client.navigate(`${BASE_URL}?route=phan-bo/index`);
        await sleep(800);
        await client.screenshot(path.join(SCREENSHOT_DIR, 'e2e_03b_qlk_danh_sach_phan_bo.png'));

        // 4. Kịch bản D: QLK điều phối YCN26100001 & NVK nhận lệnh
        console.log('\n--- 4. Kịch bản D: Điều phối nhập kho & Lệnh điều phối NVK ---');
        await client.navigate(`${BASE_URL}?route=dieu-phoi/create&req_id=YCN26100001`);
        await sleep(800);
        await client.screenshot(path.join(SCREENSHOT_DIR, 'e2e_04_qlk_dieu_phoi_ycn26100001.png'));

        await client.login('nvk01');
        await client.navigate(`${BASE_URL}?route=lenh-dieu-phoi/index`);
        await sleep(800);
        await client.screenshot(path.join(SCREENSHOT_DIR, 'e2e_04b_nvk_lenh_duoc_giao.png'));

        // 5. Kịch bản E: QLK cập nhật chênh lệch kiểm kê BBKK-2026-001 (194.000 đ)
        console.log('\n--- 5. Kịch bản E: Cập nhật chênh lệch kiểm kê (BBKK-2026-001) ---');
        await client.login('qlk01');
        await client.navigate(`${BASE_URL}?route=kiem-ke/update&id=BBKK-2026-001`);
        await sleep(800);
        await client.screenshot(path.join(SCREENSHOT_DIR, 'e2e_05_qlk_chenh_lech_kiem_ke.png'));

        await client.navigate(`${BASE_URL}?route=kiem-ke/detail&id=BBKK-2026-001`);
        await sleep(800);
        await client.screenshot(path.join(SCREENSHOT_DIR, 'e2e_05b_kiem_ke_detail_modal.png'));

        // 6. Kịch bản F: QLK đề xuất xử lý hàng lỗi HL001+HL002 (109.000 đ) & QC xem
        console.log('\n--- 6. Kịch bản F: Đề xuất xử lý hàng lỗi & QC xem ---');
        await client.navigate(`${BASE_URL}?route=hang-loi/create`);
        await sleep(800);
        await client.screenshot(path.join(SCREENSHOT_DIR, 'e2e_06_qlk_de_xuat_hang_loi.png'));

        await client.login('qc01');
        await client.navigate(`${BASE_URL}?route=hang-loi/index`);
        await sleep(800);
        await client.screenshot(path.join(SCREENSHOT_DIR, 'e2e_06b_qc_xem_hang_loi.png'));

        console.log('\n=== TẤT CẢ ẢNH CHỤP E2E ĐÃ HOÀN TẤT VÀ LƯU VÀO docs/screenshots/ ===');
    } finally {
        chrome.kill();
    }
}

run().catch(err => {
    console.error('Lỗi khi chụp màn hình E2E:', err);
    process.exit(1);
});

