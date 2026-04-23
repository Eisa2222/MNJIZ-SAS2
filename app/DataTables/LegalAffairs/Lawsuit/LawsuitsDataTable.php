<?php

namespace App\DataTables\LegalAffairs\Lawsuit;

use App\DataTables\ArabicSearchDataTable;
use App\Models\LegalAffair\Lawsuit\Lawsuit;
use Illuminate\Support\Facades\DB;

class LawsuitsDataTable extends ArabicSearchDataTable
{
    private $route  = "legal-affairs.lawsuits";
    private $page   = "legal_affairs.lawsuits";

    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "lawsuits-table";
    }

    protected function resource()
    {
        $query = Lawsuit::query();

        // التحقق من صلاحيات المستخدم
        if (auth()->user()->can('الدعاوى الخاصة بي') && !auth()->user()->can('كل الدعاوى')) {
            $query->where(function ($q) {
                $q->where('created_by', auth()->id())
                    ->orWhereHas('assignedEmployees', function ($q2) {
                        $q2->where('user_id', auth()->id());
                    });
            });
        }

        return $query;
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

            ['data' => 'name',                'name' => 'name',              'title' => 'اسم الدعوى',          'width' => '300px'],
            ['data' => 'lawsuit_number',      'name' => 'lawsuit_number',    'title' => 'رقم الدعوى',          'width' => '100px'],
            ['data' => 'plaintiff_id',        'name' => 'plaintiff_id',      'title' => 'المدعي',               'width' => '100px'],
            ['data' => 'defendant_id',        'name' => 'defendant_id',      'title' => 'المدعى عليه',          'width' => '100px'],
            ['data' => 'lawsuit_type_id',     'name' => 'lawsuit_type_id',   'title' => 'نوع الدعوى',           'width' => '200px'],
            ['data' => 'lawsuit_status',      'name' => 'lawsuit_status',    'title' => 'الحالة',               'width' => '150px'],

            // Actions
            ['data' => 'actions', 'name' => 'actions', 'title' => 'الإجراءات', 'orderable' => false, 'searchable' => false, 'width' => '150px',],
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

            ->editColumn('name', function ($row) {
                return $this->routeName(route($this->route . '.show', $row->id), $row->name);
            })


            ->addColumn('plaintiff_id', function ($row) {
                $plaintiffs = DB::table('lawsuit_plaintiffs')
                    ->leftJoin('customers', function ($join) {
                        $join->on('lawsuit_plaintiffs.plaintiff_id', '=', 'customers.id')
                            ->where('lawsuit_plaintiffs.plaintiff_type', 'App\Models\OperationsCenter\Customer');
                    })
                    ->leftJoin('opponents', function ($join) {
                        $join->on('lawsuit_plaintiffs.plaintiff_id', '=', 'opponents.id')
                            ->where('lawsuit_plaintiffs.plaintiff_type', 'App\Models\LegalAffair\Opponent');
                    })
                    ->where('lawsuit_plaintiffs.lawsuit_id', $row->id)
                    ->select(
                        DB::raw("COALESCE(customers.name, opponents.name) as name"),
                        DB::raw("CASE WHEN customers.id IS NOT NULL THEN 'customer' ELSE 'opponent' END as type")
                    )
                    ->get();

                // 2) عدّل زر المودال ليحمل خاصية data-plaintiffs كمصفوفة JSON
                $plaintiffsArray = $plaintiffs->map(function ($item) {
                    return ['name' => $item->name, 'type' => $item->type];
                })->values();

                // تحويلها لنص JSON
                $jsonData = htmlspecialchars($plaintiffsArray->toJson(), ENT_QUOTES, 'UTF-8');

                $total = $plaintiffsArray->count();

                return '<button type="button" class="btn btn-primary btn-sm" style="font-size:12px"
                                       title="عرض المدعين"
                                       onclick="showPlaintiffs(this)"
                                       data-plaintiffs="' . $jsonData . '">
                                       المدعي (' . $total . ')
                                </button>';
            })


            ->addColumn('defendant_id', function ($row) {
                // 1) جلب أسماء المدعى عليهم + النوع (عميل/خصم) من جداول customers و opponents
                $defendants = DB::table('lawsuit_defendants')
                    ->leftJoin('customers', function ($join) {
                        $join->on('lawsuit_defendants.defendant_id', '=', 'customers.id')
                            ->where('lawsuit_defendants.defendant_type', 'App\Models\OperationsCenter\Customer');
                    })
                    ->leftJoin('opponents', function ($join) {
                        $join->on('lawsuit_defendants.defendant_id', '=', 'opponents.id')
                            ->where('lawsuit_defendants.defendant_type', 'App\Models\LegalAffair\Opponent');
                    })
                    ->where('lawsuit_defendants.lawsuit_id', $row->id)
                    ->select(
                        DB::raw("COALESCE(customers.name, opponents.name) as name"),
                        DB::raw("CASE WHEN customers.id IS NOT NULL THEN 'customer' ELSE 'opponent' END as type")
                    )
                    ->get();

                // 2) تكوين مصفوفة (اسم + نوع) لكل مدعى عليه
                $defendantsArray = $defendants->map(function ($item) {
                    return [
                        'name' => $item->name,
                        'type' => $item->type,
                    ];
                })->values();

                // تحويل المصفوفة إلى JSON صالح
                $jsonData = htmlspecialchars($defendantsArray->toJson(), ENT_QUOTES, 'UTF-8');
                $total = $defendantsArray->count();

                // 3) إنشاء زر يظهر عدد المدعى عليهم وفيه خاصية data-defendants تحتوي على JSON
                //    عند النقر عليه يتم استدعاء دالة showDefendants() في الجافاسكربت
                return '<button type="button" class="btn btn-info btn-sm view-agents" style="font-size:12px"
                                        data-bs-toggle="tooltip"
                                        title="عرض المدعى عليهم"
                                        onclick="showDefendants(this)"
                                        data-defendants="' . $jsonData . '">
                                    المدعى عليه (' . $total . ')
                                </button>';
            })

            ->addColumn('lawsuit_type_id', function ($row) {
                return $row->lawsuit_type ? $row->lawsuit_type->name : 'غير متوفر';
            })

            // ->editColumn('lawsuit_status', function ($row) {
            //     return $this->statusBadge($row);
            // })

            ->addColumn('lawsuit_status', function ($row) {
                $tooltipText = '';
                if (auth()->user()->can('تعديل دعوى')) {
                    $tooltipText = 'اضفط هنا لتغيير الحالة ';
                    switch ($row->lawsuit_status->value) {
                        case 'active':
                            $badge = '<span class="badge bg-success status-toggle cursor-pointer" data-bs-toggle="modal" data-bs-target="#statusModal" data-id="' . $row->id . '" data-status="active" title="' . $tooltipText . '">نشط</span>';
                            break;
                        case 'inactive':
                            $badge = '<span class="badge bg-danger status-toggle cursor-pointer" data-bs-toggle="modal" data-bs-target="#statusModal" data-id="' . $row->id . '" data-status="inactive" title="' . $tooltipText . '">مغلق</span>';
                            break;
                        default:
                            $badge = '<span class="badge bg-secondary" data-bs-toggle="tooltip" title="' . $tooltipText . '">غير معروف</span>';
                            break;
                    }
                } else {
                    $tooltipText = 'عفوا ليس لديك صلاحيات لتغير الحالة';

                    switch ($row->lawsuit_status->value) {
                        case 'active':
                            $badge = '<span class="badge bg-success"  title="' . $tooltipText . '">نشط</span>';
                            break;
                        case 'inactive':
                            $badge = '<span class="badge bg-danger"  title="' . $tooltipText . '">مغلق</span>';
                            break;
                        default:
                            $badge = '<span class="badge bg-secondary"  title="' . $tooltipText . '">غير معروف</span>';
                            break;
                    }
                }
                return $badge;
            })

            ->addColumn('actions', function ($row) {
                return (auth()->user()->can('تعديل خصم') || auth()->user()->can('حذف خصم')) ? view($this->page . '.action', ['row' => $row, 'route' => $this->route])->render() : ' ليس لديك صلاحيات';
            });
    }

    protected function applyCustomFilters($query)
    {
        $request = request();

        if ($request->filled('project')) {
            $query->where('project_id', $request->project);
        }

        if ($request->filled('type')) {
            $query->where('lawsuit_type_id', $request->type);
        }

        if ($request->filled('court')) {
            $query->where('main_courts_id', $request->court);
        }

        return $query;
    }

    protected function rawColumns(): array
    {
        return ['checkbox', 'name', 'plaintiff_id', 'defendant_id', 'lawsuit_status', 'actions'];
    }

    /*
    |============================================================================
    |                               Optional Methods
    |============================================================================
    */
    protected function getSearchableColumns(): array
    {
        return [
            'name',
        ];
    }

    protected function getCustomOrder(): array
    {
        return [1, 'desc'];
    }


    protected function getButtons(): array
    {
        if (auth()->user()->can('إضافة دعوى')) {
            return [
                [
                    'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة دعوى',
                    'className' => 'btn btn-primary btn-add',
                    'action'    => "function(){ window.location.href='" . route($this->route . '.create') . "'; }",
                ],
            ];
        }

        return [];
    }

    /*
    |============================================================================
    |                            Private Helper Methods
    |============================================================================
    */
    private function statusBadge($row): string
    {
        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            $row->lawsuit_status->color(),
            $row->lawsuit_status->label()
        );
    }
}
