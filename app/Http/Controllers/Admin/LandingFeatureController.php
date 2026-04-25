<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLandingFeatureRequest;
use App\Http\Requests\Admin\UpdateLandingFeatureRequest;
use App\Models\LandingFeature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Phase D — Super Admin CRUD for landing-page Feature blocks.
 *
 * Single controller is mounted under BOTH `/admin/landing-features`
 * (Phase 3 legacy) AND `/super-admin/landing-features` (Phase B). It uses
 * `resolveRoutePrefix()` to decide which named route to redirect back to,
 * mirroring the Phase C SystemSettingController pattern.
 *
 * The list view is server-rendered (no Yajra AJAX) — landings rarely have
 * more than ~30 entries and a SortableJS-backed plain table is simpler
 * and matches the existing admin patterns (Coupons, Tenants).
 */
final class LandingFeatureController extends Controller
{
    public function index(): View
    {
        $features = LandingFeature::query()->ordered()->paginate(50);

        return view('admin.landing-features.index', [
            'features' => $features,
            'prefix'   => $this->resolveRoutePrefix(),
        ]);
    }

    public function create(): View
    {
        return view('admin.landing-features.create', [
            'prefix' => $this->resolveRoutePrefix(),
        ]);
    }

    public function store(StoreLandingFeatureRequest $request): RedirectResponse
    {
        $feature = LandingFeature::create($request->validated());

        return redirect()
            ->route("{$this->resolveRoutePrefix()}.landing-features.index")
            ->with('status', __('landing.admin.features.created')." (#{$feature->id})");
    }

    public function edit(LandingFeature $landing_feature): View
    {
        return view('admin.landing-features.edit', [
            'feature' => $landing_feature,
            'prefix'  => $this->resolveRoutePrefix(),
        ]);
    }

    public function update(UpdateLandingFeatureRequest $request, LandingFeature $landing_feature): RedirectResponse
    {
        $landing_feature->update($request->validated());

        return redirect()
            ->route("{$this->resolveRoutePrefix()}.landing-features.index")
            ->with('status', __('landing.admin.features.updated'));
    }

    public function destroy(LandingFeature $landing_feature): RedirectResponse
    {
        $landing_feature->delete();

        return back()->with('status', __('landing.admin.features.deleted'));
    }

    /**
     * SortableJS POST — persists the user-dragged order in a single
     * transaction. Body: `{ order: [12, 4, 7, ...] }` where the array
     * position becomes the row's new `sort_order`.
     */
    public function sort(Request $request): JsonResponse
    {
        $ids = $request->input('order', []);

        if (! is_array($ids) || count($ids) === 0) {
            return response()->json(['ok' => false, 'message' => 'order array required'], 422);
        }

        DB::transaction(function () use ($ids): void {
            foreach ($ids as $position => $id) {
                LandingFeature::query()
                    ->whereKey((int) $id)
                    ->update(['sort_order' => (int) $position]);
            }
        });

        return response()->json([
            'ok'      => true,
            'message' => __('landing.admin.features.reordered'),
        ]);
    }

    /**
     * Returns 'admin' or 'super-admin' based on the current URL — keeps
     * the same controller usable from both route trees without duplicating
     * actions.
     */
    private function resolveRoutePrefix(): string
    {
        $segment = request()->segment(1);

        return $segment === 'super-admin' ? 'super-admin' : 'admin';
    }
}
