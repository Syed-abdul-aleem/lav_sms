<?php

namespace Database\Seeders;

use App\Models\MyClass;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('subjects')->delete();

        $this->createSubjects();
    }

    protected function createSubjects()
    {
        // Pakistani curriculum subjects by class band
        $prePrimary = [
            ['English', 'Eng'], ['Urdu', 'Urdu'], ['Mathematics', 'Math'],
            ['General Knowledge', 'GK'], ['Islamiyat', 'Isl'], ['Art & Craft', 'Art'],
        ];
        $primary = [
            ['English', 'Eng'], ['Urdu', 'Urdu'], ['Mathematics', 'Math'],
            ['General Science', 'GSci'], ['Social Studies', 'SST'], ['Islamiyat', 'Isl'],
            ['Computer Science', 'CS'], ['Art & Craft', 'Art'],
        ];
        $middle = [
            ['English', 'Eng'], ['Urdu', 'Urdu'], ['Mathematics', 'Math'],
            ['General Science', 'GSci'], ['Social Studies', 'SST'], ['Islamiyat', 'Isl'],
            ['Computer Science', 'CS'], ['Arabic', 'Arab'],
        ];
        $secondary = [
            ['English', 'Eng'], ['Urdu', 'Urdu'], ['Mathematics', 'Math'],
            ['Pakistan Studies', 'PakSt'], ['Islamiyat', 'Isl'], ['Physics', 'Phy'],
            ['Chemistry', 'Chem'], ['Biology', 'Bio'], ['Computer Science', 'CS'],
        ];

        $teachers = User::where('user_type', 'teacher')->pluck('id')->all();
        if (!$teachers) { return; }

        $rows = [];
        foreach (MyClass::with('class_type')->get() as $mc) {
            $code = $mc->class_type ? $mc->class_type->code : 'P';
            $set = $code === 'PP' ? $prePrimary : ($code === 'M' ? $middle : ($code === 'S' ? $secondary : $primary));
            foreach ($set as $i => $s) {
                $rows[] = [
                    'name' => $s[0],
                    'slug' => $s[1],
                    'my_class_id' => $mc->id,
                    'teacher_id' => $teachers[$i % count($teachers)],
                ];
            }
        }
        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('subjects')->insert($chunk);
        }
    }
}
