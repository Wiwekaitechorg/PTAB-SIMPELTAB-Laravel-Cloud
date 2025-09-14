<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Psm extends Model
{
    protected $connection = 'mysql2';

    protected $table = 'tblpsm';

    protected $primaryKey = ['nomorrekening', 'tahunrekening'];
    
    public $incrementing = false;

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
