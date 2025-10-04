<?php
namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\WorkTypeDayFilter;

class WorkTypeDays extends Model
{
    use HasFactory;
    use WorkTypeDayFilter;

    protected $fillable = [
        'day_id',
        'time',
        'duration',
        'absence_category_id',
        'duration_exp',
        'work_type_id',
        'work_type_id',
    ];

    public function absenceCategory()
    {
        return $this->belongsTo(Absence_categories::class, 'absence_category_id');
    }

    public function workType()
    {
        return $this->belongsTo(WorkTypes::class, 'work_type_id');
    }
}
