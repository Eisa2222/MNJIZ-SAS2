<?php

namespace App\DataTables\SystemAdministration\ActivityLog;

use App\DataTables\ArabicSearchDataTable;
use App\Models\Hr\Employees\Employees;
use Exception;
use Spatie\Activitylog\Models\Activity;

class ActivityLogDataTable extends ArabicSearchDataTable
{
    private function getModelPathMapping(): array
    {
        return [
            // 'App\\Models\\judicial_affairs\\SessionComment'     => 'App\\Models\\JudicialAffairs\\SessionComment',
            // 'App\\Models\\Task\\Task'                           => 'App\\Models\\Tasks\\Task',
        ];
    }

    private function getCorrectModelPath(string $oldPath): string
    {
        $mapping = $this->getModelPathMapping();
        return $mapping[$oldPath] ?? $oldPath;
    }


    protected function getTableId(): string
    {
        return "activity-log-table";
    }


    protected function resource()
    {
        return Activity::with(['causer'])
            ->select(['id', 'log_name', 'description', 'subject_id', 'subject_type', 'causer_id', 'causer_type', 'properties', 'created_at']);
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

            ['data' => 'description',        'name' => 'description',   'title' => 'الوصف'],
            ['data' => 'causer',             'name' => 'causer',        'title' => 'المستخدم'],
            ['data' => 'created_at',         'name' => 'created_at',    'title' => 'التاريخ'],

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
            ->addColumn('causer', function ($row) {
                return $row->causer ? $row->causer->name : '-';
            })
            ->addColumn('subject_info', function ($row) {
                if (!$row->subject_type) {
                    return '-';
                }

                $correctPath = $this->getCorrectModelPath($row->subject_type);

                if (class_exists($correctPath)) {
                    try {
                        // محاولة تحميل النموذج بالمسار الصحيح
                        $model = $correctPath::find($row->subject_id);
                        $modelName = class_basename($correctPath);

                        if ($model) {
                            return "{$modelName} #{$row->subject_id}";
                        } else {
                            return "<span class='badge badge-secondary'>{$modelName} محذوف</span>";
                        }
                    } catch (Exception $e) {
                        return '<span class="badge badge-warning">خطأ في التحميل</span>';
                    }
                } else {
                    return '<span class="badge badge-danger">نموذج غير موجود</span>';
                }
            })
            ->editColumn('created_at', function ($row) {
                return \Carbon\Carbon::parse($row->created_at)->diffForHumans();
            })
            ->addColumn('actions', function ($row) {
                return '<button class="btn btn-sm btn-primary view-details" data-id="' . $row->id . '">عرض التفاصيل</button>';
            });
    }

    protected function applyCustomFilters($query)
    {
        $request = request();

        if ($request->filled('user')) {
            $query->where('causer_id', $request->user);
        }

        if ($request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }


        return $query;
    }

    protected function rawColumns(): array
    {
        return ['checkbox', 'actions'];
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
            'description',
            'causer.name',
        ];
    }

    protected function getCustomOrder(): array
    {
        return [1, 'desc'];
    }
}
