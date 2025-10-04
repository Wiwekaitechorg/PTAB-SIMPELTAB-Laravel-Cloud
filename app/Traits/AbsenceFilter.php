<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait AbsenceFilter
{
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        // 🔥 Filter by created_at (date only)
        if (! empty($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }

        // 🔥 Filter by staff_id
        if (! empty($filters['staff_id'])) {
            $query->where('staff_id', $filters['staff_id']);
        }

        // 🔥 Exclude absence categories
        if (! empty($filters['exclude_categories'])) {
            $query->whereNotIn('absence_category_id', (array) $filters['exclude_categories']);
        }

        // 🔥 Ensure it has absence_logs (instead of join)
        if (! empty($filters['with_logs'])) {
            $query->whereHas('absence_logs');
        }

        return $query;
    }
}
