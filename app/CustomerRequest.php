<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerRequest extends Model
{   
    // use SoftDeletes;

    protected $table = 'customer_requests';
    protected $fillable = [
        'code',
        'phone',
        'address',
        'status',
        'img',
    ];

    public function customer() { 
        return $this->belongsTo(Customer::class, 'code', 'nomorrekening'); 
    }
}
