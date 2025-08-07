<?php

namespace App\Http\Controllers;

use App\Repositories\Announcement\AnnouncementInterface;
use App\Repositories\AnnouncementClass\AnnouncementClassInterface;
use App\Repositories\ClassSection\ClassSectionInterface;
use App\Repositories\ClassSubject\ClassSubjectInterface;
use App\Repositories\Files\FilesInterface;
use App\Repositories\Student\StudentInterface;
use App\Repositories\StudentSubject\StudentSubjectInterface;
use App\Repositories\SubjectTeacher\SubjectTeacherInterface;
use App\Rules\MaxFileSize;
use App\Services\BootstrapTableService;
use App\Services\CachingService;
use App\Services\ResponseService;
use App\Services\SessionYearsTrackingsService;
use App\Services\GeneralFunctionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;
use TypeError;
use App\Repositories\SessionYear\SessionYearInterface;

class AnnouncementController extends Controller {

    private AnnouncementInterface $announcement;
    private ClassSectionInterface $classSection;
    private SubjectTeacherInterface $subjectTeacher;
    private StudentInterface $student;
    private FilesInterface $files;
    private StudentSubjectInterface $studentSubject;
    private ClassSubjectInterface $classSubject;
    private CachingService $cache;
    private AnnouncementClassInterface $announcementClass;
    private SessionYearsTrackingsService $sessionYearsTrackingsService;
    private SessionYearInterface $sessionYear;
    public function __construct(AnnouncementInterface $announcement, ClassSectionInterface $classSection, SubjectTeacherInterface $subjectTeacher, StudentInterface $student, FilesInterface $files, StudentSubjectInterface $studentSubject, ClassSubjectInterface $classSubject, CachingService $cachingService, AnnouncementClassInterface $announcementClass, SessionYearsTrackingsService $sessionYearsTrackingsService, SessionYearInterface $sessionYear) {
        $this->announcement = $announcement;
        $this->classSection = $classSection;
        $this->subjectTeacher = $subjectTeacher;
        $this->student = $student;
        $this->files = $files;
        $this->studentSubject = $studentSubject;
        $this->classSubject = $classSubject;
        $this->cache = $cachingService;
        $this->announcementClass = $announcementClass;
        $this->sessionYearsTrackingsService = $sessionYearsTrackingsService;
        $this->sessionYear = $sessionYear;
    }


    public function index() {
        ResponseService::noFeatureThenRedirect('Announcement Management');
        ResponseService::noPermissionThenRedirect('announcement-list');

        if (Auth::user()->hasRole('Teacher')) {
            $teacherId = Auth::id() ?? null;
            $class_section = $this->classSection->builder()
                ->whereHas('subject_teachers', function ($query) use ($teacherId) {
                    $query->where('teacher_id', $teacherId);
                })
                ->with('class', 'class.stream', 'section', 'medium')
                ->get();
                // dd($class_section);
        } else {
            $class_section = $this->classSection->builder()->with('class', 'class.stream', 'section', 'medium')->get(); // Get the Class Section of Teacher
        }
        $subjectTeachers = $this->subjectTeacher->builder()->with(['subject:id,name,type'])->get();
        $file_upload_size_limit = $this->cache->getSystemSettings('file_upload_size_limit');
        $sessionYears = $this->sessionYear->builder()->get();
        return view('announcement.index', compact('class_section', 'subjectTeachers', 'file_upload_size_limit', 'sessionYears'));
    }

