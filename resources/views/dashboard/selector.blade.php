@extends('layouts.app')

@section('page_title', 'Pilih Mode Dashboard')
@section('page_subtitle', 'Masuk ke tampilan yang sesuai dengan aktivitas Anda hari ini')

@section('content')
    <div class="max-w-5xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <a href="{{ url('/dashboard/main') }}" class="group bg-white border border-blue-100 hover:border-blue-300 shadow-sm hover:shadow-lg rounded-xl p-8 transition flex flex-col gap-4">
                <div class="flex items-center gap-3">
                    @include('partials.icon', ['name' => 'home', 'classes' => 'w-10 h-10 text-blue-600'])
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-800">Dashboard Toko Utama</h2>
                        <p class="text-sm text-gray-500">Kontrol penuh atas permintaan cabang, pengadaan, dan analitik penjualan.</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <p class="font-semibold text-blue-700">Pengadaan</p>
                        <p>Pantau purchase order dan status supplier secara real-time.</p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4">
                        <p class="font-semibold text-blue-700">Analitik</p>
                        <p>Visualisasikan penjualan, aging stok, serta forecasting permintaan.</p>
                    </div>
                </div>
                <span class="text-sm text-blue-600 font-semibold inline-flex items-center gap-2">Masuk Dashboard <span>&rarr;</span></span>
            </a>

            <a href="{{ url('/dashboard/branch') }}" class="group bg-white border border-green-100 hover:border-green-300 shadow-sm hover:shadow-lg rounded-xl p-8 transition flex flex-col gap-4">
                <div class="flex items-center gap-3">
                    @include('partials.icon', ['name' => 'storefront', 'classes' => 'w-10 h-10 text-green-600'])
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-800">Dashboard Cabang</h2>
                        <p class="text-sm text-gray-500">Operasional POS, penerimaan transfer, dan pemantauan stok cabang.</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                    <div class="bg-green-50 rounded-lg p-4">
                        <p class="font-semibold text-green-700">Kasir</p>
                        <p>Catat transaksi harian, shift kasir, dan metode pembayaran.</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <p class="font-semibold text-green-700">Stok Cabang</p>
                        <p>Lihat stok kritikal, batch mendekati kedaluwarsa, dan buat request.</p>
                    </div>
                </div>
                <span class="text-sm text-green-600 font-semibold inline-flex items-center gap-2">Masuk Dashboard <span>&rarr;</span></span>
            </a>
        </div>
    </div>
@endsection
