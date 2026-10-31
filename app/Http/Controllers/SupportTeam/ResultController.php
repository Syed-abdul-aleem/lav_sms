<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\ExamRecord;
use App\Models\Mark;
use App\Models\Section;
use App\Models\StudentRecord;
use App\Models\Subject;
use App\Repositories\ExamRepo;
use App\Repositories\MarkRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\StudentRepo;
use Illuminate\Http\Request;

/**
 * Results (Easy) — aik hi jagah se: exam banao, poori class ke marks
 * aik grid me darj karo, class result + har student ka result card dekho.
 * Marks wahi purani (marks) table me save hote hain — purana system
 * (detailed t1/t2 entry, marksheet, tabulation) sath chalta rehta hai.
 */
class ResultController extends Controller
{
    protected $my_class, $exam, $student, $mark, $year;

    public function __construct(MyClassRepo $my_class, ExamRepo $exam, StudentRepo $student, MarkRepo $mark)
    {
        $this->middleware('teamAccount');
        $this->my_class = $my_class;
        $this->exam = $exam;
        $this->student = $student;
        $this->mark = $mark;
        $this->year = Qs::getCurrentSession();
    }

    /* ---------------- Hub ---------------- */

    public function index()
    {
        $d['exams'] = $this->exam->getExam(['year' => $this->year]);
        $d['my_classes'] = $this->my_class->all();
        $d['sections'] = Section::all();
        return view('pages.support_team.results.index', $d);
    }

    /** Quick exam create — sirf naam + term */
    public function examStore(Request $req)
    {
        $req->validate([
            'name' => 'required|string|max:100',
            'term' => 'required|in:1,2,3',
        ]);
        // System me har term ka AIK hi exam hota hai (term+year unique)
        $existing = $this->exam->getExam(['term' => (int) $req->term, 'year' => $this->year])->first();
        if ($existing) {
            return back()->with('pop_error', "Term {$req->term} ka exam pehle se mojood hai: \"{$existing->name}\" — wahi select kar ke marks darj karein.");
        }
        $this->exam->create(['name' => $req->name, 'term' => (int) $req->term, 'year' => $this->year]);
        return back()->with('flash_success', 'Exam ban gaya — ab class chun kar marks darj karein.');
    }

    /* ---------------- Helpers ---------------- */

    protected function studentsOf($class_id, $section_id)
    {
        return StudentRecord::with('user')
            ->where('my_class_id', $class_id)->where('section_id', $section_id)
            ->get()->filter(function ($sr) { return $sr->user; })
            ->sortBy(function ($sr) { return $sr->user->name; })->values();
    }

    protected function subjectsOf($class_id)
    {
        return Subject::where('my_class_id', $class_id)->orderBy('name')->get();
    }

    protected function context(Request $req)
    {
        $exam = $this->exam->find((int) $req->exam_id);
        $class = $this->my_class->find((int) $req->class_id);
        if (!$exam || !$class) { return null; }
        $section_id = (int) $req->section_id;
        if (!$section_id) {
            $first = Section::where('my_class_id', $class->id)->orderBy('id')->first();
            $section_id = $first ? $first->id : 0;
        }
        return [$exam, $class, $section_id];
    }

    /* ---------------- Marks entry grid ---------------- */

    public function entry(Request $req)
    {
        $ctx = $this->context($req);
        if (!$ctx) { return redirect(route('results.index'))->with('pop_error', 'Pehle exam aur class select karein.'); }
        [$exam, $class, $section_id] = $ctx;

        $students = $this->studentsOf($class->id, $section_id);
        $subjects = $this->subjectsOf($class->id);
        $termCol = 'tex' . $exam->term;

        $existing = Mark::where(['exam_id' => $exam->id, 'my_class_id' => $class->id, 'section_id' => $section_id, 'year' => $this->year])
            ->get()->groupBy('student_id');

        $grid = [];
        foreach ($students as $sr) {
            foreach ($subjects as $sub) {
                $mk = optional($existing->get($sr->user_id))->firstWhere('subject_id', $sub->id);
                $grid[$sr->user_id][$sub->id] = $mk ? ($mk->$termCol ?? (($mk->tca ?: 0) + ($mk->exm ?: 0))) : null;
            }
        }

        return view('pages.support_team.results.entry', [
            'exam' => $exam, 'class' => $class, 'section_id' => $section_id,
            'students' => $students, 'subjects' => $subjects, 'grid' => $grid,
            'sections' => Section::where('my_class_id', $class->id)->orderBy('id')->get(),
        ]);
    }

