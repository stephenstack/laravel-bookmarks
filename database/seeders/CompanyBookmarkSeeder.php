<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanyBookmarkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bookmarks = [
            [
                'title' => 'Advanced Network Configuration Management Tools - rConfig',
                'url' => 'https://www.rconfig.com/',
                'description' => 'Unlock efficient IT operations with our open-source network configuration management tool. Seamless network device configuration management at scale.',
                'favicon' => '/images/brand/demo/www-rconfig-com-favicon.ico',
                'image_url' => '/images/brand/demo/www-rconfig-com-og.png',
            ],
            [
                'title' => 'HubSpot | Software & Tools for your Business - Homepage',
                'url' => 'https://www.hubspot.com/',
                'description' => "HubSpot's customer platform includes all the marketing, sales, customer service, and CRM software you need to grow your business.",
                'favicon' => '/images/brand/demo/www-hubspot-com-favicon.png',
                'image_url' => '/images/brand/demo/www-hubspot-com-og.jpg',
            ],
            [
                'title' => 'Accounting Software for Small Businesses | Xero IE',
                'url' => 'https://www.xero.com/ie/',
                'description' => 'Xero online accounting software for your business connects you to your bank, accountant, bookkeeper, and other business apps. Start a free trial today.',
                'favicon' => '/images/brand/demo/www-xero-com-favicon.ico',
                'image_url' => '/images/brand/demo/www-xero-com-og.jpg',
            ],
            [
                'title' => 'Refurbished server for sale in Hetzner Server Auction',
                'url' => 'https://www.hetzner.com/sb/',
                'description' => 'Be quick and save money: Top and cheap refurbished dedicated servers at Hetzner Server Auction',
                'favicon' => '/images/brand/demo/www-hetzner-com-favicon.ico',
            ],
            [
                'title' => 'Create Email Marketing Your Audience Will Love - MailerLite',
                'url' => 'https://www.mailerlite.com/',
                'description' => 'Digital marketing tools to grow your audience faster and drive revenue smarter. Backed by 24/7 award-winning support. Check it out now!',
                'favicon' => '/images/brand/demo/www-mailerlite-com-favicon.png',
                'image_url' => '/images/brand/demo/www-mailerlite-com-og.jpg',
            ],
        ];

        foreach ($bookmarks as $bookmark) {
            \App\Models\CompanyBookmark::create($bookmark);
        }

        // Add a default "Company" tag for users to use
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            \App\Models\Tag::updateOrCreate(
                ['user_id' => $user->id, 'name' => 'Company'],
                [
                    'slug' => 'company',
                    'color' => 'blue'
                ]
            );
        }
    }
}
