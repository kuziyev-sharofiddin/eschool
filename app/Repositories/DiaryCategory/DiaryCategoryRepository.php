<?php

namespace App\Repositories\DiaryCategory;

use App\Models\DiaryCategory;
use App\Repositories\Base\BaseRepository;

class DiaryCategoryRepository extends BaseRepository implements DiaryCategoryInterface {

    public function __construct(DiaryCategory $model) {
        parent::__construct($model);
    }
}
