<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lock extends Model
{
    protected $table = 'locks';
    protected $fillable = [
        'code',
        'customer_id',
        'status',
        'description',
        'subdapertement_id',
        'start',
        'end'
    ];

    public function customer() { 
        return $this->belongsTo(Customer::class, 'customer_id', 'nomorrekening'); 
    }
    public function subdapertement() { 
        return $this->belongsTo(Subdapertement::class, 'subdapertement_id', 'id'); 
    }
    public function lockaction() { 
        return $this->belongsTo(LockAction::class, 'id', 'lock_id'); 
    }
    

    public function scopeFilterStatus($query, $status)
    {
        if($status !=''){
        $query->where('locks.status', $status);        
        }
        return $query;
    }
    
    public function scopeFilterKeyword($query, $customer_id)
    {
        if($customer_id !=''){
        $query->where('locks.customer_id', $customer_id);        
        }
        return $query;
    }

    public function scopeFilterDepartment($query, $department)
    {
        if($department !='' && $department>0){
        $query->where('dapertements.id', $department);        
        }
        return $query;
    }

    
    public function scopeFilterSubDepartment($query, $subdepartment)
    {
        if($subdepartment !='' && $subdepartment>0){
        $query->where('subdapertement_id', $subdepartment);        
        }
        return $query;
    }

    public function staff()
    {
        return $this->belongsToMany(Staff::class, 'lock_staff', 'lock_id', 'staff_id')->with('dapertement');
    }

    public function dapertement() { 
        return $this->belongsTo(Dapertement::class, 'dapertement_id', 'id'); 
    }

   


}
