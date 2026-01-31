<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use App\Models\Setting;
use App\Models\CompanyBookmark;

class SiteSettingsController extends Controller
{
    public function edit()
    {
        $settings = Setting::all()->keyBy('key')->map(function($setting) {
             return Setting::get($setting->key);
        });

        $companyBookmarks = CompanyBookmark::orderBy('id')->get();

        return Inertia::render('Admin/SiteSettings', [
            'settings' => $settings,
            'companyBookmarks' => $companyBookmarks,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
             'site_title' => 'nullable|string',
             'company_collection_title' => 'nullable|string',
             'company_collection_color' => 'nullable|string',
             'company_collection_icon' => 'nullable|string',
             'site_repo_url' => 'nullable|url',
             'background_image' => 'nullable',
             'background_opacity' => 'nullable|integer|min:0|max:100',
        ]);

        $inputs = $request->except(['site_logo_light', 'site_logo_dark', 'site_favicon_light', 'site_favicon_dark', 'background_image']);

        foreach ($inputs as $key => $value) {
            Setting::set($key, $value, 'site');
        }

        $this->handleLogoUpload($request, 'site_logo_light');
        $this->handleLogoUpload($request, 'site_logo_dark');
        $this->handleLogoUpload($request, 'site_favicon_light');
        $this->handleLogoUpload($request, 'site_favicon_dark');
        $this->handleLogoUpload($request, 'background_image');

        return redirect()->back();
    }

    private function handleLogoUpload(Request $request, $key)
    {
        if ($request->hasFile($key)) {
            $path = $request->file($key)->store('logos', 'public');
            Setting::set($key, Storage::url($path), 'site');
        } elseif ($request->exists($key)) {
            // This allows existing strings or explicit null/empty values to be saved
            Setting::set($key, $request->input($key), 'site');
        }
    }

    public function interrogateUrl(Request $request)
    {
        $request->validate(['url' => 'required|url']);
        $url = $request->url;

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ])->timeout(15)->get($url);

            if (!$response->successful()) {
                return response()->json(['error' => 'Failed to fetch the page.'], 400);
            }

            $html = $response->body();
            $dom = new \DOMDocument();
            @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
            $xpath = new \DOMXPath($dom);

            $title = $xpath->query('//title')->item(0)?->nodeValue;
            $description = $xpath->query('//meta[@name="description"]/@content')->item(0)?->nodeValue 
                        ?? $xpath->query('//meta[@property="og:description"]/@content')->item(0)?->nodeValue;
            $ogImage = $xpath->query('//meta[@property="og:image"]/@content')->item(0)?->nodeValue;
            
            // Favicon logic
            $favicon = $xpath->query('//link[@rel="icon" or @rel="shortcut icon"]/@href')->item(0)?->nodeValue;
            
            if (!$ogImage) {
                // Fallback to WordPress mshots for screenshot
                $ogImage = "https://s0.wp.com/mshots/v1/" . urlencode($url) . "?w=1200";
            }

            if ($favicon && !str_starts_with($favicon, 'http')) {
                $parsedUrl = parse_url($url);
                $baseUrl = ($parsedUrl['scheme'] ?? 'https') . '://' . ($parsedUrl['host'] ?? '');
                $favicon = $baseUrl . (str_starts_with($favicon, '/') ? '' : '/') . $favicon;
            }

            return response()->json([
                'title' => trim($title ?? ''),
                'description' => trim($description ?? ''),
                'image_url' => $ogImage,
                'favicon' => $favicon,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateCompanyBookmarks(Request $request)
    {
        $data = $request->validate([
            'bookmarks' => 'present|array',
            'bookmarks.*.id' => 'nullable|integer',
            'bookmarks.*.title' => 'required|string',
            'bookmarks.*.url' => 'required|url',
            'bookmarks.*.description' => 'nullable|string',
            'bookmarks.*.favicon' => 'nullable|string',
            'bookmarks.*.image_url' => 'nullable|string',
        ]);
        
        $incomingIds = collect($data['bookmarks'])->pluck('id')->filter()->toArray();
        
        CompanyBookmark::whereNotIn('id', $incomingIds)->delete();
        
        foreach ($data['bookmarks'] as $bookmark) {
            CompanyBookmark::updateOrCreate(
                ['id' => $bookmark['id'] ?? null],
                [
                    'title' => $bookmark['title'],
                    'url' => $bookmark['url'],
                    'description' => $bookmark['description'] ?? null,
                    'favicon' => $bookmark['favicon'] ?? null,
                    'image_url' => $bookmark['image_url'] ?? null,
                ]
            );
        }
        
        return redirect()->back();
    }
}
