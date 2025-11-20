export function initRestockForm(section) {
    if (!section) {
        return;
    }

    const branchId = document.body.dataset.branchId || '';
    const form = section.querySelector('#restock-form');
    if (!form) {
        return;
    }

    const warning = document.getElementById('restock-branch-warning');
    const supplierSelect = form.querySelector('#supplier-select');
    const expectedDateInput = form.querySelector('#expected-date');
    const notesInput = form.querySelector('#restock-notes');
    const addRowButton = form.querySelector('#add-restock-row');
    const resetButton = form.querySelector('#reset-restock');
    const submitButton = form.querySelector('#submit-restock');
    const itemsTableBody = form.querySelector('#restock-items-table tbody');
    const tokenInput = form.querySelector('input[name="_token"]');

    if (!itemsTableBody || !supplierSelect || !addRowButton || !resetButton || !submitButton) {
        return;
    }

    if (!branchId && warning) {
        warning.classList.remove('hidden');
    }

    const state = {
        templateItems: [],
        suppliers: [],
        templateMap: new Map(),
    };

    toggleForm(false);
    loadBootstrap();

    addRowButton.addEventListener('click', () => {
        if (!state.templateItems.length) {
            toast('Belum ada produk dalam template restok.', 'warning');
            return;
        }
        addRow();
    });

    resetButton.addEventListener('click', () => {
        resetForm();
        toast('Formulir telah direset.', 'info');
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        handleSubmit();
    });

    async function loadBootstrap() {
        try {
            const response = await fetch('/api/restock/bootstrap', {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Gagal memuat data restok.');
            }

            const payload = await response.json();
            const items = Array.isArray(payload?.template_items) ? payload.template_items : [];
            const suppliers = Array.isArray(payload?.suppliers) ? payload.suppliers : [];

            state.templateItems = items;
            state.suppliers = suppliers;
            state.templateMap = new Map(
                items
                    .filter((item) => item?.product?.id)
                    .map((item) => [String(item.product.id), item])
            );

            renderSuppliers();

            if (!items.length) {
                toast('Template restok kosong. Hubungi super admin untuk menambahkan produk.', 'warning');
            }

            resetForm();
            toggleForm(items.length > 0 && suppliers.length > 0);
        } catch (error) {
            console.error(error);
            toggleForm(false);
            toast(error.message || 'Tidak dapat memuat data restok.', 'error');
        }
    }

    function toggleForm(enabled) {
        const controls = [supplierSelect, expectedDateInput, notesInput, addRowButton, resetButton, submitButton];
        controls.forEach((element) => {
            if (element) {
                element.disabled = !enabled;
            }
        });

        Array.from(itemsTableBody.querySelectorAll('input, select, button')).forEach((element) => {
            element.disabled = !enabled;
        });
    }

    function renderSuppliers() {
        supplierSelect.innerHTML = '<option value="">Pilih pemasok</option>';
        state.suppliers.forEach((supplier) => {
            const option = document.createElement('option');
            option.value = supplier.id;
            option.textContent = supplier.name;
            supplierSelect.appendChild(option);
        });
    }

    function resetForm() {
        supplierSelect.value = '';
        if (expectedDateInput) {
            expectedDateInput.value = '';
        }
        if (notesInput) {
            notesInput.value = '';
        }

        itemsTableBody.innerHTML = '';

        if (state.templateItems.length) {
            addRow(String(state.templateItems[0].product.id));
        }
    }

    function addRow(initialProductId = '') {
        const row = document.createElement('tr');
        row.className = 'border-t border-gray-200';
        row.innerHTML = `
            <td class="px-3 py-2">
                <select class="item-product border border-gray-200 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                    <option value="">Pilih produk</option>
                </select>
            </td>
            <td class="px-3 py-2 text-gray-600 item-unit">-</td>
            <td class="px-3 py-2">
                <input type="number" min="1" class="item-quantity w-24 border border-gray-200 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
            </td>
            <td class="px-3 py-2">
                <input type="number" min="0" step="0.01" class="item-cost w-32 border border-gray-200 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
            </td>
            <td class="px-3 py-2 text-center">
                <button type="button" class="remove-row text-sm text-red-600 font-semibold hover:underline">Hapus</button>
            </td>
        `;

        itemsTableBody.appendChild(row);

        const productSelect = row.querySelector('.item-product');
        const quantityInput = row.querySelector('.item-quantity');
        const costInput = row.querySelector('.item-cost');
        const unitCell = row.querySelector('.item-unit');
        const removeButton = row.querySelector('.remove-row');

        populateProductSelect(productSelect, initialProductId);

        productSelect.addEventListener('change', () => {
            applyTemplateDefaults(productSelect.value, quantityInput, costInput, unitCell);
        });

        removeButton.addEventListener('click', () => {
            if (itemsTableBody.children.length === 1) {
                toast('Minimal satu baris produk diperlukan.', 'warning');
                return;
            }
            row.remove();
        });

        if (initialProductId) {
            productSelect.value = initialProductId;
            applyTemplateDefaults(initialProductId, quantityInput, costInput, unitCell);
        }
    }

    function populateProductSelect(select, selectedValue = '') {
        select.innerHTML = '<option value="">Pilih produk</option>';
        state.templateItems.forEach((item) => {
            const option = document.createElement('option');
            option.value = item.product.id;
            const unitName = item.product.unit?.name ? ` (${item.product.unit.name})` : '';
            option.textContent = `${item.product.sku} - ${item.product.name}${unitName}`;
            select.appendChild(option);
        });

        if (selectedValue) {
            select.value = selectedValue;
        }
    }

    function applyTemplateDefaults(productId, quantityInput, costInput, unitCell) {
        const template = state.templateMap.get(String(productId));

        if (!template) {
            unitCell.textContent = '-';
            quantityInput.value = '';
            costInput.value = '';
            return;
        }

        unitCell.textContent = template.product.unit?.name || '-';

        if (!quantityInput.value) {
            quantityInput.value = template.default_quantity || 1;
        }

        if (!costInput.value) {
            const defaultCost = template.product.default_cost ?? template.product.default_price ?? 0;
            costInput.value = defaultCost ? Number(defaultCost).toFixed(2) : '';
        }
    }

    async function handleSubmit() {
        if (!branchId) {
            toast('Pilih cabang aktif terlebih dahulu.', 'warning');
            return;
        }

        if (!supplierSelect.value) {
            toast('Pilih pemasok terlebih dahulu.', 'warning');
            return;
        }

        const rows = Array.from(itemsTableBody.querySelectorAll('tr'));
        if (!rows.length) {
            toast('Tambahkan minimal satu produk restok.', 'warning');
            return;
        }

        const items = [];
        for (const row of rows) {
            const productSelect = row.querySelector('.item-product');
            const quantityInput = row.querySelector('.item-quantity');
            const costInput = row.querySelector('.item-cost');

            const productId = productSelect?.value;
            const quantity = Number(quantityInput?.value || 0);
            const unitCost = Number(costInput?.value || 0);

            if (!productId) {
                toast('Pilih produk untuk setiap baris restok.', 'warning');
                return;
            }

            if (!Number.isFinite(quantity) || quantity < 1 || !Number.isFinite(unitCost) || unitCost < 0) {
                toast('Pastikan jumlah dan harga diisi dengan benar.', 'warning');
                return;
            }

            items.push({
                product_id: Number(productId),
                quantity_ordered: quantity,
                unit_cost: unitCost,
            });
        }

        submitButton.disabled = true;
        submitButton.textContent = 'Menyimpan...';

        try {
            const response = await fetch('/api/purchase-orders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': tokenInput?.value || '',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    supplier_id: Number(supplierSelect.value),
                    branch_id: Number(branchId),
                    expected_date: expectedDateInput?.value || null,
                    items,
                    notes: notesInput?.value || null,
                }),
            });

            if (!response.ok) {
                const errorBody = await response.json().catch(() => ({}));
                throw new Error(errorBody?.message || 'Gagal membuat purchase order.');
            }

            toast('Purchase order berhasil dibuat.', 'success');
            resetForm();
        } catch (error) {
            console.error(error);
            toast(error.message || 'Terjadi kesalahan saat membuat restok.', 'error');
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Simpan Restok';
        }
    }

}
