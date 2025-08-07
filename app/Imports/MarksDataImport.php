<?php

namespace App\Imports;

use App\Models\ExamTimetable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Services\ResponseService;
use App\Repositories\ExamMarks\ExamMarksInterface;
use App\Repositories\ExamTimetable\ExamTimetableInterface;
use App\Services\CachingService;
use App\Services\SessionYearsTrackingsService;
use Illuminate\Support\Facades\Auth;
use Throwable;
use JsonException;

class MarksDataImport implements WithMultipleSheets
{
    private mixed $classSectionID;
    private mixed $examID;
    private mixed $classSubjectID;
    private SessionYearsTrackingsService $sessionYearsTrackingsService;

    public function __construct($classSectionID, $examID, $classSubjectID, SessionYearsTrackingsService $sessionYearsTrackingsService = null)
    {
        $this->classSectionID = $classSectionID;
        $this->examID = $examID;
        $this->classSubjectID = $classSubjectID;
        $this->sessionYearsTrackingsService = $sessionYearsTrackingsService ?? app(SessionYearsTrackingsService::class);
    }

    public function sheets(): array
    {
        return [
            new FirstSheetImport(
                $this->classSectionID,
                $this->examID,
                $this->classSubjectID,
                $this->sessionYearsTrackingsService,
                app(CachingService::class)
            )
        ];
    }
}

class FirstSheetImport implements ToCollection, WithHeadingRow
{
    private mixed $classSectionID;
    private mixed $examID;
    private mixed $classSubjectID;
    private mixed $sessionYearsTrackingsService;
    private CachingService $cache;

    public function __construct($classSectionID, $examID, $classSubjectID, $sessionYearsTrackingsService, CachingService $cache)
    {
        $this->classSectionID = $classSectionID;
        $this->examID = $examID;
        $this->classSubjectID = $classSubjectID;
        $this->sessionYearsTrackingsService = $sessionYearsTrackingsService;
        $this->cache = $cache;
    }

    public function collection(Collection $collection)
    {
        $validator = Validator::make($collection->toArray(), [
            '*.student_id' => 'required|numeric',
            '*.obtained_marks' => 'required|numeric',
            '*.total_marks' => 'required|numeric',
        ], [
            'student_id.required' => 'The Student ID field is required.',
            'obtained_marks.required' => 'The Obtained Marks field is required.',
            'total_marks.required' => 'The Total Marks field is required.',
        ]);

        $validator->validate();

        DB::beginTransaction();
        try {
            $examTimetable = app(ExamTimetableInterface::class);
            $examMarks = app(ExamMarksInterface::class);

            $exam_timetable = $examTimetable->builder()->where(['exam_id' => $this->examID, 'class_subject_id' => $this->classSubjectID])->firstOrFail();
            // dd($collection);
            foreach ($collection as $row) {
                $passing_marks = $exam_timetable->passing_marks;
                $status = $row['obtained_marks'] >= $passing_marks ? 1 : 0;
                $marks_percentage = ($row['obtained_marks'] / $row['total_marks']) * 100;
                $exam_grade = findExamGrade($marks_percentage);

                if ($exam_grade == null) {
                    throw new \Exception('Grades data does not exist');
                }

                $existingMark = $examMarks->builder()
                    ->where([
                        'exam_timetable_id' => $exam_timetable->id,
                        'student_id' => $row['student_id'],
                        'class_subject_id' => $this->classSubjectID,
                    ])
                    ->first();

                if (empty($row['exam_marks_id']) && $existingMark) {
                    ResponseService::errorResponse("Marks already exist. Please download the latest Dummy file to update marks.");
                }

                $mark = $examMarks->updateOrCreate([
                    'id' => $row['exam_marks_id'] ?? null
                ], [
                    'exam_timetable_id' => $exam_timetable->id,
                    'student_id' => $row['student_id'],
                    'class_subject_id' => $this->classSubjectID,
                    'obtained_marks' => $row['obtained_marks'],
                    'passing_status' => $status,
                    'session_year_id' => $exam_timetable->session_year_id,
                    'grade' => $exam_grade,
                ]);

                $sessionYear = $this->cache->getDefaultSessionYear();
                $this->sessionYearsTrackingsService->storeSessionYearsTracking(
                    'App\Models\ExamMarks',
                    $mark->id,
                    Auth::user()->id,
                    $sessionYear->id,
                    Auth::user()->school_id,
                    null
                );
            }

            DB::commit();
            return true;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
