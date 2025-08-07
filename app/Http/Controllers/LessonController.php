<?php

namespace App\Http\Controllers;

use App\Models\LessonCommon;
use App\Models\LessonTopic;
use App\Repositories\ClassSection\ClassSectionInterface;
use App\Repositories\ClassSubject\ClassSubjectInterface;
use App\Repositories\Files\FilesInterface;
use App\Repositories\Lessons\LessonsInterface;
use App\Repositories\LessonsCommon\LessonsCommonInterface;
use App\Repositories\Semester\SemesterInterface;
use App\Repositories\Student\StudentInterface;
use App\Repositories\Subject\SubjectInterface;
use App\Repositories\SubjectTeacher\SubjectTeacherInterface;
use App\Rules\DynamicMimes;
use App\Rules\MaxFileSize;
use App\Rules\uniqueLessonInClass;
use App\Rules\YouTubeUrl;
use App\Services\BootstrapTableService;
use App\Services\SessionYearsTrackingsService;
use App\Services\CachingService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Repositories\SessionYear\SessionYearInterface;
use Illuminate\Support\Str;
use Throwable;

class LessonController extends Controller {

    private SubjectTeacherInterface $subjectTeacher;
    private ClassSectionInterface $classSection;
    private LessonsInterface $lesson;
    private FilesInterface $files;
    private CachingService $cache;
    private LessonsCommonInterface $lessonCommon;
    private StudentInterface $student;
    private SubjectInterface $subject;
    private ClassSubjectInterface $class_subjects;
    private SessionYearsTrackingsService $sessionYearsTrackingsService;
    private SemesterInterface $semester;
    private SessionYearInterface $sessionYear;

    public function __construct(ClassSectionInterface $classSection, LessonsInterface $lesson, FilesInterface $files, SubjectTeacherInterface $subjectTeacher,CachingService $cache, LessonsCommonInterface $lessonCommon, StudentInterface $student, SubjectInterface $subject, ClassSubjectInterface $class_subjects, SessionYearsTrackingsService $sessionYearsTrackingsService, SemesterInterface $semester, SessionYearInterface $sessionYear)
    {
        $this->subjectTeacher = $subjectTeacher;
        $this->classSection = $classSection;
        $this->lesson = $lesson;
        $this->files = $files;
        $this->cache = $cache;
        $this->lessonCommon = $lessonCommon;
        $this->student = $student;
        $this->subject = $subject;
        $this->class_subjects = $class_subjects;
        $this->sessionYearsTrackingsService = $sessionYearsTrackingsService;
        $this->semester = $semester;
        $this->sessionYear = $sessionYear;
    }

    public function index() {
        ResponseService::noFeatureThenRedirect('Lesson Management');
        ResponseService::noPermissionThenRedirect('lesson-list');
        $class_section = $this->classSection->builder()->with('class', 'class.stream', 'section', 'medium')->get();
        $subjectTeachers = $this->subjectTeacher->builder()->with('subject:id,name,type')->get();
        $lessons = $this->lesson->builder()->get();
        $semesters = $this->semester->builder()->get();
        $sessionYears = $this->sessionYear->all();
        return response(view('lessons.index', compact('class_section', 'subjectTeachers', 'lessons', 'semesters', 'sessionYears')));
    }

