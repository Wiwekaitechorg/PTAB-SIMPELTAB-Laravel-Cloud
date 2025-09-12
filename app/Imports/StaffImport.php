<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class StaffImport implements ToArray
{
    private $data;

    public function __construct()
    {
        $this->data = [];
    }
    public function array(array $rows)
    {
        foreach ($rows as $row) {
            $this->data[] = array('id' => $row[0], 'work_unit_id' => $row[2]);
        }
    }

    public function getArray(): array
    {
        return $this->data;
    }
}
