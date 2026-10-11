@extends('layouts.app')

@section('title', 'Riwayat Transaksi - MyCafe POS System')

@section('styles')
<style>
    @media print {
        aside, header, nav, #filter-box, #stats-bar, .no-print, #global-topbar { display: none !important; }
        #trx-modal { position: static !important; background: transparent !important; padding: 0 !important; }
        #trx-modal-content { border: none !important; box-shadow: none !important; max-width: 320px !important; margin: 0 auto !important; padding: 0 !important; color: black !important; }
        body { background: white !important; color: black !important; }
    }
</style>
@endsection

@section('content')
@php
    $cUser = auth()->user();
    $isMember = ($cUser->role ?? '') === 'member';
    $actions = [
        ['url' => route('transactions.index', array_merge(request()->query(), ['export' => 'csv'])), 'label' => 'Unduh CSV', 'icon' => 'download', 'class' => 'px-3 py-1.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition shadow-subtle'],
    ];
    if (!$isMember) {
        $actions[] = ['url' => route('pos.index'), 'label' => 'Buka Kasir POS', 'icon' => 'point_of_sale', 'class' => 'px-3.5 py-1.5 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition shadow-subtle active:scale-95'];
    } else {
        $actions[] = ['url' => route('rewards.catalog'), 'label' => 'Tukar Poin Reward', 'icon' => 'redeem', 'class' => 'px-3.5 py-1.5 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition shadow-subtle active:scale-95'];
    }
