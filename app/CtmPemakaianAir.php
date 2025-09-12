<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CtmPemakaianAir extends Model
{
    protected $connection = 'mysql2';

    protected $table = 'tblpemakaianair';

    protected $primaryKey = ['nomorrekening', 'tahunrekening'];
    
    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'pemakaianair1',
        'pemakaianair2',
        'pemakaianair3',
        'pemakaianair4',
        'pemakaianair5',
        'pemakaianair6',
        'pemakaianair7',
        'pemakaianair8',
        'pemakaianair9',
        'pemakaianair10',
        'pemakaianair11',
        'pemakaianair12',
        'pencatatanmeter1',
        'pencatatanmeter2',
        'pencatatanmeter3',
        'pencatatanmeter4',
        'pencatatanmeter5',
        'pencatatanmeter6',
        'pencatatanmeter7',
        'pencatatanmeter8',
        'pencatatanmeter9',
        'pencatatanmeter10',
        'pencatatanmeter11',
        'pencatatanmeter12',
        'nomorrekening',
        'tahunrekening',
        'tglupdate',
        'operator',
        '_synced'
    ];
}
