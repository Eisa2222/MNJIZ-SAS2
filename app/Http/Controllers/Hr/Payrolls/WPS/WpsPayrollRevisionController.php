<?php

namespace App\Http\Controllers\Hr\Payrolls\WPS;

use App\DataTables\Hr\Payrolls\WPS\WpsPayrollRevisionDataTable;
use App\Enums\Hr\Payrolls\WPS\WpsPayrollDetailRevisionStatus;
use App\Enums\Hr\Payrolls\WPS\WpsPayrollDetailStatus;
use App\Enums\Hr\Payrolls\WPS\WpsStatus;
use App\Http\Controllers\Controller;
use App\Models\Hr\Employees\Employees;
use App\Models\Hr\Payrolls\WPS\WpsPayroll;
use App\Models\Hr\Payrolls\WPS\WpsPayrollDetail;
use App\Models\Hr\Payrolls\WPS\WpsPayrollDetailRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Yajra\DataTables\DataTables;

class WpsPayrollRevisionController extends Controller
{
    private $page = "hr.payrolls.wps.details.revision";

    /*
    |============================================================================
    | create
    |============================================================================
    */
    public function create(WpsPayroll $wps_payroll, Employees $employee)
    {
        if (!$wps_payroll->canBeApproved()) {
            abort(404);
        }
        $wps_payroll_details = WpsPayrollDetail::where('wps_payroll_id', $wps_payroll->id)->where('employee_id', $employee->id)->first();
        return view('hr.payrolls.wps.details.revision.create', compact('wps_payroll_details'));
    }


    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(Request $request, WpsPayroll $wps_payroll, Employees $employee)
    {
        if (!$wps_payroll->canBeApproved()) {
            abort(404);
        }

        $detail = WpsPayrollDetail::where([
            ['wps_payroll_id', $wps_payroll->id],
            ['employee_id',   $employee->id],
        ])->firstOrFail();

        $data = $request->validate([
            'basic'      => ['required', 'numeric', 'min:0'],
            'transport'  => ['required', 'numeric', 'min:0'],
            'housing'    => ['required', 'numeric', 'min:0'],
            'other'      => ['required', 'numeric', 'min:0'],
            'deductions' => ['required', 'numeric', 'min:0'],
            'insurance'  => ['required', 'numeric', 'min:0'],
            'incentives' => ['required', 'numeric', 'min:0'],
            'notes'      => ['nullable', 'string', 'max:1000'],
        ]);

        WpsPayrollDetailRevision::create([
            'wps_payroll_detail_id' => $detail->id,
            'old_values'            => $detail->only(array_keys($data)),
            'new_values'            => Arr::except($data, ['notes']),
            'notes'                 => $data['notes'] ?? null,
            'status'                => 'pending',
            'edited_by'             => auth()->user()->employee->id,
        ]);

        // تعديل حالة تفاصيل المرتب
        $this->updateWpsDetailStatus($detail);

        // لتغيير حالة المسير اذا تم اضافة اي طلب
        $this->updateWpsStatus($detail);


        return redirect()->route('hr.payrolls.wps.details.revision.show', [$wps_payroll->id, $employee->id])->with('success', 'تم تقديم طلب المراجعة بنجاح.');
    }


    /*
    |============================================================================
    | show
    |============================================================================
    */
    public function show(WpsPayroll $wps_payroll, Employees $employee, WpsPayrollRevisionDataTable $dataTable)
    {
        $wps_payroll_details = WpsPayrollDetail::where([
            ['wps_payroll_id', $wps_payroll->id],
            ['employee_id', $employee->id],
        ])->firstOrFail();

        $dataTable->wps_payroll_details = $wps_payroll_details;

        return $dataTable->render($this->page . '.show', compact(
            'wps_payroll_details',
        ));
    }


    /*
    |============================================================================
    | edit
    |============================================================================
    */
    public function edit(Request $request, WpsPayroll $wps_payroll, Employees $employee, $id)
    {
        if (!$wps_payroll->canBeApproved()) {
            abort(404);
        }

        $wps_payroll_details = WpsPayrollDetail::where('wps_payroll_id', $wps_payroll->id)->where('employee_id', $employee->id)->first();
        $revision = WpsPayrollDetailRevision::find($id);

        // تحقق أنّه لا يزال في حالة انتظار
        $this->checkPending($revision);

        return view('hr.payrolls.wps.details.revision.edit', compact('wps_payroll_details', 'revision'));
    }

