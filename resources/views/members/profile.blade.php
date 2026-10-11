@extends('layouts.app')

@section('title', 'Kartu Member & Poin - MyCafe')

@php
    $tier = strtolower($member->tier ?? 'bronze');
    $tierThresholds = [
        'bronze'   => ['next' => 'Silver',   'target' => 500,  'badge' => 'BRONZE', 'grad' => 'from-amber-800 via-amber-900 to-stone-950', 'text' => 'text-amber-400'],
        'silver'   => ['next' => 'Gold',     'target' => 1500, 'badge' => 'SILVER', 'grad' => 'from-slate-700 via-slate-800 to-slate-950', 'text' => 'text-slate-300'],
        'gold'     => ['next' => 'Platinum', 'target' => 3000, 'badge' => 'GOLD',   'grad' => 'from-amber-600 via-yellow-700 to-stone-950', 'text' => 'text-yellow-400'],
        'platinum' => ['next' => 'Maksimal', 'target' => 5000, 'badge' => 'PLATINUM', 'grad' => 'from-purple-800 via-indigo-900 to-slate-950', 'text' => 'text-purple-300'],
    ];
    $target = $tierThresholds[$tier]['target'] ?? 1000;
    $nextTier = $tierThresholds[$tier]['next'] ?? 'Silver';
    $curPoints = (int)($member->total_poin ?? 0);
    $progressPct = min(100, max(5, round(($curPoints / $target) * 100)));
    $memberCode = $member->member_code ?? 'MBR-0000';
    $memberName = $member->user->name ?? ($member->name ?? 'Member MyCafe');
@endphp

