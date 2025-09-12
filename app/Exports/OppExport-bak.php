<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OppExport implements WithStyles, WithEvents, FromCollection, WithTitle, WithHeadings
{
    use Exportable;

    private $data;

    public function title(): string
    {
        return 'Customers';
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
            "nomorrekening",
            "namapelanggan",
            "email",
            "alamat",
            "gender",
            "last_update",
            "desctype",
            "desc",
            "lat",
            "lng",
            "noktp",
			"telp",
            "status",
            "foto_rumah",
            "foto_ktp",
            "foto_wm",
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:BB1')->getFont()->setBold(true);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->setAutoFilter('A1:' . $event->sheet->getDelegate()->getHighestColumn() . '1');
            },
        ];
    }

    public function columnFormats(): array
    {
        return [
            'K' =>  "0",
          ];

    }
}
