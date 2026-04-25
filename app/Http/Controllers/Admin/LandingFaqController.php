<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLandingFaqRequest;
use App\Http\Requests\Admin\UpdateLandingFaqRequest;
use App\Models\LandingFaq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Phase D — Super Admin CRUD for landing-page FAQ entries.
 * Mirrors LandingFeatureController exactly — see that class for design notes.
 */
final class LandingFaqController extends Controller
{
    public function index(): View
    {
        $faqs = LandingFaq::query()->ordered()->paginate(50);

        return view('admin.landing-faqs.index', [
            'faqs'   => $faqs,
            'prefix' => $this->resolveRoutePrefix(),
        ]);
    }

    public function create(): View
    {
        return view('admin.landing-faqs.create', [
            'prefix' => $this->resolveRoutePrefix(),
        ]);
    }

    public function store(StoreLandingFaqRequest $request): RedirectResponse
    {
        $faq = LandingFaq::create($request->validated());

        return redirect()
            ->route("{$this->resolveRoutePrefix()}.landing-faqs.index")
            ->with('status', __('landing.admin.faqs.created')." (#{$faq->id})");
    }

    public function edit(LandingFaq $landing_faq): View
    {
        return view('admin.landing-faqs.edit', [
            'faq'    => $landing_faq,
            'prefix' => $this->resolveRoutePrefix(),
        ]);
    }

    public function update(UpdateLandingFaqRequest $request, LandingFaq $landing_faq): RedirectResponse
    {
        $landing_faq->update($request->validated());

        return redirect()
            ->route("{$this->resolveRoutePrefix()}.landing-faqs.index")
            ->with('status', __('landing.admin.faqs.updated'));
    }

    public function destroy(LandingFaq $landing_faq): RedirectResponse
    {
        $landing_faq->delete();

        return back()->with('status', __('landing.admin.faqs.deleted'));
    }

    public function sort(Request $request): JsonResponse
    {
        $ids = $request->input('order', []);

        if (! is_array($ids) || count($ids) === 0) {
            return response()->json(['ok' => false, 'message' => 'order array required'], 422);
        }

        DB::transaction(function () use ($ids): void {
            foreach ($ids as $position => $id) {
                LandingFaq::query()
                    ->whereKey((int) $id)
                    ->update(['sort_order' => (int) $position]);
            }
        });

        return response()->json([
            'ok'      => true,
            'message' => __('landing.admin.faqs.reordered'),
        ]);
    }

    private function resolveRoutePrefix(): string
    {
        $segment = request()->segment(1);

        return $segment === 'super-admin' ? 'super-admin' : 'admin';
    }
}
