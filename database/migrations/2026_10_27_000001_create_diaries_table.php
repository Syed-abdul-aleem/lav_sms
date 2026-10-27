<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class CreateDiariesTable extends Migration
{
    public function up()
    {
        Schema::create('diaries', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('my_class_id');
            $table->unsignedInteger('section_id')->nullable();
            $table->unsignedInteger('subject_id')->nullable();
            $table->unsignedInteger('user_id'); // author (teacher/admin)
            $table->date('date');
            $table->string('title');
            $table->text('description')->nullable(); // class work / notes
            $table->text('homework')->nullable();
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('diaries'); }
}
