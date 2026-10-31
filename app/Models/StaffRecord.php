<?php

namespace App\Models;

use App\User;
use Eloquent;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class StaffRecord extends Eloquent
{
    protected $fillable = ['code', 'emp_date', 'user_id', 'subjects', 'salary'];

    /** Agreed monthly salary column — purani DBs par khud ban jata hai */
    public static function ensureSalaryColumn()
    {
        if (!Schema::hasColumn('staff_records', 'salary')) {
            Schema::table('staff_records', function (Blueprint $table) {
                $table->unsignedInteger('salary')->nullable();
            });
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
