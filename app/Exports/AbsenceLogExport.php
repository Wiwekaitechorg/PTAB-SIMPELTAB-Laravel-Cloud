<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AbsenceLogExport implements FromCollection, WithTitle, WithHeadings
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
        return [
            'Departemen',
            'Name',
            'No.',
            'Date/Time',
            'Location',
            'ID Number',
            'VerifyCode',
            'shift',
            'status',
            'CardNo'
        ];
    }
}
