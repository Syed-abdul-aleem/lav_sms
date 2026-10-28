<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('grades')->delete();

        $this->createGrades();
    }

    protected function createGrades()
    {
        // Standard Pakistani board grading scale
        $d = [
            ['name' => 'A+', 'mark_from' => 90, 'mark_to' => 100, 'remark' => 'Outstanding'],
            ['name' => 'A', 'mark_from' => 80, 'mark_to' => 89, 'remark' => 'Excellent'],
            ['name' => 'B', 'mark_from' => 70, 'mark_to' => 79, 'remark' => 'Very Good'],
            ['name' => 'C', 'mark_from' => 60, 'mark_to' => 69, 'remark' => 'Good'],
            ['name' => 'D', 'mark_from' => 50, 'mark_to' => 59, 'remark' => 'Fair'],
            ['name' => 'E', 'mark_from' => 40, 'mark_to' => 49, 'remark' => 'Satisfactory'],
            ['name' => 'F', 'mark_from' => 0, 'mark_to' => 39, 'remark' => 'Fail'],
        ];
        DB::table('grades')->insert($d);
    }
}
