<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\MyClass;
use App\Models\Section;
use App\Models\StudentRecord;
use App\Repositories\MyClassRepo;
use App\Repositories\UserRepo;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * One easy form: add a parent together with all their children.
 * Replaces the old two-step flow (Users -> add parent, then Students -> add
 * each child and hunt for the parent in a dropdown).
 */
class FamilyController extends Controller
{
    protected $user, $my_class;

    public function __construct(UserRepo $user, MyClassRepo $my_class)
    {
        $this->middleware('teamSA');
        $this->user = $user;
        $this->my_class = $my_class;
    }

    public function create()
    {
        $data['parents'] = $this->user->getUserByType('parent');
        $data['my_classes'] = $this->my_class->all();
        $data['sections'] = Section::all();
        $data['year'] = (int) date('Y');
        return view('pages.support_team.students.family', $data);
    }

    public function store(Request $req)
    {
        $req->validate([
            'parent_mode' => 'required|in:existing,new',
            'existing_parent_id' => 'required_if:parent_mode,existing|nullable|integer',
            'parent_name' => 'required_if:parent_mode,new|nullable|string|min:3|max:150',
            'parent_phone' => 'required_if:parent_mode,new|nullable|string|min:7|max:20',
            'parent_email' => 'nullable|email|max:100|unique:users,email',
            'parent_password' => 'nullable|string|min:4|max:50',
            'children' => 'required|array|min:1',
            'children.*.name' => 'required|string|min:3|max:150',
            'children.*.gender' => 'required|string',
            'children.*.my_class_id' => 'required|integer',
            'children.*.section_id' => 'required|integer',
            'children.*.dob' => 'nullable|date',
            'children.*.year_admitted' => 'nullable|string|max:10',
        ]);

        $summary = DB::transaction(function () use ($req) {
            // 1) Parent — existing or new
            $plainPass = null;
            if ($req->parent_mode === 'existing') {
                $parent = User::where('user_type', 'parent')->findOrFail((int) $req->existing_parent_id);
            } else {
                [$parent, $plainPass] = Qs::createParentAccount(
                    $req->parent_name, $req->parent_phone, $req->parent_email, $req->parent_password
                );
            }

            // 2) Children
            $names = [];
            foreach ($req->children as $child) {
                $names[] = $this->createStudent($parent->id, $child);
            }

            return ['parent' => $parent, 'children' => $names, 'password' => $plainPass];
        });

        $msg = 'Family saved: Parent "' . $summary['parent']->name . '" ke ' . count($summary['children'])
            . ' bachay add ho gaye (' . implode(', ', $summary['children']) . ').';
        if ($summary['password']) {
            $loginId = $summary['parent']->email ?: $summary['parent']->username;
            $msg .= ' Parent login: ' . $loginId . ' / password: ' . $summary['password']
                . ' — parent ko ye login de dein, wo My Account se password change kar sakta hai.';
        }

        return redirect()->route('students.family.create')->with('flash_success', $msg);
    }

    protected function createStudent(int $parentId, array $child)
    {
        $class = MyClass::with('class_type')->findOrFail((int) $child['my_class_id']);
        $ct = $class->class_type ? $class->class_type->code : 'P';
        $year = $child['year_admitted'] ?: date('Y');

        // Unique admission number
        $n = 1000 + StudentRecord::count();
        do {
            $n++;
            $admNo = strtoupper(Qs::getAppCode() . '/' . $ct . '/' . $year . '/' . $n);
        } while (StudentRecord::where('adm_no', $admNo)->exists() || User::where('username', $admNo)->exists());

        $user = User::create([
            'name' => ucwords($child['name']),
            'email' => null,
            'username' => $admNo,
            'password' => Hash::make(Str::random(40)), // no student login — parents manage everything
            'user_type' => 'student',
            'code' => strtoupper(Str::random(10)),
            'gender' => $child['gender'],
            'dob' => $child['dob'] ?? null,
            'photo' => Qs::getDefaultUserImage(),
        ]);

        StudentRecord::create([
            'user_id' => $user->id,
            'my_class_id' => $class->id,
            'section_id' => (int) $child['section_id'],
            'my_parent_id' => $parentId,
            'adm_no' => $admNo,
            'session' => Qs::getSetting('current_session'),
            'year_admitted' => $year,
            'age' => !empty($child['dob']) ? (date('Y') - (int) substr($child['dob'], 0, 4)) : null,
        ]);

        return $user->name;
    }
}
