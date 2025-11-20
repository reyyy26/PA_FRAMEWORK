@extends('layouts.app')

@section('page_title', 'Monitoring Integrasi')
@section('page_subtitle', 'Pantau antrean sinkronisasi ERP dan kanal integrasi lainnya')

@section('content')
    <section class="hero-gradient p-8 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
            <div class="space-y-3 max-w-2xl">
                <p class="text-sm uppercase text-white-soft" style="letter-spacing: 0.35em;">Integration Pulse</p>
                <h1 class="text-3xl font-semibold">Monitoring Integrasi Terpusat</h1>
                <p class="text-white-fade text-sm">Lacak status antrean ERP serta kanal sinkronisasi penting lainnya dengan cepat.</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 w-full lg:w-auto">
                <div class="stat-pill"><span class="text-xs uppercase text-white-soft">Total Log</span><strong id="hero-integration-total" class="text-white">0</strong></div>
                <div class="stat-pill"><span class="text-xs uppercase text-white-soft">Antrean</span><strong id="hero-integration-queued" class="text-white">0</strong></div>
                <div class="stat-pill"><span class="text-xs uppercase text-white-soft">Gagal</span><strong id="hero-integration-failed" class="text-white">0</strong></div>
                <div class="stat-pill"><span class="text-xs uppercase text-white-soft">Channel Aktif</span><strong id="hero-integration-channels" class="text-white">0</strong></div>
            </div>
        </div>
    </section>

    <section class="glass-card p-6 -mt-6 mb-8 relative z-10">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Token Administrasi Integrasi</h2>
                <p class="text-sm text-gray-500">Token dengan izin <span class="font-mono bg-gray-100 px-2 py-1 rounded">login.manage</span> diperlukan untuk membaca log.</p>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 w-full sm:w-auto">
                <input id="admin-token-input" type="password" placeholder="Tempel API token" class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <div class="flex gap-2">
                    <button id="admin-token-apply" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg shadow">Simpan Token</button>
                    <button id="admin-token-clear" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg">Hapus</button>
                </div>
            </div>
        </div>
        <div id="global-status" class="mt-4 text-sm"></div>
    </section>

    <section class="glass-card p-6 mb-6">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
            <div class="lg:col-span-2">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Cari Log</label>
                <input id="log-search" type="text" placeholder="Cari payload, response, atau channel" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Channel</label>
                <select id="log-channel-filter" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                    <option value="">Semua channel</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</label>
                <select id="log-status-filter" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                    <option value="">Semua status</option>
                    <option value="queued">Queued</option>
                    <option value="processing">Processing</option>
                    <option value="success">Success</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button id="reload-logs" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg">Muat ulang</button>
            </div>
        </div>
    </section>

    <section class="glass-card overflow-hidden">
        <header class="px-6 py-4 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <div>
                <h3 class="text-sm font-semibold text-gray-700 uppercase">Daftar Log</h3>
                <p class="text-xs text-gray-500">Klik detail untuk melihat payload &amp; response</p>
            </div>
            <div class="text-xs text-gray-400" id="log-summary">0 log ditampilkan</div>
        </header>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">Channel</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Dibuat</th>
                    <th class="px-4 py-3 text-left">Diproses</th>
                    <th class="px-4 py-3 text-left">Ringkasan</th>
                    <th class="px-4 py-3 text-left">Aksi</th>
                </tr>
                </thead>
                <tbody class="text-sm" id="log-table-body"></tbody>
            </table>
            <div id="log-table-empty" class="text-center text-sm text-gray-500 py-6 hidden">Belum ada log yang tersedia.</div>
        </div>
        <footer id="log-pagination" class="px-6 py-4 flex flex-wrap items-center justify-between gap-3 text-sm text-gray-500"></footer>
    </section>

    <section class="glass-card p-6 mt-6">
        <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Detail Log</h3>
                <p class="text-sm text-gray-500">Pilih log dari tabel untuk meninjau payload dan response.</p>
            </div>
            <button id="copy-payload" class="px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hidden">Salin Payload</button>
        </header>
        <div id="log-detail-empty" class="text-sm text-gray-500">Belum ada log yang dipilih.</div>
        <div id="log-detail" class="grid grid-cols-1 lg:grid-cols-2 gap-4 hidden">
            <div class="bg-gray-900 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-green-200 mb-2">Payload</h4>
                <pre id="log-payload" class="text-xs text-green-100 overflow-auto h-64"></pre>
            </div>
            <div class="bg-gray-900 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-blue-200 mb-2">Response</h4>
                <pre id="log-response" class="text-xs text-blue-100 overflow-auto h-64"></pre>
            </div>
        </div>
        <div id="log-detail-meta" class="mt-4 text-sm"></div>
    </section>
