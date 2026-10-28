<?php

namespace Database\Seeders;

use App\Helpers\Qs;
use App\Models\Section;
use App\Models\StudentRecord;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentRecordsTableSeeder extends Seeder
{
    public function run()
    {
        $this->createStudentRecord();
        $this->createManyStudentRecords(3);
    }

    protected function createManyStudentRecords(int $count)
    {
        // Fast + retry-safe bulk version: factories with per-user bcrypt
        // hashing timed out on the serverless setup endpoint, and a
        // partial run must not collide on re-run.
        $expected = Section::count() * $count;
        if (StudentRecord::count() > 1 && StudentRecord::count() >= $expected) {
            return; // already fully seeded
        }

        $faker = \Faker\Factory::create();
        $hash = Hash::make('student'); // one hash reused for all demo students
        $now = date('Y-m-d H:i:s');
        $session = Qs::getCurrentSession();

        foreach (Section::all() as $section) {
            $userRows = [];
            $emails = [];
            for ($i = 0; $i < $count; $i++) {
                $email = 'student.' . Str::lower(Str::random(10)) . '@example.com';
                $emails[] = $email;
                $userRows[] = [
                    'name' => $faker->name,
                    'email' => $email,
                    'username' => $faker->userName . '_' . Str::lower(Str::random(4)),
                    'password' => $hash,
                    'user_type' => 'student',
                    'code' => strtoupper(Str::random(10)),
                    'remember_token' => Str::random(10),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('users')->insert($userRows);
            $ids = DB::table('users')->whereIn('email', $emails)->pluck('id')->all();
            $recordRows = [];
            foreach ($ids as $uid) {
                $recordRows[] = [
                    'session' => $session,
                    'my_class_id' => $section->my_class_id,
                    'section_id' => $section->id,
                    'user_id' => $uid,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            if ($recordRows) {
                DB::table('student_records')->insert($recordRows);
            }
        }
    }

    protected function createStudentRecord()
    {
        $section = Section::first();
        if (!$section) { return; }

        $user = User::where('email', 'student@student.com')->first();
        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Student CJ',
                'user_type' => 'student',
                'username' => 'student',
                'password' => Hash::make('cj'),
                'email' => 'student@student.com',
            ]);
        }

        StudentRecord::firstOrCreate(
            ['user_id' => $user->id],
            ['my_class_id' => $section->my_class_id, 'section_id' => $section->id, 'session' => Qs::getCurrentSession()]
        );
    }
}
