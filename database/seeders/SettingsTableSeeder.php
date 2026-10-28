<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('settings')->delete();

        // Default Pakistani demo school — Super Admin can rename it in Settings
        // for whichever real school uses the system.
        $data = [
            ['type' => 'current_session', 'description' => '2026-2027'],
            ['type' => 'system_title', 'description' => 'PMS'],
            ['type' => 'system_name', 'description' => 'Pakistan Model School'],
            ['type' => 'term_ends', 'description' => '31/03/2027'],
            ['type' => 'term_begins', 'description' => '01/08/2026'],
            ['type' => 'phone', 'description' => '0300-1234567'],
            ['type' => 'address', 'description' => 'Main Boulevard, Gulberg III, Lahore, Punjab, Pakistan'],
            ['type' => 'system_email', 'description' => 'info@pakistanmodelschool.pk'],
            ['type' => 'alt_email', 'description' => ''],
            ['type' => 'email_host', 'description' => ''],
            ['type' => 'email_pass', 'description' => ''],
            ['type' => 'lock_exam', 'description' => 0],
            ['type' => 'logo', 'description' => ''],
            // Next-term fees in PKR (Rs) per class type
            ['type' => 'next_term_fees_pp', 'description' => '15000'],
            ['type' => 'next_term_fees_p', 'description' => '18000'],
            ['type' => 'next_term_fees_m', 'description' => '22000'],
            ['type' => 'next_term_fees_s', 'description' => '28000'],
        ];

        DB::table('settings')->insert($data);
    }
}
