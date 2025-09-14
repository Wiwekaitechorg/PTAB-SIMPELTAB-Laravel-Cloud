<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class AbsenceCheckImport implements ToArray
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
                'date' => $row[1],
                'description' => $row[2],
            );
        }
    }

    public function getArray(): array
    {
        return $this->data;
    }
}
