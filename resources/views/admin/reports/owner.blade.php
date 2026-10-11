@extends('layouts.app')

@section('title', 'Laporan & Statistik Owner - MyCafe')

@section('styles')
<style>
    @media print {
        aside, header, nav, .no-print, button, a[href*="export"] { display: none !important; }
        main { padding: 0 !important; margin: 0 !important; }
        .print-receipt-only { display: block !important; }
    }
</style>
@endsection

@section('content')
@php
    $catLabels = $categorySales->pluck('category')->toArray();
    $catValues = $categorySales->pluck('omset')->map(fn($v) => (float)$v)->toArray();
    $tierLabels = $tierStats->pluck('tier')->map(fn($t) => ucfirst($t))->toArray();
    $tierCounts = $tierStats->pluck('cnt')->map(fn($c) => (int)$c)->toArray();
@endphp

<!-- Main Content Area -->
<main class="flex-1 flex flex-col min-w-0 min-h-screen lg:h-screen lg:overflow-y-auto">
    <!-- Topbar -->
    @include('layouts.topbar', [
        'pageTitle' => 'Laporan & Statistik Owner',
        'pageSubtitle' => 'Analisis performa finansial, kontribusi menu, dan dinamika member MyCafe.',
        'topbarActions' => [
            ['url' => route('owner.report', ['export' => 'excel']), 'label' => 'Unduh CSV', 'icon' => 'file_download', 'class' => 'px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 transition flex items-center gap-1.5 shadow-subtle'],
            ['url' => 'javascript:window.print()', 'onclick' => 'window.print()', 'label' => 'Cetak Laporan', 'icon' => 'print', 'class' => 'px-3.5 py-1.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold transition flex items-center gap-1.5 shadow-subtle active:scale-95']
        ]
    ])

    <div class="p-4 sm:p-6 lg:p-8 space-y-6 flex-1">

        <!-- Metrics Overview (4 Columns) -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Omset Bulan Ini -->
            <div class="card-hover bg-white dark:bg-card-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-subtle">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Omset Bulan Ini</span>
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <span class="material-icons text-xl">payments</span>
                    </div>
                </div>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white font-mono">{{ formatRupiah($pendBulanIni) }}</h3>
                <div class="flex items-center justify-between mt-2 pt-2 border-t border-border-light dark:border-border-dark">
                    <span class="inline-flex items-center gap-0.5 text-xs font-bold {{ $growth >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500' }}">
                        <span class="material-icons text-xs">{{ $growth >= 0 ? 'trending_up' : 'trending_down' }}</span>
                        <span>{{ ($growth >= 0 ? '+' : '') . $growth }}%</span>
                        <span class="font-normal text-slate-400 ml-1">vs bln lalu</span>
                    </span>
                    <a href="{{ route('transactions.index') }}" class="text-[11px] font-bold text-primary hover:underline">Detail &rarr;</a>
                </div>
            </div>

            <!-- Omset Tahun Ini -->
            <div class="card-hover bg-white dark:bg-card-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-subtle">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Omset Tahun Ini</span>
                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <span class="material-icons text-xl">account_balance_wallet</span>
                    </div>
                </div>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white font-mono">{{ formatRupiah($totalPendapatan) }}</h3>
                <div class="flex items-center justify-between mt-2 pt-2 border-t border-border-light dark:border-border-dark">
                    <span class="text-xs text-slate-400 font-mono">{{ number_format($totalTrx) }} total transaksi</span>
                    <a href="{{ route('reports.business') }}" class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline">Analisis &rarr;</a>
                </div>
            </div>

            <!-- Total Member -->
            <div class="card-hover bg-white dark:bg-card-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-subtle">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Member</span>
                    <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                        <span class="material-icons text-xl">groups</span>
                    </div>
                </div>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white font-mono">{{ number_format($totalMember) }}</h3>
                <div class="flex items-center justify-between mt-2 pt-2 border-t border-border-light dark:border-border-dark">
                    <span class="text-xs text-slate-400">Pelanggan loyal terdaftar</span>
                    <a href="{{ route('members.index') }}" class="text-[11px] font-bold text-primary hover:underline">Kelola &rarr;</a>
                </div>
            </div>

            <!-- Saldo Poin Member -->
            <div class="card-hover bg-white dark:bg-card-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-subtle">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Saldo Poin Member</span>
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
                        <span class="material-icons text-xl">toll</span>
                    </div>
                </div>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white font-mono">{{ number_format($totalPoin) }} <span class="text-xs font-normal text-slate-400">pts</span></h3>
                <div class="flex items-center justify-between mt-2 pt-2 border-t border-border-light dark:border-border-dark">
                    <span class="text-xs text-slate-400">Liabilitas reward aktif</span>
                    <a href="{{ route('rewards.manage') }}" class="text-[11px] font-bold text-amber-600 dark:text-amber-400 hover:underline">Reward &rarr;</a>
                </div>
            </div>
        </section>

        <!-- Visual Charts Section (Category & Tier) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Sales by Category (Chart + Progress) -->
            <div class="bg-white dark:bg-card-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-subtle flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Kontribusi Penjualan per Kategori</h3>
                            <p class="text-xs text-slate-400">Proporsi omset produk MyCafe</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                            {{ count($categorySales) }} Kategori
                        </span>
                    </div>
                    <div class="h-48 mb-4 relative">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>

                <div class="space-y-3 pt-3 border-t border-border-light dark:border-border-dark">
                    @if ($categorySales->isEmpty())
                        <p class="text-xs text-slate-400 text-center py-4">Belum ada data penjualan kategori.</p>
                    @else
                        @php
                            $maxCat = $categorySales->max('omset') ?: 1;
                        @endphp
                        @foreach ($categorySales as $cs)
                            @php
                                $pct = max(10, round(($cs->omset / $maxCat) * 100));
                            @endphp
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs font-semibold">
                                    <span class="text-slate-700 dark:text-slate-300">{{ $cs->category }} <span class="text-slate-400 text-[11px]">({{ $cs->total_qty }} terjual)</span></span>
                                    <span class="font-mono font-bold text-slate-900 dark:text-white">{{ formatRupiah($cs->omset) }}</span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-800/80 h-2 rounded-full overflow-hidden">
                                    <div class="bg-primary h-full rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Member Tier Distribution -->
            <div class="bg-white dark:bg-card-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-subtle flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Distribusi Tier Member</h3>
                            <p class="text-xs text-slate-400">Tingkat loyalitas pelanggan terdaftar</p>
                        </div>
                        <a href="{{ route('members.index') }}" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                            <span>Lihat Semua</span>
                            <span class="material-icons text-xs">arrow_forward</span>
                        </a>
                    </div>
                    <div class="h-48 mb-4 relative">
                        <canvas id="tierChart"></canvas>
                    </div>
                </div>

                <div class="space-y-3 pt-3 border-t border-border-light dark:border-border-dark">
                    @if ($tierStats->isEmpty())
                        <p class="text-xs text-slate-400 text-center py-4">Belum ada member terdaftar.</p>
                    @else
                        @foreach ($tierStats as $ts)
                            @php
                                $pct = $totalMember > 0 ? round(($ts->cnt / $totalMember) * 100) : 0;
                                $tColor = match(strtolower($ts->tier)) {
                                    'platinum' => 'bg-indigo-600',
                                    'gold'     => 'bg-amber-500',
                                    'silver'   => 'bg-slate-400',
                                    default    => 'bg-amber-700',
                                };
                                $badgeBg = match(strtolower($ts->tier)) {
                                    'platinum' => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/20',
                                    'gold'     => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                    'silver'   => 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/20',
                                    default    => 'bg-amber-800/10 text-amber-800 dark:text-amber-600 border-amber-800/20',
                                };
                            @endphp
                            <a href="{{ route('members.index', ['tier' => $ts->tier]) }}" class="block space-y-1 p-1.5 -mx-1.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition group">
                                <div class="flex justify-between items-center text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider border {{ $badgeBg }}">
                                            {{ $ts->tier }}
                                        </span>
                                        <span class="text-slate-600 dark:text-slate-300 font-semibold">{{ $ts->cnt }} Member</span>
                                    </div>
                                    <span class="font-mono font-bold text-slate-500 group-hover:text-primary transition-colors">{{ $pct }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-800/80 h-2 rounded-full overflow-hidden">
                                    <div class="{{ $tColor }} h-full rounded-full transition-all duration-500 group-hover:brightness-110" style="width: {{ $pct }}%"></div>
                                </div>
                            </a>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <!-- Big Transactions Leaderboard Table -->
        <div class="bg-white dark:bg-card-dark rounded-2xl border border-border-light dark:border-border-dark shadow-subtle overflow-hidden">
            <div class="p-5 border-b border-border-light dark:border-border-dark flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="material-icons text-amber-500 text-lg">star</span>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Transaksi Nominal Terbesar (Top Orders)</h3>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">Pesanan bernilai tertinggi untuk memantau basket size pelanggan premium.</p>
                </div>
                <a href="{{ route('transactions.index') }}" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                    <span>Buka Semua Riwayat Transaksi</span>
                    <span class="material-icons text-xs">arrow_forward</span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 uppercase font-bold tracking-wider border-b border-border-light dark:border-border-dark">
                        <tr>
                            <th class="px-5 py-3.5">Invoice</th>
                            <th class="px-5 py-3.5">Pelanggan / Member</th>
                            <th class="px-5 py-3.5">Tanggal Transaksi</th>
                            <th class="px-5 py-3.5">Metode Bayar</th>
                            <th class="px-5 py-3.5 text-right">Nilai Total</th>
                            <th class="px-5 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-light dark:divide-border-dark">
                        @if ($bigTrx->isEmpty())
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">Belum ada data transaksi yang tercatat.</td>
                        </tr>
                        @else
                            @foreach ($bigTrx as $bt)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                                <td class="px-5 py-3.5">
                                    <button type="button" 
                                            data-inv="{{ $bt->invoice_no }}"
                                            data-date="{{ \Carbon\Carbon::parse($bt->created_at)->translatedFormat('d M Y, H:i') }}"
                                            data-cashier="{{ $bt->cashier_name ?? 'Kasir MyCafe' }}"
                                            data-member="{{ $bt->member_name }}{{ $bt->member_code ? ' (' . $bt->member_code . ')' : '' }}"
                                            data-subtotal="{{ formatRupiah($bt->subtotal) }}"
                                            data-discount="- {{ formatRupiah($bt->diskon) }}"
                                            data-total="{{ formatRupiah($bt->total) }}"
                                            data-method="{{ strtoupper($bt->payment_method) }}"
                                            data-points="+{{ $bt->poin_earned }} pts"
                                            onclick="openReceiptFromBtn(this)" 
                                            class="font-mono font-bold text-primary hover:underline flex items-center gap-1 group">
                                        <span>{{ $bt->invoice_no }}</span>
                                        <span class="material-icons text-xs opacity-0 group-hover:opacity-100 transition-opacity">receipt</span>
                                    </button>
                                </td>
                                <td class="px-5 py-3.5">
                                    @if (!empty($bt->member_db_id))
                                        <a href="{{ route('members.show', $bt->member_db_id) }}" class="font-bold text-slate-900 dark:text-white hover:text-primary transition flex items-center gap-1.5 group">
                                            <span>{{ $bt->member_name }}</span>
                                            <span class="text-[10px] text-slate-400 font-mono group-hover:text-primary">({{ $bt->member_code ?? '' }})</span>
                                        </a>
                                    @else
                                        <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $bt->member_name }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 font-mono">
                                    {{ \Carbon\Carbon::parse($bt->created_at)->translatedFormat('d M Y, H:i') }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase font-mono bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ $bt->payment_method }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right font-black text-slate-900 dark:text-white font-mono text-sm">
                                    {{ formatRupiah($bt->total) }}
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <button type="button" 
                                            data-inv="{{ $bt->invoice_no }}"
                                            data-date="{{ \Carbon\Carbon::parse($bt->created_at)->translatedFormat('d M Y, H:i') }}"
                                            data-cashier="{{ $bt->cashier_name ?? 'Kasir MyCafe' }}"
                                            data-member="{{ $bt->member_name }}{{ $bt->member_code ? ' (' . $bt->member_code . ')' : '' }}"
                                            data-subtotal="{{ formatRupiah($bt->subtotal) }}"
                                            data-discount="- {{ formatRupiah($bt->diskon) }}"
                                            data-total="{{ formatRupiah($bt->total) }}"
                                            data-method="{{ strtoupper($bt->payment_method) }}"
                                            data-points="+{{ $bt->poin_earned }} pts"
                                            onclick="openReceiptFromBtn(this)" 
                                            class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-primary text-slate-700 hover:text-white text-xs font-bold transition inline-flex items-center gap-1 shadow-sm active:scale-95">
                                        <span class="material-icons text-xs">receipt_long</span>
                                        <span>Struk</span>
                                    </button>
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