@section('content')
<!-- Main Content Area -->
<main class="flex-1 flex flex-col min-w-0 min-h-screen lg:h-screen lg:overflow-y-auto">
    <!-- Topbar -->
    @include('layouts.topbar', [
        'pageTitle' => 'Kartu Member & Poin Loyalitas',
        'pageSubtitle' => 'Pantau saldo poin, status tier membership, dan tukarkan hadiah spesial MyCafe.',
        'topbarActions' => [
            ['url' => route('rewards.catalog'), 'label' => 'Tukar Poin Hadiah', 'icon' => 'redeem', 'class' => 'px-3.5 py-1.5 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition shadow-subtle active:scale-95']
        ]
    ])

    <div class="p-4 sm:p-6 lg:p-8 space-y-6 flex-1">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: Digital VIP Card & Tier Progress (5 cols) -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Digital Membership Card -->
                <div class="bg-gradient-to-br {{ $tierThresholds[$tier]['grad'] }} text-white rounded-3xl p-6 sm:p-7 shadow-soft-lg relative overflow-hidden border border-white/10 ring-1 ring-white/10">
                    <!-- Card Ambient Gloss -->
                    <div class="absolute -right-16 -top-16 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

                    <div class="flex flex-col justify-between h-56 relative z-10">
                        <!-- Card Header -->
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-xl bg-white/15 text-white flex items-center justify-center border border-white/20">
                                        <span class="material-icons text-base">local_cafe</span>
                                    </div>
                                    <span class="font-extrabold text-xs tracking-wider uppercase text-white/80">MyCafe Member</span>
                                </div>
                                <h2 class="text-xl font-black mt-3 text-white flex items-center gap-2">
                                    <span>{{ $memberName }}</span>
                                </h2>
                                <span class="inline-block mt-1 text-[10px] font-black uppercase tracking-widest px-2.5 py-0.5 rounded-full bg-white/20 text-white border border-white/30 font-mono">
                                    {{ $tierThresholds[$tier]['badge'] }} TIER
                                </span>
                            </div>

                            <!-- Clickable QR Button to Enlarge -->
                            <button type="button" onclick="openQrModal()" 
                                    class="bg-white hover:bg-amber-50 p-2.5 rounded-2xl text-slate-900 shadow-soft hover:shadow-soft-lg hover:scale-105 active:scale-95 transition-all cursor-pointer group text-left" 
                                    title="Klik untuk tampilkan QR Code & Barcode kasir">
                                <span class="material-icons text-3xl block text-slate-900 group-hover:text-primary transition-colors">qr_code_2</span>
                                <span class="text-[9px] font-black text-slate-500 block text-center uppercase tracking-tighter mt-0.5">SCAN</span>
                            </button>
                        </div>

                        <!-- Card Footer -->
                        <div class="pt-4 border-t border-white/20 flex justify-between items-end text-xs font-mono">
                            <div>
                                <span class="text-white/60 text-[9px] block uppercase">Kode Member (Klik Salin)</span>
                                <button type="button" onclick="copyMemberCode('{{ $memberCode }}')" 
                                        class="text-base font-bold text-white hover:text-amber-300 transition flex items-center gap-1.5 group text-left cursor-pointer active:scale-95" 
                                        title="Klik untuk salin kode member">
                                    <span id="member-code-text">{{ $memberCode }}</span>
                                    <span class="material-icons text-sm opacity-70 group-hover:opacity-100 transition-opacity" id="copy-btn-icon">content_copy</span>
                                </button>
                            </div>
                            <div class="text-right">
                                <span class="text-white/60 text-[9px] block uppercase">Bergabung Sejak</span>
                                <span class="text-white/90 font-bold">{{ !empty($member->join_date) ? date('d M Y', strtotime($member->join_date)) : '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Points Balance & Progress Card -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-subtle space-y-5">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Saldo Poin Aktif</p>
                            <h3 class="text-3xl font-black text-slate-900 mt-1 font-mono">
                                {{ number_format($curPoints) }} <span class="text-xs font-normal text-slate-400">Poin</span>
                            </h3>
                        </div>
                        <a href="{{ route('rewards.catalog') }}" class="w-12 h-12 rounded-2xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-500 flex items-center justify-center border border-amber-500/20 transition group" title="Tukar Poin Sekarang">
                            <span class="material-icons text-2xl group-hover:scale-110 transition-transform">toll</span>
                        </a>
                    </div>

                    <!-- Progress Bar to Next Tier -->
                    <div class="space-y-2">
                        <div class="flex justify-between text-xs font-semibold">
                            <span class="text-slate-600">Target Menuju {{ $nextTier }}</span>
                            <span class="text-primary font-bold">{{ max(0, $target - $curPoints) }} poin lagi</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                            <div class="bg-primary h-full rounded-full transition-all duration-500" style="width: {{ $progressPct }}%"></div>
                        </div>
                        <div class="flex justify-between text-[10px] text-slate-400 uppercase font-mono">
                            <span>{{ strtoupper($tier) }}</span>
                            <span>{{ $nextTier }} ({{ number_format($target) }} Pts)</span>
                        </div>
                    </div>

                    <!-- Interactive Quick Stats Cards -->
                    <div class="pt-4 border-t border-slate-200 grid grid-cols-2 gap-3 text-center">
                        <a href="{{ route('transactions.index') }}" class="p-3 bg-slate-50 hover:bg-amber-50/70 rounded-xl border border-slate-200 hover:border-amber-300 transition block group shadow-subtle" title="Klik untuk lihat riwayat kunjungan">
                            <span class="text-[10px] text-slate-500 group-hover:text-primary font-bold uppercase tracking-wider flex items-center justify-center gap-1 transition">
                                <span>Total Kunjungan</span>
                                <span class="material-icons text-[11px] group-hover:translate-x-0.5 transition-transform">arrow_forward</span>
                            </span>
                            <p class="text-base font-bold text-slate-900 group-hover:text-primary mt-0.5 font-mono transition">{{ number_format($member->total_transaksi ?? 0) }}x</p>
                        </a>
                        <a href="{{ route('transactions.index') }}" class="p-3 bg-slate-50 hover:bg-amber-50/70 rounded-xl border border-slate-200 hover:border-amber-300 transition block group shadow-subtle" title="Klik untuk lihat riwayat belanja">
                            <span class="text-[10px] text-slate-500 group-hover:text-primary font-bold uppercase tracking-wider flex items-center justify-center gap-1 transition">
                                <span>Total Belanja</span>
                                <span class="material-icons text-[11px] group-hover:translate-x-0.5 transition-transform">arrow_forward</span>
                            </span>
                            <p class="text-base font-bold text-slate-900 group-hover:text-primary mt-0.5 font-mono transition">{{ formatRupiah($member->total_belanja ?? 0) }}</p>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right: Rewards & Activity Ledger (7 cols) -->
            <div class="lg:col-span-7 space-y-6">
                <!-- Rewards Catalog Preview -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-subtle">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">card_giftcard</span>
                                <span>Reward Hadiah Pilihan</span>
                            </h3>
                            <p class="text-xs text-slate-400">Tukarkan poin Anda di kasir POS untuk menu favorit ini.</p>
                        </div>
                        <a href="{{ route('rewards.catalog') }}" class="text-primary text-xs font-bold hover:underline flex items-center gap-1">
                            <span>Katalog Lengkap</span>
                            <span class="material-icons text-sm">arrow_forward</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        @if ($rewards->isEmpty())
                            <p class="text-xs text-slate-400 col-span-2 text-center py-6">Belum ada reward aktif.</p>
                        @else
                            @foreach ($rewards as $r)
                            <a href="{{ route('rewards.catalog') }}" class="p-4 rounded-xl bg-slate-50 hover:bg-amber-50/50 border border-slate-200 hover:border-primary/40 transition flex flex-col justify-between gap-3 group shadow-subtle hover:shadow-soft">
                                <div>
                                    <div class="flex justify-between items-start gap-1 mb-1">
                                        <h4 class="font-bold text-xs text-slate-900 group-hover:text-primary transition-colors">{{ $r->name }}</h4>
                                        <span class="text-[10px] font-bold text-amber-700 bg-amber-500/10 px-2 py-0.5 rounded font-mono border border-amber-500/20 whitespace-nowrap">
                                            {{ number_format($r->poin_cost) }} Pts
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 line-clamp-2">{{ $r->description ?: 'Reward spesial MyCafe' }}</p>
                                </div>
                                <div class="text-[11px] font-bold text-primary group-hover:underline flex items-center justify-end gap-1">
                                    <span>Tukar Hadiah</span>
                                    <span class="material-icons text-xs group-hover:translate-x-0.5 transition-transform">arrow_forward</span>
                                </div>
                            </a>
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- Points History Ledger -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-subtle overflow-hidden">
                    <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span class="material-icons text-primary text-lg">history</span>
                            <span>Riwayat Transaksi Poin Terakhir</span>
                        </h3>
                        <a href="{{ route('transactions.index') }}" class="text-xs text-primary font-bold hover:underline flex items-center gap-0.5">
                            <span>Riwayat Belanja</span>
                            <span class="material-icons text-xs">arrow_forward</span>
                        </a>
                    </div>

                    <div class="divide-y divide-slate-200 text-xs">
                        @if ($poinHistory->isEmpty())
                            <p class="p-8 text-center text-slate-400">Belum ada mutasi poin.</p>
                        @else
                            @foreach ($poinHistory as $ph)
                            <div class="p-4 flex items-center justify-between hover:bg-slate-50/80 transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl {{ $ph->poin > 0 ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-600 border border-rose-500/20' }} flex items-center justify-center shrink-0">
                                        <span class="material-icons text-sm">{{ $ph->poin > 0 ? 'add' : 'remove' }}</span>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900">{{ $ph->description ?? 'Aktivitas poin' }}</p>
                                        <p class="text-[10px] text-slate-400 font-mono">{{ date('d M Y, H:i', strtotime($ph->created_at)) }}</p>
                                    </div>
                                </div>
                                <div class="text-right font-mono">
                                    <p class="font-bold {{ $ph->poin > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $ph->poin > 0 ? '+' : '' }}{{ $ph->poin }} pts
                                    </p>
                                    <p class="text-[10px] text-slate-400">Saldo: {{ number_format($ph->balance_after) }}</p>
                                </div>
                            </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Digital QR Code & Barcode Modal -->
<div id="qr-modal" class="hidden fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto" onclick="if(event.target===this) closeQrModal()">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 sm:p-7 shadow-2xl border border-slate-200 relative text-center animate-toast text-slate-900">
        <!-- Close Button -->
        <button type="button" onclick="closeQrModal()" class="absolute right-4 top-4 p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition" title="Tutup">
            <span class="material-icons text-xl">close</span>
        </button>

        <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mx-auto mb-3 border border-primary/20">
            <span class="material-icons text-2xl">qr_code_scanner</span>
        </div>

        <h3 class="font-black text-lg text-slate-900">QR & Barcode Member</h3>
        <p class="text-xs text-slate-500 mt-0.5">Tunjukkan ke kasir MyCafe saat transaksi untuk dapatkan poin loyalitas.</p>

        <!-- Large QR Visual -->
        <div class="my-5 p-5 bg-slate-50 border border-slate-200 rounded-2xl inline-block shadow-subtle">
            <!-- Dynamic SVG QR Code Representation -->
            <div class="w-44 h-44 mx-auto bg-white p-3 rounded-xl border border-slate-200 shadow-xs flex flex-col items-center justify-center relative">
                <svg viewBox="0 0 100 100" class="w-full h-full" fill="currentColor">
                    <!-- Corner Markers -->
                    <rect x="5" y="5" width="26" height="26" rx="4" fill="#0f172a"/>
                    <rect x="9" y="9" width="18" height="18" fill="#ffffff"/>
                    <rect x="13" y="13" width="10" height="10" rx="2" fill="#944b1c"/>

                    <rect x="69" y="5" width="26" height="26" rx="4" fill="#0f172a"/>
                    <rect x="73" y="9" width="18" height="18" fill="#ffffff"/>
                    <rect x="77" y="13" width="10" height="10" rx="2" fill="#944b1c"/>

                    <rect x="5" y="69" width="26" height="26" rx="4" fill="#0f172a"/>
                    <rect x="9" y="73" width="18" height="18" fill="#ffffff"/>
                    <rect x="13" y="77" width="10" height="10" rx="2" fill="#944b1c"/>

                    <!-- Data Dots Pattern -->
                    <rect x="36" y="8" width="8" height="8" fill="#0f172a"/>
                    <rect x="48" y="12" width="6" height="6" fill="#0f172a"/>
                    <rect x="36" y="24" width="6" height="6" fill="#0f172a"/>
                    <rect x="46" y="22" width="8" height="8" fill="#0f172a"/>

                    <rect x="12" y="38" width="6" height="6" fill="#0f172a"/>
                    <rect x="22" y="44" width="8" height="8" fill="#0f172a"/>
                    <rect x="36" y="40" width="12" height="12" rx="2" fill="#944b1c"/>
                    <rect x="54" y="38" width="8" height="8" fill="#0f172a"/>
                    <rect x="68" y="44" width="6" height="6" fill="#0f172a"/>
                    <rect x="80" y="38" width="8" height="8" fill="#0f172a"/>

                    <rect x="40" y="58" width="6" height="6" fill="#0f172a"/>
                    <rect x="52" y="54" width="8" height="8" fill="#0f172a"/>
                    <rect x="66" y="60" width="8" height="8" fill="#0f172a"/>
                    <rect x="82" y="54" width="6" height="6" fill="#0f172a"/>

                    <rect x="36" y="72" width="8" height="8" fill="#0f172a"/>
                    <rect x="48" y="76" width="6" height="6" fill="#0f172a"/>
                    <rect x="60" y="72" width="8" height="8" fill="#0f172a"/>
                    <rect x="74" y="78" width="6" height="6" fill="#0f172a"/>
                    <rect x="84" y="70" width="8" height="8" fill="#0f172a"/>
                </svg>
            </div>
            <!-- Barcode Pattern Below -->
            <div class="mt-3 pt-3 border-t border-slate-200">
                <div class="h-6 flex items-center justify-center gap-0.5 max-w-[160px] mx-auto overflow-hidden">
                    <span class="w-1 h-full bg-slate-900"></span>
                    <span class="w-0.5 h-full bg-slate-900"></span>
                    <span class="w-1.5 h-full bg-slate-900"></span>
                    <span class="w-0.5 h-full bg-slate-900"></span>
                    <span class="w-2 h-full bg-slate-900"></span>
                    <span class="w-0.5 h-full bg-slate-900"></span>
                    <span class="w-1 h-full bg-slate-900"></span>
                    <span class="w-1.5 h-full bg-slate-900"></span>
                    <span class="w-0.5 h-full bg-slate-900"></span>
                    <span class="w-2 h-full bg-slate-900"></span>
                    <span class="w-1 h-full bg-slate-900"></span>
                    <span class="w-0.5 h-full bg-slate-900"></span>
                    <span class="w-1.5 h-full bg-slate-900"></span>
                    <span class="w-0.5 h-full bg-slate-900"></span>
                    <span class="w-2 h-full bg-slate-900"></span>
                </div>
            </div>
        </div>

        <!-- Code Pill with Quick Copy -->
        <div class="bg-slate-100 p-3 rounded-2xl flex items-center justify-between mb-4 border border-slate-200">
            <div class="text-left font-mono">
                <span class="text-[9px] uppercase font-bold text-slate-400 block">Kode Member</span>
                <span class="text-sm font-extrabold text-slate-900 tracking-wider">{{ $memberCode }}</span>
            </div>
            <button type="button" onclick="copyMemberCode('{{ $memberCode }}')" 
                    class="px-3 py-1.5 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 transition flex items-center gap-1 shadow-subtle active:scale-95">
                <span class="material-icons text-xs">content_copy</span>
                <span>Salin</span>
            </button>
        </div>

        <div class="flex gap-2">
            <button type="button" onclick="closeQrModal()" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition shadow-subtle">
                Selesai / Tutup
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function copyMemberCode(code) {
        if (!code) return;
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(code).then(() => {
                onCopySuccess(code);
            }).catch(() => {
                fallbackCopy(code);
            });
        } else {
            fallbackCopy(code);
        }
    }

    function fallbackCopy(text) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.left = "-9999px";
        textArea.style.top = "-9999px";
        textArea.style.opacity = "0";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            onCopySuccess(text);
        } catch (err) {
            showToast('Gagal menyalin otomatis. Silakan salin manual: ' + text, 'error');
        }
        document.body.removeChild(textArea);
    }

    function onCopySuccess(code) {
        showToast('Kode ' + code + ' berhasil disalin ke clipboard!', 'success');
        const icon = document.getElementById('copy-btn-icon');
        if (icon) {
            icon.textContent = 'check';
            icon.classList.add('text-emerald-400');
            setTimeout(() => {
                icon.textContent = 'content_copy';
                icon.classList.remove('text-emerald-400');
            }, 1800);
        }
    }

    function openQrModal() {
        const modal = document.getElementById('qr-modal');
        if (modal) modal.classList.remove('hidden');
    }

    function closeQrModal() {
        const modal = document.getElementById('qr-modal');
        if (modal) modal.classList.add('hidden');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeQrModal();
    });
</script>
@endsection
