<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LgasTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('lgas')->delete();

        // Pakistani districts/cities per province (the "LGA" field works as District in the forms).
        // State IDs follow StatesTableSeeder order:
        // 1 Punjab, 2 Sindh, 3 KPK, 4 Balochistan, 5 Gilgit-Baltistan, 6 AJK, 7 ICT
        $byState = [
            1 => ['Lahore', 'Faisalabad', 'Rawalpindi', 'Gujranwala', 'Multan', 'Sialkot', 'Bahawalpur', 'Sargodha', 'Sheikhupura', 'Rahim Yar Khan', 'Jhang', 'Dera Ghazi Khan', 'Gujrat', 'Sahiwal', 'Wah Cantonment', 'Kasur', 'Okara', 'Chiniot', 'Hafizabad', 'Attock', 'Muzaffargarh', 'Khanewal', 'Mianwali', 'Bahawalnagar', 'Pakpattan', 'Vehari', 'Lodhran', 'Toba Tek Singh', 'Chakwal', 'Jhelum', 'Narowal', 'Mandi Bahauddin', 'Bhakkar', 'Khushab', 'Layyah', 'Nankana Sahib', 'Rajanpur'],
            2 => ['Karachi', 'Hyderabad', 'Sukkur', 'Larkana', 'Nawabshah', 'Mirpur Khas', 'Jacobabad', 'Shikarpur', 'Khairpur', 'Dadu', 'Badin', 'Thatta', 'Sanghar', 'Umerkot', 'Ghotki', 'Kashmore', 'Qambar Shahdadkot', 'Jamshoro', 'Matiari', 'Tando Allahyar', 'Tando Muhammad Khan', 'Sujawal', 'Tharparkar'],
            3 => ['Peshawar', 'Mardan', 'Mingora (Swat)', 'Abbottabad', 'Kohat', 'Dera Ismail Khan', 'Bannu', 'Charsadda', 'Nowshera', 'Mansehra', 'Haripur', 'Swabi', 'Dir', 'Chitral', 'Tank', 'Lakki Marwat', 'Karak', 'Hangu', 'Battagram', 'Malakand'],
            4 => ['Quetta', 'Gwadar', 'Turbat (Kech)', 'Khuzdar', 'Sibi', 'Loralai', 'Zhob', 'Chaman (Qila Abdullah)', 'Pishin', 'Nushki', 'Kalat', 'Nasirabad', 'Jaffarabad', 'Kharan', 'Awaran', 'Lasbela (Hub)', 'Mastung', 'Dera Bugti', 'Kohlu', 'Barkhan'],
            5 => ['Gilgit', 'Skardu', 'Hunza', 'Nagar', 'Ghizer', 'Diamer', 'Astore', 'Ghanche', 'Shigar', 'Kharmang'],
            6 => ['Muzaffarabad', 'Mirpur', 'Bhimber', 'Kotli', 'Poonch (Rawalakot)', 'Bagh', 'Sudhanoti', 'Neelum', 'Jhelum Valley (Hattian Bala)', 'Haveli'],
            7 => ['Islamabad'],
        ];

        $now = date('Y-m-d H:i:s');
        $rows = [];
        foreach ($byState as $stateId => $districts) {
            foreach ($districts as $name) {
                $rows[] = ['state_id' => $stateId, 'name' => $name, 'created_at' => $now, 'updated_at' => $now];
            }
        }
        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('lgas')->insert($chunk);
        }
    }
}
