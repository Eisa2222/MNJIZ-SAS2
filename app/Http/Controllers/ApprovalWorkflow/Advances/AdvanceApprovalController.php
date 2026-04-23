<?php

namespace App\Http\Controllers\ApprovalWorkflow\Advances;

use App\Http\Controllers\ApprovalWorkflow\BaseApprovalController;
use App\Models\Hr\Advances\Advance;
use App\Models\Hr\Employees\Employees;
use App\Enums\Hr\Advance\AdvanceType;
use App\DataTables\ApprovalWorkflow\Advances\AdvanceApprovalsDataTable;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class AdvanceApprovalController extends BaseApprovalController
{

    /*
    |--------------------------------------------------------------------------
    | عرض صفحة الفهرس أو إرجاع بيانات AJAX لـ DataTable.
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): View|JsonResponse
    {
        $dataTable = app(AdvanceApprovalsDataTable::class);

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
        return Advance::class;
    }

    /*
    |--------------------------------------------------------------------------
    | تحديد نوع تدفق الاعتماد.
    |--------------------------------------------------------------------------
    */
    protected function getFlowType(): string
    {
        return 'advance';
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
        return 'approval-workflow.advances';
    }

    /*
    |--------------------------------------------------------------------------
    | تحديد اسم ملف الـ view لصفحة الفهرس.
    |--------------------------------------------------------------------------
    */
    protected function getIndexViewName(): string
    {
        return 'approval-workflow.advances.index';
    }

    /*
    |--------------------------------------------------------------------------
    | تحديد اسم ملف الـ view لصفحة التفاصيل.
    |--------------------------------------------------------------------------
    */
    protected function getShowViewName(): string
    {
        return 'approval-workflow.advances.show';
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
            'advanceTypes' => AdvanceType::options(),
        ];
    }
}
