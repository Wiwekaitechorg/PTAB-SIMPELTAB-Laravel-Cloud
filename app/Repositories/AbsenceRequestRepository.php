<?php
namespace App\Repositories;

use App\AbsenceRequest;
use Carbon\Carbon;

class AbsenceRequestRepository
{
    protected ?int $staffId;
    protected ?Carbon $startDate;
    protected ?Carbon $endDate;
    public function __construct(?int $staffId = null, ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        if ($staffId !== null) {
            $this->staffId = $staffId;
        }
        if ($startDate !== null) {
            $this->startDate = $startDate;
        }
        if ($endDate !== null) {
            $this->endDate = $endDate;
        }
    }

    public function findByCategoryWithPending(string $category): ?AbsenceRequest
    {
        return AbsenceRequest::filter([
            'staff_id' => $this->staffId,
            'category' => $category,
            'status'   => ['approve', 'active', 'pending'],
            'start_date' => $this->startDate,
            'end_date'   => $this->endDate,
        ])->first();
    }

    public function findByCategory(string $category): ?AbsenceRequest
    {
        return AbsenceRequest::filter([
            'staff_id' => $this->staffId,
            'category' => $category,
            'start_date' => $this->startDate,   
            'end_date'   => $this->endDate,
        ])->first();
    }

    public function findByCategoryNotIn(string $category, array $notIn): ?AbsenceRequest
    {
        $filters             = [];
        $filters['staff_id'] = $this->staffId;
        $filters['category'] = $category;
        $filters['start_date'] = $this->startDate;
        $filters['end_date']   = $this->endDate;
        // Not filters
        foreach ($notIn as $n) {
            foreach ($n as $col => $val) {
                $filters[$col . '_not'] = $val;
            }
        }

        return AbsenceRequest::filter($filters)->first();
    }

    public function save(AbsenceRequest $absenceRequest): AbsenceRequest
    {
        $absenceRequest->save();
        return $absenceRequest;
    }

    public function findById(int $id): ?AbsenceRequest
    {
        return AbsenceRequest::find($id);
    }

    public function findByType(string $type): ?AbsenceRequest
    {
        return AbsenceRequest::where('type', $type)->first();
    }

    public function all(array $filters = []): array
    {
        $query = AbsenceRequest::query();
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
        $query = AbsenceRequest::query();
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
        return AbsenceRequest::destroy($id) > 0;
    }

    public function updateStatus(int $id, string $status): bool
    {
        return AbsenceRequest::where('id', $id)->update(['status' => $status]) > 0;
    }

    public function exists(int $id): bool
    {
        return AbsenceRequest::where('id', $id)->exists();
    }
}
