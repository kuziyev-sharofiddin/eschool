<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DateFormatTrait;

class DiaryStudent extends Model
{
    use HasFactory, SoftDeletes, DateFormatTrait;

    protected $guarded = [];

    public function diary()
    {
        return $this->belongsTo(Diary::class)->withTrashed();
    }

    public function student() {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function class_section() {
        return $this->belongsTo(ClassSection::class)->withTrashed();
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
