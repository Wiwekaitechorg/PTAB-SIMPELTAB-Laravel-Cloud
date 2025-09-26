<?php
namespace App\Repositories;

use App\AbsenceLog;
use Illuminate\Support\Collection;

class AbsenceLogRepository
{
    protected Collection $collection;
    protected Collection $collectionAllDate;
    protected Collection $collectionEndDate;
    protected Collection $collectionEndDateWithTimesheets;
    //'with'         => ['absence', 'category', 'category.workTypeDays', 'category.shiftGroupTimesheets'],

    public function __construct(?int $staffId = null)
    {
        if ($staffId !== null) {
            $this->collection        = AbsenceLog::filter(['where_has' => true, 'staff_id' => $staffId])->get();
            $this->collectionAllDate = AbsenceLog::filter(['current' => false, 'where_has' => true, 'staff_id' => $staffId])->get();
            $this->collectionEndDate = AbsenceLog::filter(['current_end' => true, 'where_has' => true, 'staff_id' => $staffId])->get();
            $this->collectionEndDateWithTimesheets = AbsenceLog::filter(['current_end' => true, 'where_has' => true, 'staff_id' => $staffId, 'with' => ['absence', 'category', 'category.shiftGroupTimesheets']])->get();
        } else {
            // no staffId given — empty collection (or all logs if you want)
            $this->collection                      = collect();
            $this->collectionAllDate               = collect();
            $this->collectionEndDate               = collect();
            $this->collectionEndDateWithTimesheets = collect();
        }
    }

