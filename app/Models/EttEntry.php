<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class EttEntry extends Model
{
    protected $table = 'ett_entries';

    protected $fillable = [
        'scope', 'my_class_id', 'section_id', 'day', 'exam_date',
        'start_time', 'end_time', 'subject', 'kind', 'teacher_id', 'teacher_name',
    ];

    public function my_class()
    {
        return $this->belongsTo(MyClass::class, 'my_class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
