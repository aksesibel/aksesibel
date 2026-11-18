document.addEventListener('DOMContentLoaded', () => {
    const alerts = document.querySelectorAll('.alert-dismissible');

    alerts.forEach((alert) => {
        let hideTimeout;

        const hideAlert = () => {
            if (alert.classList.contains('is-hiding')) return;

            clearTimeout(hideTimeout);
            alert.classList.add('is-hiding');

            setTimeout(() => {
                alert.remove();
            }, 300);
        };

        // Hilang otomatis setelah 4 detik
        hideTimeout = setTimeout(hideAlert, 4000);

        // Tutup ketika tombol X diklik
        const closeButton = alert.querySelector('.alert-close');

        closeButton?.addEventListener('click', hideAlert);
    });
});