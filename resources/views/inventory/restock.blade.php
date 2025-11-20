@extends('layouts.app')

@section('page_title', 'Restok Barang')
@section('page_subtitle', 'Pilih produk dari daftar restok dan ajukan pembelian')

@section('content')
    <section class="hero-gradient p-8 mb-6">
        <div class="space-y-3">
            <p class="text-sm uppercase text-white-soft" style="letter-spacing: 0.35em;">Restock Planner</p>
            <h1 class="text-3xl font-semibold">Formulir Restok Cabang</h1>
            <p class="text-white-fade text-sm">Gunakan daftar barang baku yang telah disediakan. Isi jumlah, harga, dan pemasok untuk membuat purchase order.</p>
        </div>
    </section>

    <section class="glass-card p-6 -mt-6 relative z-10" id="restock-section">
        <div class="mb-4 hidden" id="restock-branch-warning">
            <div class="px-4 py-3 rounded bg-amber-100 text-amber-800 text-sm">
                Pilih cabang aktif terlebih dahulu melalui menu di kanan atas sebelum membuat restok.
            </div>
        </div>

        <form class="space-y-6" id="restock-form">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs text-gray-500 uppercase tracking-wide" for="supplier-select">Pemasok</label>
                    <select id="supplier-select" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                        <option value="">Pilih pemasok</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase tracking-wide" for="expected-date">Tanggal Diharapkan</label>
                    <input type="date" id="expected-date" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
            </div>

            <div>
                <label class="text-xs text-gray-500 uppercase tracking-wide block mb-2">Daftar Produk Restok</label>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border border-gray-200 rounded-lg" id="restock-items-table">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-3 py-2 text-left w-1/3">Produk</th>
                            <th class="px-3 py-2 text-left">Unit</th>
                            <th class="px-3 py-2 text-left">Jumlah</th>
                            <th class="px-3 py-2 text-left">Harga Satuan (Rp)</th>
                            <th class="px-3 py-2 text-center">Aksi</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="mt-3 flex justify-between items-center text-xs text-gray-500">
                    <span>Produk yang tersedia berasal dari template restok. Hubungi super admin untuk memperbarui daftar.</span>
                    <button type="button" class="px-3 py-2 bg-blue-600 text-white rounded text-xs uppercase tracking-wide" id="add-restock-row">Tambah Baris</button>
                </div>
            </div>

            <div>
                <label class="text-xs text-gray-500 uppercase tracking-wide" for="restock-notes">Catatan</label>
                <textarea id="restock-notes" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" rows="3" placeholder="Tambahkan catatan khusus untuk pemasok (opsional)"></textarea>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3">
                <button type="button" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600" id="reset-restock">Reset</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm" id="submit-restock">Simpan Restok</button>
            </div>
        </form>
    </section>
@endsection

