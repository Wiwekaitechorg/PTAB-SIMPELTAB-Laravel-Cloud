<?php

namespace App\Services;

use App\Repositories\AbsenceRepository;
use App\Repositories\AbsenceRequestRepository;
use App\Repositories\AbsenceLogRepository;
use App\Repositories\StaffRepository;
use App\Models\MessageLog;
use App\Models\Visit;

class AbsenceMenuService
{
    protected int $staff_id;

    // Request states
    protected $leave;
    protected $permission;
    protected $duty;
    protected $extra;
    protected $excuse;

    // Menu states
    protected string $menuLeave = "OFF";
    protected string $menuPermission = "OFF";
    protected string $menuDuty = "OFF";
    protected string $menuVisit = "OFF";
    protected string $menuExtra = "OFF";
    protected string $menuExcuse = "OFF";
    protected string $menuWaiting = "OFF";

    // Other flags
    protected string $waitingMessage = "";
    protected string $geolocationOff = "OFF";

    protected $visitC;
    protected $extraC;

    /**
     * Request categories config
     */
    protected array $categories = [
        'leave'      => ['menu' => 'menuLeave',      'pending' => 'Menunggu Persetujuan Cuti',    'close' => 'Menunggu Persetujuan Cuti'],
        'permission' => ['menu' => 'menuPermission', 'pending' => 'Menunggu Persetujuan Izin',    'close' => 'Menunggu Persetujuan Izin'],
        'visit'      => ['menu' => 'menuDuty',       'pending' => 'Menunggu Persetujuan Dinas',   'close' => 'Menunggu Persetujuan Dinas'],
        'extra'      => ['menu' => 'menuExtra'], // handled separately
        'excuse'     => ['menu' => 'menuExcuse'],    // handled separately
    ];

    public function __construct(int $staff_id)
    {
        $this->staff_id = $staff_id;
    }

    /**
     * Main method: build absence menu data
     */
    public function getMenuData(
        AbsenceRepository $absenceRepo,
        AbsenceRequestRepository $absenceRequestRepo,
        AbsenceLogRepository $absenceLogRepo,
        StaffRepository $staffRepo
    ) {
        // Process categories dynamically
        foreach ($this->categories as $category => $config) {
            $request = $absenceRequestRepo->findByCategory($category);
            $this->{$category} = $request;

            if (! $request) continue;

            // Special cases
            if ($category === 'visit') {
                $this->handleVisit($request, $absenceLogRepo);
                continue;
            }
            if ($category === 'extra') {
                $this->handleExtra($request, $absenceLogRepo);
                continue;
            }
            if ($category === 'excuse') {
                $this->menuExcuse = "ON";
                continue;
            }

            // Default handler (leave, permission, duty)
            if ($this->handleRequestStatus($request, $config['menu'], $config['pending'], $config['close'])) {
                return response()->json($this->sendResponse());
            }
        }

        // Handle staff absence (shift vs regular)
        $staff = $staffRepo->getById($this->staff_id);
        if ($staff->shift_type === "shift") {
            $this->handleShiftAbsence($absenceRepo, $absenceLogRepo);
        } else {
            $this->handleRegularAbsence($absenceRepo, $absenceLogRepo);
        }

        return response()->json($this->sendResponse());
    }

    /**
     * === Helpers ===
     */

    private function handleRequestStatus($request, string $menuType, string $waitingPending, string $waitingClose): bool
    {
        if (! $request) return false;

        if ($request->status === "pending") {
            $this->menuWaiting = "ON";
            $this->waitingMessage = $waitingPending;
        } elseif ($request->status === "close") {
            $this->menuWaiting = "ON";
            $this->waitingMessage = $waitingClose;
        } else {
            $this->{$menuType} = "ON";
        }

        return true;
    }

    private function handleVisit($request, $absenceLogRepo): void
    {
        $visit = $absenceLogRepo->findVisitEndPending($request->id);
        if ($visit) {
            $visitEtc = Visit::where('absence_request_id', $visit->absence_request_id)->first();
            $this->menuVisit = $visitEtc ? "ON" : "ACTIVE";
        } else {
            $this->visitC = $this->absenceCategory->getByType('visit');
        }
    }

    private function handleExtra($request, $absenceLogRepo): void
    {
        $extra = $absenceLogRepo->findExtraEndPendingAllDate($request->id);
        $this->extraC = $this->absenceCategory->getByType('extra');
        $this->menuExtra = "ON";

        if ($request->type === "outside") {
            $this->geolocationOff = "ON";
        }
    }

    private function handleShiftAbsence($absenceRepo, $absenceLogRepo): void
    {
        $day = $this->getDayNumber();
        $list_absence = $absenceRepo->getAbsenceShift($this->staff_id, $day);

        if (count($list_absence) > 0) {
            $absence = $absenceRepo->create([
                'staff_id' => $this->staff_id,
                'type'     => 'shift',
                'date'     => now()->toDateString(),
            ]);

            $this->createAbsenceLogs($absence, $list_absence, $absenceLogRepo);
        }
    }

    private function handleRegularAbsence($absenceRepo, $absenceLogRepo): void
    {
        $day = $this->getDayNumber();
        $list_absence = $absenceRepo->getAbsence($this->staff_id, $day);

        if (count($list_absence) > 0) {
            $absence = $absenceRepo->create([
                'staff_id' => $this->staff_id,
                'type'     => 'regular',
                'date'     => now()->toDateString(),
            ]);

            $this->createAbsenceLogs($absence, $list_absence, $absenceLogRepo);
        }
    }

    private function createAbsenceLogs($absence, $list_absence, $absenceLogRepo): void
    {
        foreach ($list_absence as $item) {
            $absenceLogRepo->save($absence->id, $item);
            MessageLog::create([
                'absence_id'       => $absence->id,
                'shift_planner_id' => $item->shift_planner_id ?? null,
                'shift_group_timesheet_id' => $item->shift_group_timesheet_id ?? null,
                'type'             => 'absence_created',
            ]);
        }
    }

    private function getDayNumber(): int
    {
        return date('w') == '0' ? 7 : (int) date('w');
    }

    /**
     * Send response data
     */
    private function sendResponse(): array
    {
        return [
            'menuLeave'      => $this->menuLeave,
            'menuPermission' => $this->menuPermission,
            'menuDuty'       => $this->menuDuty,
            'menuVisit'      => $this->menuVisit,
            'menuExtra'      => $this->menuExtra,
            'menuExcuse'     => $this->menuExcuse,
            'menuWaiting'    => $this->menuWaiting,
            'waitingMessage' => $this->waitingMessage,
            'geolocationOff' => $this->geolocationOff,
        ];
    }
}
