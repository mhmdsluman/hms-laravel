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
        Setting::create(['key' => 'hospital_name', 'value' => 'Community General Hospital']);
        Setting::create(['key' => 'hospital_address', 'value' => '123 Main St, Anytown, USA 12345']);
        Setting::create(['key' => 'hospital_phone', 'value' => '(123) 456-7890']);
        Setting::create(['key' => 'hospital_email', 'value' => 'contact@communitygeneral.com']);
        Setting::create(['key' => 'hospital_logo', 'value' => null]);
        Setting::create(['key' => 'hospital_domain', 'value' => 'manhalhospital.com']);
    }
}
