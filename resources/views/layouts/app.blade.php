<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Nyxx Agrisupply'))</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --nyxx-blue: #2563eb;
            --nyxx-indigo: #4f46e5;
            --nyxx-emerald: #0ea5e9;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.08), rgba(79, 70, 229, 0.08));
        }

        .surface-blur {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(18px);
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.7rem 1rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: #6b7280;
            border-radius: 0.75rem;
            transition: all 0.2s ease;
        }

        .sidebar-link:hover {
            background: rgba(37, 99, 235, 0.1);
            color: var(--nyxx-blue);
        }

        .sidebar-link.active {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.95), rgba(14, 165, 233, 0.95));
            color: #fff;
            box-shadow: 0 12px 30px -12px rgba(37, 99, 235, 0.7);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.125rem 0.5rem;
            font-size: 0.7rem;
            font-weight: 600;
            border-radius: 9999px;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.92);
            border-radius: 1.25rem;
            border: 1px solid rgba(255, 255, 255, 0.65);
            box-shadow: 0 20px 40px -24px rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(20px);
        }

        .hero-gradient {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.96), rgba(14, 165, 233, 0.96));
            color: #f8fafc;
            border-radius: 1.5rem;
            box-shadow: 0 30px 50px -40px rgba(14, 165, 233, 0.9);
        }

        .text-white-soft {
            color: rgba(255, 255, 255, 0.7);
        }

        .text-white-fade {
            color: rgba(255, 255, 255, 0.82);
        }

        .stat-pill {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            padding: 1rem;
            border-radius: 1rem;
            background: rgba(15, 23, 42, 0.18);
        }

        .stat-pill strong {
            font-size: 1.35rem;
        }
    </style>
    @vite(['resources/js/app.js'])
</head>
@php
    $currentUser = auth()->user()?->loadMissing(['branches']);
    $activeBranchId = session('branch_id') ?? $currentUser?->default_branch_id;
    $branchRole = null;

    if ($currentUser) {
        if ($activeBranchId) {
            $targetBranch = $currentUser->branches?->firstWhere('id', $activeBranchId);
            $branchRole = $targetBranch?->pivot?->role;
        }

        if (!$branchRole && $currentUser->branches?->isNotEmpty()) {
            $branchRole = $currentUser->branches->first()?->pivot?->role;
        }
    }

    $roleMap = [
        'branch_manager' => 'branch_manager',
        'director' => 'branch_manager',
        'cashier' => 'cashier',
        'procurement' => 'procurement',
    ];

    $roleKey = 'staff';

    if ($currentUser?->is_super_admin) {
        $roleKey = 'super_admin';
    } elseif ($branchRole) {
        $roleKey = $roleMap[$branchRole] ?? 'staff';
    }

    $navConfig = [
        'super_admin' => [
            ['label' => 'Dashboard Utama', 'href' => url('/dashboard/main'), 'icon' => 'home'],
            ['label' => 'Dashboard Cabang', 'href' => url('/dashboard/branch'), 'icon' => 'storefront'],
            ['label' => 'Audit Trail', 'href' => url('/audit'), 'icon' => 'clock'],
            ['label' => 'Manajemen Pengguna', 'href' => url('/login/users'), 'icon' => 'shield'],
            ['label' => 'Integrasi', 'href' => url('/integrations/logs'), 'icon' => 'globe'],
            ['label' => 'Restok Barang', 'href' => url('/inventory/restock'), 'icon' => 'clipboard'],
            ['label' => 'POS Console', 'href' => url('/pos/console'), 'icon' => 'pos'],
            ['label' => 'Template Restok', 'href' => url('/inventory/restock-template'), 'icon' => 'layers'],
        ],
        'branch_manager' => [
            ['label' => 'Dashboard Cabang', 'href' => url('/dashboard/branch'), 'icon' => 'storefront'],
            ['label' => 'Restok Barang', 'href' => url('/inventory/restock'), 'icon' => 'clipboard'],
            ['label' => 'Laporan Cabang', 'href' => url('/dashboard/main'), 'icon' => 'chart'],
            ['label' => 'POS Console', 'href' => url('/pos/console'), 'icon' => 'pos'],
        ],
        'procurement' => [
            ['label' => 'Dashboard Utama', 'href' => url('/dashboard/main'), 'icon' => 'home'],
            ['label' => 'Restok Barang', 'href' => url('/inventory/restock'), 'icon' => 'clipboard'],
            ['label' => 'POS Console', 'href' => url('/pos/console'), 'icon' => 'pos'],
        ],
        'cashier' => [
            ['label' => 'POS Penjualan', 'href' => url('/pos/sales/quick'), 'icon' => 'pos'],
            ['label' => 'Dashboard Cabang', 'href' => url('/dashboard/branch'), 'icon' => 'storefront'],
        ],
        'staff' => [
            ['label' => 'Dashboard Cabang', 'href' => url('/dashboard/branch'), 'icon' => 'storefront'],
            ['label' => 'Restok Barang', 'href' => url('/inventory/restock'), 'icon' => 'clipboard'],
        ],
    ];

    $navLinks = $navConfig[$roleKey] ?? $navConfig['staff'];

    $roleLabels = [
        'super_admin' => 'Super Admin',
        'branch_manager' => 'Branch Manager',
        'procurement' => 'Procurement',
        'cashier' => 'Cashier',
        'staff' => 'Staff',
    ];

    $activeRoleLabel = $roleLabels[$roleKey] ?? 'Staff';
