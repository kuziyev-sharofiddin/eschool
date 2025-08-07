<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DateFormatTrait;

class SubscriptionBillPayment extends Model
{
    use HasFactory, DateFormatTrait;
    protected $fillable = ['subscription_bill_id','date','amount','payment_type','cheque_number','school_id'];

    public function scopeOwner()
    {
        if (Auth::user()) {
            if (Auth::user()->school_id) {
                return $this->where('school_id',Auth::user()->school_id);
            }
        }
        return $this;
    }

    /**
     * Get the school that owns the SubscriptionBillPayment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school()
    {
        return $this->belongsTo(School::class);
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
        return $this->formatDateValue($value);
    }
}
