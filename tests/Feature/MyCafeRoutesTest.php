<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Product;
use App\Models\Reward;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyCafeRoutesTest extends TestCase
{
    public function test_login_page_renders(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Masuk ke Dashboard');
    }

    public function test_admin_access_dashboard_and_reports(): void
    {
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $this->markTestSkipped('Admin user not found');
        }

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard Overview');

        $response = $this->actingAs($admin)->get('/reports/business');
        $response->assertStatus(200);
        $response->assertSee('Laporan Analisis Bisnis');

        $response = $this->actingAs($admin)->get('/reports/owner');
        $response->assertStatus(200);
        $response->assertSee('Laporan & Statistik Owner');
    }

    public function test_pos_and_transactions_access(): void
    {
        $kasir = User::where('role', 'kasir')->first();
        if (!$kasir) {
            $this->markTestSkipped('Kasir user not found');
        }

        $response = $this->actingAs($kasir)->get('/pos');
        $response->assertStatus(200);
        $response->assertSee('Kasir POS');

        $response = $this->actingAs($kasir)->get('/transactions');
        $response->assertStatus(200);
        $response->assertSee('Riwayat Transaksi');
    }

    public function test_members_management_and_profile(): void
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/members');
        $response->assertStatus(200);
        $response->assertSee('Data Member & Loyalitas');

        $response = $this->actingAs($admin)->get('/members/create');
        $response->assertStatus(200);
        $response->assertSee('Pendaftaran Member');

        $firstMember = Member::first();
        if ($firstMember) {
            $response = $this->actingAs($admin)->get('/members/view/' . $firstMember->id);
            $response->assertStatus(200);
        }

        $memberUser = User::where('role', 'member')->first();
        if ($memberUser) {
            $response = $this->actingAs($memberUser)->get('/member/profile');
            $response->assertStatus(200);
            $response->assertSee('Kartu Member');
        }
    }

    public function test_authentication_roles_and_redirects(): void
    {
        // Admin login
        $response = $this->post('/login', [
            'identifier' => 'admin',
            'password' => '123'
        ]);
        $response->assertRedirect('/admin/dashboard');

        // Logout
        $this->get('/logout');

        // Kasir login
        $response = $this->post('/login', [
            'identifier' => 'kasir',
            'password' => '123'
        ]);
        $response->assertRedirect('/pos');

        $this->get('/logout');

        // Owner login
        $response = $this->post('/login', [
            'identifier' => 'owner',
            'password' => '123'
        ]);
        $response->assertRedirect('/reports/owner');

        $this->get('/logout');

        // Member login
        $response = $this->post('/login', [
            'identifier' => 'budi',
            'password' => '123'
        ]);
        $response->assertRedirect('/member/profile');

        $this->get('/logout');

        // Wrong password
        $response = $this->post('/login', [
            'identifier' => 'admin',
            'password' => 'wrongpass'
        ]);
        $response->assertSessionHas('error');
    }

    public function test_pos_search_member(): void
    {
        $kasir = User::where('role', 'kasir')->first();
        $response = $this->actingAs($kasir)->postJson('/pos/search-member', [
            'keyword' => 'budi'
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    public function test_member_store_and_auto_login(): void
    {
        $uniquePhone = '0812' . rand(10000000, 99999999);
        $uniqueEmail = 'test' . rand(1000, 9999) . '@mycafe.test';

        $response = $this->post('/members/store', [
            'name' => 'Auto Login Test Member',
            'phone' => $uniquePhone,
            'email' => $uniqueEmail,
            'password' => '123',
            'tier' => 'bronze',
            'auto_login' => '1'
        ]);

        $response->assertRedirect('/member/profile');
        $this->assertAuthenticated();
    }

    public function test_pos_checkout_transaction(): void
    {
        $kasir = User::where('role', 'kasir')->first();
        $product = Product::first();
        $this->assertNotNull($product);

        $cartItems = [
            [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'qty' => 2,
                'poin_value' => $product->poin_reward ?? 5
            ]
        ];

        $response = $this->actingAs($kasir)->postJson('/pos/checkout', [
            'member_id' => 0,
            'items' => json_encode($cartItems),
            'payment' => 'cash',
            'poin_redeemed' => 0
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['success', 'invoice', 'subtotal', 'total']);
    }

    public function test_reward_redemption(): void
    {
        $member = Member::where('total_poin', '>=', 50)->first();
        if (!$member) {
            $member = Member::first();
            $member->total_poin = 500;
            $member->save();
        }

        $user = User::find($member->user_id);
        $reward = Reward::where('is_active', 1)->first();

        $response = $this->actingAs($user)->from('/rewards/catalog')->post('/rewards/redeem', [
            'reward_id' => $reward->id
        ]);

        $response->assertRedirect('/rewards/catalog');
        $response->assertSessionHas('success');
    }
}
