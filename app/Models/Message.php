<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DateFormatTrait;

class Message extends Model
{
    use HasFactory, DateFormatTrait;
    protected $fillable = ['chat_id','sender_id','message','read_at'];



    /**
     * Get all of the attachment for the Message
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attachment()
    {
        return $this->hasMany(Attachment::class);
    }

    /**
     * Get the chat that owns the Message
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function getCreatedAtAttribute()
    {
        return $this->formatDateValue($this->getRawOriginal('created_at'));
    }
    
    public function getUpdatedAtAttribute()
    {
        return $this->formatDateValue($this->getRawOriginal('updated_at'));
    }
    
    public function getReadAtAttribute($value)
    {
        if ($value) {
            return $this->formatDateValue($value);
        }
        return null;
    }
}
