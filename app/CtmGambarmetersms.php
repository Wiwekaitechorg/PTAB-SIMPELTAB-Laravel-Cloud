<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CtmGambarmetersms extends Model
{
    protected $connection = 'mysql2';

    protected $table = 'gambarmetersms';

    protected $primaryKey = 'idurutan';

    public $timestamps = false;

    protected $fillable = [
        'nomorpengirim',
        'bulanrekening',
        'tahunrekening',
        'tanggal',
        'nomorrekening',
        'pencatatanmeter',
        'idgambar',
        '_synced'
    ];

    public function scopeFilterAreal($query, $areal)
    {
        if($areal !=''){
            $query->where('tblwilayah.group_unit', $areal);
        }   
        return $query;
    }
    
    public function scopeFilterMonth($query, $month)
    {
        if($month !=''){
            $query->where('gambarmetersms.bulanrekening', $month);
        }        
        return $query;
    }

    public function scopeFilterYear($query, $year)
    {
        if($year !=''){
            $query->where('gambarmetersms.tahunrekening', $year);
        }        
        return $query;
    }

    public function scopeFilterOperator($query, $operator)
    {
        if($operator !=''){
            $query->where('tblopp.operator', $operator);
        }   
        return $query;
    }

    public function scopeFilterSbg($query, $sbg)
    {
        if($sbg !=''){
            $query->where('gambarmetersms.nomorrekening', $sbg);
        }                
        return $query;
    }
}
