<?php
namespace App\Repositories;

use App\AbsenceRequest;
use Illuminate\Support\Collection;

class AbsenceRequestRepository
{
    protected Collection $absence_request;
    protected Collection $absence_request_with_pending;

    public function __construct(?int $staffId = null)
    {
        if ($staffId !== null) {
            AbsenceRequest::$baseFilter['staff_id'] = $staffId;

            //general request
            $this->absence_request              = AbsenceRequest::filter(['current' => true])->get()->groupBy('category');
            $this->absence_request_with_pending = AbsenceRequest::filter(['status' => ['approve', 'active', 'pending']])->get()->groupBy('category');
        } else {
            $this->absence_request              = new Collection();
            $this->absence_request_with_pending = new Collection();
        }
    }

    public function getAbsenceRequest()
    {
        return $this->absence_request;
    }

    public function getAbsenceRequestWithPending()
    {
        return $this->absence_request_with_pending;
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

    public function findBytype(string $type): ?AbsenceRequest
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
