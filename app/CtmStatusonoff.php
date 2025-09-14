<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CtmStatusonoff extends Model
{
    protected $connection = 'mysql2';

    protected $table = 'tblstatusonoff';

    public $timestamps = false;

    protected $fillable = [
        'nomorrekening',
        'bulan',
        'tahun',
        'status',        
        '_synced'
    ];
}
