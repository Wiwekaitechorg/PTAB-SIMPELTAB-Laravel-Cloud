<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OneSignal;
use App\AbsenceRequest;
use App\MessageLog;
use App\Staff;

class ReminderPresence extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:presence';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reminder Presence';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //return 0;
        /* Job 1 */
        $data1 = [];
        if (date('w') == '0') {
            $day = '7';
        } else {
            $day = date('w');
        }
        // reguler
        // $message = MessageLog::get();
        // if (!$message) {
        //     Staff::join('work_types', 'work_types.id', '=', 'staffs.work_type_id')
        //         ->join('work_type_days', '=', 'work_type_days.work_type_id', '=', 'work_types.id')
        //         ->join('absence_categories', 'absence_categories.id', '=', 'work_type_days')
        //         ->where('day_id', $day)
        //         ->where('categories.type', 'presence')
        //         ->where('categories.queue', '1')
        //         ->get();
        // }
        $reguler =  Staff::selectRaw('staffs.*, _id_onesignal,time')
            ->join('users', 'users.staff_id', '=', 'staffs.id')
            ->join('work_types', 'work_types.id', '=', 'staffs.work_type_id')

            ->join('work_type_days', 'work_type_days.work_type_id', '=', 'work_types.id')
            ->join('absence_categories', 'absence_categories.id', '=', 'work_type_days.absence_category_id')
            ->where('day_id', $day)
            ->where('absence_categories.type', 'presence')
            ->where('absence_categories.queue', '1')
            ->get();

        for ($i = 0; $i < count($reguler); $i++) {
            $time = date("Y-m-d H:i:s",  strtotime(date('Y-m-d ' . $reguler[0]->time)));

            $start = date("Y-m-d H:i:s", strtotime('- ' . 2 . ' minutes', strtotime(date('Y-m-d H:i:s'))));
            $end = date("Y-m-d H:i:s", strtotime('+ ' . 3 . ' minutes', strtotime(date('Y-m-d H:i:s'))));
            // dd($start, $end, $time);

            if ($start < $time && $time < $end) {
                $data1[] = ['name' => $reguler[$i]->name, 'time' => $reguler[$i]->time, '_id_onesignal' => $reguler[$i]->_id_onesignal];
            }
        }


        // dd($data);
        $shift = Staff::selectRaw('staffs.*, _id_onesignal,time')
            ->join('users', 'users.staff_id', '=', 'staffs.id')
            ->join('shift_planner_staffs', 'shift_planner_staffs.staff_id', '=', 'staffs.id')
            ->join('shift_groups', 'shift_planner_staffs.shift_group_id', '=', 'shift_groups.id')
            ->join('shift_group_timesheets', 'shift_groups.id', '=', 'shift_group_timesheets.shift_group_id')
            ->join('absence_categories', 'absence_categories.id', '=', 'shift_group_timesheets.absence_category_id')
            // ->where('shift_planner')
            ->whereDate('shift_planner_staffs.start', '=', date('Y-m-d'))
            ->where('absence_categories.type', 'presence')
            ->where('absence_categories.queue', '1')
            ->get();
        // $data2 = [];

        for ($i = 0; $i < count($shift); $i++) {
            $time = date("Y-m-d H:i:s",  strtotime(date('Y-m-d ' . $shift[0]->time)));

            $start = date("Y-m-d H:i:s", strtotime('- ' . 2 . ' minutes', strtotime(date('Y-m-d H:i:s'))));
            $end = date("Y-m-d H:i:s", strtotime('+ ' . 3 . ' minutes', strtotime(date('Y-m-d H:i:s'))));

            if ($start < $time && $time < $end) {
                $data1[] = ['name' => $shift[$i]->name, 'time' => $shift[$i]->time, '_id_onesignal' => $shift[$i]->_id_onesignal];
            }
        }
        // dd($shift);
        // dd(count($data));
        for ($n = 0; $n < count($data1); $n++) {
            $message = $data1[$n]['name'] . " jangan lupa absen";
            if (!empty($data1[$n]['_id_onesignal'])) {
                OneSignal::sendNotificationToUser(
                    $message,
                    $data1[$n]['_id_onesignal'],
                    $url = null,
                    $data = null,
                    $buttons = null,
                    $schedule = null
                );
            }
        }
    }
}
