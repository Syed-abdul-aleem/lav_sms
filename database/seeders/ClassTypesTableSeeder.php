<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassTypesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('class_types')->delete();

        // Pakistani school structure
        $data = [
            ['name' => 'Pre Primary', 'code' => 'PP'],
            ['name' => 'Primary', 'code' => 'P'],
            ['name' => 'Middle', 'code' => 'M'],
            ['name' => 'Secondary', 'code' => 'S'],
        ];

        DB::table('class_types')->insert($data);
    }
}
