<?php
namespace Database\Seeders;

use App\Models\ClassType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MyClassesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('my_classes')->delete();
        $ct = ClassType::pluck('id', 'name')->all();
        // Order of ClassTypes seed: Pre Primary, Primary, Middle, Secondary
        $ids = array_values(ClassType::pluck('id')->all());

        $data = [
            ['name' => 'Playgroup', 'class_type_id' => $ids[0]],
            ['name' => 'Nursery', 'class_type_id' => $ids[0]],
            ['name' => 'KG', 'class_type_id' => $ids[0]],
            ['name' => 'Class 1', 'class_type_id' => $ids[1]],
            ['name' => 'Class 2', 'class_type_id' => $ids[1]],
            ['name' => 'Class 3', 'class_type_id' => $ids[1]],
            ['name' => 'Class 4', 'class_type_id' => $ids[1]],
            ['name' => 'Class 5', 'class_type_id' => $ids[1]],
            ['name' => 'Class 6', 'class_type_id' => $ids[2]],
            ['name' => 'Class 7', 'class_type_id' => $ids[2]],
            ['name' => 'Class 8', 'class_type_id' => $ids[2]],
            ['name' => 'Class 9', 'class_type_id' => $ids[3]],
            ['name' => 'Class 10', 'class_type_id' => $ids[3]],
        ];

        DB::table('my_classes')->insert($data);
    }
}
