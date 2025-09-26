<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait AbsenceLogFilter
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

        // staff_id
        if (! empty($filters['staff_id']) && empty($filters['where_has'])) {
            $q->where($this->getTable() . '.staff_id', $filters['staff_id']);
        }
        if (! empty($filters['where_has'])) {
            $q->whereHas('absence', function ($cat) use ($filters) {
                $cat->where('staff_id', $filters['staff_id']);
            });
        }

        // current (start/end window)
        if (! empty($filters['current'])) {
            $now      = now();
            $startCol = $filters['start_col'] ?? 'start_date';
            $endCol   = $filters['end_col'] ?? 'expired_date';

            if (empty($filters['current_end'])) {
                $q->where($startCol, '<=', $now)
                    ->where($endCol, '>=', $now);
            } else {
                $q->where($endCol, '>=', $now);
            }
        }

        // status
        if (isset($filters['status'])) {
            $q->where(function ($query) use ($filters) {
                if (is_array($filters['status'])) {
                    $query->whereIn($this->getTable() . '.status', $filters['status']);
                } else {
                    $query->where($this->getTable() . '.status', $filters['status']);
                }
            });
        }

        // absence_request_id
        if (! empty($filters['absence_request_id'])) {
            $q->where($this->getTable() . '.absence_request_id', $filters['absence_request_id']);
        }

        // type and queue via relationship
        if (! empty($filters['where_has'])) {
            $q->whereHas('category', function ($cat) use ($filters) {
                if (! empty($filters['type'])) {
                    $cat->where($this->getTable() . '.type', $filters['type']);
                }
                if (! empty($filters['queue'])) {
                    $cat->where($this->getTable() . '.queue', $filters['queue']);
                }
            });
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
