<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AbsenceReport implements FromCollection, WithHeadings
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
            'No',
            'Kode',
            'Nama',
            'Karyawan',
            'Sub_Bagian',
            'Job',
            'Total Hari',
            'Total Libur',
            'Total Efektif Kerja',
            'Total Hadir',
            'Total Alfa',
            'Total Izin',
            'Total Dinas Luar',
            'Total Cuti',
            'Total Jam Kerja',
            'Total Jam Istirahat',
            'Total Jam Lembur',
            'Total Jam Dinas Dalam',
            'Total Hari Dinas Dalam',
            'Total Jam Terlambat',
            'Total Hari Terlambat',
            'Total Jam Permisi',
            'Total Hari Permisi',
        ];
    }
}