    public function entryStore(Request $req)
    {
        $ctx = $this->context($req);
        if (!$ctx) { return redirect(route('results.index'))->with('pop_error', 'Exam ya class nahi mili.'); }
        [$exam, $class, $section_id] = $ctx;

        $students = $this->studentsOf($class->id, $section_id);
        $subjects = $this->subjectsOf($class->id);
        $class_type = $this->my_class->findTypeByClass($class->id);
        $termCol = 'tex' . $exam->term;
        $input = (array) $req->input('marks', []);
        $invalid = 0; $saved = 0;

        foreach ($students as $sr) {
            foreach ($subjects as $sub) {
                $raw = $input[$sr->user_id][$sub->id] ?? null;
                if ($raw === null || trim((string) $raw) === '') { continue; }
                $total = (int) $raw;
                if ($total < 0 || $total > 100) { $invalid++; continue; }

                $mk = $this->exam->createMark([
                    'student_id' => $sr->user_id, 'exam_id' => $exam->id, 'subject_id' => $sub->id,
                    'my_class_id' => $class->id, 'section_id' => $section_id, 'year' => $this->year,
                ]);
                $tca = (int) ($mk->tca ?: 0);
                $d = [];
                if ($total < $tca) { $d['t1'] = $d['t2'] = $d['t3'] = $d['t4'] = $d['tca'] = 0; $tca = 0; }
                $d['exm'] = $total - $tca;
                $d[$termCol] = $total;
                $grade = $this->mark->getGrade($total, $class_type ? $class_type->id : 0);
                $d['grade_id'] = $grade ? $grade->id : null;
                $this->exam->updateMark($mk->id, $d);
                $saved++;
            }
        }

        /* Subject positions (sirf un students ke liye jinki us subject ki mark row hai —
           purana helper khali row par error deta hai) */
        foreach ($subjects as $sub) {
            foreach ($students as $sr) {
                $where = ['student_id' => $sr->user_id, 'exam_id' => $exam->id, 'subject_id' => $sub->id,
                    'my_class_id' => $class->id, 'section_id' => $section_id, 'year' => $this->year];
                if (!Mark::where($where)->exists()) { continue; }
                $pos = $this->mark->getSubPos($sr->user_id, $exam, $class->id, $sub->id, $this->year);
                Mark::where($where)->update(['sub_pos' => $pos]);
            }
        }

        /* Exam records: kul total / average / class average / position */
        $classAve = $this->mark->getClassAvg($exam, $class->id, $this->year);
        foreach ($students as $sr) {
            $where = ['exam_id' => $exam->id, 'student_id' => $sr->user_id, 'my_class_id' => $class->id,
                'section_id' => $section_id, 'year' => $this->year];
            ExamRecord::firstOrCreate($where);
            $this->exam->updateRecord($where, [
                'total' => $this->mark->getExamTotalTerm($exam, $sr->user_id, $class->id, $this->year),
                'ave' => $this->mark->getExamAvgTerm($exam, $sr->user_id, $class->id, $section_id, $this->year),
                'class_ave' => $classAve,
                'pos' => $this->mark->getPos($sr->user_id, $exam, $class->id, $section_id, $this->year),
            ]);
        }

        $msg = "Marks save ho gaye ({$saved} entries). Class result tayyar hai.";
        $redirect = redirect(route('results.class', ['exam_id' => $exam->id, 'class_id' => $class->id, 'section_id' => $section_id]))
            ->with('flash_success', $msg);
        if ($invalid) {
            $redirect->with('flash_warning', "{$invalid} marks 0–100 ki range se bahar thay — wo save nahi hue.");
        }
        return $redirect;
    }

