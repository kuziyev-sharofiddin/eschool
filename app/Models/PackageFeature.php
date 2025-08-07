<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\DateFormatTrait;

class PackageFeature extends Model {
    use HasFactory, DateFormatTrait;

    protected $fillable = ['package_id', 'feature_id'];

    /**
     * Get the feature that owns the PackageFeature
     *
     * @return BelongsTo
     */
    public function feature() {
        return $this->belongsTo(Feature::class);
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
