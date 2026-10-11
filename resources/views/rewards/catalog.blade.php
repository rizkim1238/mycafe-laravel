@extends('layouts.app')

@section('title', 'Katalog Penukaran Poin - MyCafe POS System')

@section('content')
<!-- Main Content Area -->
<main class="flex-1 flex flex-col min-w-0 min-h-screen lg:h-screen lg:overflow-y-auto">
    <!-- Topbar -->
    @include('layouts.topbar', [
        'pageTitle' => 'Tukar Poin Reward',
        'pageSubtitle' => 'Tukarkan akumulasi poin setia Anda dengan minuman kopi gratis, pastry, atau voucher diskon.',
        'topbarActions' => [
            ['url' => route('member.profile'), 'label' => 'Kartu Member Saya', 'icon' => 'badge', 'class' => 'px-3.5 py-1.5 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition shadow-subtle active:scale-95']
        ]
    ])

    <div class="p-4 sm:p-6 lg:p-8 space-y-6 flex-1">
        <!-- Member Points Header Banner -->
        <div class="bg-slate-900 rounded-2xl p-6 sm:p-7 text-white shadow-soft-lg border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="max-w-xl">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="text-[10px] font-bold text-amber-400 uppercase tracking-widest bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">Loyalty Rewards</span>
                    <span class="text-xs text-slate-400">• {{ $member->user->name ?? ($member->name ?? 'Member') }}</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight">Katalog Hadiah & Diskon</h2>
                <p class="text-slate-400 text-xs sm:text-sm mt-1 leading-relaxed">
                    Setiap cangkir kopi dan pesanan makanan menambah poin Anda. Tukarkan segera dengan hadiah favorit!
                </p>
            </div>
            <div class="bg-slate-850 border border-slate-750 p-4 sm:p-5 rounded-2xl flex items-center gap-4 shrink-0 shadow-subtle">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center border border-amber-500/20">
                    <span class="material-icons text-2xl">stars</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block">Saldo Poin Anda</span>
                    <div class="text-2xl sm:text-3xl font-black text-white font-mono">
                        {{ number_format($currentPoin) }} <span class="text-xs font-normal text-slate-400">pts</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <!-- Category Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 custom-scrollbar text-xs">
                <button type="button" onclick="filterCat('')" class="rwd-cat active px-4 py-2 rounded-xl font-bold bg-primary text-white transition shrink-0 shadow-subtle" data-cat="">
                    Semua Kategori
                </button>
                @foreach ($categories as $cat)
                <button type="button" onclick="filterCat('{{ $cat }}')" class="rwd-cat px-4 py-2 rounded-xl font-semibold bg-white text-slate-700 border border-slate-200 hover:border-primary transition shrink-0" data-cat="{{ $cat }}">
                    {{ $cat }}
                </button>
                @endforeach
            </div>

            <!-- Search Input -->
            <div class="relative w-full sm:w-64">
                <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                <input type="text" id="reward-search" oninput="filterRewards()" placeholder="Cari nama reward..." class="w-full pl-9 pr-3 py-2 bg-white border border-slate-200 text-slate-900 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500"/>
            </div>
        </div>

        <!-- Rewards Grid -->
        <div id="rewards-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @if ($rewards->isEmpty())
            <div class="col-span-full py-16 text-center text-slate-400">
                <span class="material-icons text-5xl text-slate-300 mb-2">card_giftcard</span>
                <p class="font-bold text-slate-600">Belum ada reward aktif saat ini.</p>
                <p class="text-xs text-slate-400 mt-1">Silakan cek kembali di lain waktu.</p>
            </div>
            @else
                @foreach ($rewards as $r)
                @php 
                    $canRedeem = ($currentPoin >= $r->poin_cost);
                    $stockAvail = ($r->stock === -1 || $r->stock > 0);
                @endphp
                <div class="reward-card bg-white rounded-2xl border border-slate-200 shadow-subtle overflow-hidden flex flex-col justify-between hover:border-amber-500/50 transition group"
                     data-name="{{ strtolower($r->name) }}"
                     data-category="{{ $r->category ?? '' }}">
                    <div>
                        <!-- Card Header & Badge -->
                        <div class="p-5 pb-3">
                            <div class="flex justify-between items-start gap-2 mb-3">
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-slate-100 text-slate-700">
                                    {{ $r->category ?: 'Umum' }}
                                </span>
                                <div class="flex items-center gap-1 font-black text-xs text-amber-700 font-mono bg-amber-500/10 px-2 py-0.5 rounded-lg border border-amber-500/20">
                                    <span class="material-icons text-amber-500 text-sm">stars</span>
                                    <span>{{ number_format($r->poin_cost) }} Pts</span>
                                </div>
                            </div>
                            <h3 class="font-bold text-sm text-slate-900 group-hover:text-primary transition leading-snug">
                                {{ $r->name }}
                            </h3>
                            <p class="text-xs text-slate-500 mt-1.5 leading-relaxed line-clamp-2">
                                {{ $r->description ?: 'Tukarkan poin Anda untuk mendapatkan reward spesial ini.' }}
                            </p>
                        </div>
                    </div>

                    <!-- Card Footer / Action Button -->
                    <div class="p-5 pt-3 border-t border-slate-200 mt-auto bg-slate-50/50">
                        <div class="flex items-center justify-between mb-3 text-[11px] text-slate-400">
                            <span>Stok: {!! $r->stock === -1 ? 'Tersedia' : ($r->stock > 0 ? $r->stock . ' item' : '<strong class="text-rose-500">Habis</strong>') !!}</span>
                            @if ($r->valid_until)
                                <span>s/d {{ date('d M Y', strtotime($r->valid_until)) }}</span>
                            @endif
                        </div>

                        @if (!$stockAvail)
                            <button type="button" disabled class="w-full py-2.5 bg-slate-100 text-slate-400 font-bold rounded-xl text-xs cursor-not-allowed">
                                Stok Habis
                            </button>
                        @elseif ($canRedeem)
                            <button type="button" 
                                    data-id="{{ $r->id }}"
                                    data-name="{{ $r->name }}"
                                    data-cost="{{ $r->poin_cost }}"
                                    onclick="openRedeemFromBtn(this)" 
                                    class="w-full py-2.5 bg-primary hover:bg-primary-hover text-white font-bold rounded-xl text-xs transition shadow-subtle flex items-center justify-center gap-1.5 active:scale-[0.98]">
                                <span class="material-icons text-sm">redeem</span>
                                <span>Tukarkan Sekarang</span>
                            </button>
                        @else
                            <button type="button" disabled class="w-full py-2.5 bg-slate-100 text-slate-400 font-semibold rounded-xl text-xs cursor-not-allowed flex items-center justify-center gap-1">
                                <span class="material-icons text-sm">lock</span>
                                <span>Kurang {{ number_format($r->poin_cost - $currentPoin) }} Pts</span>
                            </button>
                        @endif
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>
</main>