    /*
    |============================================================================
    | update
    |============================================================================
    */
    public function update(Request $request, WpsPayroll $wps_payroll, Employees $employee, $id)
    {
        if (!$wps_payroll->canBeApproved()) {
            abort(404);
        }

        // جلب سجل التفاصيل
        $detail = WpsPayrollDetail::where([
            ['wps_payroll_id', $wps_payroll->id],
            ['employee_id',   $employee->id],
        ])->firstOrFail();

        $revision = WpsPayrollDetailRevision::find($id);

        // تحقق أنّه لا يزال في حالة انتظار
        $this->checkPending($revision);

        // التحقق من صحة البيانات
        $data = $request->validate([
            'basic'      => ['required', 'numeric', 'min:0'],
            'transport'  => ['required', 'numeric', 'min:0'],
            'housing'    => ['required', 'numeric', 'min:0'],
            'other'      => ['required', 'numeric', 'min:0'],
            'deductions' => ['required', 'numeric', 'min:0'],
            'insurance'  => ['required', 'numeric', 'min:0'],
            'incentives' => ['required', 'numeric', 'min:0'],
            'notes'      => ['nullable', 'string', 'max:1000'],
        ]);

        // إنشاء سجل المراجعة المؤقت (Revision)
        $revision->update([
            'old_values'            => $detail->only(array_keys($data)),
            'new_values'            => Arr::except($data, ['notes']),
            'notes'                 => $data['notes'] ?? $revision->notes,
        ]);

        // تعديل حالة تفاصيل المرتب
        $this->updateWpsDetailStatus($detail);


        return redirect()->route('hr.payrolls.wps.details.revision.show', [$wps_payroll->id, $employee->id])->with('success', 'تم تعديل طلب المراجعة بنجاح.');
    }

    /*
    |============================================================================
    | destroy
    |============================================================================
    */
    public function destroy(Request $request, WpsPayroll $wps_payroll, Employees $employee, $id)
    {
        if (!$wps_payroll->canBeApproved()) {
            abort(404);
        }

        $revision = WpsPayrollDetailRevision::find($id);

        $detail = WpsPayrollDetail::where([
            ['wps_payroll_id', $wps_payroll->id],
            ['employee_id',   $employee->id],
        ])->firstOrFail();

        // تحقق أنّه لا يزال في حالة انتظار
        $this->checkPending($revision);

        $revision->delete();

        // تعديل حالة تفاصيل المرتب
        $this->updateWpsDetailStatus($detail);



        return redirect()->route('hr.payrolls.wps.details.revision.show', [$wps_payroll->id, $employee->id])->with('success', 'تم حذف طلب المراجعة بنجاح.');
    }



    /*
    |============================================================================
    |============================================================================
    |                          private functions
    |============================================================================
    |============================================================================
    */
    // لتعديل حالة تفاصيل المرتب للموظف
    private function updateWpsDetailStatus(WpsPayrollDetail $detail): void
    {
        $latest = $detail->WpsPayrollRevision()
            ->orderByDesc('created_at')
            ->first();

        if (! $latest) {
            $detail->update(['status' => WpsPayrollDetailStatus::NotRequested]);
            return;
        }

        switch ($latest->status) {
            case WpsPayrollDetailRevisionStatus::Pending:
                $newStatus = WpsPayrollDetailStatus::Pending;
                break;

            case WpsPayrollDetailRevisionStatus::Approved:
                $newStatus = WpsPayrollDetailStatus::ReviewedApproved;
                break;

            case WpsPayrollDetailRevisionStatus::Rejected:
                $newStatus = WpsPayrollDetailStatus::ReviewedRejected;
                break;

            default:
                $newStatus = WpsPayrollDetailStatus::NotRequested;
                break;
        }

        $detail->update(['status' => $newStatus]);
    }

    // لتعديل حالة المسير
    private function updateWpsStatus(WpsPayrollDetail $detail)
    {
        $hasAnyRevision = $detail->WpsPayrollRevision()->exists();

        if ($hasAnyRevision) {
            $detail->wpsPayroll->update([
                'status' => WpsStatus::Modified,
            ]);
        }
    }

    private function checkPending(WpsPayrollDetailRevision $revision): void
    {
        if (! $revision->isPending()) {
            abort(
                redirect()->back()->with(
                    'error',
                    'لا يمكن التعامل مع هذا الطلب لأنه  ' .
                        ($revision->status === WpsPayrollDetailRevisionStatus::Approved
                            ? 'تمت الموافقة عليه'
                            : 'تم رفضه'
                        ) .
                        '.'
                )
            );
        }
    }
}
