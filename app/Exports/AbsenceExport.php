<?php

namespace App\Exports;


use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;

use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Concerns\WithTitle;

class AbsenceExport implements WithStyles, WithEvents, FromCollection, WithTitle, WithHeadings
{
    use Exportable;

    private $data;

    public function title(): string
    {
        return 'ABSENSI';
    }

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        // return [
        //     'Emp No',
        //     'AC-No',
        //     'No',
        //     'Name',
        //     'Auto-Asign',
        //     'Date',
        //     'TimeTable',
        //     'On_Duty',
        //     'Off_Duty',
        //     'Clock_in',
        //     'Clock_out',
        //     'Keterangan',
        //     'Memo'
        // ];

        return [
            'Emp No.',
            'AC-No.',
            'No.',
            'Name',
            'Auto-Assign',
            'Date',
            'Timetable',
            'On Duty',
            'Off Duty',
            'Clock In',
            'Clock Out',
            'Normal',
            'Real time',
            'Late',
            'Early',
            'Absent',
            'OT Time',
            'Work Time',
            'Exception',
            'Must C/In',
            'Must C/Out',
            'Department',
            'NDays',
            'WeekEnd',
            'Holiday',
            'ATT_Time',
            'NDays_OT',
            'WeekEnd_OT',
            'Holiday_OT',
            'Lembur',
            'Lembur > 4'
        ];
    }




    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:AC1')->getFont()->setBold(true);
    }

    public function registerEvents(): array
    {
        return array(
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->setAutoFilter('A1:' . $event->sheet->getDelegate()->getHighestColumn() . '1');
            }
        );
    }
}
