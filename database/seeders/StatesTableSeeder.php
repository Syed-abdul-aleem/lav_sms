<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('states')->delete();

        // Pakistan only: provinces + territories (used as "State/Province" in address forms)
        $states = [
            'Punjab',
            'Sindh',
            'Khyber Pakhtunkhwa',
            'Balochistan',
            'Gilgit-Baltistan',
            'Azad Jammu & Kashmir',
            'Islamabad Capital Territory',
        ];

        $now = date('Y-m-d H:i:s');
        DB::table('states')->insert(array_map(function ($name) use ($now) {
            return ['name' => $name, 'created_at' => $now, 'updated_at' => $now];
        }, $states));
    }
}
