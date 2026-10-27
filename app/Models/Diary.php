<?php
namespace App\Models;
use App\User;
use Eloquent;
class Diary extends Eloquent
{
    protected $fillable = ['my_class_id', 'section_id', 'subject_id', 'user_id', 'date', 'title', 'description', 'homework'];
    protected $casts = ['date' => 'date'];
    public function my_class() { return $this->belongsTo(MyClass::class); }
    public function section() { return $this->belongsTo(Section::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function author() { return $this->belongsTo(User::class, 'user_id'); }
}
