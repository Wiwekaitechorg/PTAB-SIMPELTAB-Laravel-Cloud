<?php
namespace App\Repositories;

use App\ShiftGroupTimesheets;

class ShiftGroupTimesheetRepository
{
    public function __construct(?int $staffId = null)
    {
        if ($staffId !== null) {

        } else {

        }
    }

    public function getByShiftGroup(int $shift_group_id): ShiftGroupTimesheets
    {
        return ShiftGroupTimesheets::filter([
            'shift_group_id'       => $shift_group_id,
            'with_category_fields' => true,
            'order_by_queue'       => true,
            'with'                 => ['absenceCategory', 'shiftGroup'],
        ])->get();
    }

    public function save(ShiftGroupTimesheets $shiftGroupTimesheets): ShiftGroupTimesheets
    {
        $shiftGroupTimesheets->save();
        return $shiftGroupTimesheets;
    }

    public function findById(int $id): ?ShiftGroupTimesheets
    {
        return ShiftGroupTimesheets::find($id);
    }

    public function findByType(string $type): ?ShiftGroupTimesheets
    {
        return ShiftGroupTimesheets::where('type', $type)->first();
    }

    public function all(array $filters = []): array
    {
        $query = ShiftGroupTimesheets::query();
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
        $query = ShiftGroupTimesheets::query();
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
        return ShiftGroupTimesheets::destroy($id) > 0;
    }

    public function updateStatus(int $id, string $status): bool
    {
        return ShiftGroupTimesheets::where('id', $id)->update(['status' => $status]) > 0;
    }

    public function exists(int $id): bool
    {
        return ShiftGroupTimesheets::where('id', $id)->exists();
    }
}
