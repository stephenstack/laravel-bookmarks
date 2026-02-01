<?php

namespace Database\Seeders;

use App\Models\Bookmark;
use App\Models\Collection;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CompanyBookmarkSeeder::class);

        Setting::set('site_title', 'Laravel Bookmarks Demo');
        Setting::set('site_logo_light', '/images/brand/demo/logoipsum-380-light.png');
        Setting::set('site_logo_dark', '/images/brand/demo/logoipsum-380-dark.png');
        Setting::set('site_favicon_light', '/images/brand/demo/logoipsum-380-light.png');
        Setting::set('site_favicon_dark', '/images/brand/demo/logoipsum-380-dark.png');
        Setting::set('company_collection_title', 'Company Resources');
        Setting::set('company_collection_icon', 'Building');
        Setting::set('company_collection_color', 'blue');
        Setting::set('company_tag_name', 'Company');
        Setting::set('company_tag_color', 'blue');
        Setting::set('background_image', '/images/brand/demo/1105.jpg');
        Setting::set('background_opacity', 20);

        $user = User::where('email', 'admin@domain.com')->first()
            ?? User::where('email', 'test@example.com')->first()
            ?? User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Demo Admin User',
                'email' => 'admin@domain.com',
                'password' => bcrypt('admin'),
                'is_admin' => true,
            ]);
        }

        // Create Tags
        $tags = [
            ['name' => 'dev', 'color' => 'blue'],
            ['name' => 'business', 'color' => 'emerald'],
            ['name' => 'learning', 'color' => 'violet'],
            ['name' => 'reference', 'color' => 'slate'],
            ['name' => 'inspiration', 'color' => 'amber'],
            ['name' => 'productivity', 'color' => 'teal'],
        ];

        foreach ($tags as $tagData) {
            Tag::updateOrCreate(
                ['user_id' => $user->id, 'name' => $tagData['name']],
                [
                    'slug' => strtolower($tagData['name']),
                    'color' => $tagData['color'],
                ]
            );
        }

        $tagModels = Tag::where('user_id', $user->id)->get();

        // Create Collections
        $collections = [
            ['name' => 'Engineering & Dev', 'icon' => 'Code2', 'color' => 'blue'],
            ['name' => 'Business, Product & Growth', 'icon' => 'Rocket', 'color' => 'emerald'],
            ['name' => 'Personal, Learning & Inspiration', 'icon' => 'Brain', 'color' => 'amber'],
        ];

        $collectionModels = [];
        foreach ($collections as $colData) {
            $collectionModels[$colData['name']] = Collection::updateOrCreate(
                ['user_id' => $user->id, 'name' => $colData['name']],
                [
                    'icon' => $colData['icon'],
                    'color' => $colData['color'],
                    'sort_by' => 'date-newest',
                ]
            );
        }

        $linksByCollection = [
            'Engineering & Dev' => [
                [
                    'title' => 'Laravel official documentation',
                    'url' => 'https://laravel.com/docs',
                    'description' => 'Laravel is a PHP web application framework with expressive, elegant syntax. We’ve already laid the foundation — freeing you to create without sweating the small things.',
                    'favicon' => '/images/brand/demo/laravel-com-docs-favicon.png',
                    'image_url' => '/images/brand/demo/laravel-com-docs-og.png',
                    'tags' => ['dev', 'reference', 'learning'],
                ],
                [
                    'title' => 'Vue 3 documentation',
                    'url' => 'https://vuejs.org/guide/introduction.html',
                    'description' => 'Vue.js - The Progressive JavaScript Framework',
                    'favicon' => '/images/brand/demo/vuejs-org-guide-introduction-html-favicon.svg',
                    'image_url' => '/images/brand/demo/vuejs-org-guide-introduction-html-og.png',
                    'tags' => ['dev', 'reference', 'learning'],
                ],
                [
                    'title' => 'Tailwind CSS docs',
                    'url' => 'https://tailwindcss.com/docs',
                    'description' => 'Installing Tailwind CSS as a Vite plugin is the most seamless way to integrate it with frameworks like Laravel, SvelteKit, React Router, Nuxt, and SolidJS.',
                    'favicon' => '/images/brand/demo/tailwindcss-com-docs-favicon.png',
                    'image_url' => '/images/brand/demo/tailwindcss-com-docs-og.png',
                    'tags' => ['dev', 'reference'],
                ],
                [
                    'title' => 'MDN Web Docs',
                    'url' => 'https://developer.mozilla.org',
                    'description' => 'The MDN Web Docs site provides information about Open Web technologies including HTML, CSS, and APIs for both Web sites and progressive web apps.',
                    'favicon' => '/images/brand/demo/developer-mozilla-org-favicon.ico',
                    'image_url' => '/images/brand/demo/developer-mozilla-org-og.png',
                    'tags' => ['dev', 'reference', 'learning'],
                ],
                [
                    'title' => 'GitHub',
                    'url' => 'https://github.com',
                    'description' => 'Join the world’s most widely adopted, AI-powered developer platform where millions of developers, businesses, and the largest open source community build software that advances humanity.',
                    'favicon' => '/images/brand/demo/github-com-favicon.png',
                    'image_url' => '/images/brand/demo/github-com-og.png',
                    'tags' => ['dev'],
                ],
                [
                    'title' => 'Stack Overflow',
                    'url' => 'https://stackoverflow.com',
                    'description' => 'Stack Overflow | The World’s Largest Online Community for Developers',
                    'favicon' => '/images/brand/demo/stackoverflow-com-favicon.ico',
                    'image_url' => '/images/brand/demo/stackoverflow-com-og.png',
                    'tags' => ['dev', 'reference'],
                ],
            ],
            'Business, Product & Growth' => [
                [
                    'title' => 'Y Combinator startup library',
                    'url' => 'https://www.ycombinator.com/library',
                    'description' => "YC's library of startup advice: essays and videos that teach you how to start a company.",
                    'favicon' => '/images/brand/demo/www-ycombinator-com-library-favicon.ico',
                    'image_url' => '/images/brand/demo/www-ycombinator-com-library-og.png',
                    'tags' => ['business', 'learning'],
                ],
                [
                    'title' => 'Product, growth & org design',
                    'url' => 'https://www.lennysnewsletter.com',
                    'description' => "Deeply researched no-nonsense product, growth, and career advice—newsletter, podcast, community, and living library.",
                    'favicon' => '/images/brand/demo/www-lennysnewsletter-com-favicon.png',
                    'image_url' => '/images/brand/demo/www-lennysnewsletter-com-og.jpg',
                    'tags' => ['business', 'inspiration'],
                ],
                [
                    'title' => 'SaaS scaling and GTM',
                    'url' => 'https://www.saastr.com',
                    'description' => 'B2B + AI Community, Events, Leads',
                    'favicon' => '/images/brand/demo/www-saastr-com-favicon.png',
                    'image_url' => '/images/brand/demo/www-saastr-com-og.jpg',
                    'tags' => ['business'],
                ],
                [
                    'title' => 'Indie founder stories',
                    'url' => 'https://www.indiehackers.com',
                    'description' => 'Connect with developers sharing the strategies and revenue numbers behind their companies and side projects.',
                    'favicon' => '/images/brand/demo/www-indiehackers-com-favicon.png',
                    'image_url' => '/images/brand/demo/www-indiehackers-com-og.jpg',
                    'tags' => ['business', 'inspiration'],
                ],
                [
                    'title' => 'Business & tech deep dives',
                    'url' => 'https://www.notboring.co',
                    'description' => 'Tech strategy, analysis, and philosophy, but not boring.',
                    'favicon' => '/images/brand/demo/www-notboring-co-favicon.jpg',
                    'image_url' => '/images/brand/demo/www-notboring-co-og.jpg',
                    'tags' => ['business', 'inspiration'],
                ],
                [
                    'title' => 'Market and industry analysis',
                    'url' => 'https://www.cbinsights.com/research',
                    'description' => 'Market and industry analysis',
                    'favicon' => '/images/brand/demo/www-cbinsights-com-research-favicon.ico',
                    'image_url' => '/images/brand/demo/www-cbinsights-com-research-og.png',
                    'tags' => ['business', 'reference'],
                ],
            ],
            'Personal, Learning & Inspiration' => [
                [
                    'title' => 'Farnam Street – thinking & decision making',
                    'url' => 'https://fs.blog',
                    'description' => 'Timeless lessons on decision making, thinking, and continuous improvement.',
                    'favicon' => '/images/brand/demo/fs-blog-favicon.png',
                    'image_url' => '/images/brand/demo/fs-blog-og.png',
                    'tags' => ['learning', 'productivity', 'inspiration'],
                ],
                [
                    'title' => 'Long-form essays & ideas',
                    'url' => 'https://www.brainpickings.org',
                    'description' => 'Marginalia on our search for meaning.',
                    'favicon' => '/images/brand/demo/www-brainpickings-org-favicon.ico',
                    'image_url' => '/images/brand/demo/www-brainpickings-org-og.png',
                    'tags' => ['learning', 'inspiration'],
                ],
                [
                    'title' => 'Deep dives with stick figures',
                    'url' => 'https://waitbutwhy.com',
                    'description' => 'A popular long-form, stick-figure-illustrated blog about almost everything.',
                    'favicon' => '/images/brand/demo/waitbutwhy-com-favicon.ico',
                    'image_url' => null,
                    'tags' => ['inspiration'],
                ],
                [
                    'title' => 'Online courses',
                    'url' => 'https://www.coursera.org',
                    'description' => 'Learn in-demand skills with online courses and Professional Certificates from leading companies.',
                    'favicon' => '/images/brand/demo/www-coursera-org-favicon.png',
                    'image_url' => '/images/brand/demo/www-coursera-org-og.png',
                    'tags' => ['learning'],
                ],
                [
                    'title' => 'TED talks',
                    'url' => 'https://www.ted.com/talks',
                    'description' => 'TED Talks are influential videos from expert speakers on education, business, science, tech and creativity.',
                    'favicon' => '/images/brand/demo/www-ted-com-talks-favicon.png',
                    'image_url' => '/images/brand/demo/www-ted-com-talks-og.png',
                    'tags' => ['inspiration', 'learning'],
                ],
                [
                    'title' => 'Productivity & mental models',
                    'url' => 'https://nesslabs.com',
                    'description' => 'Want to achieve more without sacrificing your mental health? Say hello to mindful productivity.',
                    'favicon' => '/images/brand/demo/nesslabs-com-favicon.png',
                    'image_url' => '/images/brand/demo/nesslabs-com-og.png',
                    'tags' => ['productivity', 'learning'],
                ],
            ],
        ];

        foreach ($linksByCollection as $collectionName => $links) {
            $col = $collectionModels[$collectionName];
            foreach ($links as $link) {
                $bm = Bookmark::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'collection_id' => $col->id,
                        'url' => $link['url'],
                    ],
                    [
                        'title' => $link['title'],
                        'description' => $link['description'],
                        'favicon' => $link['favicon'],
                        'image_url' => $link['image_url'],
                        'status' => 'active',
                    ]
                );

                $matchedTags = $tagModels->whereIn('name', $link['tags'])->pluck('id');
                $bm->tags()->syncWithoutDetaching($matchedTags);
            }
        }
    }
}
