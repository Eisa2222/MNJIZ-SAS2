<?php

namespace App\Services;

use Yajra\DataTables\Facades\DataTables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DataTableService
{
    protected $model;
    protected $selectColumns = [];
    protected $relations = [];
    protected $customColumns = [];
    protected $filters = [];

    /**
     * تعيين النموذج الذي سيتم استخدامه
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * تعيين الأعمدة المراد تحديدها
     *
     * @param  array  $columns
     * @return $this
     */
    public function setSelectColumns(array $columns)
    {
        $this->selectColumns = $columns;
        return $this;
    }

    /**
     * تعيين العلاقات المطلوبة
     *
     * @param  array  $relations
     * @return $this
     */
    public function setRelations(array $relations)
    {
        $this->relations = $relations;
        return $this;
    }

    /**
     * تعيين الأعمدة المخصصة
     *
     * @param  array  $columns
     * @return $this
     */
    public function setCustomColumns(array $columns)
    {
        $this->customColumns = $columns;
        return $this;
    }

    /**
     * تعيين الفلاتر
     *
     * @param  array  $filters
     * @return $this
     */
    public function setFilters(array $filters)
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * معالجة طلب DataTable
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request)
    {
        $query = $this->model::select($this->selectColumns);

        // تحميل العلاقات
        if (!empty($this->relations)) {
            $query->with($this->relations);
        }

        // تطبيق الفلاتر العامة
        foreach ($this->filters as $filter) {
            if ($request->has($filter['field']) && $request->input($filter['field'])) {
                $query->where($filter['field'], $filter['operator'] ?? '=', $request->input($filter['field']));
            }
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
            })
            ->applyCustomColumns($this->customColumns)
            ->applyFilters()
            ->rawColumns(['action', 'checkbox'])
            ->make(true);
    }
}

