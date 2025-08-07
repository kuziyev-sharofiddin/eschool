<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Traits\DateFormatTrait;

class LeaveDetail extends Model
{
    use HasFactory, DateFormatTrait;
    protected $fillable = ['leave_id','date','status'];
    protected $hidden = ['created_at','updated_at'];

    /**
     * Get the leave that owns the LeaveDetail
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function leave()
    {
        return $this->belongsTo(Leave::class);
    }

    public function scopeOwner()
    {
        if (Auth::user()) {
            return $this->where('school_id', Auth::user()->school_id);
        }
    }

    public function getLeaveDateAttribute()
    {
        return date('d - M',strtotime($this->date));
    }

    public function getCreatedAtAttribute()
    {
        return $this->formatDateValue($this->getRawOriginal('created_at'));
    }

    public function getUpdatedAtAttribute()
    {
        return $this->formatDateValue($this->getRawOriginal('updated_at'));
    }

    public function getFromDateAttribute($value)
    {
        return $this->formatDateValue($value);
    }

    public function getToDateAttribute($value)
    {
        return $this->formatDateValue($value);
    }
}
