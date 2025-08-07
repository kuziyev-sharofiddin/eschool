<?php

namespace App\Http\Controllers;

use App\Models\PaymentConfiguration;
use App\Repositories\Addon\AddonInterface;
use App\Repositories\AddonSubscription\AddonSubscriptionInterface;
use App\Repositories\Feature\FeatureInterface;
use App\Repositories\PaymentTransaction\PaymentTransactionInterface;
use App\Repositories\Subscription\SubscriptionInterface;
use App\Services\BootstrapTableService;
use App\Services\CachingService;
use App\Services\FeaturesService;
use App\Services\ResponseService;
use App\Services\SubscriptionService;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Checkout\Session as StripeSession;

use App\Repositories\ClassSection\ClassSectionInterface;
use App\Repositories\Subject\SubjectInterface;
use App\Repositories\StudentSubject\StudentSubjectInterface;
use App\Repositories\Student\StudentInterface;
use App\Repositories\User\UserInterface;
use App\Repositories\SessionYear\SessionYearInterface;
use App\Repositories\ClassSchool\ClassSchoolInterface;
use App\Repositories\ClassSubject\ClassSubjectInterface;
use App\Repositories\ElectiveSubjectGroup\ElectiveSubjectGroupInterface;

class AssignElectiveSubjectController extends Controller {

    private ClassSectionInterface $classSection;
    private SubjectInterface $subject;
    private StudentSubjectInterface $studentSubject;
    private StudentInterface $student;
    private UserInterface $user;
    private ClassSchoolInterface $class;
    private SessionYearInterface $sessionYear;
    private ClassSubjectInterface $classSubject;
    private ElectiveSubjectGroupInterface $electiveSubjectGroup;
    public function __construct(
        ClassSectionInterface $classSection,
        SubjectInterface $subject,
        StudentSubjectInterface $studentSubject,
        StudentInterface $student,
        UserInterface $user,
        ClassSchoolInterface $class,
        SessionYearInterface $sessionYear,
        ClassSubjectInterface $classSubject,
        ElectiveSubjectGroupInterface $electiveSubjectGroup
    ) {
        $this->classSection = $classSection;
        $this->subject = $subject;
        $this->studentSubject = $studentSubject;
        $this->classSubject = $classSubject;
        $this->electiveSubjectGroup = $electiveSubjectGroup;
        $this->student = $student;
        $this->user = $user;
        $this->sessionYear = $sessionYear;
        $this->class = $class;
    }

    public function index() {
        ResponseService::noPermissionThenRedirect('assign-elective-subject-list');
        try {
            $class_sections = $this->classSection->all(['*'], ['class', 'class.stream', 'section', 'medium']);
            $subjects = $this->subject->builder()->where('type', 'Elective')->get();
            
            // Get elective subject groups
            // $electiveSubjectGroups = $this->classSubject->builder()->with('subject')
            //     ->where('school_id', Auth::user()->school_id)
            //     ->where('type', 'Elective')
            //     ->groupBy('elective_subject_group_id')
            //     ->groupBy('subject_id')
            //     ->groupBy('class_id')
            //     ->groupBy('semester_id')
            //     ->get();   
                
            $electiveSubjectGroups = $this->class->builder()->with('elective_subject_groups.subjects:id,name,type')
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $session_years = $this->sessionYear->all();

            return view('assign-elective-subject.index', compact('class_sections', 'session_years', 'electiveSubjectGroups'));
        } catch (\Exception $e) {
            return ResponseService::logErrorResponse($e, 'AssignElectiveSubjectController -> index method');
        }
    }

