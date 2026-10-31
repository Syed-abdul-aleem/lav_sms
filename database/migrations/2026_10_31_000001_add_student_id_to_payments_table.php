<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStudentIdToPaymentsTable extends Migration
{
    /**
     * Adds payments.student_id — when set, the fee belongs to ONE student only
     * (individual fee from the Student Fees section). NULL = class/general fee.
     */
    public function up()
    {
        if (!Schema::hasColumn('payments', 'student_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->unsignedInteger('student_id')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('payments', 'student_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('student_id');
            });
        }
    }
}
