@extends('layouts.app')

@section('title', 'Penjualan Cepat · Nyxx Agrisupply')
@section('page_title', 'Penjualan Cepat')
@section('page_subtitle', 'Catat transaksi POS tanpa pembukaan shift')

@php
    $branchId = session('branch_id');
@endphp

@section('content')
    <section class="hero-gradient p-8 mb-6">
        <div class="space-y-3 max-w-3xl">
            <p class="text-sm uppercase text-white-soft" style="letter-spacing: 0.35em;">Quick POS</p>
            <h1 class="text-3xl font-semibold">Formulir Penjualan Cepat</h1>
            <p class="text-white-fade text-sm">Lengkapi daftar produk, jumlah, dan metode pembayaran untuk langsung mencatat penjualan cabang.</p>
        </div>
    </section>

    <section class="glass-card p-6 -mt-6 relative z-10 space-y-6" id="quick-sale-section">
        @if(!$branchId)
            <div class="px-4 py-3 rounded bg-amber-100 text-amber-800 text-sm">
                Pilih cabang aktif melalui menu di kanan atas sebelum membuat penjualan baru.
            </div>
        @endif

        <form id="quick-sale-form" class="space-y-6" data-branch-id="{{ $branchId ?? '' }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs text-gray-500 uppercase tracking-wide" for="quick-sale-number">Nomor Transaksi</label>
                    <input id="quick-sale-number" type="text" maxlength="100" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Opsional, otomatis jika kosong">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-gray-500 uppercase tracking-wide" for="quick-sale-date">Tanggal</label>
                        <input id="quick-sale-date" type="date" value="{{ now()->format('Y-m-d') }}" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 uppercase tracking-wide" for="quick-sale-payment">Metode Pembayaran</label>
                        <select id="quick-sale-payment" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <option value="cash">Tunai</option>
                            <option value="transfer">Transfer</option>
                            <option value="ewallet">Dompet Digital</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase tracking-wide" for="quick-sale-tax">Pajak (Rp)</label>
                    <input id="quick-sale-tax" type="number" min="0" step="0.01" value="0" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-gray-500 uppercase tracking-wide" for="quick-sale-customer-name">Nama Pelanggan</label>
                        <input id="quick-sale-customer-name" type="text" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Opsional">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 uppercase tracking-wide" for="quick-sale-customer-phone">No. Telepon</label>
                        <input id="quick-sale-customer-phone" type="text" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Opsional">
                    </div>
                </div>
            </div>

            <div>
                <label class="text-xs text-gray-500 uppercase tracking-wide block mb-2">Daftar Produk Terjual</label>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border border-gray-200 rounded-lg" id="quick-sale-items">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-3 py-2 text-left w-1/3">Produk</th>
                            <th class="px-3 py-2 text-left">Jumlah</th>
                            <th class="px-3 py-2 text-left">Harga Satuan (Rp)</th>
                            <th class="px-3 py-2 text-left">Diskon (Rp)</th>
                            <th class="px-3 py-2 text-right">Total</th>
                            <th class="px-3 py-2 text-center">Aksi</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="mt-3 flex justify-between items-center text-xs text-gray-500">
                    <span>Tambahkan minimal satu produk untuk menyimpan transaksi.</span>
                    <button type="button" class="px-3 py-2 bg-blue-600 text-white rounded text-xs uppercase tracking-wide" id="add-quick-sale-row">Tambah Produk</button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <p class="text-sm font-semibold text-gray-700">Ringkasan Pembayaran</p>
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Subtotal</span>
                        <span id="quick-sale-subtotal" class="font-medium text-gray-800">Rp0</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Total Diskon</span>
                        <span id="quick-sale-discount" class="font-medium text-gray-800">Rp0</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Pajak</span>
                        <span id="quick-sale-tax-summary" class="font-medium text-gray-800">Rp0</span>
                    </div>
                    <div class="flex justify-between text-base text-gray-900 font-semibold">
                        <span>Grand Total</span>
                        <span id="quick-sale-grand" class="text-emerald-600">Rp0</span>
                    </div>
                </div>
                <div class="flex flex-wrap items-end justify-end gap-3">
                    <button type="button" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600" id="reset-quick-sale">Reset</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm" id="submit-quick-sale">Simpan Penjualan</button>
                </div>
            </div>
        </form>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('quick-sale-form');
            if (!form) {
                return;
            }

            const branchId = Number(form.dataset.branchId || 0);
            const addRowBtn = document.getElementById('add-quick-sale-row');
            const resetBtn = document.getElementById('reset-quick-sale');
            const submitBtn = document.getElementById('submit-quick-sale');
            const taxInput = document.getElementById('quick-sale-tax');
            const itemsBody = document.querySelector('#quick-sale-items tbody');
            const subtotalLabel = document.getElementById('quick-sale-subtotal');
            const discountLabel = document.getElementById('quick-sale-discount');
            const taxSummaryLabel = document.getElementById('quick-sale-tax-summary');
            const grandLabel = document.getElementById('quick-sale-grand');

            let products = [];

            if (!branchId) {
                disableForm();
                return;
            }

            loadProducts();
            addRow();
            attachListeners();

            function disableForm() {
                [addRowBtn, resetBtn, submitBtn].forEach((btn) => {
                    if (btn) {
                        btn.disabled = true;
                        btn.classList.add('opacity-50', 'cursor-not-allowed');
                    }
                });
            }

            function loadProducts() {
                fetch('/api/pos/products', {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error('Gagal memuat daftar produk.');
                        }
                        return response.json();
                    })
                    .then((data) => {
                        if (!Array.isArray(data)) {
                            throw new Error('Format produk tidak valid.');
                        }
                        products = data;
                        refreshProductOptions();
                    })
                    .catch((error) => {
                        console.error(error);
                        toast(error.message || 'Tidak dapat memuat produk.', 'error');
                    });
            }

            function refreshProductOptions() {
                const selects = itemsBody.querySelectorAll('select.quick-sale-product');
                selects.forEach((select) => populateProductSelect(select));
            }

            function populateProductSelect(select) {
                const selectedValue = select.value;
                select.innerHTML = '<option value="">Pilih produk</option>';
                products.forEach((product) => {
                    const option = document.createElement('option');
                    option.value = product.id;
                    option.textContent = `${product.sku} — ${product.name}`;
                    option.dataset.price = product.default_price ?? 0;
                    if (String(product.id) === selectedValue) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            }

            function addRow(initial = {}) {
                const tr = document.createElement('tr');
                tr.className = 'border-t border-gray-200';
                tr.innerHTML = `
                    <td class="px-3 py-2">
                        <select class="quick-sale-product w-full border border-gray-200 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <option value="">Pilih produk</option>
                        </select>
                    </td>
                    <td class="px-3 py-2">
                        <input type="number" min="1" value="${initial.quantity ?? 1}" class="quick-sale-quantity w-full border border-gray-200 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </td>
                    <td class="px-3 py-2">
                        <input type="number" min="0" step="0.01" value="${initial.unit_price ?? 0}" class="quick-sale-price w-full border border-gray-200 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </td>
                    <td class="px-3 py-2">
                        <input type="number" min="0" step="0.01" value="${initial.discount ?? 0}" class="quick-sale-discount w-full border border-gray-200 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </td>
                    <td class="px-3 py-2 text-right font-semibold text-gray-800">
                        <span class="quick-sale-line-total">Rp0</span>
                    </td>
                    <td class="px-3 py-2 text-center">
                        <button type="button" class="remove-quick-sale-row text-red-600 font-semibold">Hapus</button>
                    </td>
                `;

                itemsBody.appendChild(tr);

                const productSelect = tr.querySelector('select.quick-sale-product');
                populateProductSelect(productSelect);
                if (initial.product_id) {
                    productSelect.value = String(initial.product_id);
                }

                const priceInput = tr.querySelector('.quick-sale-price');
                const quantityInput = tr.querySelector('.quick-sale-quantity');
                const discountInput = tr.querySelector('.quick-sale-discount');

                productSelect.addEventListener('change', () => {
                    if (!priceInput.value || Number(priceInput.value) === 0) {
                        const option = productSelect.selectedOptions[0];
                        if (option && option.dataset.price) {
                            priceInput.value = Number(option.dataset.price || 0);
                        }
                    }
                    recalculateTotals();
                });

                [quantityInput, priceInput, discountInput].forEach((input) => {
                    input.addEventListener('input', () => {
                        if (Number(input.value) < 0) {
                            input.value = '0';
                        }
                        recalculateTotals();
                    });
                });

                recalculateTotals();
            }

            function attachListeners() {
                addRowBtn.addEventListener('click', () => {
                    addRow();
                });

                resetBtn.addEventListener('click', () => {
                    itemsBody.innerHTML = '';
                    addRow();
                    form.reset();
                    document.getElementById('quick-sale-date').value = "{{ now()->format('Y-m-d') }}";
                    document.getElementById('quick-sale-payment').value = 'cash';
                    document.getElementById('quick-sale-tax').value = '0';
                    recalculateTotals();
                });

                itemsBody.addEventListener('click', (event) => {
                    const target = event.target;
                    if (target.classList.contains('remove-quick-sale-row')) {
                        const rows = itemsBody.querySelectorAll('tr');
                        if (rows.length === 1) {
                            toast('Minimal satu produk harus tercantum.', 'warning');
                            return;
                        }
                        target.closest('tr').remove();
                        recalculateTotals();
                    }
                });

                taxInput.addEventListener('input', () => {
                    if (Number(taxInput.value) < 0) {
                        taxInput.value = '0';
                    }
                    recalculateTotals();
                });

                form.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    if (submitBtn.disabled) {
                        return;
                    }

                    const payload = buildPayload();
                    if (!payload) {
                        return;
                    }

                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Menyimpan...';

                    try {
                        const response = await fetch('/api/pos/sales/quick', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify(payload)
                        });

                        const body = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            const message = body?.message || 'Gagal menyimpan penjualan.';
                            throw new Error(message);
                        }

                        toast(`Penjualan ${body?.number ?? ''} berhasil disimpan.`, 'success');
                        form.reset();
                        itemsBody.innerHTML = '';
                        addRow();
                        document.getElementById('quick-sale-date').value = "{{ now()->format('Y-m-d') }}";
                        document.getElementById('quick-sale-payment').value = 'cash';
                        document.getElementById('quick-sale-tax').value = '0';
                        recalculateTotals();
                    } catch (error) {
                        console.error(error);
                        toast(error.message || 'Terjadi kesalahan.', 'error');
                    } finally {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Simpan Penjualan';
                    }
                });
            }

            function buildPayload() {
                const rows = Array.from(itemsBody.querySelectorAll('tr'));
                if (!rows.length) {
                    toast('Tambahkan minimal satu produk.', 'warning');
                    return null;
                }

                const items = [];
                for (const row of rows) {
                    const productInput = row.querySelector('select.quick-sale-product');
                    const quantityInput = row.querySelector('.quick-sale-quantity');
                    const priceInput = row.querySelector('.quick-sale-price');
                    const discountInput = row.querySelector('.quick-sale-discount');

                    const productId = Number(productInput.value || 0);
                    const quantity = Number(quantityInput.value || 0);
                    const unitPrice = Number(priceInput.value || 0);
                    const discount = Number(discountInput.value || 0);

                    if (!productId) {
                        toast('Pilih produk untuk setiap baris.', 'warning');
                        productInput.focus();
                        return null;
                    }

                    if (quantity <= 0) {
                        toast('Jumlah produk minimal 1.', 'warning');
                        quantityInput.focus();
                        return null;
                    }

                    if (unitPrice < 0) {
                        toast('Harga tidak boleh negatif.', 'warning');
                        priceInput.focus();
                        return null;
                    }

                    const lineTotal = quantity * unitPrice;
                    if (discount < 0 || discount > lineTotal) {
                        toast('Diskon tidak boleh melebihi total baris.', 'warning');
                        discountInput.focus();
                        return null;
                    }

                    items.push({
                        product_id: productId,
                        quantity,
                        unit_price: unitPrice,
                        discount,
                    });
                }

                if (!items.length) {
                    toast('Tambahkan minimal satu produk.', 'warning');
                    return null;
                }

                const payload = {
                    branch_id: branchId,
                    number: form.querySelector('#quick-sale-number').value || null,
                    sold_at: form.querySelector('#quick-sale-date').value || null,
                    payment_method: form.querySelector('#quick-sale-payment').value || 'cash',
                    tax_total: Number(taxInput.value || 0),
                    items,
                };

                const customerName = form.querySelector('#quick-sale-customer-name').value.trim();
                const customerPhone = form.querySelector('#quick-sale-customer-phone').value.trim();

                if (customerName || customerPhone) {
                    payload.customer = {
                        name: customerName || 'Pelanggan',
                        phone: customerPhone || null,
                    };
                }

                return payload;
            }

            function recalculateTotals() {
                const rows = Array.from(itemsBody.querySelectorAll('tr'));
                let subtotal = 0;
                let discountTotal = 0;

                rows.forEach((row) => {
                    const quantity = Number(row.querySelector('.quick-sale-quantity').value || 0);
                    const unitPrice = Number(row.querySelector('.quick-sale-price').value || 0);
                    const discount = Number(row.querySelector('.quick-sale-discount').value || 0);
                    const lineTotal = Math.max(0, (quantity * unitPrice) - discount);
                    subtotal += quantity * unitPrice;
                    discountTotal += Math.min(discount, quantity * unitPrice);
                    row.querySelector('.quick-sale-line-total').textContent = formatCurrency(lineTotal);
                });

                const taxValue = Number(taxInput.value || 0);
                const grandTotal = Math.max(0, subtotal - discountTotal + taxValue);

                subtotalLabel.textContent = formatCurrency(subtotal);
                discountLabel.textContent = formatCurrency(discountTotal);
                taxSummaryLabel.textContent = formatCurrency(taxValue);
                grandLabel.textContent = formatCurrency(grandTotal);
            }

            function formatCurrency(value) {
                return Number(value || 0).toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
            }
        });
    </script>
@endpush
