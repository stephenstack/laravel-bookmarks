<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Setting;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@domain.com',
            'password' => bcrypt('admin'),
            'is_admin' => true,
        ]);

        Setting::set('site_title', 'Logoipsum Demo');
        Setting::set('site_logo_light', '/images/brand/demo/logoipsum-380-light.png');
        Setting::set('site_logo_dark', '/images/brand/demo/logoipsum-380-dark.png');
        Setting::set('site_favicon_light', '/images/brand/demo/logoipsum-380-light.png');
        Setting::set('site_favicon_dark', '/images/brand/demo/logoipsum-380-dark.png');
        Setting::set('company_collection_title', 'Company Resources');
        Setting::set('company_collection_icon', 'Building');
        Setting::set('company_collection_color', 'blue');
        Setting::set('company_tag_name', 'Company');
        Setting::set('company_tag_color', 'blue');
        Setting::set('background_image', '/images/brand/demo/www-rconfig-com-og.png');
        Setting::set('background_opacity', 80);

        $this->call([
            CompanyBookmarkSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
