<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class StaffApi extends Authenticatable
{
    protected $table = 'staffs';
    protected $fillable = [
        'code',
        'name',
        'phone',
        'dapertement_id',
        'subdapertement_id'
    ];

    public function dapertement()
    {
        return $this->belongsTo('App\Dapertement')->select('id', 'name');
    }

    public function subdapertement()
    {
        return $this->belongsTo('App\Subdapertement')->select('id', 'name');
    }

    public function action()
    {
        return $this->belongsToMany(Action::class, 'action_staff', 'action_id', 'staff_id')
            ->withPivot([
                'status'
            ]);
    }

    public function scopeFilterDapertement($query, $dapertement)
    {
        if ($dapertement != '') {
            $query->where('dapertement_id', $dapertement);
        }
        return $query;
    }
    public function scopeWhereMaps($query, $fld, $comp, $operator = '=')
    {
        if ($fld != 'id') {
            $fld_db = $this->fldMaps($fld);
        } else {
            $fld_db = 'nomorrekening';
        }
        $query->where($fld_db, $operator, $comp);
        return $query;
    }
    private function fldMaps($fld)
    {
        return array_search($fld, $this->maps);
    }
}