    public function store(Request $request) {
        ResponseService::noFeatureThenRedirect('Lesson Management');
        ResponseService::noPermissionThenRedirect('lesson-create');

        $file_upload_size_limit = $this->cache->getSystemSettings('file_upload_size_limit');

        $validator = Validator::make(
            $request->all(),
            [
                'name'                  => 'required',
                'description'           => 'required',
                'class_section_id'      => 'required|array',
                'class_section_id.*'    => 'numeric',
                'subject_id'      => 'required|numeric',
                'file_data'             => 'nullable|array',
                'file_data.*.type'      => 'required|in:file_upload,youtube_link,video_upload,other_link',
                'file_data.*.name'      => 'required_with:file_data.*.type',
                'file_data.*.thumbnail' => 'required_if:file_data.*.type,youtube_link,video_upload,other_link',
        
                'file_data.*.link' => [
                    'nullable',
                    'required_if:file_data.*.type,youtube_link,other_link',
                    new YouTubeUrl, 
                ],
                
                'file_data.*.link' => [
                    'nullable',
                    'required_if:file_data.*.type,other_link',
                    'url',
                    
                ],

                'file_data.*.file' => [
                    'nullable',
                    'required_if:file_data.*.type,file_upload,video_upload',
                    new DynamicMimes(),
                    new MaxFileSize($file_upload_size_limit), // Max file size validation
                ],
            ],
            [
                'file_data.*.file.required_if' => trans('The file field is required when uploading a file.'),
                'file_data.*.file.dynamic_mimes' => trans('The uploaded file type is not allowed.'),
                'file_data.*.file.max_file_size' => trans('The file uploaded must be less than :file_upload_size_limit MB.', [
                    'file_upload_size_limit' => $file_upload_size_limit,
                ]),
                'file_data.*.link.required_if' => trans('The link field is required when the type is YouTube link or Other link.'),
                'file_data.*.link.url' => trans('The provided link must be a valid URL.'),
                'file_data.*.link.youtube_url' => trans('The provided YouTube URL is not valid.'),
                'file_data.*.file.required_if' => trans('The file is required when uploading a video or file.'),
            ]
        );

        if ($validator->fails()) {
            ResponseService::errorResponse($validator->errors()->first());
        }
        try {
            DB::beginTransaction();
        
            $section_ids = is_array($request->class_section_id) ? $request->class_section_id : [$request->class_section_id];
            $lessonFileData = [];
        
            // Prepare file data if available
            if (!empty($request->file_data)) {
                foreach ($request->file_data as $file) {
                    if ($file['type']) {
                        $lessonFileData[] = $this->prepareFileData($file);
                    }
                }
            }
        
            // Prepare lesson data for all section IDs
            $lessonData = [];
            $lessonData = [
                'name' => $request->name,
                'description' => $request->description,
            ];
            
            // Create lesson
            $lesson = $this->lesson->create($lessonData);
            $lessonCommonData = [];
            // Create lesson_common data for each section
            foreach ($section_ids as $section_id) {
                $subjectTeacher = $this->subjectTeacher->builder()->where('class_section_id', $section_id)->where('subject_id', $request->subject_id)->first();
                $lessonCommonData[] = [
                    'lesson_id' => $lesson->id,
                    'class_section_id' => $section_id,
                    'class_subject_id' => $subjectTeacher->class_subject_id,
                ];
            }
            LessonCommon::insert($lessonCommonData);
            // Associate files with the lesson and store them
            if ($lessonFileData) {
                $lessonFile = $this->files->model();
                $lessonModelAssociate = $lessonFile->modal()->associate($lesson); // Get the Association Values of File with Announcement
                foreach ($lessonFileData as &$fileData) {
                    $fileData['modal_type'] = $lessonModelAssociate->modal_type;
                    $fileData['modal_id'] = $lessonModelAssociate->modal_id;
                }
                $this->files->createBulk($lessonFileData);
            }

            // Store Session Years Tracking
            $sessionYear = $this->cache->getDefaultSessionYear();
            $semester = $this->cache->getDefaultSemesterData();
            if ($semester) {
                $this->sessionYearsTrackingsService->storeSessionYearsTracking('App\Models\Lesson', $lesson->id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, $semester->id);
            } else {
                $this->sessionYearsTrackingsService->storeSessionYearsTracking('App\Models\Lesson', $lesson->id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, null);
            }
        
            // Send notification to all students in the class section
            $user = $this->student->builder()->with('user')->where('class_section_id', $request->class_section_id)->pluck('user_id')->toArray();
            $subjectName = $this->subject->builder()->where('id', $request->subject_id)->first();
            DB::commit();
            send_notification($user, 'Lesson Alert !!!', 'New Lesson added for ' . $subjectName->name, 'lesson');
            ResponseService::successResponse('Data Stored Successfully');
        } catch (Throwable $e) {
            
            if (Str::contains($e->getMessage(), ['does not exist', 'file_get_contents'])) {
                DB::commit();
                ResponseService::warningResponse("Data Stored successfully. But App push notification not send.");
            } else {
                DB::rollBack();
                ResponseService::logErrorResponse($e, "Lesson Controller -> Store Method");
                ResponseService::errorResponse();
            }
        }
    }

