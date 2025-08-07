<?php

namespace App\Repositories\SessionYearsTrackings;

use App\Models\SessionYearsTracking;
use App\Repositories\Saas\SaaSRepository;

class SessionYearsTrackingsRepository extends SaaSRepository implements SessionYearsTrackingsInterface {
    public function __construct(SessionYearsTracking $model) {
        parent::__construct($model, 'session_years_trackings');
    }
}
