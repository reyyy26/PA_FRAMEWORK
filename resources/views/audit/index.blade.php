@extends('layouts.app')

@section('page_title', 'Audit Trail')
@section('page_subtitle', 'Lacak seluruh aktivitas penting secara komprehensif')

@section('content')
    <section class="hero-gradient p-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
            <div class="space-y-3 max-w-2xl">
                <p class="text-sm uppercase text-white-soft" style="letter-spacing: 0.35em;">Login Watch</p>
                <h1 class="text-3xl font-semibold">Audit Trail Sistem</h1>
                <p class="text-white-fade text-sm">Visualisasi aktivitas sensitif dengan filter kontekstual dan token keamanan. Gunakan catatan ini untuk forensik maupun kepatuhan.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full lg:w-auto">
                <div class="stat-pill"><span class="text-xs uppercase text-white-soft">Log Terdeteksi</span><strong id="hero-total" class="text-white">0</strong></div>
                <div class="stat-pill"><span class="text-xs uppercase text-white-soft">Rentang Aktif</span><strong id="hero-range" class="text-white">-</strong></div>
            </div>
        </div>
    </section>

    <section class="glass-card p-6 -mt-8 mb-6 relative z-10">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-gray-800">Token Akses Audit</h2>
                <p class="text-sm text-gray-500">Gunakan token dengan izin <span class="font-mono bg-gray-100 px-2 py-1 rounded">login.manage</span> untuk memuat log.</p>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 w-full sm:w-auto">
                <input type="password" id="token-input" placeholder="Tempel API token" class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <div class="flex gap-2">
                    <button id="apply-token" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg shadow">Simpan Token</button>
                    <button id="clear-token" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg">Hapus</button>
                </div>
            </div>
        </div>
        <div id="global-status" class="text-sm"></div>
    </section>

    <section class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <form id="filter-form" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">ID Pengguna</label>
                <input type="number" name="user_id" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="Mis. 1024">
            </div>
            <div class="md:col-span-2">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi / Event</label>
                <input type="text" name="action" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="mis. pos.sale_recorded">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Dari</label>
                <input type="date" name="from" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Sampai</label>
                <input type="date" name="to" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="md:col-span-5 flex flex-wrap items-center justify-end gap-2">
                <span class="text-xs text-gray-400" id="filter-summary">Filter belum diterapkan</span>
                <button type="button" id="reset-filters" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg">Reset</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg shadow">Terapkan</button>
            </div>
        </form>
    </section>

    <section class="glass-card">
        <header class="px-6 py-4 border-b flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-gray-700 uppercase">Log Aktivitas</h3>
                <p class="text-xs text-gray-500">Klik baris untuk melihat detail perubahan.</p>
            </div>
            <div class="text-xs text-gray-400" id="result-counter">0 aktivitas ditampilkan</div>
        </header>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">Waktu</th>
                    <th class="px-4 py-3 text-left">Pengguna</th>
                    <th class="px-4 py-3 text-left">Aksi</th>
                    <th class="px-4 py-3 text-left">Entitas</th>
                    <th class="px-4 py-3 text-left">Perubahan</th>
                </tr>
                </thead>
                <tbody id="audit-table" class="bg-white divide-y divide-gray-100 text-sm"></tbody>
            </table>
        </div>
        <footer id="pagination" class="px-6 py-4 flex flex-wrap items-center justify-between gap-3 text-sm text-gray-500"></footer>
    </section>
@endsection

