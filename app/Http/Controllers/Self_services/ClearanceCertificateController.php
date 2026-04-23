<?php

namespace App\Http\Controllers\Self_services;

use App\Enums\ElectronicServices\Custody\Requests\CustodyRequestStatus;
use App\Enums\ElectronicServices\Custody\Requests\CustodyRequestType;
use App\Enums\SelfServices\ClearanceCertificateStatus;
use App\Helpers\SettingsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\SelfServices\ClearanceCertificateRequest;
use App\Models\general_setting\SettingsTemplate;
use App\Models\Self_services\ClearanceCertificate;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\Hr\Employees\Employees;
use App\Services\Common\PdfExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ClearanceCertificateController extends Controller
{
    public function __construct(private PdfExportService $pdfExport)
    {
        $this->middleware('can:إخلاء طرف')->only(['index']);
        $this->middleware('can:إضافة طلب إخلاء طرف')->only(['create', 'store']);
        $this->middleware('can:تعديل طلب إخلاء طرف')->only(['edit', 'update']);
        $this->middleware('can:حذف طلب إخلاء طرف')->only(['destroy']);
    }


    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ClearanceCertificate::where('user_id', Auth::id())->with('user')->select('clearance_certificates.*');

            return DataTables::of($query)
                ->addColumn('status', function (ClearanceCertificate $row) {

                    $bgColor = $row->status?->color() ?? 'secondary';
                    $statusName = $row->status?->label() ?? 'غير محدد';

                    return "<span class=\"badge bg-label-{$bgColor}\">{$statusName}</span>";
                })

                ->editColumn('created_at', function ($row) {
                    return $row->created_at?->format('d/m/Y H:i');
                })

                ->addColumn('action', function ($row) {
                    // **المقارنة الصحيحة مع الـ Enum**
                    if ($row->status == ClearanceCertificateStatus::PENDING) {
                        return view('self_services.clearance_certificates.actions', compact('row'))->render();
                    }

                    if ($row->status == ClearanceCertificateStatus::APPROVED) {
                        return '<a href="' . route('account.self-services.clearance-certificate.exportPdf', $row->id) . '" title="تحميل المرفق">
                            <i class="fa fa-download text-secondary" style="font-size: 1.5em;"></i>
                        </a>';
                    }

                    // للحالة REJECTED وأي حالات أخرى
                    return '<small class="text-muted">تم رفض الطلب</small>';
                })

                ->rawColumns(['status', 'action'])
                ->make(true);
        }
        return view('self_services.clearance_certificates.index');
    }

    public function create()
    {
        if ($this->checkCustody() > 0) {
            return redirect()->back()->with(
                'error',
                "لا يمكن تقديم طلب إخلاء طرف، حيث توجد لديك عدد {$this->checkCustody()} من العهد التي لم تُسلّم بعد. يُرجى تسليم جميع العهد قبل المتابعة."
            );
        }

        return view('self_services.clearance_certificates.create');
    }

    public function store(ClearanceCertificateRequest $request)
    {
        if ($this->checkCustody() > 0) {
            return redirect()->back()->with(
                'error',
                "لا يمكن تقديم طلب إخلاء طرف، حيث توجد لديك عدد {$this->checkCustody()} من العهد التي لم تُسلّم بعد. يُرجى تسليم جميع العهد قبل المتابعة."
            );
        }
        $data = $request->validated();

        $data['user_id'] = Auth::user()->id;
        ClearanceCertificate::create($data);

        return redirect()->route('account.self-services.clearance-certificate.index')->with('success', 'تم تقديم الطلب بنجاح');
    }

    public function edit($id)
    {
        if ($this->checkCustody() > 0) {
            return redirect()->back()->with(
                'error',
                "لا يمكن تقديم طلب إخلاء طرف، حيث توجد لديك عدد {$this->checkCustody()} من العهد التي لم تُسلّم بعد. يُرجى تسليم جميع العهد قبل المتابعة."
            );
        }

        $certificate = ClearanceCertificate::findOrFail($id);

        if ($certificate->status !== ClearanceCertificateStatus::PENDING) {
            return redirect()->route('account.self-services.clearance-certificate.index')->with('error', 'لا يمكن تعديل الطلب لأنه تمت معالجته بالفعل.');
        }

        return view('self_services.clearance_certificates.edit', compact('certificate'));
    }

    public function update(ClearanceCertificateRequest $request, $id)
    {
        if ($this->checkCustody() > 0) {
            return redirect()->back()->with(
                'error',
                "لا يمكن تقديم طلب إخلاء طرف، حيث توجد لديك عدد {$this->checkCustody()} من العهد التي لم تُسلّم بعد. يُرجى تسليم جميع العهد قبل المتابعة."
            );
        }

        $certificate = ClearanceCertificate::findOrFail($id);

        if ($certificate->status !== ClearanceCertificateStatus::PENDING) {
            return redirect()->route('account.self-services.clearance-certificate.index')->with('error', 'لا يمكن تعديل الطلب لأنه تمت معالجته بالفعل.');
        }


        $data = $request->validated();

        $certificate->update($data);

        return redirect()->route('account.self-services.clearance-certificate.index')->with('success', 'تم تعديل الطلب بنجاح');
    }

    public function destroy($id)
    {
        $certificate = ClearanceCertificate::findOrFail($id);

        if ($certificate->status !== ClearanceCertificateStatus::PENDING) {
            return redirect()->route('account.self-services.clearance-certificate.index')->with('error', 'تم معالجة الطلب لا يمكن التعديل عليه او حذفه');
        }
        $certificate->delete();

        return redirect()->route('account.self-services.clearance-certificate.index')->with('success', 'تم حذف الطلب بنجاح');
    }


    // export_pdf
    public function exportPdf()
    {
        $employee = Employees::where('user_id', Auth::id())->firstOrFail();
        $employee->user->status = 'inactive';
        $employee->user->save();


        try {
            if ($this->checkCustody() > 0) {
                return redirect()->back()->with(
                    'error',
                    "لا يمكن تقديم طلب إخلاء طرف، حيث توجد لديك عدد {$this->checkCustody()} من العهد التي لم تُسلّم بعد. يُرجى تسليم جميع العهد قبل المتابعة."
                );
            }

            $template = SettingsTemplate::where('template_type',  'clearance_certificates')->value('content');

            $data = $this->getData();

            foreach ($data as $key => $value) {
                $template = str_replace('{{' . $key . '}}', $value, $template);
            }

            return $this->pdfExport->exportHtml(
                $template,
                'إخلاء طرف' .  '.pdf',
                true
            );
        } catch (\Exception $e) {
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
    private function getData()
    {
        $user = Auth::user();

        return [
            'employee_name'             => $user->employee->raw_name ?? 'غير محدد',
            'id_number'                 => $user->employee->id_number ?? 'غير محدد',
            'nationality'               => $user->employee->country ? $user->employee->country->name : 'غير محدد',
            'job_title'                 => $user->employee->job_title ?? 'غير محدد',
            'contract_start_date'       => $user->employee->contract_start_date->format('Y-m-d') ?? 'غير محدد',
            'contract_end_date'         => $user->employee->contract_end_date->format('Y-m-d') ?? 'غير محدد',
            'office_name'               => SettingsHelper::get('office_name') ?? 'غير محدد',
            'current_date'              => Carbon::now()->format('Y-m-d'),

        ];
    }



    public function checkCustody()
    {
        $employee = Employees::where('user_id', Auth::id())->firstOrFail();

        return $employee->custodies()
            ->where('status', CustodyRequestStatus::Approved)
            ->where('request_type', CustodyRequestType::Assign)
            ->count();
    }
}
