<?php

namespace App\Http\Controllers\Qoyod\Test;

use App\Http\Controllers\Controller;
use App\Services\Qoyod\Contracts\Resources\TestResourceInterface;

class QTestController extends Controller
{
    /**
     * ============================================================================ 
     *   @param  TestResourceInterface  $test
     * ============================================================================
     */
    public function __construct(private TestResourceInterface $test) {$this->middleware('can:صلاحيات منصة قيود');}


    /*
    |============================================================================
    | index
    |============================================================================
    */
    public function index()
    {
        // $test = $this->test->find(339);
        $test = $this->test->all();
        return $test;

    //    $test = $this->test->all();
    //     $items = $test['data']      ?? $test['vendors'] ?? $test;
    //     // 2) لكل مورد جلب رصيده
    //     return collect($items)->map(function (array $vendor) {
    //         // يمكنك الكاش هنا إذا تريد:
    //         $balance = $this->test->balance($vendor['id']);
    //         $vendor['balance'] = $balance;
    //         return $vendor;
    //     });
    }

        /**
     * Show a single customer.
     */
    public function show(string $id)
    {
        try {
            $customer = $this->customers->all();
            return $customer;
        } catch (QoyodRequestException $e) {
            return back()->withErrors('خطأ في جلب بيانات العميل: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|max:255',
            'mobile' => 'nullable|string|max:20',
        ]);

        try {
            $this->customers->create($data);
            return redirect()->route('customers.index')
                ->with('success', 'تم إنشاء العميل بنجاح');
        } catch (QoyodRequestException $e) {
            return back()->withInput()
                ->withErrors('فشل إنشاء العميل: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|max:255',
            'mobile' => 'nullable|string|max:20',
        ]);

        try {
            $this->customers->update($id, $data);
            return redirect()->route('customers.show', $id)
                ->with('success', 'تم تحديث بيانات العميل');
        } catch (QoyodRequestException $e) {
            return back()->withInput()
                ->withErrors('فشل تحديث العميل: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(string $id)
    {
        try {
            $this->customers->delete($id);
            return redirect()->route('customers.index')
                ->with('success', 'تم حذف العميل');
        } catch (QoyodRequestException $e) {
            return back()->withErrors('فشل حذف العميل: ' . $e->getMessage());
        }
    }
}
