<?php

namespace App\Http\Controllers\ApprovalWorkflow\Contracts;

use App\DataTables\ApprovalWorkflow\Contracts\ContractApprovalsDataTable;
use App\Http\Controllers\ApprovalWorkflow\BaseApprovalController;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\general_setting\SettingsTemplate;
use App\Models\Hr\Employees\Employees;
use App\Models\OperationsCenter\Contract\Contract;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ContractApprovalController extends BaseApprovalController
{

    /*
    |--------------------------------------------------------------------------
    | Index method
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): View|JsonResponse
    {
        $dataTable = app(ContractApprovalsDataTable::class);

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
    | نوع التدفق (مطابق للإعدادات في الـ approval_flows)
    |--------------------------------------------------------------------------
    */
    protected function getFlowType(): string
    {
        return 'contract';
    }

    /*
    |--------------------------------------------------------------------------
    | اسم الصلاحية المطلوبة لهذه الصفحة
    |--------------------------------------------------------------------------
    */
    // protected function getPermissionName(): string
    // {
    //     return 'إعتماد العقود';
    // }

    /*
    |--------------------------------------------------------------------------
    | الموديل الذي سيُطبَّق عليه الاعتماد
    |--------------------------------------------------------------------------
    */
    protected function getModel(): string
    {
        return Contract::class;
    }

    /*
    |--------------------------------------------------------------------------
    | الاسم الأساسي للراوت (مطابق للأسماء في config.js)
    |--------------------------------------------------------------------------
    */
    protected function getRouteBaseName(): string
    {
        return 'approval-workflow.contracts';
    }

    /*
    |--------------------------------------------------------------------------
    | بيانات إضافية لتمريرها إلى الفلاتر
    |--------------------------------------------------------------------------
    */
    protected function getAdditionalData(): array
    {
        return [
            'customers' => Customers::select('id', 'name')->get(),
            // 'employees' => Employees::select('id', 'name', 'nickname')->get(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | اسم العرض (بليد) لصفحة الفهرس
    |--------------------------------------------------------------------------
    */
    protected function getIndexViewName(): string
    {
        return 'approval-workflow.contracts.index';
    }

    /*
    |--------------------------------------------------------------------------
    | اسم العرض (بليد) لصفحة التفاصيل
    |--------------------------------------------------------------------------
    */
    protected function getShowViewName(): string
    {
        return 'approval-workflow.contracts.show';
    }



    /*
    |--------------------------------------------------------------------------
    | الدوال الخاصة بالعقود (تبقى كما هي)
    |--------------------------------------------------------------------------
    */
    protected function getProcessedContent($contract): ?string
    {
        // نجلب محتوى القالب من الإعدادات
        $templateHtml = SettingsTemplate::where('template_type', 'contracts')->value('content') ?? '';
        if (empty($templateHtml)) {
            return null;
        }

        // نجهّز بيانات الاستبدال
        $data = $this->getContractTemplateData($contract);

        // نعمل استبدال لكل متغير {{key}}
        foreach ($data as $key => $value) {
            $templateHtml = str_replace('{{' . $key . '}}', $value, $templateHtml);
        }

        return $templateHtml;
    }


    /*
    |--------------------------------------------------------------------------
    | يُرجع مصفوفة متغيرات القالب وقيمها
    |--------------------------------------------------------------------------
    */
    private function getContractTemplateData(Contract $contract): array
    {
        return [
            'contract_number'        => $contract->contract_number,
            'contract_name'          => $contract->contract_name,
            'customer_name'          => $contract->customer?->name            ?? '-',
            'civil_registry'         => $contract->customer?->civil_registry ?? '-',
            'address'                => $contract->customer?->region->name    ?? '-',
            'contact_number'          => $contract->customer?->contact_number   ?? '-',
            'email'                  => $contract->customer?->email           ?? '-',
            'contract_start_date'    => $contract->contract_start_date,
            'contract_end_date'      => $contract->contract_end_date      ?? '-',
            'expected_closure_date'  => $contract->expected_closure_date,
            'technical_offer'        => $contract->contract_type === 'main'
                ? ($contract->offer?->technical_offer ?? '-')
                : ($contract->supplementary_technical_offer ?? '-'),
            'financial_offer'        => $contract->contract_type === 'main'
                ? ($contract->offer?->financial_offer ?? '-')
                : ($contract->supplementary_financial_offer ?? '-'),
            'current_date'           => Carbon::now()->toDateString(),
        ];
    }
}
