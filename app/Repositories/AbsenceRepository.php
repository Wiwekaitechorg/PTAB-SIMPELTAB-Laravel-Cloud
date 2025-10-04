<?php
namespace App\Repositories;

use App\Absence;

class AbsenceRepository
{
    public function __construct(?int $staffId = null)
    {
        if ($staffId !== null) {

        } else {

        }
    }

    public function findTodayByStaffExcCategory(int $staffId, int $exc): Absence
    {
        return Absence::filter([
            'date'               => now()->toDateString(), // today
            'staff_id'           => $staffId,
            'exclude_categories' => $exc,
            'with_logs'          => true,
        ])->first();
    }

    public function getTodayByStaff(int $staffId): Absence
    {
        return Absence::filter([
            'date'               => now()->toDateString(), // today
            'staff_id'           => $staffId,
            'with_logs'          => true,
        ])->first();
    }

    public function save(Absence $absence): Absence
    {
        $absence->save();
        return $absence;
    }

    public function findById(int $id): ?Absence
    {
        return Absence::find($id);
    }

    public function findByType(string $type): ?Absence
    {
        return Absence::where('type', $type)->first();
    }

    public function all(array $filters = []): array
    {
        $query = Absence::query();
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
        $query = Absence::query();
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
        return Absence::destroy($id) > 0;
    }

    public function updateStatus(int $id, string $status): bool
    {
        return Absence::where('id', $id)->update(['status' => $status]) > 0;
    }

    public function exists(int $id): bool
    {
        return Absence::where('id', $id)->exists();
    }
}
