import axios from 'axios';

let salesMixChartInstance = null;
let stockAgingChartInstance = null;

export function initMainDashboard(branchId) {
    const filterForm = document.getElementById('analytics-filter');
    if (!filterForm) {
        return;
    }

    const exportButtons = {
        sales: document.getElementById('export-sales'),
        stock: document.getElementById('export-stock'),
        demand: document.getElementById('export-demand'),
    };

    const heroNodes = {
        requests: document.getElementById('hero-requests'),
        restock: document.getElementById('hero-restock'),
        alerts: document.getElementById('hero-alerts'),
        branches: document.getElementById('hero-branches'),
    };

    const load = async () => {
        const formData = new FormData(filterForm);
        const params = Object.fromEntries(formData.entries());
        params.branch_id = branchId;

        const [salesMix, stockAging, purchaseOrders, alerts, forecast] = await Promise.all([
            axios.get('/api/analytics/sales-mix', {params}),
            axios.get('/api/analytics/stock-aging', {params}),
            axios.get('/api/purchase-orders', {params}),
            axios.get('/api/analytics/alerts', {params}),
            axios.get('/api/analytics/demand-forecast', {params}),
        ]);

        renderSalesMixChart(salesMix.data);
        renderStockAgingChart(stockAging.data);
        renderPurchaseOrders(purchaseOrders.data, heroNodes);
        renderAlerts(alerts.data, heroNodes);
        renderDemandForecast(forecast.data);

        if (heroNodes.branches) {
            const uniqueBranches = new Set((purchaseOrders.data || []).map(order => order.branch_id).filter(Boolean));
            heroNodes.branches.textContent = String(uniqueBranches.size || 0);
        }
    };

    filterForm.addEventListener('submit', (event) => {
        event.preventDefault();
        load().catch(() => console.warn('Gagal memuat data analitik'));
    });

    if (exportButtons.sales) {
        exportButtons.sales.addEventListener('click', () => downloadCsv('/api/analytics/sales-mix/export', filterForm, branchId));
    }

    if (exportButtons.stock) {
        exportButtons.stock.addEventListener('click', () => downloadCsv('/api/analytics/stock-aging/export', filterForm, branchId));
    }

    if (exportButtons.demand) {
        exportButtons.demand.addEventListener('click', () => downloadCsv('/api/analytics/demand-forecast/export', filterForm, branchId));
    }

    load().catch(() => console.warn('Gagal memuat data analitik'));
}

