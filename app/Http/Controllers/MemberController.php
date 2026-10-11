<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\PointHistory;
use App\Models\Reward;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $search  = clean($request->query('search', ''));
        $tier    = clean($request->query('tier', ''));
        $status  = clean($request->query('status', ''));
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = 10;

        $query = Member::join('users', 'members.user_id', '=', 'users.id')
            ->select(
                'members.*',
                'users.name',
                'users.phone',
                'users.email',
                'users.username',
                'users.is_active'
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('users.phone', 'like', "%{$search}%")
                  ->orWhere('members.member_code', 'like', "%{$search}%");
            });
        }

        if ($tier) {
            $query->where('members.tier', $tier);
        }

        if ($status === 'active') {
            $query->where('users.is_active', 1);
        } elseif ($status === 'inactive') {
            $query->where('users.is_active', 0);
        }

        if ($request->query('export') === 'csv') {
            $exportRows = (clone $query)->orderBy('members.id', 'desc')->get();

            return response()->streamDownload(function () use ($exportRows) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Kode Member', 'Nama', 'Nomor HP', 'Email', 'Tier', 'Total Poin', 'Total Belanja', 'Total Transaksi', 'Tanggal Gabung', 'Status']);
                foreach ($exportRows as $r) {
                    fputcsv($out, [
                        $r->member_code,
                        $r->name,
                        $r->phone,
                        $r->email,
                        strtoupper($r->tier),
                        $r->total_poin,
                        $r->total_belanja,
                        $r->total_transaksi,
                        $r->join_date,
                        $r->is_active ? 'Aktif' : 'Non-aktif',
                    ]);
                }
                fclose($out);
            }, 'member_mycafe_' . date('Ymd_His') . '.csv', ['Content-Type' => 'text/csv; charset=utf-8']);
        }

        $totalRows  = (clone $query)->count();
        $totalPages = max(1, (int) ceil($totalRows / $perPage));

        $members = (clone $query)
            ->orderBy('members.id', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $tierCounts = Member::select('tier', DB::raw('COUNT(*) as cnt'))
            ->groupBy('tier')
            ->pluck('cnt', 'tier')
            ->toArray();

        $activeCount = User::where('role', 'member')->where('is_active', 1)->count();

        return view('members.index', compact(
            'members',
            'totalRows',
            'totalPages',
            'page',
            'search',
            'tier',
            'status',
            'tierCounts',
            'activeCount'
        ));
    }

    public function create()
    {
        $isLoggedInUser = Auth::check();
        $userRole       = $isLoggedInUser ? Auth::user()->role : '';
        $isStaff        = $isLoggedInUser && in_array($userRole, ['admin', 'kasir', 'owner']);

        if ($isLoggedInUser && !$isStaff) {
            return redirect()->route('member.profile');
        }

        return view('members.create', compact('isStaff'));
    }

    public function store(Request $request)
    {
        $isLoggedInUser = Auth::check();
        $userRole       = $isLoggedInUser ? Auth::user()->role : '';
        $isStaff        = $isLoggedInUser && in_array($userRole, ['admin', 'kasir', 'owner']);

        $nama      = trim($request->input('nama', $request->input('name', '')));
        $phoneRaw  = preg_replace('/\D/', '', $request->input('phone', ''));
        $email     = trim($request->input('email', ''));
        $username  = strtolower(trim($request->input('username', '')));
        $password  = trim($request->input('password', '123'));
        $level     = $request->input('level', 'bronze');
        $autoLogin = $request->boolean('auto_login') || !$isStaff;

        $errors = [];
        if ($nama === '') {
            $errors[] = 'Nama lengkap wajib diisi.';
        }
        if (strlen($phoneRaw) < 8) {
            $errors[] = 'Nomor HP tidak valid (minimal 8 digit).';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid.';
        }
        if (!in_array($level, ['bronze', 'silver', 'gold', 'platinum'])) {
            $level = 'bronze';
        }

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        try {
            DB::beginTransaction();

            if ($username === '') {
                $cleanUser = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nama));
                if (strlen($cleanUser) < 3) {
                    $cleanUser = 'member';
                }
            } else {
                $cleanUser = preg_replace('/[^a-zA-Z0-9_]/', '', $username);
                if (strlen($cleanUser) < 3) {
                    $cleanUser = 'member';
                }
            }

            $countTaken = User::where('username', $cleanUser)->count();
            $finalUsername = $countTaken > 0 ? substr($cleanUser, 0, 10) . rand(10, 999) : $cleanUser;

            $rawPass = $password !== '' ? $password : '123';
            $hashedPassword = Hash::make($rawPass);
            $emailVal = $email ?: ($finalUsername . '@mycafe.local');
            $cleanPhone = '0' . ltrim($phoneRaw, '0');

            $user = User::create([
                'name'      => $nama,
                'email'     => $emailVal,
                'username'  => $finalUsername,
                'password'  => $hashedPassword,
                'role'      => 'member',
                'phone'     => $cleanPhone,
                'is_active' => 1,
            ]);

            $code = 'MBR-' . str_pad($user->id, 4, '0', STR_PAD_LEFT);

            Member::create([
                'user_id'         => $user->id,
                'member_code'     => $code,
                'tier'            => $level,
                'join_date'       => now()->toDateString(),
                'total_poin'      => 0,
                'total_transaksi' => 0,
                'total_belanja'   => 0,
            ]);

            DB::commit();

            if ($autoLogin) {
                Auth::login($user);
                $request->session()->regenerate();
                return redirect()->route('member.profile')->with('success', "Selamat datang di MyCafe, {$nama}! Kartu member Anda berhasil diaktifkan. (Kode: {$code}, Username: {$finalUsername})");
            } else {
                return redirect()->route('members.index')->with('success', "Member \"{$nama}\" berhasil didaftarkan! Kode: {$code} | Username: {$finalUsername} | Password: {$rawPass}");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan data: Email atau username mungkin sudah digunakan.')->withInput();
        }
    }

    public function update(Request $request)
    {
        $memberId = (int) $request->input('member_id', 0);
        $name     = trim($request->input('name', ''));
        $phone    = trim($request->input('phone', ''));
        $email    = trim($request->input('email', ''));
        $editTier = trim($request->input('tier', 'bronze'));
        $isActive = $request->has('is_active') ? 1 : 0;

        if ($name === '') {
            return back()->with('error', 'Nama member tidak boleh kosong.');
        }

        $member = Member::find($memberId);
        if (!$member) {
            return back()->with('error', 'Data member tidak ditemukan.');
        }

        $user = User::find($member->user_id);
        if ($user) {
            $user->name      = $name;
            $user->phone     = $phone;
            $user->email     = $email;
            $user->is_active = $isActive;
            $user->save();
        }

        $member->tier = $editTier;
        $member->save();

        return back()->with('success', "Data member \"{$name}\" berhasil diperbarui.");
    }

    public function adjustPoints(Request $request)
    {
        $memberId = (int) $request->input('member_id', 0);
        $points   = (int) $request->input('points', 0);
        $reason   = trim($request->input('reason', 'Penyesuaian manual oleh admin'));

        if ($points === 0) {
            return back()->with('error', 'Jumlah penyesuaian poin tidak boleh 0.');
        }

        $member = Member::with('user')->find($memberId);
        if (!$member) {
            return back()->with('error', 'Data member tidak ditemukan.');
        }

        $newPoints = max(0, $member->total_poin + $points);
        $member->total_poin = $newPoints;
        $member->save();

        PointHistory::create([
            'member_id'      => $memberId,
            'type'           => $points > 0 ? 'earn' : 'redeem',
            'poin'           => $points,
            'balance_after'  => $newPoints,
            'reference_type' => 'adjustment',
            'description'    => $reason,
        ]);

        $sign = $points > 0 ? "+{$points}" : "{$points}";
        $memberName = $member->user->name ?? 'Member';

        return back()->with('success', "Poin member {$memberName} berhasil disesuaikan ({$sign} poin). Saldo baru: {$newPoints} poin.");
    }

    public function destroy(Request $request)
    {
        $memberId = (int) $request->input('member_id', 0);
        $member = Member::with('user')->find($memberId);

        if ($member && $member->user) {
            $name = $member->user->name;
            $member->user->delete(); // Cascades member delete
            return back()->with('success', "Data member \"{$name}\" berhasil dihapus secara permanen.");
        }

        return back()->with('error', 'Data member tidak ditemukan.');
    }

    public function profile(Request $request)
    {
        $user = Auth::user();
        $memberQueryId = (int) $request->query('id', 0);

        if ($memberQueryId > 0 && in_array($user->role, ['admin', 'owner', 'kasir'])) {
            $member = Member::with('user')->find($memberQueryId);
        } else {
            $member = Member::with('user')->where('user_id', $user->id)->first();
        }

        if (!$member) {
            $member = Member::with('user')->orderBy('id', 'asc')->first();
        }

        $recentTrx = [];
        $poinHistory = [];
        $rewards = [];

        if ($member) {
            $recentTrx = Transaction::leftJoin('users as k', 'transactions.kasir_id', '=', 'k.id')
                ->where('transactions.member_id', $member->id)
                ->select('transactions.*', DB::raw("COALESCE(k.name, 'Kasir') as cashier_name"))
                ->orderBy('transactions.created_at', 'desc')
                ->limit(5)
                ->get();

            $poinHistory = PointHistory::where('member_id', $member->id)
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();

            $rewards = Reward::where('is_active', 1)
                ->orderBy('poin_cost', 'asc')
                ->limit(4)
                ->get();
        }

        return view('members.profile', compact('member', 'recentTrx', 'poinHistory', 'rewards'));
    }

    public function show($id)
    {
        $currentUser = Auth::user();
        if ($currentUser && $currentUser->role === 'member') {
            return redirect()->route('member.profile');
        }

        $memberId = (int) $id;
        $member = Member::with('user')->find($memberId);

        if (!$member) {
            $member = Member::with('user')->orderBy('id', 'asc')->first();
        }

        $trxHistory = [];
        $poinHistory = [];

        if ($member) {
            $trxHistory = Transaction::leftJoin('users as k', 'transactions.kasir_id', '=', 'k.id')
                ->where('transactions.member_id', $member->id)
                ->select('transactions.*', DB::raw("COALESCE(k.name, 'Kasir') as cashier_name"))
                ->orderBy('transactions.created_at', 'desc')
                ->limit(10)
                ->get();

            $poinHistory = PointHistory::where('member_id', $member->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }

        return view('members.show', compact('member', 'trxHistory', 'poinHistory'));
    }
}
