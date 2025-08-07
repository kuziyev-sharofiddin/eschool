<?php

namespace App\Traits;

use App\Services\CachingService;
use Illuminate\Support\Carbon;

trait DateFormatTrait
{
    /**
     * Format a date/time value according to school settings
     *
     * @param mixed $value
     * @param string $key
     * @return string
     */
    protected function formatDateValue($value, $key = null)
    {
        if (!$value) {
            return $value;
        }

        try {
            // Convert to Carbon instance if it's not already
            if (!($value instanceof Carbon)) {
                $value = Carbon::parse($value, 'UTC'); // Treat DB time as UTC
            }

            $cache = app(CachingService::class);
            $schoolSettings = $cache->getSchoolSettings();
            $systemSettings = $cache->getSystemSettings();

            // Get the date and time format from school settings, fallback to system settings, then to default
            $date_format = $schoolSettings['date_format'] ?? $systemSettings['date_format'] ?? 'Y-m-d';
            $time_format = $schoolSettings['time_format'] ?? $systemSettings['time_format'] ?? 'H:i:s';
           
            return $value->format(trim($date_format . ' ' . $time_format));
        } catch (\Exception $e) {
            return $value;
        }
    }

    protected function formatTimeOnly($value)
    {
        if (!$value) {
            return $value;
        }

        try {
            // Convert to Carbon instance if it's not already
            if (!($value instanceof Carbon)) {
                $value = Carbon::parse($value, 'UTC'); // Treat DB time as UTC
            }

            $cache = app(CachingService::class);
            $schoolSettings = $cache->getSchoolSettings();
            $systemSettings = $cache->getSystemSettings();

            $time_format = $schoolSettings['time_format'] ?? $systemSettings['time_format'] ?? 'H:i:s';

            return $value->format($time_format);
        } catch (\Exception $e) {
            return $value;
        }
    }

    protected function formatDateOnly($value)
    {
        if (!$value) {
            return $value;
        }


        $value = Carbon::parse($value, 'UTC'); // Treat DB time as UTC

        $cache = app(CachingService::class);
        $schoolSettings = $cache->getSchoolSettings();
        $systemSettings = $cache->getSystemSettings();

        $date_format = $schoolSettings['date_format'] ?? $systemSettings['date_format'] ?? 'Y-m-d';

        return $value->format($date_format);
    }
    /**
     * Convert the model's attributes to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $attributes = parent::toArray();

        // Format all date fields first
        foreach ($this->getDates() as $key) {
            if (isset($attributes[$key])) {
                $attributes[$key] = $this->formatDateValue($attributes[$key], $key);
            }
        }

        // Handle date aliases
        if (isset($this->dateAliases)) {
            foreach ($this->dateAliases as $alias => $originalField) {
                if (isset($this->$originalField)) {
                    $attributes[$alias] = $this->formatDateValue($this->$originalField, $originalField);
                }
            }
        }

        // Handle time aliases
        if (isset($this->timeAliases)) {
            foreach ($this->timeAliases as $alias => $originalField) {
                if (isset($this->$originalField)) {
                    $attributes[$alias] = $this->formatDateValue($this->$originalField, 'time');
                }
            }
        }

        return $attributes;
    }

    /**
     * Get the attributes that should be treated as dates.
     *
     * @return array
     */
    public function getDates()
    {
        $dates = isset($this->dates) ? $this->dates : [];
        return array_unique(array_merge($dates, [
            'created_at',
            'updated_at',
            'deleted_at'
        ]));
    }
}
