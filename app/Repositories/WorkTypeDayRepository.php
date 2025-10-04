<?php
namespace App\Repositories;

use App\WorkTypeDays;

class WorkTypeDayRepository
{
    public function __construct(?int $staffId = null)
    {
        if ($staffId !== null) {

        } else {

        }
    }

    public function getPresenceByWorkTypeAndDay(int $work_type_id, int $day): WorkTypeDays
    {
        return WorkTypeDay::filter([
            'work_type_id'  => $work_type_id,
            'day_id'        => $day,
            'category_type' => 'presence',
            'with_category' => true,
            'order_by'      => 'queue',
            'order_dir'     => 'ASC',
        ])->get();
    }

    public function save(WorkTypeDays $workTypeDays): WorkTypeDays
    {
        $workTypeDays->save();
        return $workTypeDays;
    }

    public function findById(int $id): ?WorkTypeDays
    {
        return WorkTypeDays::find($id);
    }

    public function findByType(string $type): ?WorkTypeDays
    {
        return WorkTypeDays::where('type', $type)->first();
    }

    public function all(array $filters = []): array
    {
        $query = WorkTypeDays::query();
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['queue'])) {
            $query->where('queue', $filters['queue']);
        }
        return $query->with('workTypeDays')->get()->all();
    }

    public function paginate(int $perPage = 15, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = WorkTypeDays::query();
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['queue'])) {
            $query->where('queue', $filters['queue']);
        }
        return $query->with('workTypeDays')->paginate($perPage);
    }

    public function delete(int $id): bool
    {
        return WorkTypeDays::destroy($id) > 0;
    }

    public function updateStatus(int $id, string $status): bool
    {
        return WorkTypeDays::where('id', $id)->update(['status' => $status]) > 0;
    }

    public function exists(int $id): bool
    {
        return WorkTypeDays::where('id', $id)->exists();
    }
}