    public function store(Request $request) {
        ResponseService::noAnyPermissionThenSendJson(['assign-elective-subject-create']);
        try {
            $validator = Validator::make($request->all(), [
                'student_ids' => 'required|not_in:0',
                'class_subject_id' => 'required',
                'class_section_id' => 'required',   
                'session_year_id' => 'required'
            ],[
                'student_ids.required' => 'Please select at least one record.',
                'student_ids.not_in' => 'Please select at least one record.',
                'class_subject_id.required' => 'The subject field is required.',
                'class_section_id.required' => 'The class section field is required.',
                'session_year_id.required' => 'The session year field is required.',
            ]);

            if ($validator->fails()) {
                ResponseService::errorResponse($validator->errors()->first());
            }

            $student_ids = explode(",", $request->student_ids);
            $school_id = Auth::user()->school_id;

            DB::beginTransaction();
            try {
                $successCount = 0;
                $duplicateCount = 0;
                $duplicateStudents = [];

                // Get the elective subject group details first
                $electiveSubject = $this->classSubject->builder()->where('id', $request->class_subject_id)->first();
                if (!$electiveSubject) {
                    return ResponseService::errorResponse('Invalid elective subject selected.');
                }

                $electiveSubjectGroup = $this->electiveSubjectGroup->builder()
                    ->where('id', $electiveSubject->elective_subject_group_id)
                    ->first();
                
                if (!$electiveSubjectGroup) {
                    return ResponseService::errorResponse('Invalid elective subject group.');
                }

                foreach ($student_ids as $student_id) {
                    $student = $this->student->builder()->where('id', $student_id)->first();
                    
                    if (!$student) {
                        continue;
                    }

                    // Count how many subjects this student already has in this group
                    $currentSubjectsCount = $this->studentSubject->builder()
                        ->whereHas('class_subject', function($query) use ($electiveSubjectGroup) {
                            $query->where('elective_subject_group_id', $electiveSubjectGroup->id);
                        })
                        ->where('student_id', $student->user_id)
                        ->where('class_section_id', $request->class_section_id)
                        ->where('session_year_id', $request->session_year_id)
                        ->count();

                    // Check if adding one more subject would exceed the limit
                    if ($currentSubjectsCount >= $electiveSubjectGroup->total_selectable_subjects) {
                        $duplicateCount++;
                        $duplicateStudents[] = $student->user->first_name . ' ' . $student->user->last_name;
                        continue;
                    }

                    // Check if the student already has this specific subject
                    $existingAssignment = $this->studentSubject->builder()
                        ->where('student_id', $student->user_id)
                        ->where('class_subject_id', $request->class_subject_id)
                        ->where('class_section_id', $request->class_section_id)
                        ->where('session_year_id', $request->session_year_id)
                        ->where('school_id', $school_id)
                        ->first();

                    if ($existingAssignment) {
                        $duplicateCount++;
                        $duplicateStudents[] = $student->user->first_name . ' ' . $student->user->last_name;
                        continue;
                    }

                    $data = [
                        'student_id' => $student->user_id,
                        'class_subject_id' => $request->class_subject_id,
                        'class_section_id' => $request->class_section_id,
                        'session_year_id' => $request->session_year_id,
                        'school_id' => $school_id
                    ];
                    
                    $this->studentSubject->create($data);
                    $successCount++;
                }
                
                DB::commit();
                
                // Prepare response with detailed information

                if($successCount > 0){
                    ResponseService::successResponse('Elective Subjects Assigned Successfully');
                }else{
                    ResponseService::errorResponse('Elective Subjects is already assigned.');
                }
                // return response()->json($response);
            } catch (\Exception $e) {
                DB::rollback();
                \Log::error('Failed to assign elective subjects', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }

        } catch (\Exception $e) {
            return ResponseService::logErrorResponse($e, 'AssignElectiveSubjectController -> store method');
        }
    }

    public function show() {
        ResponseService::noPermissionThenRedirect('assign-elective-subject-list');
        try {
            $offset = request('offset', 0);
            $limit = request('limit', 10);
            $sort = request('sort', 'id');
            $order = request('order', 'DESC');
            $search = request('search');
            $session_year_id = request('session_year_id');
            $class_section_id = request('class_section_id');
            $subject_id = request('subject_id');
            $school_id = Auth::user()->school_id;

            $sql = $this->student->builder()
                ->where('application_status', 1)
                ->with([
                    'user:id,first_name,last_name',
                    'class_section.class',
                    'class_section.section',
                    'class_section.medium',
                    'student_subjects' => function($query) use ($school_id, $session_year_id) {
                        $query->where('school_id', $school_id)
                              ->where('session_year_id', $session_year_id)
                              ->with(['class_subject' => function($q) {
                                  $q->where('type', 'Elective')
                                    ->with(['subject', 'subjectGroup']);
                              }]);
                    },
                    'class_section.class.elective_subject_groups' => function($query) use ($school_id) {
                        $query->where('school_id', $school_id);
                    }
                ]);

            // Search functionality
            if (!empty($search)) {
                $sql->where(function ($query) use ($search) {
                    $query->where('admission_no', 'LIKE', "%$search%")
                        ->orWhere('roll_number', 'LIKE', "%$search%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('first_name', 'LIKE', "%$search%")
                                ->orWhere('last_name', 'LIKE', "%$search%")
                                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
                        });
                });
            }

            // Class section filter
            if ($class_section_id) {
                $sql->whereHas('class_section', function($query) use ($class_section_id) {
                    $query->where('id', $class_section_id);
                });
            }

            $total = $sql->count();

            $sql->orderBy($sort, $order);
            $res = $sql->skip($offset)->take($limit)->get();
            
            $rows = [];
            $no = $offset + 1;
            // dd($res->toArray());     
            foreach ($res as $row) {
                $tempRow = $row->toArray();
                $tempRow['no'] = $no++;
                $tempRow['full_name'] = $row->user ? ($row->user->first_name . ' ' . $row->user->last_name) : '';
                // dd($row->class_section);
                $tempRow['class_section'] = $row->class_section ? $row->class_section->full_name : '-';
                
                // Get only elective subjects
                $electiveSubjects = $row->student_subjects->filter(function($subject) {
                    return $subject->class_subject && $subject->class_subject->type === 'Elective';
                });
                
                $tempRow['elective_subjects'] = $electiveSubjects->map(function($subject) {
                    return $subject->class_subject->subject->name . ' (' . $subject->class_subject->subject->type . ')' ?? '';
                })->filter()->implode(', ');

                // Get total required subjects from elective subject groups
                // $total_selectable_subjects = $row->class_section->class->elective_subject_groups->sum('total_selectable_subjects');
                // $total_subjects = $row->class_section->class->elective_subject_groups->sum('total_subjects');

                // $tempRow['total_subjects'] = $total_selectable_subjects;
                // $tempRow['total_selected'] = $electiveSubjects->count();
                // $tempRow['status'] = ($tempRow['total_selected'] >= $tempRow['total_subjects'] && $tempRow['total_subjects'] > 0) ? 'complete' : 'incomplete';

                $rows[] = $tempRow;
            }
            
            return response()->json([
                'total' => $total,
                'rows' => $rows
            ]);

        } catch (\Exception $e) {
            return ResponseService::logErrorResponse($e, 'AssignElectiveSubjectController -> show method');
        }
    }

