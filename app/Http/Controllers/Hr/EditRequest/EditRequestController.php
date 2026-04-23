<?php

namespace App\Http\Controllers\Hr\EditRequest;


use App\Data\Hr\Deductions\DeductionData;
use App\DataTables\Hr\EditRequest\EditRequestDataTable;
use App\Enums\Hr\EditRequest\EditRequestStatus;
use App\Enums\Hr\EditRequest\RequestFieldStatus;
use App\Http\Controllers\Controller;
use App\Models\ElectronicServices\EditRequest\EmployeeEditRequest;
use App\Models\ElectronicServices\EditRequest\EmployeeEditRequestField;
use App\Models\Hr\Employees\Employees;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class EditRequestController extends Controller
{
    private $route = "hr.modification-requests";
    private $page  = "hr.edit_request";


    public function __construct()
    {
        $this->middleware('can:طلبات تحديث البيانات');
    }


    public function index(EditRequestDataTable $dataTable)
    {

        try {
            // Statistics
            // $statusCounts = EmployeeEditRequest::query()
            //     ->select('status')
            //     ->selectRaw('COUNT(*) as count')
            //     ->groupBy('status')
            //     ->pluck('count', 'status')
            //     ->toArray();

            // $totalRequests = array_sum($statusCounts);
            // $pendingRequests = $statusCounts[EditRequestStatus::Pending->value] ?? 0;
            // $approvedRequests = $statusCounts[EditRequestStatus::Approved->value] ?? 0;
            // $rejectedRequests = $statusCounts[EditRequestStatus::Rejected->value] ?? 0;

            // // Filters
            // // $employees              = Employees::active()->select('id', 'name', 'nickname')->get();
            // // $deductionTypes         = DeductionType::options();
            // // $deductionStatus        = DeductionStatus::options();


            return $dataTable->render($this->page . '.index');
            // return $dataTable->render($this->page . '.index', compact(
            //     // Statistics
            //     'totalRequests',
            //     'pendingRequests',
            //     'approvedRequests',
            //     'rejectedRequests',
            //     // Filters
            //     'employees',
            //     'deductionTypes',
            //     'deductionStatus'
            // ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }

    public function show(EmployeeEditRequest $request)
    {
        $approved   =  $request->fields->where('status', RequestFieldStatus::Approved)->count();
        $rejected   =  $request->fields->where('status', RequestFieldStatus::Rejected)->count();

        return view($this->page . '.show', compact('request', 'approved', 'rejected'));
    }

    public function approveField(EmployeeEditRequestField $field)
    {
        $this->saveFieldAndRequest($field, RequestFieldStatus::Approved);

        return response()->json([
            'success' => true,
            'message' => 'تمت الموافقة على الحقل بنجاح'
        ]);
    }

    public function rejectField(EmployeeEditRequestField $field)
    {
        $this->saveFieldAndRequest($field, RequestFieldStatus::Rejected);

        return response()->json([
            'success' => true,
            'message' => 'تم رفض الحقل بنجاح'
        ]);
    }


    /*
    |============================================================================
    |============================================================================
    |                           Private Methods
    |============================================================================
    |============================================================================
    */
    private function saveFieldAndRequest(EmployeeEditRequestField $field, RequestFieldStatus $newFieldStatus): void
    {
        $field->status      = $newFieldStatus->value;
        $field->reviewed_by = Auth::user()->employee->id;
        $field->save();

        $column   = $field->field_name->value;
        $oldValue = $field->old_value;
        $newValue = $field->new_value;

        $employee   = Employees::find($field->request->employee_id);
        $folderName = 'employees/' . Str::slug($employee->name, '_');

        if ($newFieldStatus === RequestFieldStatus::Approved) {


            $fileFields = [
                'resume'                      => 'resume',
                'qualification_certificate'   => 'qualification_certificate',
                'contract_attachment'         => 'contract_attachment',
                'id_attachment'               => 'id_attachment',
                'bank_account_attachment'     => 'bank_account_attachment',
                'national_address_attachment' => 'national_address_attachment',
                'signature'                   => 'signature',
            ];

            if (isset($fileFields[$column]) && Storage::disk('public')->exists($newValue)) {
                $subdir        = $fileFields[$column];
                $filename      = basename($newValue);
                $permanentPath = "{$folderName}/{$subdir}/{$filename}";
                Storage::disk('public')->copy($newValue, $permanentPath);

                $employee->{$column} = $permanentPath;
                $employee->save();
            }
            // نص عادى
            elseif (!isset($fileFields[$column])) {
                $employee->{$column} = $newValue;
                $employee->save();
            }
        } elseif ($newFieldStatus === RequestFieldStatus::Rejected) {

            // // لو كان حقل مرفق
            // if (isset($fileFields[$column])) {
            //     // مسار النسخة المنسوخة
            //     $subdir        = $fileFields[$column];
            //     $filename      = basename($newValue);
            //     $permanentPath = "{$folderName}/{$subdir}/{$filename}";
            // }

            // أعد العمود إلى القديم
            $employee->{$column} = $oldValue;
            $employee->save();
        }

        $request             = $field->request;
        $request->updated_by = Auth::user()->employee->id;
        $request->updated_at = now();
        $request->save();

        $requestId     = $request->id;
        $allCount      = EmployeeEditRequestField::where('edit_request_id', $requestId)->count();

        $approvedCount = EmployeeEditRequestField::where('edit_request_id', $requestId)->where('status', RequestFieldStatus::Approved->value)->count();
        $rejectedCount = EmployeeEditRequestField::where('edit_request_id', $requestId)->where('status', RequestFieldStatus::Rejected->value)->count();
        $pendingCount  = EmployeeEditRequestField::where('edit_request_id', $requestId)->where('status', RequestFieldStatus::Pending->value)->count();

        if ($approvedCount === $allCount) {
            $newRequestStatus = EditRequestStatus::Approved;
        } elseif ($rejectedCount === $allCount) {
            $newRequestStatus = EditRequestStatus::Rejected;
        } elseif ($pendingCount === $allCount) {
            $newRequestStatus = EditRequestStatus::Pending;
        } else {
            $newRequestStatus = EditRequestStatus::PartiallyApproved;
        }

        $request->status = $newRequestStatus->value;
        $request->save();
    }
}
