/* SITARA – themed confirmation modals (SweetAlert2)
 * Any <form data-confirm="..."> is intercepted and asks for confirmation
 * before submitting. Type presets: "delete", "logout", default.
 *
 * Optional per-form overrides:
 *   data-confirm-title, data-confirm-text, data-confirm-button,
 *   data-confirm-cancel, data-confirm-image
 */
(function () {
    'use strict';

    var M = window.SITARA_SWAL || {};
    var isDark = function () { return document.body.classList.contains('dark-mode'); };

    var PRESETS = {
        delete: {
            title: 'Hapus data ini?',
            text: 'Tindakan ini tidak dapat dibatalkan.',
            button: 'Ya, hapus',
            variant: 'danger',
            image: M.deleteImage
        },
        logout: {
            title: 'Keluar dari akun?',
            text: 'Anda perlu login kembali untuk masuk ke dashboard.',
            button: 'Ya, keluar',
            variant: 'primary',
            image: M.logoutImage
        },
        default: {
            title: 'Anda yakin?',
            text: 'Mohon konfirmasi tindakan ini.',
            button: 'Ya, lanjutkan',
            variant: 'primary',
            image: M.defaultImage
        }
    };

    function buildConfig(form) {
        var type = form.getAttribute('data-confirm') || 'default';
        var p = PRESETS[type] || PRESETS.default;
        var d = form.dataset;

        return {
            title: d.confirmTitle || p.title,
            html: '<p class="sitara-swal-text">' + (d.confirmText || p.text) + '</p>',
            imageUrl: d.confirmImage || p.image || undefined,
            imageWidth: 120,
            imageAlt: 'SITARA',
            showCancelButton: true,
            reverseButtons: true,
            buttonsStyling: false,
            focusCancel: true,
            confirmButtonText: d.confirmButton || p.button,
            cancelButtonText: d.confirmCancel || 'Batal',
            customClass: {
                popup: 'sitara-swal' + (isDark() ? ' sitara-swal-dark' : ''),
                title: 'sitara-swal-title',
                image: 'sitara-swal-img',
                actions: 'sitara-swal-actions',
                confirmButton: 'sitara-swal-btn sitara-swal-btn-' + (d.confirmVariant || p.variant),
                cancelButton: 'sitara-swal-btn sitara-swal-btn-cancel'
            }
        };
    }

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.matches || !form.matches('form[data-confirm]')) return;
        if (form.dataset.confirmed === '1') return;

        e.preventDefault();

        if (typeof Swal === 'undefined') {
            // Fallback to native confirm if the library failed to load.
            if (window.confirm(form.dataset.confirmText || 'Anda yakin?')) form.submit();
            return;
        }

        Swal.fire(buildConfig(form)).then(function (result) {
            if (result.isConfirmed) {
                form.dataset.confirmed = '1';
                form.submit();
            }
        });
    });
})();
