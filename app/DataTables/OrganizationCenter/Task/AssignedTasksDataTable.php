<?php

namespace App\DataTables\OrganizationCenter\Task;


use App\DataTables\ArabicSearchDataTable;
use App\Enums\OrganizationCenter\Tasks\Task\TaskStatus;
use App\Models\OrganizationCenter\Tasks\Task\Task;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

// المهام مرتبطة مع المستخدمين وليس الموظفين
class AssignedTasksDataTable extends ArabicSearchDataTable
{
    private $route  = "organization-center.tasks";
    private $page   = "organization_center.tasks";


    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "tasks-table";
    }

    protected function resource()
    {
        $userId = auth()->id();

        return Task::where('created_by', $userId);
    }

    protected function getColumns(): array
    {
        return [
            [
                'data' => 'details_control',
                'name' => 'details_control',
                'title' => '',
                'orderable'  => false,
                'searchable' => false,
                'className'  => 'details-control',
                'defaultContent' => '<i class="ti ti-arrow-big-down-lines"></i>',
                'width' => '5px'
            ],
            [
                'data'          => 'id',
                'name'          => 'id',
                'title'         => '',
                'visible'       => false,
                'orderable'     => true,
                'searchable'    => false,
                'width'         => '5px'

            ],
            // Complete checkbox
            // [
            //     'data'       => 'complete_checkbox',
            //     'name'       => 'complete_checkbox',
            //     'title'      => '',
            //     'orderable'  => false,
            //     'searchable' => false,
            //     'width'      => '5px',
            //     'className'  => 'custom-checkbox',
            // ],
            ['data' => 'task_name',       'name' => 'task_name',        'title' => 'اسم المهمة',        'width' => '150px'],
            ['data' => 'priority',        'name' => 'priority',         'title' => 'الأولوية',           'width' => '50px'],
            ['data' => 'task_field',      'name' => 'task_field',       'title' => 'مجال المهمة',       'width' => '50px'],
            ['data' => 'status',          'name' => 'status',           'title' => 'الحالة',            'width' => '80px'],
            ['data' => 'assigned_users',  'name' => 'assigned_users',   'title' => 'المكلفين',          'width' => '80px', 'orderable' => false],
            ['data' => 'remaining_days',  'name' => 'remaining_days',   'title' => 'المتبقي',           'width' => '50px', 'orderable' => false],
            // Actions
            ['data' => 'actions', 'name' => 'actions', 'title' => 'الإجراءات', 'orderable' => false, 'searchable' => false, 'width' => '80px'],
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
            ->addColumn('details_control', function ($row) { // بدلاً من editColumn
                return $this->buildDetailsControl($row);
            })

            ->addColumn('complete_checkbox', function ($row) {
                return $this->buildCompleteCheckbox($row);
            })

            ->editColumn('task_name', function ($row) {
                $truncated = Str::limit($row->task_name, 40);
                return $this->routeName(route($this->route . ".show", $row->id), $truncated);
            })

            ->editColumn('priority', function ($row) {
                return $this->priorityBadge($row);
            })

            ->editColumn('task_field', function ($row) {
                return $this->taskFieldBadge($row);
            })

            ->editColumn('status', function ($row) {
                return $this->statusBadge($row);
            })

            ->addColumn('remaining_days', function ($row) {
                return $this->buildRemainingDays($row);
            })

            ->addColumn('assigned_users', function ($row) {
                return $this->buildAssignedUsers($row);
            })


            ->addColumn('actions', function ($row) {
                return $this->buildActions($row);
            })

            ->addColumn('steps', function ($row) {
                return $this->formatStepsForChildRows($row);
            });
    }

    /*
    |============================================================================
    |                            Private Helper Methods
    |============================================================================
    */

    private function formatStepsForChildRows($row): array
    {
        if ($row->steps->count() === 0) {
            return [];
        }

        $userId = auth()->id();
        $stepsData = [];

        foreach ($row->steps->sortBy('step_order') as $index => $step) {
            // بدلاً من إرسال HTML جاهز، أرسل البيانات فقط
            $actionData = [
                'has_permission'    => $step->assignedUsers->contains('id', $userId),
                'needs_approval'    => $step->needs_approval,
                'step_id'           => $step->id,
                'status'            => $step->status,
            ];

            $stepsData[] = [
                'name'              => $step->name,
                'status_class'      => 'bg-' . $step->status->color(), // فقط اسم الكلاس
                'status_text'       => $step->status->label(),
                'step_start_date'   => $step->step_start_date   ? $step->step_start_date->format('Y-m-d H:i:s') : null,
                'step_end_date'     => $step->step_end_date     ? $step->step_end_date->format('Y-m-d H:i:s') : null,
                'duration'          => $step->duration ?: null,
                'action_data'       => $actionData,
            ];
        }

        return $stepsData;
    }

    protected function applyCustomFilters($query)
    {
        $request = request();

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('field')) {
            $query->where('task_field', $request->field);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    protected function rawColumns(): array
    {
        return [
            'details_control',
            'complete_checkbox',
            'task_name',
            'priority',
            'task_field',
            'status',
            'remaining_days',
            'assigned_users',
            'created_by',
            'actions',

            'status_text'
        ];
    }

    /*
    |============================================================================
    |                               Optional Methods
    |============================================================================
    */
    // protected function getSearchableColumns(): array
    // {
    //     return [
    //         'task_name',
    //         'description',
    //         'createdBy.employee.name',
    //         'assignedUsers.employee.name',
    //         'priority',
    //         'task_field',
    //         'status',
    //     ];
    // }

    protected function getCustomOrder(): array
    {
        return [1, 'desc'];
    }

    protected function getButtons(): array
    {
        if (auth()->user()->can('إضافة مهمة')) {
            return [
                [
                    'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة مهمة',
                    'className' => 'btn btn-primary btn-add',
                    'action'    => "function(){ window.location.href='" . route($this->route . '.create') . "'; }",
                ],
            ];
        }
        return [];
    }

    protected function parameters(): array
    {
        $params = [
            'processing' => true,
            'serverSide' => true,
            'language'   => ['url' => asset('assets/json/ar.json')],
            'pageLength' => 10,
            'orderCellsTop' => true,

            // إعداد الأعمدة
            'columnDefs' => [
                [
                    'className' => 'details-control',
                    'orderable' => false,
                    'targets' => 0,
                    'data' => null,
                ]
            ],

            'responsive' => false,
            // استخدام الدالة من الملف الخارجي
            'initComplete' => 'function(settings, json) {
            if (typeof window.TasksDataTable !== "undefined") {
                window.TasksDataTable.init(this.api());
            }
        }'
        ];

        $customOrder = $this->getCustomOrder();
        if (!empty($customOrder)) {
            $params['order'] = [$customOrder];
        }

        return $params;
    }

    private function buildDetailsControl($row): string
    {
        $hasSteps = $row->steps->count() > 0;

        if ($hasSteps) {
            return '<i class="ti ti-arrow-big-down-lines text-primary" style="cursor: pointer; font-size: 18px;" title="عرض الخطوات"></i>';
        } else {
            return '<i class="ti ti-arrow-big-down-lines text-muted" style="cursor: not-allowed; font-size: 18px;" title="لا توجد خطوات"></i>';
        }
    }

    private function buildCompleteCheckbox($row): string
    {
        $userId = auth()->id();

        if (!$row->assignedUsers->contains('id', $userId)) {
            return '<input title="عفوا ليس لديك صلاحيات اكمال او الغاء اكمال هذه المهمة"
                        class="row-checkbox disable-checkbox bg-danger border border-border-danger"
                        type="checkbox" disabled>';
        }

        $checked = $row->status === TaskStatus::Completed ? 'checked' : '';
        return '<input class="row-checkbox task-complete-checkbox" type="checkbox"
                    data-task-id="' . $row->id . '" ' . $checked . '>';
    }

    /*
    |============================================================================
    |============================================================================
    |                               Badge
    |============================================================================
    |============================================================================
    */
    private function priorityBadge($row): string
    {
        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            $row->priority->color(),
            $row->priority->label()
        );
    }

    private function taskFieldBadge($row): string
    {
        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            $row->task_field->color(),
            $row->task_field->label()
        );
    }

    private function statusBadge($row): string
    {
        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            $row->status->color(),
            $row->status->label()
        );
    }

    private function getStepStatusClass($status): string
    {
        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            $status->color(),
            $status->label()
        );
    }

    /*
    |============================================================================
    |============================================================================
    |                            End Badge
    |============================================================================
    |============================================================================
    */
    // المتبقي للمهمة
    private function buildRemainingDays($row): string
    {
        if ($row->status == TaskStatus::Completed) {
            return $row->duration ?? '-';
        }

        if ($row->steps->contains('status', 'rejected')) {
            return '<span style="font-size:10px;" class="badge bg-danger">تم رفض الاعتماد</span>';
        }

        try {
            $start = $row->created_at;
            $due = Carbon::parse($row->due_date . ' ' . $row->due_time);
            $now = Carbon::now();

            $totalDuration = $start->diffInSeconds($due);
            $elapsed = $start->diffInSeconds($now);

            if ($now->lessThanOrEqualTo($due)) {
                $progressRatio = $totalDuration > 0 ? $elapsed / $totalDuration : 0;
                $progressWidthPercent = $progressRatio * 100;
                $diff = $now->diff($due);
                $timeText = "{$diff->d} يوم, {$diff->h} ساعة, {$diff->i} دقيقة";
                $barClass = 'bg-success';
                $statusClass = 'text-success';
            } else {
                $progressWidthPercent = 100;
                $diff = $due->diff($now);
                $timeText = "-{$diff->d} يوم, {$diff->h} ساعة, {$diff->i} دقيقة";
                $barClass = 'bg-danger';
                $statusClass = 'text-danger';
            }

            return view($this->page . '.partials.remaining-days', compact(
                'progressWidthPercent',
                'barClass',
                'statusClass',
                'timeText'
            ))->render();
        } catch (\Exception $e) {
            return '<span class="text-muted">خطأ في التاريخ</span>';
        }
    }

    private function buildAssignedUsers($row): string
    {
        $assignedUsers = $row->assignedUsers;

        if ($assignedUsers->isEmpty()) {
            return '<span class="badge bg-secondary">غير محدد</span>';
        }

        return view($this->page . '.partials.assigned-users', compact('assignedUsers'))->render();
    }

    private function buildActions($row): string
    {
        if ($row->status === TaskStatus::Completed) {
            return '<span class="text-muted">مكتملة</span>';
        }

        // رااااجعها
        if ($row->steps->contains('status', 'rejected')) {
            return '<span class="text-muted">تم رفض الاعتماد - لا يمكن التعديل أو الحذف</span>';
        }

        if ($row->created_by !== Auth::id()) {
            return '<span class="text-muted">ليس لديك صلاحيات</span>';
        }

        return view($this->page . '.actions', ['row' => $row, 'route' => $this->route])->render();
    }



    // private function getStepStatusText($status): string
    // {
    //     return [
    //         'completed' => 'مكتملة',
    //         'pending' => 'قيد الانتظار',
    //         'in_progress' => 'قيد التنفيذ',
    //         'approved' => 'معتمدة',
    //         'rejected' => 'مرفوضة',
    //     ][$status] ?? 'غير محدد';
    // }
}
