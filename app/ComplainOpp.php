<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ComplainOpp extends Model
{
    protected $table = 'complain_opp';
    protected $fillable = [
        'operator',
        'nomorrekening',
    ];
    public function pelanggan()
    {
        return $this->belongsTo(CtmPelanggan::class, 'nomorrekening', 'nomorrekening');
    }
}
