<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    protected $connection = 'mysql2';

    protected $table = 'tblpelanggan';

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
    ];

    protected $maps = array('nomorrekening' => 'code',
        'namapelanggan' => 'name',
        'telp' => 'phone',
        'alamat' => 'address',
        '_email' => 'mail',
        '_password' => 'password',
        '_gender' => 'gender',
        '_type' => 'type');

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

    public function scopeFilter($query, $req_obj)
    {
        if (!empty($req_obj->code)) {
            $query->where('nomorrekening', $req_obj->code);
        }
        if (!empty($req_obj->type)) {
            $query->where('_type', $req_obj->type);
        }
        return $query;
    }

    public function scopeOrder($query, $fld, $sort_type)
    {
        if ($fld != 'id') {
            $fld_db = $this->fldMaps($fld);
        } else {
            $fld_db = 'nomorrekening';
        }
        $query->orderBy($fld_db, $sort_type);
        return $query;
    }

    private function fldMaps($fld)
    {
        return array_search($fld, $this->maps);
    }
}
