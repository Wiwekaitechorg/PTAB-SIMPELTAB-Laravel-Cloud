<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait ShiftPlannerStaffFilter
{
    /**
     * Base filters for the model. You can override this in each model if needed.
     */
    // public static array $baseFilter = [
    //     'current' => true,
    // ];

    /**
     * Scope to apply dynamic filters merged with base filter.
     */
    public function scopeFilter(Builder $q, array $filters = []): Builder
    {
        $filters = array_merge(static::$baseFilter, $filters);

        // Define which simple fields you want to support
        $simpleFilters = ['status', 'staff_id'];

        foreach ($simpleFilters as $field) {
            if (array_key_exists($field, $filters)) {
                $value = $filters[$field];

                $q->where(function ($query) use ($field, $value) {
                    if (is_array($value)) {
                        $query->whereIn($field, $value);
                    } else {
                        $query->where($field, $value);
                    }
                });
            }
        }
        
        // select
        if (! empty($filters['select'])) {
            if (is_array($filters['select'])) {
                $q->select($filters['select']);
            } else {
                $q->selectRaw($filters['select']);
            }
        }

        // allow eager-loading if model has relationships
        if (! empty($filters['with'])) {
            // normalize to array
            $with = is_array($filters['with'])
                ? $filters['with']
                : [$filters['with']];

            $q->with($with);
        }        

        // no absence logs filter
        if (!empty($filters['without_absence'])) {
            $q->whereDoesntHave('absenceLogs');
        }

        // date filter (use 'date' key)
        if (isset($filters['start_date'])) {
            $q->whereDate('start', $filters['start_date']);
        }

        // current (start/end window)
        if (! empty($filters['current'])) {
            $now      = now();
            $startCol = $filters['start_col'] ?? 'start_date';
            $endCol   = $filters['end_col'] ?? 'expired_date';

            // If we need the sick/other behaviour:
            $q->where(function ($query) use ($now, $startCol, $endCol) {
                $query->where(function ($q1) use ($now, $startCol, $endCol) {
                    $q1->where('type', '!=', 'sick')
                        ->where($startCol, '<=', $now)
                        ->where($endCol, '>=', $now);
                })->orWhere(function ($q2) use ($now, $startCol) {
                    $q2->where('type', 'sick')
                        ->where($startCol, '<=', $now);
                });
            });
        }

        // Example: order by related ShiftGroup.queue
        if (!empty($filters['order_by_queue'])) {
            $q->orderBy(
                ShiftGroup::select('queue')
                    ->whereColumn('shift_groups.id', 'shift_planner_staffs.shift_group_id')
            );
        }

        // === Ordering ===

        // allow raw order(s)
        if (! empty($filters['order_by_raw'])) {
            if (is_array($filters['order_by_raw'])) {
                foreach ($filters['order_by_raw'] as $raw) {
                    $q->orderByRaw($raw);
                }
            } else {
                $q->orderByRaw($filters['order_by_raw']);
            }
        }

        // allow single string or array for order_by
        if (! empty($filters['order_by'])) {
            if (is_array($filters['order_by'])) {
                // example: ['name'=>'asc', 'created_at'=>'desc']
                foreach ($filters['order_by'] as $col => $dir) {
                    // if you passed only values (0-based array), assume ASC
                    if (is_int($col)) {
                        $q->orderBy($dir, $filters['order_dir'] ?? 'ASC');
                    } else {
                        $q->orderBy($col, $dir);
                    }
                }
            } else {
                // single column
                $q->orderBy($filters['order_by'], $filters['order_dir'] ?? 'ASC');
            }
        }

        return $q;
    }
}
