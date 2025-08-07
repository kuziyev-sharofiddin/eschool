<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Traits\DateFormatTrait;

class Attachment extends Model
{
    use HasFactory, DateFormatTrait;
    protected $fillable = ['message_id','file','file_type'];

    public function getFileAttribute($value) {
        if ($value) {
            return url(Storage::url($value));
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

}