    private function prepareFileData($file)
    {
        if ($file['type']) {
           
            $tempFileData = [
                'file_name'  => $file['name']
            ];
            // If File Upload
            if ($file['type'] == "file_upload") {
                // Add Type And File Url to TempDataArray and make Thumbnail data null
                $tempFileData['type'] = 1;
                $tempFileData['file_thumbnail'] = null;
                $tempFileData['file_url'] = $file['file'];
            } elseif ($file['type'] == "youtube_link") {

                // Add Type , Thumbnail and Link to TempDataArray
                $tempFileData['type'] = 2;
                $tempFileData['file_thumbnail'] = $file['thumbnail'];
                $tempFileData['file_url'] = $file['link'];
            } elseif ($file['type'] == "video_upload") {

                // Add Type , File Thumbnail and File URL to TempDataArray
                $tempFileData['type'] = 3;
                $tempFileData['file_thumbnail'] = $file['thumbnail'];
                $tempFileData['file_url'] = $file['file'];
            } elseif ($file['type'] == "other_link") {
                // Add Type , File Thumbnail and File URL to TempDataArray
                $tempFileData['type'] = 4;
                $tempFileData['file_thumbnail'] = $file['thumbnail'];
                $tempFileData['file_url'] = $file['link'];
            }
        }

        return $tempFileData;

    }

