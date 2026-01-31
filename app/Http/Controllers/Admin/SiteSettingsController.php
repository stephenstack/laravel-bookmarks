<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use App\Models\Setting;
use App\Models\CompanyBookmark;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;

class SiteSettingsController extends Controller
{
    public function testEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        try {
            Mail::to($request->email)->send(new TestMail());
            return response()->json(['message' => 'Email sent']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function edit()
    {
        $settings = Setting::all()->keyBy('key')->map(function($setting) {
             return Setting::get($setting->key);
        });

        $settings['setup_required'] = !Setting::get('company_collection_title') || CompanyBookmark::count() === 0;

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
            // Use Microlink for higher quality extraction and actual screenshots
            // It provides title, description, logo, and screenshot in one go.
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ])->timeout(20)->get("https://api.microlink.io/", [
                'url' => $url,
                'screenshot' => true,
                'meta' => true
            ]);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                
                $image = $data['image']['url'] ?? $data['screenshot']['url'] ?? null;
                
                // If Microlink didn't find an image, use Thum.io for a clean site screenshot (no WP logo)
                if (!$image) {
                    $image = "https://image.thum.io/get/width/1200/crop/675/" . $url;
                }

                return response()->json([
                    'title' => trim($data['title'] ?? ''),
                    'description' => trim($data['description'] ?? ''),
                    'image_url' => $image,
                    'favicon' => $data['logo']['url'] ?? "https://www.google.com/s2/favicons?sz=128&domain_url=" . urlencode($url),
                ]);
            }

            // Fallback to manual scraping if Microlink fails
            $response = Http::timeout(10)->get($url);
            $html = $response->body();
            $dom = new \DOMDocument();
            @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
            $xpath = new \DOMXPath($dom);

            $title = $xpath->query('//title')->item(0)?->nodeValue;
            $description = $xpath->query('//meta[@name="description"]/@content')->item(0)?->nodeValue 
                        ?? $xpath->query('//meta[@property="og:description"]/@content')->item(0)?->nodeValue
                        ?? $xpath->query('//meta[@name="twitter:description"]/@content')->item(0)?->nodeValue;
            
            $image = $xpath->query('//meta[@property="og:image"]/@content')->item(0)?->nodeValue
                    ?? $xpath->query('//meta[@name="twitter:image"]/@content')->item(0)?->nodeValue;
            
            // If still no image found in meta, use Thum.io for a clean screenshot
            if (!$image) {
                $image = "https://image.thum.io/get/width/1200/crop/675/" . $url;
            }

            return response()->json([
                'title' => trim($title ?? ''),
                'description' => trim($description ?? ''),
                'image_url' => $image,
                'favicon' => "https://www.google.com/s2/favicons?sz=128&domain_url=" . urlencode($url),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not interrogate URL: ' . $e->getMessage()], 500);
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
