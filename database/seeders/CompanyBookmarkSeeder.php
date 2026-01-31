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
                'title' => 'RConfig',
                'url' => 'https://www.rconfig.com/',
                'description' => 'Network automation platform',
                'favicon' => 'https://www.rconfig.com/favicon.ico',
            ],
            [
                'title' => 'Company Portal',
                'url' => 'https://example.com/portal',
                'description' => 'Employee portal access',
                'favicon' => 'https://example.com/favicon.ico',
            ],
            [
                'title' => 'File Share',
                'url' => 'https://example.com/files',
                'description' => 'Secure document sharing',
                'favicon' => 'https://example.com/favicon.ico',
            ],
            [
                'title' => 'Admin Console',
                'url' => 'https://example.com/admin',
                'description' => 'Administration console',
                'favicon' => 'https://example.com/favicon.ico',
            ],
            [
                'title' => 'CRM Dashboard',
                'url' => 'https://example.com/crm',
                'description' => 'Customer relationship management',
                'favicon' => 'https://example.com/favicon.ico',
            ],
            [
                'title' => 'Support Desk',
                'url' => 'https://example.com/support',
                'description' => 'Support ticketing',
                'favicon' => 'https://example.com/favicon.ico',
            ],
            [
                'title' => 'Finance Suite',
                'url' => 'https://example.com/finance',
                'description' => 'Accounting and billing',
                'favicon' => 'https://example.com/favicon.ico',
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
