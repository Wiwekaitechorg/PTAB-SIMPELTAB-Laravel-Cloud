<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CtmOpp extends Model
{
    protected $connection = 'mysql2';

    protected $table = 'tblopp';

    protected $primaryKey = 'nomorrekening';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        
    ];
    public function pelanggan()
    {
        return $this->belongsTo(CtmPelanggan::class, 'nomorrekening', 'nomorrekening');
    }
}
