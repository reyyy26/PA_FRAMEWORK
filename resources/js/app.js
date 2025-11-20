import './bootstrap';
import {initMainDashboard, loadBranchDashboard} from './dashboard';
import {initPosConsole} from './pos-console';
import {initPosHistory} from './pos-history';
import {initRestockForm} from './restock';

document.addEventListener('DOMContentLoaded', () => {
    const mainDashboard = document.getElementById('salesMixChart');
    const branchDashboard = document.getElementById('product-table');
    const branchSelect = document.getElementById('branch-select');

    if (branchSelect) {
        fetch('/api/branches', {
            headers: {
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(branches => {
                if (!Array.isArray(branches)) {
                    return;
                }

                branches.forEach(branch => {
                    const option = document.createElement('option');
                    option.value = branch.id;
                    option.textContent = `${branch.code} - ${branch.name}`;
                    if (document.body.dataset.branchId === String(branch.id)) {
                        option.selected = true;
                    }
                    branchSelect.appendChild(option);
                });
            });
    }

    const branchId = document.body.dataset.branchId ?? null;

    if (mainDashboard && branchId) {
        initMainDashboard(branchId);
    }

    if (branchDashboard && branchId) {
        loadBranchDashboard(branchId).catch(() => {
            console.warn('Gagal memuat dashboard cabang');
        });
    }

    const posConsoleRoot = document.getElementById('pos-console');
    if (posConsoleRoot) {
        initPosConsole(branchId, posConsoleRoot);
    }

    const posHistoryRoot = document.getElementById('pos-history');
    if (posHistoryRoot) {
        initPosHistory(branchId, posHistoryRoot);
    }

    const restockSection = document.getElementById('restock-section');
    if (restockSection) {
        initRestockForm(restockSection);
    }
});