    public function store(Request $request) {
        ResponseService::noFeatureThenSendJson('Announcement Management');
        ResponseService::noPermissionThenSendJson('announcement-create');
        $file_upload_size_limit = $this->cache->getSystemSettings('file_upload_size_limit');
        $request->validate([
            'title'            => 'required',
            'class_section_id' => 'required|array',
            'subject_id'       => Auth::user() && Auth::user()->hasRole('Teacher') ? 'required|exists:subjects,id' : 'nullable|exists:subjects,id',
            'file'             => 'nullable|array',
            'file.*'           => ['mimes:jpeg,png,jpg,gif,svg,webp,pdf,doc,docx,xml', new MaxFileSize($file_upload_size_limit) ],
            'add_url'          => $request->checkbox_add_url ? 'required' : 'nullable',
        ], [
            'class_section_id.required' => trans('the_class_section_field_id_required'),
            'file.*' => trans('The file Uploaded must be less than :file_upload_size_limit MB.', [
                'file_upload_size_limit' => $file_upload_size_limit,  
            ]),
        ]);
        try {
            DB::beginTransaction();
            $sessionYear = $this->cache->getDefaultSessionYear(); // Get Current Session Year
            $section_ids = is_array($request->class_section_id) ? $request->class_section_id : [$request->class_section_id];
            // Custom Announcement Array to Store Data
            $announcementData = array(
                'title'           => $request->title,
                'description'     => $request->description,
                'session_year_id' => $sessionYear->id,
                'school_id'       => Auth::user()->school_id, // Ensure this returns a valid school ID
            );
            
            $announcement = $this->announcement->create($announcementData); // Store Data

            $this->sessionYearsTrackingsService->storeSessionYearsTracking('App\Models\Announcement', $announcement->id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, null);

            $announcementClassData = array();
            $classSubjects = [];
            if (!empty($request->subject_id)) {

                foreach ($section_ids as $section_id) {
                    $classSection = $this->classSection->builder()->where('id', $section_id)->with('class')->first();
                    $classSubjects = $this->classSubject->builder()->where('class_id', $classSection->class->id)->where('subject_id', $request->subject_id)->first();
                }
                // When Subject is passed then Store the data according to Subject Teacher
                $teacherId = Auth::user()->id; // Teacher ID
                $subjectTeacherData = $this->subjectTeacher->builder()->with('subject')->whereIn('class_section_id', $request->class_section_id)->where(['teacher_id' => $teacherId, 'class_subject_id' => $classSubjects->id])->first(); // Get the Subject Teacher Data
                $subjectName = $subjectTeacherData->subject_with_name; // Subject Name

                // Check the Subject Type and Select Students According to it for Notification
                $getClassSubjectType = $this->classSubject->findById($classSubjects->id,['type']);
                if ($getClassSubjectType == 'Elective') {
                    $getStudentId = $this->studentSubject->builder()->select('student_id')->whereIn('class_section_id', $request->class_section_id)->where(['class_subject_id' => $classSubjects->id])->get()->pluck('student_id'); // Get the Student's ID According to Class Subject
                    $notifyUser = $this->student->builder()->select('user_id')->whereIn('id', $getStudentId)->get()->pluck('user_id'); // Get the Student's User ID
                } else {
                    $notifyUser = $this->student->builder()->select('user_id')->whereIn('class_section_id', $request->class_section_id)->get()->pluck('user_id'); // Get the All Student's User ID In Specified Class
                }

                $title = trans('New announcement in') . " " .$subjectName; // Title for Notification

            } else {
                $notifyUser = $this->student->builder()->select('user_id')->whereIn('class_section_id', $section_ids)->get()->pluck('user_id'); // Get the Student's User ID of Specified Class for Notification

                $title = trans('New announcement'); // Title for Notification
            }

            foreach ($section_ids as $section_id) {
                $classSection = $this->classSection->builder()->where('id', $section_id)->with('class')->first();
                $classSubjects = $this->classSubject->builder()->where('class_id', $classSection->class->id)->where('subject_id', $request->subject_id)->first();

                if (!empty($request->subject_id)) {
                    $announcementClassData = [
                        'announcement_id'   => $announcement->id,
                        'class_section_id'  => $section_id,
                        'class_subject_id'  => $classSubjects->id
                    ];
                } else {
                    $announcementClassData = [
                        'announcement_id'   => $announcement->id,
                        'class_section_id'  => $section_id,
                    ];
                }
        
                $announcementClass = $this->announcementClass->create($announcementClassData);

                $semester = $this->cache->getDefaultSemesterData();
                if ($semester) {
                    $this->sessionYearsTrackingsService->storeSessionYearsTracking('App\Models\AnnouncementClass', $announcementClass->id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, $semester->id); 
                } else {
                    $this->sessionYearsTrackingsService->storeSessionYearsTracking('App\Models\AnnouncementClass', $announcementClass->id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, null);
                }
            }

            // If File Exists
            if ($request->hasFile('file')) {
                $fileData = array(); // Empty FileData Array
                $fileInstance = $this->files->model(); // Create A File Model Instance
                $announcementModelAssociate = $fileInstance->modal()->associate($announcement); // Get the Association Values of File with Announcement
                foreach ($request->file as $file_upload) {
                    // Create Temp File Data Array
                    $tempFileData = array(
                        'modal_type' => $announcementModelAssociate->modal_type,
                        'modal_id'   => $announcementModelAssociate->modal_id,
                        'file_name'  => $file_upload->getClientOriginalName(),
                        'type'       => 1,
                        'file_url'   => $file_upload
                    );
                    $fileData[] = $tempFileData; // Store Temp File Data in Multi-Dimensional File Data Array
                }
                $this->files->createBulk($fileData); // Store File Data
            }
            if ($request->add_url) {
                $urlData = array(); // Empty URL data array
            
                $urls = is_array($request->add_url) ? $request->add_url : [$request->add_url];
            
                foreach ($urls as $url) {
                    $urlParts = parse_url($url);
                    
                    $fileName = basename($urlParts['path'] ?? '/'); // Extract the file name from the URL
                    $fileInstance = $this->files->model();
                    $announcementModelAssociate = $fileInstance->modal()->associate($announcement);
            
                    $tempUrlData = array(
                        'modal_type' => $announcementModelAssociate->modal_type,
                        'modal_id'   => $announcementModelAssociate->modal_id,
                        'file_name'  => $fileName, 
                        'type'       => 4,
                        'file_url'   => $url,
                    );
            
                    $urlData[] = $tempUrlData; // Store temp URL data in the array
                }
            
                // Store the URL data
                $this->files->createBulk($urlData);
            }

            $semester = $this->cache->getDefaultSemesterData();
            if ($semester) {
                $this->sessionYearsTrackingsService->storeSessionYearsTracking('App\Models\Announcement', $announcement->id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, $semester->id);
            } else {
                $this->sessionYearsTrackingsService->storeSessionYearsTracking('App\Models\Announcement', $announcement->id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, null);
            }

            DB::commit();

            if ($notifyUser !== null && !empty($title)) {
                $type = 'Class Section'; // Get The Type for Notification
                $body = $request->title; // Get The Body for Notification
                send_notification($notifyUser->toArray(), $title, $body, $type); // Send Notification
            }

           

            ResponseService::successResponse('Data Stored Successfully');
        } catch (Throwable $e) {
            $notificationStatus = app(GeneralFunctionService::class)->wrongNotificationSetup($e);
            if ($notificationStatus) {
                DB::rollBack();
                ResponseService::logErrorResponse($e, "Announcement Controller -> Store Method");
                ResponseService::errorResponse();
            } else {
                DB::commit();
                ResponseService::warningResponse("Data Stored successfully. But App push notification not send.");
            }
            
        }
    }

