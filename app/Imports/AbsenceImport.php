<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class AbsenceImport implements ToArray
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
                'category_id' => $row[2],
                'group_visit' => $row[3],
                'description' => $row[4]
            );
        }
    }

    public function getArray(): array
    {
        return $this->data;
    }
}
