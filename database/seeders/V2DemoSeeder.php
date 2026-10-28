<?php
namespace Database\Seeders;

use App\Models\Diary;
use App\Models\SalaryPayment;
use App\Models\StaffRecord;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class V2DemoSeeder extends Seeder
{
    public function run()
    {
        // Staff records for all staff accounts (needed by salary slips),
        // with each teacher's subjects written at creation time.
        $teacherSubjects = [
            'teacher' => 'English, Mathematics',
            'teacher2' => 'Urdu, General Science, Islamiyat',
        ];
        $staff = User::whereIn('user_type', ['teacher', 'admin', 'accountant', 'librarian'])->get();
        foreach ($staff as $u) {
            StaffRecord::updateOrCreate(
                ['user_id' => $u->id],
                ['code' => 'STF-' . str_pad($u->id, 4, '0', STR_PAD_LEFT), 'emp_date' => '2023-01-15',
                 'subjects' => $teacherSubjects[$u->username] ?? null]
            );
        }

        // Demo class diary entries by the demo teacher (Pakistani school context)
        $teacher = User::where('username', 'teacher')->first();
        if ($teacher && DB::table('my_classes')->exists()) {
            $classes = DB::table('my_classes')->orderBy('id')->limit(2)->get();
            foreach ($classes as $c) {
                $section = DB::table('sections')->where('my_class_id', $c->id)->first();
                $subject = DB::table('subjects')->where('my_class_id', $c->id)->first();
                Diary::firstOrCreate(
                    ['my_class_id' => $c->id, 'date' => date('Y-m-d'), 'title' => 'Welcome & class rules'],
                    ['section_id' => $section ? $section->id : null, 'subject_id' => $subject ? $subject->id : null, 'user_id' => $teacher->id,
                     'description' => 'Introduced the class routine, attendance and class rules. Completed the first lesson and checked notebooks.',
                     'homework' => 'Revise today\'s lesson and complete exercise questions 1-5 in the notebook.']
                );
                Diary::firstOrCreate(
                    ['my_class_id' => $c->id, 'date' => date('Y-m-d', strtotime('-1 day')), 'title' => 'Reading & practice work'],
                    ['section_id' => $section ? $section->id : null, 'subject_id' => $subject ? $subject->id : null, 'user_id' => $teacher->id,
                     'description' => 'Reading practice in class; students read aloud and answered oral questions.',
                     'homework' => 'Read the passage on page 12 and write 5 new words with meanings.']
                );
            }
        }

        // Demo salary payments (PKR) for the current month
        $month = date('Y-m');
        $mk = function ($username, $basic, $allow, $ded, $paid) use ($month) {
            $u = User::where('username', $username)->first();
            if (!$u) return;
            $next = (SalaryPayment::max('id') ?: 0) + 1;
            SalaryPayment::firstOrCreate(['user_id' => $u->id, 'month' => $month], [
                'basic' => $basic, 'allowances' => $allow, 'deductions' => $ded, 'amt_paid' => $paid,
                'method' => 'Bank Transfer', 'receipt_no' => 'SAL-' . str_pad($next, 6, '0', STR_PAD_LEFT),
                'paid_on' => date('Y-m-d'), 'recorded_by' => 1,
            ]);
        };
        $mk('teacher', 85000, 5000, 2000, 88000);      // fully paid
        $mk('accountant', 70000, 3000, 1000, 40000);   // partial
        $mk('admin', 120000, 10000, 3000, 127000);     // fully paid
    }
}
