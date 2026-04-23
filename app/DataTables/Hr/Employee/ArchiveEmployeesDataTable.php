<?php

namespace App\DataTables\Hr\Employee;

use App\DataTables\ArabicSearchDataTable;
use App\Models\Hr\Employees\Employees;

class ArchiveEmployeesDataTable extends ArabicSearchDataTable
{
    private $route = "hr.employees.archive";

    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "employees-table";
    }

    protected function resource()
    {
        return  Employees::with('user.roles')->whereHas('user', function ($query) {
            $query->where('status', 'inactive');
        })->select([
            'id',
            'name',
            'nickname',
            'user_id',
            'profile_picture',
            'national_number'
        ]);
    }

    protected function getColumns(): array
    {
        return [
            [
                'data' => '',
                'orderable'  => false,
                'searchable' => false,
            ],
            [
                'data' => 'id',
                'name' => 'id',
                'title' => 'ID',
                'visible' => false,
                'orderable' => true,
                'searchable' => false,
            ],
            // Checkbox
            [
                'data'       => 'checkbox',
                'name'       => 'checkbox',
                'title'      => '<span class="custom-checkbox-header"><input type="checkbox" id="select-all"></span>',
                'orderable'  => false,
                'searchable' => false,
                'width'      => '10px',
                'className'  => 'custom-checkbox',
                'titleAttr'  => 'تحديد الكل',
            ],
            ['data' => 'profile_img',       'name' => 'profile_img',        'title' => '', 'orderable' => false, 'searchable' => false, 'width' => '20px'],
            ['data' => 'name',              'name' => 'name',               'title' => 'اسم الموظف'],
            ['data' => 'nickname',          'name' => 'nickname',           'title' => 'اللقب'],
            ['data' => 'national_number',   'name' => 'national_number',    'title' => 'الرقم الوظيفي', 'defaultContent' => '--'],
            ['data' => 'role',              'name' => 'role',               'title' => 'المسمى الوظيفي'],
            ['data' => 'fingerprints',      'name' => 'fingerprints',       'title' => 'البصمة'],

            // Actions
            ['data' => 'actions', 'name' => 'actions', 'title' => 'الإجراءات', 'orderable' => false, 'searchable' => false, 'width' => '20px'],
        ];
    }

    /*
    |============================================================================
    |                           Helper Methods
    |============================================================================
    */
    protected function addCustomColumns($datatable)
    {
        $datatable
            ->addColumn('checkbox', function ($row) {
                return $this->checkbox($row);
            })

            ->addColumn('name', function ($row) {
                return $this->routeName(route('account.employee.profile', $row->id), $row->getRawNameAttribute() ?? '-');
            })

            ->editColumn('profile_img', function ($row) {
                // تعيين مسار الصورة
                if ($row->profile_picture && file_exists(public_path('storage/' . $row->profile_picture))) {
                    $imageUrl = asset('storage/' . $row->profile_picture);
                } else {
                    $imageUrl = asset('assets/img/branding/Alburhan-Logo.png'); // صورة افتراضية
                }

                return '<img src="' . $imageUrl . '" alt="Profile" class="rounded-circle" width="40" height="40">';
            })

            ->addColumn('role', function ($row) {
                $role = $row->user && $row->user->roles->count() > 0 ? $row->user->roles->first()->name : 'غير محدد';
                return e($role);
            })

            ->addColumn('fingerprints', function ($row) {
                if ($row->user->fingerprints) {
                    $fingerprint = $row->user->fingerprints->first(); // نفترض أن هناك بصمة واحدة لكل مستخدم
                    return '<button class="btn btn-link manage-fingerprint" data-fingerprint-id="' . $fingerprint->id . '" title="إدارة البصمة"><i class="fa fa-fingerprint " style="color: green;"></i></button>';
                } else {
                    // إذا لم يكن لدى المستخدم بصمات، عرض أيقونة باللون البرتقالي كزر لفتح المودال لإضافة بصمة
                    return '<button class="btn btn-link capture-fingerprint" data-user-id="' . $row->id . '" title="اضافة بصمة"><i class="fa fa-fingerprint fa-2x" style="color: orange;"></i></button>';
                }
            })

            ->addColumn('actions', function ($row) {
                return view('hr.employees.archive.action', ['row' => $row, 'route' => $this->route])->render();
            });
    }


    protected function applyCustomFilters($query)
    {
        $request = request();

        if ($request->filled('hr_status_id')) {
            $query->where('hr_status_id', $request->hr_status_id);
        }

        if ($request->role) {
            $query->whereHas('user.roles', function ($q) use ($request) {
                $q->where('id', $request->role);
            });
        }

        if ($request->insurance_status) {
            $query->where('insurance_status', $request->insurance_status);
        }

        return $query;
    }


    protected function rawColumns(): array
    {
        return ['checkbox', 'profile_img', 'name', 'fingerprints', 'actions'];
    }
    /*
    |============================================================================
    |                               Optional Methods
    |============================================================================
    */
    // الحقول المسموح البحث فيها
    protected function getSearchableColumns(): array
    {
        return [
            'name',
            'nickname',
        ];
    }

    protected function getCustomOrder(): array
    {
        return [1, 'desc'];
    }
}
