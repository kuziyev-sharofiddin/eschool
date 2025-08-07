<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DateFormatTrait;

class SchoolInquiry extends Model
{
    use HasFactory, DateFormatTrait;

    protected $fillable = [
        'school_address',  
        'school_phone', 
        'school_name',        
        'school_email',
        'school_tagline',      
        'date',   
        'status'    
    ];

    public function extra_school_details()
    {
        return $this->hasMany(ExtraSchoolData::class, 'school_inquiry_id', 'id'); 
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
