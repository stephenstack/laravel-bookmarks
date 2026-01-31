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
                'title' => 'Pastebin',
                'url' => 'https://paste.rconfig.com/',
                'description' => 'Internal code sharing tool',
                'favicon' => 'https://paste.rconfig.com/favicon.ico',
            ],
            [
                'title' => 'Drop',
                'url' => 'https://drop.rconfig.com/',
                'description' => 'File sharing service',
                'favicon' => 'https://drop.rconfig.com/favicon.ico',
            ],
            [
                'title' => 'Portal Login',
                'url' => 'https://portal.rconfig.com/login',
                'description' => 'Customer portal access',
                'favicon' => 'https://portal.rconfig.com/favicon.ico',
            ],
            [
                'title' => 'Nova Admin',
                'url' => 'https://nova.rconfig.com/admin/login',
                'description' => 'Nova administration panel',
                'favicon' => 'https://nova.rconfig.com/favicon.ico',
            ],
            [
                'title' => 'CRM Dashboard',
                'url' => 'https://crm.rconfig.com/dashboard',
                'description' => 'Customer Relationship Management',
                'favicon' => 'https://crm.rconfig.com/favicon.ico',
            ],
            [
                'title' => 'Helpdesk Tickets',
                'url' => 'https://helpdesk.rconfig.com/a/tickets/filters/14421',
                'description' => 'Support ticket queue',
                'favicon' => 'https://helpdesk.rconfig.com/favicon.ico',
            ],
            [
                'title' => 'Xero Login',
                'url' => 'https://login.xero.com/identity/user/login',
                'description' => 'Accounting software',
                'favicon' => 'https://login.xero.com/favicon.ico',
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
