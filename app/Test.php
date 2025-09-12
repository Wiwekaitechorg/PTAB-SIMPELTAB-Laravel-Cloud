<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    protected $fillable = [
        'code',
        'value',
        'name',
        'student_id',
        'subject_id'
    ];
    function subject(){
        return $this->belongsTo('App\subject');
    }
    function student(){
        return $this->belongsTo('App\student');
    }
}
