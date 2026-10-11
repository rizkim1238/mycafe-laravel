@extends('layouts.app')

@section('title', 'Laporan Analisis Bisnis - MyCafe')

@section('styles')
<style>
    @media print {
        aside, header, nav, .action-buttons, #global-topbar { display: none !important; }
        main { margin: 0 !important; padding: 0 !important; }
        body { background: white !important; color: black !important; }
    }
</style>
@endsection

@section('content')
<!-- Main Content Area -->
<main class="flex-1 flex flex-col min-w-0 min-h-screen lg:h-screen lg:overflow-y-auto">
    <!-- Topbar -->
    @include('layouts.topbar', [
        'pageTitle' => 'Laporan Analisis Bisnis',
        'pageSubtitle' => 'Analisis komprehensif retensi pelanggan, liabilitas poin, dan performa penjualan MyCafe.',
        'topbarActions' => [
            ['url' => route('reports.business', ['export' => 'excel']), 'label' => 'Unduh CSV', 'icon' => 'download', 'class' => 'px-3 py-1.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition shadow-subtle'],
            ['url' => 'javascript:window.print()', 'onclick' => 'window.print()', 'label' => 'Cetak Laporan', 'icon' => 'print', 'class' => 'px-3.5 py-1.5 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition shadow-subtle active:scale-95']
        ]
    ])

    <div class="p-4 sm:p-6 lg:p-8 space-y-6 flex-1">
        <!-- KPI Cards -->
        <section class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('rewards.manage') }}" class="card-hover bg-white p-5 rounded-2xl border border-slate-200 shadow-subtle block group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center shrink-0 border border-amber-500/20">
                        <span class="material-icons text-2xl">savings</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Poin Belum Ditukar</p>
                        <h3 class="text-2xl font-black text-slate-900 mt-0.5 font-mono">
                            {{ number_format($totalPoinBelumDitukar) }} <span class="text-xs font-normal text-slate-400">pts</span>
                        </h3>
                        <div class="flex items-center justify-between mt-1 text-[11px] text-slate-400">
                            <span>Liabilitas loyalitas aktif</span>
                            <span class="font-semibold text-primary group-hover:translate-x-0.5 transition-transform">Katalog &rarr;</span>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('transactions.index') }}" class="card-hover bg-white p-5 rounded-2xl border border-slate-200 shadow-subtle block group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-500/20">
                        <span class="material-icons text-2xl">point_of_sale</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Rata-rata Transaksi Member</p>
                        <h3 class="text-2xl font-black text-slate-900 mt-0.5 font-mono">
                            {{ formatRupiah($avgTrxMember) }}
                        </h3>
                        <div class="flex items-center justify-between mt-1 text-[11px] text-slate-400">
                            <span>Basket size per kunjungan</span>
                            <span class="font-semibold text-emerald-600 group-hover:translate-x-0.5 transition-transform">Riwayat &rarr;</span>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('members.index') }}" class="card-hover bg-white p-5 rounded-2xl border border-slate-200 shadow-subtle block group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-sky-500/10 text-sky-600 flex items-center justify-center shrink-0 border border-sky-500/20">
                        <span class="material-icons text-2xl">trending_up</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pertumbuhan Member</p>
                        <h3 class="text-2xl font-black text-slate-900 mt-0.5 font-mono">
                            {{ ($growthPct >= 0 ? '+' : '') . $growthPct }}%
                        </h3>
                        <div class="flex items-center justify-between mt-1 text-[11px] text-slate-400">
                            <span>Pertumbuhan bulan ini</span>
                            <span class="font-semibold text-primary group-hover:translate-x-0.5 transition-transform">Member &rarr;</span>
                        </div>
                    </div>
                </div>
            </a>
        </section>

        <!-- Charts & Highlights -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- 6-Month Sales Trend Chart -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-subtle flex flex-col justify-between">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Tren Penjualan 6 Bulan Terakhir</h2>
                        <p class="text-xs text-slate-400">Total omset pendapatan bulanan</p>
                    </div>
                    <a href="{{ route('transactions.index') }}" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                        <span>Detail</span>
                        <span class="material-icons text-sm">arrow_forward</span>
                    </a>
                </div>
                <div class="h-60 relative w-full">
                    <canvas id="businessTrendChart"></canvas>
                </div>
            </div>

            <!-- Top Rewards Redeemed -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-subtle flex flex-col justify-between">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Reward Paling Populer</h2>
                        <p class="text-xs text-slate-400">Katalog hadiah yang paling sering ditebus</p>
                    </div>
                    <a href="{{ route('rewards.manage') }}" class="text-primary text-xs font-bold hover:underline flex items-center gap-1">
                        <span>Kelola Reward</span>
                        <span class="material-icons text-sm">arrow_forward</span>
                    </a>
                </div>
                <div class="space-y-3.5 flex-1 justify-center flex flex-col">
                    @if ($topRewards->isEmpty())
                        <p class="text-xs text-slate-400 text-center py-8">Belum ada penukaran reward yang dicatat.</p>
                    @else
                        @php
                            $maxCnt = $topRewards->max('cnt') ?: 1;
                        @endphp
                        @foreach ($topRewards as $tr)
                        @php
                            $pct = max(10, round(($tr->cnt / $maxCnt) * 100));
                        @endphp
                        <a href="{{ route('rewards.manage', ['search' => $tr->name]) }}" class="block space-y-1.5 group p-2 rounded-xl hover:bg-slate-50 transition">
                            <div class="flex justify-between text-xs font-semibold">
                                <span class="text-slate-800 group-hover:text-primary transition">{{ $tr->name }} ({{ $tr->poin_cost }} pts)</span>
                                <span class="text-primary font-bold font-mono">{{ number_format($tr->cnt) }}x Ditebus</span>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-primary h-full rounded-full transition-all" style="width: {{ $pct }}%"></div>
                            </div>
                        </a>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Spending Members Leaderboard -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-subtle overflow-hidden">
            <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Top 5 Member Paling Loyal</h2>
                    <p class="text-xs text-slate-400">Pelanggan dengan poin dan transaksi tertinggi</p>
                </div>
                <a href="{{ route('members.index') }}" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                    <span>Semua Member</span>
                    <span class="material-icons text-sm">arrow_forward</span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 text-slate-400 text-[11px] uppercase font-bold tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4 text-center">Rank</th>
                            <th class="py-3 px-4">Nama Pelanggan</th>
                            <th class="py-3 px-4">Kode Member</th>
                            <th class="py-3 px-4">Tier</th>
                            <th class="py-3 px-4 font-mono">Total Transaksi</th>
                            <th class="py-3 px-4 font-mono">Total Belanja</th>
                            <th class="py-3 px-4 font-mono text-right">Saldo Poin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @php $rank = 1; @endphp
                        @foreach ($topMembers as $tm)
                        @php
                            $medal = match($rank) {
                                1 => 'bg-yellow-500/10 text-yellow-600 border border-yellow-500/20 font-black',
                                2 => 'bg-slate-300/40 text-slate-700 border border-slate-300 font-bold',
                                3 => 'bg-amber-600/10 text-amber-700 border border-amber-600/20 font-bold',
                                default => 'text-slate-400 font-bold',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs {{ $medal }}">
                                    #{{ $rank++ }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-900">
                                <a href="{{ route('members.show', $tm->id) }}" class="hover:text-primary transition">
                                    {{ $tm->name }}
                                </a>
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-500">
                                {{ $tm->member_code }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-500/10 text-amber-700 border border-amber-500/20">
                                    {{ strtoupper($tm->tier) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-700">
                                {{ number_format($tm->total_transaksi) }} kali
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                {{ formatRupiah($tm->total_belanja) }}
                            </td>
                            <td class="py-3 px-4 font-mono font-black text-amber-600 text-right">
                                {{ number_format($tm->total_poin) }} pts
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('businessTrendChart').getContext('2d');
        const gridColor = 'rgba(0, 0, 0, 0.05)';
        const textColor = '#64748b';

        const labels = @json($trend->pluck('bln'));
        const values = @json($trend->pluck('omset')->map(fn($v) => (float)$v));

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels.length ? labels : ['Bulan 1', 'Bulan 2'],
                datasets: [{
                    label: 'Omset Penjualan (Rp)',
                    data: values.length ? values : [0, 0],
                    backgroundColor: '#944b1c',
                    hoverBackgroundColor: '#7c3c14',
                    borderRadius: 8,
                    maxBarThickness: 42
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
