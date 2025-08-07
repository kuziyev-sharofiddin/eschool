<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Repositories\ClassSection\ClassSectionInterface;
use App\Repositories\Diary\DiaryInterface;
use App\Repositories\DiaryCategory\DiaryCategoryInterface;
use App\Repositories\DiaryStudent\DiaryStudentInterface;
use App\Repositories\SessionYear\SessionYearInterface;
use App\Repositories\Student\StudentInterface;
use App\Repositories\Subject\SubjectInterface;
use App\Repositories\SubjectTeacher\SubjectTeacherInterface;
use App\Repositories\User\UserInterface;
use App\Services\BootstrapTableService;
use App\Services\CachingService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Storage;
use Str;
use Throwable;

class DiaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    private ClassSectionInterface $classSection;
    private SessionYearInterface $sessionYear;
    private DiaryCategoryInterface $diaryCategories;
    private SubjectInterface $subject;
    private StudentInterface $student;
    private UserInterface $user;
    private DiaryInterface $diary;
    private DiaryStudentInterface $diaryStudent;
    private CachingService $cache;
    private SubjectTeacherInterface $subjectTeacher;

    public function __construct(ClassSectionInterface $classSection, SessionYearInterface $sessionYear, DiaryCategoryInterface $diaryCategories, SubjectInterface $subject, UserInterface $user, StudentInterface $student, CachingService $cache, DiaryInterface $diary, DiaryStudentInterface $diaryStudent, SubjectTeacherInterface $subjectTeacher)
    {
        $this->classSection = $classSection;
        $this->sessionYear = $sessionYear;
        $this->diaryCategories = $diaryCategories;
        $this->subject = $subject;
        $this->user = $user;
        $this->student = $student;
        $this->cache = $cache;
        $this->diary = $diary;
        $this->diaryStudent = $diaryStudent;
        $this->subjectTeacher = $subjectTeacher;
    }

    public function index()
    {
        ResponseService::noPermissionThenRedirect('student-diary-list');
        // $class_sections = $this->classSection->all(['*'], ['class', 'class.stream', 'section', 'medium']);
        $class_sections = $this->classSection->builder()->with('class', 'class.stream', 'section', 'medium')->get();
        $subjectTeachers = $this->subjectTeacher->builder()->with('subject:id,name,type')->get();
        $sessionYears = $this->sessionYear->all();
        $diaryCategories = $this->diaryCategories->all();
        $subjects = $this->subject->all();

        return view('diary.index', compact('class_sections', 'subjectTeachers', 'sessionYears', 'diaryCategories', 'subjects'));
    }

    public function changeSubjectsByClassSection(Request $request)
    {
        $class_section_id = $request->class_section_id;

        // if ($class_section_id) {
        //     dd($class_section_id);
        // }

        $subjectTeachers = $this->subjectTeacher->builder()->where('class_section_id', $class_section_id)->with('subject:id,name,type')->get();

        return response()->json($subjectTeachers);
    }

    public function showStudents(Request $request)
    {
        ResponseService::noPermissionThenRedirect('student-diary-list');
        // ResponseService::noPermissionThenRedirect('student-list');
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'ASC');
        $search = request('search');
        // $class_section_ids = request('class_section_id');

        $sql = $this->student->builder()->where('application_type', 'offline')->where('application_type', 'online')
            ->orwhere(function ($query) {
                $query->where('application_status', 1); // Only online applications with status 1
            })
            ->with('user')
            ->where(function ($query) use ($search) {
                $query->when($search, function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->orWhere('roll_number', 'LIKE', "%$search%")
                            ->orWhereHas('user', function ($q) use ($search) {
                                $q->where('first_name', 'LIKE', "%$search%")
                                    ->orwhere('last_name', 'LIKE', "%$search%")
                                    ->orWhereRaw("concat(first_name,' ',last_name) LIKE '%" . $search . "%'");
                            });
                    });
                });
                //class section filter data
            })
            ->when(request()->filled('class_section_id'), function ($query) {
                $query->where('class_section_id', request('class_section_id'));
            })
            ->when(request()->filled('session_year_id'), function ($query) {
                $query->where('session_year_id', request('session_year_id'));
            });


        if ($request->show_deactive) {
            $sql = $sql->whereHas('user', function ($query) {
                $query->where('status', 0)->withTrashed();
            });
        } else {
            $sql = $sql->whereHas('user', function ($query) {
                $query->where('status', 1);
            });
        }

        $total = $sql->count();
        if (!empty($request->class_section_id)) {
            $sql = $sql->orderBy('roll_number', 'ASC');
        } else {
            $sql = $sql->orderBy($sort, $order);
        }
        $sql->skip($offset)->take($limit);
        $res = $sql->get();
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate request data
        ResponseService::noPermissionThenRedirect('student-diary-create');

        $validator = Validator::make($request->all(), [
            'diary_category_id' => 'required',
            'student_class_section_map' => 'required|not_in:0,null', // required data like this - "{"10":1,"25":2,"26":3}"
            'date' => 'required|date',
        ], [
            'session_year_id.required' => 'Session Year is required.',
            'diary_category_id.required' => 'Diary Category is required.',
            'student_class_section_map.required' => 'Please select Students',
            'date.required' => 'Please select Date',
        ]);

        if ($validator->fails()) {
            ResponseService::errorResponse($validator->errors()->first());
        }

        try {
            DB::beginTransaction();
            $sessionYear = $this->cache->getDefaultSessionYear();
            $data = [
                'diary_category_id' => $request->diary_category_id,
                'user_id' => Auth::user()->id,
                'subject_id' => $request->subject_id,
                'session_year_id' => $sessionYear->id,
                'description' => $request->description,
                'date' => $request->date,
            ];
            $diary = $this->diary->create($data);

            $studentsClassSections = json_decode($request->student_class_section_map, true);

            $notifyUser = [];
            $studentsData = [];
            foreach ($studentsClassSections as $student_id => $class_section_id) {
                $studentsData[] = [
                    'diary_id' => $diary->id,
                    'student_id' => $student_id,
                    'class_section_id' => $class_section_id,
                ];
                $notifyUser[] = $student_id;
            }

            $this->diaryStudent->createBulk($studentsData);

            $body = [];
            $customData = [];
            if ($diary->description) {
                $body = [
                    'description' => $diary->description,
                    'date' => $diary->date,
                ];
            }
            $title = $diary->diary_category->name; // Title for Notification
            $body = $request->message;
            $type = 'Notification';

            DB::commit();
            send_notification($notifyUser, $title, $body, $type, $customData); // Send Notification
            ResponseService::successResponse('Data Stored Successfully');
        } catch (Throwable $e) {
            if (Str::contains($e->getMessage(), [
                'does not exist',
                'file_get_contents'
            ])) {
                DB::commit();
                ResponseService::warningResponse("Data Stored successfully. But App push notification not send.");
            } else {
                DB::rollBack();
                ResponseService::logErrorResponse($e, "Diary Controller -> Store Method");
                ResponseService::errorResponse();
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // dd(Auth::user()->hasRole('Teacher'));
        ResponseService::noPermissionThenRedirect('student-diary-list');

        $loggedInUser = Auth::user();
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $search = request('search');
        // $showDeleted = request('show_deleted');

        $sql = $this->diary->builder()
            ->with('session_year', 'diary_category', 'subject', 'diary_students.student', 'diary_students.class_section', 'diary_students.class_section.section', 'diary_students.class_section.class')
            ->where(function ($query) use ($search) {
                $query->when($search, function ($query) use ($search) {
                    $query->where('id', 'LIKE', "%$search%")
                        ->orWhere('description', 'LIKE', "%$search%")
                        ->orWhereHas('session_year', function ($q) use ($search) {
                            $q->where('name', 'LIKE', "%$search%");
                        })
                        ->orWhereHas('diary_category', function ($q) use ($search) {
                            $q->where('name', 'LIKE', "%$search%");
                        })
                        ->orWhereHas('subject', function ($q) use ($search) {
                            $q->where('name', 'LIKE', "%$search%");
                        })
                        ->orWhereHas('diary_students.student.user', function ($q) use ($search) {
                            $q->where('first_name', 'LIKE', "%$search%")
                                ->orWhere('last_name', 'LIKE', "%$search%")
                                ->orWhereRaw("concat(first_name,' ',last_name) LIKE '%" . $search . "%'");
                        });
                });
            })
            ->when(request()->filled('class_section_id'), function ($query) {
                $classId = request('class_section_id');
                $query->whereHas('diary_students', function ($q) use ($classId) {
                    $q->where('class_section_id', $classId);
                });
            })->when(request()->filled('session_year_id'), function ($query) {
                $sessionYearID = request('session_year_id');
                $query->where(function ($query) use ($sessionYearID) {
                    $query->where('session_year_id', $sessionYearID);
                });
            })
            ->when(request()->filled('filter_diary_type'), function ($query) {
                $type = request('filter_diary_type');
                $query->whereHas('diary_category', function ($q) use ($type) {
                    $q->where('type', $type);
                });
            })
            ->when(!empty($showDeleted), function ($query) {
                $query->onlyTrashed();
            });

        if ($loggedInUser->hasRole('Teacher')) {
            $sql->where('user_id', $loggedInUser->id);
        }

        if ($loggedInUser->hasRole('School Admin')) {
            $sql->with('user');
        }

        $total = $sql->count();

        $sql->orderBy($sort, $order)->skip($offset)->take($limit);
        $res = $sql->get();

        $bulkData = [];
        $bulkData['total'] = $total;
        $rows = [];
        $no = 1;

        foreach ($res as $row) {
            $stu = 'Click here to view';

            $operate = '';
            $operate .= BootstrapTableService::deleteButton(route('diary.destroy', $row->id));

            $tempRow = $row->toArray();
            $tempRow['no'] = $no++;
            $tempRow['student'] = $stu;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        ResponseService::noPermissionThenRedirect('student-diary-delete');
        try {
            DB::beginTransaction();
            // $this->diaryCategory->findOnlyTrashedById($id);
            $this->diary->findTrashedById($id)->forceDelete();
            DB::commit();
            ResponseService::successResponse('Data Deleted Successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, 'Diary Controller ->destroy Method');
            ResponseService::errorResponse();
        }
    }

    public function removeStudent($diaryId, $id)
    {
        ResponseService::noPermissionThenRedirect('student-diary-delete');
        $studentCount = $this->diaryStudent->builder()
            ->where(['diary_id' => $diaryId])->count();
        // dd('diary id', $diaryId, 'student', $studentCount,'id', $id);

        if ($studentCount == 1 || $studentCount < 2) {
            $this->destroy($diaryId);
        } else {
            try {
                DB::beginTransaction();
                // $this->diaryCategory->findOnlyTrashedById($id);
                $this->diaryStudent->findTrashedById($id)->forceDelete();
                DB::commit();
                ResponseService::successResponse('Student Removed Successfully');
            } catch (Throwable $e) {
                DB::rollBack();
                ResponseService::logErrorResponse($e, 'Diary Controller ->removeStudent Method');
                ResponseService::errorResponse();
            }
        }
    }
}
