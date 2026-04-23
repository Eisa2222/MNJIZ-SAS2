<?php

namespace App\Http\Controllers\Self_services;

use App\Enums\Hr\Employee\LicenseType;
use App\Helpers\SettingsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\SelfServices\SelfServiceRequest;
use App\Models\general_setting\SettingsTemplate;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\Self_services\SelfServiceRequest as Self_servicesSelfServiceRequest;
use App\Services\Common\PdfExportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SelfServicesController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | constructor
    |--------------------------------------------------------------------------
    */
    public function __construct(private PdfExportService $pdfExport)
    {
        $this->middleware('can:تعريف بالراتب')->only('salary_definition_index', 'salary_definition_submit');
        $this->middleware('can:تثبيت راتب')->only('salary_fixation_index', 'salary_fixation_submit');
        $this->middleware('can:إفادة تدريب')->only('training_certificate_index', 'training_certificate_submit');
    }


    /*
    |--------------------------------------------------------------------------
    | salary_definition_index
    |--------------------------------------------------------------------------
    */
    public function salary_definition_index()
    {
        return view('self_services.salary_definition.index');
    }


    /*
    |--------------------------------------------------------------------------
    | to create salary definition
    |--------------------------------------------------------------------------
    */
    public function salary_definition_submit(SelfServiceRequest $request)
    {
        $data  = $request->validated();

        $this->createSelfServiceRequest('salary_definition', $data['recipient']);

        return $this->exportPdf($data['recipient'], 'salary_definition', 'تعريف بالراتب');
    }


    /*
    |--------------------------------------------------------------------------
    | salary_fixation_index
    |--------------------------------------------------------------------------
    */
    public function salary_fixation_index()
    {
        return view('self_services.salary_fixation.index');
    }


    /*
    |--------------------------------------------------------------------------
    | to create salary fixation
    |--------------------------------------------------------------------------
    */
    public function salary_fixation_submit(SelfServiceRequest $request)
    {
        $data  = $request->validated();

        $this->createSelfServiceRequest('salary_fixation', $data['recipient']);

        return $this->exportPdf($data['recipient'], 'salary_fixation', 'تثبيت الراتب');
    }


    /*
    |--------------------------------------------------------------------------
    | training_report_index
    |--------------------------------------------------------------------------
    */
    public function training_certificate_index()
    {
        // هل لديه رخصة محامي متدرب
        if (Auth::user()->employee->license_type != LicenseType::LawyerTrainee) {
            abort(404);
        }
        return view('self_services.training_certificate.index');
    }


    /*
    |--------------------------------------------------------------------------
    | to create training report
    |--------------------------------------------------------------------------
    */
    public function training_certificate_submit(SelfServiceRequest $request)
    {
        // هل لديه رخصة محامي متدرب
        if (Auth::user()->employee->license_type != LicenseType::LawyerTrainee) {
            abort(404);
        }
        $data  = $request->validated();

        $this->createSelfServiceRequest('training_certificate', $data['recipient']);

        return $this->exportPdf($data['recipient'], 'training_certificate', 'إفادة تدريب');
    }

    /*
    |--------------------------------------------------------------------------
    | createSelfServiceRequest
    |--------------------------------------------------------------------------
    */
    private function createSelfServiceRequest($type, $recipient)
    {
        return Self_servicesSelfServiceRequest::create([
            'user_id' => auth()->id(),
            'request_type' => $type,
            'recipient' => $recipient,
        ]);
    }


    // export_pdf
    public function exportPdf($recipient, $type, $title_file)
    {
        try {
            $template = SettingsTemplate::where('template_type',  $type)->value('content');

            $data = $this->getData($recipient);

            foreach ($data as $key => $value) {
                $template = str_replace('{{' . $key . '}}', $value, $template);
            }

            return $this->pdfExport->exportHtml(
                $template,
                $title_file .  '.pdf',
                true,
                true,
            );
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'حدث خطأ أثناء التصدير يرجى المحاولة لاحقاً.');
        }
    }


    /*
    |============================================================================
    |============================================================================
    |                          Praivat functions
    |============================================================================
    |============================================================================
    */
    private function getData($recipient)
    {
        $user = Auth::user();

        $total_salary = $user->employee->basic_salary + $user->employee->housing_allowance + $user->employee->transportation_allowance + $user->employee->other_allowances ?? 'غير محدد';

        return [
            'recipient'                 => $recipient,
            'employee_name'             => $user->employee->raw_name ?? 'غير محدد',
            'id_number'                 => $user->employee->id_number ?? 'غير محدد',
            'nationality'               => $user->employee->country ? $user->employee->country->name : 'غير محدد',
            'job_title'                 => $user->employee->job_title ?? 'غير محدد',
            'contract_start_date'       => $user->employee->contract_start_date?->format('Y-m-d') ?? 'غير محدد',
            'national_number'           => $user->employee->national_number ?? 'غير محدد',
            'iban'                      => $user->employee->iban ?? 'غير محدد',
            'basic_salary'              => $user->employee->basic_salary ?? 'غير محدد',
            'housing_allowance'         => $user->employee->housing_allowance ?? 'غير محدد',
            'transportation_allowance'  => $user->employee->transportation_allowance ?? 'غير محدد',
            'other_allowances'          => $user->employee->other_allowances ?? 'غير محدد',
            'total_salary'              => number_format($total_salary, 2, '.', ','),
            'training_number'           => $user->employee->training_number ?? 'غير محدد',
            'current_date'              => Carbon::now()->format('Y-m-d'),

        ];
    }
}