@push('scripts')
    <script>
        const table = document.getElementById('audit-table');
        const paginationRoot = document.getElementById('pagination');
        const filterForm = document.getElementById('filter-form');
        const tokenInput = document.getElementById('token-input');
        const applyTokenBtn = document.getElementById('apply-token');
        const clearTokenBtn = document.getElementById('clear-token');
        const resetFiltersBtn = document.getElementById('reset-filters');
        const globalStatus = document.getElementById('global-status');
        const filterSummary = document.getElementById('filter-summary');
        const resultCounter = document.getElementById('result-counter');
        const heroTotal = document.getElementById('hero-total');
        const heroRange = document.getElementById('hero-range');

        let currentPage = 1;
        const storageKey = 'nyxx_admin_token';

        function notify(message, type = 'info') {
            if (!globalStatus) {
                return;
            }
            globalStatus.textContent = message || '';
            globalStatus.className = 'text-sm';
            if (!message) {
                return;
            }
            const tone = {
                success: 'text-green-600',
                error: 'text-red-600',
                warning: 'text-yellow-600',
                info: 'text-gray-600'
            }[type] || 'text-gray-600';
            globalStatus.classList.add(tone);
        }

        function savedToken() {
            return localStorage.getItem(storageKey) || '';
        }

        function setToken(token) {
            if (token) {
                localStorage.setItem(storageKey, token);
            } else {
                localStorage.removeItem(storageKey);
            }
        }

        function updateFilterSummary() {
            const params = new URLSearchParams(new FormData(filterForm));
            const entries = [];
            if (params.get('user_id')) entries.push(`User #${params.get('user_id')}`);
            if (params.get('action')) entries.push(`Aksi "${params.get('action')}"`);
            if (params.get('from')) entries.push(`dari ${params.get('from')}`);
            if (params.get('to')) entries.push(`hingga ${params.get('to')}`);

            filterSummary.textContent = entries.length ? `Filter aktif: ${entries.join(', ')}` : 'Filter belum diterapkan';
        }

        async function fetchLogs(page = 1) {
            const token = savedToken();
            if (!token) {
                notify('Tempel API token untuk memuat data audit.', 'warning');
                table.innerHTML = '<tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">Token diperlukan untuk memuat data audit.</td></tr>';
                paginationRoot.innerHTML = '';
                return;
            }

            const params = new URLSearchParams(new FormData(filterForm));
            params.set('page', page);
            const from = params.get('from');
            const to = params.get('to');
            const rangeLabel = from || to
                ? `${from || 'tanpa batas'} → ${to || 'sekarang'}`
                : 'Seluruh histori';

            notify('Memuat data audit...', 'info');
            const response = await fetch(`/api/audit/logs?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            });

            if (!response.ok) {
                notify(`Gagal memuat data (${response.status}).`, 'error');
                table.innerHTML = `<tr><td colspan="5" class="px-4 py-4 text-center text-red-500">Gagal memuat data (${response.status}).</td></tr>`;
                return;
            }

            const data = await response.json();
            renderTable(data.data);
            renderPagination(data);
            resultCounter.textContent = `${data.data.length} aktivitas ditampilkan`;
            if (heroTotal) {
                const totalRecords = data.total ?? data.meta?.total ?? data.data.length;
                heroTotal.textContent = totalRecords;
            }
            if (heroRange) {
                heroRange.textContent = rangeLabel;
            }
            updateFilterSummary();
            notify('', 'info');
        }

        function renderTable(rows) {
            if (!rows.length) {
                table.innerHTML = '<tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">Belum ada aktivitas.</td></tr>';
                return;
            }

            table.innerHTML = rows.map((row) => `
                <tr>
                    <td class="px-4 py-3 whitespace-nowrap">${new Date(row.created_at).toLocaleString()}</td>
                    <td class="px-4 py-3">${row.user?.name ?? 'Sistem'}</td>
                    <td class="px-4 py-3 font-mono text-xs">${row.action}</td>
                    <td class="px-4 py-3">
                        <div>${row.model_type ?? '-'}</div>
                        <div class="text-xs text-gray-500">ID: ${row.model_id ?? '-'}</div>
                    </td>
                    <td class="px-4 py-3 text-xs">
                        <pre class="bg-gray-50 p-2 rounded overflow-x-auto">${JSON.stringify(row.changes ?? {}, null, 2)}</pre>
                    </td>
                </tr>
            `).join('');
        }

        function renderPagination(meta) {
            const { current_page, last_page, total } = meta;
            currentPage = current_page;

            const makeButton = (label, page, disabled = false) => `
                <button data-page="${page}" class="px-3 py-1 border rounded ${disabled ? 'text-gray-400 border-gray-200' : 'hover:bg-gray-100'}" ${disabled ? 'disabled' : ''}>${label}</button>`;

            const buttons = [
                makeButton('Awal', 1, current_page === 1),
                makeButton('Sebelumnya', current_page - 1, current_page === 1),
                `<span class="px-3 py-1">Halaman ${current_page} dari ${last_page}</span>`,
                makeButton('Berikutnya', current_page + 1, current_page === last_page),
                makeButton('Terakhir', last_page, current_page === last_page)
            ].join('');

            paginationRoot.innerHTML = `
                <div class="text-sm text-gray-500">Total ${total} aktivitas</div>
                <div class="space-x-2">${buttons}</div>
            `;

            paginationRoot.querySelectorAll('button[data-page]').forEach(btn => {
                btn.addEventListener('click', () => fetchLogs(Number(btn.dataset.page)));
            });
        }

        filterForm.addEventListener('submit', (event) => {
            event.preventDefault();
            fetchLogs(1);
        });

        resetFiltersBtn.addEventListener('click', () => {
            filterForm.reset();
            fetchLogs(1);
        });

        applyTokenBtn.addEventListener('click', () => {
            const token = tokenInput.value.trim();
            if (!token) {
                notify('Token tidak boleh kosong.', 'warning');
                return;
            }
            setToken(token);
            notify('Token tersimpan.', 'success');
            fetchLogs(1);
        });

        clearTokenBtn.addEventListener('click', () => {
            setToken('');
            tokenInput.value = '';
            table.innerHTML = '<tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">Token diperlukan untuk memuat data audit.</td></tr>';
            paginationRoot.innerHTML = '';
            notify('Token dihapus.', 'info');
        });

        document.addEventListener('DOMContentLoaded', () => {
            tokenInput.value = savedToken();
            if (tokenInput.value) {
                notify('Token dimuat dari penyimpanan lokal.', 'info');
            }
            updateFilterSummary();
            fetchLogs(1);
        });
    </script>
@endpush