    /* ---------------- Class result ---------------- */

    protected function resultRows($exam, $class, $section_id)
    {
        $students = $this->studentsOf($class->id, $section_id);
        $subjects = $this->subjectsOf($class->id);
        $termCol = 'tex' . $exam->term;
        $class_type = $this->my_class->findTypeByClass($class->id);

        $marks = Mark::where(['exam_id' => $exam->id, 'my_class_id' => $class->id, 'section_id' => $section_id, 'year' => $this->year])
            ->get()->groupBy('student_id');
        $records = ExamRecord::where(['exam_id' => $exam->id, 'my_class_id' => $class->id, 'section_id' => $section_id, 'year' => $this->year])
            ->get()->keyBy('student_id');

        $rows = [];
        foreach ($students as $sr) {
            $cells = []; $sum = 0; $counted = 0;
            foreach ($subjects as $sub) {
                $mk = optional($marks->get($sr->user_id))->firstWhere('subject_id', $sub->id);
                $val = $mk ? ($mk->$termCol ?? (($mk->tca ?: 0) + ($mk->exm ?: 0))) : null;
                $cells[$sub->id] = $val;
                if ($val !== null) { $sum += $val; $counted++; }
            }
            $rec = $records->get($sr->user_id);
            $total = $rec && $rec->total !== null ? (float) $rec->total : $sum;
            $ave = $subjects->count() ? round($total / $subjects->count(), 1) : 0;
            $grade = $this->mark->getGrade((int) round($ave), $class_type ? $class_type->id : 0);
            $rows[] = [
                'sr' => $sr, 'cells' => $cells, 'total' => $total, 'ave' => $ave,
                'pos' => $rec ? $rec->pos : null,
                'grade' => $grade ? $grade->name : '—',
                'remark' => $grade ? $grade->remark : '',
                'counted' => $counted,
            ];
        }
        /* Agar saved positions na hon to total se rank bana do */
        $hasPos = collect($rows)->filter(function ($r) { return $r['pos']; })->count();
        if (!$hasPos) {
            $sorted = collect($rows)->sortByDesc('total')->values();
            foreach ($sorted as $i => $r) {
                foreach ($rows as &$row) {
                    if ($row['sr']->user_id === $r['sr']->user_id) { $row['pos'] = $i + 1; }
                }
            }
        } else {
            usort($rows, function ($a, $b) { return ($a['pos'] ?: 999) <=> ($b['pos'] ?: 999); });
        }
        return [$students, $subjects, $rows];
    }

    public function classResult(Request $req)
    {
        $ctx = $this->context($req);
        if (!$ctx) { return redirect(route('results.index'))->with('pop_error', 'Pehle exam aur class select karein.'); }
        [$exam, $class, $section_id] = $ctx;
        [$students, $subjects, $rows] = $this->resultRows($exam, $class, $section_id);
        return view('pages.support_team.results.class', [
            'exam' => $exam, 'class' => $class, 'section_id' => $section_id,
            'subjects' => $subjects, 'rows' => $rows,
            'sections' => Section::where('my_class_id', $class->id)->orderBy('id')->get(),
        ]);
    }

    public function classPrint(Request $req)
    {
        $ctx = $this->context($req);
        if (!$ctx) { return redirect(route('results.index')); }
        [$exam, $class, $section_id] = $ctx;
        [$students, $subjects, $rows] = $this->resultRows($exam, $class, $section_id);
        $section = Section::find($section_id);
        return view('pages.support_team.results.print', [
            'exam' => $exam, 'class' => $class, 'section' => $section,
            'subjects' => $subjects, 'rows' => $rows, 'year' => $this->year,
            'school' => Qs::getSetting('system_name') ?: config('app.name'),
        ]);
    }
}
