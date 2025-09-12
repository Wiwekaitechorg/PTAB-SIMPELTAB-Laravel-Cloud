<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CtmPembayaran extends Model
{
    protected $connection = 'mysql2';

    protected $table = 'tblpembayaran';

    protected $primaryKey = null;

    public $timestamps = false;

    protected $fillable = [
        'nomorrekening',
        'bulanrekening',
        'tahunrekening',
        'idgol',
        'idareal',
        'tarif01',
        'tarif02',
        'tarif03',
        'tarif04',
        'tarif05',
        'tarif06',
        'danameter',
        'adm',
        'beban',
        'denda',
        'batas1',
        'batas2',
        'batas3',
        'batas4',
        'batas5',
        'pajak',
        'pemakaianair',
        'pemakaianair01',
        'pemakaianair02',
        'pemakaianair03',
        'pemakaianair04',
        'pemakaianair05',
        'pemakaianair06',
        'bulanini',
        'bulanlalu',
        'wajibdibayar',
        'idbiro',
        'tglbayarterakhir',
        'operator',
        'operator1',
        '_synced'
    ];
}
