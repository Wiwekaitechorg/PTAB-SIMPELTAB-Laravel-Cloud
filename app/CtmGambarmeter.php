<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CtmGambarmeter extends Model
{
    protected $connection = 'mysql2';

    protected $table = 'gambarmeter';

    protected $primaryKey = 'idgambar';

    public $timestamps = false;

    protected $fillable = [
        'nomorpengirim',
        'bulanrekening',
        'tahunrekening',
        'tanggal',
        'filegambar',
        'operator',
        'infowaktu',
        'filegambar1',
        '_synced'
    ];
}
