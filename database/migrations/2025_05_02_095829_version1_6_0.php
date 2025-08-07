<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Version 1.5.5 changes
        
        Schema::create('contact_inquiry', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->text('message');
            $table->timestamps();
            $table->softDeletes();
        });
       
        // Clear cache to apply changes
        Cache::flush();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No changes to revert

        Schema::dropIfExists('contact_inquiry');
    }
};
