<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DateFormatTrait;

class StaffPayroll extends Model
{
    use HasFactory, DateFormatTrait;
    protected $fillable = ['expense_id', 'payroll_setting_id', 'amount', 'percentage', 'school_id'];

    public function scopeOwner()
    {
        if (Auth::user()) {
            return $this->where('school_id', Auth::user()->school_id);
        }
    }

    /**
     * Get the payroll_setting that owns the StaffPayroll
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payroll_setting()
    {
        return $this->belongsTo(PayrollSetting::class)->withTrashed();
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
