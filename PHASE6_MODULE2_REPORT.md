# Phase 6 — Module 2: OperationsCenter (Contracts + Payments)

**Risk Tier:** HIGH (financial data — receivables, contract values, payments)
**Status:** ✅ SEALED — 114/114 tests pass (109 prior + 5 new)
**Date:** 2026-04-24

---

## 1. Bugs المكتشفة

### Discovery scan verdict
Raw query audit of every controller + service + model under `App\Http\Controllers\OperationsCenter\*`, `App\Services\OperationsCenter\*`, and `App\Models\OperationsCenter\*`:

| Risk pattern | Occurrences | Verdict |
|---|---|---|
| `DB::raw(...)` on OC tables | 0 | ✅ None |
| `DB::table(...)` on OC tables | 0 | ✅ None |
| `DB::select(...)` against OC tables | 0 | ✅ None |
| Joins that bypass Eloquent on OC tables | 0 | ✅ None |
| `sum()` / `avg()` / `groupBy()` on unscoped queries | 0 | ✅ All go through `Model::query()` → `TenantScope` |

**No production bugs were found** — the module was written idiomatically with Eloquent everywhere, so adding `BelongsToTenant` automatically scoped every existing query.

### Test-infra bugs (pre-existing, surfaced by new tests)
Not production bugs but discovered while building isolation tests:
1. `ExceptionalContract::booted()` looks up two employees by **hardcoded Arabic names** (`سعيد محمد سعيد القرني`, `حسين عبدالله علي الزهراني`). If either is missing, approvals get `NULL approver_id` → FK violation. Flagged as a separate follow-up; test works around it by seeding those employees per tenant.

---

## 2. Queries الخطرة

**Three aggregation sites** that LOOK dangerous on inspection but are all safe because they start from the Eloquent model (scope auto-applied):

| File | Line | Query | Safety |
|---|---|---|---|
| `ContractController.php` | 62–67 | `Contract::query()->select('status')->selectRaw('COUNT(*) as count')->groupBy('status')` | ✅ `TenantScope` prepends `WHERE tenant_id = ?` |
| `OfferController.php` | 53–58 | same pattern on `Offers::query()` | ✅ Scoped |
| `CustomersController.php` | 63–68 | same pattern on `Customers::query()` | ✅ Scoped (from earlier phase) |
| `ContractPayment::sum('fixed_amount')` | — | direct model aggregate | ✅ Scoped — **regression-tested** |
| `Contract::count()`, `ContractPayment::count()` | — | direct model count | ✅ Scoped — **regression-tested** |

`app/Http/Controllers/OperationsCenter/Contract/old.php` contains legacy queries (line 223+, 1329+) but is dead code — not registered in any route, kept around for reference. Left untouched.

---

## 3. Fixes

### 3.1 Migration — `2026_04_24_100000_add_tenant_id_to_operations_center_tables.php`
Idempotent schema change adding `tenant_id` + FK + indexed column to **seven tables**:

1. `contracts`
2. `offers`
3. `exceptional_contracts`
4. `exceptional_contract_approvals`
5. `contract_payments`
6. `contract_attachments`
7. `offer_approval_logs`

Backfills existing rows to `TENANCY_DEFAULT_TENANT_ID` (env-driven; default = 1) before adding the `NOT NULL` + FK constraint. Same pattern as Module 1.

### 3.2 `BelongsToTenant` trait on seven models
```php
use App\Tenancy\Concerns\BelongsToTenant;

class Contract                       extends Model { use …, BelongsToTenant; }
class ContractAttachment             extends Model { use …, BelongsToTenant; }
class ContractPayment                extends Model { use …, BelongsToTenant; }
class Offers                         extends Model { use …, BelongsToTenant; }
class ExceptionalContract            extends Model { use …, BelongsToTenant; }
class ExceptionalContractApproval    extends Model { use …, BelongsToTenant; }
class OfferApprovalLog               extends Model { use …, BelongsToTenant; }
```

The trait:
- Registers the global `TenantScope` (adds `WHERE tenant_id = current()` to every query)
- Auto-fills `tenant_id` on `creating` from `TenantContext::current()`
- Throws `RuntimeException("Cross-tenant reassignment…")` on any attempt to mutate `tenant_id` after insert
- Exposes `withoutTenancy()` escape hatch for super-admin / cross-tenant aggregates
- Wires `creating` for approvals table (auto-fills tenant from parent on `hasMany` saves)

### 3.3 `CheckContractPayments` console command
Financial cron that flips overdue payments `scheduled → late` and emails contract managers. Was a single global loop → refactored to iterate tenants:

```php
use App\Console\Concerns\IteratesTenants;
class CheckContractPayments extends Command
{
    use IteratesTenants;

    public function handle(): int
    {
        $this->perTenant(fn (Tenant $t) => $this->runForTenant($t));
        return self::SUCCESS;
    }

    private function runForTenant(Tenant $tenant): void { /* original logic, now scoped */ }
}
```

