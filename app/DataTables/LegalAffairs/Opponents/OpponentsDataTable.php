<?php

namespace App\DataTables\LegalAffairs\Opponents;


use App\DataTables\ArabicSearchDataTable;
use App\Helpers\SettingsHelper;
use App\Models\LegalAffair\Opponent\Opponent;

class OpponentsDataTable extends ArabicSearchDataTable
{
    private $route  = "legal-affairs.opponents";
    private $page   = "legal_affairs.opponents";

    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "opponents-table";
    }

    protected function resource()
    {
        $query = Opponent::query();

        // التحقق من صلاحيات المستخدم
        if (auth()->user()->can('الخصوم الخاصين بي') && !auth()->user()->can('كل الخصوم')) {
            $query->where('created_by', auth()->id());
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

            ['data' => 'name',            'name' => 'name',             'title' => 'اسم الخصم',          'width' => '400px'],
            ['data' => 'type',            'name' => 'type',             'title' => 'نوع الخصم',          'width' => '400px'],
            ['data' => 'email',           'name' => 'email',            'title' => 'البريد الالكتروني',  'width' => '400px', 'defaultContent' => "-"],
            ['data' => 'contact_number',  'name' => 'contact_number',   'title' => 'رقم الاتصال ',        'width' => '400px', 'defaultContent' => "-"],

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

            ->editColumn('type', function ($row) {
                return $this->typeBadge($row);
            })

            ->addColumn('actions', function ($row) {
                return (auth()->user()->can('تعديل خصم') || auth()->user()->can('حذف خصم')) ? view($this->page . '.action', ['row' => $row, 'route' => $this->route])->render() : ' ليس لديك صلاحيات';
            });
    }

    protected function applyCustomFilters($query)
    {
        $request = request();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('region')) {
            $query->where('settings_region_id', $request->region);
        }

        return $query;
    }

    protected function rawColumns(): array
    {
        return ['checkbox', 'name', 'type', 'actions'];
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
        ];
    }

    protected function getCustomOrder(): array
    {
        return [1, 'desc'];
    }


    protected function getActionButtons(): array
    {
        $buttons = [];

        // إضافة زر SMS إذا كان مفعلاً والمستخدم لديه صلاحية
        if (auth()->user()->can('إسال SMS للخصوم')) {
            $buttons[] = [
                'text' => 'إرسال SMS',
                'className' => 'btn btn-default btn-send-sms',
                'action' => 'function(e, dt, node, config) {
                var selectedIds = [];
                var selectedNames = [];
                $(\'.row-checkbox:checked\').each(function() {
                    selectedIds.push($(this).val());
                    var row = $(this).closest(\'tr\');
                    var name = row.find(\'td:eq(2)\').text().trim();
                    selectedNames.push(name);
                });

                if (selectedIds.length === 0) {
                    toastr.warning(\'يرجى تحديد خصوم أولاً.\');
                    return;
                }

                // إزالة الحقول المخفية السابقة
                $(\'input[name="opponent_ids[]"]\').remove();

                // إضافة حقل مخفي لكل معرف خصم
                selectedIds.forEach(function(id) {
                    $(\'#send-sms-form\').append(
                        \'<input type="hidden" name="opponent_ids[]" value="\' + id + \'">\');
                });

                // عرض قائمة الخصوم في المودال
                var opponentListHtml = \'\';
                selectedNames.forEach(function(name) {
                    opponentListHtml += \'<li>\' + name + \'</li>\';
                });
                $(\'#selected-opponents-list\').html(opponentListHtml);
                $(\'#sendSmsModal\').modal(\'show\');
            }'
            ];
        }

        return $buttons;
    }
    protected function getButtons(): array
    {
        if (auth()->user()->can('إضافة خصم')) {
            return [
                [
                    'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة خصم',
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
    private function typeBadge($row): string
    {
        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            $row->type->color(),
            $row->type->label()
        );
    }
}
