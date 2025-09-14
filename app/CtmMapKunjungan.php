<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CtmMapKunjungan extends Model
{
    protected $connection = 'mysql2';

    protected $table = 'map_kunjungan';

    protected $primaryKey = 'idkunjungan';

    public $timestamps = false;

    protected $fillable = [
        'bulan',
        'tahun',
        'nomorrekening',
        'lat',
        'lng',
        'time',
        'accuracy',
        'statuskunjungan',
        '_synced'
    ];
}
