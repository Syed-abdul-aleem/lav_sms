<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->delete();

        $this->createCoreUsers();
    }

    /**
     * Only the core accounts a school needs — no factory/test logins.
     * All demo passwords are 'cj' and MUST be changed before real use.
     * Pakistani demo names, Punjab (state 1) / Lahore (first district) addresses.
     * state_id / lga_id follow StatesTableSeeder + LgasTableSeeder order,
     * resolved by name lookup so reseeds stay correct.
     */
    protected function createCoreUsers()
    {
        $password = Hash::make('cj');
        $lahore = DB::table('lgas')->where('name', 'Lahore')->first();
        $stateId = $lahore ? $lahore->state_id : 1;
        $lgaId = $lahore ? $lahore->id : 1;
        $pakistan = DB::table('nationalities')->where('name', 'Pakistani')->value('id') ?: 1;

        $base = [
            'password' => $password,
            'address' => 'Main Boulevard, Gulberg III, Lahore, Punjab, Pakistan',
            'state_id' => $stateId,
            'lga_id' => $lgaId,
            'nal_id' => $pakistan,
            'gender' => 'Male',
        ];

        $d = [
            array_merge($base, [
                'name' => 'Muhammad Ahmad',
                'email' => 'cj@cj.com',
                'username' => 'cj',
                'user_type' => 'super_admin',
                'phone' => '0300-1111111',
                'code' => strtoupper(Str::random(10)),
                'remember_token' => Str::random(10),
            ]),
            array_merge($base, [
                'name' => 'Ali Raza',
                'email' => 'admin@admin.com',
                'username' => 'admin',
                'user_type' => 'admin',
                'phone' => '0300-2222222',
                'code' => strtoupper(Str::random(10)),
                'remember_token' => Str::random(10),
            ]),
            array_merge($base, [
                'name' => 'Ahmed Khan',
                'email' => 'teacher@teacher.com',
                'username' => 'teacher',
                'user_type' => 'teacher',
                'phone' => '0300-3333333',
                'code' => strtoupper(Str::random(10)),
                'remember_token' => Str::random(10),
            ]),
            array_merge($base, [
                'name' => 'Fatima Noor',
                'email' => 'teacher2@teacher.com',
                'username' => 'teacher2',
                'user_type' => 'teacher',
                'gender' => 'Female',
                'phone' => '0300-4444444',
                'code' => strtoupper(Str::random(10)),
                'remember_token' => Str::random(10),
            ]),
            array_merge($base, [
                'name' => 'Bilal Hussain',
                'email' => 'parent@parent.com',
                'username' => 'parent',
                'user_type' => 'parent',
                'phone' => '0300-5555555',
                'code' => strtoupper(Str::random(10)),
                'remember_token' => Str::random(10),
            ]),
            array_merge($base, [
                'name' => 'Ayesha Malik',
                'email' => 'parent2@parent.com',
                'username' => 'parent2',
                'user_type' => 'parent',
                'gender' => 'Female',
                'phone' => '0300-6666666',
                'code' => strtoupper(Str::random(10)),
                'remember_token' => Str::random(10),
            ]),
            array_merge($base, [
                'name' => 'Usman Tariq',
                'email' => 'accountant@accountant.com',
                'username' => 'accountant',
                'user_type' => 'accountant',
                'phone' => '0300-7777777',
                'code' => strtoupper(Str::random(10)),
                'remember_token' => Str::random(10),
            ]),
        ];

        DB::table('users')->insert($d);
    }
}
