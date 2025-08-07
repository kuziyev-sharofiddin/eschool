<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Traits\DateFormatTrait;

class FeesInstallment extends Model
{
    use HasFactory;
    use DateFormatTrait;
    protected $fillable = [
        'name',
        'due_date',
        'due_charges',
        'session_year_id',
        'school_id',
        'created_at',
        'updated_at',
        'installment_amount'
    ];

    public function session_year(){
        return $this->belongsTo(SessionYear::class, 'session_year_id')->withTrashed();
    }

    public function scopeOwner($query)
    {
        if (Auth::user()) {
            if (Auth::user()->hasRole('Super Admin')) {
                return $query;
            }
    
            if (Auth::user()->hasRole('School Admin') || Auth::user()->hasRole('Teacher')) {
                return $query->where('school_id', Auth::user()->school_id);
            }
    
            if (Auth::user()->hasRole('Student')) {
                return $query->where('school_id', Auth::user()->school_id);
            }
        }

        return $query;
    }

    public function compulsory_fees()
    {
        return $this->hasMany(CompulsoryFee::class, 'installment_id')->withTrashed();
    }

    protected function setDueDateAttribute($value) {
        $this->attributes['due_date'] = date('Y-m-d', strtotime($value));
    }

    // public function getDueDateAttribute($value) {
    //     return $this->formatDateValue($value);
    // }

    public function getCreatedAtAttribute()
    {
        return $this->formatDateValue($this->getRawOriginal('created_at'));
    }

    public function getUpdatedAtAttribute()
    {
        return $this->formatDateValue($this->getRawOriginal('updated_at'));
    }

    public function getformatDueDateAttribute()
    {
        return $this->formatDateOnly($this->due_date);
    }
}
