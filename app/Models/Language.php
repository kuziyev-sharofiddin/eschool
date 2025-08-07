<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DateFormatTrait;

class Language extends Model {
    use HasFactory;
    use DateFormatTrait;

    protected $fillable = [
        'id',
        'name',
        'code',
        'file',
        'status',
        'is_rtl'
    ];
    protected $connection = 'mysql';

    public function getCreatedAtAttribute()
    {
        return $this->formatDateValue($this->getRawOriginal('created_at'));
    }

    public function getUpdatedAtAttribute()
    {
        return $this->formatDateValue($this->getRawOriginal('updated_at'));
    }
    
}
