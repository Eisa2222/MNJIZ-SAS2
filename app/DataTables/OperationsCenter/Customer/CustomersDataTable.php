<?php

namespace App\DataTables\OperationsCenter\Customer;

use App\DataTables\ArabicSearchDataTable;
use App\Enums\OperationsCenter\Customer\CustomerType;
use App\Helpers\SettingsHelper;
use App\Models\OperationsCenter\Customer\Customers;

class CustomersDataTable extends ArabicSearchDataTable
{
    private $route = "operations-center.customers";

    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "customers-table";
    }

    protected function resource()
    {
        $query = Customers::query()
            ->with([
                'relationshipManager:id,name,nickname',
                'status',
            ]);

        // التحقق من صلاحيات المستخدم
        if (auth()->user()->can('العملاء الخاصين بي') && !auth()->user()->can('كل العملاء')) {
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

            ['data' => 'name',                        'name' => 'name',                       'title' => 'اسم العميل',           'width' => '400px'],
            ['data' => 'customer_type',               'name' => 'customer_type',              'title' => 'نوع العميل',           'width' => '400px'],
            ['data' => 'status_id',                   'name' => 'status_id',                  'title' => 'حالة العميل',          'width' => '400px'],
            ['data' => 'relationship_manager_id',     'name' => 'relationship_manager_id',    'title' => 'مسوؤل العلاقة',         'width' => '300px'],

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

            ->editColumn('customer_type', function ($row) {
                return $this->typeBadge($row);
            })

            ->editColumn('status_id', function ($row) {
                return $row->status?->name;
            })

            ->editColumn('relationship_manager_id', function ($row) {
                return $this->routeName(route('account.employee.profile', $row->relationship_manager_id), $row->relationshipManager->name ?? '-');
            })

            ->addColumn('actions', function ($row) {
                return (auth()->user()->can('تعديل عميل') || auth()->user()->can('حذف عميل')) ? view('operations_center.customers.action', ['row' => $row, 'route' => $this->route])->render() : ' ليس لديك صلاحيات';
            });
    }

    protected function applyCustomFilters($query)
    {
        $request = request();

        if ($request->filled('status')) {
            $query->where('status_id', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('customer_type', $request->type);
        }

        if ($request->filled('manager')) {
            $query->where('relationship_manager_id', $request->manager);
        }

        return $query;
    }

    protected function rawColumns(): array
    {
        return ['checkbox', 'name', 'customer_type', 'relationship_manager_id', 'actions'];
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
            'title',
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
        if (SettingsHelper::get('sms_enabled') && auth()->user()->can('إسال SMS للعملاء')) {
            $buttons[] = [
                'text'      => '<i class="fas fa-sms me-1"></i> إرسال SMS',
                'className' => 'btn btn-default btn-send-sms',
                'action'    => "
                function(e, dt, node, config) {
                    // جمع معرفات العملاء المحددين
                    var selectedIds = [];
                    var selectedNames = [];
                    $('.row-checkbox:checked').each(function() {
                        selectedIds.push($(this).val());
                        var row = $(this).closest('tr');
                        var name = row.find('td:eq(2)').text().trim(); // افتراض أن عمود الاسم هو الثالث
                        selectedNames.push(name);
                    });

                    if (selectedIds.length === 0) {
                        toastr.warning('يرجى تحديد عملاء اولاً.');
                        return;
                    }

                    // إزالة الحقول المخفية السابقة
                    $('input[name=\"customer_ids[]\"]').remove();

                    // إضافة حقل مخفي لكل معرف عميل
                    selectedIds.forEach(function(id) {
                        $('#send-sms-form').append(
                            '<input type=\"hidden\" name=\"customer_ids[]\" value=\"' + id + '\">'
                        );
                    });

                    // عرض قائمة العملاء في المودال
                    var customerListHtml = '';
                    selectedNames.forEach(function(name) {
                        customerListHtml += '<li>' + name + '</li>';
                    });
                    $('#selected-customers-list').html(customerListHtml);

                    // فتح المودال
                    $('#sendSmsModal').modal('show');
                }
            ",
            ];
        }

        return $buttons;
    }
    protected function getButtons(): array
    {
        $buttons = [];
        if (auth()->user()->can('إضافة عميل')) {
            $buttons = [
                [
                    'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة عميل',
                    'className' => 'btn btn-primary btn-add',
                    'action'    => "function(){ window.location.href='" . route($this->route . '.create') . "'; }",
                ],
            ];
        }

        return $buttons;
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
            $row->customer_type->color(),
            $row->customer_type->label()
        );
    }
}
