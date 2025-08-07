<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Traits\DateFormatTrait;
use App\Services\CachingService;

class Attendance extends Model
{
    use HasFactory, DateFormatTrait;

    protected $hidden = ["remark"];
    protected $fillable = [
        'class_section_id',
        'student_id',
        'session_year_id',
        'type',
        'date',
        'remark',
        'school_id'
    ];

    protected $appends = ['roll_number', 'get_date_original', 'date_format'];


    use DateFormatTrait;

    public function user()
    {
        return $this->belongsTo(User::class, 'student_id')->withTrashed();
    }

    public function scopeOwner($query)
    {
        if(Auth::user()) {
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

    public function getRollNumberAttribute()
    {   
        if ($this->user) {
            if ($this->user->student) {
                return $this->user->student->roll_number;        
            }
        }
        return '';
    }

    public function getCreatedAtAttribute()
    {
        return $this->formatDateValue($this->getRawOriginal('created_at'));
    }
    
    public function getUpdatedAtAttribute()
    {
        return $this->formatDateValue($this->getRawOriginal('updated_at'));
    }

    public function getDateAttribute($value)
    {
        return $this->formatDateOnly($value);
    }

    public function getDateFormatAttribute()
    {
        $cache = app(CachingService::class);
        return $cache->getSchoolSettings('date_format');
    }

    public function getGetDateOriginalAttribute()
    {
        return $this->getRawOriginal('date');
    }
}
