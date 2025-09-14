<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\DefaultValueBinder;                 // ← use this binder
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OppExport extends DefaultValueBinder implements
    WithCustomValueBinder,    // ← tell Laravel-Excel to use this binder
    WithStyles,
    WithEvents,
    FromCollection,
    WithTitle,
    WithHeadings,
    WithColumnFormatting
{
    use Exportable;

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Customers';
    }

    public function collection()
    {
        // Keep original data; binder will control cell types
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            "nomorrekening", // A
            "namapelanggan", // B
            "email",         // C
            "alamat",        // D
            "gender",        // E
            "last_update",   // F
            "desctype",      // G
            "desc",          // H
            "lat",           // I
            "lng",           // J
            "noktp",         // K
            "telp",          // L
            "status",        // M
            "foto_rumah",    // N
            "foto_ktp",      // O
            "foto_wm",       // P
        ];
    }

    // 🔒 Force columns A, K, L to be written as TEXT at the cell level
    public function bindValue(Cell $cell, $value): bool
    {
        $col = $cell->getColumn();

        if (in_array($col, ['A', 'K', 'L'], true)) {
            // Ensure we pass a scalar string; null becomes empty string
            $stringValue = is_null($value) ? '' : (string) $value;

            // Explicitly set as string so Excel will NEVER convert to scientific notation
            $cell->setValueExplicit($stringValue, DataType::TYPE_STRING);
            return true; // we handled the binding
        }

        // Fallback to default behaviour for other columns
        return parent::bindValue($cell, $value);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:BB1')->getFont()->setBold(true);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->setAutoFilter(
                    'A1:' . $event->sheet->getDelegate()->getHighestColumn() . '1'
                );
            },
        ];
    }

    public function columnFormats(): array
    {
        // Optional but nice to keep the display as text too
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'K' => NumberFormat::FORMAT_TEXT,
            'L' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
