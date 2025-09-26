<?php
namespace App\Repositories;

use App\ShiftPlannerStaffs;
use Illuminate\Support\Collection;

class ShiftPlannerStaffsRepository
{
    public function __construct(?int $staffId = null)
    {
        if ($staffId !== null) {
            
        } else {
            
        }
    }    

    public function save(ShiftPlannerStaffs $absenceRequest): ShiftPlannerStaffs
    {
        $absenceRequest->save();
        return $absenceRequest;
    }

    public function findById(int $id): ?ShiftPlannerStaffs
    {
        return ShiftPlannerStaffs::find($id);
    }

    public function findBytype(string $type): ?ShiftPlannerStaffs
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
