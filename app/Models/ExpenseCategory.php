<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Traits\DateFormatTrait;

class ExpenseCategory extends Model
{
    use HasFactory, SoftDeletes, DateFormatTrait;
    protected $fillable = ['name', 'description', 'school_id'];

    public function scopeOwner()
    {
        if (Auth::user() && Auth::user()->school_id) {
            return $this->where('school_id',Auth::user()->school_id);
        }
        return $this;
    }

    /**
     * Get all of the expense for the ExpenseCategory
     *
     * @return HasMany
     */
    public function expense()
    {
        return $this->hasMany(Expense::class, 'category_id');
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
