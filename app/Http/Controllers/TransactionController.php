<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = clean($request->query('date_from', ''));
        $dateTo   = clean($request->query('date_to', ''));
        $status   = clean($request->query('status', ''));
        $search   = clean($request->query('search', ''));
        $page     = max(1, (int) $request->query('page', 1));
        $perPage  = 10;

        $user = Auth::user();

        $query = Transaction::leftJoin('members', 'transactions.member_id', '=', 'members.id')
            ->leftJoin('users as m_user', 'members.user_id', '=', 'm_user.id')
            ->leftJoin('users as cashier', 'transactions.kasir_id', '=', 'cashier.id')
            ->select(
                'transactions.*',
                DB::raw("COALESCE(m_user.name, 'Pelanggan Umum') as member_name"),
                DB::raw("COALESCE(cashier.name, 'Kasir') as kasir_name")
            );

        if ($user->role === 'member') {
            $memberId = Member::where('user_id', $user->id)->value('id');
            $query->where('transactions.member_id', $memberId ?: 0);
        }

        if ($dateFrom) {
            $query->whereDate('transactions.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('transactions.created_at', '<=', $dateTo);
        }
        if ($status && $status !== 'all') {
            $query->where('transactions.status', $status);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transactions.invoice_no', 'like', "%{$search}%")
                  ->orWhere('m_user.name', 'like', "%{$search}%");
            });
        }

        // CSV Export Handler
        if ($request->query('export') === 'csv') {
            $exportTrx = (clone $query)->orderBy('transactions.created_at', 'desc')->get();

            return response()->streamDownload(function () use ($exportTrx) {
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Invoice', 'Member', 'Kasir', 'Tanggal', 'Subtotal', 'Diskon', 'Total', 'Poin Earned', 'Poin Redeem', 'Metode', 'Status']);
                foreach ($exportTrx as $row) {
                    fputcsv($output, [
                        $row->invoice_no,
                        $row->member_name,
                        $row->kasir_name,
                        $row->created_at,
                        $row->subtotal,
                        $row->diskon,
                        $row->total,
                        $row->poin_earned,
                        $row->poin_redeemed,
                        strtoupper($row->payment_method),
                        strtoupper($row->status),
                    ]);
                }
                fclose($output);
            }, 'MyCafe_Transaksi_' . date('Ymd_His') . '.csv', ['Content-Type' => 'text/csv; charset=utf-8']);
        }

        $totalRows = (clone $query)->count();
        $totalPages = max(1, (int) ceil($totalRows / $perPage));

        $transactions = (clone $query)
            ->orderBy('transactions.created_at', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $statsQuery = Transaction::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);

        if ($user->role === 'member') {
            $memberId = Member::where('user_id', $user->id)->value('id');
            $statsQuery->where('member_id', $memberId ?: 0);
        }

        $pendapatan = (float) (clone $statsQuery)->where('status', 'completed')->sum('total');
        $totalPoin = (int) (clone $statsQuery)->sum('poin_earned');

        return view('transactions.index', compact(
            'transactions',
            'totalRows',
            'totalPages',
            'page',
            'dateFrom',
            'dateTo',
            'status',
            'search',
            'pendapatan',
            'totalPoin'
        ));
    }
}
