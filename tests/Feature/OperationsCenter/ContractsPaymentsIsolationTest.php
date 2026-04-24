<?php

declare(strict_types=1);

namespace Tests\Feature\OperationsCenter;

use App\Models\Hr\Employees\Employees;
use App\Models\OperationsCenter\Contract\Contract;
use App\Models\OperationsCenter\Contract\Payment\ContractPayment;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\OperationsCenter\ExceptionalContract\ExceptionalContract;
use App\Models\OperationsCenter\Offer\Offers;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Phase 6 Module 2 — HIGH-risk (financial) regression suite.
 *
 * Every test here encodes a failure mode that would corrupt tenant money:
 *   1. Contract / payment row isolation
 *   2. sum(amount) → tenant-scoped only
 *   3. count() → tenant-scoped only
 *   4. Cross-tenant reassignment blocked
 *   5. Offer + ExceptionalContract isolation
 *
 * Supporting rows (user/employee/customer) are created INSIDE the tenant
 * context so their tenant_id matches — avoiding FK cross-tenant leaks while
 * still exercising the scope.
 */
final class ContractsPaymentsIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_contracts_are_isolated_per_tenant(): void
    {
        [$a, $b] = $this->twoTenants();

        TenantContext::runAs($a, function () {
            $this->makeContract('CTR-A-001');
        });
        TenantContext::runAs($b, function () {
            $this->makeContract('CTR-B-001');
        });

        TenantContext::runAs($a, function () {
            $this->assertSame(1, Contract::count());
            $this->assertTrue(Contract::where('contract_number', 'CTR-A-001')->exists());
            $this->assertFalse(Contract::where('contract_number', 'CTR-B-001')->exists());
        });

        TenantContext::runAs($b, function () {
            $this->assertSame(1, Contract::count());
            $this->assertTrue(Contract::where('contract_number', 'CTR-B-001')->exists());
        });

        TenantContext::forget();
        $this->assertSame(2, Contract::withoutTenancy()->count(), 'cross-tenant admin view');
    }

    /**
     * CRITICAL: A financial aggregation (sum of payment amounts) must return
     * only the current tenant's total. A bug here = one firm's receivables
     * appear in another's dashboard.
     */
    public function test_contract_payment_sum_returns_only_current_tenant_total(): void
    {
        [$a, $b] = $this->twoTenants();

        TenantContext::runAs($a, function () {
            $c = $this->makeContract('SUM-A-1');
            $this->makePayment($c, 1000);
            $this->makePayment($c, 2000);
            $this->makePayment($c, 500);     // 3,500 total for A
        });

        TenantContext::runAs($b, function () {
            $c = $this->makeContract('SUM-B-1');
            $this->makePayment($c, 99_999);  // B must be invisible to A
        });

        TenantContext::runAs($a, function () {
            $this->assertEqualsWithDelta(
                3500,
                (float) ContractPayment::sum('fixed_amount'),
                0.01,
                'tenant A sum MUST NOT include tenant B payments'
            );
        });

        TenantContext::runAs($b, function () {
            $this->assertEqualsWithDelta(99_999, (float) ContractPayment::sum('fixed_amount'), 0.01);
        });

        // Super-admin aggregate across tenants (explicit bypass)
        TenantContext::forget();
        $this->assertEqualsWithDelta(
            103_499,
            (float) ContractPayment::withoutTenancy()->sum('fixed_amount'),
            0.01,
            'withoutTenancy() aggregates across all tenants for Super Admin'
        );
    }

    public function test_contract_payment_count_respects_scope(): void
    {
        [$a, $b] = $this->twoTenants();

        TenantContext::runAs($a, function () {
            $c = $this->makeContract('CNT-A');
            $this->makePayment($c, 100);
            $this->makePayment($c, 200);
        });
        TenantContext::runAs($b, function () {
            $c = $this->makeContract('CNT-B');
            $this->makePayment($c, 500);
            $this->makePayment($c, 600);
            $this->makePayment($c, 700);
        });

        TenantContext::runAs($a, fn () => $this->assertSame(2, ContractPayment::count()));
        TenantContext::runAs($b, fn () => $this->assertSame(3, ContractPayment::count()));

        TenantContext::forget();
        $this->assertSame(5, ContractPayment::withoutTenancy()->count());
    }

    public function test_cross_tenant_reassignment_blocked_on_payment(): void
    {
        [$a, $b] = $this->twoTenants();

        $payment = TenantContext::runAs($a, function () {
            $c = $this->makeContract('RE-A-1');
            return $this->makePayment($c, 100);
        });

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Cross-tenant reassignment/i');

        $payment->tenant_id = $b->id;
        $payment->save();
    }

    public function test_offer_and_exceptional_contract_are_isolated(): void
    {
        [$a, $b] = $this->twoTenants();

        TenantContext::runAs($a, function () {
            $this->makeOffer('OFR-A-1');
            $this->makeExceptionalContract('Exceptional A');
        });

        TenantContext::runAs($b, function () {
            $this->makeOffer('OFR-B-1');
            $this->makeExceptionalContract('Exceptional B');
        });

        TenantContext::runAs($a, function () {
            $this->assertSame(1, Offers::count());
            $this->assertSame(1, ExceptionalContract::count());
            $this->assertFalse(Offers::where('offer_number', 'OFR-B-1')->exists());
        });

        TenantContext::runAs($b, function () {
            $this->assertSame(1, Offers::count());
            $this->assertSame(1, ExceptionalContract::count());
        });

        TenantContext::forget();
        $this->assertSame(2, Offers::withoutTenancy()->count());
        $this->assertSame(2, ExceptionalContract::withoutTenancy()->count());
    }

    // ------------------------------------------------------------------ helpers

    /** @return array{0: Tenant, 1: Tenant} */
    private function twoTenants(): array
    {
        return [
            Tenant::create(['name' => 'OC-A', 'slug' => 'oc-a-'.uniqid(), 'status' => 'active']),
            Tenant::create(['name' => 'OC-B', 'slug' => 'oc-b-'.uniqid(), 'status' => 'active']),
        ];
    }

    private function makeUser(): User
    {
        return User::create([
            'name'                => 'User '.uniqid(),
            'email'               => 'u-'.uniqid().'@test.sa',
            'password'            => Hash::make('secret123'),
            'nationality'         => 'SA',
            'tour_completed'      => 1,
            'tour_task_completed' => 1,
        ]);
    }

    private function makeEmployee(): Employees
    {
        return Employees::create([
            'name'             => 'Emp '.uniqid(),
            'id_number'        => (string) random_int(1_000_000_000, 9_999_999_999),
            'work_email'       => 'emp-'.uniqid().'@test.sa',
            'insurance_status' => 'not_registered',
        ]);
    }

    private function makeCustomer(): Customers
    {
        return Customers::create([
            'name'                   => 'Test Customer '.uniqid(),
            'title'                  => 'مؤسسة',
            'contact_number'         => '0500000000',
            'email'                  => 'cust-'.uniqid().'@test.sa',
            'relationship_manager_id' => $this->makeEmployee()->id,
            'customer_type'          => 'individual',
            'created_by'             => $this->makeUser()->id,
        ]);
    }

    private function makeContract(string $number): Contract
    {
        $customer = $this->makeCustomer();

        return Contract::create([
            'contract_number'       => $number,
            'contract_name'         => 'Contract '.$number,
            'customer_id'           => $customer->id,
            'expected_closure_date' => now()->addMonths(6)->toDateString(),
            'contract_start_date'   => now()->toDateString(),
            'is_private_and_secret' => 0,
            'created_by'            => $this->makeEmployee()->id,
        ]);
    }

    private function makePayment(Contract $contract, float $amount): ContractPayment
    {
        return ContractPayment::create([
            'contract_id'        => $contract->id,
            'calculation_type'   => 'fixed',
            'payment_batch_type' => 'advance',
            'fixed_amount'       => $amount,
            'currency'           => 'SAR',
            'due_date'           => now()->addDays(7)->toDateString(),
            'status'             => 'scheduled',
            'created_by'         => $this->makeEmployee()->id,
        ]);
    }

    private function makeOffer(string $number): Offers
    {
        return Offers::create([
            'offer_number'            => $number,
            'offer_name'              => 'Offer '.$number,
            'customer_id'             => $this->makeCustomer()->id,
            'relationship_manager_id' => $this->makeEmployee()->id,
            'start_date'              => now()->toDateString(),
            'status'                  => 'under_study',
            'is_private_and_secret'   => 0,
            'created_by'              => $this->makeEmployee()->id,
        ]);
    }

    private function makeExceptionalContract(string $name): ExceptionalContract
    {
        // ExceptionalContract::booted() auto-creates approvals for two hardcoded
        // employee names. Seed them inside the current tenant so the approvals
        // pass their NOT NULL approver_id constraint.
        $this->ensureExceptionalApprovers();

        return ExceptionalContract::create([
            'contract_name'  => $name,
            'created_by_id'  => $this->makeEmployee()->id,
            'status'         => 'pending',
        ]);
    }

    private function ensureExceptionalApprovers(): void
    {
        foreach (['سعيد محمد سعيد القرني', 'حسين عبدالله علي الزهراني'] as $fullName) {
            if (! Employees::where('name', $fullName)->exists()) {
                Employees::create([
                    'name'             => $fullName,
                    'id_number'        => (string) random_int(1_000_000_000, 9_999_999_999),
                    'work_email'       => 'approver-'.uniqid().'@test.sa',
                    'insurance_status' => 'not_registered',
                ]);
            }
        }
    }
}
