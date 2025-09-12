<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AbsenceAccuracy implements FromCollection, WithHeadings
{
    use Exportable;

    private $data;

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
            'name',
            'device',
            'avg_accuracy',
            'max_accuracy',
            'min_accuracy',
            'avg_distance',
            'max_distance',
            'min_distance',
        ];
    }
}
