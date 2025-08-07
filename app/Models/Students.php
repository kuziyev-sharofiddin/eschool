<?php

namespace App\Models;

use App\Repositories\StudentSubject\StudentSubjectInterface;
use App\Services\CachingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\SessionYearsTracking;
use App\Traits\DateFormatTrait;

class Students extends Model {
    use SoftDeletes;
    use HasFactory, DateFormatTrait;

    protected $fillable = [
        'user_id',
        'class_id',
        'class_section_id',
        'admission_no',
        'roll_number',
        'admission_date',
        'guardian_id',
        'school_id',
        'session_year_id',
        'application_type',
        'application_status',
        'join_session_year_id',
        'leave_session_year_id'
    ];
    protected $appends = ['first_name','last_name','full_name'];

    public function scopeOwner($query) {
        if (Auth::user()) {
            if (Auth::user()->hasRole('Super Admin')) {
                return $query;
            }

            if (Auth::user()->hasRole('Teacher')) {
                $classSectionID = ClassTeacher::where('teacher_id', Auth::user()->id)->pluck('class_section_id')->toArray();
                $subjectTeachers = SubjectTeacher::where('teacher_id', Auth::user()->id)->pluck('class_section_id')->toArray();
                $class_section_ids = array_merge($classSectionID, $subjectTeachers);
                return $query->whereIn('class_section_id', $class_section_ids);
            }

            if (Auth::user()->school_id || Auth::user()->hasRole('Student') || Auth::user()->hasRole('School Admin')) {
                $cache = app(CachingService::class);
                $query->with(['session_years_trackings' => function ($q) use ($cache) {
                    $q->where('modal_type', 'App\Models\Students')
                      ->where('session_year_id', $cache->getDefaultSessionYear()->id);
                }]);
                return $query->where('school_id', Auth::user()->school_id);
            }
        }

        return $query;
    }

    public function announcement() {
        return $this->morphMany(Announcement::class, 'table');
    }