@endsection

@push('scripts')
    <script>
        (function () {
            const storageKey = 'nyxx_admin_token';

            const state = {
                token: '',
                logs: [],
                meta: null,
                selectedLog: null,
                channels: new Set(),
            };

            const els = {
                tokenInput: document.getElementById('admin-token-input'),
                tokenApply: document.getElementById('admin-token-apply'),
                tokenClear: document.getElementById('admin-token-clear'),
                globalStatus: document.getElementById('global-status'),
                search: document.getElementById('log-search'),
                channelFilter: document.getElementById('log-channel-filter'),
                statusFilter: document.getElementById('log-status-filter'),
                reload: document.getElementById('reload-logs'),
                tableBody: document.getElementById('log-table-body'),
                tableEmpty: document.getElementById('log-table-empty'),
                pagination: document.getElementById('log-pagination'),
                detailEmpty: document.getElementById('log-detail-empty'),
                detailWrapper: document.getElementById('log-detail'),
                payload: document.getElementById('log-payload'),
                response: document.getElementById('log-response'),
                detailMeta: document.getElementById('log-detail-meta'),
                copyPayload: document.getElementById('copy-payload'),
                logSummary: document.getElementById('log-summary'),
                heroTotal: document.getElementById('hero-integration-total'),
                heroQueued: document.getElementById('hero-integration-queued'),
                heroFailed: document.getElementById('hero-integration-failed'),
                heroChannels: document.getElementById('hero-integration-channels'),
            };

            function notify(target, message, type = 'info') {
                if (!target) {
                    return;
                }
                target.textContent = message || '';
                target.className = 'text-sm';
                if (!message) {
                    return;
                }
                const tone = {
                    success: 'text-green-600',
                    error: 'text-red-600',
                    warning: 'text-yellow-600',
                    info: 'text-gray-600',
                }[type] || 'text-gray-600';
                target.classList.add(tone);
            }

            function savedToken() {
                return localStorage.getItem(storageKey) || '';
            }

            function setToken(value) {
                state.token = value;
                if (value) {
                    localStorage.setItem(storageKey, value);
                } else {
                    localStorage.removeItem(storageKey);
                }
            }

            function authHeaders() {
                if (!state.token) {
                    return {};
                }
                return {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${state.token}`,
                };
            }

            async function fetchJson(url, options = {}) {
                const response = await fetch(url, {
                    ...options,
                    headers: {
                        ...authHeaders(),
                        ...(options.headers || {}),
                    },
                });

                if (response.status === 204) {
                    return null;
                }

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const message = data?.message ?? `Permintaan gagal (${response.status})`;
                    throw new Error(message);
                }

                return data;
            }

            function ensureToken(action) {
                if (!state.token) {
                    notify(els.globalStatus, `Token diperlukan untuk ${action}.`, 'warning');
                    return false;
                }
                return true;
            }

            function humanJson(value) {
                if (value === null || value === undefined) {
                    return 'null';
                }
                return JSON.stringify(value, null, 2);
            }

            function renderChannelOptions() {
                const current = els.channelFilter.value;
                const options = ['<option value="">Semua channel</option>', ...Array.from(state.channels).sort().map(channel => `<option value="${channel}">${channel}</option>`)];
                els.channelFilter.innerHTML = options.join('');
                if (state.channels.has(current)) {
                    els.channelFilter.value = current;
                }
            }

            function renderLogs() {
                const term = (els.search.value || '').toLowerCase();
                const channel = els.channelFilter.value;
                const status = els.statusFilter.value;

                const filtered = state.logs.filter(log => {
                    const matchesChannel = !channel || log.channel === channel;
                    const matchesStatus = !status || log.status === status;
                    const payloadText = JSON.stringify(log.payload || {}).toLowerCase();
                    const responseText = JSON.stringify(log.response || {}).toLowerCase();
                    const matchesTerm = !term || payloadText.includes(term) || responseText.includes(term) || (log.channel || '').toLowerCase().includes(term);
                    return matchesChannel && matchesStatus && matchesTerm;
                });

                if (!filtered.length) {
                    els.tableBody.innerHTML = '';
                    els.tableEmpty.classList.remove('hidden');
                } else {
                    els.tableEmpty.classList.add('hidden');
                    els.tableBody.innerHTML = filtered.map(log => {
                        const createdAt = log.created_at ? new Date(log.created_at).toLocaleString() : '-';
                        const processedAt = log.processed_at ? new Date(log.processed_at).toLocaleString() : '-';
                        const payloadSummary = Array.isArray(log.payload) ? `${log.payload.length} item` : `${Object.keys(log.payload || {}).length} field`;
                        const statusClass = {
                            queued: 'text-yellow-600',
                            processing: 'text-blue-600',
                            success: 'text-green-600',
                            failed: 'text-red-600',
                        }[log.status] || 'text-gray-600';
                        const isSelected = state.selectedLog && state.selectedLog.id === log.id;
                        return `
                            <tr class="${isSelected ? 'bg-blue-50' : ''}">
                                <td class="px-4 py-2">${log.channel}</td>
                                <td class="px-4 py-2 ${statusClass}">${log.status}</td>
                                <td class="px-4 py-2">${createdAt}</td>
                                <td class="px-4 py-2">${processedAt}</td>
                                <td class="px-4 py-2">${payloadSummary}</td>
                                <td class="px-4 py-2">
                                    <button data-log-id="${log.id}" class="px-3 py-1 bg-blue-600 text-white rounded select-log">Detail</button>
                                </td>
                            </tr>`;
                    }).join('');

                    document.querySelectorAll('.select-log').forEach(btn => {
                        btn.addEventListener('click', () => {
                            const id = Number(btn.dataset.logId);
                            const log = state.logs.find(item => item.id === id);
                            if (log) {
                                state.selectedLog = log;
                                renderLogs();
                                renderDetail();
                            }
                        });
                    });
                }

                renderPagination();
                updateHeroSummary();
            }

            function updateHeroSummary() {
                if (!els.heroTotal) {
                    return;
                }

                const total = state.meta?.total ?? state.logs.length;
                const queued = state.logs.filter(log => ['queued', 'processing'].includes(log.status)).length;
                const failed = state.logs.filter(log => log.status === 'failed').length;
                const channelCount = state.channels.size;

                els.heroTotal.textContent = String(total || 0);
                els.heroQueued.textContent = String(queued || 0);
                els.heroFailed.textContent = String(failed || 0);
                els.heroChannels.textContent = String(channelCount || 0);
            }

            function renderPagination() {
                if (!state.meta) {
                    els.pagination.innerHTML = '';
                    return;
                }
                const { current_page, last_page, total } = state.meta;
                const disablePrev = current_page <= 1;
                const disableNext = current_page >= last_page;
                els.pagination.innerHTML = `
                    <div>Total ${total} log</div>
                    <div class="space-x-2">
                        <button class="px-3 py-1 border rounded ${disablePrev ? 'text-gray-400 border-gray-200' : 'hover:bg-gray-100'}" data-page="${current_page - 1}" ${disablePrev ? 'disabled' : ''}>Sebelumnya</button>
                        <span>Hal ${current_page} / ${last_page}</span>
                        <button class="px-3 py-1 border rounded ${disableNext ? 'text-gray-400 border-gray-200' : 'hover:bg-gray-100'}" data-page="${current_page + 1}" ${disableNext ? 'disabled' : ''}>Berikutnya</button>
                    </div>`;

                els.pagination.querySelectorAll('button[data-page]').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const page = Number(btn.dataset.page);
                        if (!Number.isNaN(page)) {
                            loadLogs(page);
                        }
                    });
                });
            }

            function renderDetail() {
                if (!state.selectedLog) {
                    els.detailWrapper.classList.add('hidden');
                    els.detailEmpty.classList.remove('hidden');
                    els.detailMeta.textContent = '';
                    els.copyPayload.classList.add('hidden');
                    return;
                }
                const log = state.selectedLog;
                els.detailEmpty.classList.add('hidden');
                els.detailWrapper.classList.remove('hidden');
                els.copyPayload.classList.remove('hidden');

                els.payload.textContent = humanJson(log.payload);
                els.response.textContent = humanJson(log.response);

                const createdAt = log.created_at ? new Date(log.created_at).toLocaleString() : '-';
                const processedAt = log.processed_at ? new Date(log.processed_at).toLocaleString() : '-';
                els.detailMeta.innerHTML = `
                    <div class="space-y-1">
                        <div>Channel: <strong>${log.channel}</strong></div>
                        <div>Status: <strong>${log.status}</strong></div>
                        <div>Dibuat: <strong>${createdAt}</strong></div>
                        <div>Diproses: <strong>${processedAt}</strong></div>
                    </div>`;
            }

            async function loadLogs(page = 1) {
                if (!ensureToken('memuat log')) {
                    return;
                }
                const params = new URLSearchParams({ per_page: 50, page });
                try {
                    notify(els.globalStatus, 'Memuat log integrasi...', 'info');
                    const data = await fetchJson(`/api/integrations/logs?${params.toString()}`);
                    state.logs = data.data || [];
                    state.meta = {
                        current_page: data.current_page,
                        last_page: data.last_page,
                        total: data.total,
                    };
                    state.channels = new Set(state.logs.map(log => log.channel).filter(Boolean));
                    renderChannelOptions();
                    if (els.logSummary) {
                        els.logSummary.textContent = `${state.logs.length} log ditampilkan`;
                    }
                    notify(els.globalStatus, '', 'info');
                    renderLogs();
                    renderDetail();
                    updateHeroSummary();
                } catch (error) {
                    notify(els.globalStatus, error.message, 'error');
                }
            }

            function initializeToken() {
                const existing = savedToken();
                if (existing) {
                    setToken(existing);
                    els.tokenInput.value = existing;
                    notify(els.globalStatus, 'Token dimuat dari penyimpanan lokal.', 'info');
                    loadLogs();
                }

                els.tokenApply.addEventListener('click', () => {
                    const token = els.tokenInput.value.trim();
                    if (!token) {
                        notify(els.globalStatus, 'Token tidak boleh kosong.', 'warning');
                        return;
                    }
                    setToken(token);
                    notify(els.globalStatus, 'Token tersimpan.', 'success');
                    loadLogs();
                });

                els.tokenClear.addEventListener('click', () => {
                    setToken('');
                    els.tokenInput.value = '';
                    state.logs = [];
                    state.meta = null;
                    state.selectedLog = null;
                    state.channels.clear();
                    renderChannelOptions();
                    renderLogs();
                    renderDetail();
                    updateHeroSummary();
                    notify(els.globalStatus, 'Token dihapus.', 'info');
                });
            }

            function initializeListeners() {
                els.search.addEventListener('input', renderLogs);
                els.channelFilter.addEventListener('change', renderLogs);
                els.statusFilter.addEventListener('change', renderLogs);
                els.reload.addEventListener('click', () => loadLogs(state.meta?.current_page ?? 1));
                els.copyPayload.addEventListener('click', () => {
                    if (!state.selectedLog) {
                        return;
                    }
                    navigator.clipboard.writeText(JSON.stringify(state.selectedLog.payload || {}, null, 2)).then(() => {
                        notify(els.globalStatus, 'Payload disalin ke clipboard.', 'success');
                    }).catch(() => {
                        notify(els.globalStatus, 'Gagal menyalin payload.', 'error');
                    });
                });
            }

            document.addEventListener('DOMContentLoaded', () => {
                initializeToken();
                initializeListeners();
            });
        }());
    </script>
@endpush
