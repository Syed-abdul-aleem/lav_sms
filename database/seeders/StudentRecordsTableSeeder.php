<?php

namespace Database\Seeders;

use App\Helpers\Qs;
use App\Models\Section;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentRecordsTableSeeder extends Seeder
{
    /**
     * Small, clean Pakistani demo set — 6 students across 2 parents.
     * Students do NOT get logins in this system: their internal user rows
     * carry a random unusable password and student login is blocked at
     * the login controller. Parents see everything via My Children.
     */
    public function run()
    {
        DB::table('student_records')->delete();
        DB::table('users')->where('user_type', 'student')->delete();

        $parents = DB::table('users')->where('user_type', 'parent')->orderBy('id')->pluck('id')->all();
        if (!$parents) { return; }

        $lahore = DB::table('lgas')->where('name', 'Lahore')->first();
        $stateId = $lahore ? $lahore->state_id : 1;
        $lgaId = $lahore ? $lahore->id : 1;
        $pakistani = DB::table('nationalities')->where('name', 'Pakistani')->value('id') ?: 1;

        // [name, gender, dob, class name, parent index]
        $students = [
            ['Muhammad Ali',    'Male',   '2014-03-12', 'Class 5',  0],
            ['Fatima Zahra',    'Female', '2016-07-25', 'Class 3',  0],
            ['Ahmed Raza',      'Male',   '2011-01-19', 'Class 8',  0],
            ['Zainab Bibi',     'Female', '2014-11-05', 'Class 5',  1],
            ['Abdullah Shahid', 'Male',   '2019-05-30', 'Class 1',  1],
            ['Maryam Siddiqui', 'Female', '2009-09-14', 'Class 10', 1],
        ];

        $hash = Hash::make(Str::random(40)); // unusable on purpose — no student logins
        $now = date('Y-m-d H:i:s');
        $session = Qs::getCurrentSession();
        $appCode = Qs::getAppCode();

        foreach ($students as $i => $s) {
            [$name, $gender, $dob, $className, $pIdx] = $s;
            $section = Section::whereHas('my_class', function ($q) use ($className) {
                $q->where('name', $className);
            })->where('name', 'A')->first();
            if (!$section) { continue; }

            $class = $section->my_class;
            $ctCode = $class->class_type ? $class->class_type->code : 'P';
            $admNo = strtoupper($appCode . '/' . $ctCode . '/2026/' . (1001 + $i));

            $userId = DB::table('users')->insertGetId([
                'name' => $name,
                'email' => null,
                'username' => $admNo,
                'password' => $hash,
                'user_type' => 'student',
                'code' => strtoupper(Str::random(10)),
                'remember_token' => Str::random(10),
                'gender' => $gender,
                'dob' => $dob,
                'address' => 'Main Boulevard, Gulberg III, Lahore, Punjab, Pakistan',
                'state_id' => $stateId,
                'lga_id' => $lgaId,
                'nal_id' => $pakistani,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('student_records')->insert([
                'session' => $session,
                'my_class_id' => $section->my_class_id,
                'section_id' => $section->id,
                'user_id' => $userId,
                'my_parent_id' => $parents[$pIdx % count($parents)],
                'adm_no' => $admNo,
                'year_admitted' => '2026',
                'age' => date('Y') - (int) substr($dob, 0, 4),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
