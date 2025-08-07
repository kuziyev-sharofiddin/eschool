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

        changeEnv(['QUEUE_CONNECTION' => 'database']);
        
        Schema::table('schools', function (Blueprint $table) {
            if (!Schema::hasColumn('schools', 'installed')) {
                $table->tinyInteger('installed')->default(0)->comment('0: Not installed, 1: Installed')->after('status');
            }
        });

        School::whereNotNull('id')->update(['installed' => 1]);

        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (Schema::hasColumn('schools', 'installed')) {
                $table->dropColumn('installed');
            }
        });

        Schema::dropIfExists('jobs');
        Schema::dropIfExists('contact_inquiry');

    }
};
