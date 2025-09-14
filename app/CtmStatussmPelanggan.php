<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CtmStatussmPelanggan extends Model
{
    protected $connection = 'mysql2';

    protected $table = 'tblstatussmpelanggan';

    public function scopeFilterMonth($query, $month)
    {
        if ($month != '') {
            $query->where('tblstatussmpelanggan.bulan', $month);
        }
        return $query;
    }

    public function scopeFilterYear($query, $year)
    {
        if ($year != '') {
            $query->where('tblstatussmpelanggan.tahun', $year);
        }
        return $query;
    }

    public function scopeFilterStatusWM($query, $ststussm)
    {
        if ($ststussm != '') {
            $query->where('tblstatussmpelanggan.statussm', $ststussm);
        }
        return $query;
    }

    public function scopeFilterAreas($query, $idareal)
    {
        if ($idareal != '') {
            $query->where('tblpelanggan.idareal', $idareal);
        }
        return $query;
    }

    public function scopeFilterNomorrekening($query, $nomorrekening)
    {
        if ($nomorrekening != '') {
            $query->where('tblpelanggan.nomorrekening', 'like', '%' . $nomorrekening . '%');
        }
        return $query;
    }
}
