<?php

namespace App\Repositories\ContactInquiry;

use App\Models\ContactInquiry;
use App\Repositories\Base\BaseRepository;

class ContactInquiryRepository extends BaseRepository implements ContactInquiryInterface {
    public function __construct(ContactInquiry $model) {
        parent::__construct($model);
    }
}
