@extends('layouts.app')

@section('page_title', 'Template Restok')
@section('page_subtitle', 'Kelola daftar produk yang tersedia pada form restok')

@section('content')
    <section class="hero-gradient p-8 mb-6">
        <div class="space-y-3">
            <p class="text-sm uppercase text-white-soft" style="letter-spacing: 0.35em;">Super Admin Only</p>
            <h1 class="text-3xl font-semibold">Pengaturan Template Restok</h1>
            <p class="text-white-fade text-sm">Tambah, nonaktifkan, atau ubah urutan produk yang tampil pada halaman restok cabang.</p>
        </div>
    </section>

    <section class="glass-card p-6 -mt-6 relative z-10 space-y-6" id="restock-template-section">
        <div>
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Tambah Produk ke Template</h2>
            <form class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3 items-end" id="restock-template-form">
                @csrf
                <div>
                    <label class="text-xs text-gray-500 uppercase tracking-wide" for="template-product">Produk</label>
                    <select id="template-product" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                        <option value="">Pilih produk aktif</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase tracking-wide" for="template-default-quantity">Jumlah Default</label>
                    <input type="number" min="1" value="1" id="template-default-quantity" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Tambah Produk</button>
                    <button type="button" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600" id="refresh-products">Muat Ulang</button>
                </div>
            </form>
        </div>

        <div>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Daftar Produk Template</h2>
                <span class="text-xs text-gray-500" id="template-count">0 produk</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border border-gray-200 rounded-lg" id="restock-template-table">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-3 py-2 text-left">Produk</th>
                        <th class="px-3 py-2 text-left">Jumlah Default</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-center">Aksi</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const productSelect = document.getElementById('template-product');
            const defaultQuantityInput = document.getElementById('template-default-quantity');
            const templateForm = document.getElementById('restock-template-form');
            const refreshBtn = document.getElementById('refresh-products');
            const tableBody = document.querySelector('#restock-template-table tbody');
            const templateCount = document.getElementById('template-count');

            if (!templateForm) {
                return;
            }

            function renderProducts(products) {
                productSelect.innerHTML = '<option value="">Pilih produk aktif</option>';
                products.forEach(product => {
                    const option = document.createElement('option');
                    option.value = product.id;
                    option.textContent = `${product.sku} - ${product.name}`;
                    productSelect.appendChild(option);
                });
            }

            function loadProducts() {
                productSelect.disabled = true;
                fetch('/api/restock/products', {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Gagal memuat produk aktif.');
                        }
                        return response.json();
                    })
                    .then(renderProducts)
                    .catch(error => {
                        console.error(error);
                        toast(error.message || 'Tidak dapat memuat produk.', 'error');
                    })
                    .finally(() => {
                        productSelect.disabled = false;
                    });
            }

            function renderTemplate(items) {
                tableBody.innerHTML = '';
                templateCount.textContent = `${items.length} produk`;

                if (!items.length) {
                    const emptyRow = document.createElement('tr');
                    emptyRow.innerHTML = '<td colspan="4" class="px-3 py-6 text-center text-gray-500 text-sm">Belum ada produk pada template restok.</td>';
                    tableBody.appendChild(emptyRow);
                    return;
                }

                items.forEach(item => {
                    const tr = document.createElement('tr');
                    tr.className = 'border-t border-gray-200';
                    tr.dataset.id = item.id;

                    const unit = item.product.unit?.name ? ` (${item.product.unit.name})` : '';

                    tr.innerHTML = `
                        <td class="px-3 py-2">
                            <div class="font-semibold text-gray-800">${item.product.name}</div>
                            <div class="text-xs text-gray-500">${item.product.sku}${unit}</div>
                        </td>
                        <td class="px-3 py-2">
                            <input type="number" min="1" value="${item.default_quantity}" class="template-quantity border border-gray-200 rounded px-3 py-2 w-28 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </td>
                        <td class="px-3 py-2">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                                <input type="checkbox" class="template-status" ${item.is_active ? 'checked' : ''}>
                                <span>${item.is_active ? 'Aktif' : 'Nonaktif'}</span>
                            </label>
                        </td>
                        <td class="px-3 py-2 text-center">
                            <div class="flex justify-center gap-3 text-sm">
                                <button type="button" class="simpan-template text-blue-600 font-semibold">Simpan</button>
                                <button type="button" class="hapus-template text-red-600 font-semibold">Hapus</button>
                            </div>
                        </td>
                    `;

                    tableBody.appendChild(tr);
                });
            }

            function loadTemplate() {
                fetch('/api/restock/templates', {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Gagal memuat template restok.');
                        }
                        return response.json();
                    })
                    .then(renderTemplate)
                    .catch(error => {
                        console.error(error);
                        toast(error.message || 'Tidak dapat memuat template.', 'error');
                    });
            }

            templateForm.addEventListener('submit', (event) => {
                event.preventDefault();

                const productId = productSelect.value;
                const quantity = Number(defaultQuantityInput.value || 0);

                if (!productId) {
                    toast('Pilih produk terlebih dahulu.', 'warning');
                    return;
                }

                if (!quantity || quantity < 1) {
                    toast('Jumlah default minimal 1.', 'warning');
                    return;
                }

                const payload = {
                    product_id: Number(productId),
                    default_quantity: quantity,
                };

                templateForm.querySelector('button[type="submit"]').disabled = true;

                fetch('/api/restock/templates', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                })
                    .then(async response => {
                        templateForm.querySelector('button[type="submit"]').disabled = false;
                        if (!response.ok) {
                            const errorBody = await response.json().catch(() => ({}));
                            throw new Error(errorBody.message || 'Gagal menambah produk.');
                        }

                        toast('Produk berhasil ditambahkan ke template.', 'success');
                        loadTemplate();
                        productSelect.value = '';
                        defaultQuantityInput.value = 1;
                    })
                    .catch(error => {
                        console.error(error);
                        toast(error.message || 'Terjadi kesalahan.', 'error');
                    });
            });

            refreshBtn.addEventListener('click', () => {
                loadProducts();
                toast('Daftar produk diperbarui.', 'info');
            });

            tableBody.addEventListener('click', (event) => {
                const target = event.target;
                const row = target.closest('tr');
                if (!row || !row.dataset.id) {
                    return;
                }

                const itemId = row.dataset.id;

                if (target.classList.contains('hapus-template')) {
                    if (!confirm('Hapus produk ini dari template restok?')) {
                        return;
                    }

                    fetch(`/api/restock/templates/${itemId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Gagal menghapus produk.');
                            }
                            toast('Produk dihapus dari template.', 'success');
                            loadTemplate();
                        })
                        .catch(error => {
                            console.error(error);
                            toast(error.message || 'Terjadi kesalahan saat menghapus.', 'error');
                        });
                }

                if (target.classList.contains('simpan-template')) {
                    const quantityInput = row.querySelector('.template-quantity');
                    const statusInput = row.querySelector('.template-status');
                    const payload = {
                        default_quantity: Number(quantityInput.value || 1),
                        is_active: statusInput.checked,
                    };

                    if (!payload.default_quantity || payload.default_quantity < 1) {
                        toast('Jumlah default minimal 1.', 'warning');
                        return;
                    }

                    target.disabled = true;

                    fetch(`/api/restock/templates/${itemId}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(payload)
                    })
                        .then(async response => {
                            target.disabled = false;
                            if (!response.ok) {
                                const errorBody = await response.json().catch(() => ({}));
                                throw new Error(errorBody.message || 'Gagal memperbarui produk.');
                            }
                            toast('Produk berhasil diperbarui.', 'success');
                            loadTemplate();
                        })
                        .catch(error => {
                            console.error(error);
                            toast(error.message || 'Terjadi kesalahan saat menyimpan.', 'error');
                            target.disabled = false;
                        });
                }
            });

            tableBody.addEventListener('change', (event) => {
                if (event.target.classList.contains('template-status')) {
                    const label = event.target.nextElementSibling;
                    if (label) {
                        label.textContent = event.target.checked ? 'Aktif' : 'Nonaktif';
                    }
                }
            });

            loadProducts();
            loadTemplate();
        });
    </script>
@endpush
