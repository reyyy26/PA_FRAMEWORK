@extends('layouts.app')

@section('title', 'Riwayat Penjualan · Nyxx Agrisupply')
@section('page_title', 'Riwayat Penjualan')
@section('page_subtitle', 'Lihat transaksi terbaru untuk cabang aktif')

@section('content')
    <section class="hero-gradient p-8 mb-6">
        <div class="space-y-3">
            <p class="text-sm uppercase text-white-soft" style="letter-spacing: 0.35em;">POS History</p>
            <h1 class="text-3xl font-semibold">Riwayat Penjualan Cabang</h1>
            <p class="text-white-fade text-sm">Data diambil dari transaksi POS yang tercatat via aplikasi ini.</p>
        </div>
    </section>

    <section class="glass-card p-6 -mt-6 relative z-10 space-y-6" id="pos-history">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Transaksi Terbaru</h2>
            <button type="button" class="px-3 py-2 bg-blue-600 text-white text-xs uppercase tracking-wide rounded" data-history-refresh>Refresh</button>
        </div>

        <p data-history-status class="text-xs text-gray-500">Memuat data...</p>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border border-gray-200 rounded-lg">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-3 py-2 text-left">Nomor &amp; Waktu</th>
                    <th class="px-3 py-2 text-left">Produk</th>
                    <th class="px-3 py-2 text-left">Pelanggan</th>
                    <th class="px-3 py-2 text-left">Pembayaran</th>
                    <th class="px-3 py-2 text-right">Grand Total</th>
                    <th class="px-3 py-2 text-left">Kasir</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>
@endsection
