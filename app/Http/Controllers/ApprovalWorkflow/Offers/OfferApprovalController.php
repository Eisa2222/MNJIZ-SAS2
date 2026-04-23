<?php

namespace App\Http\Controllers\ApprovalWorkflow\Offers;

use App\DataTables\ApprovalWorkflow\Offers\OfferApprovalsDataTable;
use App\Http\Controllers\ApprovalWorkflow\BaseApprovalController;
use App\Models\OperationsCenter\Offer\Offers;
use App\Models\general_setting\SettingsTemplate;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\Hr\Employees\Employees;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;


class OfferApprovalController extends BaseApprovalController
{
    /*
    |--------------------------------------------------------------------------
    | تنفيذ الدوال المجردة
    |--------------------------------------------------------------------------
    */

    /**
     * Display the index page or return AJAX data.
     */
    public function index(Request $request): View|JsonResponse
    {
        // الحل: نقوم بجلب (resolve) كائن الداتا تيبل من حاوية الخدمات مباشرة
        // بدلاً من حقنه في بارامترات الدالة.
        $dataTable = app(OfferApprovalsDataTable::class);

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



    protected function getModel(): string
    {
        return Offers::class;
    }

    protected function getFlowType(): string
    {
        return 'offer';
    }

    // protected function getPermissionName(): string
    // {
    //     return 'إعتماد العروض';
    // }

    protected function getRouteBaseName(): string
    {
        return 'approval-workflow.offers';
    }

    protected function getAdditionalData(): array
    {
        return [
            'customers' => Customers::select('id', 'name')->get(),
            'employees' => Employees::active()->select('id', 'name', 'nickname')->get(),
        ];
    }


    protected function getIndexViewName(): string
    {
        return 'approval-workflow.offers.index';
    }

    protected function getShowViewName(): string
    {
        return 'approval-workflow.offers.show';
    }

    /*
    |--------------------------------------------------------------------------
    | دوال خاصة بالعروض
    |--------------------------------------------------------------------------
    */

    protected function getProcessedContent($offer): ?string
    {
        $templateHtml = SettingsTemplate::where('template_type', 'offers')->value('content') ?? '';

        if (empty($templateHtml)) {
            return null;
        }

        $data = $this->getOfferTemplateData($offer);

        $processedContent = $templateHtml;
        foreach ($data as $key => $value) {
            $processedContent = str_replace('{{' . $key . '}}', $value, $processedContent);
        }

        return $processedContent;
    }

    private function getOfferTemplateData(Offers $offer): array
    {
        return [
            'offer_number' => $offer->offer_number,
            'offer_name' => $offer->offer_name,
            'customer_name' => $offer->customer->name ?? 'غير محدد',
            'start_date' => $offer->start_date,
            'technical_offer' => $offer->technical_offer,
            'financial_offer' => $offer->financial_offer,
            'current_date' => Carbon::now()->toDateString(),
        ];
    }
}
