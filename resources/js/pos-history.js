import axios from 'axios';

const CURRENCY_FORMAT = { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 };

export function initPosHistory(branchId, root) {
    if (!root) {
        return;
    }

    const tableBody = root.querySelector('tbody');
    const statusLabel = root.querySelector('[data-history-status]');
    const refreshButton = root.querySelector('[data-history-refresh]');

    if (!branchId) {
        setStatus('Pilih cabang aktif terlebih dahulu untuk melihat riwayat.', 'warning');
        if (refreshButton) {
            refreshButton.disabled = true;
            refreshButton.classList.add('opacity-50', 'cursor-not-allowed');
        }
        return;
    }

    loadHistory();

    if (refreshButton) {
        refreshButton.addEventListener('click', () => loadHistory());
    }

    async function loadHistory() {
        setStatus('Memuat riwayat penjualan...', 'info');

        try {
            const { data } = await axios.get('/api/pos/sales', {
                params: {
                    branch_id: branchId,
                    limit: 100,
                },
            });

            renderRows(Array.isArray(data) ? data : []);
            if (!data?.length) {
                setStatus('Belum ada transaksi yang tercatat untuk hari ini.', 'muted');
            } else {
                setStatus(`${data.length} transaksi terbaru ditampilkan.`, 'success');
            }
        } catch (error) {
            console.error('Gagal memuat riwayat penjualan', error);
            renderRows([]);
            setStatus(error.response?.data?.message || 'Tidak dapat memuat riwayat penjualan.', 'error');
        }
    }

    function renderRows(items) {
        if (!tableBody) {
            return;
        }
        tableBody.innerHTML = '';

        if (!items.length) {
            const empty = document.createElement('tr');
            empty.innerHTML = '<td colspan="6" class="px-3 py-6 text-center text-gray-500 text-sm">Belum ada transaksi.</td>';
            tableBody.appendChild(empty);
            return;
        }

        items.forEach((sale) => {
            const row = document.createElement('tr');
            row.className = 'border-t border-gray-200';

            const customerName = sale.customer?.name || 'Umum';
            const paymentSummary = formatPayments(sale.payments ?? []);

            row.innerHTML = `
                <td class="px-3 py-2">
                    <div class="font-semibold text-gray-800">${sale.number}</div>
                    <div class="text-xs text-gray-500">${formatDateTime(sale.sold_at)}</div>
                </td>
                <td class="px-3 py-2 text-sm text-gray-600">${sale.items?.length ?? 0} produk</td>
                <td class="px-3 py-2 text-sm text-gray-600">${customerName}</td>
                <td class="px-3 py-2 text-sm text-gray-600">${paymentSummary}</td>
                <td class="px-3 py-2 text-right font-semibold text-gray-800">${formatCurrency(sale.grand_total)}</td>
                <td class="px-3 py-2 text-sm text-gray-500">${sale.cashier?.name ?? '-'}</td>
            `;

            tableBody.appendChild(row);
        });
    }

    function setStatus(message, type) {
        if (!statusLabel) {
            return;
        }

        const colorMap = {
            success: 'text-emerald-600',
            error: 'text-rose-600',
            warning: 'text-amber-600',
            info: 'text-blue-600',
            muted: 'text-gray-500',
        };

        statusLabel.className = `text-xs font-medium ${colorMap[type] ?? colorMap.muted}`;
        statusLabel.textContent = message;
    }

    function formatCurrency(value) {
        return Number(value || 0).toLocaleString('id-ID', CURRENCY_FORMAT);
    }

    function formatDateTime(value) {
        if (!value) {
            return '-';
        }
        const date = new Date(value);
        return date.toLocaleString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    }

    function formatPayments(payments) {
        if (!payments.length) {
            return 'Tanpa catatan';
        }

        return payments
            .map((payment) => `${payment.method ?? 'Lainnya'} · ${formatCurrency(payment.amount)}`)
            .join(', ');
    }
}