<!-- Printable Thermal Receipt Modal -->
<div id="receiptModal" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm hidden items-center justify-center p-4 overflow-y-auto no-print" onclick="if(event.target === this) closeReceiptModal()">
    <div class="bg-white dark:bg-card-dark border border-border-light dark:border-border-dark rounded-3xl max-w-sm w-full p-6 shadow-2xl relative animate-toast">
        <!-- Modal Header -->
        <div class="text-center pb-4 border-b border-dashed border-border-light dark:border-border-dark">
            <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mx-auto mb-2 border border-primary/20">
                <span class="material-icons text-2xl">local_cafe</span>
            </div>
            <h3 class="font-black text-lg text-slate-900 dark:text-white tracking-tight">MyCafe Artisan</h3>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Struk Pembayaran Sah & Resmi</p>
            <div class="mt-2 inline-block px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 font-mono text-xs font-bold text-primary" id="modalInv">
                TRX-00000000-000
            </div>
        </div>

        <!-- Receipt Info -->
        <div class="py-3 text-xs space-y-1.5 border-b border-dashed border-border-light dark:border-border-dark font-mono text-slate-600 dark:text-slate-300">
            <div class="flex justify-between">
                <span class="text-slate-400 font-sans">Waktu:</span>
                <span id="modalDate">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400 font-sans">Kasir:</span>
                <span id="modalCashier">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400 font-sans">Pelanggan:</span>
                <span id="modalMember" class="font-bold text-primary">-</span>
            </div>
        </div>

        <!-- Totals & Payment -->
        <div class="py-3 space-y-2 border-b border-border-light dark:border-border-dark text-xs">
            <div class="flex justify-between text-slate-600 dark:text-slate-400">
                <span>Subtotal:</span>
                <span class="font-mono font-bold text-slate-900 dark:text-white" id="modalSubtotal">Rp 0</span>
            </div>
            <div class="flex justify-between text-rose-500">
                <span>Diskon Poin:</span>
                <span class="font-mono font-bold" id="modalDiscount">- Rp 0</span>
            </div>
            <div class="flex justify-between text-sm font-black text-slate-900 dark:text-white pt-2 border-t border-border-light dark:border-border-dark">
                <span>Total Pembayaran:</span>
                <span class="text-primary font-mono text-base" id="modalTotal">Rp 0</span>
            </div>
            <div class="flex justify-between text-[11px] text-slate-500 dark:text-slate-400 pt-1">
                <span>Metode:</span>
                <span class="font-mono font-bold uppercase" id="modalMethod">-</span>
            </div>
            <div class="flex justify-between text-[11px] text-emerald-600 dark:text-emerald-400 font-bold">
                <span>Poin Didapat:</span>
                <span class="font-mono" id="modalPoints">+0 pts</span>
            </div>
        </div>

        <div class="pt-4 flex gap-2.5">
            <button type="button" onclick="window.print()" class="flex-1 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm">
                <span class="material-icons text-sm">print</span>
                <span>Cetak Struk</span>
            </button>
            <button type="button" onclick="closeReceiptModal()" class="px-4 py-2.5 rounded-xl border border-border-light dark:border-border-dark bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition">
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Chart.js Category Breakdown
    const catLabels = @json($catLabels);
    const catValues = @json($catValues);
    const tierLabels = @json($tierLabels);
    const tierCounts = @json($tierCounts);

    const isDarkMode = document.documentElement.classList.contains('dark');
    const textColor = isDarkMode ? '#94a3b8' : '#64748b';

    // 1. Category Chart (Doughnut)
    if (catLabels.length > 0 && document.getElementById('categoryChart')) {
        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catValues,
                    backgroundColor: ['#944b1c', '#d97706', '#0284c7', '#10b981', '#8b5cf6', '#ec4899'],
                    borderWidth: 2,
                    borderColor: isDarkMode ? '#131b2e' : '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textColor,
                            font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' },
                            boxWidth: 12,
                            padding: 12
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.label + ': Rp ' + Number(context.raw).toLocaleString('id-ID');
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    }

    // 2. Member Tier Chart (Bar)
    if (tierLabels.length > 0 && document.getElementById('tierChart')) {
        new Chart(document.getElementById('tierChart'), {
            type: 'bar',
            data: {
                labels: tierLabels,
                datasets: [{
                    label: 'Jumlah Member',
                    data: tierCounts,
                    backgroundColor: ['#b45309', '#94a3b8', '#f59e0b', '#6366f1'],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: textColor, font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: isDarkMode ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)' },
                        ticks: {
                            color: textColor,
                            stepSize: 1,
                            font: { family: 'JetBrains Mono', size: 10 }
                        }
                    }
                }
            }
        });
    }

    // Receipt Modal Handlers
    function openReceiptFromBtn(btn) {
        const ds = btn.dataset;
        document.getElementById('modalInv').textContent = ds.inv || '-';
        document.getElementById('modalDate').textContent = ds.date || '-';
        document.getElementById('modalCashier').textContent = ds.cashier || 'Kasir MyCafe';
        document.getElementById('modalMember').textContent = ds.member || 'Pelanggan Umum';
        document.getElementById('modalSubtotal').textContent = ds.subtotal || 'Rp 0';
        document.getElementById('modalDiscount').textContent = ds.discount || '- Rp 0';
        document.getElementById('modalTotal').textContent = ds.total || 'Rp 0';
        document.getElementById('modalMethod').textContent = ds.method || 'CASH';
        document.getElementById('modalPoints').textContent = ds.points || '+0 pts';
        
        const modal = document.getElementById('receiptModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function showReceipt(tx) {
        if (!tx) return;
        document.getElementById('modalInv').textContent = tx.invoice_no || '-';
        document.getElementById('modalDate').textContent = tx.created_at || '-';
        document.getElementById('modalCashier').textContent = tx.cashier_name || 'Kasir MyCafe';
        document.getElementById('modalMember').textContent = (tx.member_name || 'Pelanggan') + (tx.member_code ? ' (' + tx.member_code + ')' : '');
        document.getElementById('modalSubtotal').textContent = 'Rp ' + Number(tx.subtotal || 0).toLocaleString('id-ID');
        document.getElementById('modalDiscount').textContent = '- Rp ' + Number(tx.diskon || 0).toLocaleString('id-ID');
        document.getElementById('modalTotal').textContent = 'Rp ' + Number(tx.total || 0).toLocaleString('id-ID');
        document.getElementById('modalMethod').textContent = (tx.payment_method || 'CASH').toUpperCase();
        document.getElementById('modalPoints').textContent = '+' + (tx.poin_earned || 0) + ' pts';
        
        const modal = document.getElementById('receiptModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeReceiptModal() {
        const modal = document.getElementById('receiptModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeReceiptModal();
    });
</script>
@endsection
