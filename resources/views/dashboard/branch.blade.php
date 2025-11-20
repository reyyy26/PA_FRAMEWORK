@extends('layouts.app')

@section('page_title', 'Dashboard Cabang')
@section('page_subtitle', 'Operasikan POS dan manajemen stok secara efisien di lapangan')

@section('content')
    <section class="hero-gradient p-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
            <div class="space-y-3 max-w-2xl">
                <p class="text-sm uppercase text-white-soft" style="letter-spacing: 0.35em;">Branch Operations</p>
                <h1 class="text-3xl font-semibold">Ringkasan Operasional Cabang</h1>
                <p class="text-white-fade text-sm">
                    Monitoring transaksi harian, transfer masuk, serta akses cepat untuk tindakan POS. Pastikan token admin aktif untuk sinkronisasi data.
                </p>
            </div>
            <div class="grid grid-cols-2 gap-3 w-full lg:w-auto">
                <div class="stat-pill"><span class="text-xs uppercase text-white-soft">Transaksi</span><strong data-metric="transactions" class="text-white">0</strong></div>
                <div class="stat-pill"><span class="text-xs uppercase text-white-soft">Grand Total</span><strong data-metric="grand_total" class="text-white">Rp0</strong></div>
                <div class="stat-pill"><span class="text-xs uppercase text-white-soft">Shift Aktif</span><strong data-metric="active_shift" class="text-white">-</strong></div>
                <div class="stat-pill"><span class="text-xs uppercase text-white-soft">Transfer Masuk</span><strong id="hero-inbound" class="text-white">0</strong></div>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6 -mt-8 relative z-10">
        <article class="glass-card p-6">
            <header class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-gray-700 uppercase">Penjualan Hari Ini</h2>
                    <p class="text-xs text-gray-500">Update kasir per-shift</p>
                </div>
                <span class="badge bg-indigo-100 text-indigo-700">POS</span>
            </header>
            <dl id="pos-summary" class="space-y-3 text-sm text-gray-700">
                <div class="flex items-center justify-between">
                    <dt>Total Transaksi</dt>
                    <dd class="font-semibold" data-metric="transactions">0</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt>Grand Total</dt>
                    <dd class="font-semibold" data-metric="grand_total">Rp0</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt>Shift Aktif</dt>
                    <dd class="text-gray-500" data-metric="active_shift">-</dd>
                </div>
            </dl>
        </article>

        <article class="glass-card p-6">
            <header class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-gray-700 uppercase">Transfer Masuk</h2>
                    <p class="text-xs text-gray-500">Antrian penerimaan dari gudang pusat</p>
                </div>
                <span class="badge bg-rose-100 text-rose-700">Inbound</span>
            </header>
            <ul id="incoming-transfers" class="space-y-2 text-sm text-gray-700">
                <li class="text-gray-400">Belum ada pengiriman.</li>
            </ul>
        </article>

        <article class="glass-card p-6">
            <header class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-gray-700 uppercase">Alat Cepat</h2>
                    <p class="text-xs text-gray-500">Akses tindakan rutin harian</p>
                </div>
                <span class="badge bg-green-100 text-green-700">Aksi</span>
            </header>
            <div class="grid grid-cols-1 gap-3 text-sm">
                <a href="{{ url('/pos/console') }}" class="flex items-center justify-between px-4 py-3 rounded-lg border bg-green-50 text-green-700 font-semibold hover:bg-green-100">
                    Buka POS
                    <span>&rarr;</span>
                </a>
                <a href="{{ url('/inventory/restock') }}" class="flex items-center justify-between px-4 py-3 rounded-lg border bg-blue-50 text-blue-700 font-semibold hover:bg-blue-100">
                    Formulir Restok
                    <span>&rarr;</span>
                </a>
            </div>
        </article>
    </section>

    <section class="glass-card p-6">
        <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-4">
            <div>
                <h2 class="text-sm font-semibold text-gray-700 uppercase">Inventori Cabang</h2>
                <p class="text-xs text-gray-500">Pantau stok kritis dan batch mendekati kedaluwarsa</p>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="badge bg-red-100 text-red-600">Stok Rendah</span>
                <span class="badge bg-yellow-100 text-yellow-600">Akan Kedaluwarsa</span>
            </div>
        </header>
        <div class="overflow-hidden rounded-lg border border-gray-100">
            <table class="w-full text-sm" id="product-table">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-2 text-left">SKU</th>
                    <th class="px-4 py-2 text-left">Produk</th>
                    <th class="px-4 py-2 text-right">Stok</th>
                    <th class="px-4 py-2 text-left">Kedaluwarsa</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-gray-400">Memuat daftar produk cabang...</td>
                </tr>
                </tbody>
            </table>
        </div>
    </section>
@endsection
