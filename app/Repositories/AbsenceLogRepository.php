<?php
namespace App\Repositories;

use App\AbsenceLog;
use Illuminate\Support\Collection;

class AbsenceLogRepository
{
    protected ?int $staffId;

    public function __construct(?int $staffId = null)
    {
        if ($staffId !== null) {
            $this->staffId = $staffId;
        }
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
            //'with'         => ['absence', 'shiftPlanner', 'category', 'category.workTypeDays'],
        ])->first();
    }

    public function findPresencePending()
    {
        return AbsenceLog::filter([
            'staff_id'  => $this->staffId,
            'where_has' => true,
            'type'      => 'presence',
            'status'    => [1],
            'order_by'  => 'start_date',
            'order_dir' => 'ASC',
        ])->first();
    }

    public function findOutPending(?int $id = null)
    {
        return AbsenceLog::filter([
            'staff_id'  => $this->staffId,
            'current_end' => true,
            'where_has' => true,
            'type'      => 'presence',
            'queue'     => 2,
            'status'    => [1],
            'order_by'  => 'start_date',
            'order_dir' => 'ASC',
            'absence_id' => $id !== null ? $id : null,
        ])->first();
    }

    public function findExcBreakPending(?int $id = null)
    {
        return AbsenceLog::filter([
            'staff_id'  => $this->staffId,
            'current' => false,
            'where_has' => true,
            'type'        => 'break',
            'type_not_in' => true,
            'status'      => [1],
            'order_by'    => 'start_date',
            'order_dir'   => 'ASC',
            'absence_id' => $id !== null ? $id : null,
        ])->first();
    }

    public function findBreakPending(?int $id = null)
    {
        return AbsenceLog::filter([
            'staff_id'  => $this->staffId,
            'where_has' => true,
            'type'      => 'break',
            'status'    => [1],
            'order_by'  => 'start_date',
            'order_dir' => 'ASC',
            'absence_id' => $id !== null ? $id : null,
        ])->first();
    }

    public function findExcuseEndPending(?int $id = null)
    {
        return AbsenceLog::filter([
            'staff_id'  => $this->staffId,
            'where_has' => true,
            'type'   => 'excuse',
            'queue'  => 2,
            'status' => [1],
            'absence_request_id' => $id !== null ? $id : null,
        ])->first();
    }

    public function findInActive()
    {
        return AbsenceLog::filter([
            'staff_id'  => $this->staffId,
            'where_has' => true,
            'type'   => 'presence',
            'queue'  => 1,
            'status' => [0],
        ])->first();
    }

    public function findVisitEndPending(?int $id = null)
    {
        return AbsenceLog::filter([
            'staff_id'  => $this->staffId,
            'where_has' => true,
            'type'   => 'visit',
            'queue'  => 2,
            'status' => [1],
            'absence_request_id' => $id !== null ? $id : null,
        ])->first();
    }

    public function findExtraEndPending(?int $id = null)
    {
        return AbsenceLog::filter([
            'staff_id'  => $this->staffId,
            'where_has' => true,
            'type'   => 'extra',
            'queue'  => 2,
            'status' => [1],
            'absence_request_id' => $id !== null ? $id : null,
        ])->first();
    }

    public function findExtraEndPendingAllDate(?int $id = null)
    {
        return AbsenceLog::filter([
            'staff_id'  => $this->staffId,
            'current_end' => false,
            'where_has' => true,
            'type'   => 'extra',
            'queue'  => 2,
            'status' => [1],
            'absence_request_id' => $id !== null ? $id : null,
        ])->first();
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
