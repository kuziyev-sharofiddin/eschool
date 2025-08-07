<?php

namespace Database\Seeders;

use App\Models\ClassSchool;
use App\Models\ClassSection;
use App\Models\ClassSubject;
use App\Models\Feature;
use App\Models\Mediums;
use App\Models\Package;
use App\Models\PackageFeature;
use App\Models\School;
use App\Models\Section;
use App\Models\SessionYear;
use App\Models\Staff;
use App\Models\Students;
use App\Models\Subject;
use App\Models\User;
use App\Models\Holiday;
use App\Models\Assignment;
use App\Models\Announcement;
use App\Models\Timetable;
use App\Models\OnlineExam;
use App\Models\Exam;
use App\Models\ExamTimetable;
use App\Models\ExamResult;
use App\Models\Fee;
use App\Models\File;
use App\Models\FeesType;
use App\Models\FeeInstallment;
use App\Models\Attendance;
use App\Models\Slider;
use App\Models\Notification;
use App\Models\Leave;
use App\Models\ExpenseCategory;
use App\Models\Expense;
use App\Models\PayrollSetting;
use App\Models\Payroll;
use App\Models\FormField;
use App\Models\Fees;
use App\Models\FeesClassType;
use App\Models\FeesInstallment;
use App\Models\Lesson;
use App\Models\LessonCommon;
use App\Models\LessonTopic;
use App\Models\TopicCommon;
use App\Models\Semester;
use App\Models\AssignmentCommon;
use App\Services\CachingService;
use App\Services\SchoolDataService;
use App\Services\UserService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Faker\Factory as Faker;
use App\Services\SessionYearsTrackingsService;
use App\Models\AnnouncementClass;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DummySampleDataSeeder extends Seeder
{
    private SchoolDataService $schoolService;
    private SessionYearsTrackingsService $sessionYearsTrackingsService;
    private $faker;
    private CachingService $cache;

    public function __construct(SchoolDataService $schoolService, SessionYearsTrackingsService $sessionYearsTrackingsService, CachingService $cache)
    {
        $this->schoolService = $schoolService;
        $this->sessionYearsTrackingsService = $sessionYearsTrackingsService;
        $this->cache = $cache;
        $this->faker = Faker::create();
    }

    public function run()
    {
        try {
            // Create a default school if none exists
            $school = School::firstOrCreate(
                ['id' => 1],
                [
                    'name' => 'Demo School',
                    'address' => '123 Education Street',
                    'support_phone' => '+1234567890',
                    'support_email' => 'support@demoschool.com',
                    'tagline' => 'Education for All',
                    'logo' => 'default-logo.png',
                    'status' => 1,
                    'database_name' => 'school_demo'
                ]
            );

            $schoolId = $school->id;

            // Set up database connection if needed
            if ($school->database_name) {
                Config::set('database.connections.school.database', $school->database_name);
                DB::purge('school');
                DB::connection('school')->reconnect();
                DB::setDefaultConnection('school');
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            try {
                // Seed Session Years
                // $this->seedSessionYears($schoolId);

                // // Seed Custom Fields
                // $this->seedCustomFields($schoolId);

                // // Seed Mediums
                // $this->seedMediums($schoolId);

                // // Seed Streams
                // $this->seedStreams($schoolId);

                // // Seed Shifts
                // $this->seedShifts($schoolId);

                // // Seed Semesters
                // $this->seedSemesters($schoolId);

                // // Seed Sections
                // $this->seedSections($schoolId);

                // // Seed Classes
                // $this->seedClasses($schoolId);

                // // Seed Class Groups
                // $this->seedClassGroups($schoolId);

                // // Seed Class Sections
                // $this->seedClassSections($schoolId);

                // // Seed Subjects
                // $this->seedSubjects($schoolId);

                // // Seed Class Subjects
                // $this->seedClassSubjects($schoolId);

                // // Seed Users
                // $this->seedUsers($schoolId);

                // // Seed Class Teachers
                // $this->seedClassTeachers($schoolId);

                // Seed Students
                $this->seedStudents($schoolId);

                // Seed Admission Inquiries
                // $this->seedAdmissionInquiries($schoolId);

                // // Seed Fee Types
                // $this->seedFeesTypes($schoolId);

                // // Seed Fees
                // $this->seedManageFees($schoolId);

                // // Seed Attendance
                // $this->seedAttendance($schoolId);

                // // Seed Offline Exams
                // $this->seedExams($schoolId);

                // // Seed Online Exams
                // $this->seedOnlineExams($schoolId);

                // // Seed Exam Results
                // $this->seedExamResults($schoolId);

                // // Seed Expense Categories
                // $this->seedExpenseCategories($schoolId);

                // // Seed Expenses
                // $this->seedExpenses($schoolId);

                // // Seed Payroll Settings
                // $this->seedPayrollSettings($schoolId);

                // // Seed Payrolls
                // $this->seedPayrolls($schoolId);

                // // Seed Roles
                // $this->seedStaffRoles($schoolId);

                // // Seed Staff
                // $this->seedStaff($schoolId);

                // // Seed Leaves
                // $this->seedLeaves($schoolId);

                // // Seed Holidays
                // $this->seedHolidays($schoolId);

                // // Seed Notifications
                // $this->seedNotifications($schoolId);

                // // Seed Announcements
                // $this->seedAnnouncements($schoolId);

                // // Seed Gallery
                // $this->seedGallery($schoolId);

                // // Seed Certificates
                // $this->seedCertificates($schoolId);

                // // Seed Timetable
                // $this->seedTimetable($schoolId);

                // // Seed Sliders
                // $this->seedSliders($schoolId);

                // // Seed Lesson
                // $this->seedLesson($schoolId);

                // // Seed Lesson Topic
                // $this->seedLessonTopic($schoolId);

                // // Seed Assignment
                // $this->seedAssignment($schoolId);

                $this->command->info('Sample data seeded successfully!');
            } catch (\Exception $e) {
                $this->command->error('Error seeding data: ' . $e->getMessage());
                throw $e;
            } finally {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
        } catch (\Exception $e) {
            $this->command->error('Error in seeder: ' . $e->getMessage());
            throw $e;
        }
    }

    private function seedStaffRoles($schoolId)
    {
        $roles = [
            'Accountant',
            'Librarian',
            'Receptionist',
            'Principal',
            'Vice Principal',
            'Staff',
        ];

        $permissions = Permission::all();

        foreach ($roles as $role) {
            $role = Role::create(['name' => $role, 'school_id' => $schoolId]);
            $role->syncPermissions($permissions);

            $sessionYear = SessionYear::where('school_id', $schoolId)->where('default', 1)->first();
            $this->sessionYearsTrackingsService->storeSessionYearsTracking('App\Models\Role', $role->id, Auth::user()->id, $sessionYear->id, $schoolId, null);
        }
    }

    private function seedStaff($schoolId)
    {
        $roles = Role::where('school_id', $schoolId)->get();

        for ($i = 1; $i <= 10; $i++) {
            $number = $this->faker->phoneNumber;
            $user = User::create([
                'first_name'         => "Staff " . $i,
                'last_name'          => $this->faker->lastName,
                'email'              => strtolower("staff" . $i) . '@example.com',
                'password'           => Hash::make('1234567890'),
                'gender'             => rand(0, 1) ? 'Male' : 'Female',
                'mobile'             => $number,
                'dob'                => $this->faker->date('Y-m-d', '2000-01-01'),
                'image'              => null,
                'current_address'    => "Current Address " . $i,
                'permanent_address'  => "Permanent Address " . $i,
                'occupation'         => null,
                'status'             => 1,
                'reset_request'      => 0,
                'fcm_id'            => null,
                'school_id'          => $schoolId,
                'language'           => 'en',
                'remember_token'     => null,
                'email_verified_at'  => now(),
                'two_factor_enabled' => 1,
                'two_factor_secret'  => null,
                'two_factor_expires_at' => null,
                'created_at'         => now(),
                'updated_at'         => now(),
                'deleted_at'         => null
            ]);

            foreach ($roles as $role) {
                $user->assignRole($role);
            }
        }
    }


    private function seedSessionYears($schoolId)
    {
        try {
            $sessionYears = [
                [
                    'name' => '2023-2024',
                    'default' => 1,
                    'start_date' => '2023-04-01',
                    'end_date' => '2024-03-31',
                    'school_id' => $schoolId,
                ],
                [
                    'name' => '2024-2025',
                    'default' => 0,
                    'start_date' => '2024-04-01',
                    'end_date' => '2025-03-31',
                    'school_id' => $schoolId,
                ],
            ];

            foreach ($sessionYears as $year) {
                SessionYear::updateOrCreate(
                    [
                        'name' => $year['name'],
                        'school_id' => $schoolId
                    ],
                    array_merge($year, [
                        'created_at' => now(),
                        'updated_at' => now()
                    ])
                );
            }
        } catch (\Exception $e) {
            $this->command->error('Error seeding session years: ' . $e->getMessage());
            throw $e;
        }
    }

    private function seedCustomFields($schoolId)
    {
        $formFields = [
            [
                'name' => 'Student First Name',
                'type' => 'text',
                'is_required' => 0,
                'default_values' => null,
                'school_id' => $schoolId,
                'user_type' => 1,
                'rank' => 1,
                'display_on_id' => 0,
            ],
            [
                'name' => 'Blood group',
                'type' => 'dropdown',
                'is_required' => 0,
                'default_values' => json_encode(["A+","B+","A","B","O","O+"]),
                'school_id' => $schoolId,
                'user_type' => 1,
                'rank' => 2,
                'display_on_id' => 1,
            ],
            [
                'name' => 'Previous School',
                'type' => 'text',
                'is_required' => 0,
                'default_values' => null,
                'school_id' => $schoolId,
                'user_type' => 1,
                'rank' => 3,
                'display_on_id' => 0,
            ],
            [
                'name' => 'Transport Route',
                'type' => 'dropdown',
                'is_required' => 0,
                'default_values' => json_encode(['Route A', 'Route B', 'Route C', 'Route D']),
                'school_id' => $schoolId,
                'user_type' => 1,
                'rank' => 4,
                'display_on_id' => 0,
            ],
            [
                'name' => 'Experience Years',
                'type' => 'number',
                'is_required' => 1,
                'default_values' => null,
                'school_id' => $schoolId,
                'user_type' => 2,
                'rank' => 1,
                'display_on_id' => 0,
            ],
            [
                'name' => 'Additional Qualifications',
                'type' => 'textarea',
                'is_required' => 0,
                'default_values' => null,
                'school_id' => $schoolId,
                'user_type' => 2,
                'rank' => 2,
                'display_on_id' => 0,
            ]
        ];

        foreach ($formFields as $field) {
            DB::table('form_fields')->insert(array_merge($field, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function seedMediums($schoolId)
    {
        Mediums::insert([
            [
                'name' => 'English',
                'school_id' => $schoolId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gujarati',
                'school_id' => $schoolId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Hindi',
                'school_id' => $schoolId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    private function seedStreams($schoolId)
    {
        $streams = [
            'Science',
            'Commerce',
            'Arts',
            'Vocational'
        ];

        foreach ($streams as $stream) {
            DB::table('streams')->insert([
                'name' => $stream,
                'school_id' => $schoolId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    private function seedShifts($schoolId)
    {
        $shifts = [
            [
                'name' => 'Morning Shift',
                'start_time' => '07:30:00',
                'end_time' => '13:30:00'
            ],
            [
                'name' => 'Afternoon Shift',
                'start_time' => '13:00:00',
                'end_time' => '19:00:00'
            ]
        ];

        foreach ($shifts as $shift) {
            DB::table('shifts')->insert(array_merge($shift, [
                'school_id' => $schoolId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    private function seedSemesters($schoolId)
    {
        $semesters = [
            [
                'name' => 'Semester 1',
                'start_month' => 4, // April
                'end_month' => 9,   // September
            ],
            [
                'name' => 'Semester 2',
                'start_month' => 10, // October
                'end_month' => 3,    // March
            ]
        ];

        foreach ($semesters as $semester) {
            DB::table('semesters')->insert(array_merge($semester, [
                'school_id' => $schoolId,
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    private function seedSections($schoolId)
    {
        Section::insert([
            [
                'name' => 'A',
                'school_id' => $schoolId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'B',
                'school_id' => $schoolId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'C',
                'school_id' => $schoolId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    private function seedClasses($schoolId)
    {
        $mediums = Mediums::where('school_id', $schoolId)->pluck('id');
        $shifts = DB::table('shifts')->where('school_id', $schoolId)->pluck('id');
        $streams = DB::table('streams')->where('school_id', $schoolId)->pluck('id');
        
        $classes = [
            ['name' => 'Class 1', 'include_semesters' => 0],
            ['name' => 'Class 2', 'include_semesters' => 0],
            ['name' => 'Class 3', 'include_semesters' => 0],
            ['name' => 'Class 4', 'include_semesters' => 0],
            ['name' => 'Class 5', 'include_semesters' => 0],
            ['name' => 'Class 6', 'include_semesters' => 0],
            ['name' => 'Class 7', 'include_semesters' => 0],
            ['name' => 'Class 8', 'include_semesters' => 0],
            ['name' => 'Class 9', 'include_semesters' => 1],
            ['name' => 'Class 10', 'include_semesters' => 1],
            ['name' => 'Class 11', 'include_semesters' => 1],
            ['name' => 'Class 12', 'include_semesters' => 1],
        ];

        foreach ($mediums as $mediumId) {
            foreach ($classes as $class) {
                ClassSchool::create([
                    'name' => $class['name'],
                    'medium_id' => $mediumId,
                    'shift_id' => $shifts->random(),
                    'stream_id' => $class['include_semesters'] ? $streams->random() : null,
                    'include_semesters' => $class['include_semesters'],
                    'school_id' => $schoolId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedClassGroups($schoolId)
    {
        $groups = [
            'Group A - Science',
            'Group B - Commerce',
            'Group C - Arts',
            'Group D - Vocational'
        ];

        foreach ($groups as $group) {
            DB::table('class_groups')->insert([
                'name' => $group,
                'school_id' => $schoolId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    private function seedClassSections($schoolId)
    {
        $classes = ClassSchool::where('school_id', $schoolId)->get();
        $sections = Section::where('school_id', $schoolId)->get();

        foreach ($classes as $class) {
            foreach ($sections as $section) {
                // Check if combination already exists
                $exists = ClassSection::where([
                    'class_id' => $class->id,
                    'section_id' => $section->id,
                    'medium_id' => $class->medium_id,
                    'school_id' => $schoolId
                ])->exists();

                if (!$exists) {
                    ClassSection::create([
                        'class_id' => $class->id,
                        'section_id' => $section->id,
                        'medium_id' => $class->medium_id,
                        'school_id' => $schoolId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    private function seedSubjects($schoolId)
    {
        $classes = ClassSchool::where('school_id', $schoolId)->get();
        $subjects = [
            [
                'name' => 'Mathematics',
                'type' => 'Compulsory',
                'code' => 'MATH',
                'background_color' => '#000000',
            ],
            [
                'name' => 'Science',
                'type' => 'Compulsory',
                'code' => 'SCI',
                'background_color' => '#000000',
            ],
            [
                'name' => 'English',
                'type' => 'Compulsory',
                'code' => 'ENG',
                'background_color' => '#000000',
            ],
            //elective subjects
            [
                'name' => 'Elective 1',
                'type' => 'Elective',
                'code' => 'ELE',
                'background_color' => '#000000',
            ],
            [
                'name' => 'Elective 2',
                'type' => 'Elective',
                'code' => 'ELE',
                'background_color' => '#000000',
            ],
            
        ];
        
        
        foreach ($classes as $class) {
            foreach ($subjects as $subject) {
                // Check if subject already exists
                $exists = Subject::where([
                    'name' => $subject['name'],
                    'medium_id' => $class->medium_id,
                    'school_id' => $schoolId,
                ])->exists();

                if (!$exists) {
                    Subject::create([
                        'name' => $subject['name'],
                        'code' => $subject['code'],
                        'type' => $subject['type'],
                        'medium_id' => $class->medium_id,
                        'school_id' => $schoolId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // After creating subjects, create class subject mappings
        $this->seedClassSubjects($schoolId);
    }

    private function seedClassSubjects($schoolId)
    {
        try {
            $classes = ClassSchool::where('school_id', $schoolId)->get();
            $subjects = Subject::where('school_id', $schoolId)->get();
            
            foreach ($classes as $class) {
                foreach ($subjects as $subject) {
                    DB::table('class_subjects')->updateOrInsert(
                        [
                            'class_id' => $class->id,
                            'subject_id' => $subject->id,
                            'school_id' => $schoolId,
                        ],
                        [
                            'type' => $subject->type,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            $this->command->error('Error seeding class subjects: ' . $e->getMessage());
            throw $e;
        }
    }

    private function seedUsers($schoolId)
    {
        $faker = Faker::create();
        $sessionYear = SessionYear::where('school_id', $schoolId)->where('default', 1)->first();
    
        for ($i = 1; $i <= 10; $i++) {
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;
            $number = rand(1000000000, 9999999999);
            $userData = [
                'first_name'         => $firstName,
                'last_name'          => $lastName,
                'email'              => strtolower($firstName) . $i . '@example.com',
                'password'           => Hash::make($number),
                'gender'             => $faker->randomElement(['Male', 'Female']),
                'mobile'             => $number,
                'dob'                => $faker->date('Y-m-d', '2000-01-01'),
                'image'              => null,
                'current_address'    => $faker->address,
                'permanent_address'  => $faker->address,
                'occupation'         => null,
                'status'             => 1,
                'reset_request'      => 0,
                'fcm_id'            => null,
                'school_id'          => $schoolId,
                'language'           => 'en',
                'remember_token'     => null,
                'email_verified_at'  => now(),
                'two_factor_enabled' => 1,
                'two_factor_secret'  => null,
                'two_factor_expires_at' => null,
                'created_at'         => now(),
                'updated_at'         => now(),
                'deleted_at'         => null
            ];
    
            $user = User::create($userData);
            $user->assignRole('Teacher');

            // Add leave permissions
            $leave_permission = [
                'leave-list',
                'leave-create',
                'leave-edit',
                'leave-delete',
            ];
            $user->givePermissionTo($leave_permission);

            // Create staff record
            Staff::create([
                'user_id'       => $user->id,
                'qualification' => $faker->randomElement(['B.Ed', 'M.Ed', 'PhD', 'M.A, B.Ed', 'M.Com, B.Ed', 'B.Sc, B.Ed']),
                'salary'        => $faker->numberBetween(30000, 80000),
                'joining_date'  => $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                'session_year_id' => $sessionYear->id,
                'join_session_year_id' => $sessionYear->id,
                'leave_session_year_id' => null,
                'created_at'    => now(),
                'updated_at'    => now()
            ]);
        }
    }

    private function seedClassTeachers($schoolId)
    {
        // Get all class sections
        $classSections = ClassSection::where('school_id', $schoolId)->get();
        
        // Get all teachers
        $teachers = User::where('school_id', $schoolId)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Teacher');
            })
            ->get();

        if ($teachers->isEmpty()) {
            return;
        }

        foreach ($classSections as $classSection) {
            // Randomly assign 1-2 class teachers per section
            $classTeachers = $teachers->random(rand(1, 2));

            foreach ($classTeachers as $teacher) {
                // Create class teacher assignment
                DB::table('class_teachers')->insert([
                    'class_section_id' => $classSection->id,
                    'teacher_id' => $teacher->id,
                    'school_id' => $schoolId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Get subjects for this class
            $classSubjects = DB::table('class_subjects')
                ->where('class_id', $classSection->class_id)
                ->where('school_id', $schoolId)
                ->get();

            foreach ($classSubjects as $classSubject) {
                // Randomly assign 1-2 teachers per subject
                $subjectTeachers = $teachers->random(rand(1, 2));
                
                foreach ($subjectTeachers as $teacher) {
                    // Create subject teacher assignment
                    DB::table('subject_teachers')->insert([
                        'class_section_id' => $classSection->id,
                        'subject_id' => $classSubject->subject_id,
                        'teacher_id' => $teacher->id,
                        'class_subject_id' => $classSubject->id,
                        'school_id' => $schoolId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
    }

    private function seedStudents($schoolId)
    {
        $classSections = ClassSection::where('school_id', $schoolId)->get();
        $sessionYear = SessionYear::where('school_id', $schoolId)->where('default', 1)->first();
        
        foreach ($classSections as $classSection) {
            for ($i = 1; $i <= 10; $i++) {
               
                // Create student with admission details
                $userService = app(UserService::class);
                $sessionYear = SessionYear::where('school_id', $schoolId)->where('default', 1)->first();

                $get_student = Students::latest('id')->withTrashed()->pluck('id')->first();
                $admission_no = $sessionYear->name .'0'. $schoolId . '0' . ($get_student + 1);

                $guardian = $userService->createOrUpdateParent('Guardian '.$i, 'Guardian '.$i, "guardian{$i}_{$classSection->id}@example.com", rand(1000000000, 9999999999), rand(1, 2) == 1 ? 'Male' : 'Female', null);
                $student = $userService->createStudentUser("Student ".$i, "Student ".$i, $admission_no, rand(1000000000, 9999999999), date('Y-m-d', strtotime('-10 years')), rand(1, 2) == 1 ? 'Male' : 'Female', null, $classSection->id, now()->subMonths(rand(1, 6)), 'Test Address', 'Test Permanent Address', $sessionYear->id, $guardian->id, [], 1, false);

                // if($student){
                //     // Add custom field data
                //     $this->addStudentCustomFieldData($student->id, $schoolId);
                // }
            }
        }
    }

    private function seedAdmissionInquiries($schoolId)
    {
        $classSections = ClassSection::where('school_id', $schoolId)->get();
        
        foreach ($classSections as $classSection) {
            for ($i = 1; $i <= 10; $i++) {
                $password = rand(1000000000, 9999999999);

                $parent = array(
                    'first_name' => "Guardian ".$i,
                    'last_name'  => "Guardian ".$i,
                    'mobile'     => rand(1000000000, 9999999999),
                    'gender'     => rand(1, 2) == 1 ? 'Male' : 'Female',
                    'school_id'  => $schoolId
                );
        
                //NOTE : This line will return the old values if the user is already exists
                $user = User::where('email', "guardian{$i}_{$classSection->id}@example.com")->first();
                
                if (!empty($user)) {
                    $user->assignRole('Guardian');
                    $user->update($parent);
                } else {
                    $parent['password'] = Hash::make($password);
                    $user = User::create($parent);
                    $user->assignRole('Guardian');
                }

                $student = Students::create([
                    'user_id' => $user->id,
                    'class_section_id' => $classSection->id,
                    'admission_no' => $admission_no,
                    'roll_number' => null,
                    'admission_date' => now(),
                    'guardian_id' => $user->id,
                    'session_year_id' => $sessionYear->id,
                    'class_id' => $classSection->class_id ?? null,
                    'application_type' => "online",
                    'application_status' => 0,
                    'school_id' => $schoolId,
                ]);
            }

        }
    }

    private function addStudentCustomFieldData($studentId, $schoolId)
    {
        $customFields = FormField::where('school_id', $schoolId)->where('user_type', 1)->get();
        dd($customFields);
        foreach ($customFields as $field) {
            $value = '';
            
            switch ($field->type) {
                case 'text':
                    $value = $faker->name;
                    break;

                case 'dropdown':
                    // Parse the default_values JSON string to array
                    $options = json_decode($field->default_values, true);
                    if (!empty($options)) {
                        $value = $options[array_rand($options)];
                    }
                    break;

                case 'number':
                    if (str_contains(strtolower($field->name), 'year')) {
                        $value = $faker->numberBetween(1, 10);
                    } else {
                        $value = $faker->numberBetween(1, 100);
                    }
                    break;

                case 'textarea':
                    $value = $faker->paragraph(1);
                    break;

                case 'date':
                    $value = $faker->date();
                    break;

                case 'checkbox':
                    $value = $faker->boolean ? '1' : '0';
                    break;

                case 'radio':
                    $options = json_decode($field->default_values, true);
                    if (!empty($options)) {
                        $value = $options[array_rand($options)];
                    }
                    break;

                default:
                    $value = $faker->word;
            }

            // Insert the custom field value
            DB::table('')->insert([
                'form_field_id' => $field->id,
                'field_value' => $value,
                'model_type' => 'App\\Models\\Students',
                'model_id' => $studentId,
                'school_id' => $schoolId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedFeesTypes($schoolId)
    {
        try {
            $sessionYear = SessionYear::where('school_id', $schoolId)
                ->where('default', 1)
                ->first();

            if (!$sessionYear) {
                throw new \Exception('Default session year not found');
            }

            $feeTypes = [
                [
                    'name' => 'Tuition Fee',
                    'description' => 'Monthly tuition fee',
                    'school_id' => $schoolId
                ],
                [
                    'name' => 'Development Fee',
                    'description' => 'Annual development charges',
                    'school_id' => $schoolId
                ],
                [
                    'name' => 'Library Fee',
                    'description' => 'Annual library charges',
                    'school_id' => $schoolId
                ],
                [
                    'name' => 'Computer Lab Fee',
                    'description' => 'Computer laboratory charges',
                    'school_id' => $schoolId
                ],
                [
                    'name' => 'Sports Fee',
                    'description' => 'Sports and physical education charges',
                    'school_id' => $schoolId
                ]
            ];

            foreach ($feeTypes as $feeType) {
                $type = FeesType::updateOrCreate(
                    [
                        'name' => $feeType['name'],
                        'school_id' => $schoolId
                    ],
                    array_merge($feeType, [
                        'created_at' => now(),
                        'updated_at' => now()
                    ])
                );

                // Create tracking record
                $this->sessionYearsTrackingsService->storeSessionYearsTracking(
                    'App\\Models\\FeesType',
                    $type->id,
                    Auth::id() ?? 1,
                    $sessionYear->id,
                    $schoolId,
                    null
                );
            }
        } catch (\Exception $e) {
            $this->command->error('Error seeding fee types: ' . $e->getMessage());
            throw $e;
        }
    }

    private function seedManageFees($schoolId)
    {
        try {
            // Get required data
            $sessionYear = SessionYear::where('school_id', $schoolId)
                ->where('default', 1)
                ->first();

            if (!$sessionYear) {
                throw new \Exception('Default session year not found');
            }

            $semester = Semester::where('school_id', $schoolId)->first();
            
            // Get all classes
            $classes = ClassSchool::where('school_id', $schoolId)->get();
            
            // Get or create school admin
            $schoolAdmin = User::where('school_id', $schoolId)
                ->whereHas('roles', function($q) {
                    $q->where('name', 'School Admin');
                })
                ->first();

            if (!$schoolAdmin) {
                $schoolAdmin = User::create([
                    'first_name' => 'School',
                    'last_name' => 'Admin',
                    'email' => 'admin_' . $schoolId . '@school.com',
                    'password' => Hash::make('password'),
                    'school_id' => $schoolId
                ]);
                $schoolAdmin->assignRole('School Admin');
            }

            // Get fee types
            $feeTypes = FeesType::where('school_id', $schoolId)->get();
            if ($feeTypes->isEmpty()) {
                throw new \Exception('No fee types found. Please run seedFeesTypes first.');
            }

            foreach ($classes as $class) {
                // Create a fee record for each class
                $feeName = "School Fees - " . $class->name;
                
                $fee = Fee::updateOrCreate(
                    [
                        'name' => $feeName,
                        'class_id' => $class->id,
                        'school_id' => $schoolId,
                        'session_year_id' => $sessionYear->id
                    ],
                    [
                        'due_date' => now()->addMonths(1),
                        'due_charges' => 100.00,
                        'due_charges_amount' => 0.00,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );

                // Create tracking record
                $this->sessionYearsTrackingsService->storeSessionYearsTracking(
                    'App\\Models\\Fee',
                    $fee->id,
                    $schoolAdmin->id,
                    $sessionYear->id,
                    $schoolId,
                    $semester ? $semester->id : null
                );

                // Create fee class types
                foreach ($feeTypes as $index => $feeType) {
                    FeesClassType::updateOrCreate(
                        [
                            'fees_id' => $fee->id,
                            'class_id' => $class->id,
                            'fees_type_id' => $feeType->id,
                        ],
                        [
                            'amount' => rand(1000, 5000),
                            'optional' => $index === count($feeTypes) - 1 ? 1 : 0 // Make last fee type optional
                        ]
                    );
                }

                // Create installments
                $installments = [
                    [
                        'name' => 'First Installment',
                        'due_date' => now()->addMonth(),
                        'amount' => 3000
                    ],
                    [
                        'name' => 'Second Installment',
                        'due_date' => now()->addMonths(2),
                        'amount' => 3000
                    ],
                    [
                        'name' => 'Final Installment',
                        'due_date' => now()->addMonths(3),
                        'amount' => 2000
                    ]
                ];

                foreach ($installments as $installment) {
                    FeeInstallment::updateOrCreate(
                        [
                            'name' => $installment['name'],
                            'fees_id' => $fee->id,
                            'session_year_id' => $sessionYear->id
                        ],
                        [
                            'due_date' => $installment['due_date'],
                            'due_charges_type' => 'fixed',
                            'due_charges' => 100,
                            'installment_amount' => $installment['amount'],
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                }
            }

        } catch (\Exception $e) {
            $this->command->error('Error seeding fees: ' . $e->getMessage());
            throw $e;
        }
    }

    private function seedAttendance($schoolId)
    {
        $students = Students::where('school_id', $schoolId)->get();
        $sessionYear = SessionYear::where('school_id', $schoolId)->where('default', 1)->first();
        
        // Generate attendance for last 30 days
        for ($i = 30; $i >= 0; $i--) {
            $date = now()->subDays($i);
            
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($students as $student) {
                // 90% chance of present
                $status = $this->faker->randomElement(['present', 'present', 'present', 'present', 'present', 'present', 'present', 'present', 'present', 'absent']);
                
                Attendance::create([
                    'student_id' => $student->id,
                    'class_section_id' => $student->class_section_id,
                    'session_year_id' => $sessionYear->id,
                    'date' => $date,
                    'status' => $status,
                    'school_id' => $schoolId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedExams($schoolId)
    {
        $sessionYear = SessionYear::where('school_id', $schoolId)->where('default', 1)->first();
        $classSections = ClassSection::where('school_id', $schoolId)->get();

        $examTypes = [
            ['name' => 'First Term Examination', 'start_date' => '2024-09-15', 'end_date' => '2024-09-30'],
            ['name' => 'Mid Term Examination', 'start_date' => '2024-12-15', 'end_date' => '2024-12-30'],
            ['name' => 'Final Term Examination', 'start_date' => '2025-03-15', 'end_date' => '2025-03-30'],
        ];

        foreach ($examTypes as $examType) {
            foreach ($classSections as $classSection) {
                $exam = Exam::create([
                    'name' => $examType['name'],
                    'description' => 'Regular term examination',
                    'class_section_id' => $classSection->id,
                    'session_year_id' => $sessionYear->id,
                    'start_date' => $examType['start_date'],
                    'end_date' => $examType['end_date'],
                    'school_id' => $schoolId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Create exam timetable
                $subjects = Subject::where('class_id', $classSection->class_id)->get();
                $examDate = Carbon::parse($examType['start_date']);

                foreach ($subjects as $subject) {
                    if ($examDate->isWeekend()) {
                        $examDate->addDays(2);
                    }

                    ExamTimetable::create([
                        'exam_id' => $exam->id,
                        'subject_id' => $subject->id,
                        'date' => $examDate->format('Y-m-d'),
                        'start_time' => '09:00:00',
                        'end_time' => '12:00:00',
                        'school_id' => $schoolId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $examDate->addDay();
                }
            }
        }
    }

    private function seedExamResults($schoolId)
    {
        $exams = Exam::where('school_id', $schoolId)->get();
        
        foreach ($exams as $exam) {
            $students = Students::where('class_section_id', $exam->class_section_id)->get();
            $examTimetables = ExamTimetable::where('exam_id', $exam->id)->get();

            foreach ($students as $student) {
                foreach ($examTimetables as $timetable) {
                    ExamResult::create([
                        'exam_id' => $exam->id,
                        'student_id' => $student->id,
                        'subject_id' => $timetable->subject_id,
                        'marks' => $this->faker->numberBetween(60, 98),
                        'grade' => 'A',
                        'school_id' => $schoolId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    private function seedExpenseCategories($schoolId)
    {
        try {
            $sessionYear = SessionYear::where('school_id', $schoolId)
                ->where('default', 1)
                ->first();

            if (!$sessionYear) {
                throw new \Exception('Default session year not found');
            }

            // Get or create school admin for tracking
            $schoolAdmin = User::where('school_id', $schoolId)
                ->whereHas('roles', function($q) {
                    $q->where('name', 'School Admin');
                })
                ->first();

            if (!$schoolAdmin) {
                $schoolAdmin = User::create([
                    'first_name' => 'School',
                    'last_name' => 'Admin',
                    'email' => 'admin_' . $schoolId . '@school.com',
                    'password' => Hash::make('password'),
                    'school_id' => $schoolId
                ]);
                $schoolAdmin->assignRole('School Admin');
            }

            $expenseCategories = [
                [
                    'name' => 'Utilities',
                    'description' => 'Electricity, water, internet bills',
                    'school_id' => $schoolId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Maintenance',
                    'description' => 'Building and equipment maintenance',
                    'school_id' => $schoolId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Supplies',
                    'description' => 'Office and educational supplies',
                    'school_id' => $schoolId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Events',
                    'description' => 'School events and functions',
                    'school_id' => $schoolId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ];

            foreach ($expenseCategories as $categoryData) {
                $expenseCategory = ExpenseCategory::create($categoryData);
                
                // Create tracking record
                $this->sessionYearsTrackingsService->storeSessionYearsTracking(
                    'App\\Models\\ExpenseCategory',
                    $expenseCategory->id,
                    $schoolAdmin->id,
                    $sessionYear->id,
                    $schoolId,
                    null
                );
            }
        } catch (\Exception $e) {
            \Log::error('Error seeding expense categories: ' . $e->getMessage());
            throw $e;
        }
    }

    private function seedExpenses($schoolId)
    {
        try {
            $categories = ExpenseCategory::where('school_id', $schoolId)->get();
            $sessionYear = SessionYear::where('school_id', $schoolId)
                ->where('default', 1)
                ->first();

            if (!$sessionYear) {
                throw new \Exception('Default session year not found');
            }

            // Get or create school admin for tracking
            $schoolAdmin = User::where('school_id', $schoolId)
                ->whereHas('roles', function($q) {
                    $q->where('name', 'School Admin');
                })
                ->first();

            if (!$schoolAdmin) {
                $schoolAdmin = User::create([
                    'first_name' => 'School',
                    'last_name' => 'Admin',
                    'email' => 'admin_' . $schoolId . '@school.com',
                    'password' => Hash::make('password'),
                    'school_id' => $schoolId
                ]);
                $schoolAdmin->assignRole('School Admin');
            }
            
            foreach ($categories as $category) {
                for ($i = 1; $i <= 10; $i++) {
                    $expense = Expense::create([
                        'title' => $this->faker->sentence(3),
                        'category_id' => $category->id,
                        'ref_no' => $this->faker->unique()->regexify('[A-Z]{2}[0-9]{4}'),
                        'staff_id' => null,
                        'basic_salary' => 0,
                        'paid_leaves' => 0.00,
                        'month' => now()->month,
                        'year' => now()->year,
                        'description' => $this->faker->paragraph(1),
                        'amount' => $this->faker->randomFloat(2, 5000, 50000),
                        'date' => $this->faker->dateTimeBetween('-6 months', 'now'),
                        'school_id' => $schoolId,
                        'session_year_id' => $sessionYear->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    // Create tracking record
                    $this->sessionYearsTrackingsService->storeSessionYearsTracking(
                        'App\\Models\\Expense',
                        $expense->id,
                        $schoolAdmin->id,
                        $sessionYear->id,
                        $schoolId,
                        null
                    );
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error seeding expenses: ' . $e->getMessage());
            throw $e;
        }
    }

    private function seedPayrollSettings($schoolId)
    {
        try {
            // Get required data
            $sessionYear = SessionYear::where('school_id', $schoolId)
                ->where('default', 1)
                ->first();

            if (!$sessionYear) {
                throw new \Exception('Default session year not found');
            }

            // Get or create school admin for tracking
            $schoolAdmin = User::where('school_id', $schoolId)
                ->whereHas('roles', function($q) {
                    $q->where('name', 'School Admin');
                })
                ->first();

            if (!$schoolAdmin) {
                $schoolAdmin = User::create([
                    'first_name' => 'School',
                    'last_name' => 'Admin',
                    'email' => 'admin_' . $schoolId . '@school.com',
                    'password' => Hash::make('password'),
                    'school_id' => $schoolId
                ]);
                $schoolAdmin->assignRole('School Admin');
            }

            $payrollSettingList = [
                [
                    'name' => 'Basic Salary',
                    'amount' => 10000,
                    'percentage' => null,
                    'type' => 'allowance',
                    'school_id' => $schoolId,
                ],
                [
                    'name' => 'HRA', 
                    'amount' => 1000,
                    'percentage' => null,
                    'type' => 'allowance',
                    'school_id' => $schoolId,
                ],
                [
                    'name' => 'DA',
                    'amount' => null, 
                    'percentage' => 15,
                    'type' => 'allowance',
                    'school_id' => $schoolId,
                ],
                [
                    'name' => 'Medical Allowance',
                    'amount' => null,
                    'percentage' => 8,
                    'type' => 'allowance', 
                    'school_id' => $schoolId,
                ],
                [
                    'name' => 'Professional Tax',
                    'amount' => 200,
                    'percentage' => null,
                    'type' => 'deduction',
                    'school_id' => $schoolId,
                ],
                [
                    'name' => 'PF (Provident Fund)',
                    'amount' => null,
                    'percentage' => 12,
                    'type' => 'deduction',
                    'school_id' => $schoolId,
                ],
                [
                    'name' => 'Income Tax',
                    'amount' => null,
                    'percentage' => 10,
                    'type' => 'deduction',
                    'school_id' => $schoolId,
                ]
            ];
            

            foreach ($payrollSettingList as $payrollSetting) {
                $payrollSetting = PayrollSetting::create($payrollSetting);
                $sessionYear = SessionYear::where('school_id', $schoolId)->where('default', 1)->first();
                $this->sessionYearsTrackingsService->storeSessionYearsTracking('App\Models\PayrollSetting', $payrollSetting->id, $schoolAdmin->id, $sessionYear->id, $schoolId, null);
            }

        } catch (\Exception $e) {
            \Log::error('Error seeding payroll settings: ' . $e->getMessage());
            throw $e;
        }
    }

    private function seedPayrolls($schoolId)
    {
        $staff = Staff::where('school_id', $schoolId)->where('role', 'teacher')->orWhere('role', 'Staff')->get();
        $settings = PayrollSetting::where('school_id', $schoolId)->first();
        
        foreach ($staff as $employee) {
            $basicSalary = $employee->salary;
            $hra = ($basicSalary * $settings->hra_percentage) / 100;
            $da = ($basicSalary * $settings->da_percentage) / 100;
            $pf = ($basicSalary * $settings->pf_percentage) / 100;
            $medical = ($basicSalary * $settings->medical_allowance_percentage) / 100;

            Payroll::create([
                'staff_id' => $employee->id,
                'month' => now()->format('m'),
                'year' => now()->format('Y'),
                'basic_salary' => $basicSalary,
                'hra' => $hra,
                'da' => $da,
                'pf' => $pf,
                'medical_allowance' => $medical,
                'net_salary' => $basicSalary + $hra + $da - $pf + $medical,
                'status' => 'paid',
                'school_id' => $schoolId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedLeaves($schoolId)
    {
        $staff = Staff::where('school_id', $schoolId)->get();
        $leaveTypes = ['Sick Leave', 'Casual Leave', 'Emergency Leave'];
        
        foreach ($staff as $employee) {
            for ($i = 1; $i <= 3; $i++) {
                $startDate = $this->faker->dateTimeBetween('-3 months', '+1 month');
                $endDate = Carbon::parse($startDate)->addDays($this->faker->numberBetween(1, 3));
                
                Leave::create([
                    'staff_id' => $employee->id,
                    'type' => $this->faker->randomElement($leaveTypes),
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'reason' => $this->faker->sentence(),
                    'status' => $this->faker->randomElement(['approved', 'pending', 'rejected']),
                    'school_id' => $schoolId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedHolidays($schoolId)
    {
        $sessionYear = SessionYear::where('school_id', $schoolId)->where('default', 1)->first();
        
        $startDate = Carbon::parse($sessionYear->start_date);
        $endDate = Carbon::parse($sessionYear->end_date);

        Holiday::insert([
            [
                'date' => $startDate->copy()->addDays(rand(1, 30))->format('Y-m-d'),
                'title' => 'Republic Day',
                'description' => 'National Holiday',
                'school_id' => $schoolId,
            ],
            [
                'date' => $startDate->copy()->addDays(rand(31, 60))->format('Y-m-d'), 
                'title' => 'Independence Day',
                'description' => 'National Holiday',
                'school_id' => $schoolId,
            ],
            [
                'date' => $startDate->copy()->addDays(rand(61, 90))->format('Y-m-d'),
                'title' => 'Gandhi Jayanti', 
                'description' => 'National Holiday',
                'school_id' => $schoolId,
            ],
            [
                'date' => $startDate->copy()->addDays(rand(91, 120))->format('Y-m-d'),
                'title' => 'Christmas', 
                'description' => 'National Holiday',
                'school_id' => $schoolId,
            ],
            [
                'date' => $startDate->copy()->addDays(rand(121, 150))->format('Y-m-d'),
                'title' => 'New Year', 
                'description' => 'National Holiday',
                'school_id' => $schoolId,
            ],
            [
                'date' => $startDate->copy()->addDays(rand(151, 180))->format('Y-m-d'),
                'title' => 'Holi', 
                'description' => 'National Holiday',
                'school_id' => $schoolId,
            ],
            [
                'date' => $startDate->copy()->addDays(rand(181, 210))->format('Y-m-d'),
                'title' => 'Baisakhi', 
                'description' => 'National Holiday',
                'school_id' => $schoolId,
            ]
        ]);
    }

    private function seedNotifications($schoolId)
    {
        $sessionYear = SessionYear::where('school_id', $schoolId)->where('default', 1)->first();

        $users = User::where('school_id', $schoolId)->get();
        $notifications = [
            [
                'title' => 'Welcome to the New Session',
                'message' => 'Get ready for an exciting academic year ahead!',
                'image' => null,
                'send_to' => 'Guardian',
                'session_year_id' => $sessionYear->id,
                'school_id' => $schoolId,
            ],
            [
                'title' => 'Parent-Teacher Meeting',
                'message' => 'A PTM is scheduled for this Friday at 10 AM.',
                'image' => null,
                'send_to' => 'Guardian',
                'session_year_id' => $sessionYear->id,
                'school_id' => $schoolId,
            ],
            [
                'title' => 'Holiday Notice',
                'message' => 'School will remain closed on Monday due to public holiday.',
                'image' => null,
                'send_to' => 'Guardian',
                'session_year_id' => $sessionYear->id,
                'school_id' => $schoolId,
            ],
            [
                'title' => 'Exam Schedule Released',
                'message' => 'The mid-term exam schedule has been uploaded.',
                'image' => null,
                'send_to' => 'Student',
                'session_year_id' => $sessionYear->id,
                'school_id' => $schoolId,
            ],
            [
                'title' => 'Library Week',
                'message' => 'Join us for Library Week and discover amazing books!',
                'image' => null,
                'send_to' => 'Student',
                'session_year_id' => $sessionYear->id,
                'school_id' => $schoolId,
            ],
            [
                'title' => 'Science Fair',
                'message' => 'Students from all classes are invited to participate in the science fair.',
                'image' => null,
                'send_to' => 'Student',
                'session_year_id' => $sessionYear->id,
                'school_id' => $schoolId,
            ],
            [
                'title' => 'Fee Reminder',
                'message' => 'Please pay your fees before the 10th of this month to avoid late charges.',
                'image' => null,
                'send_to' => 'Guardian',
                'session_year_id' => $sessionYear->id,
                'school_id' => $schoolId,
            ],
            [
                'title' => 'New Uniform Guidelines',
                'message' => 'Please refer to the updated uniform policy effective from next week.',
                'image' => null,
                'send_to' => 'Guardian',
                'session_year_id' => $sessionYear->id,
                'school_id' => $schoolId,
            ],
            [
                'title' => 'Annual Sports Meet',
                'message' => 'Cheer for your house! Annual sports meet is on 25th Nov.',
                'image' => null,
                'send_to' => 'Guardian',
                'session_year_id' => $sessionYear->id,
                'school_id' => $schoolId,
            ],
            [
                'title' => 'Mobile App Update',
                'message' => 'A new version of the school mobile app is now available.',
                'image' => null,
                'send_to' => 'Guardian',
                'session_year_id' => $sessionYear->id,
                'school_id' => $schoolId,
            ],

        ];

        foreach ($notifications as $notification) {
            $notification = Notification::create($notification);
            $this->sessionYearsTrackingsService->storeSessionYearsTracking('App\Models\Notification', $notification->id, $users->random()->id, $sessionYear->id, $schoolId, null);
        }
    }

    private function seedAssignment($schoolId)
    {
        try {
            // Get required data
            $sessionYear = SessionYear::where('school_id', $schoolId)
                ->where('default', 1)
                ->first();

            if (!$sessionYear) {
                throw new \Exception('Default session year not found');
            }

            // Get a teacher for assignment creation
            $teachers = User::where('school_id', $schoolId)
                ->whereHas('roles', function($q) {
                    $q->where('name', 'Teacher');
                })
                ->get();

            // Get all class sections
            $classSections = ClassSection::where('school_id', $schoolId)->get();

            foreach ($classSections as $classSection) {
                // Get subjects for this class
                $classSubjects = DB::table('class_subjects')
                    ->where('class_id', $classSection->class_id)
                    ->where('school_id', $schoolId)
                    ->get();

                foreach ($classSubjects as $classSubject) {
                    foreach ($teachers as $teacher) {
                    // Create assignment data
                        $assignmentData = [
                            'class_section_id' => $classSection->id,
                            'class_subject_id' => $classSubject->id,
                            'name' => "Assignment - " . $classSubject->subject_id,
                            'instructions' => "Please complete all questions",
                            'due_date' => now()->addDays(7),
                            'points' => 100,
                            'resubmission' => false,
                            'extra_days_for_resubmission' => null,
                            'session_year_id' => $sessionYear->id,
                            'school_id' => $schoolId,
                            'created_by' => $teacher->id, // Use teacher ID
                            'edited_by' => $teacher->id  // Use teacher ID
                        ];

                        // Create the assignment
                        $assignment = Assignment::create($assignmentData);

                        // Create assignment common data
                        $assignmentCommon = AssignmentCommon::create([
                            'assignment_id' => $assignment->id,
                            'class_section_id' => $classSection->id,
                            'class_subject_id' => $classSubject->id,
                        ]);

                        // Get semester data from cache
                        $sessionYear = $this->cache->getDefaultSessionYear();
                        $semester = $this->cache->getDefaultSemesterData();
                        if ($semester) {
                            $this->sessionYearsTrackingsService->storeSessionYearsTracking('App\Models\Assignment', $assignment->id, $teacher->id, $sessionYear->id, $schoolId, $semester->id);
                        } else {
                            $this->sessionYearsTrackingsService->storeSessionYearsTracking('App\Models\Assignment', $assignment->id, $teacher->id, $sessionYear->id, $schoolId, null);
                        }
                    }
                }
            }

            // Return teacher credentials for reference
            return [
                'email' => $teacher->email,
                'password' => '1234567890'
            ];

        } catch (\Exception $e) {
            \Log::error('Error seeding assignments: ' . $e->getMessage());
            throw $e;
        }
    }

    private function seedAnnouncements($schoolId)
    {
        $sessionYear = SessionYear::where('school_id', $schoolId)->where('default', 1)->first();
        
        // Get the first semester instead of looking for default
        $semester = Semester::where('school_id', $schoolId)->first();
        
        $section_ids = ClassSection::where('school_id', $schoolId)->get()->pluck('id');
        
        // Get a school admin user for creating announcements
        $schoolAdmin = User::where('school_id', $schoolId)
            ->whereHas('roles', function($q) {
                $q->where('name', 'School Admin');
            })
            ->first();

        if (!$schoolAdmin) {
            // Create a school admin if none exists
            $schoolAdmin = User::create([
                'first_name' => 'School',
                'last_name' => 'Admin',
                'email' => 'admin_' . $schoolId . '@school.com',
                'password' => Hash::make('password'),
                'school_id' => $schoolId
            ]);
            $schoolAdmin->assignRole('School Admin');
        }

        $announcements = array(
            array(
                'title' => 'School Annual Day',
                'description' => 'Annual day celebration will be held next month.',
                'session_year_id' => $sessionYear->id,
                'school_id' => $schoolId
            ),
            array(
                'title' => 'Parent Teacher Meeting',
                'description' => 'PTM scheduled for next weekend. Please do not miss it!',
                'session_year_id' => $sessionYear->id,
                'school_id' => $schoolId
            ),
            array(
                'title' => 'Winter Break Notice',
                'description' => 'School will remain closed from Dec 24th to Jan 2nd for winter holidays.',
                'session_year_id' => $sessionYear->id,
                'school_id' => $schoolId
            ),
            array(
                'title' => 'Exam Timetable Released',
                'description' => 'The final exam timetable is now available in the app and website.',
                'session_year_id' => $sessionYear->id,
                'school_id' => $schoolId
            ),
            array(
                'title' => 'Science Fair Registration',
                'description' => 'Registrations are open for the inter-school science fair!',
                'session_year_id' => $sessionYear->id,
                'school_id' => $schoolId
            )
        );

        foreach ($announcements as $announcementData) {
            try {
                // Create the announcement
                $announcement = Announcement::create($announcementData);

                // Create tracking record
                $this->sessionYearsTrackingsService->storeSessionYearsTracking(
                    'App\\Models\\Announcement',
                    $announcement->id,
                    $schoolAdmin->id,
                    $sessionYear->id,
                    $schoolId,
                    $semester ? $semester->id : null
                );

                // Create announcement class associations for all sections
                foreach ($section_ids as $section_id) {
                    $announcementClassData = array(
                        'announcement_id' => $announcement->id,
                        'class_section_id' => $section_id
                    );

                    $announcementClass = AnnouncementClass::create($announcementClassData);

                    // Create tracking record for announcement class
                    $this->sessionYearsTrackingsService->storeSessionYearsTracking(
                        'App\\Models\\AnnouncementClass',
                        $announcementClass->id,
                        $schoolAdmin->id,
                        $sessionYear->id,
                        $schoolId,
                        $semester ? $semester->id : null
                    );
                }

                // Get all students for notifications
                $students = Students::whereIn('class_section_id', $section_ids)
                    ->with('user')
                    ->get();

                // Create notifications for students
                foreach ($students as $student) {
                    Notification::create(array(
                        'title' => 'New Announcement: ' . $announcement->title,
                        'message' => $announcement->description,
                        'user_id' => $student->user_id,
                        'school_id' => $schoolId,
                        'session_year_id' => $sessionYear->id,
                        'send_to' => 'Student'
                    ));
                }
            } catch (\Exception $e) {
                // Log the error but continue with other announcements
                \Log::error('Error creating announcement: ' . $e->getMessage());
                continue;
            }
        }
    }

    private function seedTimetable($schoolId)
    {
        $classSections = ClassSection::where('school_id', $schoolId)->get();
        $sessionYear = SessionYear::where('school_id', $schoolId)->where('default', 1)->first();
        $staff = Staff::where('school_id', $schoolId)->get();
        
        $timeSlots = [
            ['start' => '08:00:00', 'end' => '08:45:00'],
            ['start' => '08:45:00', 'end' => '09:30:00'],
            ['start' => '09:30:00', 'end' => '10:15:00'],
            ['start' => '10:15:00', 'end' => '11:00:00'],
            ['start' => '11:30:00', 'end' => '12:15:00'],
            ['start' => '12:15:00', 'end' => '13:00:00'],
            ['start' => '13:00:00', 'end' => '13:45:00'],
        ];

        foreach ($classSections as $classSection) {
            $subjects = Subject::where('class_id', $classSection->class_id)->get();
            
            for ($day = 1; $day <= 5; $day++) { // Monday to Friday
                foreach ($timeSlots as $index => $timeSlot) {
                    // Skip one slot for break time
                    if ($index === 4) {
                        continue;
                    }

                    Timetable::create([
                        'class_section_id' => $classSection->id,
                        'subject_id' => $subjects->random()->id,
                        'staff_id' => $staff->random()->id,
                        'day' => $day,
                        'start_time' => $timeSlot['start'],
                        'end_time' => $timeSlot['end'],
                        'session_year_id' => $sessionYear->id,
                        'school_id' => $schoolId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    private function seedOnlineExams($schoolId)
    {
        $sessionYear = SessionYear::where('school_id', $schoolId)->where('default', 1)->first();
        $classSections = ClassSection::where('school_id', $schoolId)->get();

        foreach ($classSections as $classSection) {
            $subjects = Subject::where('class_id', $classSection->class_id)->get();
            
            foreach ($subjects as $subject) {
                $exam = OnlineExam::create([
                    'name' => "Online Test - {$subject->name}",
                    'description' => "Online assessment for {$subject->name}",
                    'class_section_id' => $classSection->id,
                    'subject_id' => $subject->id,
                    'session_year_id' => $sessionYear->id,
                    'duration' => 60, // 60 minutes
                    'start_date' => now()->addDays(7),
                    'end_date' => now()->addDays(7)->addHours(24),
                    'total_marks' => 100,
                    'passing_marks' => 35,
                    'school_id' => $schoolId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Add questions
                for ($i = 1; $i <= 10; $i++) {
                    DB::table('online_exam_questions')->insert([
                        'online_exam_id' => $exam->id,
                        'question' => "Question {$i} for {$subject->name}",
                        'options' => json_encode([
                            'option_1' => "Option 1 for question {$i}",
                            'option_2' => "Option 2 for question {$i}",
                            'option_3' => "Option 3 for question {$i}",
                            'option_4' => "Option 4 for question {$i}",
                        ]),
                        'correct_answer' => 'option_' . $this->faker->numberBetween(1, 4),
                        'marks' => 10,
                        'school_id' => $schoolId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    private function seedGallery($schoolId)
    {
        $categories = [
            'Annual Day',
            'Sports Day',
            'Independence Day',
            'Republic Day',
            'Science Exhibition'
        ];

        foreach ($categories as $category) {
            $galleryId = DB::table('gallery')->insertGetId([
                'title' => $category . ' Celebration',
                'description' => "Photos from {$category} celebration",
                'school_id' => $schoolId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Add dummy image entries
            for ($i = 1; $i <= 5; $i++) {
                DB::table('gallery_images')->insert([
                    'gallery_id' => $galleryId,
                    'image_url' => "gallery/default{$i}.jpg",
                    'school_id' => $schoolId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedCertificates($schoolId)
    {
        $certificateTypes = [
            [
                'name' => 'Transfer Certificate',
                'template' => '<div class="certificate">
                    <h1>{SCHOOL_NAME}</h1>
                    <h2>Transfer Certificate</h2>
                    <p>This is to certify that {STUDENT_NAME} has successfully completed their education up to Class {CLASS_NAME} for the academic year {ACADEMIC_YEAR}.</p>
                </div>'
            ],
            [
                'name' => 'Character Certificate',
                'template' => '<div class="certificate">
                    <h1>{SCHOOL_NAME}</h1>
                    <h2>Character Certificate</h2>
                    <p>This is to certify that {STUDENT_NAME} of Class {CLASS_NAME} has maintained good character and discipline during their stay in the school.</p>
                </div>'
            ],
            [
                'name' => 'Bonafide Certificate',
                'template' => '<div class="certificate">
                    <h1>{SCHOOL_NAME}</h1>
                    <h2>Bonafide Certificate</h2>
                    <p>This is to certify that {STUDENT_NAME} is a bonafide student of our school studying in Class {CLASS_NAME} for the academic year {ACADEMIC_YEAR}.</p>
                </div>'
            ]
        ];

        foreach ($certificateTypes as $type) {
            DB::table('certificate_templates')->insert([
                'name' => $type['name'],
                'template' => $type['template'],
                'school_id' => $schoolId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedSliders($schoolId)
    {
        $sliders = [
            [
                'title' => 'Welcome to Our School',
                'image' => 'sliders/welcome.jpg',
                'description' => 'Nurturing minds, building futures',
            ],
            [
                'title' => 'Academic Excellence',
                'image' => 'sliders/academic.jpg',
                'description' => 'Committed to academic excellence',
            ],
            [
                'title' => 'Sports Facilities',
                'image' => 'sliders/sports.jpg',
                'description' => 'State-of-the-art sports facilities',
            ],
            [
                'title' => 'Cultural Activities',
                'image' => 'sliders/cultural.jpg',
                'description' => 'Promoting cultural values',
            ],
        ];

        foreach ($sliders as $slider) {
            Slider::create(array_merge($slider, [
                'school_id' => $schoolId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function seedLesson($schoolId)
    {
        try {
            // Get required data
            $sessionYear = SessionYear::where('school_id', $schoolId)
                ->where('default', 1)
                ->first();

            if (!$sessionYear) {
                throw new \Exception('Default session year not found');
            }

            // Get school admin for tracking
            $schoolAdmin = User::where('school_id', $schoolId)
                ->whereHas('roles', function($q) {
                    $q->where('name', 'School Admin');
                })
                ->first();

            if (!$schoolAdmin) {
                throw new \Exception('School admin not found');
            }

            // Get all class sections for the school
            $classSections = ClassSection::where('school_id', $schoolId)->get();
            
            // For each class section, create sample lessons
            foreach ($classSections as $classSection) {
                // Get subjects for this class from the pivot table
                $classSubjects = DB::table('class_subjects')
                    ->where('class_id', $classSection->class_id)
                    ->where('school_id', $schoolId)
                    ->get();
                
                foreach ($classSubjects as $classSubject) {
                    // Create lesson data
                    $lessonData = [
                        'name' => 'Sample Lesson - ' . $classSubject->subject_id,
                        'description' => 'This is a sample lesson for subject ID ' . $classSubject->subject_id,
                        'class_section_id' => $classSection->id,
                        'class_subject_id' => $classSubject->id,
                        'school_id' => $schoolId,
                    ];

                    // Create or update the lesson
                    $lesson = Lesson::updateOrCreate(
                        [
                            'name' => $lessonData['name'],
                            'class_section_id' => $classSection->id,
                            'class_subject_id' => $classSubject->id,
                            'school_id' => $schoolId,
                        ],
                        $lessonData
                    );

                    // Create lesson common data
                    LessonCommon::updateOrCreate(
                        [
                            'lesson_id' => $lesson->id,
                            'class_section_id' => $classSection->id,
                            'class_subject_id' => $classSubject->id,
                        ]
                    );

                    // Get semester data
                    $semester = Semester::where('school_id', $schoolId)
                        ->where('start_month', '<=', now()->month)
                        ->where('end_month', '>=', now()->month)
                        ->first();

                    // Store session year tracking
                    $this->sessionYearsTrackingsService->storeSessionYearsTracking(
                        'App\\Models\\Lesson',
                        $lesson->id,
                        $schoolAdmin->id,
                        $sessionYear->id,
                        $schoolId,
                        $semester ? $semester->id : null
                    );
                }
            }
        } catch (\Exception $e) {
            $this->command->error('Error seeding lessons: ' . $e->getMessage());
            throw $e;
        }
    }

    private function seedLessonTopic($schoolId)
    {
        try {
            // Get required data
            $sessionYear = SessionYear::where('school_id', $schoolId)
                ->where('default', 1)
                ->first();

            if (!$sessionYear) {
                throw new \Exception('Default session year not found');
            }
                
            // Get school admin for tracking
            $schoolAdmin = User::where('school_id', $schoolId)
                ->whereHas('roles', function($q) {
                    $q->where('name', 'School Admin');
                })
                ->first();

            if (!$schoolAdmin) {
                throw new \Exception('School admin not found');
            }

            // Get all class sections for the school
            $classSections = ClassSection::where('school_id', $schoolId)->get();
            
            // For each class section, create sample lesson topics
            foreach ($classSections as $classSection) {
                // Get subjects for this class from the pivot table
                $classSubjects = DB::table('class_subjects')
                    ->where('class_id', $classSection->class_id)
                    ->where('school_id', $schoolId)
                    ->get();
                
                foreach ($classSubjects as $classSubject) {
                    // First check if a lesson exists for this subject
                    $lesson = Lesson::where('class_subject_id', $classSubject->id)
                        ->where('school_id', $schoolId)
                        ->first();

                    if (!$lesson) {
                        continue; // Skip if no lesson exists
                    }
                        
                    // Create lesson topic data
                    $lessonTopicData = [
                        'name' => 'Sample Topic - ' . $classSubject->subject_id,
                        'description' => 'This is a sample topic for subject ID ' . $classSubject->subject_id,
                        'lesson_id' => $lesson->id,
                        'school_id' => $schoolId,
                    ];

                    // Create or update the lesson topic
                    $topic = LessonTopic::updateOrCreate(
                        [
                            'name' => $lessonTopicData['name'],
                            'lesson_id' => $lesson->id,
                            'school_id' => $schoolId,
                        ],
                        $lessonTopicData
                    );

                    // Create or update topic common data
                    TopicCommon::updateOrCreate(
                        [
                            'lesson_topics_id' => $topic->id,
                            'class_section_id' => $classSection->id,
                            'class_subject_id' => $classSubject->id
                        ]
                    );

                    // Get semester data
                    $semester = Semester::where('school_id', $schoolId)
                        ->where('start_month', '<=', now()->month)
                        ->where('end_month', '>=', now()->month)
                        ->first();

                    // Store session year tracking
                    $this->sessionYearsTrackingsService->storeSessionYearsTracking(
                        'App\\Models\\LessonTopic',
                        $topic->id,
                        $schoolAdmin->id,
                        $sessionYear->id,
                        $schoolId,
                        $semester ? $semester->id : null
                    );
                }
            }
        } catch (\Exception $e) {
            $this->command->error('Error seeding lesson topics: ' . $e->getMessage());
            throw $e;
        }
    }
}