`IteratesTenants::perTenant()` re-enters `TenantContext` per tenant and restores the prior context on exception, guaranteeing no tenant-A rows leak into tenant-B's mail.

---

## 4. Tests

`tests/Feature/OperationsCenter/ContractsPaymentsIsolationTest.php` — **5 tests, 21 assertions, all passing**:

| # | Test | What it proves |
|---|---|---|
| 1 | `test_contracts_are_isolated_per_tenant` | Tenant A sees only its contracts; Tenant B sees only its own; admin `withoutTenancy()` sees both |
| 2 | **`test_contract_payment_sum_returns_only_current_tenant_total`** ⭐ | `ContractPayment::sum('fixed_amount')` under tenant A (3500) does NOT include tenant B's 99,999 payment; admin aggregate = 103,499 |
| 3 | `test_contract_payment_count_respects_scope` | `count()` under each tenant returns only its own rows |
| 4 | `test_cross_tenant_reassignment_blocked_on_payment` | Trying to change `payment.tenant_id` from A → B throws `RuntimeException("Cross-tenant reassignment…")` |
| 5 | `test_offer_and_exceptional_contract_are_isolated` | Both sibling models (`Offers`, `ExceptionalContract`) respect the scope in row count and visibility |

Test #2 is the **user-mandated critical regression** — it fails loudly (assertion error on the sum) the moment someone breaks tenant scoping on the payment aggregate.

### Full suite result
```
Tests:    114 passed (266 assertions)
Duration: 90.57s
```
No regression in the 109 pre-existing tests.

---

## 5. Risk Analysis

| Risk | Mitigation | Residual |
|---|---|---|
| Sum of tenant A's receivables shows B's | Global scope + regression test #2 | None |
| Admin dashboard accidentally filters to one tenant | `withoutTenancy()` escape hatch + explicit call site | None |
| Payment row reassigned across tenants | `BelongsToTenant` mutation block throws | None |
| Queue job processes stale tenant context | `TenantAwareJob` trait (from Phase 6 Module 1) | None |
| Scheduled command leaks tenant A into B's email | `IteratesTenants::perTenant` re-enters context; exception-safe | None |
| Future Eloquent query on these models forgets scope | **Impossible** — scope is on the Model, not the query | None |
| Future raw `DB::table('contract_payments')` query | ⚠️ Would bypass scope | Covered by PR-review checklist (no such queries today) |
| Super-admin bypass via `withoutTenancy()` misused in tenant controller | Grep shows zero current usage in OC controllers | Monitored |

---

## 6. Before / After Behavior

### Before Module 2
```php
// Tenant A user opens dashboard
ContractPayment::sum('fixed_amount')    // → 103,499 (A + B combined) ❌ LEAK
Contract::count()                       // → all tenants combined     ❌ LEAK
CheckContractPayments cron              // → mails all managers       ❌ LEAK
```

### After Module 2
```php
// Tenant A user opens dashboard
ContractPayment::sum('fixed_amount')    // → 3,500 (A only)            ✅
Contract::count()                       // → A's contracts only         ✅

// Super admin cross-tenant view (explicit)
ContractPayment::withoutTenancy()->sum('fixed_amount')   // → 103,499  ✅

// Cross-tenant reassignment attempt
$payment = ContractPayment::find($idFromA);
$payment->tenant_id = $b->id;
$payment->save();   // → RuntimeException("Cross-tenant reassignment…") ✅

// Scheduled command
php artisan contract-payments:check
// → iterates tenants, mails A's contracts only to A's managers, B's to B's ✅
```

---

## Deliverables (files changed)

**New:**
- `database/migrations/2026_04_24_100000_add_tenant_id_to_operations_center_tables.php`
- `tests/Feature/OperationsCenter/ContractsPaymentsIsolationTest.php`
- `PHASE6_MODULE2_REPORT.md` (this file)

**Modified:**
- `app/Models/OperationsCenter/Contract/Contract.php`
- `app/Models/OperationsCenter/Contract/ContractAttachment.php`
- `app/Models/OperationsCenter/Contract/Payment/ContractPayment.php`
- `app/Models/OperationsCenter/Offer/Offers.php`
- `app/Models/OperationsCenter/Offer/OfferApprovalLog.php`
- `app/Models/OperationsCenter/ExceptionalContract/ExceptionalContract.php`
- `app/Models/OperationsCenter/ExceptionalContract/ExceptionalContractApproval.php`
- `app/Console/Commands/Financial/ContractPayment/CheckContractPayments.php`

---

## Go / No-Go for Module 3

- ✅ All tests PASS (114/114)
- ✅ Zero cross-tenant leak detected
- ✅ All aggregations safe (regression-tested)
- ✅ Scheduled command tenant-aware
- ✅ Migration idempotent + backfilled

**Verdict: GO — proceed to Module 3 (LegalAffair).**
