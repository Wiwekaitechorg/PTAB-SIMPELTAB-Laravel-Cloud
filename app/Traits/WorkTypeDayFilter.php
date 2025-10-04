<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait WorkTypeDayFilter
{
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        // 🔥 Filter by work_type_id
        if (! empty($filters['work_type_id'])) {
            $query->where('work_type_id', $filters['work_type_id']);
        }

        // 🔥 Filter by day_id
        if (! empty($filters['day_id'])) {
            $query->where('day_id', $filters['day_id']);
        }

        // 🔥 Filter by related absence_category.type
        if (! empty($filters['category_type'])) {
            $query->whereHas('absenceCategory', function (Builder $q) use ($filters) {
                $q->where('type', $filters['category_type']);
            });
        }

        // 🔥 Always eager-load the category if asked
        if (! empty($filters['with_category'])) {
            $query->with('absenceCategory');
        }

        // 🔥 Ordering
        if (! empty($filters['order_by'])) {
            $query->orderBy(
                $filters['order_by'],
                $filters['order_dir'] ?? 'ASC'
            );
        }

        return $query;
    }
}
