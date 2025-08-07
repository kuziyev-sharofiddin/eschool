<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\SessionYearsTracking;
use App\Traits\DateFormatTrait;

class Lesson extends Model {
    use HasFactory, DateFormatTrait;

    protected $fillable = [
        'name',
        'description',
        'school_id'
    ];

    protected $appends = ['class_section_with_medium','subject_with_name'];


    protected static function boot() {
        parent::boot();
        static::deleting(static function ($lesson) { // before delete() method call this
            if ($lesson->file) {
                foreach ($lesson->file as $file) {
                    if (Storage::disk('public')->exists($file->getRawOriginal('file_url'))) {
                        Storage::disk('public')->delete($file->getRawOriginal('file_url'));
                    }
                    if ($file->file_thumbnail && Storage::disk('public')->exists($file->getRawOriginal('file_thumbnail'))) {
                        Storage::disk('public')->delete($file->getRawOriginal('file_thumbnail'));
                    }
                }

                $lesson->file()->delete();
            }
            if ($lesson->topic) {
                $lesson->topic()->delete();
            }
        });
    }

    public function scopeOwner($query) {
        if (Auth::user()) {
            if (Auth::user()->hasRole('Super Admin')) {
                return $query;
            }
    
            if (Auth::user()->hasRole('School Admin')) {
                return $query->where('school_id', Auth::user()->school_id);
            }
    
            if (Auth::user()->hasRole('Teacher')) {
                $teacherId = Auth::user()->id;
                $subjectTeacher = SubjectTeacher::where('teacher_id', $teacherId)->pluck('class_subject_id')->toArray();
                return $query->whereHas('lesson_commons', function ($query) use ($subjectTeacher) {
                    $query->whereIn('class_subject_id', $subjectTeacher);
                });
                return $query->where('school_id', Auth::user()->school_id);
            }
    
            if (Auth::user()->hasRole('Student')) {
                return $query->where('school_id', Auth::user()->school_id);
            }
        }

        return $query;
    }

    public function class_subject() {
        return $this->belongsTo(ClassSubject::class);
    }

    public function class_section() {
        return $this->belongsTo(ClassSection::class)->with('class', 'section', 'medium')->withTrashed();
    }

    public function file() {
        return $this->morphMany(File::class, 'modal');
    }

    public function topic() {
        return $this->hasMany(LessonTopic::class);
    }

    public function lesson_commons() {
        return $this->hasMany(LessonCommon::class);
    }

    public function assignment_commons() {
        return $this->hasMany(AssignmentCommon::class,'assignment_id');
    }

    public function getClassSectionWithMediumAttribute() {
        if ($this->relationLoaded('lesson_commons')) {
            if ($this->lesson_commons) {
                return $this->lesson_commons()->first()->class_section->class->name . ' ' . $this->lesson_commons()->first()->class_section->section->name . ' - ' . $this->lesson_commons()->first()->class_section->medium->name;
            }
        }
        return null;
    }


    public function getSubjectWithNameAttribute() {
        if ($this->relationLoaded('lesson_commons')) {
            if ($this->lesson_commons) {
                return $this->lesson_commons()->first()->class_subject->subject->name . ' - ' . $this->lesson_commons()->first()->class_subject->subject->type;
            }
            
        }
        return null;
    }

    /**
     * Get all of the subject_teacher for the Assignment
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function subject_teacher()
    {
        return $this->belongsTo(SubjectTeacher::class, 'class_subject_id','class_subject_id');
    }

    public function semester() {
        return $this->belongsTo(Semester::class,'semester_id','id');
    }

    public function session_years_trackings()
    {
        return $this->hasMany(SessionYearsTracking::class, 'modal_id', 'id')->where('modal_type', 'App\Models\Lesson');
    }

    public function getCreatedAtAttribute()
    {
        return $this->formatDateValue($this->getRawOriginal('created_at'));
    }

    public function getUpdatedAtAttribute()
    {
        return $this->formatDateValue($this->getRawOriginal('updated_at'));
    }
}
