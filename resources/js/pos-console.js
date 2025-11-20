import axios from 'axios';

const CURRENCY_FORMAT = { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 };

export function initPosConsole(branchId, root) {
    if (!root) {
        return;
    }

    const todayISO = new Date().toISOString().slice(0, 10);

    const els = {
        hero: {
            transactions: document.querySelector('[data-pos-hero="transactions"]'),
            grandTotal: document.querySelector('[data-pos-hero="grand_total"]'),
            cash: document.querySelector('[data-pos-hero="cash_total"]'),
            nonCash: document.querySelector('[data-pos-hero="non_cash_total"]'),
        },
        summary: {
            grandTotal: document.getElementById('pos-summary-grand-total'),
            transactions: document.getElementById('pos-summary-transactions'),
            cash: document.getElementById('pos-summary-cash'),
            nonCash: document.getElementById('pos-summary-non-cash'),
            note: document.getElementById('pos-summary-note'),
        },
        actions: {
            newSale: document.getElementById('pos-new-sale'),
        },
    };

    if (!branchId) {
        updateSummaryNote('Pilih cabang aktif untuk memuat data POS.');
        disableSaleAction();
        return;
    }

    setupNewSaleAction();
    loadData();

    async function loadData() {
        try {
            const { data } = await axios.get('/api/pos/overview', {
                params: {
                    branch_id: branchId,
                    date: todayISO,
                },
            });

            applySalesSummary(data?.summary ?? {});
        } catch (error) {
            console.error('Gagal memuat POS console', error);
            updateSummaryNote('Tidak dapat memuat data POS. Silakan coba lagi.');
        }
    }

    function applySalesSummary(summary) {
        const transactions = Number(summary.transactions || 0);
        const grandTotal = Number(summary.grand_total || 0);
        const cashTotal = Number(summary.cash_total || 0);
        const nonCashTotal = Number(summary.non_cash_total || 0);

        setText(els.summary.transactions, formatNumber(transactions));
        setText(els.summary.grandTotal, formatCurrency(grandTotal));
        setText(els.summary.cash, formatCurrency(cashTotal));
        setText(els.summary.nonCash, formatCurrency(nonCashTotal));
        updateSummaryNote(`Data dihitung per ${formatDate(todayISO)}.`);

        setText(els.hero.transactions, formatNumber(transactions));
        setText(els.hero.grandTotal, formatCurrency(grandTotal));
        setText(els.hero.cash, formatCurrency(cashTotal));
        setText(els.hero.nonCash, formatCurrency(nonCashTotal));
    }

    function updateSummaryNote(message) {
        if (els.summary.note) {
            els.summary.note.textContent = message;
        }
    }

    function setText(node, value) {
        if (node) {
            node.textContent = value;
        }
    }

    function disableSaleAction() {
        const button = els.actions.newSale;
        if (button) {
            button.disabled = true;
            button.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    function setupNewSaleAction() {
        if (els.actions.newSale) {
            els.actions.newSale.addEventListener('click', () => {
                window.location.href = '/pos/sales/quick';
            });
        }
    }

    function formatNumber(value) {
        return Number(value || 0).toLocaleString('id-ID');
    }

    function formatCurrency(value) {
        return Number(value || 0).toLocaleString('id-ID', CURRENCY_FORMAT);
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    }
}