    public function edit($id) {
        //
    }

    public function update(Request $request, $id) {
        ResponseService::noPermissionThenSendJson('assign-elective-subject-edit');
        ResponseService::successResponse('Data Updated Successfully');
    }

    public function destroy($id) {
        ResponseService::noPermissionThenSendJson('assign-elective-subject-delete');
        try {
            $studentSubject = $this->studentSubject->findOrFail($id);
            
            if ($studentSubject->school_id != Auth::user()->school_id) {
                throw new \Exception(__('Invalid Assignment'));
            }

            $studentSubject->delete();
            ResponseService::successResponse('Data Deleted Successfully');
        } catch (\Exception $e) {
            ResponseService::logErrorResponse($e, 'AssignElectiveSubjectController -> destroy method');
            ResponseService::errorResponse();
        }
    }

    public function restore($id) {
        ResponseService::noPermissionThenSendJson('assign-elective-subject-edit');
        ResponseService::successResponse('Data Restored Successfully');
    }
  
    public function status($id) {
        ResponseService::noAnyPermissionThenSendJson(['assign-elective-subject-create', 'assign-elective-subject-edit']);
        try {
            DB::beginTransaction();
            $addon = $this->addon->findById($id);
            $addon = ['status' => $addon->status == 1 ? 0 : 1];
            $this->addon->update($id, $addon);
            DB::commit();
            ResponseService::successResponse('Data Updated Successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, 'AssignElectiveSubjectController -> status method');
            ResponseService::errorResponse();
        }
    }

    public function removeSubject(Request $request) {
        try {
            // dd($request->all());


            $studentSubject = $this->studentSubject->builder()->where('student_id', $request->student_id)->where('class_subject_id', $request->class_subject_id);
          
            $studentSubject->delete();
            ResponseService::successResponse('Data Deleted Successfully');
        } catch (\Exception $e) {
            ResponseService::logErrorResponse($e, 'AssignElectiveSubjectController -> removeSubject method');
            ResponseService::errorResponse();
        }
    }

}

