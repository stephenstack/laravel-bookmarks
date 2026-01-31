<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Collection;
use App\Models\Bookmark;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'test@example.com')->first();
        if (!$user) return;

        // Create Tags
        $tags = [
            ['name' => 'Docs', 'color' => 'blue'],
            ['name' => 'Tool', 'color' => 'green'],
            ['name' => 'Design', 'color' => 'purple'],
            ['name' => 'Social', 'color' => 'pink'],
        ];

        foreach ($tags as $tagData) {
            Tag::create([
                'user_id' => $user->id,
                'name' => $tagData['name'],
                'slug' => strtolower($tagData['name']),
                'color' => $tagData['color'],
            ]);
        }

        $tagModels = Tag::where('user_id', $user->id)->get();

        // Create Collections
        $collections = [
            ['name' => 'Development', 'icon' => 'Code', 'color' => 'blue'],
            ['name' => 'Design Resources', 'icon' => 'Palette', 'color' => 'purple'],
            ['name' => 'Social Media', 'icon' => 'Share2', 'color' => 'pink'],
        ];

        foreach ($collections as $colData) {
            $col = Collection::create([
                'user_id' => $user->id,
                'name' => $colData['name'],
                'icon' => $colData['icon'],
                'color' => $colData['color'],
                'sort_by' => 'date-newest',
            ]);

            // Add some benchmarks
            if ($col->name === 'Development') {
                $links = [
                    [
                        'title' => 'Laravel Documentation',
                        'url' => 'https://laravel.com/docs',
                        'description' => 'The best PHP framework documentation in the world.',
                        'favicon' => 'https://laravel.com/favicon.ico',
                        'image_url' => 'https://laravel.com/img/og-image.png',
                        'tags' => ['Docs', 'Tool']
                    ],
                    [
                        'title' => 'Tailwind CSS',
                        'url' => 'https://tailwindcss.com',
                        'description' => 'A utility-first CSS framework packed with classes.',
                        'favicon' => 'https://tailwindcss.com/favicons/favicon-32x32.png?v=3',
                        'image_url' => 'https://tailwindcss.com/api/og?path=/docs/installation',
                        'tags' => ['Design', 'Tool']
                    ],
                    [
                        'title' => 'Inertia.js',
                        'url' => 'https://inertiajs.com',
                        'description' => 'The Modern Monolith. Build single-page apps without the complexity.',
                        'favicon' => 'https://inertiajs.com/favicon.ico',
                        'image_url' => 'https://inertiajs.com/img/og-image.png',
                        'tags' => ['Docs']
                    ]
                ];

                foreach ($links as $link) {
                    $bm = $col->bookmarks()->create([
                        'user_id' => $user->id,
                        'title' => $link['title'],
                        'url' => $link['url'],
                        'description' => $link['description'],
                        'favicon' => $link['favicon'],
                        'image_url' => $link['image_url'],
                        'status' => 'active',
                    ]);
                    
                    $matchedTags = $tagModels->whereIn('name', $link['tags'])->pluck('id');
                    $bm->tags()->attach($matchedTags);
                }
            }

            if ($col->name === 'Social Media') {
                $links = [
                    [
                        'title' => 'GitHub',
                        'url' => 'https://github.com',
                        'description' => 'Where the world builds software.',
                        'favicon' => 'https://github.githubassets.com/favicons/favicon.svg',
                        'image_url' => 'https://github.githubassets.com/images/modules/open_graph/github-logo.png',
                        'tags' => ['Social']
                    ],
                    [
                        'title' => 'X (formerly Twitter)',
                        'url' => 'https://x.com',
                        'description' => 'Live updates and real-time news.',
                        'favicon' => 'https://abs.twimg.com/favicons/twitter.3.ico',
                        'image_url' => 'https://abs.twimg.com/errors/logo46x38.png',
                        'tags' => ['Social']
                    ]
                ];

                foreach ($links as $link) {
                    $bm = $col->bookmarks()->create([
                        'user_id' => $user->id,
                        'title' => $link['title'],
                        'url' => $link['url'],
                        'description' => $link['description'],
                        'favicon' => $link['favicon'],
                        'image_url' => $link['image_url'],
                        'status' => 'active',
                    ]);
                    $matchedTags = $tagModels->whereIn('name', $link['tags'])->pluck('id');
                    $bm->tags()->attach($matchedTags);
                }
            }
        }
    }
}
