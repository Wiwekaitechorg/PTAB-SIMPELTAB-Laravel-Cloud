<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\Models\AbsenceCategory;

trait ShiftGroupTimesheetFilter
{
    /**
     * Default filters (can be overridden per model).
     */
    public static array $baseFilter = [
        'exclude_break' => true, // by default, skip "break" categories
    ];

    /**
     * Scope to apply dynamic filters merged with base filter.
     */
    public function scopeFilter(Builder $q, array $filters = []): Builder
    {
        // merge with base filters
        $filters = array_merge(static::$baseFilter, $filters);

        // 🔹 shift_group_id
        if (! empty($filters['shift_group_id'])) {
            $q->where($this->getTable() . '.shift_group_id', $filters['shift_group_id']);
        }

        // 🔹 exclude type = break (via absenceCategory relation)
        if (! empty($filters['exclude_break'])) {
            $q->whereHas('absenceCategory', function (Builder $cat) {
                $cat->where('type', '!=', 'break');
            });
        }

        // 🔹 eager load relations
        if (! empty($filters['with'])) {
            $with = is_array($filters['with']) ? $filters['with'] : [$filters['with']];
            $q->with($with);
        }

        // 🔹 select extra fields (queue, type) using subquery
        if (! empty($filters['with_category_fields'])) {
            $q->addSelect([
                $this->getTable().'.*',
                'queue' => AbsenceCategory::select('queue')
                    ->whereColumn('absence_categories.id', $this->getTable().'.absence_category_id')
                    ->limit(1),
                'type' => AbsenceCategory::select('type')
                    ->whereColumn('absence_categories.id', $this->getTable().'.absence_category_id')
                    ->limit(1),
            ]);
        }

        // 🔹 ordering
        if (! empty($filters['order_by_queue'])) {
            $q->orderBy('queue', 'ASC');
        }

        return $q;
    }
}
