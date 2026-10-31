<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSalaryToStaffRecordsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('staff_records', 'salary')) {
            Schema::table('staff_records', function (Blueprint $table) {
                $table->unsignedInteger('salary')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('staff_records', 'salary')) {
            Schema::table('staff_records', function (Blueprint $table) {
                $table->dropColumn('salary');
            });
        }
    }
}