    private function findCollection($logs, array $criteria = [])
    {
        // first filter by criteria
        $filtered = $logs->filter(function ($log) use ($criteria) {
            // type filter (support single or array)
            if (isset($criteria['type'])) {
                $types = (array) $criteria['type'];
                if (isset($criteria['type_not_in']) && $criteria['type_not_in'] === true) {
                    if (in_array(optional($log->category)->type, $types, true)) {
                        return false;
                    }
                } else {
                    if (! in_array(optional($log->category)->type, $types, true)) {
                        return false;
                    }
                }
            }

            // queue filter (support single or array)
            if (isset($criteria['queue'])) {
                $queues = (array) $criteria['queue'];
                if (isset($criteria['queue_not_in']) && $criteria['queue_not_in'] === true) {
                    if (in_array(optional($log->category)->queue, $queues, true)) {
                        return false;
                    }
                } else {
                    if (! in_array(optional($log->category)->queue, $queues, true)) {
                        return false;
                    }
                }
            }

            // status filter
            if (isset($criteria['status']) && ! in_array($log->status, (array) $criteria['status'], true)) {
                return false;
            }

            // absence_request_id filter
            if (isset($criteria['absence_request_id']) &&
                $log->absence_request_id !== $criteria['absence_request_id']) {
                return false;
            }

            // absence_id filter
            if (isset($criteria['absence_id']) &&
                $log->absence_id !== $criteria['absence_id']) {
                return false;
            }

            // id filter
            if (isset($criteria['id']) &&
                $log->id !== $criteria['id']) {
                return false;
            }

            // // ensure BOTH work_type_id AND day_id are provided for the strict match
            // if (isset($criteria['work_type_id']) && isset($criteria['day_id'])) {
            //     $workTypeIds = (array) $criteria['work_type_id'];
            //     $dayIds      = (array) $criteria['day_id'];

            //     // get workTypeDays safely (could be relation or array)
            //     $workTypeDays = collect(optional($log->category)->workTypeDays ?? $log->category['work_type_days'] ?? []);

            //     // must have at least one row that matches BOTH conditions (AND)
            //     $hasMatch = $workTypeDays->contains(function ($wtd) use ($workTypeIds, $dayIds) {
            //         return in_array($wtd->work_type_id, $workTypeIds, true)
            //         && in_array($wtd->day_id, $dayIds, true);
            //     });

            //     if (! $hasMatch) {
            //         return false; // reject this log
            //     }
            // }

            return true; // passed all filters
        });

        // --- Trim nested workTypeDays to only the matching rows if criteria requested ---
        if (isset($criteria['work_type_id']) && isset($criteria['day_id'])) {
            $workTypeIds = (array) $criteria['work_type_id'];
            $dayIds      = (array) $criteria['day_id'];

            $filtered = $filtered->map(function ($log) use ($workTypeIds, $dayIds) {
                if (! $log->category) {
                    return $log;
                }

                // get existing collection/array of workTypeDays
                $wtds = collect($log->category->workTypeDays ?? $log->category['work_type_days'] ?? []);

                // keep only rows where BOTH work_type_id AND day_id match
                $matched = $wtds->filter(function ($wtd) use ($workTypeIds, $dayIds) {
                    return in_array($wtd->work_type_id, $workTypeIds, true)
                    && in_array($wtd->day_id, $dayIds, true);
                })->values();

                // attach the filtered list back to the category — support both Eloquent model and array
                if (is_object($log->category) && method_exists($log->category, 'setRelation')) {
                    // setRelation accepts a collection of models
                    $log->category->setRelation('workTypeDays', $matched);
                } else {
                    // plain array/stdClass — store as snake-case key too if necessary
                    $log->category['work_type_days'] = $matched->toArray();
                    $log->category->work_type_days   = $matched->toArray();
                }

                return $log;
            });
        }

        // --- Trim nested shiftGroupTimesheets to only the matching rows if criteria requested ---
        if (isset($criteria['shift_group_id'])) {
            $shiftGroupIds = (array) $criteria['shift_group_id'];

            $filtered = $filtered->map(function ($log) use ($shiftGroupIds) {
                if (! $log->category) {
                    return $log;
                }

                // get existing collection/array of shiftGroupTimesheets
                $wtds = collect($log->category->shiftGroupTimesheets ?? $log->category['shift_group_timesheets'] ?? []);

                // keep only rows where BOTH shift_group_id AND day_id match
                $matched = $wtds->filter(function ($wtd) use ($shiftGroupIds) {
                    return in_array($wtd->shift_group_id, $shiftGroupIds, true);
                })->values();

                // attach the filtered list back to the category — support both Eloquent model and array
                if (is_object($log->category) && method_exists($log->category, 'setRelation')) {
                    // setRelation accepts a collection of models
                    $log->category->setRelation('shiftGroupTimesheets', $matched);
                } else {
                    // plain array/stdClass — store as snake-case key too if necessary
                    $log->category['shift_group_timesheets'] = $matched->toArray();
                    $log->category->shift_group_timesheets   = $matched->toArray();
                }

                return $log;
            });
        }

        // default order field and direction
        $orderBy  = $criteria['order_by'] ?? 'id';
        $orderDir = $criteria['order_dir'] ?? 'DESC';

        // sort the filtered collection
        $filtered = $orderDir === 'DESC'
            ? $filtered->sortByDesc($orderBy)
            : $filtered->sortBy($orderBy);

        return $filtered->values(); // reset keys
    }

    public function findLogPresenceShiftGroupPending(int $shift_group_id, ?int $id = null)
    {
        // base criteria
        $criteria = [
            'type'         => 'presence',
            'status'       => [1],
            'shift_group_id' => $shift_group_id,
            'order_by'     => 'start_date',
            'order_dir'    => 'ASC',
        ];
        // add optional filter if id provided
        if ($id !== null) {
            $criteria['id'] = $id;
        }
        return $this->findCollection($this->collectionEndDateWithTimesheets, $criteria)?->first();
    }

    public function findLogPresenceWorkTypePending(int $work_type_id, int $day_id, ?int $id = null)
    {
        // base criteria
        $criteria = [
            'type'         => 'presence',
            'status'       => [1],
            'work_type_id' => $work_type_id,
            'day_id'       => $day_id,
            'order_by'     => 'start_date',
            'order_dir'    => 'ASC',
        ];
        // add optional filter if id provided
        if ($id !== null) {
            $criteria['id'] = $id;
        }
        return $this->findCollection($this->collectionEndDate, $criteria)?->first();
    }

