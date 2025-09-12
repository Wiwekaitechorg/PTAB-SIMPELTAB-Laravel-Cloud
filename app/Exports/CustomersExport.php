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

class CustomersExport implements WithStyles, WithEvents, FromCollection, WithTitle, WithHeadings
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
            "nomorrekening",
            "namapelanggan",
            "alamat",
            "dusun",
            "desa",
            "kecamatan",
            "idgol",
            "idareal",
            "tglterdaftar",
            "tgltersambung",
            "status",
            "idurut",
            "idurutcode",
            "tipe",
            "idbiro",
            "idstatusdenda",
            "nomorhp",
            "nomorsurat",
            "tmplahir",
            "tgllahir",
            "alamat_detail",
            "alamat_ktp",
            "telp",
            "pekerjaan",
            "flagpendaftaran",
            "tglentry",
            "tglrab",
            "norab",
            "tglpanggil",
            "biayainstalasi",
            "cicilan",
            "flaginstalasi",
            "tglbap",
            "nobap",
            "tglberlakubap",
            "wmnomor",
            "wmmerek",
            "wmukuran",
            "wmstandmeter",
            "wmpetugas",
            "totalbayar",
            "status_posting",
            "flag_bayar",
            "sono",
            "sms",
            "rupiah_meter",
            "last_opp",
            "last_update",
            "file_wm",
            "file_denah",
            "_lat",
            "_lng",
            "_tahun_rekening",
            "_pemakaian air",
            "foto_rumh",
            "foto_ktp",
            "_foto_wmlng",
            "foto_lin",
        ];
    }




    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:BB1')->getFont()->setBold(true);
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
