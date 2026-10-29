const { spawn } = require('child_process');
const fs = require('fs');
const path = require('path');

const CHROME_PATH = "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe";
const BASE_URL = 'http://localhost:81/marvel-wms/public/';
const USER_DATA_DIR = path.join(__dirname, '..', '.chrome_temp_layout');
const SCREENSHOT_DIR = path.join(__dirname, '..', 'docs', 'screenshots', 'fix_layout');

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

    async setViewport(width, height) {
        await this.send('Emulation.setDeviceMetricsOverride', {
            width,
            height,
            deviceScaleFactor: 1,
            mobile: false
        });
        await sleep(300);
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
        console.log(`Saved screenshot: ${path.basename(filepath)}`);
    }

    async login(user) {
        await this.navigate(`${BASE_URL}?route=auth/logout`);
        await sleep(300);
        await this.navigate(`${BASE_URL}?route=auth/login`);
        await sleep(500);
        await this.evaluate(`
            document.getElementById('username').value = '${user}';
            document.getElementById('password').value = '123456';
            document.querySelector('button[type="submit"]').click();
        `);
        await sleep(1200);
    }
}

async function run() {
    console.log('Starting Chrome in headless mode for layout verification...');
    const chrome = spawn(CHROME_PATH, [
        '--headless=new',
        '--remote-debugging-port=9222',
        `--user-data-dir=${USER_DATA_DIR}`,
        '--disable-gpu',
        '--no-first-run',
        '--no-default-browser-check',
        '--window-size=1920,1080'
    ]);

    try {
        const wsUrl = await getWsUrl();
        console.log('Connected to Chrome WebSocket:', wsUrl);

        const newTabRes = await fetch('http://127.0.0.1:9222/json/new', { method: 'PUT' });
        const tabInfo = await newTabRes.json();
        const client = new CDPClient(tabInfo.webSocketDebuggerUrl);

        await client.send('Page.enable');
        await client.send('Runtime.enable');

        const pages = [
            // LKH (Kế hoạch): de-nghi-mua, lenh-san-xuat
            { user: 'lkh01', prefix: 'de_nghi_mua_index', url: `${BASE_URL}?route=de-nghi-mua/index` },
            { user: 'lkh01', prefix: 'de_nghi_mua_create', url: `${BASE_URL}?route=de-nghi-mua/create&plan_id=KHSX004` },
            { user: 'lkh01', prefix: 'de_nghi_mua_detail', url: `${BASE_URL}?route=de-nghi-mua/detail&id=DNM-20261027-568A973` },
            { user: 'lkh01', prefix: 'lenh_san_xuat_index', url: `${BASE_URL}?route=lenh-san-xuat/index` },
            { user: 'lkh01', prefix: 'lenh_san_xuat_create', url: `${BASE_URL}?route=lenh-san-xuat/create&plan_id=KHSX003` },
            { user: 'lkh01', prefix: 'lenh_san_xuat_detail', url: `${BASE_URL}?route=lenh-san-xuat/detail&id=LSX2610001` },

            // QLK (Quản lý kho): phan-bo, dieu-phoi, kiem-ke, hang-loi
            { user: 'qlk01', prefix: 'phan_bo_index', url: `${BASE_URL}?route=phan-bo/index` },
            { user: 'qlk01', prefix: 'phan_bo_create', url: `${BASE_URL}?route=phan-bo/create&order_id=LSX2610001&kho_id=K001` },
            { user: 'qlk01', prefix: 'phan_bo_history', url: `${BASE_URL}?route=phan-bo/index&tab=history` },
            { user: 'qlk01', prefix: 'dieu_phoi_index', url: `${BASE_URL}?route=dieu-phoi/index` },
            { user: 'qlk01', prefix: 'dieu_phoi_create', url: `${BASE_URL}?route=dieu-phoi/create&req_id=YCN26100002` },
            { user: 'qlk01', prefix: 'dieu_phoi_detail', url: `${BASE_URL}?route=dieu-phoi/detail&id=YCN26100002` },
            { user: 'qlk01', prefix: 'kiem_ke_index', url: `${BASE_URL}?route=kiem-ke/index` },
            { user: 'qlk01', prefix: 'kiem_ke_update', url: `${BASE_URL}?route=kiem-ke/update&id=BBKK-2026-003` },
            { user: 'qlk01', prefix: 'kiem_ke_detail', url: `${BASE_URL}?route=kiem-ke/detail&id=BBKK-2026-001` },
            { user: 'qlk01', prefix: 'hang_loi_index', url: `${BASE_URL}?route=hang-loi/index` },
            { user: 'qlk01', prefix: 'hang_loi_create', url: `${BASE_URL}?route=hang-loi/create` },
            { user: 'qlk01', prefix: 'hang_loi_detail', url: `${BASE_URL}?route=hang-loi/detail&id=DXLH-20261028-0001` },
        ];

        const viewports = [
            { width: 1280, height: 900, label: '1280px' },
            { width: 1920, height: 1080, label: '1920px' }
        ];

        let currentUser = '';

        for (const vp of viewports) {
            console.log(`\n=================== BẮT ĐẦU CHỤP Ở KHỔ ${vp.label} ===================`);
            await client.setViewport(vp.width, vp.height);

            for (const item of pages) {
                if (currentUser !== item.user) {
                    console.log(`Đăng nhập vai trò ${item.user}...`);
                    await client.login(item.user);
                    currentUser = item.user;
                }

                await client.navigate(item.url);
                await sleep(500);

                // Kiểm tra sơ bộ các chỉ số giao diện trong trang
                const checkInfo = await client.evaluate(`
                    (() => {
                        const tableWrappers = document.querySelectorAll('.table-wrapper');
                        const stickyHeaders = document.querySelectorAll('.col-actions, .th-action, .col-action, .has-sticky-actions th:last-child');
                        const inputs = document.querySelectorAll('.table input[type="number"], .table input.qty-input, .table .lot-alloc-input');
                        let minInputW = 999;
                        inputs.forEach(inp => {
                            const rect = inp.getBoundingClientRect();
                            if (rect.width > 0 && rect.width < minInputW) minInputW = rect.width;
                        });
                        return {
                            tableWrapperCount: tableWrappers.length,
                            stickyHeaderCount: stickyHeaders.length,
                            inputCount: inputs.length,
                            minInputWidth: minInputW === 999 ? 'N/A' : minInputW + 'px'
                        };
                    })()
                `);

                const filename = `${item.prefix}_${vp.label}.png`;
                await client.screenshot(path.join(SCREENSHOT_DIR, filename));
                console.log(`  -> Checked ${item.prefix}: wrappers=${checkInfo.tableWrapperCount}, inputs=${checkInfo.inputCount}, minInputWidth=${checkInfo.minInputWidth}`);
            }
        }

        console.log('\n=== HOÀN THÀNH CHỤP ẢNH TẤT CẢ 6 PHÂN HỆ Ở 1280px VÀ 1920px! ===');
    } finally {
        chrome.kill();
    }
}

run().catch(err => {
    console.error('Error running screenshot script:', err);
    process.exit(1);
});

