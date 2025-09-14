<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Audited extends Model
{
    protected $table = 'audited';
    protected $fillable = [
        'name',
        'periode',
        'file',
    ];

    public function scopeFilterYear($query, $year)
    {
        if($year !=''){
            $query->where('audited.periode', $year);
        }        
        return $query;
    }
}