    public function findLogPresencePending()
    {
        // base criteria
        $criteria = [
            'type'      => 'presence',
            'status'    => [1],
            'order_by'  => 'start_date',
            'order_dir' => 'ASC',
        ];
        return $this->findCollection($this->collection, $criteria)?->first();
    }

    public function findLogOutPending(?int $id = null)
    {
        // base criteria
        $criteria = [
            'type'      => 'presence',
            'queue'     => 2,
            'status'    => [1],
            'order_by'  => 'start_date',
            'order_dir' => 'ASC',
        ];
        // add optional filter if id provided
        if ($id !== null) {
            $criteria['absence_id'] = $id;
        }
        return $this->findCollection($this->collectionEndDate, $criteria)?->first();
    }

    public function findLogExcBreakPending(?int $id = null)
    {
        // base criteria
        $criteria = [
            'type'        => 'break',
            'type_not_in' => true,
            'status'      => [1],
            'order_by'    => 'start_date',
            'order_dir'   => 'ASC',
        ];
        // add optional filter if id provided
        if ($id !== null) {
            $criteria['absence_id'] = $id;
        }
        return $this->findCollection($this->collectionAllDate, $criteria)?->first();
    }

    public function findLogBreakPending(?int $id = null)
    {
        // base criteria
        $criteria = [
            'type'      => 'break',
            'status'    => [1],
            'order_by'  => 'start_date',
            'order_dir' => 'ASC',
        ];
        // add optional filter if id provided
        if ($id !== null) {
            $criteria['absence_id'] = $id;
        }
        return $this->findCollection($this->collection, $criteria)?->first();
    }

    public function findLogExcuseEndPending(?int $id = null)
    {
        // base criteria
        $criteria = [
            'type'   => 'excuse',
            'queue'  => 2,
            'status' => [1],
        ];
        // add optional filter if id provided
        if ($id !== null) {
            $criteria['absence_request_id'] = $id;
        }
        return $this->findCollection($this->collection, $criteria)?->first();
    }

    public function findLogInActive()
    {
        // base criteria
        $criteria = [
            'type'   => 'presence',
            'queue'  => 1,
            'status' => [0],
        ];
        return $this->findCollection($this->collection, $criteria)?->first();
        //return $this->collection;
    }

    public function findLogVisitEndPending(?int $id = null)
    {
        // base criteria
        $criteria = [
            'type'   => 'visit',
            'queue'  => 2,
            'status' => [1],
        ];
        // add optional filter if id provided
        if ($id !== null) {
            $criteria['absence_request_id'] = $id;
        }
        return $this->findCollection($this->collection, $criteria)?->first();
    }

    public function findLogExtraEndPendingAllDate(?int $id = null)
    {
        // base criteria
        $criteria = [
            'type'   => 'extra',
            'queue'  => 2,
            'status' => [1],
        ];
        // add optional filter if id provided
        if ($id !== null) {
            $criteria['absence_request_id'] = $id;
        }
        return $this->findCollection($this->collectionAllDate, $criteria)?->first();
    }

    public function save(AbsenceLog $absenceLog): AbsenceLog
    {
        $absenceLog->save();
        return $absenceLog;
    }

    public function findById(int $id): ?AbsenceLog
    {
        return AbsenceLog::find($id);
    }

    public function findBytype(string $type): ?AbsenceLog
    {
        return AbsenceLog::where('type', $type)->first();
    }

    public function all(array $filters = []): array
    {
        $query = AbsenceLog::query();
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['queue'])) {
            $query->where('queue', $filters['queue']);
        }
        return $query->with('absence')->get()->all();
    }

    public function paginate(int $perPage = 15, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = AbsenceLog::query();
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['queue'])) {
            $query->where('queue', $filters['queue']);
        }
        return $query->with('absence')->paginate($perPage);
    }

    public function delete(int $id): bool
    {
        return AbsenceLog::destroy($id) > 0;
    }

    public function updateStatus(int $id, string $status): bool
    {
        return AbsenceLog::where('id', $id)->update(['status' => $status]) > 0;
    }

    public function exists(int $id): bool
    {
        return AbsenceLog::where('id', $id)->exists();
    }
}
