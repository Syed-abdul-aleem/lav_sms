<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\Diary;
use App\Models\MyClass;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiaryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function canWrite()
    {
        return Qs::userIsTeamSAT() || Qs::userIsAdmin() || Qs::userIsSuperAdmin();
    }

    public function index(Request $req)
    {
        $user = Auth::user();
        $q = Diary::with(['my_class', 'section', 'subject', 'author'])->orderByDesc('date')->orderByDesc('id');

        // Students only see their own class (and section when set)
        if (Qs::userIsStudent()) {
            $sr = $user->student_record;
            if (!$sr) { return view('pages.support_team.diaries.index', ['diaries' => collect(), 'can_write' => false, 'classes' => MyClass::orderBy('name')->get(), 'subjects' => collect()]); }
            $q->where('my_class_id', $sr->my_class_id)
              ->where(function ($w) use ($sr) { $w->whereNull('section_id')->orWhere('section_id', $sr->section_id); });
        }

        // Parents only see diaries for their children's classes
        if (Qs::userIsParent()) {
            $children = \App\Models\StudentRecord::where('my_parent_id', $user->id)->get();
            $classIds = $children->pluck('my_class_id')->unique()->all();
            $sectionIds = $children->pluck('section_id')->unique()->all();
            $q->whereIn('my_class_id', $classIds ?: [0])
              ->where(function ($w) use ($sectionIds) { $w->whereNull('section_id')->orWhereIn('section_id', $sectionIds ?: [0]); });
        }

        if ($req->filled('class_id')) { $q->where('my_class_id', $req->class_id); }
        if ($req->filled('date')) { $q->whereDate('date', $req->date); }

        $data['diaries'] = $q->paginate(20)->withQueryString();
        $data['can_write'] = $this->canWrite();
        $data['classes'] = MyClass::orderBy('name')->get();
        $data['subjects'] = Subject::orderBy('name')->get();
        return view('pages.support_team.diaries.index', $data);
    }

    public function create()
    {
        if (!$this->canWrite()) { abort(403); }
        $data['classes'] = MyClass::orderBy('name')->get();
        $data['sections'] = Section::orderBy('name')->get();
        $data['subjects'] = Subject::orderBy('name')->get();
        return view('pages.support_team.diaries.create', $data);
    }

    public function store(Request $req)
    {
        if (!$this->canWrite()) { abort(403); }
        $req->validate([
            'my_class_id' => 'required|exists:my_classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'date' => 'required|date',
            'title' => 'required|string|max:191',
            'description' => 'nullable|string|max:5000',
            'homework' => 'nullable|string|max:5000',
        ]);
        Diary::create($req->only(['my_class_id', 'section_id', 'subject_id', 'date', 'title', 'description', 'homework']) + ['user_id' => Auth::id()]);
        return redirect()->route('diaries.index')->with('flash_success', 'Diary entry added successfully.');
    }

    public function edit($diary_id)
    {
        if (!$this->canWrite()) { abort(403); }
        $diary = Diary::findOrFail($diary_id);
        if (Qs::userIsTeacher() && (int)$diary->user_id !== (int)Auth::id()) { abort(403, 'Teachers can only edit their own diary entries.'); }
        $data['diary'] = $diary;
        $data['classes'] = MyClass::orderBy('name')->get();
        $data['sections'] = Section::orderBy('name')->get();
        $data['subjects'] = Subject::orderBy('name')->get();
        return view('pages.support_team.diaries.edit', $data);
    }

    public function update(Request $req, $diary_id)
    {
        if (!$this->canWrite()) { abort(403); }
        $diary = Diary::findOrFail($diary_id);
        if (Qs::userIsTeacher() && (int)$diary->user_id !== (int)Auth::id()) { abort(403); }
        $req->validate([
            'my_class_id' => 'required|exists:my_classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'date' => 'required|date',
            'title' => 'required|string|max:191',
            'description' => 'nullable|string|max:5000',
            'homework' => 'nullable|string|max:5000',
        ]);
        $diary->update($req->only(['my_class_id', 'section_id', 'subject_id', 'date', 'title', 'description', 'homework']));
        return redirect()->route('diaries.index')->with('flash_success', 'Diary entry updated successfully.');
    }

    public function destroy($diary_id)
    {
        $diary = Diary::findOrFail($diary_id);
        if (!Qs::userIsTeamSA() && !(Qs::userIsTeacher() && (int)$diary->user_id === (int)Auth::id())) { abort(403); }
        $diary->delete();
        return back()->with('flash_success', 'Diary entry deleted.');
    }
}
