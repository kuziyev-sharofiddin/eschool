<?php

namespace App\Rules;

use App\Models\ClassSubject;
use App\Models\Lesson;
use App\Models\SubjectTeacher;
use Illuminate\Contracts\Validation\Rule;

class uniqueLessonInClass implements Rule {


    protected $class_section_id;
    protected $class_subject_id;
    protected $lesson_id;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($class_section_id, $class_subject_id, $lesson_id = NULL) {
        $this->class_section_id = $class_section_id;
        $this->class_subject_id = $class_subject_id;
        $this->lesson_id = $lesson_id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */

    public function passes($attribute, $value) {

        $section_ids = is_array($this->class_section_id) ? $this->class_section_id : [$this->class_section_id];

        $classSubject = SubjectTeacher::whereIn('class_section_id', $section_ids)->where('subject_id', $this->class_subject_id)->pluck('class_subject_id')->toArray();
        if ($this->lesson_id == NULL) {
            $count = Lesson::where('name', $value)->whereHas('lesson_commons', function ($query) use ($section_ids, $classSubject) {
                $query->whereIn('class_section_id', $section_ids)->whereIn('class_subject_id', $classSubject);
            })->count();
            return $count == 0;
        }

        $count = Lesson::where('name', $value)->whereHas('lesson_commons', function ($query) use ($section_ids, $classSubject) {
            $query->whereIn('class_section_id', $section_ids)->whereIn('class_subject_id', $classSubject);
        })->whereNot('id', $this->lesson_id)->count();
        return $count == 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message() {
        return trans('lesson_already_exists');
    }
}