@endphp
<body data-branch-id="{{ session('branch_id') }}">
<div class="min-h-screen flex">
    <aside class="hidden lg:flex lg:flex-col lg:w-64 surface-blur border-r shadow-sm">
        <div class="px-6 py-5 border-b">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <img src="{{ asset('logo/nyxx-agrisupply.svg') }}" alt="Nyxx Agrisupply" class="w-12 h-12">
                <span class="text-lg font-semibold text-gray-800">Nyxx Agrisupply</span>
            </a>
            </a>
        </div>
        @php
            $currentUrl = url()->current();
        @endphp
        <nav class="flex-1 overflow-y-auto py-4 space-y-1">
            @foreach($navLinks as $link)
                <a href="{{ $link['href'] }}" class="sidebar-link {{ $currentUrl === $link['href'] ? 'active' : '' }}">
                    @include('partials.icon', ['name' => $link['icon'], 'classes' => 'w-5 h-5'])
                    <span>{{ $link['label'] }}</span>
                </a>
            @endforeach
        </nav>
        <div class="px-6 py-4 border-t text-xs text-gray-500 space-y-1">
            <p>Cabang aktif: <span id="active-branch" class="font-medium text-gray-700">{{ session('branch_id') ? 'ID #' . session('branch_id') : 'Belum dipilih' }}</span></p>
            <p>&copy; {{ date('Y') }} Nyxx Agrisupply</p>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-h-screen">
        <header class="surface-blur shadow-sm">
            <div class="px-4 lg:px-8 py-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <button type="button" class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-md border text-gray-600" id="mobile-nav-toggle">
                        @include('partials.icon', ['name' => 'menu', 'classes' => 'w-5 h-5'])
                    </button>
                    <div>
                        <h1 class="text-xl font-semibold text-gray-800">@yield('page_title', 'Panel Operasional')</h1>
                        <p class="text-sm text-gray-500">@yield('page_subtitle', 'Kelola operasi agrisupply secara terpusat')</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <form method="POST" action="{{ url('/api/context/switch-branch') }}" class="flex items-center gap-2" id="branch-switcher">
                        <label class="text-sm text-gray-500" for="branch-select">Cabang</label>
                        <select class="border px-3 py-2 rounded text-sm" id="branch-select">
                            <option value="">Pilih cabang</option>
                        </select>
                        <button type="button" class="px-3 py-2 bg-blue-600 text-white text-sm rounded" onclick="switchBranch()">Ganti</button>
                    </form>
                    <div class="hidden sm:flex flex-col text-right text-xs text-gray-500">
                        <span class="inline-flex items-center gap-2 justify-end">
                            {{ $currentUser?->name }}
                            <span class="badge bg-blue-100 text-blue-700 border border-blue-200">{{ $activeRoleLabel }}</span>
                        </span>
                        <form method="POST" action="{{ route('logout') }}" class="flex items-center justify-end">
                            @csrf
                            <button type="submit" class="text-blue-600 font-semibold hover:underline">Keluar</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 px-4 lg:px-8 py-8" id="app-content">
            <div class="max-w-7xl mx-auto space-y-8">
                @yield('content')
            </div>
        </main>

        <footer class="surface-blur border-t px-4 lg:px-8 py-4 text-xs text-gray-500 flex items-center justify-between">
            <span>Versi Aplikasi 1.0 · Keamanan terjaga</span>
            <span>Dipantau oleh Nyxx Operations</span>
        </footer>
    </div>
