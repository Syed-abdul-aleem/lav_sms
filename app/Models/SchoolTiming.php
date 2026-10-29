<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolTiming extends Model
{
    protected $table = 'school_timings';

    protected $fillable = [
        'start_time', 'end_time', 'friday_end_time',
        'assembly_start', 'assembly_end', 'break_start', 'break_end', 'note',
    ];
}
