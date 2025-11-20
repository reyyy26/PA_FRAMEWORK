@extends('layouts.app')

@section('page_title', 'Dashboard Toko Utama')
@section('page_subtitle', 'Insight real-time untuk pengadaan, stok, dan kinerja cabang')

@section('content')
    <section class="hero-gradient p-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
            <div class="space-y-3 max-w-2xl">
                <p class="text-sm uppercase text-white-soft" style="letter-spacing: 0.35em;">Central Operations</p>
                <h1 class="text-3xl font-semibold">Dashboard Toko Utama</h1>
                <p class="text-white-fade text-sm">
                    Insight real-time untuk pengadaan, stok, dan kinerja cabang. Gunakan filter untuk menyesuaikan periode analitik dan ekspor laporan instan.
                </p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 flex-shrink-0 w-full lg:w-auto">
                <div class="stat-pill"><span class="text-xs uppercase text-white-soft">Permintaan Cabang</span><strong id="hero-requests" class="text-white">-</strong></div>
                <div class="stat-pill"><span class="text-xs uppercase text-white-soft">Restok Aktif</span><strong id="hero-restock" class="text-white">-</strong></div>
                <div class="stat-pill"><span class="text-xs uppercase text-white-soft">Alert</span><strong id="hero-alerts" class="text-white">-</strong></div>
                <div class="stat-pill"><span class="text-xs uppercase text-white-soft">Cabang Aktif</span><strong id="hero-branches" class="text-white">-</strong></div>
            </div>
        </div>
    </section>

    <section class="glass-card p-6 -mt-8 mb-6 relative z-10">
        <form id="analytics-filter" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
                <label class="text-xs text-gray-500 uppercase tracking-wide">Rentang Analitik (hari)</label>
                <input type="number" name="window_days" value="30" min="7" max="180" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
                <label class="text-xs text-gray-500 uppercase tracking-wide">Mulai</label>
                <input type="date" name="from" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
                <label class="text-xs text-gray-500 uppercase tracking-wide">Selesai</label>
                <input type="date" name="to" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700">Terapkan</button>
                <button type="button" id="export-sales" class="px-4 py-2 bg-emerald-500 text-white rounded-lg shadow hover:bg-emerald-600">Eksport Penjualan</button>
                <button type="button" id="export-stock" class="px-4 py-2 bg-amber-500 text-white rounded-lg shadow hover:bg-amber-600">Eksport Stok</button>
                <button type="button" id="export-demand" class="px-4 py-2 bg-purple-600 text-white rounded-lg shadow hover:bg-purple-700">Eksport Forecast</button>
            </div>
        </form>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
        <article class="glass-card p-6">
            <header class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-gray-700 uppercase">Permintaan Cabang</h2>
                    <p class="text-xs text-gray-500">Status approval dan progress pemenuhan</p>
                </div>
                <span class="badge bg-blue-100 text-blue-700">Live</span>
            </header>
            <ul id="stock-request-summary" class="space-y-3 text-sm text-gray-700"></ul>
        </article>

        <article class="glass-card p-6">
            <header class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-gray-700 uppercase">Restok Supplier Terbaru</h2>
                    <p class="text-xs text-gray-500">Monitor PO yang baru disetujui</p>
                </div>
                <span class="badge bg-emerald-100 text-emerald-700">Procurement</span>
            </header>
            <div class="overflow-hidden rounded-lg border border-gray-100">
                <table class="w-full text-sm" id="recent-purchase-orders">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-2 text-left">Nomor</th>
                        <th class="px-4 py-2 text-left">Supplier</th>
                        <th class="px-4 py-2 text-left">Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="3" class="px-4 py-4 text-center text-gray-400">Memuat data purchase order...</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </article>

        <article class="glass-card p-6">
            <header class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-gray-700 uppercase">Notifikasi Sistem</h2>
                    <p class="text-xs text-gray-500">Lihat tindak lanjut penting dari modul otomatis</p>
                </div>
                <span class="badge bg-purple-100 text-purple-700">Alert</span>
            </header>
            <div id="system-notifications" class="text-sm text-gray-700 space-y-3"></div>
        </article>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
        <article class="glass-card p-6">
            <header class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-700 uppercase">Sales Mix</h2>
                <span class="text-xs text-gray-400" id="sales-mix-period">30 hari terakhir</span>
            </header>
            <canvas id="salesMixChart" class="w-full h-64"></canvas>
        </article>
        <article class="glass-card p-6">
            <header class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-700 uppercase">Aging Stok</h2>
                <span class="text-xs text-gray-400" id="stock-aging-summary">Segmentasi batch</span>
            </header>
            <canvas id="stockAgingChart" class="w-full h-64"></canvas>
        </article>
    </section>

    <section class="glass-card p-6">
        <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-4">
            <div>
                <h2 class="text-sm font-semibold text-gray-700 uppercase">Permintaan Produk</h2>
                <p class="text-xs text-gray-500">Proyeksi stok berbasis pola keluar barang</p>
            </div>
            <div class="text-xs text-gray-400" id="demand-metadata">Memuat ringkasan...</div>
        </header>
        <div class="overflow-hidden rounded-lg border border-gray-100">
            <table class="w-full text-sm" id="demand-forecast-table">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-2 text-left">Produk</th>
                    <th class="px-4 py-2 text-right">Rata-rata Harian</th>
                    <th class="px-4 py-2 text-right">Proyeksi Mingguan</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td colspan="3" class="px-4 py-6 text-center text-gray-400">Menunggu data forecasting...</td>
                </tr>
                </tbody>
            </table>
        </div>
    </section>
@endsection
