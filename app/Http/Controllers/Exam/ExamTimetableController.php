<?php

namespace App\Http\Controllers\Exam;

use Throwable;
use Illuminate\Http\Request;
use App\Services\CachingService;
use App\Services\ResponseService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\Exam\ExamInterface;
use Illuminate\Support\Facades\Validator;
use App\Repositories\ExamTimetable\ExamTimetableInterface;
use App\Repositories\ClassSection\ClassSectionInterface;
use App\Repositories\ClassTeachers\ClassTeachersInterface;
use App\Repositories\Student\StudentInterface;
use Carbon\Carbon;

class ExamTimetableController extends Controller {
    private ExamInterface $exam;
    private ExamTimetableInterface $examTimetable;
    private CachingService $cache;
    private ClassSectionInterface $classSection;
    private ClassTeachersInterface $classTeachers;
    private StudentInterface $student;

    public function __construct(ExamInterface $exam, ExamTimetableInterface $examTimetable, CachingService $cache, ClassSectionInterface $classSection, ClassTeachersInterface $classTeachers, StudentInterface $student) {
        $this->exam = $exam;
        $this->examTimetable = $examTimetable;
        $this->cache = $cache;
        $this->classSection = $classSection;
        $this->classTeachers = $classTeachers;
        $this->student = $student;
    }

    public function edit($examId) {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenRedirect('exam-timetable-list');
        $currentSessionYear = $this->cache->getDefaultSessionYear();
        $currentSemester = $this->cache->getDefaultSemesterData();
        $exam = $this->exam->builder()->where(['id' => $examId])->with(['class.medium', 'class.all_subjects' => function($query) use($currentSemester){
            (isset($currentSemester) && !empty($currentSemester)) ? $query->where('semester_id',$currentSemester->id)->orWhereNull('semester_id') : $query->orWhereNull('semester_id');
        }, 'timetable'])->firstOrFail();
        $last_result_submission_date = $exam->getRawOriginal('last_result_submission_date');
        $disabled = $exam->publish ? 'disabled' : '';

        // dd($exam->toArray());
        return response(view('exams.timetable', compact('exam','currentSessionYear','disabled','last_result_submission_date')));
    }

    public function update(Request $request, $examID) {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenSendJson('exam-timetable-create');
        $validator = Validator::make($request->all(), [
            'timetable'                 => 'required|array',
            'timetable.*.passing_marks' => 'required|lte:timetable.*.total_marks',
            'timetable.*.end_time'      => 'required|after:timetable.*.start_time',
            'timetable.*.date'          => 'required|date',
            'last_result_submission_date' => 'required|date',
        ], [
            'timetable.*.passing_marks.lte' => trans('passing_marks_should_less_than_or_equal_to_total_marks'),
            'timetable.*.end_time.after'    => trans('end_time_should_be_greater_than_start_time'),
            'last_result_submission_date.after'   => trans('the_exam_result_marks_submission_date_should_be_greater_than_last_exam_timetable_date'),
        ]);

        $validator->after(function ($validator) use ($request) {
            $timetable = $request->timetable;
            $lastResultDate = $request->last_result_submission_date;
          
            if (!empty($timetable) && $lastResultDate) {
                // Extract the latest date from the timetable
                $latestExamDate = collect($timetable)
                ->pluck('date')
                ->map(fn($date) => Carbon::createFromFormat('d-m-Y', $date)) // Convert to Carbon
                ->max() // Get the max date
                ->format('Y-m-d'); 

                $latestExamDate = Carbon::parse($latestExamDate)->format('Y-m-d');
                $lastResultDate = Carbon::parse($lastResultDate)->format('Y-m-d');

                if ($latestExamDate && $lastResultDate <= $latestExamDate) {
                    $validator->errors()->add(
                        'last_result_submission_date',
                        trans('the_exam_result_marks_submission_date_should_be_greater_than_last_exam_timetable_date')
                    );
                }
            }
        });

        if ($validator->fails()) {
            ResponseService::errorResponse($validator->errors()->first());
        }
        try {
            DB::beginTransaction();

            foreach ($request->timetable as $timetable) {
                $examTimetable = array(
                    'exam_id'           => $examID,
                    'class_subject_id'  => $timetable['class_subject_id'],
                    'total_marks'       => $timetable['total_marks'],
                    'passing_marks'     => $timetable['passing_marks'],
                    'start_time'        => $timetable['start_time'],
                    'end_time'          => $timetable['end_time'],
                    'date'              => date('Y-m-d', strtotime($timetable['date'])),
                    'session_year_id'   => $request->session_year_id,
                );
                $this->examTimetable->updateOrCreate(['id' => $timetable['id'] ?? null], $examTimetable);
            }

            // Get Start Date & End Date From Exam Timetable
            $examTimetable = $this->examTimetable->builder()->where('exam_id',$examID);
            $startDate = $examTimetable->min('date');
            $endDate = $examTimetable->max('date');
            $last_result_submission_date = date('Y-m-d', strtotime($request->last_result_submission_date));
           
            // Update Start Date and End Date to the particular Exam
            $exam = $this->exam->update($examID,['start_date' => $startDate,'end_date' => $endDate, 'last_result_submission_date' => $last_result_submission_date]);
            // dd($exam);
            DB::commit();
          
             //Get class sections for notifications
            $classSectionIds = $this->classSection->builder()
                ->where('class_id', $exam->class_id)
                ->pluck('id');
           
            $classTeacherIds = $this->classTeachers->builder()
                ->whereIn('class_section_id', $classSectionIds)
                ->distinct()
                ->pluck('teacher_id')
                ->toArray();

            // Send notifications
            $title = "Exams Timetable Scheduled";
            $body = "Exam Timetable Scheduled Click here to see !!!";
            $type = "exam";

            $students = $this->student->builder()
                ->whereHas('class_section', function ($q) use ($classSectionIds) {
                    $q->whereIn('class_id', $classSectionIds);
                })
                ->get();

            $guardian_ids = $students->pluck('guardian_id')->toArray();
            $student_ids = $students->pluck('user_id')->toArray();
            $users = array_unique(array_merge($student_ids, $guardian_ids, $classTeacherIds));

            DB::commit();
            send_notification($users, $title, $body, $type);

            ResponseService::successResponse('Data Stored Successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, "Exam Timetable Controller -> Store method");
            ResponseService::errorResponse();
        }
    }

    public function destroy($id) {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenSendJson('exam-timetable-delete');
        try {
            $this->examTimetable->deleteById($id);
            ResponseService::successResponse('Data Deleted Successfully');
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "Exam Controller -> DeleteTimetable method");
            ResponseService::errorResponse();
        }
    }
}
