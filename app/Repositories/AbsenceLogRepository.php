<?php
namespace App\Repositories;

use App\AbsenceLog;

class AbsenceLogRepository
{
    protected ?int $staffId;
    protected ?Carbon $startDate;
    protected ?Carbon $endDate;
    protected array $baseFilters;

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
        $this->baseFilters = [
            'staff_id'   => $this->staffId,
            'where_has'  => true,
            'start_date' => $this->startDate,
            'end_date'   => $this->endDate,
        ];
    }

    public function findPresenceShiftGroupPending(int $shift_group_id, ?int $id = null)
    {
        return AbsenceLog::filter([
            'staff_id'       => $this->staffId,
            'current_end'    => true,
            'where_has'      => true,
            'with'           => ['absence', 'category', 'category.shiftGroupTimesheets'],
            'type'           => 'presence',
            'status'         => [1],
            'shift_group_id' => $shift_group_id,
            'id'             => $id !== null ? $id : null,
            'start_date'     => $this->startDate,
            'end_date'       => $this->endDate,
        ])->first();
    }

    public function findPresenceWorkTypePending(int $work_type_id, int $day_id, ?int $id = null)
    {
        return AbsenceLog::filter([
            'staff_id'     => $this->staffId,
            'current_end'  => true,
            'where_has'    => true,
            'type'         => 'presence',
            'status'       => [1],
            'work_type_id' => $work_type_id,
            'day_id'       => $day_id,
            'order_by'     => 'start_date',
            'order_dir'    => 'ASC',
            'id'           => $id !== null ? $id : null,
            'start_date'   => $this->startDate,
            'end_date'     => $this->endDate,
        ])->first();
    }

    public function findPresencePending()
    {
        return AbsenceLog::filter([
            'staff_id'   => $this->staffId,
            'where_has'  => true,
            'type'       => 'presence',
            'status'     => [1],
            'order_by'   => 'start_date',
            'order_dir'  => 'ASC',
            'start_date' => $this->startDate,
            'end_date'   => $this->endDate,
        ])->first();
    }

    public function findOutPending(?int $id = null)
    {
        return AbsenceLog::filter([
            'staff_id'    => $this->staffId,
            'current_end' => true,
            'where_has'   => true,
            'type'        => 'presence',
            'queue'       => 2,
            'status'      => [1],
            'order_by'    => 'start_date',
            'order_dir'   => 'ASC',
            'absence_id'  => $id !== null ? $id : null,
            'start_date'  => $this->startDate,
            'end_date'    => $this->endDate,
        ])->first();
    }

    public function findExcBreakPending(?int $id = null)
    {
        return AbsenceLog::filter([
            'staff_id'    => $this->staffId,
            'current'     => false,
            'where_has'   => true,
            'type'        => 'break',
            'type_not_in' => true,
            'status'      => [1],
            'order_by'    => 'start_date',
            'order_dir'   => 'ASC',
            'absence_id'  => $id !== null ? $id : null,
            'start_date'  => $this->startDate,
            'end_date'    => $this->endDate,
        ])->first();
    }

    public function findBreakPending(?int $id = null)
    {
        return AbsenceLog::filter([
            'staff_id'   => $this->staffId,
            'where_has'  => true,
            'type'       => 'break',
            'status'     => [1],
            'order_by'   => 'start_date',
            'order_dir'  => 'ASC',
            'absence_id' => $id !== null ? $id : null,
            'start_date' => $this->startDate,
            'end_date'   => $this->endDate,
        ])->first();
    }

    public function findExcuseEndPending(?int $id = null)
    {
        return AbsenceLog::filter([
            'staff_id'           => $this->staffId,
            'where_has'          => true,
            'type'               => 'excuse',
            'queue'              => 2,
            'status'             => [1],
            'absence_request_id' => $id !== null ? $id : null,
            'start_date'         => $this->startDate,
            'end_date'           => $this->endDate,
        ])->first();
    }

    public function findInActive()
    {
        return AbsenceLog::filter([
            'staff_id'   => $this->staffId,
            'where_has'  => true,
            'type'       => 'presence',
            'queue'      => 1,
            'status'     => [0],
            'start_date' => $this->startDate,
            'end_date'   => $this->endDate,
        ])->first();
    }

    public function findVisitEndPending(?int $id = null)
    {
        return AbsenceLog::filter([
            'staff_id'           => $this->staffId,
            'where_has'          => true,
            'type'               => 'visit',
            'queue'              => 2,
            'status'             => [1],
            'absence_request_id' => $id !== null ? $id : null,
            'start_date'         => $this->startDate,
            'end_date'           => $this->endDate,
        ])->first();
    }

    public function findExtraEndPending(?int $id = null)
    {
        return AbsenceLog::filter([
            'staff_id'           => $this->staffId,
            'where_has'          => true,
            'type'               => 'extra',
            'queue'              => 2,
            'status'             => [1],
            'absence_request_id' => $id !== null ? $id : null,
            'start_date'         => $this->startDate,
            'end_date'           => $this->endDate,
        ])->first();
    }

    public function findOpen(string $type, ?int $absence_request_id = null)
    {
        $filters = array_merge($this->baseFilters, [
            'type'               => $type,
            'queue'              => [1, 2],
            'status'             => [1],
            'order_by'           => 'id',
            'order_dir'          => 'ASC',
            'absence_request_id' => $absence_request_id !== null ? $absence_request_id : null,
        ]);
        return AbsenceLog::filter($filters)->first();
    }

    public function findActiveExpired(string $type, ?int $absence_request_id = null)
    {
        $filters = array_merge($this->baseFilters, [
            'expired_end'        => true,
            'type'               => $type,
            'queue'              => 2,
            'status'             => [1],
            'absence_request_id' => $absence_request_id !== null ? $absence_request_id : null,
        ]);
        $endExpired  = AbsenceLog::filter($filters)->first();
        $startClosed = $this->findClosedByAbsenceId($endExpired->absence->id, $type, 1);
        if (! $startClosed) {
            return null;
        } else {
            return $endExpired;
        }
    }

    public function findClosedByAbsenceId(int $absence_id, string $type, int $queue)
    {
        $filters = array_merge($this->baseFilters, [
            'current'    => false,
            'type'       => $type,
            'queue'      => $queue,
            'status'     => [0],
            'absence_id' => $absence_id,
        ]);
        return AbsenceLog::filter($filters)->first();
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

    public function findByType(string $type): ?AbsenceLog
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
