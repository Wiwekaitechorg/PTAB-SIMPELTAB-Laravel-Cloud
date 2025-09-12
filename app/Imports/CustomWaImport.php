<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class CustomWaImport implements ToArray
{
    private $data;

    public function __construct()
    {
        $this->data = [];
    }
    public function array(array $rows)
    {
        foreach ($rows as $row) {
            $this->data[] = array('id' => $row[0], 'name' => $row[1], 'adress' => $row[2], 'phone' => $row[3]);
        }
    }

    public function getArray(): array
    {
        return $this->data;
    }
}
