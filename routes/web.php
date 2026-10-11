<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - MyCafe POS & Loyalty System
|--------------------------------------------------------------------------
*/

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Root Redirect based on authentication & role
Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    return match (auth()->user()->role) {
        'admin'  => redirect()->route('admin.dashboard'),
        'kasir'  => redirect()->route('pos.index'),
        'owner'  => redirect()->route('owner.report'),
        'member' => redirect()->route('member.profile'),
        default  => redirect()->route('login'),
    };
})->name('home');

// Member Registration (Accessible publicly or by staff)
Route::get('/members/create', [MemberController::class, 'create'])->name('members.create');
Route::post('/members/store', [MemberController::class, 'store'])->name('members.store');

// Logout
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware('auth')->group(function () {

    // Executive & Management Dashboards (Admin & Owner)
    Route::middleware('role:admin,owner')->group(function () {
        Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');
        Route::get('/reports/business', [DashboardController::class, 'businessReport'])->name('reports.business');
        Route::get('/reports/owner', [DashboardController::class, 'ownerReport'])->name('owner.report');
    });

    // Cashier Point of Sale (Admin, Owner & Kasir)
    Route::middleware('role:admin,owner,kasir')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('/pos/search-member', [PosController::class, 'searchMember'])->name('pos.search-member');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    });

    // Transactions History (Admin, Owner, Kasir, Member)
    Route::middleware('role:admin,owner,kasir,member')->group(function () {
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    });

    // Members Directory & Management (Admin, Owner, Kasir, Member)
    Route::middleware('role:admin,owner,kasir')->group(function () {
        Route::get('/members', [MemberController::class, 'index'])->name('members.index');
        Route::post('/members/update', [MemberController::class, 'update'])->name('members.update');
        Route::post('/members/adjust-points', [MemberController::class, 'adjustPoints'])->name('members.adjust-points');
        Route::post('/members/delete', [MemberController::class, 'destroy'])->name('members.delete');
    });
    Route::middleware('role:admin,owner,kasir,member')->group(function () {
        Route::get('/members/view/{id}', [MemberController::class, 'show'])->name('members.show');
    });

    // Member Loyalty Card & Customer Profile (Member, Admin, Owner, Kasir)
    Route::middleware('role:admin,owner,kasir,member')->group(function () {
        Route::get('/member/profile', [MemberController::class, 'profile'])->name('member.profile');
    });

    // Rewards Management (Admin & Owner)
    Route::middleware('role:admin,owner')->group(function () {
        Route::get('/admin/rewards', [RewardController::class, 'manage'])->name('rewards.manage');
        Route::post('/admin/rewards/store', [RewardController::class, 'store'])->name('rewards.store');
        Route::post('/admin/rewards/toggle/{id}', [RewardController::class, 'toggle'])->name('rewards.toggle');
        Route::post('/admin/rewards/delete/{id}', [RewardController::class, 'destroy'])->name('rewards.delete');
    });

    // Reward Redemption Catalog (Member, Admin, Kasir, Owner)
    Route::middleware('role:admin,owner,kasir,member')->group(function () {
        Route::get('/rewards/catalog', [RewardController::class, 'catalog'])->name('rewards.catalog');
        Route::post('/rewards/redeem', [RewardController::class, 'redeem'])->name('rewards.redeem');
    });
});
