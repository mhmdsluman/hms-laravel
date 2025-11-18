<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::updateOrCreate(['key' => 'hospital_name'], ['value' => 'Community General Hospital']);
        Setting::updateOrCreate(['key' => 'hospital_address'], ['value' => '123 Main St, Anytown, USA 12345']);
        Setting::updateOrCreate(['key' => 'hospital_phone'], ['value' => '(123) 456-7890']);
        Setting::updateOrCreate(['key' => 'hospital_email'], ['value' => 'contact@communitygeneral.com']);
        Setting::updateOrCreate(['key' => 'hospital_logo'], ['value' => null]);
        Setting::updateOrCreate(['key' => 'hospital_domain'], ['value' => 'manhalhospital.com']);
        Setting::updateOrCreate(['key' => 'last_uhid_char'], ['value' => 'A']);
        Setting::updateOrCreate(['key' => 'last_uhid_numeric'], ['value' => '0']);
    }
}
