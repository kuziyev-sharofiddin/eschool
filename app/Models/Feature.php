<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DateFormatTrait;
class Feature extends Model
{
    use HasFactory;
    use DateFormatTrait;
    protected $fillable = ['name','is_default','status','required_vps'];
    protected $connection = 'mysql';

    protected $appends = ['short_name'];

    public function getShortNameAttribute()
    {
        return trim(str_replace('Management',"", $this->name));
    }

    /**
     * Get all of the addon_subscription for the Feature
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addon_subscription()
    {
        return $this->hasMany(AddonSubscription::class)->withTrashed();
    }

    public function scopeActiveFeatures()
    {
        return $this->where('status' ,1);
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
