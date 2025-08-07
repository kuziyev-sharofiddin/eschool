<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DateFormatTrait;

class FeesAdvance extends Model
{
    use HasFactory, DateFormatTrait;
    protected $table = 'fees_advance';
    protected $fillable = [
        'compulsory_fee_id',
        'student_id',
        'parent_id',
        'amount'
    ];

    /**
     * Get the user that owns the FeesAdvance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'student_id');
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
