<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\Billing\CouponDuration;
use App\Enums\Billing\CouponType;
use App\Models\Admin;
use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Admin coupon CRUD — exercises the routes + the Blade views + the
 * super_admin role gate.
 */
final class CouponManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_list_coupons(): void
    {
        $this->actingAs($this->superAdmin(), 'admin');

        Coupon::create($this->sampleCoupon());

        $this->get('/admin/coupons')
            ->assertOk()
            ->assertSee('SUMMER25');
    }

    public function test_super_admin_can_access_create_form(): void
    {
        $this->actingAs($this->superAdmin(), 'admin');

        $this->get('/admin/coupons/create')
            ->assertOk()
            ->assertSee('Create Coupon', false);
    }

    public function test_super_admin_can_store_a_coupon(): void
    {
        $this->actingAs($this->superAdmin(), 'admin');

        $response = $this->post('/admin/coupons', [
            'code'       => 'LAUNCH10',
            'name'       => 'Launch 10%',
            'type'       => CouponType::Percentage->value,
            'value'      => 10,
            'duration'   => CouponDuration::Once->value,
            'applies_to' => 'any',
            'is_active'  => 1,
        ]);

        $response->assertRedirect('/admin/coupons');
        $this->assertDatabaseHas('coupons', ['code' => 'LAUNCH10']);
    }

    public function test_non_super_admin_cannot_access_create_form(): void
    {
        $this->actingAs($this->regularAdmin(), 'admin');

        $this->get('/admin/coupons/create')
            ->assertStatus(403);
    }

    public function test_non_super_admin_cannot_store(): void
    {
        $this->actingAs($this->regularAdmin(), 'admin');

        $response = $this->post('/admin/coupons', [
            'code' => 'ANYTHING', 'name' => 'x',
            'type' => 'percentage', 'value' => 5,
            'duration' => 'once', 'applies_to' => 'any',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('coupons', ['code' => 'ANYTHING']);
    }

    public function test_regular_admin_can_view_coupon_list_but_cannot_edit(): void
    {
        $admin = $this->regularAdmin();
        $this->actingAs($admin, 'admin');

        Coupon::create($this->sampleCoupon());

        // list: allowed
        $this->get('/admin/coupons')->assertOk();

        // edit: blocked
        $coupon = Coupon::where('code', 'SUMMER25')->first();
        $this->get("/admin/coupons/{$coupon->id}/edit")->assertStatus(403);
    }

    public function test_support_cannot_manage_coupons(): void
    {
        $this->actingAs($this->supportAdmin(), 'admin');

        $this->get('/admin/coupons/create')->assertStatus(403);
    }

    public function test_unauthenticated_access_redirects_to_admin_login(): void
    {
        $this->get('/admin/coupons')->assertRedirect('/admin/login');
    }

    private function superAdmin(): Admin
    {
        return Admin::create([
            'name'     => 'Super',
            'email'    => 'super-'.uniqid().'@mnjiz.sa',
            'password' => Hash::make('x'),
            'role'     => Admin::ROLE_SUPER_ADMIN,
            'status'   => Admin::STATUS_ACTIVE,
        ]);
    }

    private function regularAdmin(): Admin
    {
        return Admin::create([
            'name'     => 'Ops',
            'email'    => 'ops-'.uniqid().'@mnjiz.sa',
            'password' => Hash::make('x'),
            'role'     => Admin::ROLE_ADMIN,
            'status'   => Admin::STATUS_ACTIVE,
        ]);
    }

    private function supportAdmin(): Admin
    {
        return Admin::create([
            'name'     => 'Sup',
            'email'    => 'sup-'.uniqid().'@mnjiz.sa',
            'password' => Hash::make('x'),
            'role'     => Admin::ROLE_SUPPORT,
            'status'   => Admin::STATUS_ACTIVE,
        ]);
    }

    private function sampleCoupon(): array
    {
        return [
            'code'       => 'SUMMER25',
            'name'       => 'Summer 25%',
            'type'       => CouponType::Percentage,
            'value'      => 25,
            'duration'   => CouponDuration::Once,
            'applies_to' => 'any',
            'is_active'  => true,
        ];
    }
}
