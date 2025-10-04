<?php
namespace App\Repositories;

use App\Absence_categories;

class AbsenceCategoryRepository
{
    public function __construct(?int $staffId = null)
    {
        if ($staffId !== null) {

        } else {

        }
    }

    public function findByWorkTypeAndDay(int $work_type_id, int $day): Absence_categories
    {
        return Absence_categories::filter([
            'work_type_id' => $work_type_id,
            'day_id'       => $day,
        ])->first();
    }

    public function save(Absence_categories $absenceCategory): Absence_categories
    {
        $absenceCategory->save();
        return $absenceCategory;
    }

    public function getByType(string $type): ?Absence_categories
    {
        return Absence_categories::where('type', $type)->get();
    }

    public function findById(int $id): ?Absence_categories
    {
        return Absence_categories::find($id);
    }

    public function findBytype(string $type): ?Absence_categories
    {
        return Absence_categories::where('type', $type)->first();
    }

    public function all(array $filters = []): array
    {
        $query = Absence_categories::query();
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
        $query = Absence_categories::query();
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
        return Absence_categories::destroy($id) > 0;
    }

    public function updateStatus(int $id, string $status): bool
    {
        return Absence_categories::where('id', $id)->update(['status' => $status]) > 0;
    }

    public function exists(int $id): bool
    {
        return Absence_categories::where('id', $id)->exists();
    }
}
