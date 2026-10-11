<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\PointHistory;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::where('is_active', 1)->orderBy('category')->orderBy('name')->get();
        $categories = $products->pluck('category')->unique()->values()->all();

        return view('pos.index', compact('products', 'categories'));
    }

    public function searchMember(Request $request)
    {
        $q = '%' . trim($request->input('q', '')) . '%';

        $data = Member::join('users', 'members.user_id', '=', 'users.id')
            ->where(function ($query) use ($q) {
                $query->where('users.name', 'like', $q)
                      ->orWhere('users.phone', 'like', $q)
                      ->orWhere('members.member_code', 'like', $q);
            })
            ->select('members.id', 'members.member_code', 'members.tier', 'members.total_poin', 'users.name', 'users.phone')
            ->limit(8)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    public function checkout(Request $request)
    {
        $memberId   = (int) $request->input('member_id', 0) ?: null;
        $items      = json_decode($request->input('items', '[]'), true);
        $payment    = clean($request->input('payment', 'cash'));
        $poinRedeem = (int) $request->input('poin_redeemed', 0);

        if (empty($items)) {
            return response()->json(['success' => false, 'msg' => 'Keranjang pesanan masih kosong!']);
        }

        $subtotal = 0;
        $poinEarned = 0;
        foreach ($items as $item) {
            $qty   = (int) $item['qty'];
            $price = (float) $item['price'];
            $subtotal   += $qty * $price;
            $poinEarned += $qty * ((int) ($item['poin_value'] ?? 0));
        }

        // 1 point = Rp 50 discount
        $diskon = $poinRedeem > 0 ? min($poinRedeem * 50, $subtotal) : 0;
        $total  = max(0, $subtotal - $diskon);
        $invoiceNo = 'TRX-' . date('ymd') . '-' . str_pad(rand(100, 9999), 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $trx = Transaction::create([
                'invoice_no'     => $invoiceNo,
                'member_id'      => $memberId,
                'kasir_id'       => Auth::id(),
                'subtotal'       => $subtotal,
                'diskon'         => $diskon,
                'total'          => $total,
                'poin_earned'    => $poinEarned,
                'poin_redeemed'  => $poinRedeem,
                'payment_method' => $payment,
                'status'         => 'completed',
            ]);

            foreach ($items as $item) {
                TransactionItem::create([
                    'transaction_id' => $trx->id,
                    'product_id'     => $item['id'] ?? null,
                    'product_name'   => $item['name'],
                    'qty'            => $item['qty'],
                    'unit_price'     => $item['price'],
                    'subtotal'       => $item['qty'] * $item['price'],
                    'poin_per_item'  => $item['poin_value'] ?? 0,
                ]);
            }

            $newBalance = 0;
            if ($memberId) {
                $member = Member::find($memberId);
                if ($member) {
                    $member->total_poin = max(0, $member->total_poin + $poinEarned - $poinRedeem);
                    $member->total_transaksi += 1;
                    $member->total_belanja += $total;
                    $member->last_visit = now()->toDateString();
                    $member->save();
                    $newBalance = $member->total_poin;

                    if ($poinEarned > 0) {
                        PointHistory::create([
                            'member_id'      => $memberId,
                            'type'           => 'earn',
                            'poin'           => $poinEarned,
                            'balance_after'  => $newBalance,
                            'reference_type' => 'transaction',
                            'reference_id'   => $trx->id,
                            'description'    => "Transaksi $invoiceNo",
                        ]);
                    }
                    if ($poinRedeem > 0) {
                        PointHistory::create([
                            'member_id'      => $memberId,
                            'type'           => 'redeem',
                            'poin'           => -$poinRedeem,
                            'balance_after'  => $newBalance,
                            'reference_type' => 'transaction',
                            'reference_id'   => $trx->id,
                            'description'    => "Redeem diskon $invoiceNo",
                        ]);
                    }
                }
            }
            DB::commit();

            return response()->json([
                'success'      => true,
                'invoice'      => $invoiceNo,
                'subtotal'     => $subtotal,
                'diskon'       => $diskon,
                'total'        => $total,
                'poin_earned'  => $poinEarned,
                'poin_redeem'  => $poinRedeem,
                'new_balance'  => $newBalance,
                'date'         => date('d M Y, H:i'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }
}
