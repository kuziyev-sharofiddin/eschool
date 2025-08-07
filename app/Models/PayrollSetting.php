<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\SessionYearsTracking;
use App\Traits\DateFormatTrait;
class PayrollSetting extends Model
{
    use HasFactory, SoftDeletes, DateFormatTrait;

    protected $fillable = [
        'id',
        'name',
        'amount',
        'percentage',
        'type',
        'school_id'
    ];

    public function scopeOwner()
    {
        if(Auth::user()) {
            return $this->where('school_id', Auth::user()->school_id);
        }
    }

   
    public function staffSalary()
    {
        return $this->hasMany(StaffSalary::class , 'payroll_setting_id', 'id');
    }

    public function session_years_trackings()
    {
        return $this->hasMany(SessionYearsTracking::class, 'modal_id', 'id')->where('modal_type', 'App\Models\PayrollSetting');
    }

    public function getCreatedAtAttribute()
    {
        return $this->formatDateValue($this->getRawOriginal('created_at'));
    }

    public function getUpdatedAtAttribute()
    {
        return $this->formatDateValue($this->getRawOriginal('updated_at'));
    }
    
    

}
