@extends('layouts.app')

@section('page_title', 'Pengaturan Akses Pengguna')
@section('page_subtitle', 'Atur cabang tujuan login dan hak super admin untuk setiap akun')

@section('content')
    <section class="hero-gradient p-8 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-3">
                <p class="text-sm uppercase text-white-soft" style="letter-spacing: 0.35em;">Login Command</p>
                <h1 class="text-3xl font-semibold">Kontrol Akses Pengguna</h1>
                <p class="text-white-fade text-sm">Tentukan lokasi dashboard setiap pengguna saat login dan kelola hak super admin.</p>
            </div>
            <div class="grid grid-cols-2 gap-3 text-white text-center">
                <div class="stat-pill">
                    <span class="text-xs uppercase text-white-soft">Total Pengguna</span>
                    <strong class="text-white text-xl">{{ $users->count() }}</strong>
                </div>
                <div class="stat-pill">
                    <span class="text-xs uppercase text-white-soft">Super Admin</span>
                    <strong class="text-white text-xl">{{ $users->where('is_super_admin', true)->count() }}</strong>
                </div>
            </div>
        </div>
    </section>

    <section class="glass-card p-6 -mt-6 relative z-10 space-y-6">
        @if(session('status'))
            <div class="px-4 py-3 rounded bg-green-50 text-green-700 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="px-4 py-3 rounded bg-rose-50 text-rose-700 text-sm">
                <p class="font-semibold mb-1">Terjadi kesalahan:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="border border-gray-100 rounded-xl p-5 bg-white/70">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Tambah Pengguna Baru</h2>
            <form method="POST" action="{{ route('login.users.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="text-xs text-gray-500 uppercase tracking-wide" for="new-user-name">Nama Lengkap</label>
                    <input id="new-user-name" name="name" type="text" value="{{ old('name') }}" required class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase tracking-wide" for="new-user-email">Email</label>
                    <input id="new-user-email" name="email" type="email" value="{{ old('email') }}" required class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase tracking-wide" for="new-user-phone">No. Telepon</label>
                    <input id="new-user-phone" name="phone" type="text" value="{{ old('phone') }}" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Opsional">
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase tracking-wide" for="new-user-password">Password Awal</label>
                    <input id="new-user-password" name="password" type="password" required minlength="8" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Minimal 8 karakter">
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase tracking-wide" for="new-user-default-branch">Cabang Default</label>
                    <select id="new-user-default-branch" name="default_branch_id" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">Dashboard utama</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(old('default_branch_id') == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500 uppercase tracking-wide" for="new-user-role">Peran Cabang</label>
                    <select id="new-user-role" name="branch_role" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="member" @selected(old('branch_role') === 'member')>Staf Operasional</option>
                        <option value="branch_manager" @selected(old('branch_role') === 'branch_manager')>Branch Manager</option>
                        <option value="director" @selected(old('branch_role') === 'director')>Direktur / Manajemen</option>
                        <option value="procurement" @selected(old('branch_role') === 'procurement')>Procurement</option>
                        <option value="cashier" @selected(old('branch_role') === 'cashier')>Kasir</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs text-gray-500 uppercase tracking-wide" for="new-user-branches">Cabang yang Dapat Diakses</label>
                    <select id="new-user-branches" name="branches[]" multiple size="4" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(collect(old('branches', []))->contains($branch->id))>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-gray-500">Pilih satu atau lebih cabang. Jika cabang default dipilih, tambahkan juga ke daftar ini.</p>
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_super_admin" value="0">
                    <input id="new-user-super" type="checkbox" name="is_super_admin" value="1" @checked(old('is_super_admin')) class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="new-user-super" class="text-sm text-gray-600">Jadikan super admin</label>
                </div>
                <div class="md:col-span-2 flex justify-end gap-2">
                    <button type="reset" class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm uppercase tracking-wide">Reset</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm uppercase tracking-wide">Simpan Pengguna</button>
                </div>
            </form>
        </div>

        <div class="space-y-4">
            @forelse($users as $user)
                @php
                    $userBranchIds = $user->branches->pluck('id')->all();
                    $currentRole = optional($user->branches->firstWhere('id', $user->default_branch_id))->pivot?->role
                        ?? optional($user->branches->first())->pivot?->role
                        ?? 'member';
                @endphp
                <div class="border border-gray-100 rounded-xl p-5 bg-white/60">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-800">{{ $user->name }}</h3>
                            <p class="text-xs text-gray-500">ID Pengguna: {{ $user->id }}</p>
                        </div>
                        <div class="text-xs text-gray-500">Cabang terhubung: {{ $user->branches->count() }}</div>
                    </div>
                    <form method="POST" action="{{ route('login.users.update', $user) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="text-xs text-gray-500 uppercase tracking-wide" for="user-name-{{ $user->id }}">Nama</label>
                            <input id="user-name-{{ $user->id }}" name="name" type="text" value="{{ $user->name }}" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 uppercase tracking-wide" for="user-email-{{ $user->id }}">Email</label>
                            <input id="user-email-{{ $user->id }}" name="email" type="email" value="{{ $user->email }}" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 uppercase tracking-wide" for="user-phone-{{ $user->id }}">No. Telepon</label>
                            <input id="user-phone-{{ $user->id }}" name="phone" type="text" value="{{ $user->phone }}" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 uppercase tracking-wide" for="user-password-{{ $user->id }}">Password Baru</label>
                            <input id="user-password-{{ $user->id }}" name="password" type="password" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Kosongkan jika tidak berubah" minlength="8">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 uppercase tracking-wide" for="user-default-branch-{{ $user->id }}">Cabang Default</label>
                            <select id="user-default-branch-{{ $user->id }}" name="default_branch_id" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <option value="">Dashboard utama</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected($user->default_branch_id === $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 uppercase tracking-wide" for="user-role-{{ $user->id }}">Peran Cabang</label>
                            <select id="user-role-{{ $user->id }}" name="branch_role" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <option value="member" @selected($currentRole === 'member')>Staf Operasional</option>
                                <option value="branch_manager" @selected($currentRole === 'branch_manager')>Branch Manager</option>
                                <option value="director" @selected($currentRole === 'director')>Direktur / Manajemen</option>
                                <option value="procurement" @selected($currentRole === 'procurement')>Procurement</option>
                                <option value="cashier" @selected($currentRole === 'cashier')>Kasir</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs text-gray-500 uppercase tracking-wide" for="user-branches-{{ $user->id }}">Cabang yang Dapat Diakses</label>
                            <select id="user-branches-{{ $user->id }}" name="branches[]" multiple size="4" class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected(in_array($branch->id, $userBranchIds, true))>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-xs text-gray-500">Cabang default akan otomatis ditambahkan bila belum dipilih.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="is_super_admin" value="0">
                            <input id="user-super-{{ $user->id }}" type="checkbox" name="is_super_admin" value="1" @checked($user->is_super_admin) class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="user-super-{{ $user->id }}" class="text-sm text-gray-600">Super admin</label>
                        </div>
                        <div class="md:col-span-2 flex items-center justify-between gap-3 flex-wrap">
                            <div class="text-xs text-gray-500">
                                @if($user->branches->isNotEmpty())
                                    <span>Cabang terkait: {{ $user->branches->pluck('name')->implode(', ') }}</span>
                                @else
                                    <span>Belum memiliki cabang yang diizinkan.</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="reset" class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm uppercase tracking-wide">Reset</button>
                                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm uppercase tracking-wide">Simpan Perubahan</button>
                            </div>
                        </div>
                    </form>
                    <div class="mt-3 flex justify-end">
                        <form method="POST" action="{{ route('login.users.destroy', $user) }}" onsubmit="return confirm('Hapus pengguna {{ $user->name }}? Tindakan ini tidak dapat dibatalkan.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 bg-rose-600 text-white rounded-lg text-sm uppercase tracking-wide">Hapus Pengguna</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 text-center py-6">Belum ada pengguna terdaftar.</p>
            @endforelse
        </div>
    </section>
@endsection