<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('session_years_trackings', function (Blueprint $table) {
            $table->id();
            $table->string('modal_type');
            $table->integer('modal_id');
            
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('session_year_id')->constrained('session_years')->onDelete('cascade');
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            
            $table->timestamps();
        });
        
        // 1. Create contact_inquiry table
        Schema::create('contact_inquiry', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject');
            $table->text('message');
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Add amount_installment to fees_installments table
        Schema::table('fees_installments', function (Blueprint $table) {
            $table->double('installment_amount', 64, 4)->after('name')->default(0);
        });
        
        // 3. Add join_leave_year columns to students and staffs tables
        Schema::table('students', function (Blueprint $table) {
            $table->bigInteger('join_session_year_id')->nullable()->after('guardian_id');
            $table->bigInteger('leave_session_year_id')->nullable()->after('join_session_year_id');
        });

        Schema::table('staffs', function (Blueprint $table) {
            $table->foreignId('session_year_id')->nullable()->after('joining_date')->constrained('session_years')->onDelete('cascade');
            $table->bigInteger('join_session_year_id')->nullable()->after('session_year_id');
            $table->bigInteger('leave_session_year_id')->nullable()->after('join_session_year_id');
        });

        Schema::table('promote_students', function (Blueprint $table) {
            $table->bigInteger('current_session_year_id')->nullable()->after('session_year_id');
            $table->bigInteger('current_class_section_id')->nullable()->after('current_session_year_id');
        });

        // mediums table
        // Schema::table('mediums', function (Blueprint $table) {
        //     $table->foreignId('session_year_id')->after('school_id')->constrained('session_years')->onDelete('cascade');
        // });

        // // sections table
        // Schema::table('sections', function (Blueprint $table) {
        //     $table->foreignId('session_year_id')->after('school_id')->constrained('session_years')->onDelete('cascade');
        // });

        // // subjects table
        // Schema::table('subjects', function (Blueprint $table) {
        //     $table->foreignId('session_year_id')->after('school_id')->constrained('session_years')->onDelete('cascade');
        // });

        // // Streams table
        // Schema::table('streams', function (Blueprint $table) {
        //     $table->foreignId('session_year_id')->after('school_id')->constrained('session_years')->onDelete('cascade');
        // });

        // // Shift
        // Schema::table('shifts', function (Blueprint $table) {
        //     $table->foreignId('session_year_id')->after('school_id')->constrained('session_years')->onDelete('cascade');
        // });

        // // classes
        // Schema::table('classes', function (Blueprint $table) {
        //     $table->foreignId('session_year_id')->after('school_id')->constrained('session_years')->onDelete('cascade');
        // });

        // // session year id in semesters table
        // Schema::table('semesters', function (Blueprint $table) {
        //     $table->foreignId('session_year_id')->after('school_id')->constrained('session_years')->onDelete('cascade');
        // });

        // // timetable
        // Schema::table('timetables', function (Blueprint $table) {
        //     $table->foreignId('session_year_id')->nullable()->after('semester_id')->constrained('session_years')->onDelete('cascade');
        // });

        // // Holiday
        // Schema::table('holidays', function (Blueprint $table) {
        //     $table->foreignId('session_year_id')->after('school_id')->constrained('session_years')->onDelete('cascade');
        // });

        // Clear cache to apply changes
        Cache::flush();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('session_years_trackings');
        
        // 1. Drop contact_inquiry table
        Schema::dropIfExists('contact_inquiry');

        Schema::table('fees_installments', function (Blueprint $table) {
            $table->dropColumn('installment_amount');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['join_session_year_id', 'leave_session_year_id']);
        });

        Schema::table('staffs', function (Blueprint $table) {
            if (Schema::hasColumn('staffs', 'session_year_id')) {
                $table->dropColumn('session_year_id');
            }
            if (Schema::hasColumn('staffs', 'join_session_year_id')) {
                $table->dropColumn('join_session_year_id');
            }
            if (Schema::hasColumn('staffs', 'leave_session_year_id')) {
                $table->dropColumn('leave_session_year_id');
            }
        });

        Schema::table('promote_students', function (Blueprint $table) {
            $table->dropColumn('current_session_year_id');
            $table->dropColumn('current_class_section_id');
        });

        // Schema::table('mediums', function (Blueprint $table) {
        //     $table->dropColumn('session_year_id');
        // });

        // Schema::table('sections', function (Blueprint $table) {
        //     $table->dropColumn('session_year_id');
        // });

        // Schema::table('subjects', function (Blueprint $table) {
        //     $table->dropColumn('session_year_id');
        // });

        // Schema::table('streams', function (Blueprint $table) {
        //     $table->dropColumn('session_year_id');
        // });

        // Schema::table('shifts', function (Blueprint $table) {
        //     $table->dropColumn('session_year_id');
        // });

        // Schema::table('classes', function (Blueprint $table) {
        //     $table->dropColumn('session_year_id');
        // });

        // Schema::table('semesters', function (Blueprint $table) {
        //     $table->dropColumn('session_year_id');
        // });

        // Schema::table('timetables', function (Blueprint $table) {
        //     $table->dropColumn('session_year_id');
        // });

        // Schema::table('holidays', function (Blueprint $table) {
        //     $table->dropColumn('session_year_id');
        // });
    }
};
