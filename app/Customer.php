<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{

    protected $connection = 'mysql2';

    protected $table = 'tblpelanggan';

    protected $primaryKey = 'nomorrekening';

    public $timestamps = false;

    protected $appends = ['id', 'code', 'name', 'phone', 'address', 'email', 'email_verified_at', 'password', 'type', 'remember_token', 'gender'];

    protected $fillable = [
        'nomorrekening',
        'namapelanggan',
        'telp',
        'alamat',
        '_email',
        '_password',
        '_gender',
        '_type',
        '_synced'
    ];

    protected $maps = array(
        'nomorrekening' => 'code',
        'namapelanggan' => 'name',
        'telp' => 'phone',
        'alamat' => 'address',
        '_email' => 'mail',
        '_password' => 'password',
        '_gender' => 'gender',
        '_type' => 'type'
    );

    public function getIdAttribute()
    {
        return $this->attributes['nomorrekening'];
    }

    public function getCodeAttribute()
    {
        return $this->attributes['nomorrekening'];
    }

    public function getNameAttribute()
    {
        return $this->attributes['namapelanggan'];
    }

    public function getPhoneAttribute()
    {
        return $this->attributes['telp'];
    }

    public function getAddressAttribute()
    {
        return $this->attributes['alamat'];
    }

    public function getEmailAttribute()
    {
        return $this->attributes['_email'];
    }

    public function getEmailVerifiedAtAttribute()
    {
        return $this->attributes['_email_verified_at'];
    }

    public function getPasswordAttribute()
    {
        return $this->attributes['_password'];
    }

    public function getTypeAttribute()
    {
        return $this->attributes['_type'];
    }

    public function getRememberTokenAttribute()
    {
        return $this->attributes['_remember_token'];
    }

    public function getGenderAttribute()
    {
        return $this->attributes['_gender'];
    }

    public function setCodeAttribute($value)
    {
        $this->attributes['nomorrekening'] = $value;
    }

    public function setNameAttribute($value)
    {
        $this->attributes['namapelanggan'] = $value;
    }

    public function setPhoneAttribute($value)
    {
        $this->attributes['telp'] = $value;
    }

    public function setAddressAttribute($value)
    {
        $this->attributes['alamat'] = $value;
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['_email'] = $value;
    }

    public function setEmailVerifiedAtAttribute($value)
    {
        $this->attributes['_email_verified_at'] = $value;
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['_password'] = $value;
    }

    public function setTypeAttribute($value)
    {
        $this->attributes['_type'] = $value;
    }

    public function setRememberTokenAttribute($value)
    {
        $this->attributes['_remember_token'] = $value;
    }

    public function setGenderAttribute($value)
    {
        $this->attributes['_gender'] = $value;
    }

    public function scopeFilterMaps($query, $req_obj)
    {
        if (!empty($req_obj->code)) {
            $query->where('nomorrekening', $req_obj->code);
        }
        if (!empty($req_obj->type)) {
            $query->where('_type', $req_obj->type);
        }
        return $query;
    }

    public function scopeFilterStatusNew($query, $status)
    {
        if ($status != '') {
            if ($status == 2) {
                $query->having('jumlahtunggakan', '>=', 2)->having('jumlahtunggakan', '<', 4);
            } else if ($status == 4) {
                $query->having('jumlahtunggakan', '>=', 4);
            } else {
                $query->having('jumlahtunggakan', '=', $status);
            }
            return $query;
        } else {
        }
    }

    public function scopeFilterStatus($query, $status)
    {
        if ($status != '') {
            $query->having('jumlahtunggakan', '=', $status);
            return $query;
        } else {
        }
    }

    public function scopeFilterNomorrekening($query, $nomorrekening)
    {
        if ($nomorrekening != '') {
            $query->having('nomorrekening', '=', $nomorrekening);
            return $query;
        } else {
        }
    }

    // new
    public function scopeFilterCustomerId($query, $customer)
    {
        if ($customer != '') {
            $query->having('tblpelanggan.nomorrekening', '=', $customer);
            return $query;
        } else {
        }
    }

    // end new

    public function scopeFilterWilayah($query, $area)
    {
        if ($area != '') {
            $query->where('tblpelanggan.idareal', '=', $area);
            return $query;
        } else {
        }
    }
    public function scopeFilterSearch($query, $status)
    {
        if ($status != '') {
            $query->having('jumlahtunggakan', '=', $status);
            return $query;
        } else {
        }
    }

    public function scopeFilterNumber($query, $phone)
    {
        if ($phone != '') {
            $query->where('telp', 'like', $phone . '%');
            return $query;
        } else {
            $query->where('telp', '=', '');
            return $query;
        }
    }

    public function scopeOrderMaps($query, $fld, $sort_type)
    {
        if ($fld != 'id') {
            $fld_db = $this->fldMaps($fld);
        } else {
            $fld_db = 'nomorrekening';
        }
        $query->orderBy($fld_db, $sort_type);
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

    public function scopeOrderRawMaps($query, $fld, $sort_type)
    {
        if ($fld != 'id') {
            $fld_db = $this->fldMaps($fld);
        } else {
            $fld_db = 'nomorrekening';
        }
        $query->orderByRaw('CAST(' . $fld_db . ' AS DECIMAL) ' . $sort_type);
        return $query;
    }

    public function jenispelanggan()
    {
        return $this->belongsTo(Jenispelanggan::class, 'idgol', 'id');
    }

    public function scopeFilterKeyword($query, $keyword)
    {
        if ($keyword != '') {
            $query->where('tblpelanggan.nomorrekening', 'LIKE',  '%' . $keyword . '%')->orWhere('tblpelanggan.namapelanggan', 'LIKE',  '%' . $keyword . '%');
            return $query;
        } else {
        }
    }
}