    public function show()
    {
        ResponseService::noFeatureThenRedirect('Lesson Management');
        ResponseService::noPermissionThenRedirect('lesson-list');
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $search = request('search');
        $semester_id = request('semester_id');

        $sql = $this->lesson->builder()->with('class_subject', 'class_section', 'topic', 'file', 'lesson_commons','session_years_trackings')->where(function ($query) use ($search) {
            $query->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('id', 'LIKE', "%$search%")
                        ->orWhere('name', 'LIKE', "%$search%")
                        ->orWhere('description', 'LIKE', "%$search%")
                        ->orWhere('created_at', 'LIKE', "%" . date('Y-m-d H:i:s', strtotime($search)) . "%")
                        ->orWhere('updated_at', 'LIKE', "%" . date('Y-m-d H:i:s', strtotime($search)) . "%")
                        ->orWhereHas('class_section.section', function ($q) use ($search) {
                            $q->where('name', 'LIKE', "%$search%");
                        })
                        ->orWhereHas('class_section.class', function ($q) use ($search) {
                            $q->where('name', 'LIKE', "%$search%");
                        })
                        ->orWhereHas('class_subject.subject', function ($q) use ($search) {
                            $q->where('name', 'LIKE', "%$search%");
                        });
                });
            });
        });

        if(request('class_id')) {
            $class_id = request('class_id');
            $sql = $sql->whereHas('lesson_commons', function ($q) use ($class_id ) {
                $q->where('class_section_id', $class_id);
            });
        }

        if(request('class_subject_id')) {
            $subject_id = request('class_subject_id');
            $sql = $sql->whereHas('lesson_commons', function ($q) use ($subject_id ) {
                $q->where('class_subject_id', $subject_id);
            });
        }

        if(request('semester_id')) {
            $semester_id = request('semester_id');
            $sql = $sql->whereHas('session_years_trackings', function ($q) use ($semester_id ) {
                $q->where('semester_id', $semester_id);
            });
        }
        if(request('session_year_id')) {
            $sessionYear = request('session_year_id');
            $sql = $sql->whereHas('session_years_trackings', function ($q) use ($sessionYear) {
                $q->where('session_year_id', $sessionYear);
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
            // lesson commons with class section details
            $lessonCommons = $row->lesson_commons->map(function ($common) {
                return $common->class_section ? $common->class_section->full_name : null;
            });
            
            $lessonCommons->filter()->map(function ($name) {
                return "{$name},";
            })->toArray();
            
            
            // dd( $lessonCommons);
            // $operate = BootstrapTableService::button(route('lesson.edit', $row->id), ['btn-gradient-primary'], ['title' => 'Edit'], ['fa fa-edit']);
            $operate = BootstrapTableService::button('fa fa-edit', route('lesson.edit', $row->id), ['btn-gradient-primary'], ['title' => 'Edit']);
            $operate .= BootstrapTableService::deleteButton(route('lesson.destroy', $row->id));

            $tempRow = $row->toArray();
            $tempRow['no'] = $no++;
            $tempRow['class_section_with_medium'] =  $lessonCommons;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function edit($id)
    {
        ResponseService::noFeatureThenRedirect('Lesson Management');
        ResponseService::noPermissionThenRedirect('lesson-edit');
        $class_section = $this->classSection->builder()->with('class', 'class.stream', 'section', 'medium')->get();
        $subjectTeachers = $this->subjectTeacher->builder()->with('subject:id,name,type')->get();
        $lesson = $this->lesson->builder()->with('file','lesson_commons.class_subject')->where('id', $id)->first();
        $lessonCommonClassSections = $lesson->lesson_commons->pluck('class_section_id');
        $subjectId = $lesson->lesson_commons->first()->class_subject->subject_id;

        return response(view('lessons.edit_lesson', compact('class_section', 'lessonCommonClassSections', 'subjectTeachers', 'lesson', 'subjectId')));
    }

    public function update(Request $request, $id)
    {
        ResponseService::noFeatureThenRedirect('Lesson Management');
        ResponseService::noPermissionThenSendJson('lesson-edit');
        $file_upload_size_limit = $this->cache->getSystemSettings('file_upload_size_limit');
        $validator = Validator::make(
            $request->all(),
            [
                'name'             => 'required',
                'description'      => 'required',
                'class_section_id' => 'required|array',
                'class_section_id*' => 'required|numeric',
                'class_subject_id' => 'required|numeric',
                'file_data'        => 'nullable|array',
                'file_data.*.type' => 'required|in:file_upload,youtube_link,video_upload,other_link',
                'file_data.*.name' => 'required_with:file_data.*.type',
                'file_data.*.link' => ['nullable', 'required_if:file_data.*.type,youtube_link', new YouTubeUrl], //Regex for YouTube Link
                'file_data.*.link' => [
                    'nullable',
                    'required_if:file_data.*.type,other_link',
                    'url',
                    
                ],
                'file_data.*.file' => ['nullable', new DynamicMimes, new MaxFileSize($file_upload_size_limit) ],
            ],
            [
                'name.unique' => trans('lesson_already_exists'),
                'file_data.*.file' => trans('The file Uploaded must be less than :file_upload_size_limit MB.', [
                    'file_upload_size_limit' => $file_upload_size_limit,  
                ]),
            ]
        );
        if ($validator->fails()) {
            ResponseService::errorResponse($validator->errors()->first());
        }
        $classSectionIds = is_array($request->class_section_id) ? $request->class_section_id : [$request->class_section_id];
        $data = [
            'name' => $request->name,
            'description' => $request->description,
        ];
        
        try {
            DB::beginTransaction();
            $lesson = $this->lesson->update($id, $data);

            // Delete existing relationships first if you want clean slate
            LessonCommon::where('lesson_id', $id)->delete();

            // Then insert fresh relationships
            $lessonCommonData = [];
            foreach ($classSectionIds as $sectionId) {
                $subjectTeacher = $this->subjectTeacher->builder()->where('class_section_id', $sectionId)->where('subject_id', $request->class_subject_id)->first();
                $lessonCommonData[] = [
                    'lesson_id' => $id,
                    'class_section_id' => $sectionId,
                    'class_subject_id' => $subjectTeacher->class_subject_id,
                ];
            }
            LessonCommon::insert($lessonCommonData);
            //Add the new Files
            if ($request->file_data) {
                // Initialize the Empty Array

                foreach ($request->file_data as $file) {
                    if ($file['type']) {

                        // Create A File Model Instance
                        $lessonFile = $this->files->model();

                        // Get the Association Values of File with Lesson
                        $lessonModelAssociate = $lessonFile->modal()->associate($lesson);

                        // Make custom Array for storing the data in TempFileData
                        $tempFileData = array(
                            'id'         => $file['id'] ?? null,
                            'modal_type' => $lessonModelAssociate->modal_type,
                            'modal_id'   => $lessonModelAssociate->modal_id,
                            'file_name'  => $file['name'],
                        );

                        // If File Upload
                        if ($file['type'] == "file_upload") {

                            // Add Type And File Url to TempDataArray and make Thumbnail data null
                            $tempFileData['type'] = 1;
                            $tempFileData['file_thumbnail'] = null;
                            if (!empty($file['file'])) {
                                $tempFileData['file_url'] = $file['file'];
                            }
                        } elseif ($file['type'] == "youtube_link") {

                            // Add Type , Thumbnail and Link to TempDataArray
                            $tempFileData['type'] = 2;
                            if (!empty($file['thumbnail'])) {
                                $tempFileData['file_thumbnail'] = $file['thumbnail'];
                            }
                            $tempFileData['file_url'] = $file['link'];
                        } elseif ($file['type'] == "video_upload") {

                            // Add Type , File Thumbnail and File URL to TempDataArray
                            $tempFileData['type'] = 3;
                            if (!empty($file['thumbnail'])) {
                                $tempFileData['file_thumbnail'] = $file['thumbnail'];
                            }
                            if (!empty($file['file'])) {
                                $tempFileData['file_url'] = $file['file'];
                            }
                        } elseif ($file['type'] == "other_link") {

                            // Add Type , File Thumbnail and File URL to TempDataArray
                            $tempFileData['type'] = 4;
                            if (!empty($file['thumbnail'])) {
                                $tempFileData['file_thumbnail'] = $file['thumbnail'];
                            }
                            $tempFileData['file_url'] = $file['link'];
                        }
                        $tempFileData['created_at'] = date('Y-m-d H:i:s');
                        $tempFileData['updated_at'] = date('Y-m-d H:i:s');

                        $this->files->updateOrCreate(['id' => $file['id']], $tempFileData);
                    }
                }
            }

            $user = $this->student->builder()->with('user')->where('class_section_id', $request->class_section_id)->pluck('user_id')->toArray();
            $subjectTeacher = $this->subjectTeacher->builder()->with('subject')->whereIn('class_section_id', $request->class_section_id)->where('subject_id', $request->class_subject_id)->first();
            $title = "Lesson Alert !!!";
            $body = 'Lesson Updated for ' . $subjectTeacher->subject->name;
            $type = "lesson";
           
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
                DB::rollBack();
                ResponseService::logErrorResponse($e, "Lesson Controller -> Update Method");
                ResponseService::errorResponse();
            }
        }
    }

    public function destroy($id)
    {
        ResponseService::noFeatureThenRedirect('Lesson Management');
        ResponseService::noPermissionThenSendJson('lesson-delete');
        try {

            $lesson_topics = LessonTopic::where('lesson_id', $id)->count();
            if ($lesson_topics) {
                $response = array('error' => true, 'message' => trans('cannot_delete_because_data_is_associated_with_other_data'));
            } else {

                // Find the Data By ID
                $lesson = $this->lesson->findById($id);

                // If File exists
                if ($lesson->file) {

                    // Loop on the Files
                    foreach ($lesson->file as $file) {

                        // Remove the Files From the Local
                        if (Storage::disk('public')->exists($file->file_url)) {
                            Storage::disk('public')->delete($file->file_url);
                        }
                    }
                }

                // Delete File Data
                $lesson->file()->delete();

                // Delete Lesson Data
                $lesson->delete();

                $sessionYear = $this->cache->getDefaultSessionYear();
                $this->sessionYearsTrackingsService->deleteSessionYearsTracking('App\Models\Lesson', $id, Auth::user()->id, $sessionYear->id, Auth::user()->school_id, null);

                ResponseService::successResponse('Data Deleted Successfully');
            }
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, "Lesson Controller -> Destroy method");
            ResponseService::errorResponse();
        }
        return response()->json($response);
    }


    public function search(Request $request)
    {
        ResponseService::noFeatureThenRedirect('Lesson Management');
        ResponseService::noPermissionThenRedirect('lesson-list');
        try {
            // Get the new Instance of Lesson Model
            $lesson = $this->lesson->model();

            if (isset($request->subject_id)) {
                $lesson = $lesson->where('subject_id', $request->subject_id);
            }

            if (isset($request->class_section_id)) {
                $lesson = $lesson->where('class_section_id', $request->class_section_id);
            }

            $lesson = $lesson->get();

            $response = array(
                'error'   => false,
                'data'    => $lesson,
                'message' => 'Lesson fetched successfully'
            );
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "Lesson Controller -> Search Method");
            ResponseService::errorResponse();
        }
        return response()->json($response);
    }

    public function deleteFile($id)
    {
        ResponseService::noFeatureThenRedirect('Lesson Management');
        ResponseService::noAnyPermissionThenRedirect(['lesson-delete', 'topic-delete']);
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
            ResponseService::logErrorResponse($e, "Lesson Controller -> deleteFile Method");
            ResponseService::errorResponse();
        }
    }
}
