<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Product;
use App\Models\Reward;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function admin()
    {
        $totalMember = Member::count();
        $transaksiHariIni = Transaction::whereDate('created_at', today())->count();
        $poinTerpakai = (int) Transaction::whereDate('created_at', today())->sum('poin_redeemed');
        $pendapatanHariIni = (float) Transaction::whereDate('created_at', today())->where('status', 'completed')->sum('total');
        $totalPendapatanBulanIni = (float) Transaction::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->where('status', 'completed')
            ->sum('total');

        $trendLabels = [];
        $trendValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $trendLabels[] = $date->format('d M');
            $val = (float) Transaction::whereDate('created_at', $date->toDateString())
                ->where('status', 'completed')
                ->sum('total');
            $trendValues[] = $val;
        }

        $recentTrx = Transaction::leftJoin('members', 'transactions.member_id', '=', 'members.id')
            ->leftJoin('users as m_user', 'members.user_id', '=', 'm_user.id')
            ->leftJoin('users as cashier', 'transactions.kasir_id', '=', 'cashier.id')
            ->select('transactions.*', DB::raw("COALESCE(m_user.name, 'Pelanggan Umum') as member_name"), DB::raw("COALESCE(cashier.name, 'Kasir') as cashier_name"))
            ->orderBy('transactions.created_at', 'desc')
            ->limit(6)
            ->get();

        $rewards = Reward::withCount('redemptions as redemption_count')
            ->where('is_active', 1)
            ->orderBy('redemption_count', 'desc')
            ->orderBy('poin_cost', 'asc')
            ->limit(3)
            ->get();

        $tierCounts = Member::select('tier', DB::raw('COUNT(*) as cnt'))
            ->groupBy('tier')
            ->pluck('cnt', 'tier')
            ->toArray();

        return view('admin.dashboard', compact(
            'totalMember',
            'transaksiHariIni',
            'poinTerpakai',
            'pendapatanHariIni',
            'totalPendapatanBulanIni',
            'trendLabels',
            'trendValues',
            'recentTrx',
            'rewards',
            'tierCounts'
        ));
    }

    public function businessReport(Request $request)
    {
        $totalPoinBelumDitukar = (int) Member::sum('total_poin');
        $avgTrxMember = (float) Member::where('total_transaksi', '>', 0)
            ->select(DB::raw('COALESCE(AVG(total_belanja / NULLIF(total_transaksi,0)),0) as avg_val'))
            ->value('avg_val');

        $memberBulanIni = Member::whereYear('join_date', now()->year)->whereMonth('join_date', now()->month)->count();
        $lastMonth = now()->subMonth();
        $memberBulanLalu = Member::whereYear('join_date', $lastMonth->year)->whereMonth('join_date', $lastMonth->month)->count();
        $growthPct = $memberBulanLalu > 0 ? round(($memberBulanIni - $memberBulanLalu) / $memberBulanLalu * 100, 1) : ($memberBulanIni > 0 ? 100 : 0);

        $topMembers = Member::join('users', 'members.user_id', '=', 'users.id')
            ->select('members.*', 'users.name')
            ->orderBy('members.total_poin', 'desc')
            ->orderBy('members.total_transaksi', 'desc')
            ->limit(5)
            ->get();

        $topRewards = Reward::leftJoin('reward_redemptions', 'rewards.id', '=', 'reward_redemptions.reward_id')
            ->select('rewards.name', 'rewards.poin_cost', DB::raw('COUNT(reward_redemptions.id) as cnt'))
            ->groupBy('rewards.id', 'rewards.name', 'rewards.poin_cost')
            ->orderBy('cnt', 'desc')
            ->orderBy('rewards.poin_cost', 'asc')
            ->limit(4)
            ->get();

        $trend = Transaction::where('created_at', '>=', now()->subMonths(6))
            ->where('status', 'completed')
            ->select(DB::raw("DATE_FORMAT(created_at, '%b %Y') as bln"), DB::raw('SUM(total) as omset'), DB::raw('COUNT(id) as trx_count'), DB::raw('YEAR(created_at) as yr'), DB::raw('MONTH(created_at) as mo'))
            ->groupBy('yr', 'mo', 'bln')
            ->orderBy('yr', 'asc')
            ->orderBy('mo', 'asc')
            ->get();

        if ($request->query('export') === 'excel') {
            return response()->streamDownload(function () use ($totalPoinBelumDitukar, $avgTrxMember, $growthPct, $topMembers) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Ringkasan Bisnis MyCafe']);
                fputcsv($out, ['Total Poin Belum Ditukar', $totalPoinBelumDitukar]);
                fputcsv($out, ['Rata-rata Transaksi Member', $avgTrxMember]);
                fputcsv($out, ['Pertumbuhan Member Bulan Ini (%)', $growthPct]);
                fputcsv($out, []);
                fputcsv($out, ['Peringkat', 'Top Member', 'Total Transaksi', 'Total Poin', 'Tier']);
                $rank = 1;
                foreach ($topMembers as $tm) {
                    fputcsv($out, [$rank++, $tm->name, $tm->total_transaksi, $tm->total_poin, strtoupper($tm->tier)]);
                }
                fclose($out);
            }, 'MyCafe_Laporan_Bisnis_' . date('Ymd') . '.csv', ['Content-Type' => 'text/csv; charset=utf-8']);
        }

        return view('admin.reports.business', compact(
            'totalPoinBelumDitukar',
            'avgTrxMember',
            'growthPct',
            'topMembers',
            'topRewards',
            'trend'
        ));
    }

    public function ownerReport(Request $request)
    {
        $totalPendapatan = (float) Transaction::whereYear('created_at', now()->year)->where('status', 'completed')->sum('total');
        $totalMember     = Member::count();
        $totalTrx        = Transaction::where('status', 'completed')->count();
        $totalPoin       = (int) Member::sum('total_poin');
        $pendBulanIni    = (float) Transaction::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->where('status', 'completed')->sum('total');

        $lastMonth = now()->subMonth();
        $pendBulanLalu   = (float) Transaction::whereYear('created_at', $lastMonth->year)->whereMonth('created_at', $lastMonth->month)->where('status', 'completed')->sum('total');
        $growth = $pendBulanLalu > 0 ? round(($pendBulanIni - $pendBulanLalu) / $pendBulanLalu * 100, 1) : ($pendBulanIni > 0 ? 100 : 0);

        $categorySales = DB::table('transaction_items as ti')
            ->join('products as p', 'ti.product_id', '=', 'p.id')
            ->select('p.category', DB::raw('SUM(ti.subtotal) as omset'), DB::raw('SUM(ti.qty) as total_qty'))
            ->groupBy('p.category')
            ->orderBy('omset', 'desc')
            ->get();

        $tierStats = Member::select('tier', DB::raw('COUNT(*) as cnt'))
            ->groupBy('tier')
            ->get();

        $bigTrx = Transaction::leftJoin('members', 'transactions.member_id', '=', 'members.id')
            ->leftJoin('users as u', 'members.user_id', '=', 'u.id')
            ->leftJoin('users as k', 'transactions.kasir_id', '=', 'k.id')
            ->where('transactions.status', 'completed')
            ->select('transactions.*', DB::raw("COALESCE(u.name, 'Pelanggan Umum') as member_name"), 'members.id as member_db_id', 'members.member_code', DB::raw("COALESCE(k.name, 'Kasir') as cashier_name"))
            ->orderBy('transactions.total', 'desc')
            ->limit(6)
            ->get();

        if ($request->query('export') === 'excel') {
            return response()->streamDownload(function () use ($pendBulanIni, $totalPendapatan, $growth, $totalMember, $totalPoin, $categorySales, $bigTrx) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Ringkasan Statistik Eksekutif MyCafe']);
                fputcsv($out, ['Tanggal Unduh', date('d/m/Y H:i:s')]);
                fputcsv($out, []);
                fputcsv($out, ['Metrik Finansial', 'Nilai']);
                fputcsv($out, ['Omset Bulan Ini', $pendBulanIni]);
                fputcsv($out, ['Total Omset Tahun Ini', $totalPendapatan]);
                fputcsv($out, ['Pertumbuhan vs Bulan Lalu (%)', $growth]);
                fputcsv($out, ['Total Member Terdaftar', $totalMember]);
                fputcsv($out, ['Saldo Poin Beredar', $totalPoin]);
                fputcsv($out, []);
                fputcsv($out, ['Kategori Menu', 'Total Terjual (Qty)', 'Total Omset (Rp)']);
                foreach ($categorySales as $cs) {
                    fputcsv($out, [$cs->category, $cs->total_qty, $cs->omset]);
                }
                fputcsv($out, []);
                fputcsv($out, ['Transaksi Terbesar', 'Nama Member', 'Kode Member', 'Tanggal', 'Metode', 'Total Belanja (Rp)']);
                foreach ($bigTrx as $bt) {
                    fputcsv($out, [$bt->invoice_no, $bt->member_name, $bt->member_code ?? '-', $bt->created_at, strtoupper($bt->payment_method), $bt->total]);
                }
                fclose($out);
            }, 'MyCafe_Laporan_Eksekutif_' . date('Ymd') . '.csv', ['Content-Type' => 'text/csv; charset=utf-8']);
        }

        return view('admin.reports.owner', compact(
            'totalPendapatan',
            'totalMember',
            'totalTrx',
            'totalPoin',
            'pendBulanIni',
            'growth',
            'categorySales',
            'tierStats',
            'bigTrx'
        ));
    }
}
