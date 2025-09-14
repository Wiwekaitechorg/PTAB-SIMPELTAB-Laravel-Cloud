<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerMaps extends Model
{
    protected $connection = 'mysql2';

    protected $table = 'map_koordinatpelanggan';

    protected $primaryKey = 'idkoordinat';

    public $timestamps = false;    
}
