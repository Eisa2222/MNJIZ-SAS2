<?php

namespace App\DataTables;

abstract class ArabicSearchDataTable extends AbstractDataTable
{
    private $arabicMap = [
        'أ' => ['ا', 'إ', 'آ'],
        'إ' => ['ا', 'أ', 'آ'],
        'آ' => ['ا', 'أ', 'إ'],
        'ا' => ['أ', 'إ', 'آ'],
        'ة' => ['ه'],
        'ه' => ['ة'],
        'ي' => ['ى'],
        'ى' => ['ي'],
        'ء' => ['ا']
    ];


    protected function applyGlobalSearch($instance)
    {
        $searchValue = request('search.value');
        if (!$searchValue) return;

        $columns = $this->getSearchableColumns();
        if (empty($columns)) return;

        $instance->where(function ($query) use ($columns, $searchValue) {
            foreach ($columns as $column) {
                $this->searchColumn($query, $column, $searchValue, 'or');
            }
        });
    }

    protected function searchColumn($query, $column, $searchValue, $operator = 'or')
    {
        // إنشاء نص البحث مع البدائل
        $searchPattern = $this->createSearchPattern($searchValue);

        if (strpos($column, '.') !== false) {
            // بحث في العلاقة
            [$relation, $field] = explode('.', $column, 2);
            $method = $operator === 'or' ? 'orWhereHas' : 'whereHas';

            $query->$method($relation, function ($q) use ($field, $searchPattern) {
                $q->whereRaw("$field REGEXP ?", [$searchPattern]);
            });
        } else {
            // بحث مباشر
            $method = $operator === 'or' ? 'orWhereRaw' : 'whereRaw';
            $query->$method("$column REGEXP ?", [$searchPattern]);
        }
    }

    protected function createSearchPattern($text)
    {
        $pattern = '';
        $chars = mb_str_split($text);

        foreach ($chars as $char) {
            if (isset($this->arabicMap[$char])) {
                // إذا كان الحرف له بدائل
                $alternatives = array_merge([$char], $this->arabicMap[$char]);
                $pattern .= '[' . implode('', $alternatives) . ']';
            } else {
                // حرف عادي
                $pattern .= preg_quote($char, '/');
            }
        }

        return $pattern;
    }

    protected function addSearchCondition($query, $column, $searchValue, $operator = 'or')
    {
        $this->searchColumn($query, $column, $searchValue, $operator);
    }

    protected function searchInColumns($query, $searchTerm, $columns = [])
    {
        if (!$searchTerm || empty($columns)) return $query;

        return $query->where(function ($q) use ($searchTerm, $columns) {
            foreach ($columns as $column) {
                $this->searchColumn($q, $column, $searchTerm, 'or');
            }
        });
    }
}
