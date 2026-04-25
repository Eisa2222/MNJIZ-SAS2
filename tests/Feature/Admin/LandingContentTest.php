<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\LandingFaq;
use App\Models\LandingFeature;
use App\Models\SuperAdmin;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Phase D — Landing Content Dynamic regression suite.
 *
 *   1. Public landing page loads (HTTP 200)
 *   2. Active features visible on landing
 *   3. Active FAQs visible on landing
 *   4. Inactive features hidden from landing
 *   5. SortableJS endpoint reorders rows
 *   6. Super admin can create a feature (POST happy path)
 *   7. Super admin can update a feature
 *   8. Super admin can delete a feature
 *   9. Sort endpoint persists sort_order
 *  10. /super-admin/landing-features routes work
 *  11. /admin/landing-features routes still work
 */
final class LandingContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SystemSetting::forgetCache();
        Cache::flush();
        RateLimiter::clear('ops@mnjiz.sa|127.0.0.1');
    }

    // 1 ─────────────────────────────────────────────────────────────────
    public function test_landing_page_loads(): void
    {
        $r = $this->get('/');

        $r->assertOk();
        // Defensive fallback FAQ keys render when DB is empty.
        $r->assertSee(__('landing.features.title'), false);
    }

    // 2 ─────────────────────────────────────────────────────────────────
    public function test_active_features_render_on_landing(): void
    {
        LandingFeature::create([
            'title'       => 'AI-Drafted Briefs',
            'description' => 'Auto-generate first drafts of legal memos.',
            'icon'        => '🤖',
            'is_active'   => true,
            'sort_order'  => 0,
        ]);

        $r = $this->get('/');

        $r->assertOk();
        $r->assertSee('AI-Drafted Briefs');
        $r->assertSee('Auto-generate first drafts of legal memos.');
    }

    // 3 ─────────────────────────────────────────────────────────────────
    public function test_active_faqs_render_on_landing(): void
    {
        LandingFaq::create([
            'question'   => 'How do refunds work?',
            'answer'     => 'Cancel any time and get a pro-rated refund.',
            'is_active'  => true,
            'sort_order' => 0,
        ]);

        $r = $this->get('/');

        $r->assertOk();
        $r->assertSee('How do refunds work?');
        $r->assertSee('Cancel any time and get a pro-rated refund.');
    }

    // 4 ─────────────────────────────────────────────────────────────────
    public function test_inactive_feature_hidden_from_landing(): void
    {
        LandingFeature::create([
            'title'       => 'SECRET-INTERNAL-FEATURE-ABC',
            'description' => 'Should never appear publicly.',
            'is_active'   => false,
            'sort_order'  => 0,
        ]);

        $r = $this->get('/');

        $r->assertOk();
        $r->assertDontSee('SECRET-INTERNAL-FEATURE-ABC');
    }

    // 5 ─────────────────────────────────────────────────────────────────
    public function test_sort_endpoint_reorders_features(): void
    {
        $a = LandingFeature::create(['title' => 'A', 'description' => '...', 'sort_order' => 0, 'is_active' => true]);
        $b = LandingFeature::create(['title' => 'B', 'description' => '...', 'sort_order' => 1, 'is_active' => true]);
        $c = LandingFeature::create(['title' => 'C', 'description' => '...', 'sort_order' => 2, 'is_active' => true]);

        $super = $this->makeSuperAdmin();
        $this->actingAs($super, 'super_admin');

        // Move C → A → B (so positions become C=0, A=1, B=2).
        $r = $this->postJson('/super-admin/landing-features/sort', [
            'order' => [$c->id, $a->id, $b->id],
        ]);

        $r->assertOk();
        $r->assertJson(['ok' => true]);

        $this->assertSame(0, LandingFeature::find($c->id)->sort_order);
        $this->assertSame(1, LandingFeature::find($a->id)->sort_order);
        $this->assertSame(2, LandingFeature::find($b->id)->sort_order);
    }

    // 6 ─────────────────────────────────────────────────────────────────
    public function test_super_admin_can_create_feature(): void
    {
        $super = $this->makeSuperAdmin();
        $this->actingAs($super, 'super_admin');

        $r = $this->post('/super-admin/landing-features', [
            'title'       => 'New Block',
            'description' => 'Brand new feature description.',
            'icon'        => '✨',
            'is_active'   => 1,
            'sort_order'  => 5,
        ]);

        $r->assertRedirect();

        $row = LandingFeature::query()->where('title', 'New Block')->first();
        $this->assertNotNull($row);
        $this->assertSame('Brand new feature description.', $row->description);
        $this->assertTrue($row->is_active);
        $this->assertSame(5, $row->sort_order);
    }

    // 7 ─────────────────────────────────────────────────────────────────
    public function test_super_admin_can_update_feature(): void
    {
        $f = LandingFeature::create([
            'title' => 'Old', 'description' => 'old desc', 'is_active' => true, 'sort_order' => 0,
        ]);

        $super = $this->makeSuperAdmin();
        $this->actingAs($super, 'super_admin');

        $r = $this->put("/super-admin/landing-features/{$f->id}", [
            'title'       => 'Updated',
            'description' => 'new desc',
            'icon'        => '🔥',
            'is_active'   => 1,
            'sort_order'  => 10,
        ]);

        $r->assertRedirect();

        $f->refresh();
        $this->assertSame('Updated', $f->title);
        $this->assertSame('new desc', $f->description);
        $this->assertSame(10, $f->sort_order);
    }

    // 8 ─────────────────────────────────────────────────────────────────
    public function test_super_admin_can_delete_feature(): void
    {
        $f = LandingFeature::create([
            'title' => 'Doomed', 'description' => '...', 'is_active' => true, 'sort_order' => 0,
        ]);

        $super = $this->makeSuperAdmin();
        $this->actingAs($super, 'super_admin');

        $r = $this->delete("/super-admin/landing-features/{$f->id}");

        $r->assertRedirect();
        $this->assertDatabaseMissing('landing_features', ['id' => $f->id]);
    }

    // 9 ─────────────────────────────────────────────────────────────────
    public function test_sort_endpoint_for_faqs_persists_order(): void
    {
        $q1 = LandingFaq::create(['question' => 'Q1', 'answer' => 'A1', 'is_active' => true, 'sort_order' => 0]);
        $q2 = LandingFaq::create(['question' => 'Q2', 'answer' => 'A2', 'is_active' => true, 'sort_order' => 1]);

        $super = $this->makeSuperAdmin();
        $this->actingAs($super, 'super_admin');

        $r = $this->postJson('/super-admin/landing-faqs/sort', [
            'order' => [$q2->id, $q1->id],
        ]);

        $r->assertOk();
        $this->assertSame(0, LandingFaq::find($q2->id)->sort_order);
        $this->assertSame(1, LandingFaq::find($q1->id)->sort_order);
    }

    // 10 ────────────────────────────────────────────────────────────────
    public function test_super_admin_landing_routes_work(): void
    {
        $super = $this->makeSuperAdmin();
        $this->actingAs($super, 'super_admin');

        $this->get('/super-admin/landing-features')->assertOk();
        $this->get('/super-admin/landing-features/create')->assertOk();
        $this->get('/super-admin/landing-faqs')->assertOk();
        $this->get('/super-admin/landing-faqs/create')->assertOk();
    }

    // 11 ────────────────────────────────────────────────────────────────
    public function test_admin_landing_routes_still_work(): void
    {
        $super = $this->makeSuperAdmin();
        // Same admin row also reachable via the legacy `admin` guard
        // (Phase B: SuperAdmin extends Admin, both guards share `admins` table).
        $admin = Admin::find($super->id);
        $this->actingAs($admin, 'admin');

        $this->get('/admin/landing-features')->assertOk();
        $this->get('/admin/landing-features/create')->assertOk();
        $this->get('/admin/landing-faqs')->assertOk();
        $this->get('/admin/landing-faqs/create')->assertOk();
    }

    // ────────────────────────────────────────────────────────── helpers

    private function makeSuperAdmin(): SuperAdmin
    {
        $admin = Admin::create([
            'name'     => 'Ops Lead',
            'email'    => 'ops@mnjiz.sa',
            'password' => Hash::make('correct-horse-battery-staple'),
            'role'     => Admin::ROLE_SUPER_ADMIN,
            'status'   => Admin::STATUS_ACTIVE,
        ]);

        return SuperAdmin::find($admin->id);
    }
}
