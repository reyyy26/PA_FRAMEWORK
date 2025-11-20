@extends('layouts.app')

@section('title', 'POS Console · Nyxx Agrisupply')
@section('page_title', 'POS Console')
@section('page_subtitle', 'Kelola transaksi kasir secara real-time')

@php
    $currentUser = auth()->user();
@endphp

@section('content')
    <section class="hero-gradient p-8 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
            <div class="space-y-3 max-w-2xl">
                <p class="text-sm uppercase text-white-soft" style="letter-spacing: 0.35em;">POS Operations</p>
                <h1 class="text-3xl font-semibold">Konsol Penjualan Kasir</h1>
                <p class="text-white-fade text-sm">Pantau performa penjualan harian dan catat transaksi pelanggan dengan cepat.</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 w-full lg:w-auto">
                <div class="stat-pill">
                    <span class="text-xs uppercase text-white-soft">Transaksi</span>
                    <strong data-pos-hero="transactions" class="text-white">0</strong>
                </div>
                <div class="stat-pill">
                    <span class="text-xs uppercase text-white-soft">Grand Total</span>
                    <strong data-pos-hero="grand_total" class="text-white">Rp0</strong>
                </div>
                <div class="stat-pill">
                    <span class="text-xs uppercase text-white-soft">Pembayaran Tunai</span>
                    <strong data-pos-hero="cash_total" class="text-white">Rp0</strong>
                </div>
                <div class="stat-pill">
                    <span class="text-xs uppercase text-white-soft">Pembayaran Non Tunai</span>
                    <strong data-pos-hero="non_cash_total" class="text-white">Rp0</strong>
                </div>
            </div>
        </div>
    </section>

    <div id="pos-console" class="grid grid-cols-1 lg:grid-cols-2 gap-6" data-user-id="{{ $currentUser?->id }}">
        <div class="lg:col-span-2 glass-card p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Aksi Cepat</h2>
                    <p class="text-sm text-gray-500">Catat transaksi pelanggan secara instan tanpa membuka shift.</p>
                </div>
                <span class="badge bg-emerald-100 text-emerald-700 border border-emerald-200">Kasir Aktif</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <button id="pos-new-sale" class="glass-card px-4 py-6 text-left border border-emerald-100 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-3">
                        @include('partials.icon', ['name' => 'storefront', 'classes' => 'w-8 h-8 text-emerald-500'])
                        <span class="badge bg-emerald-100 text-emerald-700 border border-emerald-200">Penjualan</span>
                    </div>
                    <p class="text-sm font-semibold text-gray-700">Transaksi Baru</p>
                    <p class="text-xs text-gray-500 mt-1">Masuk ke modul POS untuk mencatat transaksi pelanggan.</p>
                </button>
                <a href="{{ url('/pos/sales') }}" class="glass-card px-4 py-6 text-left border border-blue-100 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-3">
                        @include('partials.icon', ['name' => 'chart', 'classes' => 'w-8 h-8 text-blue-500'])
                        <span class="badge bg-blue-100 text-blue-700 border border-blue-200">Riwayat</span>
                    </div>
                    <p class="text-sm font-semibold text-gray-700">Riwayat Penjualan</p>
                    <p class="text-xs text-gray-500 mt-1">Lihat daftar transaksi terbaru untuk cabang aktif.</p>
                </a>
            </div>
        </div>

        <div class="glass-card p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Cabang</h2>
            <dl class="space-y-3 text-sm text-gray-600">
                <div class="flex items-center justify-between">
                    <dt>Kasir</dt>
                    <dd class="font-medium text-gray-800">{{ $currentUser?->name ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt>Cabang Aktif</dt>
                    <dd class="font-medium text-gray-800">{{ session('branch_id') ? 'ID #' . session('branch_id') : 'Belum dipilih' }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt>Tanggal</dt>
                    <dd class="font-medium text-gray-800">{{ now()->format('d M Y') }}</dd>
                </div>
            </dl>
            <p class="text-xs text-gray-500 mt-4">
                Pastikan cabang aktif sudah dipilih sebelum mencatat transaksi baru.
            </p>
        </div>
    </div>

    <div class="glass-card p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Performa Kasir Hari Ini</h2>
                <p class="text-sm text-gray-500">Rekap ringkas transaksi yang telah dicatat.</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm text-gray-600">
            <div class="stat-pill">
                <span class="text-white-soft">Total Penjualan</span>
                <strong id="pos-summary-grand-total" class="text-white-fade">Rp0</strong>
            </div>
            <div class="stat-pill">
                <span class="text-white-soft">Jumlah Transaksi</span>
                <strong id="pos-summary-transactions" class="text-white-fade">0</strong>
            </div>
            <div class="stat-pill">
                <span class="text-white-soft">Metode Tunai</span>
                <strong id="pos-summary-cash" class="text-white-fade">Rp0</strong>
            </div>
            <div class="stat-pill">
                <span class="text-white-soft">Metode Non Tunai</span>
                <strong id="pos-summary-non-cash" class="text-white-fade">Rp0</strong>
            </div>
        </div>
        <p id="pos-summary-note" class="text-xs text-gray-500 mt-6">
            Statistik ditarik dari transaksi yang tercatat pada hari ini.
        </p>
    </div>
@endsection
