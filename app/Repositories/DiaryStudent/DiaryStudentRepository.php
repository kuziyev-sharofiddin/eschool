<?php

namespace App\Repositories\DiaryStudent;

use App\Models\DiaryStudent;
use App\Repositories\Base\BaseRepository;

class DiaryStudentRepository extends BaseRepository implements DiaryStudentInterface {

    public function __construct(DiaryStudent $model) {
        parent::__construct($model);
    }
}
