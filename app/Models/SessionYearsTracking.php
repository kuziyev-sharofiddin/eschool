<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SessionYearsTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'modal_type',
        'modal_id',
        'user_id',
        'session_year_id',
        'semester_id',
        'school_id',
        'created_at',
        'updated_at',
    ];

    public function modal() {
        return $this->morphTo();
    }

    public function scopeOwner($query) {
        if(Auth::user()) {
            if (Auth::user()->hasRole('Super Admin')) {
                return $query;
            }
    
            if (Auth::user()->hasRole('School Admin') || Auth::user()->hasRole('Teacher')) {
                return $query->where('school_id', Auth::user()->school_id);
            }
    
            if (Auth::user()->hasRole('Student')) {
                return $query->where('school_id', Auth::user()->school_id);
            }
        }

        return $query;
    }

    /**
     * Filter by session year
     */
    public function scopeBySessionYear($query, $sessionYearId)
    {
        return $query->where('session_year_id', $sessionYearId);
    }

    /**
     * Filter by modal type
     */
    public function scopeByModalType($query, $modalType)
    {
        return $query->where('modal_type', $modalType);
    }

    /**
     * Filter by semester
     */
    public function scopeBySemester($query, $semesterId)
    {
        return $query->where('semester_id', $semesterId);
    }

    /**
     * Filter by school
     */
    public function scopeBySchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Filter by modal ID
     */
    public function scopeByModalId($query, $modalId)
    {
        return $query->where('modal_id', $modalId);
    }

    /**
     * Filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Filter by default session year
     */
    public function scopeByDefaultSessionYear($query)
    {
        $cache = app(CachingService::class);
        return $query->where('session_year_id', $cache->getDefaultSessionYear()->id);
    }

    /**
     * Filter by multiple modal types
     */
    public function scopeByModalTypes($query, array $modalTypes)
    {
        return $query->whereIn('modal_type', $modalTypes);
    }

    /**
     * Filter by multiple session years
     */
    public function scopeBySessionYears($query, array $sessionYearIds)
    {
        return $query->whereIn('session_year_id', $sessionYearIds);
    }

    /**
     * Filter by multiple schools
     */
    public function scopeBySchools($query, array $schoolIds)
    {
        return $query->whereIn('school_id', $schoolIds);
    }

    /**
     * Filter by multiple users
     */
    public function scopeByUsers($query, array $userIds)
    {
        return $query->whereIn('user_id', $userIds);
    }

    /**
     * Filter by multiple semesters
     */
    public function scopeBySemesters($query, array $semesterIds)
    {
        return $query->whereIn('semester_id', $semesterIds);
    }
}
