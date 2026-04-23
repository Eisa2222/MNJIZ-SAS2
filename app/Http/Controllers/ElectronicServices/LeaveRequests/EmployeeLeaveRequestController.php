<?php

namespace App\Http\Controllers\ElectronicServices\LeaveRequests;

use App\DataTables\ElectronicServices\LeaveRequests\EmployeeLeaveRequestsDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\StoreLeaveRequest;
use App\Http\Requests\Hr\UpdateLeaveRequest;
use App\Services\ElectronicServices\LeaveRequests\Request\EmployeeLeaveRequestService;
use App\Services\LeaveRequestPolicyService;
use Illuminate\Http\Request;

class EmployeeLeaveRequestController extends Controller
{
    private $page = "electronic_services.leave_requests";

    public function __construct(
        private EmployeeLeaveRequestService $employeeLeaveRequestService
    ) {
        $this->middleware('can:طلبات الإجازات الخاصة بي')->only(['index']);
        $this->middleware('can:إضافة طلب إجازة')->only(['create', 'store']);
        $this->middleware('can:تعديل طلب الإجازة')->only(['edit', 'update']);
        $this->middleware('can:حذف طلب الإجازة')->only(['destroy']);
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    | Display a listing of employee leave requests with statistics and filters.
    */
    public function index(EmployeeLeaveRequestsDataTable $dataTable)
    {
        try {
            $data = $this->employeeLeaveRequestService->getIndexData();

            return $dataTable->render($this->page . '.index', array_merge(
                $data['statistics'],
                $data['filters']
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    | Show the form for creating a new leave request.
    */
    public function create()
    {
        $data = $this->employeeLeaveRequestService->getCreateFormData();

        return view('electronic_services.leave_requests.create', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    | Store a newly created leave request in storage.
    */
    public function store(StoreLeaveRequest $request, LeaveRequestPolicyService $policy)
    {
        try {
            $result = $this->employeeLeaveRequestService->store($request->validated(), $policy);

            $redirect = redirect()
                ->route('account.electronic-services.leave-requests.index')
                ->withSuccess($result['message']);

            if ($result['warning']) {
                $redirect->with('warning', $result['warning']);
            }

            return $redirect;
        } catch (\App\Exceptions\PolicyValidationException $e) {
            throw $e->toValidationException();
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    | Show the form for editing the specified leave request.
    */
    public function edit($id)
    {
        $data = $this->employeeLeaveRequestService->getEditFormData($id);
        return view('electronic_services.leave_requests.edit', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    | Update the specified leave request in storage.
    */
    public function update(UpdateLeaveRequest $request, $id, LeaveRequestPolicyService $policy)
    {
        try {
            $result = $this->employeeLeaveRequestService->update($request->validated(), $id, $policy);

            return redirect()
                ->route('account.electronic-services.leave-requests.index')
                ->withSuccess($result['message']);
        } catch (\App\Exceptions\PolicyValidationException $e) {
            throw $e->toValidationException();
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    | Display the specified leave request details.
    */
    public function show($id)
    {
        $data = $this->employeeLeaveRequestService->getShowData($id);

        return view('electronic_services.leave_requests.show', $data);
    }


    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    | Remove the specified leave request from storage.
    */
    public function destroy($id)
    {
        try {
            $result = $this->employeeLeaveRequestService->delete($id);

            return redirect()
                ->route('account.electronic-services.leave-requests.index')
                ->with('success', $result['message']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Mass Delete
    |--------------------------------------------------------------------------
    | Remove multiple leave requests from storage.
    */
    public function massDelete(Request $request)
    {
        $result = $this->employeeLeaveRequestService->massDelete($request->input('ids'));

        return response()->json($result);
    }
}
