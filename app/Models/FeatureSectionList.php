<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Storage;
use App\Traits\DateFormatTrait;
class FeatureSectionList extends Model
    {
        use HasFactory, DateFormatTrait;
    protected $fillable = ['feature_section_id','feature','description','image'];


    public function getImageAttribute($value)
    {
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
