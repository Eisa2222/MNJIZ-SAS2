<?php

namespace App\Http\Controllers\ElectronicServices\EditRequest;


use App\Http\Controllers\Controller;
use App\Models\ElectronicServices\EditRequest\EmployeeEditRequest;
use App\Models\ElectronicServices\EditRequest\EmployeeEditRequestField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmployeeEditRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request, $id)
    {
        $employee = auth()->user()->employee;

        $request->validate([
            'fields'                 => 'nullable|array|min:1',
            'fields.*.name'          => 'nullable|string',
            'fields.*.value'         => 'nullable',
            'attachments'            => 'sometimes|array',
            'attachments.*'          => 'file|max:10240',
            'notes'                  => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $textChanges = [];

            // 1) جمع تغييرات الحقول النصية
            foreach ($request->fields ?? [] as $field) {
                $old = $employee->{$field['name']};

                // توحيد بعض الأنواع
                if ($field['name'] === 'birth_date') {
                    $old = optional($old)->format('Y-m-d');
                }
                if ($field['name'] === 'qualification_degree') {
                    $old = optional($old)->value;
                }

                if ($old == $field['value']) {
                    continue;
                }

                $textChanges[] = [
                    'field_name' => $field['name'],
                    'old_value'  => $old,
                    'new_value'  => $field['value'],
                ];
            }

            // 2) تأكد هناك تغيير نصي أو مرفق
            if (empty($textChanges) && ! $request->hasFile('attachments')) {
                DB::rollBack();
                return back()->withInput()
                    ->with('info', 'لم يتم رصد أى تغييرات لإرسالها.');
            }

            // 3) أنشئ طلب التعديل
            $editRequest = EmployeeEditRequest::create([
                'employee_id' => $employee->id,
                'notes'       => $request->notes,
                'status'      => 'pending',
            ]);

            // 4) خزن تغييرات الحقول النصية
            foreach ($textChanges as $c) {
                EmployeeEditRequestField::create([
                    'edit_request_id' => $editRequest->id,
                    'field_name'      => $c['field_name'],
                    'old_value'       => $c['old_value'],
                    'new_value'       => $c['new_value'],
                    'status'          => 'pending',
                ]);
            }

            // 5) خزن المرفقات مباشرةً في مجلد الطلب النهائي
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $fieldName => $file) {
                    if (! $file->isValid()) {
                        continue;
                    }

                    // نخزن الملف بمسار: storage/app/public/employee-edit-requests/{id}/...
                    $path = $file->store(
                        "employee-edit-requests/{$editRequest->id}",
                        'public'
                    );

                    EmployeeEditRequestField::create([
                        'edit_request_id' => $editRequest->id,
                        'field_name'      => $fieldName,
                        'old_value'       => $employee->{$fieldName},
                        'new_value'       => $path,          // هنا يخزن مسار الملف
                        'status'          => 'pending',
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('account.employee.profile.edit', $id)
                ->with('success', 'تم إرسال التغييرات بنجاح وستُراجع من قِبل الموارد البشرية.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
            return back()->withInput()
                ->withErrors(['error' => 'حدث خطأ أثناء إرسال الطلب، حاول مرة أخرى.']);
        }
    }
}