    public function user() {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function class_section() {
        return $this->belongsTo(ClassSection::class)->withTrashed();
    }

    public function class() {
        return $this->belongsTo(ClassSchool::class)->withTrashed();
    }

    public function subjects() {
        $studentSubject = app(StudentSubjectInterface::class);
//        $class_id = $this->class_section->class->id;
        $class_section_id = $this->class_section->id;
        $core_subjects = $this->class_section->class->core_subjects;
        $elective_subject_count = $this->class_section->class->elective_subject_groups->count();
        $elective_subjects = $studentSubject->builder()->where('student_id', $this->user_id)->where('class_section_id', $class_section_id)->select("subject_id")->with('subject')->get();
        $response = array(
            'core_subject' => $core_subjects
        );
        if ($elective_subject_count > 0) {
            $response['elective_subject'] = $elective_subjects;
        }
        return $response;
    }

    public function currentSemesterSubjects(){

        if(request('student_id')){
            $student = $this->where('user_id', request('student_id'))->first();
        }else{
            $student = $this;
        }

        $studentSubject = app(StudentSubjectInterface::class);
        $cache = app(CachingService::class);
        $currentSemester = $cache->getDefaultSemesterData($student->school_id);
        // dd($this->toArray());
//        $class_id = $this->class_section->class->id;
        $class_section_id = $student->class_section->id;
        $core_subjects = $student->class_section->class->core_subjects()->where(function($query) use($currentSemester){
            (isset($currentSemester) && !empty($currentSemester)) ? $query->where('semester_id',$currentSemester->id)->orWhereNull('semester_id') : $query->orWhereNull('semester_id');
        })->get();
       
        $elective_subject_count = $student->class_section->class->elective_subject_groups()->where(function($query) use($currentSemester){
            (isset($currentSemester) && !empty($currentSemester)) ? $query->where('semester_id',$currentSemester->id)->orWhereNull('semester_id') : $query->orWhereNull('semester_id');
        })->count();
        // dd($elective_subject_count);
        $elective_subjects = $studentSubject->builder()->where('student_id', $student->user_id)->where('class_section_id', $class_section_id)->select("class_subject_id")->with('class_subject.subject')->get();
        // dd($elective_subjects->toArray());
        $response = array(
            'core_subject' => $core_subjects
        );
        if ($elective_subject_count > 0) {
            $response['elective_subject'] = $elective_subjects;
        }
        //dd($response);
        return $response;
    }

    public function classSubjects() {
        $core_subjects = $this->class_section->class->core_subjects;
        $elective_subjects = $this->class_section->class->elective_subject_groups->load('subjects');
        return ['core_subject' => $core_subjects, 'elective_subject_group' => $elective_subjects];
    }

    public function currentSemesterClassSubjects() {
        $cache = app(CachingService::class);
        $currentSemester = $cache->getDefaultSemesterData($this->school_id);
        $core_subjects = $this->class_section->class->core_subjects()->where(function($query) use($currentSemester){
            (isset($currentSemester) && !empty($currentSemester)) ? $query->where('semester_id',$currentSemester->id)->orWhereNull('semester_id') : $query->orWhereNull('semester_id');
        })->get();
        $elective_subjects = $this->class_section->class->elective_subject_groups()->where(function($query) use($currentSemester){
            (isset($currentSemester) && !empty($currentSemester)) ? $query->where('semester_id',$currentSemester->id)->orWhereNull('semester_id') : $query->orWhereNull('semester_id');
        })->with('subjects')->get();
        return ['core_subject' => $core_subjects, 'elective_subject_group' => $elective_subjects];
    }


    public function guardian() {
        return $this->belongsTo(User::class, 'guardian_id')->withTrashed();
    }

//    public function scopeOfTeacher($query) {
//        $user = Auth::user();
//        if ($user->hasRole('Teacher')) {
//            // for teacher list
//            $class_teacher = $user->teacher->class_section;
//            $class_section_id = array();
//            if ($class_teacher) {
//                $class_section_id[] = array($class_teacher->class_section_id);
//            }
//            $subject_teachers = $user->teacher->subjects;
//            if ($subject_teachers) {
//                foreach ($subject_teachers as $subject_teacher) {
//                    $class_section_id[] = array($subject_teacher->class_section_id);
//                }
//            }
//            return $query->whereIn('class_section_id', $class_section_id);
//        }
//
//        // for admin list
//        return $query;
//        //return if it doesn't affect above conditions
////        return $query->where('class_section_id', 0);
//    }

    public function fees_paid() {
        return $this->hasMany(FeesPaid::class, 'student_id')->withTrashed();
    }


    public function getFirstNameAttribute() {
        $firstName = '';
        if ($this->relationLoaded('user')) {
            $firstName .= $this->user->first_name;
        }
        return $firstName;
    }
    public function getLastNameAttribute() {
        $lastName = '';
        if ($this->relationLoaded('user')) {
            $lastName .= $this->user->last_name;
        }
        return $lastName;
    }
    public function getFullNameAttribute() {
        $fullName = '';
        if ($this->relationLoaded('user')) {
            $fullName .= $this->user->first_name.' '.$this->user->last_name;
        }
        return $fullName;
    }

    public function selectedStudentSubjects(){
        $studentSubject = app(StudentSubjectInterface::class);
        $cache = app(CachingService::class);
        $currentSemester = $cache->getDefaultSemesterData($this->school_id);

        $core_subjects = $this->class_section->class->core_subjects()->when($currentSemester, function($query) use($currentSemester) {
            return $query->where('semester_id', $currentSemester->id);
        })->orWhereNull('semester_id')->get();

        $subjects = $core_subjects->toArray();

        $elective_subject_count = $this->class_section->class->elective_subject_groups()->where(function($query) use($currentSemester){
            (isset($currentSemester) && !empty($currentSemester)) ? $query->where('semester_id',$currentSemester->id)->orWhereNull('semester_id') : $query->orWhereNull('semester_id');
        })->count();

        if ($elective_subject_count > 0) {
            $elective_subjects = $studentSubject->builder()->where('student_id', $this->user_id)->with('class_subject.subject')->get();
            $subjects = array_merge($subjects,$elective_subjects->toArray());
        }
        return collect($subjects);
    }

    /**
     * Get all of the exam_result for the Students
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function exam_result()
    {
        return $this->hasMany(ExamResult::class, 'student_id', 'user_id');
    }

    /**
     * Get all of the attendance for the Students
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'student_id', 'user_id');
    }

    public function session_year() {
        return $this->belongsTo(SessionYear::class, 'session_year_id');
    }

    public function shift() {
        return $this->belongsTo(Shift::class, 'shift_id', 'id');
    }

    public function session_years_trackings()
    {
        return $this->hasMany(SessionYearsTracking::class, 'modal_id', 'id')->where('modal_type', 'App\Models\Students');
    }

    public function student_subjects() {
        return $this->hasMany(StudentSubject::class, 'student_id', 'user_id');
    }

    // public function elective_subject_groups() {
    //     return $this->hasMany(ElectiveSubjectGroup::class, 'class_id', 'class_id');
    // }

    public function getCreatedAtAttribute()
    {
        return $this->formatDateValue($this->getRawOriginal('created_at'));
    }   

    public function getUpdatedAtAttribute()
    {
        return $this->formatDateValue($this->getRawOriginal('updated_at'));
    }

    public function getAdmissionDateAttribute($value)
    {
        return $this->formatDateOnly($value);
    }

}
