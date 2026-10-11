@extends('layouts.app')

@section('title', 'Dashboard Overview - MyCafe POS System')

@section('content')
<!-- Main Content Area -->
<main class="flex-1 flex flex-col min-w-0 min-h-screen lg:h-screen lg:overflow-y-auto">
    <!-- Topbar -->
    @include('layouts.topbar', [
        'pageTitle' => 'Dashboard Overview',
        'pageSubtitle' => 'Ringkasan metrik penjualan, aktivitas kasir POS, dan pertumbuhan anggota setia MyCafe.',
        'topbarActions' => [
            ['url' => route('members.create'), 'label' => 'Tambah Member', 'icon' => 'person_add', 'class' => 'px-3 py-1.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition shadow-subtle'],
            ['url' => route('pos.index'), 'label' => 'Buka Kasir POS', 'icon' => 'point_of_sale', 'class' => 'px-3.5 py-1.5 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition shadow-subtle active:scale-95']
        ]
    ])

    <div class="p-4 sm:p-6 lg:p-8 space-y-6 flex-1">
        <!-- 4 KPI Cards -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Member -->
            <a href="{{ route('members.index') }}" class="card-hover bg-white p-5 rounded-2xl border border-slate-200 shadow-subtle block group">
                <div class="flex justify-between items-start mb-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center">
                        <span class="material-icons text-xl">groups</span>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-400 group-hover:text-primary transition flex items-center gap-0.5">
                        <span>Data Member</span>
                        <span class="material-icons text-xs">chevron_right</span>
                    </span>
                </div>
                <h3 class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Member</h3>
                <p class="text-2xl font-black mt-1 text-slate-900 font-mono">{{ number_format($totalMember) }}</p>
                <p class="text-[11px] text-slate-400 mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Pelanggan terdaftar aktif</span>
                </p>
            </a>

            <!-- Transaksi Hari Ini -->
            <a href="{{ route('transactions.index') }}" class="card-hover bg-white p-5 rounded-2xl border border-slate-200 shadow-subtle block group">
                <div class="flex justify-between items-start mb-3">
                    <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-600 flex items-center justify-center">
                        <span class="material-icons text-xl">receipt_long</span>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-400 group-hover:text-primary transition flex items-center gap-0.5">
                        <span>Riwayat Transaksi</span>
                        <span class="material-icons text-xs">chevron_right</span>
                    </span>
                </div>
                <h3 class="text-slate-500 text-xs font-bold uppercase tracking-wider">Transaksi Hari Ini</h3>
                <p class="text-2xl font-black mt-1 text-slate-900 font-mono">{{ number_format($transaksiHariIni) }}</p>
                <p class="text-[11px] text-slate-400 mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                    <span>Pesanan kasir terproses</span>
                </p>
            </a>

            <!-- Omset Hari Ini -->
            <a href="{{ route('reports.business') }}" class="card-hover bg-white p-5 rounded-2xl border border-slate-200 shadow-subtle block group">
                <div class="flex justify-between items-start mb-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                        <span class="material-icons text-xl">payments</span>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-400 group-hover:text-primary transition flex items-center gap-0.5">
                        <span>Laporan Bisnis</span>
                        <span class="material-icons text-xs">chevron_right</span>
                    </span>
                </div>
                <h3 class="text-slate-500 text-xs font-bold uppercase tracking-wider">Omset Hari Ini</h3>
                <p class="text-2xl font-black mt-1 text-slate-900 font-mono">{{ formatRupiah($pendapatanHariIni) }}</p>
                <p class="text-[11px] text-slate-400 mt-2">
                    Bulan ini: <strong class="text-slate-700 font-mono">{{ formatRupiah($totalPendapatanBulanIni) }}</strong>
                </p>
            </a>

            <!-- Poin Loyalitas Digunakan -->
            <a href="{{ route('rewards.catalog') }}" class="card-hover bg-white p-5 rounded-2xl border border-slate-200 shadow-subtle block group">
                <div class="flex justify-between items-start mb-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-600 flex items-center justify-center">
                        <span class="material-icons text-xl">loyalty</span>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-400 group-hover:text-primary transition flex items-center gap-0.5">
                        <span>Katalog Reward</span>
                        <span class="material-icons text-xs">chevron_right</span>
                    </span>
                </div>
                <h3 class="text-slate-500 text-xs font-bold uppercase tracking-wider">Poin Ditukar Hari Ini</h3>
                <p class="text-2xl font-black mt-1 text-slate-900 font-mono">{{ number_format($poinTerpakai) }} <span class="text-xs font-normal text-slate-400">pts</span></p>
                <p class="text-[11px] text-slate-400 mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                    <span>Diskon loyalitas digunakan</span>
                </p>
            </a>
        </section>

        <!-- Middle Section: Chart & Operational Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- 7-Day Revenue Trend Chart (2 cols) -->
            <div class="lg:col-span-2 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-subtle flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Tren Pendapatan 7 Hari Terakhir</h2>
                        <p class="text-xs text-slate-400">Fluktuasi omset harian kasir cafe</p>
                    </div>
                    <a href="{{ route('reports.business') }}" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                        <span>Laporan Bisnis Lengkap</span>
                        <span class="material-icons text-sm">arrow_forward</span>
                    </a>
                </div>
                <div class="h-64 relative w-full">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Quick POS Hub & Member Tier Distribution (1 col) -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-subtle flex flex-col justify-between space-y-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 mb-1">Akses Operasional Cepat</h2>
                    <p class="text-xs text-slate-400 mb-4">Pintasan tugas kasir dan administrasi</p>
                    
                    <div class="space-y-2">
                        <a href="{{ route('pos.index') }}" class="flex items-center justify-between p-3 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-700 border border-amber-500/20 transition group">
                            <div class="flex items-center gap-3">
                                <span class="material-icons text-primary group-hover:scale-110 transition-transform">point_of_sale</span>
                                <div>
                                    <p class="text-xs font-bold text-slate-900">Buka Layar Kasir POS</p>
                                    <p class="text-[11px] text-slate-500">Catat transaksi, beri poin member</p>
                                </div>
                            </div>
                            <span class="material-icons text-sm">arrow_forward</span>
                        </a>

                        <a href="{{ route('members.create') }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 transition group">
                            <div class="flex items-center gap-3">
                                <span class="material-icons text-slate-600 group-hover:text-primary transition">person_add</span>
                                <div>
                                    <p class="text-xs font-bold text-slate-900">Pendaftaran Member</p>
                                    <p class="text-[11px] text-slate-500">Cetak kode & akun member baru</p>
                                </div>
                            </div>
                            <span class="material-icons text-sm text-slate-400">chevron_right</span>
                        </a>

                        <a href="{{ route('rewards.manage') }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 transition group">
                            <div class="flex items-center gap-3">
                                <span class="material-icons text-slate-600 group-hover:text-primary transition">card_giftcard</span>
                                <div>
                                    <p class="text-xs font-bold text-slate-900">Kelola Katalog Reward</p>
                                    <p class="text-[11px] text-slate-500">Atur hadiah & kuota stok poin</p>
                                </div>
                            </div>
                            <span class="material-icons text-sm text-slate-400">chevron_right</span>
                        </a>
                    </div>
                </div>

                <!-- Tier Summary -->
                <div class="pt-4 border-t border-slate-200">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Sebaran Tier Anggota:</span>
                        <a href="{{ route('members.index') }}" class="text-[10px] font-bold text-primary hover:underline">Semua &rarr;</a>
                    </div>
                    <div class="grid grid-cols-4 gap-1.5 text-center text-xs">
                        <a href="{{ route('members.index', ['tier' => 'bronze']) }}" class="p-2 rounded-lg bg-amber-500/10 border border-amber-500/20 hover:scale-105 active:scale-95 transition-all block group" title="Filter Member Bronze">
                            <p class="text-[10px] font-bold text-amber-700 uppercase group-hover:underline">Bronze</p>
                            <p class="font-bold text-slate-900 mt-0.5 font-mono">{{ $tierCounts['bronze'] ?? 0 }}</p>
                        </a>
                        <a href="{{ route('members.index', ['tier' => 'silver']) }}" class="p-2 rounded-lg bg-slate-200/60 border border-slate-300 hover:scale-105 active:scale-95 transition-all block group" title="Filter Member Silver">
                            <p class="text-[10px] font-bold text-slate-600 uppercase group-hover:underline">Silver</p>
                            <p class="font-bold text-slate-900 mt-0.5 font-mono">{{ $tierCounts['silver'] ?? 0 }}</p>
                        </a>
                        <a href="{{ route('members.index', ['tier' => 'gold']) }}" class="p-2 rounded-lg bg-yellow-500/10 border border-yellow-500/20 hover:scale-105 active:scale-95 transition-all block group" title="Filter Member Gold">
                            <p class="text-[10px] font-bold text-yellow-600 uppercase group-hover:underline">Gold</p>
                            <p class="font-bold text-slate-900 mt-0.5 font-mono">{{ $tierCounts['gold'] ?? 0 }}</p>
                        </a>
                        <a href="{{ route('members.index', ['tier' => 'platinum']) }}" class="p-2 rounded-lg bg-purple-500/10 border border-purple-500/20 hover:scale-105 active:scale-95 transition-all block group" title="Filter Member Platinum">
                            <p class="text-[10px] font-bold text-purple-600 uppercase group-hover:underline">Plat</p>
                            <p class="font-bold text-slate-900 mt-0.5 font-mono">{{ $tierCounts['platinum'] ?? 0 }}</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions Table -->
        <section class="bg-white rounded-2xl border border-slate-200 shadow-subtle overflow-hidden">
            <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Transaksi Kasir Terkini</h2>
                    <p class="text-xs text-slate-400">6 transaksi terakhir yang tersimpan di sistem</p>
                </div>
                <a href="{{ route('transactions.index') }}" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                    <span>Lihat Semua Transaksi</span>
                    <span class="material-icons text-sm">arrow_forward</span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-4">No. Invoice</th>
                            <th class="py-3 px-4">Pelanggan</th>
                            <th class="py-3 px-4">Kasir</th>
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Total</th>
                            <th class="py-3 px-4">Loyalitas</th>
                            <th class="py-3 px-4">Metode</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @if ($recentTrx->isEmpty())
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                Belum ada transaksi hari ini. Buka Kasir POS untuk mulai melayani pelanggan!
                            </td>
                        </tr>
                        @else
                            @foreach ($recentTrx as $rx)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                    <button type="button" 
                                            data-inv="{{ $rx->invoice_no }}"
                                            data-member="{{ $rx->member_name }}"
                                            data-kasir="{{ $rx->cashier_name ?? 'Kasir' }}"
                                            data-date="{{ date('d M Y, H:i', strtotime($rx->created_at)) }}"
                                            data-subtotal="{{ formatRupiah($rx->subtotal) }}"
                                            data-diskon="{{ formatRupiah($rx->diskon) }}"
                                            data-total="{{ formatRupiah($rx->total) }}"
                                            data-method="{{ strtoupper($rx->payment_method) }}"
                                            data-poin="{{ $rx->poin_earned }}"
                                            onclick="openDashboardReceipt(this)" 
                                            class="text-primary hover:underline flex items-center gap-1 group font-bold text-left" title="Klik untuk lihat struk">
                                        <span>{{ $rx->invoice_no }}</span>
                                        <span class="material-icons text-xs opacity-0 group-hover:opacity-100 transition-opacity">receipt</span>
                                    </button>
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-800">
                                    {{ $rx->member_name }}
                                </td>
                                <td class="py-3 px-4 text-slate-500">
                                    {{ $rx->cashier_name ?? 'Kasir' }}
                                </td>
                                <td class="py-3 px-4 text-slate-500">
                                    {{ date('d M, H:i', strtotime($rx->created_at)) }}
                                </td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                    {{ formatRupiah($rx->total) }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-emerald-600 font-semibold font-mono text-[11px]">+{{ $rx->poin_earned }}</span>
                                        @if ($rx->poin_redeemed > 0)
                                            <span class="text-rose-500 font-mono text-[11px]">-{{ $rx->poin_redeemed }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="uppercase text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                        {{ $rx->payment_method }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <button type="button" 
                                            data-inv="{{ $rx->invoice_no }}"
                                            data-member="{{ $rx->member_name }}"
                                            data-kasir="{{ $rx->cashier_name ?? 'Kasir' }}"
                                            data-date="{{ date('d M Y, H:i', strtotime($rx->created_at)) }}"
                                            data-subtotal="{{ formatRupiah($rx->subtotal) }}"
                                            data-diskon="{{ formatRupiah($rx->diskon) }}"
                                            data-total="{{ formatRupiah($rx->total) }}"
                                            data-method="{{ strtoupper($rx->payment_method) }}"
                                            data-poin="{{ $rx->poin_earned }}"
                                            onclick="openDashboardReceipt(this)"
                                            class="text-[10px] font-bold px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-primary hover:text-white text-slate-700 transition inline-flex items-center gap-1 shadow-subtle active:scale-95" title="Buka Struk">
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
        </section>
    </div>
</main>

<!-- Dashboard Receipt Thermal Preview Modal -->
<div id="dash-receipt-modal" onclick="if(event.target===this) closeDashboardReceipt()" class="hidden fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-soft-lg border border-slate-200 relative animate-toast text-slate-900">
        <button type="button" onclick="closeDashboardReceipt()" class="absolute right-4 top-4 p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition cursor-pointer" title="Tutup">
            <span class="material-icons text-xl">close</span>
        </button>
        <div class="text-center pb-3 border-b border-dashed border-slate-300">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-primary flex items-center justify-center mx-auto mb-1.5">
                <span class="material-icons text-xl">local_cafe</span>
            </div>
            <h3 class="font-black text-xl text-slate-900 tracking-tight">MyCafe POS</h3>
            <p class="text-[11px] text-slate-500">Struk Transaksi Penjualan</p>
            <p class="text-[10px] font-mono text-slate-500 mt-1" id="dash-inv">TRX-0000</p>
            <p class="text-[10px] text-slate-500" id="dash-date">01 Jan 2026</p>
        </div>

        <div class="py-2.5 text-xs space-y-1 font-mono text-slate-600 border-b border-dashed border-slate-300">
            <div class="flex justify-between">
                <span>Kasir:</span>
                <span id="dash-kasir" class="font-bold">-</span>
            </div>
            <div class="flex justify-between">
                <span>Pelanggan:</span>
                <span id="dash-member" class="font-bold">Umum</span>
            </div>
            <div class="flex justify-between">
                <span>Metode:</span>
                <span id="dash-method" class="font-bold">CASH</span>
            </div>
        </div>

        <div class="py-3 space-y-1 text-xs font-mono border-b border-dashed border-slate-300">
            <div class="flex justify-between text-slate-600">
                <span>Subtotal:</span>
                <span id="dash-subtotal">Rp 0</span>
            </div>
            <div class="flex justify-between text-rose-600">
                <span>Diskon Poin:</span>
                <span id="dash-diskon">- Rp 0</span>
            </div>
            <div class="flex justify-between font-bold text-sm text-slate-900 pt-1 border-t border-slate-200">
                <span>TOTAL BAYAR:</span>
                <span id="dash-total" class="font-black text-primary">Rp 0</span>
            </div>
            <div class="flex justify-between text-emerald-700 font-bold pt-1">
                <span>Poin Loyalitas:</span>
                <span id="dash-poin">+0 pts</span>
            </div>
        </div>

        <div class="mt-5 flex gap-2">
            <button type="button" onclick="window.print()" class="flex-1 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-subtle">
                <span class="material-icons text-base">print</span>
                <span>Cetak Struk</span>
            </button>
            <button type="button" onclick="closeDashboardReceipt()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openDashboardReceipt(btn) {
        const ds = btn.dataset;
        document.getElementById('dash-inv').textContent = ds.inv || '-';
        document.getElementById('dash-member').textContent = ds.member || 'Pelanggan Umum';
        document.getElementById('dash-kasir').textContent = ds.kasir || 'Kasir';
        document.getElementById('dash-date').textContent = ds.date || '-';
        document.getElementById('dash-subtotal').textContent = ds.subtotal || 'Rp 0';
        document.getElementById('dash-diskon').textContent = '- ' + (ds.diskon || 'Rp 0');
        document.getElementById('dash-total').textContent = ds.total || 'Rp 0';
        document.getElementById('dash-method').textContent = ds.method || 'CASH';
        document.getElementById('dash-poin').textContent = '+' + (ds.poin || '0') + ' pts';
        document.getElementById('dash-receipt-modal').classList.remove('hidden');
    }

    function closeDashboardReceipt() {
        document.getElementById('dash-receipt-modal').classList.add('hidden');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeDashboardReceipt();
    });

    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const gridColor = 'rgba(0, 0, 0, 0.05)';
        const textColor = '#64748b';

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($trendLabels),
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: @json($trendValues),
                    borderColor: '#944b1c',
                    backgroundColor: 'rgba(148, 75, 28, 0.08)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#944b1c',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: textColor, font: { size: 11, family: 'Plus Jakarta Sans' } }
                    },
                    y: {
                        grid: { color: gridColor },
                        ticks: {
                            color: textColor,
                            font: { size: 10, family: 'JetBrains Mono' },
                            callback: function(value) {
                                if (value >= 1000000) return (value / 1000000).toFixed(1) + ' jt';
                                if (value >= 1000) return (value / 1000).toFixed(0) + ' rb';
                                return value;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