<!-- Confirmation Modal -->
<div id="redeem-modal" onclick="if(event.target===this) closeRedeemModal()" class="hidden fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-soft-lg border border-slate-200 relative text-center animate-toast text-slate-900">
        <button type="button" onclick="closeRedeemModal()" class="absolute right-4 top-4 p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition cursor-pointer" title="Tutup">
            <span class="material-icons text-xl">close</span>
        </button>
        <div class="w-14 h-14 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center mx-auto mb-3 border border-amber-500/20">
            <span class="material-icons text-3xl">redeem</span>
        </div>
        <h3 class="font-extrabold text-base text-slate-900" id="modal-rwd-name">Konfirmasi Penukaran</h3>
        <p class="text-xs text-slate-500 mt-1 mb-4">Apakah Anda yakin ingin menukarkan poin untuk reward ini?</p>

        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs space-y-2 mb-5 font-mono">
            <div class="flex justify-between">
                <span class="text-slate-500">Biaya Poin:</span>
                <span class="font-bold text-rose-500" id="modal-rwd-cost">- 0 Poin</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Saldo Poin Anda:</span>
                <span class="font-bold text-slate-900">{{ number_format($currentPoin) }} Poin</span>
            </div>
            <div class="flex justify-between pt-2 border-t border-slate-200">
                <span class="text-slate-500">Sisa Poin Nanti:</span>
                <span class="font-bold text-emerald-600" id="modal-rwd-remain">0 Poin</span>
            </div>
        </div>

        <form action="{{ route('rewards.redeem') }}" method="POST">
            @csrf
            <input type="hidden" name="reward_id" id="modal-rwd-id" value="0"/>
            <div class="flex gap-2">
                <button type="button" onclick="closeRedeemModal()" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-bold transition shadow-subtle active:scale-95">
                    Ya, Tukar Sekarang
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let currentFilterCat = '';

    function filterCat(cat) {
        currentFilterCat = cat;
        document.querySelectorAll('.rwd-cat').forEach(b => {
            if (b.getAttribute('data-cat') === cat) {
                b.className = 'rwd-cat active px-4 py-2 rounded-xl font-bold bg-primary text-white transition shrink-0 shadow-subtle';
            } else {
                b.className = 'rwd-cat px-4 py-2 rounded-xl font-semibold bg-white text-slate-700 border border-slate-200 hover:border-primary transition shrink-0';
            }
        });
        applyFilter();
    }

    function filterRewards() {
        applyFilter();
    }

    function applyFilter() {
        const q = document.getElementById('reward-search').value.toLowerCase().trim();
        document.querySelectorAll('.reward-card').forEach(card => {
            const name = card.getAttribute('data-name') || '';
            const cat  = card.getAttribute('data-category') || '';
            const matchCat = (currentFilterCat === '' || cat.toLowerCase() === currentFilterCat.toLowerCase());
            const matchQ   = (q === '' || name.includes(q));
            card.style.display = (matchCat && matchQ) ? 'flex' : 'none';
        });
    }

    const myPoints = {{ $currentPoin }};

    function openRedeemFromBtn(btn) {
        const ds = btn.dataset;
        const cost = parseInt(ds.cost) || 0;
        document.getElementById('modal-rwd-id').value = ds.id;
        document.getElementById('modal-rwd-name').textContent = ds.name;
        document.getElementById('modal-rwd-cost').textContent = '- ' + cost.toLocaleString('id-ID') + ' Poin';
        document.getElementById('modal-rwd-remain').textContent = Math.max(0, myPoints - cost).toLocaleString('id-ID') + ' Poin';
        document.getElementById('redeem-modal').classList.remove('hidden');
    }

    function confirmRedeem(r) {
        if (!r) return;
        document.getElementById('modal-rwd-id').value = r.id;
        document.getElementById('modal-rwd-name').textContent = r.name;
        document.getElementById('modal-rwd-cost').textContent = '- ' + Number(r.poin_cost).toLocaleString('id-ID') + ' Poin';
        document.getElementById('modal-rwd-remain').textContent = (myPoints - r.poin_cost).toLocaleString('id-ID') + ' Poin';
        document.getElementById('redeem-modal').classList.remove('hidden');
    }

    function closeRedeemModal() {
        document.getElementById('redeem-modal').classList.add('hidden');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeRedeemModal();
    });
</script>
@endsection
