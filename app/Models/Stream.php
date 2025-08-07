<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Traits\DateFormatTrait;

class Stream extends Model {
    use HasFactory, SoftDeletes, DateFormatTrait;
    use softDeletes;

    protected $fillable = [
        'name',
        'school_id'
    ];

    public function scopeOwner($query) {
        if (Auth::user()) {
            if (Auth::user()->school_id) {
                if (Auth::user()->hasRole('School Admin')) {
                    return $query->where('school_id', Auth::user()->school_id);
                }
        
                if (Auth::user()->hasRole('Teacher')) {
                    return $query->where('school_id', Auth::user()->school_id);
                }
        
                if (Auth::user()->hasRole('Student')) {
                    return $query->where('school_id', Auth::user()->school_id);
                }
                return $query->where('school_id', Auth::user()->school_id);
            }

            if (!Auth::user()->school_id) {
                if (Auth::user()->hasRole('Super Admin')) {
                    return $query;
                }
                return $query;
            }
        }

        return $query;
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
