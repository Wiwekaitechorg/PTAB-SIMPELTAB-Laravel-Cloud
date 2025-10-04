<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait AbsenceCategoryFilter
{
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        // 🔥 Filter by work_type_id + day
        if (! empty($filters['work_type_id']) && ! empty($filters['day_id'])) {
            $workTypeId = $filters['work_type_id'];
            $dayId      = $filters['day_id'];

            $query->with(['workTypeDays' => function ($q) use ($workTypeId, $dayId) {
                    $q->where('work_type_id', $workTypeId)
                      ->where('day_id', $dayId)
                      ->select('id', 'absence_category_id', 'work_type_id', 'day_id', 'start', 'end');
                }])
                ->whereHas('workTypeDays', function ($q) use ($workTypeId, $dayId) {
                    $q->where('work_type_id', $workTypeId)
                      ->where('day_id', $dayId);
                });
        }

        return $query;
    }
}
