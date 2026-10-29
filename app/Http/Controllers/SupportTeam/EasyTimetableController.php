<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\EttEntry;
use App\Models\MyClass;
use App\Models\SchoolTiming;
use App\Models\Section;
use App\Models\Subject;
use App\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Easy Timetables — deliberately simple, single-form timetables:
 *  - Class timetable (one form: day, start, end, subject, optional teacher)
 *  - Teacher timetable (built automatically from the class periods)
 *  - School timing (one small form)
 *  - Master timetable (all classes on one page)
 *  - Exam timetable (date-based, same simple form)
 *
 * Tables are created lazily when missing: on the hosted serverless
 * deployment, migrations only run through a one-time setup flow, so the
 * module must be able to set itself up. The guarded migration file covers
 * normal installs.
 */
class EasyTimetableController extends Controller
{
    const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    const KINDS = ['Period', 'Break', 'Assembly', 'Other'];

    public function __construct()
    {
        $this->middleware('teamSA', ['only' => [
            'classForm', 'store', 'examForm', 'storeExam', 'timingForm', 'storeTiming', 'destroyClass', 'destroyExam',
        ]]);
        self::ensureTables();
    }

    public static function ensureTables()
    {
        if (!Schema::hasTable('ett_entries')) {
            Schema::create('ett_entries', function (Blueprint $table) {
                $table->increments('id');
                $table->string('scope', 10)->default('class');
                $table->unsignedInteger('my_class_id')->nullable();
                $table->unsignedInteger('section_id')->nullable();
                $table->string('day', 10)->nullable();
                $table->date('exam_date')->nullable();
                $table->string('start_time', 5);
                $table->string('end_time', 5);
                $table->string('subject', 120)->nullable();
                $table->string('kind', 20)->default('Period');
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

    public static function fmt($hhmm)
    {
        if (!$hhmm) return '';
        $ts = strtotime($hhmm);
        return $ts ? date('h:i A', $ts) : $hhmm;
    }

    protected function timing()
    {
        return SchoolTiming::first();
    }

    /* ------------------------------ Hub ------------------------------ */

    public function index()
    {
        $data['classes'] = MyClass::with('sections')->orderBy('id')->get();
        $data['counts'] = EttEntry::where('scope', 'class')
            ->select('my_class_id', 'section_id', DB::raw('count(*) as c'))
            ->groupBy('my_class_id', 'section_id')->get()
            ->mapWithKeys(function ($r) { return [$r->my_class_id . '-' . $r->section_id => $r->c]; });
        $data['exam_counts'] = EttEntry::where('scope', 'exam')
            ->select('my_class_id', DB::raw('count(*) as c'))->groupBy('my_class_id')->get()
            ->mapWithKeys(function ($r) { return [$r->my_class_id => $r->c]; });
        $data['teachers'] = User::where('user_type', 'teacher')->orderBy('name')->get();
        $data['timing'] = $this->timing();
        return view('pages.support_team.ett.index', $data);
    }

    /* ------------------------- Class timetable ------------------------ */

    public function classForm(Request $req)
    {
        $data['my_classes'] = MyClass::orderBy('id')->get();
        $data['sections'] = Section::all();
        $data['teachers'] = User::where('user_type', 'teacher')->orderBy('name')->get();
        $data['subjects'] = Subject::orderBy('name')->get(['id', 'my_class_id', 'name']);
        $data['days'] = self::DAYS;
        $data['kinds'] = self::KINDS;

        $classId = (int) ($req->query('class_id') ?: optional($data['my_classes']->first())->id);
        $section = Section::where('my_class_id', $classId)->orderBy('id')
            ->when($req->query('section_id'), function ($q) use ($req) { return $q->where('id', (int) $req->query('section_id')); })
            ->first() ?: Section::where('my_class_id', $classId)->orderBy('id')->first();
        $data['sel_class'] = $classId;
        $data['sel_section'] = $section ? $section->id : null;
        $data['entries'] = $section
            ? EttEntry::where('scope', 'class')->where('my_class_id', $classId)->where('section_id', $section->id)
                ->orderByRaw($this->dayOrderSql())->orderBy('start_time')->get()
            : collect();

        return view('pages.support_team.ett.class_form', $data);
    }

    public function store(Request $req)
    {
        $req->validate([
            'my_class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'rows' => 'nullable|array',
            'rows.*.day' => 'nullable|in:' . implode(',', self::DAYS),
            'rows.*.start_time' => 'nullable|date_format:H:i',
            'rows.*.end_time' => 'nullable|date_format:H:i',
            'rows.*.subject' => 'nullable|string|max:120',
            'rows.*.kind' => 'nullable|in:' . implode(',', self::KINDS),
            'rows.*.teacher_id' => 'nullable|integer',
        ]);

        $classId = (int) $req->my_class_id;
        $sectionId = (int) $req->section_id;

        DB::transaction(function () use ($req, $classId, $sectionId) {
            EttEntry::where('scope', 'class')->where('my_class_id', $classId)->where('section_id', $sectionId)->delete();
            foreach ((array) $req->rows as $row) {
                if (empty($row['day']) || empty($row['start_time']) || empty($row['end_time'])) continue;
                $teacher = !empty($row['teacher_id'])
                    ? User::where('user_type', 'teacher')->find((int) $row['teacher_id'])
                    : null;
                $teacherName = $teacher ? $teacher->name : null;
                EttEntry::create([
                    'scope' => 'class',
                    'my_class_id' => $classId,
                    'section_id' => $sectionId,
                    'day' => $row['day'],
                    'start_time' => $row['start_time'],
                    'end_time' => $row['end_time'],
                    'subject' => $row['subject'] ?? null,
                    'kind' => $row['kind'] ?? 'Period',
                    'teacher_id' => $teacher ? $teacher->id : null,
                    'teacher_name' => $teacherName,
                ]);
            }
        });

        return redirect()->route('ett.class.show', [$classId, $sectionId])
            ->with('flash_success', 'Class timetable saved.');
    }

    public function showClass($class_id, $section_id)
    {
        $data = $this->classData((int) $class_id, (int) $section_id);
        return view('pages.support_team.ett.class_show', $data);
    }

    public function printClass($class_id, $section_id)
    {
        $data = $this->classData((int) $class_id, (int) $section_id);
        return view('pages.support_team.ett.print_class', $data);
    }

    protected function classData(int $classId, int $sectionId)
    {
        $entries = EttEntry::where('scope', 'class')->where('my_class_id', $classId)->where('section_id', $sectionId)
            ->orderBy('start_time')->get();
        return [
            'class' => MyClass::find($classId),
            'section' => Section::find($sectionId),
            'byDay' => $entries->groupBy('day'),
            'days' => self::DAYS,
            'timing' => $this->timing(),
        ];
    }

    public function destroyClass($class_id, $section_id)
    {
        EttEntry::where('scope', 'class')->where('my_class_id', (int) $class_id)->where('section_id', (int) $section_id)->delete();
        return redirect()->route('ett.index')->with('flash_success', 'Class timetable deleted.');
    }

    /* ------------------------ Teacher timetable ----------------------- */

    public function teachers()
    {
        $data['teachers'] = User::where('user_type', 'teacher')->orderBy('name')->get();
        $data['counts'] = EttEntry::where('scope', 'class')->whereNotNull('teacher_id')
            ->select('teacher_id', DB::raw('count(*) as c'))->groupBy('teacher_id')->get()
            ->mapWithKeys(function ($r) { return [$r->teacher_id => $r->c]; });
        return view('pages.support_team.ett.teachers', $data);
    }

    public function teacherShow($teacher_id)
    {
        $data = $this->teacherData((int) $teacher_id);
        return view('pages.support_team.ett.teacher_show', $data);
    }

    public function printTeacher($teacher_id)
    {
        $data = $this->teacherData((int) $teacher_id);
        return view('pages.support_team.ett.print_teacher', $data);
    }

    public function my()
    {
        if (!Qs::userIsTeacher()) {
            return redirect()->route('dashboard')->with('pop_error', 'Only teachers have a personal timetable.');
        }
        $data = $this->teacherData(Auth::id());
        $data['mine'] = true;
        return view('pages.support_team.ett.teacher_show', $data);
    }

    protected function teacherData(int $teacherId)
    {
        $entries = EttEntry::with(['my_class', 'section'])
            ->where('scope', 'class')->where('teacher_id', $teacherId)
            ->orderBy('start_time')->get();
        return [
            'teacher' => User::find($teacherId),
            'byDay' => $entries->groupBy('day'),
            'days' => self::DAYS,
            'timing' => $this->timing(),
        ];
    }

    /* --------------------------- School timing ------------------------ */

    public function timingForm()
    {
        $data['timing'] = $this->timing();
        return view('pages.support_team.ett.timing', $data);
    }

    public function storeTiming(Request $req)
    {
        $req->validate([
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'friday_end_time' => 'nullable|date_format:H:i',
            'assembly_start' => 'nullable|date_format:H:i',
            'assembly_end' => 'nullable|date_format:H:i',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i',
            'note' => 'nullable|string|max:190',
        ]);

        $t = SchoolTiming::first() ?: new SchoolTiming();
        $t->fill($req->only([
            'start_time', 'end_time', 'friday_end_time',
            'assembly_start', 'assembly_end', 'break_start', 'break_end', 'note',
        ]));
        $t->save();

        return redirect()->route('ett.index')->with('flash_success', 'School timing saved.');
    }

    /* ------------------------------ Master ---------------------------- */

    public function master()
    {
        $pairs = EttEntry::where('scope', 'class')
            ->select('my_class_id', 'section_id')->distinct()->get();
        $data['blocks'] = $pairs->map(function ($p) {
            return $this->classData((int) $p->my_class_id, (int) $p->section_id);
        });
        $data['timing'] = $this->timing();
        return view('pages.support_team.ett.master', $data);
    }

    /* -------------------------- Exam timetable ------------------------ */

    public function examForm(Request $req)
    {
        $data['my_classes'] = MyClass::orderBy('id')->get();
        $data['teachers'] = User::where('user_type', 'teacher')->orderBy('name')->get();
        $data['sel_class'] = (int) ($req->query('class_id') ?: optional($data['my_classes']->first())->id);
        $data['entries'] = EttEntry::where('scope', 'exam')->where('my_class_id', $data['sel_class'])
            ->orderBy('exam_date')->orderBy('start_time')->get();
        return view('pages.support_team.ett.exam_form', $data);
    }

    public function storeExam(Request $req)
    {
        $req->validate([
            'my_class_id' => 'required|integer',
            'rows' => 'nullable|array',
            'rows.*.exam_date' => 'nullable|date',
            'rows.*.start_time' => 'nullable|date_format:H:i',
            'rows.*.end_time' => 'nullable|date_format:H:i',
            'rows.*.subject' => 'nullable|string|max:120',
            'rows.*.teacher_id' => 'nullable|integer',
        ]);

        $classId = (int) $req->my_class_id;

        DB::transaction(function () use ($req, $classId) {
            EttEntry::where('scope', 'exam')->where('my_class_id', $classId)->delete();
            foreach ((array) $req->rows as $row) {
                if (empty($row['exam_date']) || empty($row['start_time']) || empty($row['end_time'])) continue;
                $teacher = !empty($row['teacher_id'])
                    ? User::where('user_type', 'teacher')->find((int) $row['teacher_id'])
                    : null;
                $teacherName = $teacher ? $teacher->name : null;
                EttEntry::create([
                    'scope' => 'exam',
                    'my_class_id' => $classId,
                    'section_id' => null,
                    'exam_date' => $row['exam_date'],
                    'start_time' => $row['start_time'],
                    'end_time' => $row['end_time'],
                    'subject' => $row['subject'] ?? null,
                    'kind' => 'Period',
                    'teacher_id' => $teacher ? $teacher->id : null,
                    'teacher_name' => $teacherName,
                ]);
            }
        });

        return redirect()->route('ett.exam.show', $classId)
            ->with('flash_success', 'Exam timetable saved.');
    }

    public function examShow($class_id)
    {
        $data['class'] = MyClass::find((int) $class_id);
        $data['entries'] = EttEntry::where('scope', 'exam')->where('my_class_id', (int) $class_id)
            ->orderBy('exam_date')->orderBy('start_time')->get();
        $data['timing'] = $this->timing();
        return view('pages.support_team.ett.exam_show', $data);
    }

    public function printExam($class_id)
    {
        $data['class'] = MyClass::find((int) $class_id);
        $data['entries'] = EttEntry::where('scope', 'exam')->where('my_class_id', (int) $class_id)
            ->orderBy('exam_date')->orderBy('start_time')->get();
        $data['timing'] = $this->timing();
        return view('pages.support_team.ett.print_exam', $data);
    }

    public function destroyExam($class_id)
    {
        EttEntry::where('scope', 'exam')->where('my_class_id', (int) $class_id)->delete();
        return redirect()->route('ett.index')->with('flash_success', 'Exam timetable deleted.');
    }

    /* ------------------------------ Helpers --------------------------- */

    protected function dayOrderSql()
    {
        $cases = collect(self::DAYS)->map(function ($d, $i) { return "WHEN day = '$d' THEN $i"; })->implode(' ');
        return "CASE $cases ELSE 99 END";
    }
}
