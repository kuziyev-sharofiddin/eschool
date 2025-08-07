<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentCommon;
use App\Repositories\Assignment\AssignmentInterface;
use App\Repositories\AssignmentCommon\AssignmentCommonInterface;
use App\Repositories\AssignmentSubmission\AssignmentSubmissionInterface;
use App\Repositories\ClassSection\ClassSectionInterface;
use App\Repositories\ClassSubject\ClassSubjectInterface;
use App\Repositories\Files\FilesInterface;
use App\Repositories\Semester\SemesterInterface;
use App\Repositories\SessionYear\SessionYearInterface;
use App\Repositories\Student\StudentInterface;
use App\Repositories\Subject\SubjectInterface;
use App\Repositories\SubjectTeacher\SubjectTeacherInterface;
use App\Rules\MaxFileSize;
use App\Services\BootstrapTableService;
use App\Services\CachingService;
use App\Services\ResponseService;
use App\Services\SessionYearsTrackingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class AssignmentController extends Controller
{
    private AssignmentInterface $assignment;
    private ClassSectionInterface $classSection;
    private SubjectInterface $subject;
    private FilesInterface $files;
    private StudentInterface $student;
    private AssignmentSubmissionInterface $assignmentSubmission;
    private SessionYearInterface $sessionYear;
    private CachingService $cache;
    private SubjectTeacherInterface $subjectTeacher;
    private AssignmentCommonInterface $assignmentCommon;
    private ClassSubjectInterface $class_subjects;
    private SessionYearsTrackingsService $sessionYearsTrackingsService;
    private SemesterInterface $semester;

    public function __construct(AssignmentInterface $assignment, ClassSectionInterface $classSection, SubjectInterface $subject, FilesInterface $files, StudentInterface $student, AssignmentSubmissionInterface $assignmentSubmission, SessionYearInterface $sessionYear, CachingService $cachingService, SubjectTeacherInterface $subjectTeacher, AssignmentCommonInterface $assignmentCommon, ClassSubjectInterface $class_subjects, SessionYearsTrackingsService $sessionYearsTrackingsService, SemesterInterface $semester)
    {
        $this->assignment = $assignment;
        $this->classSection = $classSection;
        $this->subject = $subject;
        $this->files = $files;
        $this->student = $student;
        $this->assignmentSubmission = $assignmentSubmission;
        $this->sessionYear = $sessionYear;
        $this->cache = $cachingService;
        $this->subjectTeacher = $subjectTeacher;
        $this->assignmentCommon = $assignmentCommon;
        $this->class_subjects = $class_subjects;
        $this->sessionYearsTrackingsService = $sessionYearsTrackingsService;
        $this->semester = $semester;
    }

    public function index()
    {
        ResponseService::noFeatureThenRedirect('Assignment Management');
        ResponseService::noPermissionThenRedirect('assignment-list');
        $assignment = $this->assignment->builder()->with('class_section.class', 'class_section.section', 'class_section.medium', 'file', 'class_subject.subject', 'assignment_commons')->first();
        $classSections = $this->classSection->builder()->with('class', 'class.stream', 'section', 'medium')->get();
        $subjectTeachers = $this->subjectTeacher->builder()->with('subject:id,name,type')->get();
        $sessionYears = $this->sessionYear->all();
        $semesters = $this->semester->builder()->get();
        $user = Auth::user();

       
        return response(view('assignment.index', compact('assignment', 'classSections', 'subjectTeachers', 'sessionYears', 'semesters')));
    }

    public function store(Request $request)
    {
        ResponseService::noFeatureThenRedirect('Assignment Management');
        ResponseService::noPermissionThenRedirect('assignment-create');
        $file_upload_size_limit = $this->cache->getSystemSettings('file_upload_size_limit');
        $request->validate([
            "class_section_id"      => 'required|array',
            "class_section_id.*"    => 'numeric',
            "subject_id"            => 'required|numeric',
            "name"                        => 'required',
            "description"                 => 'nullable',
            "due_date"                    => 'required|date',
            "points"                      => 'nullable',
            "resubmission"                => 'nullable|boolean',
            "extra_days_for_resubmission" => 'nullable|numeric',
            'file'                        => 'nullable|array',
            'file.*'                      => ['mimes:jpeg,png,jpg,gif,svg,webp,pdf,doc,docx,xml', new MaxFileSize($file_upload_size_limit) ],
            'add_url'          => $request->checkbox_add_url ? 'required' : 'nullable',
        ],[
            'file.*' => trans('The file Uploaded must be less than :file_upload_size_limit MB.', [
                'file_upload_size_limit' => $file_upload_size_limit,  
            ]),
        ]);
        try {
            DB::beginTransaction();

            $sessionYear = $this->cache->getDefaultSessionYear();

            $assignmentData = array(
                ...$request->all(),
                'due_date'                    => date('Y-m-d H:i', strtotime($request->due_date)),
                'resubmission'                => $request->resubmission ? 1 : 0,
                'extra_days_for_resubmission' => $request->resubmission ? $request->extra_days_for_resubmission : null,
                'session_year_id'             => $sessionYear->id,
                'created_by'                  => Auth::user()->id,
            );

            $section_ids = is_array($request->class_section_id) ? $request->class_section_id : [$request->class_section_id];
            $assignment = [];
            $assignmentCommonData = [];
        
            foreach ($section_ids as $section_id) {
                $assignmentData = array_merge($assignmentData, ['class_section_id' => $section_id]);
            }
        
            // Get class_section_id to class_subject_id
            $classSection = [];
            if ($request->class_section_id) {
                foreach ($request->class_section_id as $section_id) {
                    $classSection = $this->classSection->builder()->where('id', $section_id)->with(['class_subject' => function ($q) use ($request) {
                        $q->where('subject_id', $request->subject_id);
                    }])->first();
                    $subjectTeacher = $this->subjectTeacher->builder()->where('class_section_id', $section_id)->where('subject_id', $request->subject_id)->first();
                }
            }
        
            // Store the assignment data
            $assignmentData['class_subject_id'] = $subjectTeacher->class_subject_id;
            unset($assignmentData['subject_id']);
            unset($assignmentData['user_id']);
            $assignment = $this->assignment->create($assignmentData);
            // dd($assignment);

            // Create assignment_commons for each section
            foreach ($section_ids as $section_id) {
                $classSection = $this->classSection->builder()->where('id', $section_id)->with('class')->first();
                $subjectTeacher = $this->subjectTeacher->builder()->where('class_section_id', $section_id)->where('subject_id', $request->subject_id)->first();
                $assignmentCommonData['assignment_id'] = $assignment->id;
                $assignmentCommonData['class_section_id'] = $section_id;
                $assignmentCommonData['class_subject_id'] = $subjectTeacher->class_subject_id;
                $this->assignmentCommon->create($assignmentCommonData);
            }
        
            // Handle File Upload
            if ($request->hasFile('file')) {
                $fileData = [];
        
                $assignmentModelAssociate = $this->files->model()->modal()->associate($assignment);
        
                foreach ($request->file('file') as $file_upload) {
                    $tempFileData = array(
                        'modal_type' => $assignmentModelAssociate->modal_type,
                        'modal_id'   => $assignmentModelAssociate->modal_id,
                        'file_name'  => $file_upload->getClientOriginalName(),
                        'type'       => 1,
                        'file_url'   => $file_upload, 
                    );
                    $fileData[] = $tempFileData;
                }
        
                // Store the files data
                $this->files->createBulk($fileData);
            }
        
            // Handle URL Upload
            if ($request->add_url) {
                $urlData = [];
                $urls = is_array($request->add_url) ? $request->add_url : [$request->add_url];
        
                foreach ($urls as $url) {
                    $urlParts = parse_url($url);
                    $fileName = basename($urlParts['path'] ?? '/');
        
                    $assignmentModelAssociate = $this->files->model()->modal()->associate($assignment);
        
                    $tempUrlData = array(
                        'modal_type' => $assignmentModelAssociate->modal_type,
                        'modal_id'   => $assignmentModelAssociate->modal_id,
                        'file_name'  => $fileName, 
                        'type'       => 4,
                        'file_url'   => $url,
                    );
        
                    $urlData[] = $tempUrlData;
                }
        
                // Store the URL data
                $this->files->createBulk($urlData);
            }
        
            // Send Notification
            $subjectName = $this->subject->builder()->select('name')->where('id', $request->subject_id)->pluck('name')->first();
            $title = 'New assignment added in ' . $subjectName;
            $body = $request->name;
            $type = "assignment";
        
            // Get student and guardian IDs for notifications
            $students = $this->student->builder()->where('class_section_id', $request->class_section_id)->get();
            $guardian_id = $students->pluck('guardian_id')->toArray();
            $student_id = $students->pluck('user_id')->toArray();
            $user = array_merge($student_id, $guardian_id);
        
            $sessionYear = $this->cache->getDefaultSessionYear();
            $semester = $this->cache->getDefaultSemesterData();
            // dd($sessionYear, $semester);
            if (filled($semester)) {
                $this->sessionYearsTrackingsService->storeSessionYearsTracking('App\Models\Assignment', $assignment->id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, $semester->id);
            } else {
                $this->sessionYearsTrackingsService->storeSessionYearsTracking('App\Models\Assignment', $assignment->id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, null);
            }
            
            DB::commit();

            send_notification($user, $title, $body, $type);
        
           
            ResponseService::successResponse('Data Stored Successfully');
        } catch (Throwable $e) {
            
            if (Str::contains($e->getMessage(), ['does not exist', 'file_get_contents'])) {
                DB::commit();
                ResponseService::warningResponse("Data Stored successfully. But App push notification not send.");
            } else {
                DB::rollBack();
                ResponseService::logErrorResponse($e, "Assignment Controller -> Store Method");
                ResponseService::errorResponse();
            }
        }
    }

    public function show(Request $request)
    {
        ResponseService::noFeatureThenRedirect('Assignment Management');
        ResponseService::noPermissionThenRedirect('assignment-list');
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'ASC');
        $search = request('search');
        $semester_id = request('semester_id');

        $sql = $this->assignment->builder()->with([
                'class_section.medium', 
                'file', 
                'class_subject.subject',
                'assignment_commons.class_section.class',
                'assignment_commons.class_section.section', 
                'assignment_commons.class_section.medium',
                'assignment_commons.class_subject',
                'session_years_trackings'
            ])
            ->where(function ($query) use ($search) {
                $query->when($search, function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('id', 'LIKE', "%$search%")
                            ->orwhere('name', 'LIKE', "%$search%")
                            ->orwhere('instructions', 'LIKE', "%$search%")
                            ->orwhere('points', 'LIKE', "%$search%")
                            ->orwhere('session_year_id', 'LIKE', "%$search%")
                            ->orwhere('extra_days_for_resubmission', 'LIKE', "%$search%")
                            ->orwhere('due_date', 'LIKE', "%" . date('Y-m-d H:i:s', strtotime($search)) . "%")
                            ->orwhere('created_at', 'LIKE', "%" . date('Y-m-d H:i:s', strtotime($search)) . "%")
                            ->orwhere('updated_at', 'LIKE', "%" . date('Y-m-d H:i:s', strtotime($search)) . "%")
                            ->orWhereHas('class_section.class', function ($q) use ($search) {
                                $q->where('name', 'LIKE', "%$search%");
                            })->orWhereHas('class_section.section', function ($q) use ($search) {
                                $q->where('name', 'LIKE', "%$search%");
                            })->orWhereHas('class_subject.subject', function ($q) use ($search) {
                                $q->where('name', 'LIKE', "%$search%");
                            });
                    });
                });
            })
            ->when(request('subject_id') != null, function ($query) {
                $subject_id = request('subject_id');
                $query->whereHas('assignment_commons', function ($query) use ($subject_id) {
                    $query->where('class_subject_id', $subject_id);
                });
            })
            ->when(request('class_id') != null, function ($query) {
                $class_id = request('class_id');

                $query->whereHas('assignment_commons', function ($q) use ($class_id) {
                    $q->where('class_section_id', $class_id);
                });
            })->when(request('session_year_id') != null, function ($query) use ($request) {
                $query->where('session_year_id', $request->session_year_id);
            });

        if(request('semester_id') != null) {
            $semester_id = request('semester_id');
            $sql = $sql->whereHas('session_years_trackings', function ($q) use ($semester_id ) {
                $q->where('semester_id', $semester_id);
            });
        }

        $total = $sql->count();

        $sql->orderBy($sort, $order)->skip($offset)->take($limit);
        $res = $sql->get();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $no = 1;

        foreach ($res as $row) {
            $row = (object)$row;

            $assignmentCommons = $row->assignment_commons->map(function ($common) {
                return $common->class_section ? $common->class_section->full_name : null;
            });
            
            $assignmentCommons->filter()->map(function ($name) {
                return "{$name},";
            })->toArray();


            //Show Edit and Soft Delete Buttons
            $operate = BootstrapTableService::editButton(route('assignment.update', $row->id));
            $operate .= BootstrapTableService::button("fa fa-eye",route('assignment.submissionDetails', $row->id), ['btn-success'],['title' => trans('View Submissions')]);
            $operate .= BootstrapTableService::deleteButton(route('assignment.destroy', $row->id));

            $tempRow = $row->toArray();
            $tempRow['no'] = $no++;
            $tempRow['no'] = $no++;
            $tempRow['org_due_date'] = $row->getRawOriginal('due_date');
            $tempRow['class_section_with_medium'] =  $assignmentCommons;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function edit($id)
    {
        ResponseService::noFeatureThenRedirect('Assignment Management');
        ResponseService::noPermissionThenRedirect('assignment-edit');
        $assignment = $this->assignment->builder()->with('class_section.class', 'class_section.section', 'class_section.medium', 'file', 'class_subject.subject', 'assignment_commons')->where('id', $id)->first();
        $classSections = $this->classSection->builder()->with('class', 'class.stream', 'section', 'medium')->get();
        $subjectTeachers = $this->subjectTeacher->builder()->with('subject:id,name,type')->get();
        $sessionYears = $this->sessionYear->all();

        $user = Auth::user();
        $assignmentCommons = AssignmentCommon::where('assignment_id', $id)->get();
        // dd($assignment->file);
        return response(view('assignment.edit', compact('assignment', 'classSections', 'subjectTeachers', 'sessionYears', 'assignmentCommons')));
    }

    public function update($id, Request $request)
    {
        ResponseService::noFeatureThenRedirect('Assignment Management');
        ResponseService::noPermissionThenRedirect('assignment-edit');
        $file_upload_size_limit = $this->cache->getSystemSettings('file_upload_size_limit');
        $request->validate([
            "class_section_id"      => 'required|array',
            "class_section_id.*"    => 'numeric',
            "class_subject_id"            => 'required|numeric',
            "name"                        => 'required',
            "description"                 => 'nullable',
            "due_date"                    => 'required|date',
            "points"                      => 'required',
            "resubmission"                => 'nullable|boolean',
            "extra_days_for_resubmission" => 'nullable|numeric',
            'file'                        => 'nullable|array',
            'file.*'                      => ['mimes:jpeg,png,jpg,gif,svg,webp,pdf,doc,docx,xml', new MaxFileSize($file_upload_size_limit) ]
        ],[
            'file.*' => trans('The file Uploaded must be less than :file_upload_size_limit MB.', [
                'file_upload_size_limit' => $file_upload_size_limit,  
            ]), 
        ]);
        try {
            DB::beginTransaction();

            // $sessionYearId = getSchoolSettings('session_year');
            $sessionYear = $this->cache->getDefaultSessionYear();

            $classID = $this->classSection->builder()->where('id', $request->class_section_id)->pluck('class_id')->first();
            $classSubject = $this->class_subjects->builder()->where('class_id', $classID)->where('subject_id', $request->class_subject_id)->first();
            $assignmentData = array(
                ...$request->all(),
                'class_subject_id'            => $classSubject->id,
                'due_date'                    => date('Y-m-d H:i', strtotime($request->due_date)),
                'resubmission'                => $request->resubmission ? 1 : 0,
                'extra_days_for_resubmission' => $request->resubmission ? $request->extra_days_for_resubmission : null,
                'session_year_id'             => $sessionYear->id,
                'edited_by'                   => Auth::user()->id,
            );
            
            $section_ids = is_array($request->class_section_id) ? $request->class_section_id : [$request->class_section_id];
            foreach ($section_ids as $section_id) {
                $assignmentData = array_merge($assignmentData, ['class_section_id' => $section_id]);
            }

            // DB::enableQueryLog();
            $assignment = $this->assignment->update($id, $assignmentData);
            // dd(DB::getQueryLog());
            // If File Exists
            if ($request->hasFile('file')) {
                $fileData = array(); // Empty FileData Array
                // Create A File Model Instance
                $assignmentModelAssociate = $this->files->model()->modal()->associate($assignment); // Get the Association Values of File with Assignment
                foreach ($request->file as $file_upload) {
                    // Create Temp File Data Array
                    $tempFileData = array(
                        'modal_type' => $assignmentModelAssociate->modal_type,
                        'modal_id'   => $assignmentModelAssociate->modal_id,
                        'file_name'  => $file_upload->getClientOriginalName(),
                        'type'       => 1,
                        'file_url'   => $file_upload
                    );
                    $fileData[] = $tempFileData; // Store Temp File Data in Multi-Dimensional File Data Array
                }
                $this->files->createBulk($fileData); // Store File Data
            }

            if ($request->add_url) {
                $fileInstance = $this->files->model();
                $assignmentModelAssociate = $fileInstance->modal()->associate($assignment);
                $tempUrlData = array([
                    'id'         => $request->add_url_id ?? null,
                    'modal_type' => $assignmentModelAssociate->modal_type,
                    'modal_id'   => $assignmentModelAssociate->modal_id,
                    'file_name'  => '', 
                    'type'       => 4,
                    'file_url'   => $request->add_url,
                ]);
                $this->files->upsert($tempUrlData, ['id'], ['id', 'modal_type', 'modal_id', 'file_name', 'type', 'file_url']);
            } else {
                if ($request->add_url_id) {
                    $this->files->deleteById($request->add_url_id);
                }
            }

            $subject_name = $this->subject->builder()->select('name')->where('id', $request->subject_id)->pluck('name')->first();
            $title = 'Update assignment in ' . $subject_name;
            $body = $request->name;
            $type = "assignment";
            $user = $this->student->builder()->select('user_id')->where('class_section_id', $request->class_section_id)->get()->pluck('user_id');
            $assignment->save();

            DB::commit();

            send_notification($user, $title, $body, $type);

            ResponseService::successResponse('Data Updated Successfully');
        } catch (Throwable $e) {
            if (Str::contains($e->getMessage(), [
                'does not exist','file_get_contents'
            ])) {
                DB::commit();
                ResponseService::warningResponse("Data Stored successfully. But App push notification not send.");
            } else {
                DB::rollback();
                ResponseService::logErrorResponse($e, "Assignment Controller -> Update Method");
                ResponseService::errorResponse();
            }
        }
    }

    public function destroy($id)
    {
        ResponseService::noFeatureThenRedirect('Assignment Management');
        ResponseService::noPermissionThenSendJson('assignment-delete');
        try {
            $this->assignment->deleteById($id);
            $sessionYear = $this->cache->getDefaultSessionYear();
            $this->sessionYearsTrackingsService->deleteSessionYearsTracking('App\Models\Assignment', $id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, null);
            ResponseService::successResponse('Data Deleted Successfully');
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "Assignment Controller -> Destroy Method");
            ResponseService::errorResponse();
        }
    }

    public function viewAssignmentSubmission()
    {
        ResponseService::noFeatureThenRedirect('Assignment Management');
        ResponseService::noPermissionThenRedirect('assignment-submission');
        $classSections = $this->classSection->builder()->with('class', 'class.stream', 'section', 'medium')->get();
        $subjectTeachers = $this->subjectTeacher->builder()->with('subject:id,name,type')->get();
        $semesters = $this->semester->builder()->get();
        return response(view('assignment.submission', compact('classSections', 'subjectTeachers', 'semesters')));
    }

    // public function assignmentSubmissionDetails($id, $class_section_id, $subject_id)
    public function assignmentSubmissionDetails($id)
    {
        $assignment = $this->assignment->builder()->with([
                'class_section.medium',
                'class_subject.subject',
        ])->where('id', $id)->first();
        return response(view('assignment.details', compact('assignment')));
    }

    public function showAssignmentSubmissionDetails($id, $class_section_id, $subject_id)
    {
        ResponseService::noFeatureThenRedirect('Assignment Management');
        ResponseService::noPermissionThenRedirect('assignment-submission');
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'ASC');
        $search = request('search');

        $sql = $this->assignmentSubmission->builder()->with('assignment.class_subject.subject', 'student:first_name,last_name,id', 'file', 'session_year', 'assignment.class_section.class', 'assignment.class_section.medium')->where('assignment_id', $id)
        ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('id', 'LIKE', "%$search%")
                        ->orwhere('created_at', 'LIKE', "%" . date('Y-m-d H:i:s', strtotime($search)) . "%")
                        ->orwhere('updated_at', 'LIKE', "%" . date('Y-m-d H:i:s', strtotime($search)) . "%")
                        ->orwhere('points', 'LIKE', "%$search%")
                        ->orwhere('feedback', 'LIKE', "%$search%")
                        ->orWhereHas('student', function ($query) use ($search) {
                            $query->whereRaw("concat(users.first_name,' ',users.last_name) LIKE '%" . $search . "%'");
                        });
                });
            });
        
        $total = $sql->count();
        
        $sql->orderBy($sort, $order)->skip($offset)->take($limit);
        $res = $sql->get();
        if (!$res) {
            ResponseService::errorResponse("Assignment Submission not found");
        }
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $no = 1;
        foreach ($res as $row) {
            $tempRow = $row->toArray();
            $tempRow['no'] = $no++;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function bulkAssignmentSubmissionUpdate(Request $request)
    {
        // return response()->json($request->assignment_data);
        ResponseService::noFeatureThenRedirect('Assignment Management');
        ResponseService::noPermissionThenRedirect('assignment-submission');
        $request->validate([
            'assignment_name' => 'required',
            'subject_name'    => 'required',
            'assignment_data' => 'required|array',
            'user_ids'        => 'required|string',
        ],[
            'user_ids.required' => 'Please select at least one student.',
        ]);

        // Get user IDs as array (remove any whitespace and filter empty values)
        $userIds = array_filter(
            array_map('trim', explode(',', $request->input('user_ids')))
        );

        // return response()->json($userIds);

        try {
            DB::beginTransaction();
            $assignmentSubmissionData = [];
            $acceptedStudentIds = [];
            $rejectedStudentIds = [];
            foreach ($request->assignment_data as $item) {
                if (in_array($item['id'], $userIds)) { // Only process if ID is in user_ids
                    $assignmentSubmissionData[] = [
                        'id'         => $item['id'],
                        'student_id' => $item['student_id'],
                        'status'     => $item['status'],
                        'points'     => $item['points'] ?? '',
                        'feedback'   => $item['feedback'],
                    ];
                    
                    if ($item['status'] == 1) {
                        $acceptedStudentIds[] = (int)$item['student_id'];
                    } else {
                        $rejectedStudentIds[] = (int)$item['student_id'];
                    }
                }
            }
            $acceptedGuardianIds = $this->student->builder()->whereIn('user_id', $acceptedStudentIds)->pluck('guardian_id')->toArray();
            $acceptedTitle = "Assignment accepted";
            $acceptedBody = $request->assignment_name . " accepted in " . $request->subject_name . " subject";
            
            if ($rejectedStudentIds != []) {
                $rejectedGuardianIds = $this->student->builder()->whereIn('user_id', $rejectedStudentIds)->pluck('guardian_id')->toArray();
                $rejectedTitle = "Assignment rejected";
                $rejectedBody = $request->assignment_name . " rejected in " . $request->subject_name . " subject";
            }
            // dd($guardianIds);
            // Upsert: Update existing by id or insert if doesn't exist
            $this->assignmentSubmission->upsert(
                $assignmentSubmissionData,
                ['id'], // Unique key for upsert
                ['student_id', 'status', 'points', 'feedback'] // Fields to update
            );

            DB::commit();

            // notification
            $type = "assignment";
            if (!empty($acceptedStudentIds) || !empty($acceptedGuardianIds)) {
                $acceptedUsers = array_merge($acceptedStudentIds, $acceptedGuardianIds);
                send_notification($acceptedUsers, $acceptedTitle, $acceptedBody, $type);
            }
            if (!empty($rejectedStudentIds) || !empty($rejectedGuardianIds)) {
                $rejectedUsers = array_merge($rejectedStudentIds, $rejectedGuardianIds);
                send_notification($rejectedUsers, $rejectedTitle, $rejectedBody, $type);
            }
            // dd($user);
            
            ResponseService::successResponse("Data Updated Successfully");
        } catch (Throwable $e) {
            if (Str::contains($e->getMessage(), [
                'does not exist','file_get_contents'
            ])) {
                DB::commit();
                ResponseService::warningResponse("Data Stored successfully. But App push notification not send.");
            } else {
                DB::rollback();
                ResponseService::logErrorResponse($e);
                ResponseService::errorResponse();
            }
        }
    }

    public function assignmentSubmissionList()
    {
        ResponseService::noFeatureThenRedirect('Assignment Management');
        ResponseService::noPermissionThenRedirect('assignment-submission');
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'ASC');
        $search = request('search');
        // $semester_id = request('semester_id');

        $sql = $this->assignmentSubmission->builder()->with('assignment.class_subject.subject', 'student:first_name,last_name,id', 'file', 'session_year', 'assignment.class_section.class', 'assignment.class_section.medium', 'session_years_trackings')
            //search query
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('id', 'LIKE', "%$search%")
                        ->orwhere('session_year_id', 'LIKE', "%$search%")
                        ->orwhere('created_at', 'LIKE', "%" . date('Y-m-d H:i:s', strtotime($search)) . "%")
                        ->orwhere('updated_at', 'LIKE', "%" . date('Y-m-d H:i:s', strtotime($search)) . "%")
                        ->orWhereHas('assignment.class_subject.subject', function ($query) use ($search) {
                            $query->where('name', 'LIKE', "%$search%");
                        })->orWhereHas('assignment', function ($query) use ($search) {
                            $query->where('name', 'LIKE', "%$search%");
                        })->orWhereHas('student', function ($query) use ($search) {
                            $query->whereRaw("concat(users.first_name,' ',users.last_name) LIKE '%" . $search . "%'");
                        });
                });
            })
            //subject filter data
            ->when(request('subject_id') != null, function ($query) {
                $subject_id = request('subject_id');
                $query->where(function ($query) use ($subject_id) {
                    $query->whereHas('assignment', function ($q) use ($subject_id) {
                        $q->where('class_subject_id', $subject_id);
                    });
                });
            })->when(request('class_section_id') != null, function ($query) {
                $class_section_id = request('class_section_id');
                $query->where(function ($query) use ($class_section_id) {
                    $query->whereHas('assignment', function ($q) use ($class_section_id) {
                        $q->where('class_section_id', $class_section_id);
                    });
                });
            });
    
        if(request('semester_id') != null) {
            $semester_id = request('semester_id');
            $sql = $sql->whereHas('session_years_trackings', function ($q) use ($semester_id ) {
                $q->where('semester_id', $semester_id);
            });
        }

        $total = $sql->count();

        $sql->orderBy($sort, $order)->skip($offset)->take($limit);
        $res = $sql->get();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $no = 1;
        foreach ($res as $row) {
            $row = (object)$row;
            $operate = BootstrapTableService::editButton(route('assignment.submission.update', $row->id));
            $tempRow = $row->toArray();
            $tempRow['points'] = $row->points;
            $tempRow['no'] = $no++;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }


    public function updateAssignmentSubmission($id, Request $request)
    {
        ResponseService::noFeatureThenRedirect('Assignment Management');
        ResponseService::noPermissionThenRedirect('assignment-submission');
        $request->validate([
            'status'   => 'required|numeric|in:1,2',
            'feedback' => 'nullable',
        ]);

        try {
            DB::beginTransaction();
            $updateAssignmentSubmissionData = array(
                'feedback' => $request->feedback,
                'points'   => $request->status == 1 ? $request->points : 0,
                'status'   => $request->status,
            );
            $assignmentSubmission = $this->assignmentSubmission->update($id, $updateAssignmentSubmissionData);

            $assignmentData = $this->assignment->builder()->where('id', $assignmentSubmission->assignment_id)->with('class_subject.subject')->first();
            if ($request->status == 1) {
                $title = "Assignment accepted";
                $body = $assignmentData->name . " accepted in " . $assignmentData->class_subject->subject->name_with_type . " subject";
            } else {
                $title = "Assignment rejected";
                $body = $assignmentData->name . " rejected in " . $assignmentData->class_subject->subject->name_with_type . " subject";
            }

            $type = "assignment";
            $students = $this->student->builder()->where('user_id', $assignmentSubmission->student_id)->get();
            $guardian_id = $students->pluck('guardian_id')->toArray();
            $student_id = $students->pluck('user_id')->toArray();
            $user = array_merge($student_id, $guardian_id);

            DB::commit();

            send_notification($user, $title, $body, $type);

            ResponseService::successResponse("Data Updated Successfully");
        } catch (Throwable $e) {
            if (Str::contains($e->getMessage(), [
                'does not exist','file_get_contents'
            ])) {
                DB::commit();
                ResponseService::warningResponse("Data Stored successfully. But App push notification not send.");
            } else {
                DB::rollback();
                ResponseService::logErrorResponse($e);
                ResponseService::errorResponse();
            }
        }
    }
}
