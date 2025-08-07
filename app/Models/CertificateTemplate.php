<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Traits\DateFormatTrait;

class CertificateTemplate extends Model
{
    use HasFactory, DateFormatTrait;
    protected $fillable = ['name','certificate_type_id','page_layout','height','width','user_image_shape','image_size','background_image','signature','description','fields','style','type','school_id'];

    protected static function boot() {
        parent::boot();
        static::deleting(static function ($certificateTemplate) { // before delete() method call this
            if ($certificateTemplate->background_image) {
                if (Storage::disk('public')->exists($certificateTemplate->getRawOriginal('background_image'))) {
                    Storage::disk('public')->delete($certificateTemplate->getRawOriginal('background_image'));
                }
            }
        });
    }

    public function scopeOwner()
    {
        if (Auth::user()) {
            if (Auth::user()->school_id) {
                return $this->where('school_id',Auth::user()->school_id);
            }
        }
        return $this;
    }

    public function getBackgroundImageAttribute($value)
    {
        if ($value) {
            return url(Storage::url($value));    
        }
        return '';
        
    }

    public function getFieldsAttribute($value)
    {
        return explode(",",$value);
    }

    public function session_years_trackings()
    {
        return $this->hasMany(SessionYearsTracking::class, 'modal_id', 'id')->where('modal_type', 'App\Models\CertificateTemplate');
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
