<?php
namespace App\Repositories;

use App\ShiftPlannerStaffs;

class ShiftPlannerStaffRepository
{
    public function __construct(?int $staffId = null)
    {
        if ($staffId !== null) {

        } else {

        }
    }

    public function getLogYet(int $staffId): ShiftPlannerStaffs
    {
        $shiftPlannerStaffs = ShiftPlannerStaffs::filter([
            'staff_id'       => $staffId,
            'start_date'     => now()->toDateString(),
            'no_absence'     => true,
            'order_by_queue' => true, // just a flag to enable the orderBy subselect
        ])
            ->get();
        return $shiftPlannerStaffs;
    }

    public function save(ShiftPlannerStaffs $shiftPlannerStaffs): ShiftPlannerStaffs
    {
        $shiftPlannerStaffs->save();
        return $shiftPlannerStaffs;
    }

    public function findById(int $id): ?ShiftPlannerStaffs
    {
        return ShiftPlannerStaffs::find($id);
    }

    public function findByType(string $type): ?ShiftPlannerStaffs
    {
        return ShiftPlannerStaffs::where('type', $type)->first();
    }

    public function all(array $filters = []): array
    {
        $query = ShiftPlannerStaffs::query();
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
        $query = ShiftPlannerStaffs::query();
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
        return ShiftPlannerStaffs::destroy($id) > 0;
    }

    public function updateStatus(int $id, string $status): bool
    {
        return ShiftPlannerStaffs::where('id', $id)->update(['status' => $status]) > 0;
    }

    public function exists(int $id): bool
    {
        return ShiftPlannerStaffs::where('id', $id)->exists();
    }
}
