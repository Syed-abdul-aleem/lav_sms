<?php
namespace Database\Seeders;

use App\Models\MyClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectionsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('sections')->delete();
        $classes = MyClass::pluck('id')->all();

        // Every class gets Section A (active) and Section B
        $data = [];
        foreach ($classes as $cid) {
            $data[] = ['name' => 'A', 'my_class_id' => $cid, 'active' => 1];
            $data[] = ['name' => 'B', 'my_class_id' => $cid, 'active' => 0];
        }

        DB::table('sections')->insert($data);
    }
}
