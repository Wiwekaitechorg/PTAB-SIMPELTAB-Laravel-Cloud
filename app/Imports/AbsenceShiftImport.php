<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class AbsenceShiftImport implements ToArray
{
    private $data;

    public function __construct()
    {
        $this->data = [];
    }
    public function array(array $rows)
    {
        foreach ($rows as $row) {
            $this->data[] = array(
                'nik' => $row[0],
                'shiftGroup' => $row[1],
                'shift' => $row[2],
                'date' => $row[3],
                'category_id' => $row[4],
                'group_id' => $row[5],
                'description' => $row[6]
            );
        }
    }

    public function getArray(): array
    {
        return $this->data;
    }
}
