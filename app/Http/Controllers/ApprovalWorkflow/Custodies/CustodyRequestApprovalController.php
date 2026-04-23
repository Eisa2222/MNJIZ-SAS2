<?php

namespace App\Http\Controllers\ApprovalWorkflow\Custodies;

use App\Http\Controllers\ApprovalWorkflow\BaseApprovalController;
use App\Models\ElectronicServices\Custody\Request\CustodyRequest;
use App\Models\Hr\Employees\Employees;
use App\Enums\ElectronicServices\Custody\Requests\CustodyRequestType;
use App\DataTables\ApprovalWorkflow\Custodies\CustodyRequestApprovalDataTable;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class CustodyRequestApprovalController extends BaseApprovalController
{

    /*
    |--------------------------------------------------------------------------
    | عرض صفحة الفهرس أو إرجاع بيانات AJAX لـ DataTable.
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): View|JsonResponse
    {
        $dataTable = app(CustodyRequestApprovalDataTable::class);

        if ($request->ajax()) {
            return $dataTable->ajax();
        }

        return $dataTable->render(
            $this->getIndexViewName(),
            array_merge([
                'statistics'    => $this->getStatistics(),
                'routeBaseName' => $this->getRouteBaseName(),
            ], $this->getAdditionalData())
        );
    }

    /*
    |--------------------------------------------------------------------------
    | تحديد المودل الرئيسي لهذه الوحدة.
    |--------------------------------------------------------------------------
    */
    protected function getModel(): string
    {
        return CustodyRequest::class;
    }

    /*
    |--------------------------------------------------------------------------
    | تحديد نوع تدفق الاعتماد.
    |--------------------------------------------------------------------------
    */
    protected function getFlowType(): string
    {
        return 'custody';
    }

    /*
    |--------------------------------------------------------------------------
    | تحديد اسم الصلاحية المطلوبة للوصول لهذه الصفحة.
    |--------------------------------------------------------------------------
    */
    // protected function getPermissionName(): string
    // {
    //     return 'إعتماد العروض';
    // }

    /*
    |--------------------------------------------------------------------------
    |  تحديد الاسم الأساسي للمسارات (Routes).
    |--------------------------------------------------------------------------
    */
    protected function getRouteBaseName(): string
    {
        return 'approval-workflow.custodies';
    }

    /*
    |--------------------------------------------------------------------------
    | تحديد اسم ملف الـ view لصفحة الفهرس.
    |--------------------------------------------------------------------------
    */
    protected function getIndexViewName(): string
    {
        return 'approval-workflow.custodies.index';
    }

    /*
    |--------------------------------------------------------------------------
    | تحديد اسم ملف الـ view لصفحة التفاصيل.
    |--------------------------------------------------------------------------
    */
    protected function getShowViewName(): string
    {
        return 'approval-workflow.custodies.show';
    }

    /*
    |--------------------------------------------------------------------------
    | جلب البيانات الإضافية اللازمة للفلاتر في صفحة الفهرس.
    |--------------------------------------------------------------------------
    */
    protected function getAdditionalData(): array
    {
        return [
            'employees' => Employees::active()->select('id', 'name', 'nickname')->get(),
            'requestTypes' => CustodyRequestType::options(),
        ];
    }
}
