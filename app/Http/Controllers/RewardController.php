<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\PointHistory;
use App\Models\Reward;
use App\Models\RewardRedemption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RewardController extends Controller
{
    public function manage(Request $request)
    {
        $search   = clean($request->query('search', ''));
        $status   = clean($request->query('status', ''));
        $category = clean($request->query('category', ''));

        $query = Reward::withCount('redemptions as redemption_count');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('rewards.name', 'like', "%{$search}%")
                  ->orWhere('rewards.description', 'like', "%{$search}%");
            });
        }
        if ($status === 'aktif') {
            $query->where('rewards.is_active', 1);
        }
        if ($status === 'nonaktif') {
            $query->where('rewards.is_active', 0);
        }
        if ($category) {
            $query->where('rewards.category', $category);
        }

        $rewards = $query->orderBy('rewards.id', 'desc')->get();

        $totalReward  = Reward::count();
        $totalAktif   = Reward::where('is_active', 1)->count();
        $totalDitebus = RewardRedemption::count();

        $categories = Reward::whereNotNull('category')->distinct()->pluck('category')->all();

        return view('rewards.manage', compact(
            'rewards',
            'totalReward',
            'totalAktif',
            'totalDitebus',
            'categories',
            'search',
            'status',
            'category'
        ));
    }

    public function store(Request $request)
    {
        $id       = (int) $request->input('id', 0);
        $name     = clean($request->input('name', ''));
        $desc     = clean($request->input('description', ''));
        $poin     = max(1, (int) $request->input('poin_cost', 0));
        $stock    = (int) $request->input('stock', -1);
        $category = clean($request->input('category', 'Minuman'));
        $valid    = clean($request->input('valid_until', '')) ?: null;

        if (empty($name)) {
            return back()->with('error', 'Nama reward wajib diisi.');
        }

        if ($id > 0) {
            $reward = Reward::find($id);
            if ($reward) {
                $reward->update([
                    'name'        => $name,
                    'description' => $desc,
                    'poin_cost'   => $poin,
                    'stock'       => $stock,
                    'category'    => $category,
                    'valid_until' => $valid,
                ]);
                return back()->with('success', "Reward \"{$name}\" berhasil diperbarui.");
            }
        } else {
            Reward::create([
                'name'        => $name,
                'description' => $desc,
                'poin_cost'   => $poin,
                'stock'       => $stock,
                'category'    => $category,
                'valid_until' => $valid,
                'is_active'   => 1,
            ]);
            return back()->with('success', "Reward baru \"{$name}\" berhasil ditambahkan.");
        }

        return back();
    }

    public function toggle($id)
    {
        $reward = Reward::findOrFail((int) $id);
        $reward->is_active = $reward->is_active ? 0 : 1;
        $reward->save();

        return back()->with('success', 'Status keaktifan reward berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $reward = Reward::findOrFail((int) $id);
        $reward->delete();

        return back()->with('success', 'Reward berhasil dihapus dari sistem.');
    }

    public function catalog(Request $request)
    {
        $user = Auth::user();
        $member = Member::with('user')->where('user_id', $user->id)->first();
        if (!$member) {
            $member = Member::with('user')->orderBy('id', 'asc')->first();
        }

        $currentPoin = (int) ($member->total_poin ?? 0);

        $rewards = Reward::where('is_active', 1)
            ->where(function ($q) {
                $q->whereNull('valid_until')->orWhereDate('valid_until', '>=', now()->toDateString());
            })
            ->orderBy('poin_cost', 'asc')
            ->get();

        $categories = $rewards->pluck('category')->filter()->unique()->values()->all();

        return view('rewards.catalog', compact('rewards', 'categories', 'member', 'currentPoin'));
    }

    public function redeem(Request $request)
    {
        $rewardId = (int) $request->input('reward_id', 0);
        $user = Auth::user();

        $member = Member::where('user_id', $user->id)->first();
        if (!$member) {
            $member = Member::orderBy('id', 'asc')->first();
        }

        if (!$member) {
            return back()->with('error', 'Profil member tidak ditemukan.');
        }

        $reward = Reward::where('id', $rewardId)
            ->where('is_active', 1)
            ->where(function ($q) {
                $q->where('stock', -1)->orWhere('stock', '>', 0);
            })
            ->first();

        if (!$reward) {
            return back()->with('error', 'Item reward tidak tersedia.');
        }

        if ($member->total_poin < $reward->poin_cost) {
            return back()->with('error', 'Poin Anda tidak mencukupi untuk menukarkan reward ini.');
        }

        DB::beginTransaction();
        try {
            RewardRedemption::create([
                'member_id' => $member->id,
                'reward_id' => $reward->id,
                'poin_used' => $reward->poin_cost,
                'status'    => 'completed',
            ]);

            $member->total_poin -= $reward->poin_cost;
            $member->save();

            if ($reward->stock > 0) {
                $reward->decrement('stock');
            }

            $newBalance = $member->total_poin;

            PointHistory::create([
                'member_id'      => $member->id,
                'type'           => 'redeem',
                'poin'           => -$reward->poin_cost,
                'balance_after'  => $newBalance,
                'reference_type' => 'redemption',
                'description'    => 'Penukaran reward: ' . $reward->name,
            ]);

            DB::commit();

            return back()->with('success', "Selamat! Penukaran reward \"{$reward->name}\" berhasil. Silakan tunjukkan voucher ke kasir.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}
