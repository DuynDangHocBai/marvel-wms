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
        await sleep(1000);
    }

    async evaluate(expression) {
        const res = await this.send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
        return res.result ? res.result.value : null;
    }

    async screenshot(filepath) {
        const res = await this.send('Page.captureScreenshot', { format: 'png', quality: 100 });
        const buffer = Buffer.from(res.data, 'base64');
        fs.writeFileSync(filepath, buffer);
        console.log(`Saved screenshot: ${filepath}`);
    }
}

async function run() {
    console.log('Starting Chrome in headless mode...');
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
        console.log('Connected to Chrome WebSocket:', wsUrl);

        // Open a new target/tab to communicate with
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

        // 1. Chụp màn hình Đăng nhập
        console.log('\n--- 1. Chụp màn hình Đăng nhập ---');
        await client.navigate(`${BASE_URL}?route=auth/logout`);
        await sleep(300);
        await client.navigate(`${BASE_URL}?route=auth/login`);
        await sleep(600);
        await client.screenshot(path.join(SCREENSHOT_DIR, '00_login_page.png'));

        // Danh sách 6 vai trò cần test
        const roles = [
            { user: 'qtv01', name: '01_dashboard_qtv.png', title: 'Quản trị viên (QTV)' },
            { user: 'bgd01', name: '02_dashboard_bgd.png', title: 'Ban Giám Đốc (BGD)' },
            { user: 'lkh01', name: '03_dashboard_lkh.png', title: 'Kế hoạch (LKH)' },
            { user: 'qlk01', name: '04_dashboard_qlk.png', title: 'Quản lý kho (QLK)' },
            { user: 'nvk01', name: '05_dashboard_nvk.png', title: 'Nhân viên kho (NVK)' },
            { user: 'qc01',  name: '06_dashboard_qc.png',  title: 'Kiểm tra chất lượng (QC)' },
        ];

        for (const r of roles) {
            console.log(`\n--- Đăng nhập ${r.user} (${r.title}) ---`);
            // Đăng xuất nếu đang trong phiên
            await client.navigate(`${BASE_URL}?route=auth/logout`);
            await sleep(400);

            // Điền form đăng nhập và click nút đăng nhập
            await client.evaluate(`
                document.getElementById('username').value = '${r.user}';
                document.getElementById('password').value = '123456';
                document.querySelector('button[type="submit"]').click();
            `);
            await sleep(1500);

            // Chụp dashboard + sidebar
            await client.screenshot(path.join(SCREENSHOT_DIR, r.name));
        }

        // 7. Thử truy cập URL không được phép (Ví dụ NVK truy cập Quản lý tài khoản: ?route=tai-khoan/index)
        console.log('\n--- 7. Kiểm tra truy cập trái quyền (403 Forbidden) ---');
        await client.navigate(`${BASE_URL}?route=auth/logout`);
        await sleep(400);
        await client.evaluate(`
            document.getElementById('username').value = 'nvk01';
            document.getElementById('password').value = '123456';
            document.querySelector('button[type="submit"]').click();
        `);
        await sleep(1500);

        // NVK cố tình vào Quản lý tài khoản (chỉ QTV mới có quyền)
        await client.navigate(`${BASE_URL}?route=tai-khoan/index`);
        await sleep(1200);
        await client.screenshot(path.join(SCREENSHOT_DIR, '07_forbidden_403.png'));

        // 8. Chụp trang UI Kit
        console.log('\n--- 8. Chụp trang Thư viện UI Kit ---');
        await client.navigate(`${BASE_URL}?route=uikit/index`);
        await sleep(800);
        await client.screenshot(path.join(SCREENSHOT_DIR, '08_uikit_preview.png'));

        console.log('\n=== TẤT CẢ ẢNH CHỤP MÀN HÌNH ĐÃ ĐƯỢC TẠO THÀNH CÔNG ===');
    } finally {
        chrome.kill();
    }
}

run().catch(err => {
    console.error('Error running screenshot script:', err);
    process.exit(1);
});