    public function update($id, Request $request) {
        ResponseService::noFeatureThenSendJson('Announcement Management');
        ResponseService::noPermissionThenSendJson('announcement-edit');

        $file_upload_size_limit = $this->cache->getSystemSettings('file_upload_size_limit');
        $validator = Validator::make($request->all(), [
            'title'            => 'required',
            'class_section_id' => 'required',
            'file'             => 'nullable|array',
            'file.*'           => ['mimes:jpeg,png,jpg,gif,svg,webp,pdf,doc,docx,xml', new MaxFileSize($file_upload_size_limit) ]
        ],[
            'file.*' => trans('The file Uploaded must be less than :file_upload_size_limit MB.', [
                'file_upload_size_limit' => $file_upload_size_limit,  
            ]),
        ]);
        if ($validator->fails()) {
            ResponseService::errorResponse($validator->errors()->first());
        }
        try {
            DB::beginTransaction();
            $sessionYear = $this->cache->getDefaultSessionYear(); // Get Current Session Year

            // Custom Announcement Array to Store Data
            $announcementData = array(
                'title'           => $request->title,
                'description'     => $request->description,
                'session_year_id' => $sessionYear->id,
            );

            $announcement = $this->announcement->update($id, $announcementData); // Store Data
            $announcementClassData = array();
            $oldClassSection = $this->announcement->findById($id)->announcement_class->pluck('class_section_id')->toArray();

            //Check the Assign Data
         
            if (!empty($request->subject_id)) {

                // When Subject is passed then Store the data according to Subject Teacher
                // $teacherId = Auth::user()->teacher->id; // Teacher ID
                $teacherId = Auth::user()->id; // Teacher ID foreign key directly assign to user table
                $classSection = $this->classSection->builder()->where('id', $request->class_section_id)->with('class')->first();
                $classSubjects = $this->classSubject->builder()->where('class_id', $classSection->class->id)->where('subject_id', $request->subject_id)->first();

                $subjectTeacherData = $this->subjectTeacher->builder()->whereIn('class_section_id', $request->class_section_id)->where(['teacher_id' => $teacherId, 'class_subject_id' => $classSubjects->id])->first(); // Get the Subject Teacher Data
                $subjectName = $subjectTeacherData->subject->name; // Subject Name

                // Check the Subject Type and Select Students According to it for Notification
                $getClassSubjectType = $this->classSubject->builder()->where('id', $request->class_subject_id)->pluck('type')->first();
                if ($getClassSubjectType == 'Elective') {
                    $getStudentId = $this->studentSubject->builder()->select('student_id')->whereIn('class_section_id', $request->class_section_id)->where(['class_subject_id' => $request->class_subject_id])->get()->pluck('student_id'); // Get the Student's ID According to Class Subject
                    $notifyUser = $this->student->builder()->select('user_id')->whereIn('id', $getStudentId)->get()->pluck('user_id'); // Get the Student's User ID
                } else {
                    $notifyUser = $this->student->builder()->select('user_id')->whereIn('class_section_id', $request->class_section_id)->get()->pluck('user_id'); // Get the All Student's User ID In Specified Class
                }

              
                
                foreach ($request->class_section_id as $class_section) {
                    $announcementClassData[] = [
                        'announcement_id'   => $announcement->id,
                        'class_section_id'  => $classSection->id,
                        'class_subject_id'  => $classSubjects->id
                    ];

                    // Check class section
                    $key = array_search($class_section, $oldClassSection);
                    if ($key !== false) {
                        unset($oldClassSection[$key]);
                    }
                }

                $title = trans('Updated announcement in') . $subjectName; // Title for Notification


            } else {
                // When only Class Section is passed
                $notifyUser = $this->student->builder()->select('user_id')->whereIn('class_section_id', $request->class_section_id)->get()->pluck('user_id'); // Get the Student's User ID of Specified Class for Notification


                // Set class sections
                foreach ($request->class_section_id as $class_section) {
                    $announcementClassData[] = [
                        'announcement_id'  => $announcement->id,
                        'class_section_id' => $class_section
                    ];
                    // Check class section
                    $key = array_search($class_section, $oldClassSection);
                    if ($key !== false) {
                        unset($oldClassSection[$key]);
                    }
                }
                $title = trans('Updated announcement'); // Title for Notification
            }

            $this->announcementClass->upsert($announcementClassData, ['announcement_id', 'class_section_id', 'school_id'], ['announcement_id', 'class_section_id', 'school_id', 'class_subject_id']);

            // Delete announcement class sections
            $this->announcementClass->builder()->where('announcement_id', $id)->whereIn('class_section_id', $oldClassSection)->delete();


            // If File Exists
            if ($request->hasFile('file')) {
                $fileData = array(); // Empty FileData Array
                $fileInstance = $this->files->model(); // Create A File Model Instance
                $announcementModelAssociate = $fileInstance->modal()->associate($announcement); // Get the Association Values of File with Announcement
                foreach ($request->file as $file_upload) {
                    // Create Temp File Data Array
                    $tempFileData = array(
                        'modal_type' => $announcementModelAssociate->modal_type,
                        'modal_id'   => $announcementModelAssociate->modal_id,
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
                $announcementModelAssociate = $fileInstance->modal()->associate($announcement);
                $tempUrlData = array([
                    'id'         => $request->add_url_id ?? null,
                    'modal_type' => $announcementModelAssociate->modal_type,
                    'modal_id'   => $announcementModelAssociate->modal_id,
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
            
            DB::commit();

            if ($notifyUser !== null && !empty($title)) {
                $type = $request->aissgn_to; // Get The Type for Notification
                $body = $request->title; // Get The Body for Notification
                // send_notification($notifyUser, $title, $body, $type); // Send Notification
            }

         
            ResponseService::successResponse('Data Updated Successfully');
        } catch (Throwable $e) {
            $notificationStatus = app(GeneralFunctionService::class)->wrongNotificationSetup($e);
            if ($notificationStatus) {
                DB::rollBack();
                ResponseService::logErrorResponse($e, "Announcement Controller -> Update Method");
                ResponseService::errorResponse();
            } else {
                DB::commit();
                ResponseService::warningResponse("Data Stored successfully. But App push notification not send.");
            }
            

            // if (Str::contains($e->getMessage(), [
            //         'does not exist','file_get_contents'
            //     ])) {
            //     DB::commit();
            //     ResponseService::warningResponse("Data Stored successfully. But App push notification not send.");
            // } else {
            //     DB::rollBack();
            //     ResponseService::logErrorResponse($e, "Announcement Controller -> Update Method");
            //     ResponseService::errorResponse();
            // }
        }
    }

    public function show() {
        ResponseService::noFeatureThenSendJson('Announcement Management');
        ResponseService::noPermissionThenSendJson('announcement-list');
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'ASC');
        $search = request('search');
        $class_section_id = request('class_section_id');
        $subject_id = request('subject_id');
        $session_year_id = request('session_year_id');
        $sql = $this->announcement->builder()->with('file', 'announcement_class.class_section.class', 'announcement_class.class_section.section', 'announcement_class.class_section.medium' ,'announcement_class.class_subject.subject','session_years_trackings')
            ->where(function ($q) use ($search) {
                $q->when($search, function ($query) use ($search) {
                $query->where('id', 'LIKE', "%$search%")
                    ->orwhere('title', 'LIKE', "%$search%")
                    ->orwhere('description', 'LIKE', "%$search%");
                });
            });
            
        // Filter by class section if provided
        if ($class_section_id) {
            $sql->whereHas('announcement_class', function($q) use ($class_section_id) {
                $q->where('class_section_id', $class_section_id);
            });
        }

        // Filter by subject if provided
        if ($subject_id) {
            $sql->whereHas('announcement_class.class_subject', function($q) use ($subject_id) {
                $q->where('subject_id', $subject_id);
            });
        }
        
        if($session_year_id) {
            $sql = $sql->whereHas('session_years_trackings', function ($q) use ($session_year_id) {
                $q->where('session_year_id', $session_year_id);
            });
        }

        $total = $sql->count();
        $sql->orderBy($sort, $order)->skip($offset)->take($limit);
        $res = $sql->get();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $no = 1;
        $user = Auth::user();
        foreach ($res as $row) {
            $operate = '';
            $class_section = array();
            $class_section_id = array();
            $class_subject_id = '';
            // $class->roles->id == $user->id
            foreach ($row->announcement_class as $index => $class) {
                if ($user->hasRole('School Admin') || !$user->hasRole('Teacher')) {
                    $operate = BootstrapTableService::editButton(route('announcement.update', $row->id));
                    $operate .= BootstrapTableService::deleteButton(route('announcement.destroy', $row->id));
                }

                if (($user->hasRole('School Admin') && ($class->class_subject_id == "" || $class->class_subject_id) ) || ($user->hasRole('Teacher') && $class->class_subject_id ) ) {
                    //Show Edit and Soft Delete Buttons
                    $operate = BootstrapTableService::editButton(route('announcement.update', $row->id));
                    $operate .= BootstrapTableService::deleteButton(route('announcement.destroy', $row->id));
                }
                $class_section_id[] = $class->class_section_id;

                // Add teacher subject
                if ($class->class_subject_id) {
                    $class_subject_id = $class->class_subject_id;
                    $class_section[] = $class->class_section->full_name . ' #' . $class->class_subject->subject->name;
                } else {
                    $class_section[] = $class->class_section->full_name;
                }
            }

            $tempRow = $row->toArray();
            $tempRow['id'] = $row->id;
            $tempRow['no'] = $no++;
            $tempRow['class_subject_id'] = $class_subject_id;
            $tempRow['class_sections'] = $class_section_id;
            $tempRow['assignto'] = $class_section;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function destroy($id) {
        ResponseService::noFeatureThenSendJson('Announcement Management');
        ResponseService::noPermissionThenSendJson('announcement-delete');
        try {
            DB::beginTransaction();
            $this->announcement->deleteById($id);
            $sessionYear = $this->cache->getDefaultSessionYear();
            $semester = $this->cache->getDefaultSemesterData();
            if ($semester) {
                $this->sessionYearsTrackingsService->deleteSessionYearsTracking('App\Models\Announcement', $id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, $semester->id);
            } else {
                $this->sessionYearsTrackingsService->deleteSessionYearsTracking('App\Models\Announcement', $id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, null);
            }
            DB::commit();
            ResponseService::successResponse('Data Deleted Successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, "Announcement Controller -> Destroy Method");
            ResponseService::errorResponse();
        }
    }

    public function fileDelete($id) {
        ResponseService::noFeatureThenSendJson('Announcement Management');
        ResponseService::noPermissionThenSendJson('announcement-delete');
        try {
            DB::beginTransaction();

            // Find the Data by FindByID
            $file = $this->files->findById($id);

            // Delete the file data
            $file->delete();

            DB::commit();
            ResponseService::successResponse('Data Deleted Successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, "Announcement Controller -> fileDelete Method");
            ResponseService::errorResponse();
        }
    }
}