@endphp
<main class="flex-1 flex flex-col min-w-0 min-h-screen lg:h-screen lg:overflow-y-auto">
    <!-- Topbar -->
    @include('layouts.topbar', [
        'pageTitle' => $isMember ? 'Riwayat Belanja Saya' : 'Riwayat Transaksi',
        'pageSubtitle' => $isMember ? 'Daftar riwayat pembelian, perolehan poin loyalitas, dan struk belanja Anda.' : 'Audit log transaksi pemesanan kasir, histori pembayaran, dan perolehan poin.',
        'topbarActions' => $actions
    ])

    <div class="p-4 sm:p-6 lg:p-8 space-y-6 flex-1">
        <!-- Stats Bar (Clickable Filters) -->
        <div id="stats-bar" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('transactions.index') }}" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-subtle card-hover block group">
                <span class="text-xs text-slate-500 font-bold uppercase tracking-wider block">Total Transaksi</span>
                <p class="text-2xl font-black text-slate-900 mt-1 font-mono">{{ number_format($totalRows) }}</p>
                <span class="text-[11px] text-primary font-semibold mt-1 inline-block">Semua Transaksi &rarr;</span>
            </a>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-subtle">
                <span class="text-xs text-emerald-600 font-bold uppercase tracking-wider block">{{ $isMember ? 'Total Belanja Bulan Ini' : 'Omset Bulan Ini' }}</span>
                <p class="text-2xl font-black text-emerald-600 mt-1 font-mono">{{ formatRupiah($pendapatan) }}</p>
                <span class="text-[11px] text-slate-400 mt-1 block">Khusus status selesai</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-subtle">
                <span class="text-xs text-amber-600 font-bold uppercase tracking-wider block">{{ $isMember ? 'Poin Didapat (Bulan Ini)' : 'Poin Diberikan (Bulan Ini)' }}</span>
                <p class="text-2xl font-black text-amber-600 mt-1 font-mono">{{ number_format($totalPoin) }} <span class="text-xs font-normal text-slate-400">pts</span></p>
                <span class="text-[11px] text-slate-400 mt-1 block">{{ $isMember ? 'Akumulasi reward setia' : 'Insentif loyalitas pelanggan' }}</span>
            </div>
        </div>

        <!-- Filter Form -->
        <form id="filter-box" action="{{ route('transactions.index') }}" method="GET" class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-subtle space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                <div>
                    <label class="block font-bold text-slate-600 mb-1">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-primary focus:border-primary"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 mb-1">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-primary focus:border-primary"/>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 mb-1">Status Pembayaran</label>
                    <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-primary focus:border-primary">
                        <option value="all" {{ $status === 'all' || !$status ? 'selected' : '' }}>Semua Status</option>
                        <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed (Selesai)</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                        <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled (Batal)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-600 mb-1">Pencarian Invoice / Nama</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Ketik invoice atau nama member..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-primary focus:border-primary"/>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-subtle">
                    <span class="material-icons text-sm">filter_alt</span>
                    <span>Terapkan Filter</span>
                </button>
                @if ($dateFrom || $dateTo || $status || $search)
                <a href="{{ route('transactions.index') }}" class="text-xs text-slate-500 hover:text-slate-800 px-3 py-2 font-semibold hover:underline">
                    Reset Filter
                </a>
                @endif
            </div>
        </form>

        <!-- Transactions Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-subtle overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 text-slate-400 text-[11px] uppercase font-bold tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3.5">Invoice No</th>
                            <th class="px-5 py-3.5">Nama Member</th>
                            <th class="px-5 py-3.5">Waktu Transaksi</th>
                            <th class="px-5 py-3.5">Poin Loyalitas</th>
                            <th class="px-5 py-3.5">Metode</th>
                            <th class="px-5 py-3.5 text-right">Total</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5 text-center">Aksi & Struk</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @if ($transactions->isEmpty())
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                <span class="material-icons text-4xl text-slate-300 mb-2">receipt</span>
                                <p class="font-bold text-slate-600">Belum ada transaksi ditemukan</p>
                                <p class="text-xs text-slate-400 mt-1">Coba sesuaikan filter pencarian atau tanggal.</p>
                            </td>
                        </tr>
                        @else
                            @foreach ($transactions as $t)
                            <tr class="hover:bg-slate-50/70 transition group">
                                <td class="px-5 py-4 font-mono text-xs font-bold text-slate-800">
                                    <button type="button" 
                                            data-inv="{{ $t->invoice_no }}"
                                            data-member="{{ $t->member_name }}"
                                            data-kasir="{{ $t->kasir_name }}"
                                            data-date="{{ date('d M Y, H:i', strtotime($t->created_at)) }}"
                                            data-subtotal="{{ formatRupiah($t->subtotal) }}"
                                            data-diskon="{{ formatRupiah($t->diskon) }}"
                                            data-total="{{ formatRupiah($t->total) }}"
                                            data-method="{{ strtoupper($t->payment_method) }}"
                                            data-poin="{{ $t->poin_earned }}"
                                            onclick="openTrxFromBtn(this)"
                                            class="text-amber-700 hover:underline flex items-center gap-1 font-bold" title="Lihat Struk">
                                        <span>{{ $t->invoice_no }}</span>
                                    </button>
                                </td>
                                <td class="px-5 py-4">
                                    @if (!empty($t->member_id))
                                        <a href="{{ $isMember ? route('member.profile') : route('members.show', $t->member_id) }}" class="font-bold text-slate-900 hover:text-primary transition block">
                                            {{ $t->member_name }}
                                        </a>
                                    @else
                                        <div class="font-bold text-slate-900">{{ $t->member_name }}</div>
                                    @endif
                                    <div class="text-[10px] text-slate-400">Kasir: {{ $t->kasir_name }}</div>
                                </td>
                                <td class="px-5 py-4 text-xs text-slate-600 font-mono">
                                    {{ date('d M Y, H:i', strtotime($t->created_at)) }}
                                </td>
                                <td class="px-5 py-4 text-xs">
                                    @if ($t->poin_earned > 0)
                                        <span class="text-emerald-600 font-bold font-mono">+{{ $t->poin_earned }} pts</span>
                                    @else
                                        <span class="text-slate-400 font-mono">0 pts</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                        {{ $t->payment_method }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right font-mono font-bold text-slate-900">
                                    {{ formatRupiah($t->total) }}
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $t->status === 'completed' ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20' : ($t->status === 'pending' ? 'bg-amber-500/10 text-amber-600' : 'bg-rose-500/10 text-rose-600') }}">
                                        {{ strtoupper($t->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <button type="button" 
                                            data-inv="{{ $t->invoice_no }}"
                                            data-member="{{ $t->member_name }}"
                                            data-kasir="{{ $t->kasir_name }}"
                                            data-date="{{ date('d M Y, H:i', strtotime($t->created_at)) }}"
                                            data-subtotal="{{ formatRupiah($t->subtotal) }}"
                                            data-diskon="{{ formatRupiah($t->diskon) }}"
                                            data-total="{{ formatRupiah($t->total) }}"
                                            data-method="{{ strtoupper($t->payment_method) }}"
                                            data-poin="{{ $t->poin_earned }}"
                                            onclick="openTrxFromBtn(this)"
                                            class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-amber-500/10 hover:text-amber-600 text-slate-700 transition text-[11px] font-semibold flex items-center justify-center gap-1 mx-auto active:scale-95" title="Lihat Struk">
                                        <span class="material-icons text-sm">receipt</span>
                                        <span>Struk</span>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($totalPages > 1)
            <div class="p-4 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
                <p>Menampilkan {{ count($transactions) }} dari {{ $totalRows }} transaksi</p>
                <div class="flex gap-1">
                    @for ($i = 1; $i <= $totalPages; $i++)
                        @php
                            $pQuery = request()->query();
                            $pQuery['page'] = $i;
                        @endphp
                        <a href="{{ route('transactions.index', $pQuery) }}" 
                           class="w-8 h-8 flex items-center justify-center rounded-lg font-bold {{ $i === $page ? 'bg-primary text-white shadow-subtle' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                            {{ $i }}
                        </a>
                    @endfor
                </div>
            </div>
            @endif
        </div>
    </div>
</main>

<!-- Receipt Thermal Preview Modal -->
<div id="trx-modal" onclick="if(event.target===this) closeTrxModal()" class="hidden fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div id="trx-modal-content" class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-soft-lg border border-slate-200 relative animate-toast text-slate-900">
        <!-- Close Button -->
        <button type="button" onclick="closeTrxModal()" class="absolute right-4 top-4 p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition" title="Tutup">
            <span class="material-icons text-xl">close</span>
        </button>

        <div class="text-center pb-3 border-b border-dashed border-slate-300">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-primary flex items-center justify-center mx-auto mb-1.5">
                <span class="material-icons text-xl">local_cafe</span>
            </div>
            <h3 class="font-black text-xl text-slate-900 tracking-tight">MyCafe POS</h3>
            <p class="text-[11px] text-slate-500">Struk Transaksi Penjualan</p>
            <p class="text-[10px] font-mono text-slate-500 mt-1" id="m-inv">TRX-0000</p>
            <p class="text-[10px] text-slate-500" id="m-date">01 Jan 2026</p>
        </div>

        <div class="py-2.5 text-xs space-y-1 font-mono text-slate-600 border-b border-dashed border-slate-300">
            <div class="flex justify-between">
                <span>Kasir:</span>
                <span id="m-kasir" class="font-bold">-</span>
            </div>
            <div class="flex justify-between">
                <span>Pelanggan:</span>
                <span id="m-member" class="font-bold">Umum</span>
            </div>
            <div class="flex justify-between">
                <span>Metode:</span>
                <span id="m-method" class="font-bold">CASH</span>
            </div>
        </div>

        <div class="py-3 space-y-1 text-xs font-mono border-b border-dashed border-slate-300">
            <div class="flex justify-between text-slate-600">
                <span>Subtotal:</span>
                <span id="m-subtotal">Rp 0</span>
            </div>
            <div class="flex justify-between text-rose-600">
                <span>Diskon Poin:</span>
                <span id="m-diskon">- Rp 0</span>
            </div>
            <div class="flex justify-between font-bold text-sm text-slate-900 pt-1 border-t border-slate-200">
                <span>TOTAL BAYAR:</span>
                <span id="m-total" class="font-black">Rp 0</span>
            </div>
            <div class="flex justify-between text-emerald-700 font-bold pt-1">
                <span>Poin Loyalitas:</span>
                <span id="m-poin">+0 pts</span>
            </div>
        </div>

        <div class="mt-5 flex gap-2 no-print">
            <button type="button" onclick="window.print()" class="flex-1 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-subtle">
                <span class="material-icons text-base">print</span>
                <span>Cetak Struk</span>
            </button>
            <button type="button" onclick="closeTrxModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openTrxFromBtn(btn) {
        const ds = btn.dataset;
        showTrxModal(ds.inv, ds.member, ds.kasir, ds.date, ds.subtotal, ds.diskon, ds.total, ds.method, ds.poin);
    }

    function showTrxModal(inv, member, kasir, date, subtotal, diskon, total, method, poin) {
        document.getElementById('m-inv').textContent = inv || '-';
        document.getElementById('m-member').textContent = member || 'Umum';
        document.getElementById('m-kasir').textContent = kasir || 'Kasir';
        document.getElementById('m-date').textContent = date || '-';
        document.getElementById('m-subtotal').textContent = subtotal || 'Rp 0';
        document.getElementById('m-diskon').textContent = '- ' + (diskon || 'Rp 0');
        document.getElementById('m-total').textContent = total || 'Rp 0';
        document.getElementById('m-method').textContent = method || 'CASH';
        document.getElementById('m-poin').textContent = '+' + (poin || '0') + ' pts';
        document.getElementById('trx-modal').classList.remove('hidden');
    }

    function closeTrxModal() {
        document.getElementById('trx-modal').classList.add('hidden');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeTrxModal();
    });
</script>
@endsection
