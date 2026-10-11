@extends('layouts.app')

@section('title', 'Kelola Katalog Reward - MyCafe POS System')

@section('content')
<!-- Main Content Area -->
<main class="flex-1 flex flex-col min-w-0 min-h-screen lg:h-screen lg:overflow-y-auto">
    <!-- Topbar -->
    @include('layouts.topbar', [
        'pageTitle' => 'Katalog Reward',
        'pageSubtitle' => 'Kelola katalog hadiah penukaran poin, kuota stok, dan status promosi loyalitas.',
        'topbarActions' => [
            ['url' => 'javascript:void(0)', 'onclick' => 'openRewardModal()', 'label' => 'Tambah Reward', 'icon' => 'add_circle', 'class' => 'px-3.5 py-1.5 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition shadow-subtle active:scale-95']
        ]
    ])

    <div class="p-4 sm:p-6 lg:p-8 space-y-6 flex-1">
        <!-- Stats Grid as Quick Filter Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('rewards.manage') }}" class="card-hover bg-white p-4 rounded-2xl border border-slate-200 shadow-subtle block group">
                <span class="text-xs text-slate-500 font-bold uppercase tracking-wider">Total Item Reward</span>
                <div class="flex items-center justify-between mt-1">
                    <p class="text-2xl font-black text-slate-900 font-mono">{{ number_format($totalReward) }}</p>
                    <span class="text-[11px] text-primary group-hover:translate-x-0.5 transition-transform">Semua &rarr;</span>
                </div>
            </a>
            <a href="{{ route('rewards.manage', ['status' => 'aktif']) }}" class="card-hover bg-white p-4 rounded-2xl border border-slate-200 shadow-subtle block group">
                <span class="text-xs text-emerald-600 font-bold uppercase tracking-wider">Reward Aktif</span>
                <div class="flex items-center justify-between mt-1">
                    <p class="text-2xl font-black text-emerald-600 font-mono">{{ number_format($totalAktif) }}</p>
                    <span class="text-[11px] text-emerald-600 group-hover:translate-x-0.5 transition-transform">Filter &rarr;</span>
                </div>
            </a>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-subtle">
                <span class="text-xs text-amber-500 font-bold uppercase tracking-wider">Total Klaim / Ditebus</span>
                <div class="flex items-center justify-between mt-1">
                    <p class="text-2xl font-black text-amber-600 font-mono">{{ number_format($totalDitebus) }} <span class="text-xs text-slate-400 font-normal">kali</span></p>
                    <span class="text-[11px] text-slate-400 font-mono">Riwayat member</span>
                </div>
            </div>
        </div>

        <!-- Category Pills Quick Filter -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 custom-scrollbar text-xs">
            <a href="{{ route('rewards.manage') }}" class="px-3.5 py-1.5 rounded-xl font-bold transition shrink-0 {{ empty($category) ? 'bg-primary text-white shadow-subtle border border-primary' : 'bg-white border border-slate-200 text-slate-600 hover:border-primary' }}">
                Semua Kategori
            </a>
            @foreach (['Minuman', 'Makanan', 'Diskon', 'Merchandise'] as $cItem)
            <a href="{{ route('rewards.manage', ['category' => $cItem]) }}" class="px-3.5 py-1.5 rounded-xl font-semibold transition shrink-0 {{ $category === $cItem ? 'bg-primary text-white shadow-subtle font-bold border border-primary' : 'bg-white border border-slate-200 text-slate-600 hover:border-primary' }}">
                {{ $cItem }}
            </a>
            @endforeach
        </div>

        <!-- Filter Bar -->
        <form action="{{ route('rewards.manage') }}" method="GET" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-subtle flex flex-col sm:flex-row gap-3 items-center">
            <div class="relative flex-1 w-full">
                <span class="material-icons absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                <input name="search" value="{{ $search }}" class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition" placeholder="Cari nama reward atau deskripsi hadiah..."/>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select name="status" class="bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 font-semibold">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ $status === 'nonaktif' ? 'selected' : '' }}>Non-aktif</option>
                </select>
                <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-4 py-2 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-1.5 transition shadow-subtle active:scale-95 shrink-0">
                    <span class="material-icons text-sm">filter_list</span>
                    <span>Filter</span>
                </button>
                @if ($search || $status || $category)
                <a href="{{ route('rewards.manage') }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition" title="Reset">
                    <span class="material-icons text-lg">close</span>
                </a>
                @endif
            </div>
        </form>

        <!-- Table of Rewards -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-subtle overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 text-slate-400 text-[11px] uppercase font-bold tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3.5">Item Reward</th>
                            <th class="px-5 py-3.5">Kategori</th>
                            <th class="px-5 py-3.5">Biaya Poin</th>
                            <th class="px-5 py-3.5 text-center">Stok Kuota</th>
                            <th class="px-5 py-3.5 text-center">Total Ditebus</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @if ($rewards->isEmpty())
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <span class="material-icons text-4xl text-slate-300 mb-2">redeem</span>
                                <p class="font-bold text-slate-600">Belum ada item reward</p>
                                <p class="text-xs text-slate-400 mt-1">Tambahkan item reward baru agar member dapat menukarkan poin mereka.</p>
                                <button type="button" onclick="openRewardModal()" class="mt-3 bg-primary hover:bg-primary-hover text-white text-xs font-bold px-4 py-2 rounded-xl shadow-subtle transition">
                                    + Tambah Reward Baru
                                </button>
                            </td>
                        </tr>
                        @else
                            @foreach ($rewards as $r)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-primary flex items-center justify-center shrink-0 border border-amber-500/20">
                                            <span class="material-icons text-lg">card_giftcard</span>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-900">{{ $r->name }}</h4>
                                            <p class="text-[11px] text-slate-400 line-clamp-1">{{ $r->description ?: 'Tidak ada deskripsi' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                        {{ $r->category ?: 'Umum' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-1 font-bold text-amber-700 font-mono text-xs">
                                        <span class="material-icons text-amber-500 text-sm">stars</span>
                                        <span>{{ number_format($r->poin_cost) }} pts</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-center font-mono text-xs font-bold text-slate-800">
                                    {!! $r->stock === -1 ? '<span class="text-slate-400 font-sans text-[11px]">Unlimited</span>' : number_format($r->stock) !!}
                                </td>
                                <td class="px-5 py-4 text-center text-xs font-bold text-slate-600 font-mono">
                                    {{ number_format($r->redemption_count ?? 0) }}x
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <form action="{{ route('rewards.toggle', $r->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase transition {{ $r->is_active ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20' : 'bg-slate-500/10 text-slate-500 border border-slate-500/20' }}" title="Klik untuk ubah status keaktifan">
                                            {{ $r->is_active ? 'Aktif' : 'Non-aktif' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" 
                                                data-id="{{ $r->id }}"
                                                data-name="{{ $r->name }}"
                                                data-cat="{{ $r->category }}"
                                                data-poin="{{ $r->poin_cost }}"
                                                data-stock="{{ $r->stock }}"
                                                data-desc="{{ $r->description }}"
                                                onclick="openEditRewardFromBtn(this)" 
                                                class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-500/10 rounded-lg transition active:scale-95" title="Edit Reward">
                                            <span class="material-icons text-base">edit</span>
                                        </button>
                                        <form action="{{ route('rewards.delete', $r->id) }}" method="POST" onsubmit="return confirm('Hapus reward {{ addslashes($r->name) }}?')" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-500/10 rounded-lg transition" title="Hapus Reward">
                                                <span class="material-icons text-base">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Add/Edit Reward Modal -->
<div id="reward-modal" onclick="if(event.target===this) closeRewardModal()" class="hidden fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-soft-lg border border-slate-200 relative animate-toast text-slate-900">
        <div class="flex justify-between items-center pb-3 border-b border-slate-200">
            <div class="flex items-center gap-2">
                <span class="material-icons text-primary text-xl">card_giftcard</span>
                <h3 class="font-extrabold text-base text-slate-900" id="modal-title">Tambah Reward Baru</h3>
            </div>
            <button type="button" onclick="closeRewardModal()" class="text-slate-400 hover:text-slate-600">
                <span class="material-icons">close</span>
            </button>
        </div>

        <form action="{{ route('rewards.store') }}" method="POST" class="pt-4 space-y-3.5">
            @csrf
            <input type="hidden" name="id" id="rew-id" value="0"/>

            <div>
                <label class="block text-xs font-bold uppercase mb-1">Nama Reward <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="rew-name" required placeholder="Contoh: Voucher Kopi Gratis 1 Cup" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500"/>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase mb-1">Kategori</label>
                <select name="category" id="rew-cat" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 font-semibold">
                    <option value="Minuman">Minuman</option>
                    <option value="Makanan">Makanan</option>
                    <option value="Diskon">Diskon</option>
                    <option value="Merchandise">Merchandise</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase mb-1">Biaya Poin <span class="text-rose-500">*</span></label>
                    <input type="number" name="poin_cost" id="rew-poin" required min="1" value="100" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-mono font-bold text-slate-900 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500"/>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase mb-1">Stok Kuota (-1 = ∞)</label>
                    <input type="number" name="stock" id="rew-stock" value="-1" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-mono font-bold text-slate-900 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500"/>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase mb-1">Deskripsi Singkat</label>
                <textarea name="description" id="rew-desc" rows="2" placeholder="Ketentuan penukaran reward..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-900 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closeRewardModal()" class="px-4 py-2 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-bold transition shadow-subtle">Simpan Reward</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openRewardModal() {
        document.getElementById('modal-title').textContent = 'Tambah Reward Baru';
        document.getElementById('rew-id').value = '0';
        document.getElementById('rew-name').value = '';
        document.getElementById('rew-cat').value = 'Minuman';
        document.getElementById('rew-poin').value = '100';
        document.getElementById('rew-stock').value = '-1';
        document.getElementById('rew-desc').value = '';
        document.getElementById('reward-modal').classList.remove('hidden');
    }

    function openEditRewardFromBtn(btn) {
        const ds = btn.dataset;
        document.getElementById('modal-title').textContent = 'Edit Reward: ' + ds.name;
        document.getElementById('rew-id').value = ds.id;
        document.getElementById('rew-name').value = ds.name;
        document.getElementById('rew-cat').value = ds.cat || 'Minuman';
        document.getElementById('rew-poin').value = ds.poin;
        document.getElementById('rew-stock').value = ds.stock;
        document.getElementById('rew-desc').value = ds.desc || '';
        document.getElementById('reward-modal').classList.remove('hidden');
    }

    function editReward(r) {
        if (!r) return;
        document.getElementById('modal-title').textContent = 'Edit Reward: ' + r.name;
        document.getElementById('rew-id').value = r.id;
        document.getElementById('rew-name').value = r.name;
        document.getElementById('rew-cat').value = r.category || 'Minuman';
        document.getElementById('rew-poin').value = r.poin_cost;
        document.getElementById('rew-stock').value = r.stock;
        document.getElementById('rew-desc').value = r.description || '';
        document.getElementById('reward-modal').classList.remove('hidden');
    }

    function closeRewardModal() {
        document.getElementById('reward-modal').classList.add('hidden');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeRewardModal();
    });
</script>
@endsection
