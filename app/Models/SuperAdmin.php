<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Phase B — spec-compliant alias for the existing Admin model.
 *
 * The reference SaaS prompt asks for a `SuperAdmin` Eloquent model bound
 * to the `super_admin` guard. We satisfy that contract WITHOUT renaming
 * the underlying table:
 *
 *   - same `admins` table (no migration, no data move)
 *   - same fillable / casts / accessors / role helpers (inherited)
 *   - new class identity → distinct provider in config/auth.php
 *
 * This means a single row in `admins` is reachable through EITHER:
 *   Auth::guard('admin')->user()        // legacy — App\Models\Admin
 *   Auth::guard('super_admin')->user()  // new    — App\Models\SuperAdmin
 *
 * Both sessions are independent (separate cookies). Logging in on one
 * does NOT authenticate the other — by design, so existing /admin
 * sessions cannot accidentally elevate into the new /super-admin area
 * and vice versa, while still letting the same admin person sign in to
 * either.
 *
 * @see App\Models\Admin
 */
class SuperAdmin extends Admin
{
    protected $table = 'admins';
}
