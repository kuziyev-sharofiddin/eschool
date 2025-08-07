<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Traits\DateFormatTrait;

class UserStatusForNextCycle extends Model
{
    use HasFactory, DateFormatTrait;
    protected $fillable = ['user_id','status','school_id'];

    public function scopeOwner() {
        if (Auth::user()) {
            return $this->where('school_id', Auth::user()->school_id);
        }
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