</div>

<div id="toast-container" class="fixed top-5 right-5 space-y-3 z-50 max-w-sm"></div>

<div id="mobile-sidebar" class="lg:hidden fixed inset-0 bg-black bg-opacity-40 hidden">
    <div class="absolute inset-y-0 left-0 w-64 bg-white shadow-xl">
        <div class="px-6 py-5 border-b flex items-center justify-between">
            <span class="flex items-center gap-3">
                <img src="{{ asset('logo/nyxx-agrisupply.svg') }}" alt="Nyxx Agrisupply" class="w-10 h-10">
                <span class="text-lg font-semibold text-gray-800">Navigasi</span>
            </span>
            <button type="button" class="text-gray-500" id="mobile-nav-close">&times;</button>
        </div>
        <nav class="py-4 space-y-1 overflow-y-auto">
            @foreach($navLinks as $link)
                <a href="{{ $link['href'] }}" class="sidebar-link {{ $currentUrl === $link['href'] ? 'active' : '' }}">
                    @include('partials.icon', ['name' => $link['icon'], 'classes' => 'w-5 h-5'])
                    <span>{{ $link['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>
</div>

<script>
    function switchBranch() {
        const branchId = document.getElementById('branch-select').value;
        if (!branchId) {
            toast('Pilih cabang terlebih dahulu.', 'warning');
            return;
        }

        fetch('/api/context/switch-branch', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ branch_id: branchId })
        }).then(response => {
            if (response.ok) {
                toast('Cabang telah diganti.', 'success');
                setTimeout(() => window.location.reload(), 600);
            } else {
                toast('Gagal mengganti cabang.', 'error');
            }
        }).catch(() => toast('Tidak dapat terhubung ke server.', 'error'));
    }

    function toast(message, type = 'info') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const colors = {
            success: 'bg-green-500 text-white',
            error: 'bg-red-500 text-white',
            warning: 'bg-yellow-500 text-gray-900',
            info: 'bg-blue-500 text-white'
        };

        const el = document.createElement('div');
        el.className = `${colors[type] || colors.info} px-4 py-3 rounded shadow flex items-start gap-3`; 
        el.innerHTML = `
            <span class="font-semibold capitalize">${type}</span>
            <span class="flex-1 text-sm">${message}</span>
            <button type="button" class="text-sm font-semibold" aria-label="Close">&times;</button>
        `;

        el.querySelector('button').addEventListener('click', () => {
            container.removeChild(el);
        });

        container.appendChild(el);

        setTimeout(() => {
            if (container.contains(el)) {
                container.removeChild(el);
            }
        }, 5000);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const mobileToggle = document.getElementById('mobile-nav-toggle');
        const mobileSidebar = document.getElementById('mobile-sidebar');
        const mobileClose = document.getElementById('mobile-nav-close');

        if (mobileToggle && mobileSidebar) {
            mobileToggle.addEventListener('click', () => mobileSidebar.classList.remove('hidden'));
        }
        if (mobileClose && mobileSidebar) {
            mobileClose.addEventListener('click', () => mobileSidebar.classList.add('hidden'));
            mobileSidebar.addEventListener('click', (event) => {
                if (event.target === mobileSidebar) {
                    mobileSidebar.classList.add('hidden');
                }
            });
        }
    });
</script>

@stack('scripts')
</body>
</html>
