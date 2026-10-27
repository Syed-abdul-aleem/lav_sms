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
        // Demo contact numbers for the core seeded accounts (fictional demo data)
        $phones = [1 => '0300-0000001', 2 => '0300-0000002', 3 => '0300-0000003', 4 => '0300-0000004', 5 => '0300-0000005'];
        foreach ($phones as $id => $phone) {
            User::where('id', $id)->update(['phone' => $phone]);
        }
        User::where('user_type', 'parent')->whereNull('phone')
            ->orWhere('user_type', 'parent')->where('phone', '')
            ->get()->each(function ($u) { $u->update(['phone' => '0301-0000' . str_pad($u->id % 1000, 3, '0', STR_PAD_LEFT)]); });

        // Staff records for all staff accounts (needed by salary slips)
        $staff = User::whereIn('user_type', ['teacher', 'admin', 'accountant', 'librarian'])->get();
        foreach ($staff as $i => $u) {
            StaffRecord::firstOrCreate(['user_id' => $u->id], ['code' => 'STF-' . str_pad($u->id, 4, '0', STR_PAD_LEFT), 'emp_date' => '2023-01-15']);
        }

        // Link students to parents (round-robin) so parent logins show their children
        $parentIds = User::where('user_type', 'parent')->pluck('id')->all();
        if ($parentIds) {
            $records = DB::table('student_records')->whereNull('my_parent_id')->orderBy('id')->get();
            foreach ($records as $i => $r) {
                DB::table('student_records')->where('id', $r->id)->update(['my_parent_id' => $parentIds[$i % count($parentIds)]]);
            }
        }

        // Demo class diary entries by the demo teacher
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

        // Demo salary payments for the current month
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
