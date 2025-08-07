<?php

namespace App\Services;

use App\Models\SessionYearsTracking;
use App\Repositories\SessionYearsTrackings\SessionYearsTrackingsInterface;

class SessionYearsTrackingsService {
    
    public function __construct() {
       
    }

    /**
     * Store session years tracking
     *
     * @param string $modal_type
     * @param int $modal_id
     * @param int $user_id
     * @param int $session_year_id
     * @param int $school_id
     * @param int $semester_id
     */
    public static function storeSessionYearsTracking($modal_type, $modal_id, $user_id, $session_year_id, $school_id, $semester_id = null) {
        $sessionYearsTrackingModel = new SessionYearsTracking();
        $sessionYearsTrackingModel->modal_type = $modal_type;
        $sessionYearsTrackingModel->modal_id = $modal_id;
        $sessionYearsTrackingModel->user_id = $user_id;
        $sessionYearsTrackingModel->session_year_id = $session_year_id;
        $sessionYearsTrackingModel->school_id = $school_id;
        $sessionYearsTrackingModel->semester_id = $semester_id;
        $sessionYearsTrackingModel->save();
    }

    /**
     * Update session years tracking
     *
     * @param string $modal_type
     * @param int $modal_id
     * @param int $user_id
     * @param int $session_year_id
     * @param int $school_id
     * @param int $semester_id
     */

    public static function updateSessionYearsTracking($modal_type, $modal_id, $user_id, $session_year_id, $school_id, $semester_id = null) {
        $sessionYearsTrackingModel = SessionYearsTracking::where('modal_type', $modal_type)->where('modal_id', $modal_id)->where('user_id', $user_id)->where('session_year_id', $session_year_id)->where('school_id', $school_id)->where('semester_id', $semester_id)->update([
            'session_year_id' => $session_year_id,
            'school_id' => $school_id,
            'semester_id' => $semester_id
        ]);
    }

    /**
     * Delete session years tracking
     *
     * @param string $modal_type
     * @param int $modal_id
     * @param int $user_id
     * @param int $session_year_id
     * @param int $school_id
     * @param int $semester_id
     */
    public static function deleteSessionYearsTracking($modal_type, $modal_id, $user_id, $session_year_id, $school_id, $semester_id = null) {
        $sessionYearsTrackingModel = SessionYearsTracking::where('modal_type', $modal_type)->where('modal_id', $modal_id)->where('user_id', $user_id)->where('session_year_id', $session_year_id)->where('school_id', $school_id)->where('semester_id', $semester_id)->delete();
    }

    /**
     * Upsert session years tracking
     *
     * @param string $modal_type
     * @param int $modal_id
     * @param int $user_id
     * @param int $session_year_id
     * @param int $school_id
     * @param int $semester_id
     */

    public static function upsertSessionYearsTracking($modal_type, $modal_id, $user_id, $session_year_id, $school_id, $semester_id = null) {
        $sessionYearsTrackingModel = SessionYearsTracking::where('modal_type', $modal_type)->where('modal_id', $modal_id)->where('user_id', $user_id)->where('session_year_id', $session_year_id)->where('school_id', $school_id)->where('semester_id', $semester_id)->update([
            'session_year_id' => $session_year_id,
            'school_id' => $school_id,
            'semester_id' => $semester_id
        ]);
    }

}