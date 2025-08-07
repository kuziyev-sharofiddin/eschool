<?php

namespace App\Repositories\Diary;

use App\Models\Diary;
use App\Repositories\Base\BaseRepository;

class DiaryRepository extends BaseRepository implements DiaryInterface {

    public function __construct(Diary $model) {
        parent::__construct($model);
    }
}
