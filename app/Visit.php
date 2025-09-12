<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'staff_id',
        'created_at',
        'updated_at',
        'customer_id',
        'status_wm',
        'visit_category_id',
        'dapertement_id',
        'description',
        'group_id',
        'lng',
        'lat',
        'absence_request_id'
    ];

    public function visitImages()
    {
        return $this->hasMany(VisitImage::class);
    }
    public function  visitCategory()
    {
        return $this->belongsTo(VisitCategory::class, 'visit_category_id');
    }
    public function scopeFilterDate($query, $from, $to)
    {
        if ($from != '' && $to != '') {
            //$query->whereBetween('created_at', [$from, $to]);
            $query->whereRaw("DATE(created_at) >= '".$from."' AND DATE(created_at) <= '".$to."'");
        }
        return $query;
    }
    public function scopeFilterType($query, $type)
    {
        if ($type != '') {
            $query->where('status_wm', '!=', null);
        } else {
            return $query;
        }
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'nomorrekening');
    }
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'id');
    }
    public function scopeFilterCustomer($query, $nomorrekening)
    {
        if ($nomorrekening != '') {
            $query->where('nomorrekening', $nomorrekening);
        }
        return $query;
    }
    public function scopeFilterArea($query, $area)
    {
        if ($area != '') {
            $query->where('idareal', $area);
        }
        return $query;
    }
}
