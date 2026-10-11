@extends('layouts.app')

@section('title', 'Detail Member: ' . ($member->user->name ?? ($member->name ?? 'Pelanggan')) . ' - MyCafe')

@php
    $memberData = $member;
    $tier = strtolower($memberData->tier ?? 'bronze');
    $tierGradients = [
        'platinum' => 'from-purple-800 via-indigo-900 to-slate-950',
        'gold'     => 'from-amber-600 via-yellow-700 to-stone-950',
        'silver'   => 'from-slate-700 via-slate-800 to-slate-950',
        'bronze'   => 'from-amber-800 via-amber-900 to-stone-950',
    ];
@endphp

@section('content')
<!-- Main Content -->
<main class="flex-1 flex flex-col min-w-0 min-h-screen lg:h-screen lg:overflow-y-auto">
    <!-- Topbar -->
    @include('layouts.topbar', [
        'pageTitle' => 'Detail Profil: ' . ($memberData->user->name ?? ($memberData->name ?? 'Pelanggan')),
        'pageSubtitle' => 'Audit data pelanggan, riwayat belanja kasir, dan perolehan poin loyalitas.',
        'topbarActions' => [
            ['url' => route('pos.index'), 'label' => 'Buka Kasir POS', 'icon' => 'point_of_sale', 'class' => 'px-3.5 py-1.5 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition shadow-subtle active:scale-95'],
            ['url' => route('members.index'), 'label' => 'Kembali ke Daftar', 'icon' => 'arrow_back', 'class' => 'px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-subtle']
        ]
    ])

    <div class="p-4 sm:p-6 lg:p-8 space-y-6 flex-1">
        @if (!$memberData)
        <div class="p-12 text-center bg-white rounded-2xl border border-slate-200">
            <span class="material-icons text-4xl text-slate-400 mb-2">person_off</span>
            <p class="font-bold text-slate-800">Data Member Tidak Ditemukan</p>
            <a href="{{ route('members.index') }}" class="mt-4 inline-block px-4 py-2 bg-primary text-white text-xs font-bold rounded-xl shadow-subtle">Kembali ke Daftar</a>
        </div>
        @else
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: Member Card & Stats (5 cols) -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Digital Card -->
                <div class="bg-gradient-to-br {{ $tierGradients[$tier] ?? 'from-amber-800 to-stone-950' }} text-white rounded-3xl p-6 sm:p-7 shadow-soft-lg relative overflow-hidden border border-white/10 ring-1 ring-white/10">
                    <div class="flex flex-col justify-between h-56 relative z-10">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-xl bg-white/15 text-white flex items-center justify-center border border-white/20">
                                        <span class="material-icons text-base">local_cafe</span>
                                    </div>
                                    <span class="font-extrabold text-xs tracking-wider uppercase text-white/80">MyCafe Club</span>
                                </div>
                                <h2 class="text-xl font-black mt-3 text-white">
                                    {{ $memberData->user->name ?? ($memberData->name ?? '-') }}
                                </h2>
                                <span class="inline-block mt-1 text-[10px] font-black uppercase tracking-widest px-2.5 py-0.5 rounded-full bg-white/20 text-white border border-white/30 font-mono">
                                    {{ strtoupper($memberData->tier) }} TIER
                                </span>
                            </div>
                            <button type="button" onclick="openQrModal()" class="bg-white hover:bg-amber-50 p-2.5 rounded-2xl text-slate-900 shadow-soft hover:shadow-soft-lg hover:scale-105 active:scale-95 transition-all cursor-pointer group text-left" title="Klik untuk tampilkan QR Code & Barcode">
                                <span class="material-icons text-3xl block text-slate-900 group-hover:text-primary transition-colors">qr_code_2</span>
                                <span class="text-[9px] font-black text-slate-500 block text-center uppercase tracking-tighter mt-0.5">SCAN</span>
                            </button>
                        </div>

                        <div class="pt-4 border-t border-white/20 flex justify-between items-end text-xs font-mono">
                            <div>
                                <span class="text-white/60 text-[9px] block uppercase">Kode Member</span>
                                <button type="button" onclick="copyCode('{{ $memberData->member_code }}')" class="text-base font-bold text-white hover:text-amber-300 transition flex items-center gap-1 group text-left cursor-pointer" title="Klik untuk salin">
                                    <span>{{ $memberData->member_code }}</span>
                                    <span class="material-icons text-xs opacity-60 group-hover:opacity-100 transition-opacity">content_copy</span>
                                </button>
                            </div>
                            <div class="text-right">
                                <span class="text-white/60 text-[9px] block uppercase">Bergabung</span>
                                <span class="text-white/90 font-bold">{{ !empty($memberData->join_date) ? date('d M Y', strtotime($memberData->join_date)) : '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Details Card -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-subtle space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Informasi Kontak & Akun</h3>
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between pb-2 border-b border-slate-200">
                            <span class="text-slate-500">Nomor Telepon:</span>
                            <strong class="font-mono text-slate-900">{{ $memberData->user->phone ?? ($memberData->phone ?: '-') }}</strong>
                        </div>
                        <div class="flex justify-between pb-2 border-b border-slate-200">
                            <span class="text-slate-500">Email:</span>
                            <strong class="text-slate-900">{{ $memberData->user->email ?? ($memberData->email ?: '-') }}</strong>
                        </div>
                        <div class="flex justify-between pb-2 border-b border-slate-200">
                            <span class="text-slate-500">Status Akun:</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold {{ ($memberData->user->is_active ?? $memberData->is_active) ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20' : 'bg-slate-500/10 text-slate-500 border border-slate-500/20' }}">
                                {{ ($memberData->user->is_active ?? $memberData->is_active) ? 'Aktif' : 'Non-aktif' }}
                            </span>
                        </div>
                        <div class="flex justify-between pb-2 border-b border-slate-200">
                            <span class="text-slate-500">Saldo Poin:</span>
                            <strong class="font-mono text-amber-600 font-bold">{{ number_format($memberData->total_poin) }} pts</strong>
                        </div>
                        <div class="flex justify-between pb-2 border-b border-slate-200">
                            <span class="text-slate-500">Total Kunjungan:</span>
                            <strong class="font-mono text-slate-900">{{ number_format($memberData->total_transaksi) }} kali</strong>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Total Belanja:</span>
                            <strong class="font-mono text-slate-900">{{ formatRupiah($memberData->total_belanja) }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Transactions & Points History (7 cols) -->
            <div class="lg:col-span-7 space-y-6">
                <!-- Transactions List -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-subtle overflow-hidden">
                    <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span class="material-icons text-primary text-lg">receipt_long</span>
                            <span>Riwayat Transaksi Belanja</span>
                        </h3>
                        <span class="text-xs text-slate-400 font-mono">{{ count($trxHistory) }} transaksi</span>
                    </div>

                    <div class="divide-y divide-slate-200 text-xs">
                        @if ($trxHistory->isEmpty())
                            <p class="p-8 text-center text-slate-400">Belum ada transaksi tercatat untuk member ini.</p>
                        @else
                            @foreach ($trxHistory as $tx)
                            <div class="p-4 flex items-center justify-between hover:bg-slate-50/80 transition">
                                <div>
                                    <p class="font-bold text-slate-900 font-mono">{{ $tx->invoice_no }}</p>
                                    <p class="text-[10px] text-slate-400 font-mono">{{ date('d M Y, H:i', strtotime($tx->created_at)) }} &bull; Kasir: {{ $tx->cashier_name }}</p>
                                </div>
                                <div class="text-right font-mono">
                                    <p class="font-bold text-slate-900">{{ formatRupiah($tx->total) }}</p>
                                    <p class="text-[10px] text-emerald-600">+{{ $tx->poin_earned }} pts</p>
                                </div>
                            </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- Points History -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-subtle overflow-hidden">
                    <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span class="material-icons text-primary text-lg">toll</span>
                            <span>Riwayat Mutasi Poin</span>
                        </h3>
                        <span class="text-xs text-slate-400 font-mono">{{ count($poinHistory) }} mutasi</span>
                    </div>

                    <div class="divide-y divide-slate-200 text-xs">
                        @if ($poinHistory->isEmpty())
                            <p class="p-8 text-center text-slate-400">Belum ada mutasi poin tercatat.</p>
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
        @endif
    </div>
</main>

<!-- Digital QR Code & Barcode Modal -->
<div id="qr-modal" class="hidden fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto" onclick="if(event.target===this) closeQrModal()">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 sm:p-7 shadow-2xl border border-slate-200 relative text-center animate-toast text-slate-900">
        <button type="button" onclick="closeQrModal()" class="absolute right-4 top-4 p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition" title="Tutup">
            <span class="material-icons text-xl">close</span>
        </button>

        <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mx-auto mb-3 border border-primary/20">
            <span class="material-icons text-2xl">qr_code_scanner</span>
        </div>

        <h3 class="font-black text-lg text-slate-900">QR & Barcode Member</h3>
        <p class="text-xs text-slate-500 mt-0.5">Tunjukkan ke scanner kasir saat bertransaksi.</p>

        <div class="my-5 p-5 bg-slate-50 border border-slate-200 rounded-2xl inline-block shadow-subtle">
            <div class="w-44 h-44 mx-auto bg-white p-3 rounded-xl border border-slate-200 shadow-xs flex flex-col items-center justify-center">
                <svg viewBox="0 0 100 100" class="w-full h-full" fill="currentColor">
                    <rect x="5" y="5" width="26" height="26" rx="4" fill="#0f172a"/>
                    <rect x="9" y="9" width="18" height="18" fill="#ffffff"/>
                    <rect x="13" y="13" width="10" height="10" rx="2" fill="#944b1c"/>
                    <rect x="69" y="5" width="26" height="26" rx="4" fill="#0f172a"/>
                    <rect x="73" y="9" width="18" height="18" fill="#ffffff"/>
                    <rect x="77" y="13" width="10" height="10" rx="2" fill="#944b1c"/>
                    <rect x="5" y="69" width="26" height="26" rx="4" fill="#0f172a"/>
                    <rect x="9" y="73" width="18" height="18" fill="#ffffff"/>
                    <rect x="13" y="77" width="10" height="10" rx="2" fill="#944b1c"/>
                    <rect x="36" y="8" width="8" height="8" fill="#0f172a"/>
                    <rect x="48" y="12" width="6" height="6" fill="#0f172a"/>
                    <rect x="36" y="40" width="12" height="12" rx="2" fill="#944b1c"/>
                    <rect x="54" y="38" width="8" height="8" fill="#0f172a"/>
                    <rect x="68" y="44" width="6" height="6" fill="#0f172a"/>
                    <rect x="80" y="38" width="8" height="8" fill="#0f172a"/>
                    <rect x="36" y="72" width="8" height="8" fill="#0f172a"/>
                    <rect x="60" y="72" width="8" height="8" fill="#0f172a"/>
                </svg>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-200">
                <div class="h-6 flex items-center justify-center gap-0.5 max-w-[160px] mx-auto overflow-hidden">
                    <span class="w-1 h-full bg-slate-900"></span>
                    <span class="w-0.5 h-full bg-slate-900"></span>
                    <span class="w-1.5 h-full bg-slate-900"></span>
                    <span class="w-2 h-full bg-slate-900"></span>
                    <span class="w-0.5 h-full bg-slate-900"></span>
                    <span class="w-1.5 h-full bg-slate-900"></span>
                    <span class="w-1 h-full bg-slate-900"></span>
                    <span class="w-2 h-full bg-slate-900"></span>
                </div>
            </div>
        </div>

        <div class="bg-slate-100 p-3 rounded-2xl flex items-center justify-between mb-4 border border-slate-200">
            <div class="text-left font-mono">
                <span class="text-[9px] uppercase font-bold text-slate-400 block">Kode Member</span>
                <span class="text-sm font-extrabold text-slate-900 tracking-wider">{{ $memberData->member_code }}</span>
            </div>
            <button type="button" onclick="copyCode('{{ $memberData->member_code }}')" 
                    class="px-3 py-1.5 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 transition flex items-center gap-1 shadow-subtle active:scale-95">
                <span class="material-icons text-xs">content_copy</span>
                <span>Salin</span>
            </button>
        </div>

        <button type="button" onclick="closeQrModal()" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition shadow-subtle">
            Tutup
        </button>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function copyCode(code) {
        if (!code) return;
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(code).then(() => {
                showToast('Kode ' + code + ' disalin ke clipboard!', 'success');
            }).catch(() => fallbackCopy(code));
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
            showToast('Kode ' + text + ' disalin ke clipboard!', 'success');
        } catch (err) {
            showToast('Gagal menyalin: ' + text, 'error');
        }
        document.body.removeChild(textArea);
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
