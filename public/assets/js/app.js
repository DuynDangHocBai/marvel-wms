/**
 * MARVEL WMS - Front-end Helper Scripts
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Tự động ẩn Flash message sau 5 giây
    const flashMessages = document.querySelectorAll('.alert-dismissible');
    flashMessages.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(function () {
                alert.remove();
            }, 400);
        }, 5000);
    });

    // 2. Xử lý mở/đóng Modal
    document.querySelectorAll('[data-modal-target]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-modal-target');
            const modal = document.getElementById(targetId);
            if (modal) {
                modal.classList.add('show');
            }
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const modal = this.closest('.modal-backdrop');
            if (modal) {
                modal.classList.remove('show');
            }
        });
    });

    // Đóng modal khi bấm ra ngoài vùng nội dung
    document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
        backdrop.addEventListener('click', function (e) {
            if (e.target === this) {
                this.classList.remove('show');
            }
        });
    });

    // 3. Phím tắt nhanh bàn phím: Esc đóng modal, Ctrl + S lưu
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.show').forEach(function (modal) {
                modal.classList.remove('show');
            });
        }
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            const saveBtn = document.querySelector('button[type="submit"].btn-primary, [data-hotkey-save]');
            if (saveBtn) {
                e.preventDefault();
                saveBtn.click();
            }
        }
    });

    // 4. Quick fill tài khoản demo ở màn hình login
    window.quickFillLogin = function (username, password) {
        const userInput = document.getElementById('username');
        const passInput = document.getElementById('password');
        if (userInput && passInput) {
            userInput.value = username;
            passInput.value = password;
            userInput.focus();
        }
    };

    // 5. Định dạng số thống nhất kiểu vi-VN (formatSo)
    window.formatSo = function (val, isCurrency = false) {
        if (val === null || val === undefined || val === '') {
            return isCurrency ? '0 đ' : '0';
        }
        const num = Number(val);
        if (isNaN(num)) return isCurrency ? '0 đ' : '0';

        if (isCurrency) {
            return Math.round(num).toLocaleString('vi-VN') + ' đ';
        }
        return num.toLocaleString('vi-VN', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 3
        });
    };
});

