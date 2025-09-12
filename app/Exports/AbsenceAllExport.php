<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AbsenceAllExport implements FromCollection, WithTitle, WithHeadings
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
            "No",
            "Nama",
            "Tipe Kerja",
            "Jumlah masuk",
            "Jumlah Kegiatan/kontrol 1",
            "Jumlah Kegiatan/kontrol 2",
            "Jumlah Dinas Dalam",
            "Jumlah Dinas Luar",
            "Jumlah Cuti",
            "Jumlah Lembur < 4",
            "Jumlah Lembur >= 4",
            "Jumlah Permisi",
            "Jumlah Izin",
            "Jumlah Sakit",
            "Jumlah Dispen",
            "Tanpa Keterangan",
            "Jumlah Kerja",
            "Jumlah Libur",
            "Absen Bolong",
            "Absen Lambat",
        ];
    }
}
