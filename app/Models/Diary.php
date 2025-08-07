<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DateFormatTrait;

class Diary extends Model
{
    use HasFactory, SoftDeletes, DateFormatTrait;

    protected $guarded = [];

    public function diary_category()
    {
        return $this->belongsTo(DiaryCategory::class);
    }

    public function diary_students()
    {
        return $this->hasMany(DiaryStudent::class)->withTrashed();
    }

//     public function diary_students()
//    {
//        return $this->hasMany(DiaryStudent::class);
//    }

    // public function students()
    // {
    //     return $this->belongsTo(Students::class)->withTrashed();
    // }

    public function session_year()
    {
        return $this->belongsTo(SessionYear::class)->withTrashed();
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
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
