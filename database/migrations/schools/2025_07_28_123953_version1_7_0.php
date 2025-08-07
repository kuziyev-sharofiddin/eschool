<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\School;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Schema::create('diary_categories', static function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->enum('type', ['positive', 'negative'])->default('positive');
                $table->timestamps();
                $table->softDeletes();
            });
    
            Schema::create('diaries', static function (Blueprint $table) {
                $table->id();
                $table->foreignId('diary_category_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('subject_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('session_year_id')->constrained()->cascadeOnDelete();
                $table->longText('description')->nullable();
                $table->date('date');
                $table->timestamps();
                $table->softDeletes();
            });
    
            Schema::create('diary_students', static function (Blueprint $table) {
                $table->id();
                $table->foreignId('diary_id')->constrained()->cascadeOnDelete();
                $table->foreignId('student_id')->nullable(true)->comment('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreignId('class_section_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });    

            Schema::table('online_exam_questions', function (Blueprint $table) {
                $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('easy')->after('note');
            });

            Schema::table('online_exam_questions', function (Blueprint $table) {
                // class_section_id, class_subject_id remove this foreign key and column
                $table->dropForeign(['class_section_id']);
                $table->dropForeign(['class_subject_id']);
                $table->dropColumn('class_section_id');
                $table->dropColumn('class_subject_id');
            });

            Schema::table('lessons', function (Blueprint $table) {
                $table->dropForeign(['class_section_id']);
                $table->dropForeign(['class_subject_id']);
                $table->dropColumn('class_section_id');
                $table->dropColumn('class_subject_id');
            });

            Schema::table('schools', function (Blueprint $table) {
                if (!Schema::hasColumn('schools', 'installed')) {
                    $table->tinyInteger('installed')->default(0)->comment('0: Not installed, 1: Installed')->after('status');
                }
            });
    
            School::whereNotNull('id')->update(['installed' => 1]);

            Schema::dropIfExists('topic_commons');

            Schema::dropIfExists('contact_inquiry');

        
            Schema::create('contact_inquiry', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->string('subject')->nullable();
                $table->string('message')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

        } catch (\Throwable $th) {
            
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diary_categories');
        Schema::dropIfExists('diaries');
        Schema::dropIfExists('diary_students');

        Schema::table('online_exam_questions', function (Blueprint $table) {
            $table->dropColumn('difficulty');
        });

        Schema::table('online_exam_questions', function (Blueprint $table) {
            $table->foreignId('class_section_id')->nullable()->constrained('class_sections');
            $table->foreignId('class_subject_id')->nullable()->constrained('class_subjects');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->foreignId('class_section_id')->nullable()->constrained()->cascadeOnDelete('set null');
            $table->foreignId('class_subject_id')->nullable()->constrained()->cascadeOnDelete('set null');
        });

        Schema::create('topic_commons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete('set null');
            $table->foreignId('class_section_id')->constrained()->cascadeOnDelete('set null');
            $table->foreignId('class_subject_id')->constrained()->cascadeOnDelete('set null');
            $table->timestamps();
        });

        Schema::dropIfExists('contact_inquiry');
    }
};
