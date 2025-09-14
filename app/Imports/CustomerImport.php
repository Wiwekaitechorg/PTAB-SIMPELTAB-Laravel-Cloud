<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class CustomerImport implements ToArray
{
    private $data;

    public function __construct()
    {
        $this->data = [];
    }
    public function array(array $rows)
    {
        foreach ($rows as $row) {
            $this->data[] = array('nomorrekening' => $row[0], 'phone' => $row[1], 'nomorhp' => $row[2]);
        }
    }

    public function getArray(): array
    {
        return $this->data;
    }
}
