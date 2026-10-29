<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEasyTimetableTables extends Migration
{
    public function up()
    {
        // Guarded: on the hosted (serverless) deployment these tables are also
        // created lazily by EasyTimetableController when missing, because
        // migrations there run through a one-time setup flow.
        if (!Schema::hasTable('ett_entries')) {
            Schema::create('ett_entries', function (Blueprint $table) {
                $table->increments('id');
                $table->string('scope', 10)->default('class'); // class | exam
                $table->unsignedInteger('my_class_id')->nullable();
                $table->unsignedInteger('section_id')->nullable();
                $table->string('day', 10)->nullable();          // Monday..Sunday (class scope)
                $table->date('exam_date')->nullable();          // exam scope
                $table->string('start_time', 5);                // HH:MM 24h
                $table->string('end_time', 5);                  // HH:MM 24h
                $table->string('subject', 120)->nullable();
                $table->string('kind', 20)->default('Period');  // Period | Break | Assembly | Other
                $table->unsignedInteger('teacher_id')->nullable();
                $table->string('teacher_name', 120)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('school_timings')) {
            Schema::create('school_timings', function (Blueprint $table) {
                $table->increments('id');
                $table->string('start_time', 5)->nullable();
                $table->string('end_time', 5)->nullable();
                $table->string('friday_end_time', 5)->nullable();
                $table->string('assembly_start', 5)->nullable();
                $table->string('assembly_end', 5)->nullable();
                $table->string('break_start', 5)->nullable();
                $table->string('break_end', 5)->nullable();
                $table->string('note', 190)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ett_entries');
        Schema::dropIfExists('school_timings');
    }
}