async function downloadCsv(url, form, branchId) {
    const params = new URLSearchParams(new FormData(form));
    params.append('branch_id', branchId);

    const response = await fetch(`${url}?${params.toString()}`, {
        headers: {
            'Accept': 'text/csv'
        },
        credentials: 'same-origin'
    });

    if (!response.ok) {
        alert('Gagal mengunduh data');
        return;
    }

    const blob = await response.blob();
    const link = document.createElement('a');
    link.href = window.URL.createObjectURL(blob);
    const disposition = response.headers.get('Content-Disposition');
    const filenameMatch = disposition && disposition.match(/filename="?(.*)"?/i);
    link.download = filenameMatch ? filenameMatch[1] : 'data.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function renderSalesMixChart(data) {
    const ctx = document.getElementById('salesMixChart');
    if (!ctx) return;

    if (salesMixChartInstance) {
        salesMixChartInstance.destroy();
    }

    salesMixChartInstance = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: data.map(item => item.product_name ?? `Produk ${item.product_id}`),
            datasets: [{
                data: data.map(item => item.sales),
                backgroundColor: ['#60a5fa', '#34d399', '#f87171', '#fbbf24', '#f472b6', '#22d3ee'],
            }],
        },
        options: {
            plugins: {
                tooltip: {
                    callbacks: {
                        label: (context) => {
                            const entry = data[context.dataIndex];
                            return `${entry.product_name}: Rp ${Number(entry.sales).toLocaleString('id-ID')} (${entry.mix_percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

function renderStockAgingChart(data) {
    const ctx = document.getElementById('stockAgingChart');
    if (!ctx) return;

    if (stockAgingChartInstance) {
        stockAgingChartInstance.destroy();
    }

    const statusOrder = ['healthy', 'near_expiry', 'expired', 'no_expiry'];
    const statusLabels = {
        healthy: 'Sehat',
        near_expiry: 'Mendekati',
        expired: 'Kedaluwarsa',
        no_expiry: 'Tanpa Kadaluarsa',
    };

    const statusCount = data.reduce((acc, item) => {
        const key = item.status ?? 'unknown';
        acc[key] = (acc[key] || 0) + item.quantity;
        return acc;
    }, {});

    const labels = statusOrder.filter(status => statusCount[status]).map(status => statusLabels[status]);
    const values = statusOrder.filter(status => statusCount[status]).map(status => statusCount[status]);

    stockAgingChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Jumlah Unit',
                data: values,
                backgroundColor: ['#22c55e', '#fbbf24', '#ef4444', '#94a3b8'],
            }],
        },
    });
}

function renderPurchaseOrders(orders, heroNodes = {}) {
    const tbody = document.querySelector('#recent-purchase-orders tbody');
    if (!tbody) return;

    tbody.innerHTML = '';

    orders.slice(0, 5).forEach(order => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="py-1">${order.number}</td>
            <td>${order.supplier.name}</td>
            <td>${order.status}</td>
        `;
        tbody.appendChild(tr);
    });

    if (!orders.length) {
        tbody.innerHTML = '<tr><td colspan="3" class="px-4 py-4 text-center text-gray-400">Belum ada purchase order.</td></tr>';
    }

    if (heroNodes.requests) {
        heroNodes.requests.textContent = orders.length || '0';
    }

    if (heroNodes.restock) {
        const activeRestock = orders.filter(order => ['approved', 'processing'].includes(order.status)).length;
        heroNodes.restock.textContent = activeRestock || '0';
    }
}

export async function loadBranchDashboard(branchId) {
    const [sales, transfers, products] = await Promise.all([
        axios.get('/api/pos/sales', {params: {branch_id: branchId}}).catch(() => ({data: []})),
        axios.get('/api/stock-transfers', {params: {branch_id: branchId}}),
        axios.get('/api/products', {params: {branch_id: branchId}}),
    ]);

    renderSalesSummary(sales.data);
    renderIncomingTransfers(transfers.data);
    renderProductTable(products.data);
}

function renderAlerts(alerts, heroNodes = {}) {
    const container = document.getElementById('system-notifications');
    if (!container) return;

    container.innerHTML = '';

    if (!alerts.length) {
        container.innerHTML = '<p>Tidak ada pemberitahuan.</p>';
        return;
    }

    alerts.forEach(alert => {
        const badgeColors = {
            critical: 'bg-red-200 text-red-800',
            warning: 'bg-yellow-200 text-yellow-800',
            info: 'bg-blue-200 text-blue-800',
        };

        const div = document.createElement('div');
        div.className = 'border border-gray-200 rounded p-3';
        div.innerHTML = `
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold">${alert.type}</span>
                <span class="text-xs px-2 py-1 rounded ${badgeColors[alert.severity] ?? 'bg-gray-200'}">${alert.severity}</span>
            </div>
            <p class="text-sm text-gray-700">${alert.message}</p>
        `;
        container.appendChild(div);
    });

    if (!alerts.length) {
        container.innerHTML = '<p class="text-sm text-gray-500">Semua sistem normal.</p>';
    }

    if (heroNodes.alerts) {
        heroNodes.alerts.textContent = alerts.length || '0';
    }
}

function renderDemandForecast(forecast) {
    const tbody = document.querySelector('#demand-forecast-table tbody');
    if (!tbody) return;

    if (!forecast.length) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center text-gray-500 py-4">Belum ada data forecast.</td></tr>';
        return;
    }

    tbody.innerHTML = '';

    forecast.forEach(item => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="py-2">Produk #${item.product_id}</td>
            <td>${Number(item.average_daily_sales).toFixed(2)}</td>
            <td>${Number(item.projected_weekly_demand).toFixed(2)}</td>
        `;
        tbody.appendChild(tr);
    });
}

function renderSalesSummary(sales) {
    const transactions = sales.length;
    const grandTotal = sales.reduce((acc, sale) => acc + parseFloat(sale.grand_total ?? 0), 0);
    const transactionNode = document.querySelector('[data-metric="transactions"]');
    const grandTotalNode = document.querySelector('[data-metric="grand_total"]');

    if (transactionNode) {
        transactionNode.textContent = transactions;
    }

    if (grandTotalNode) {
        grandTotalNode.textContent = grandTotal.toLocaleString('id-ID', {style: 'currency', currency: 'IDR'});
    }
}

function renderIncomingTransfers(transfers) {
    const list = document.getElementById('incoming-transfers');
    if (!list) return;
    list.innerHTML = '';

    const inbound = transfers.filter(transfer => transfer.status === 'in_transit');

    inbound.forEach(transfer => {
        const li = document.createElement('li');
        li.textContent = `${transfer.number} - ${transfer.source_branch.name}`;
        list.appendChild(li);
    });

    if (!list.children.length) {
        list.innerHTML = '<li>Belum ada pengiriman.</li>';
    }

    const heroInbound = document.getElementById('hero-inbound');
    if (heroInbound) {
        heroInbound.textContent = inbound.length || '0';
    }
}

function renderProductTable(products) {
    const tbody = document.querySelector('#product-table tbody');
    if (!tbody) return;
    tbody.innerHTML = '';

    products.forEach(product => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="py-1">${product.sku}</td>
            <td>${product.name}</td>
            <td>${product.batches?.reduce((acc, batch) => acc + batch.quantity, 0) ?? 0}</td>
            <td>${product.batches?.[0]?.expiry_date ?? '-'}</td>
        `;
        tbody.appendChild(tr);
    });
}
