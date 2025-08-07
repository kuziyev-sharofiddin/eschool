<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Traits\DateFormatTrait;

class ExtraSchoolData extends Model
{
    use HasFactory, DateFormatTrait;

    protected $table = 'extra_school_datas';

    protected $fillable = [
        'school_inquiry_id',
        'school_id',
        'form_field_id',
        'data',
    ];

    public function form_field() {
        return $this->belongsTo(FormField::class, 'form_field_id')->withTrashed();
    }


    public function getFileUrlAttribute() {
        if ($this->relationLoaded('form_field')) {
            if ($this->form_field->type == "file" && !empty($this->data)) {
                return url(Storage::url($this->data));
            }

            return null;
        }
        return null;

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
