<?php

namespace App\Http\Traits;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;

trait ApiQueryHelper
{
    protected function applyPagination(Builder $query, Request $request, int $defaultPerPage = 20): Builder
    {
        $perPage = min((int) $request->input('per_page', $defaultPerPage), 100);
        $page = max((int) $request->input('page', 1), 1);

        $query->forPage($page, $perPage);

        return $query;
    }

    protected function applySorting(Builder $query, Request $request, array $allowedFields, string $defaultField = 'created_at', string $defaultDir = 'desc'): Builder
    {
        $field = $request->input('sort_by', $defaultField);
        $dir = strtolower($request->input('sort_dir', $defaultDir));

        if (! in_array($field, $allowedFields, true)) {
            $field = $defaultField;
        }

        if (! in_array($dir, ['asc', 'desc'], true)) {
            $dir = $defaultDir;
        }

        return $query->orderBy($field, $dir);
    }

    protected function applyFilters(Builder $query, Request $request, array $filterMap): Builder
    {
        $filters = $request->input('filter');

        if (is_string($filters)) {
            $filters = json_decode($filters, true);
        }

        if (! is_array($filters)) {
            return $query;
        }

        foreach ($filterMap as $param => $column) {
            $value = $filters[$param] ?? $filters[$column] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            if (is_callable($column)) {
                $column($query, $value);
            } elseif (str_ends_with($column, '.like')) {
                $col = substr($column, 0, -5);
                $query->where($col, 'like', "%{$value}%");
            } elseif (str_ends_with($column, '.min')) {
                $col = substr($column, 0, -4);
                $query->where($col, '>=', $value);
            } elseif (str_ends_with($column, '.max')) {
                $col = substr($column, 0, -4);
                $query->where($col, '<=', $value);
            } elseif (str_ends_with($column, '.in')) {
                $col = substr($column, 0, -3);
                $values = is_array($value) ? $value : explode(',', $value);
                $query->whereIn($col, $values);
            } else {
                $query->where($column, $value);
            }
        }

        return $query;
    }

    protected function paginatedResponse(Builder $query, Request $request, ?callable $mapper = null): array
    {
        $totalQuery = clone $query;
        $total = $totalQuery->count();

        $perPage = min((int) $request->input('per_page', 20), 100);
        $page = max((int) $request->input('page', 1), 1);
        $lastPage = max((int) ceil($total / $perPage), 1);

        $this->applyPagination($query, $request, $perPage);

        $items = $query->get();

        if ($mapper) {
            $items = $items->map($mapper);
        }

        return [
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
                'from' => (($page - 1) * $perPage) + 1,
                'to' => min($page * $perPage, $total),
            ],
        ];
    }
}
