
let selectedDecision = 'reject';

// Toast notification
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');

    if (!toast) return;

    toast.textContent = message;
    toast.className = `toast show ${type}`;

    setTimeout(() => {
        toast.className = 'toast';
    }, 3000);
}

// Memilih laporan dan menampilkan detailnya
function selectReport(element) {
    document.querySelectorAll('.report-card').forEach(card => {
        card.classList.remove('active');
    });

    element.classList.add('active');

    const data = element.dataset;

    document.getElementById('detailId').textContent = data.id;
    document.getElementById('detailTitle').textContent = data.title;
    document.getElementById('detailReporter').textContent = data.reporter;
    document.getElementById('detailDate').textContent = data.date;
    document.getElementById('detailFacility').textContent = data.facility;
    document.getElementById('detailLocation').textContent = data.location;
    document.getElementById('detailDescription').textContent = data.description;

    // Reset checklist ketika berganti laporan
    document.querySelectorAll('.inspection-check').forEach(check => {
        check.checked = false;
    });

    // Reset keputusan
    selectDecision('reject');
}

// Memilih keputusan verifikasi

function selectDecision(decision) {
    selectedDecision = decision;

    const approveButton = document.getElementById('approveOption');
    const rejectButton = document.getElementById('rejectOption');
    const reasonSection = document.getElementById('reasonSection');
    const notice = document.getElementById('decisionNotice');
    const submitButton = document.getElementById('submitDecision');

    // Mengubah tampilan tombol pilihan
    approveButton.classList.toggle('active', decision === 'approve');
    rejectButton.classList.toggle('active', decision === 'reject');

    // Mengubah ikon radio
    approveButton.querySelector('i').className =
        decision === 'approve'
            ? 'fa-solid fa-circle-check'
            : 'fa-regular fa-circle';

    rejectButton.querySelector('i').className =
        decision === 'reject'
            ? 'fa-solid fa-circle'
            : 'fa-regular fa-circle';

    // Menampilkan atau menyembunyikan alasan penolakan
    reasonSection.style.display =
        decision === 'reject' ? 'block' : 'none';

    notice.style.display =
        decision === 'reject' ? 'flex' : 'none';

    // Mengubah tombol submit
    submitButton.className = decision === 'approve'
        ? 'btn btn-primary'
        : 'btn btn-reject';

    submitButton.innerHTML = decision === 'approve'
        ? '<i class="fa-regular fa-circle-check"></i> Terima & verifikasi'
        : '<i class="fa-regular fa-circle-xmark"></i> Tolak laporan';
}

// Simulasi pengiriman keputusan
function submitDummyDecision() {
    if (selectedDecision === 'reject') {
        const reason = document.getElementById('rejectionReason');

        if (!reason.value.trim()) {
            showToast('Alasan penolakan harus diisi.', 'error');
            return;
        }
    }

    showToast(
        selectedDecision === 'approve'
            ? 'Laporan berhasil diverifikasi (simulasi).'
            : 'Laporan berhasil ditolak (simulasi).'
    );
}

// Membuka bukti foto
function openEvidence() {
    const image = document.getElementById('evidenceImage');

    if (!image || !image.src) {
        showToast('Bukti foto belum tersedia.', 'error');
        return;
    }

    window.open(image.src, '_blank');
}

// Menyimpan checklist pemeriksaan
function saveInspection() {
    const checkboxes = document.querySelectorAll('.inspection-check');
    const checkedCount = document.querySelectorAll(
        '.inspection-check:checked'
    ).length;

    showToast(
        `${checkedCount} dari ${checkboxes.length} pemeriksaan telah selesai.`,
        checkedCount === checkboxes.length ? 'success' : 'error'
    );
}

// Pencarian laporan
function searchReports() {
    const keyword = document
        .getElementById('searchReport')
        .value.toLowerCase();

    document.querySelectorAll('.report-card').forEach(card => {
        card.style.display = card.textContent.toLowerCase().includes(keyword)
            ? ''
            : 'none';
    });
}

// Inisialisasi halaman
document.addEventListener('DOMContentLoaded', function () {
    const reportCards = document.querySelectorAll('.report-card');

    // Event klik setiap laporan
    reportCards.forEach(card => {
        card.addEventListener('click', function () {
            selectReport(this);
        });
    });

    // Tampilkan detail laporan pertama
    if (reportCards.length > 0) {
        selectReport(reportCards[0]);
    }

    // Event pencarian
    const searchInput = document.getElementById('searchReport');

    if (searchInput) {
        searchInput.addEventListener('input', searchReports);
    }

    // Tab antrean dan riwayat
    document.getElementById('tabMasuk')?.addEventListener('click', function () {
        this.classList.add('active');
        document.getElementById('tabRiwayat').classList.remove('active');
        showToast('Menampilkan laporan masuk.');
    });

    document.getElementById('tabRiwayat')?.addEventListener('click', function () {
        this.classList.add('active');
        document.getElementById('tabMasuk').classList.remove('active');
        showToast('Fitur riwayat verifikasi belum dihubungkan.');
    });
});
