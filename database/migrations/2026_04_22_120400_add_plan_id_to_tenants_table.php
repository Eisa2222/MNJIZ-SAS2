<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Links every tenant to a Plan.
 *
 * Nullable because (a) migrations need to run before plans seed runs, and
 * (b) Super Admin may want to temporarily un-assign a plan. Application
 * layer treats NULL plan_id as "no features" — see Tenant::hasPlan().
 *
 * Backfill to the default plan (if present) happens at the END of the
 * migration so it survives a single-shot `php artisan migrate --seed`.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'plan_id')) {
                return;
            }

            $table->foreignId('plan_id')
                ->nullable()
                ->after('status')
                ->constrained('plans')
                ->nullOnDelete();

            $table->index(['plan_id', 'status']);
        });

        // Backfill AFTER DefaultPlansSeeder runs (DatabaseSeeder order guarantees it).
        $slug = env('BILLING_DEFAULT_PLAN_SLUG', 'free');
        $plan = DB::table('plans')->where('slug', $slug)->first();

        if ($plan) {
            DB::table('tenants')->whereNull('plan_id')->update(['plan_id' => $plan->id]);
        }
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (! Schema::hasColumn('tenants', 'plan_id')) {
                return;
            }
            try { $table->dropIndex(['plan_id', 'status']); } catch (\Throwable $e) {}
            $table->dropConstrainedForeignId('plan_id');
        });
    }
};
